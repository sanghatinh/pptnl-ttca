<?php

namespace App\Http\Controllers\QuanlyHS;
use App\Http\Controllers\Controller; // Add this import
use App\Models\QuanlyHS\PaymentRequestAction; // เพิ่ม model สำหรับตาราง Action
use App\Models\QuanlyHS\PaymentRequest;
use App\Models\QuanlyHS\PaymentRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\QuanlyHS\BienBanNghiemThu;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
class PaymentRequestController extends Controller
{
    /**
     * ตรวจสอบรายการซ้ำซ้อน
     */
    public function checkDuplicates(Request $request)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string'
        ]);

        try {
            $receiptIds = $request->receipt_ids;
            
            // Use the correct table name here
            $duplicates = DB::table('Logs_phieu_trinh_thanh_toan') 
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select('ma_nghiem_thu', 'ma_trinh_thanh_toan')
                ->get();
            
            return response()->json([
                'success' => true,
                'duplicates' => $duplicates
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment request duplicates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking for duplicates: ' . $e->getMessage()
            ], 500);
        }
    }


/**
 * สร้างเอกสารขอเบิกเงินใหม่
 */
public function createPaymentRequest(Request $request)
{
    $request->validate([
        'investment_project' => 'required|string',
        'payment_type' => 'required|string',
        'payment_installment' => 'required|integer|min:1',
        'proposal_number' => 'required|string|max:50',
        'created_date' => 'required|date',
        'receipt_ids' => 'required|array',
        'receipt_ids.*' => 'string',
    ]);

    DB::beginTransaction();
    try {
        // 1. สร้าง ID ในรูปแบบ MTTT-TTCA-{Ma_Vudautu}-{ID}
        $lastId = DB::table('tb_phieu_trinh_thanh_toan')->max('id') ?? 0;
        $newId = $lastId + 1;
        $codeId = str_pad($newId, 6, '0', STR_PAD_LEFT);
        $maTrinh = "PTTT-TTCA-{$request->investment_project}-{$codeId}";
        
        // 2. สร้างชื่อเรื่อง (Tiêu đề) ในรูปแบบ TIEUDE-MTTT-TTCA-{Ma_Vudautu}-{ID}
        $tieuDe = "PTTT-{$request->payment_type}-{$request->investment_project}-{$codeId}";
        
        // 3. กำหนดค่าเริ่มต้นของยอดเงินรวมเป็น 0 ก่อน
        $totalAmount = 0;
        
        // 4. สร้างเอกสารขอเบิกเงิน - แก้ไขชื่อ field ให้ตรงกับโครงสร้าง SQL
        $paymentRequest = PaymentRequest::create([
            'ma_trinh_thanh_toan' => $maTrinh,
            'tieu_de' => $tieuDe,
            'vu_dau_tu' => $request->investment_project,
            'loai_thanh_toan' => $request->payment_type,
            'trang_thai_thanh_toan' => 'processing', // ใช้ field trang_thai_thanh_toan
            'so_to_trinh' => $request->proposal_number,
            'ngay_tao' => $request->created_date,
            'tong_tien_thanh_toan' => $totalAmount,
            'so_dot_thanh_toan' => $request->payment_installment,
        ]);
        
        // 5. สร้างประวัติ (logs) สำหรับแต่ละรายการที่เลือก
        foreach ($request->receipt_ids as $receiptId) {
            // บันทึกความสัมพันธ์ระหว่างใบเบิกเงินและรายการที่เบิก
            PaymentRequestLog::create([
                'ma_trinh_thanh_toan' => $maTrinh,
                'ma_nghiem_thu' => $receiptId,
            ]);
        }

        // 6. คำนวณยอดเงินรวมจาก receipt_ids
        $totalAmount = $this->calculateTotalAmount($request->receipt_ids);
        
        // 7. อัปเดตยอดเงินรวมในเอกสารขอเบิกเงิน
        $paymentRequest->tong_tien_thanh_toan = $totalAmount;
        $paymentRequest->save();

        // บันทึกประวัติการดำเนินการสร้างใบเบิกเงิน
        PaymentRequestAction::create([
            'ma_trinh_thanh_toan' => $maTrinh,
            'action' => 'processing', // สถานะเริ่มต้น
            'action_by' => Auth::id(),
            'action_date' => now(),
            'comments' => 'Đã tạo hồ sơ trình thanh toán'
        ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment request created successfully',
            'payment_request_id' => $maTrinh
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating payment request: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error creating payment request: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * คำนวณยอดเงินรวมจาก receipt_ids
     */
    private function calculateTotalAmount(array $receiptIds)
    {
        // เปลี่ยนชื่อตารางให้ตรงกับที่ใช้จริง - อาจจะต้องปรับตามชื่อตารางที่ถูกต้อง
        $totalAmount = DB::table('tb_bien_ban_nghiemthu_dv')
            ->whereIn('ma_nghiem_thu', $receiptIds)
            ->sum('tong_tien');
            
        return $totalAmount;
    }

    /**
     * ดึงรายการเอกสารขอเบิกเงินทั้งหมด
     */
    public function index()
{
    try {
        // Query payment requests with their latest action data
        $paymentRequests = DB::table('tb_phieu_trinh_thanh_toan as pr')
            ->leftJoin(DB::raw('(
                SELECT DISTINCT
                    ma_trinh_thanh_toan,
                    FIRST_VALUE(action_by) OVER (PARTITION BY ma_trinh_thanh_toan ORDER BY created_at DESC) as latest_action_by
                FROM Action_phieu_trinh_thanh_toan
            ) as latest_actions'), 'latest_actions.ma_trinh_thanh_toan', '=', 'pr.ma_trinh_thanh_toan')
            ->leftJoin('users', 'latest_actions.latest_action_by', '=', 'users.id')
            ->select(
                'pr.*', 
                'latest_actions.latest_action_by as action_by',
                'users.full_name as action_by_name'
            )
            ->orderBy('pr.id', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $paymentRequests
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching payment requests: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error fetching payment requests: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * ดึงรายละเอียดของเอกสารขอเบิกเงิน
     */
    public function show($id)
    {
        try {
            // Get the main payment request record
            $document = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->first();
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Get the creator information by finding the 'processing' action
            $creatorInfo = DB::table('Action_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->where('action', 'processing')
                ->join('users', 'Action_phieu_trinh_thanh_toan.action_by', '=', 'users.id')
                ->select('users.full_name')
                ->first();
            
            // Get all receipt IDs associated with this payment request from logs
            $receiptLogs = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->select('ma_nghiem_thu', 'ma_de_nghi_giai_ngan') // Select both receipt ID and disbursement code
                ->get();
            
            $receiptIds = $receiptLogs->pluck('ma_nghiem_thu')->toArray();
            
            // Create a mapping of receipt IDs to disbursement codes
            $disbursementCodeMap = [];
            foreach ($receiptLogs as $log) {
                $disbursementCodeMap[$log->ma_nghiem_thu] = $log->ma_de_nghi_giai_ngan;
            }
            
            // Get detailed information for each receipt from tb_bien_ban_nghiemthu_dv
$paymentDetails = DB::table('tb_bien_ban_nghiemthu_dv as bb')
    ->join('Logs_phieu_trinh_thanh_toan as logs', 'logs.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
    ->join('tb_phieu_trinh_thanh_toan as pt', 'pt.ma_trinh_thanh_toan', '=', 'logs.ma_trinh_thanh_toan')
    ->where('logs.ma_trinh_thanh_toan', $id)
    ->whereIn('bb.ma_nghiem_thu', $receiptIds)
    ->select(
        'bb.ma_nghiem_thu as document_code',
        'bb.tieu_de as title',
        'bb.vu_dau_tu as investment_project',
        'bb.khach_hang_ca_nhan_dt_mia',
        'bb.khach_hang_doanh_nghiep_dt_mia',
        'bb.hop_dong_dau_tu_mia as contract_number',
        'bb.hinh_thuc_thuc_hien_dv as service_type',
        'bb.hop_dong_cung_ung_dich_vu',
        'bb.tram',
        'bb.tong_tien as amount',
        'pt.so_dot_thanh_toan as installment' // ดึงจากตาราง tb_phieu_trinh_thanh_toan
    )
    ->get();

            
            // Map the payment details and add the disbursement_code from our mapping
            $mappedPaymentDetails = $paymentDetails->map(function($item) use ($disbursementCodeMap) {
                $item->disbursement_code = $disbursementCodeMap[$item->document_code] ?? '';
               
                return $item;
            });
            
            // Get processing history/logs from the Action table
            $processingHistory = DB::table('Action_phieu_trinh_thanh_toan')
                ->select(
                    'id',
                    'ma_trinh_thanh_toan',
                    'action',
                    'comments as note',
                    'created_at',
                    'action_by' // Keep the action_by field for reference
                )
                ->where('ma_trinh_thanh_toan', $id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'document' => [
                    'payment_code' => $document->ma_trinh_thanh_toan,
                    'title' => $document->tieu_de,
                    'investment_project' => $document->vu_dau_tu,
                    'payment_type' => $document->loai_thanh_toan,
                    'status' => $document->trang_thai_thanh_toan,
                    'payment_installment' => $document->so_dot_thanh_toan,
                    'proposal_number' => $document->so_to_trinh,
                    'created_at' => $document->ngay_tao,
                    'payment_date' => $document->ngay_thanh_toan, // Add this line
                    'total_amount' => $document->tong_tien_thanh_toan,
                    'creator_name' => $creatorInfo ? $creatorInfo->full_name : 'Unknown',
                    'notes' => $document->note, // Add notes field
                    // Add new financial fields
    'total_hold_amount' => $document->tong_tien_tam_giu,
    'total_deduction' => $document->tong_tien_khau_tru,
    'total_interest' => $document->tong_tien_lai_suat,
    'total_remaining' => $document->tong_tien_thanh_toan_con_lai,
                ],
                'paymentDetails' => $mappedPaymentDetails,
                'processingHistory' => $processingHistory
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching payment request details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * อัพเดตสถานะของเอกสารขอเบิกเงิน
     *
     * @param  Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate the request
            $validationRules = [
                'status' => 'required|string|in:processing,submitted,paid,cancelled',
                'action_notes' => 'nullable|string|max:255',
                'tong_tien' => 'nullable|numeric', // Tổng tiền thanh toán
                'tong_tien_tam_giu' => 'nullable|numeric', // Tổng tiền tạm giữ
                'tong_tien_khau_tru' => 'nullable|numeric', // Tổng tiền khấu trừ
                'tong_tien_lai_suat' => 'nullable|numeric', // Tổng tiền lãi suất
                'tong_tien_thanh_toan_con_lai' => 'nullable|numeric', // Tổng tiền thanh toán còn lại
            ];
            
            // Add payment_date validation if status is 'paid'
            if ($request->input('status') === 'paid') {
                $validationRules['payment_date'] = 'required|date';
            }
            
            $validated = $request->validate($validationRules);
    
            // Find the payment request
            $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->first();
    
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy phiếu trình thanh toán',
                ], 404);
            }
    
            // Create update data array
            $updateData = [
                'trang_thai_thanh_toan' => $validated['status']
            ];
            
            // Add payment_date to update data if status is 'paid'
            if ($validated['status'] === 'paid' && isset($validated['payment_date'])) {
                $updateData['ngay_thanh_toan'] = $validated['payment_date'];
            }
            
            // Add financial fields
            if (isset($validated['tong_tien'])) {
                $updateData['tong_tien_thanh_toan'] = $validated['tong_tien'];
            }
            
            if (isset($validated['tong_tien_tam_giu'])) {
                $updateData['tong_tien_tam_giu'] = $validated['tong_tien_tam_giu'];
            }
            
            if (isset($validated['tong_tien_khau_tru'])) {
                $updateData['tong_tien_khau_tru'] = $validated['tong_tien_khau_tru'];
            }
            
            if (isset($validated['tong_tien_lai_suat'])) {
                $updateData['tong_tien_lai_suat'] = $validated['tong_tien_lai_suat'];
            }
            
            if (isset($validated['tong_tien_thanh_toan_con_lai'])) {
                $updateData['tong_tien_thanh_toan_con_lai'] = $validated['tong_tien_thanh_toan_con_lai'];
            }
    
            // Update the payment request status and financial fields
            DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->update($updateData);
    
            // Record the action in the action table
           DB::table('Action_phieu_trinh_thanh_toan')->insert([
    'ma_trinh_thanh_toan' => $id,
    'action' => $validated['status'],
    'action_by' => auth()->id(),
    'action_date' => $validated['status'] === 'paid' && isset($validated['payment_date']) 
                    ? $validated['payment_date'] 
                    : now(),
    'comments' => $validated['action_notes'] ?? null,
    'created_at' => now(),
    'updated_at' => now()
]);
            
            // NEW CODE: Update the status in tb_de_nghi_thanhtoan_dv table
            // Get all disbursement codes associated with this payment request
            $disbursementCodes = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->whereNotNull('ma_de_nghi_giai_ngan')
                ->distinct()
                ->pluck('ma_de_nghi_giai_ngan')
                ->toArray();
                
            // Update status for all related disbursement requests
            if (!empty($disbursementCodes)) {
                $disbursementUpdateData = [
                    'trang_thai_thanh_toan' => $validated['status']
                ];
                
                // If status is paid, update payment date as well
                if ($validated['status'] === 'paid' && isset($validated['payment_date'])) {
                    $disbursementUpdateData['ngay_thanh_toan'] = $validated['payment_date'];
                }
                
                DB::table('tb_de_nghi_thanhtoan_dv')
                    ->whereIn('ma_giai_ngan', $disbursementCodes)
                    ->update($disbursementUpdateData);
            }
            
            // อัปเดตข้อมูลทางการเงินสำหรับเอกสารการจ่ายเงินทั้งหมด
            // โดยส่งข้อมูลไปยัง API endpoint ของ PhieudenghithanhtoandvControllers
            $financialData = [
                'tong_tien' => $validated['tong_tien'] ?? null,
                'tong_tien_tam_giu' => $validated['tong_tien_tam_giu'] ?? null,
                'tong_tien_khau_tru' => $validated['tong_tien_khau_tru'] ?? null,
                'tong_tien_lai_suat' => $validated['tong_tien_lai_suat'] ?? null,
                'tong_tien_thanh_toan_con_lai' => $validated['tong_tien_thanh_toan_con_lai'] ?? null,
            ];
            
            // ถ้าสถานะเป็น 'paid' ให้อัปเดต payment_date ด้วย
            if ($validated['status'] === 'paid' && isset($validated['payment_date'])) {
                $financialData['payment_date'] = $validated['payment_date'];
            }
            
            // เรียกใช้ PhieudenghithanhtoandvControllers ผ่าน Dependency Injection หรือสร้าง instance ของ controller
            $disbursementController = app()->make('App\Http\Controllers\QuanlyTaichinh\PhieudenghithanhtoandvControllers');
            $updateFinancialRequest = new Request([
                'payment_code' => $id,
                'financial_data' => $financialData
            ]);
            $disbursementController->updateFinancialData($updateFinancialRequest);
    
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái phiếu trình thanh toán thành công',
                'status' => $validated['status'],
                'payment_date' => $validated['status'] === 'paid' ? $validated['payment_date'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating payment request status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment request status: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * ดึงข้อมูลโครงการลงทุน (Vụ đầu tư)
     */
    public function getInvestmentProjects()
    {
        try {
            // ปรับตามชื่อตารางที่เก็บข้อมูลโครงการลงทุน
            $projects = DB::table('tb_vudautu')
                ->select('Ma_Vudautu', 'Ten_Vudautu')
                ->orderBy('Ten_Vudautu')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching investment projects: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching investment projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * อัพเดตรายการ receipt records เพื่อแก้ไขข้อมูล ma_de_nghi_giai_ngan
     */
    public function updateRecords(Request $request, $id)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string',
            'disbursement_code' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Check if payment request exists
            $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Update ONLY the ma_de_nghi_giai_ngan field in the logs table
            $affected = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->whereIn('ma_nghiem_thu', $request->receipt_ids)
                ->update(['ma_de_nghi_giai_ngan' => $request->disbursement_code]);
            
            // ลบการบันทึกประวัติการดำเนินการในตาราง Action
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Records updated successfully',
                'affected_rows' => $affected
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment request records: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ลบรายการ receipt records จากตาราง Logs_phieu_trinh_thanh_toan
     */
    public function deleteRecords(Request $request, $id)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            // Check if payment request exists
            $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Delete records from the logs table based on ma_nghiem_thu
            $affected = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->whereIn('ma_nghiem_thu', $request->receipt_ids)
                ->delete();
            
            // Recalculate total amount after deletion
            $remainingReceipts = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->pluck('ma_nghiem_thu')
                ->toArray();
            
            $newTotalAmount = $this->calculateTotalAmount($remainingReceipts);
            
            // Update the payment request with new total amount
            $paymentRequest->tong_tien_thanh_toan = $newTotalAmount;
            $paymentRequest->save();
            
            // ลบการบันทึกประวัติการดำเนินการในตาราง Action
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Records deleted successfully',
                'affected_rows' => $affected,
                'new_total_amount' => $newTotalAmount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting payment request records: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add receipts to a payment request
     */
    public function addReceipts(Request $request, $id)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            // Check if payment request exists
            $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Get receipt details to calculate total amount
            $receipts = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $request->receipt_ids)
                ->select(
                    'ma_nghiem_thu',
                    'tong_tien_thanh_toan'
                )
                ->get();
                
            $receiptMap = [];
            foreach ($receipts as $receipt) {
                $receiptMap[$receipt->ma_nghiem_thu] = $receipt->tong_tien_thanh_toan;
            }
            
            // Add records to logs table
            $logsData = [];
            foreach ($request->receipt_ids as $receiptId) {
                $logsData[] = [
                    'ma_trinh_thanh_toan' => $id,
                    'ma_nghiem_thu' => $receiptId,
                    'ma_de_nghi_giai_ngan' => null, // Will be updated later
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // Insert new logs
            DB::table('Logs_phieu_trinh_thanh_toan')->insert($logsData);
            
            // Update total amount for payment request
            $totalAmount = $paymentRequest->tong_tien_thanh_toan;
            foreach ($request->receipt_ids as $receiptId) {
                $totalAmount += $receiptMap[$receiptId] ?? 0;
            }
            
            $paymentRequest->tong_tien_thanh_toan = $totalAmount;
            $paymentRequest->save();
            
           
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Receipts added successfully',
                'new_total_amount' => $totalAmount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding receipts to payment request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error adding receipts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import data from CSV/Excel to update payment details
     */
    public function importData(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);

        try {
            // Check if payment request exists
            $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            $file = $request->file('file');
            $filePath = $file->getPathname();
            $extension = $file->getClientOriginalExtension();
            
            // Process the file based on its extension
            if ($extension == 'csv') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            }
            
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Get headers from first row
            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is empty or does not contain data rows',
                    'errors' => ['Please check the file format and try again.']
                ], 422);
            }
            
            $headers = array_map('trim', $rows[0]);
            
            // Find indices of required columns
            $maIndex = array_search('ma_nghiem_thu', array_map('strtolower', $headers));
            $maGiaiNganIndex = array_search('ma_de_nghi_giai_ngan', array_map('strtolower', $headers));
            
            // Alternative column names that might be used
            if ($maIndex === false) {
                $maIndex = array_search('ma nghiem thu', array_map('strtolower', $headers)) !== false ? 
                           array_search('ma nghiem thu', array_map('strtolower', $headers)) : 
                           array_search('ma_nghiem_thu', array_map('strtolower', $headers));
            }
            
            if ($maGiaiNganIndex === false) {
                $maGiaiNganIndex = array_search('ma giai ngan', array_map('strtolower', $headers)) !== false ? 
                                   array_search('ma giai ngan', array_map('strtolower', $headers)) : 
                                   array_search('ma de nghi giai ngan', array_map('strtolower', $headers));
            }
            
            if ($maIndex === false || $maGiaiNganIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required columns are missing',
                    'errors' => ['File must contain columns for ma_nghiem_thu and ma_de_nghi_giai_ngan.']
                ], 422);
            }
            
            // Skip header row
            array_shift($rows);
            
            $updatedCount = 0;
            $errorRows = [];
            $errors = [];
            
            // Process rows
            DB::beginTransaction();
            try {
                foreach ($rows as $index => $row) {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    $maValue = trim($row[$maIndex] ?? '');
                    $maGiaiNganValue = trim($row[$maGiaiNganIndex] ?? '');
                    
                    if (empty($maValue)) {
                        $errorRows[] = $index + 2; // +2 for 1-indexed and header row
                        continue;
                    }
                    
                    // Update the record in Logs_phieu_trinh_thanh_toan
                    $updated = DB::table('Logs_phieu_trinh_thanh_toan')
                        ->where('ma_trinh_thanh_toan', $id)
                        ->where('ma_nghiem_thu', $maValue)
                        ->update(['ma_de_nghi_giai_ngan' => $maGiaiNganValue]);
                    
                    if ($updated) {
                        $updatedCount++;
                    } else {
                        $errorRows[] = $index + 2;
                    }
                }
                
                if (!empty($errorRows)) {
                    $errors[] = 'Could not update rows: ' . implode(', ', $errorRows) . '. These rows may not exist in the payment request.';
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Successfully updated {$updatedCount} records" . 
                                (!empty($errors) ? " with {$errors} warnings" : ""),
                    'updated_count' => $updatedCount,
                    'errors' => $errors
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error importing payment request data: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing import file',
                    'errors' => [$e->getMessage()]
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in import process: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing import file',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

public function updateBasicInfo(Request $request, $id)
{
    try {
        // Validate the request data - เพิ่ม ngay_thanh_toan
        $validated = $request->validate([
            'so_to_trinh' => 'nullable|string|max:100', // Số tờ trình
            'ngay_tao' => 'nullable|date', // Ngày tạo
            'so_dot_thanh_toan' => 'nullable|integer|min:1', // Số đợt thanh toán
            'loai_thanh_toan' => 'nullable|string|max:100', // Loại thanh toán
            'vu_dau_tu' => 'nullable|string|max:100', // Vụ đầu tư
            'ngay_thanh_toan' => 'nullable|date', // Ngày thanh toán - เพิ่มบรรทัดนี้
            'tong_tien' => 'nullable|numeric', // Tổng tiền thanh toán
            'tong_tien_tam_giu' => 'nullable|numeric', // Tổng tiền tạm giữ
            'tong_tien_khau_tru' => 'nullable|numeric', // Tổng tiền khấu trừ
            'tong_tien_lai_suat' => 'nullable|numeric', // Tổng tiền lãi suất
            'tong_tien_thanh_toan_con_lai' => 'nullable|numeric', // Tổng tiền thanh toán còn lại
        ]);

        // Find the payment request record
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu trình thanh toán'
            ], 404);
        }

        // Prepare update data
        $updateData = [];
        
        if (isset($validated['so_to_trinh'])) {
            $updateData['so_to_trinh'] = $validated['so_to_trinh'];
        }
        
        if (isset($validated['ngay_tao'])) {
            $updateData['ngay_tao'] = $validated['ngay_tao'];
        }
        
        if (isset($validated['so_dot_thanh_toan'])) {
            $updateData['so_dot_thanh_toan'] = $validated['so_dot_thanh_toan'];
        }
        
        if (isset($validated['loai_thanh_toan'])) {
            $updateData['loai_thanh_toan'] = $validated['loai_thanh_toan'];
        }
        
        if (isset($validated['vu_dau_tu'])) {
            $updateData['vu_dau_tu'] = $validated['vu_dau_tu'];
        }
        
        if (isset($validated['ngay_thanh_toan'])) {
            $updateData['ngay_thanh_toan'] = $validated['ngay_thanh_toan'];
        }
        
        // Add new financial fields to update data
        if (isset($validated['tong_tien'])) {
            $updateData['tong_tien_thanh_toan'] = $validated['tong_tien'];
        }
        
        if (isset($validated['tong_tien_tam_giu'])) {
            $updateData['tong_tien_tam_giu'] = $validated['tong_tien_tam_giu'];
        }
        
        if (isset($validated['tong_tien_khau_tru'])) {
            $updateData['tong_tien_khau_tru'] = $validated['tong_tien_khau_tru'];
        }
        
        if (isset($validated['tong_tien_lai_suat'])) {
            $updateData['tong_tien_lai_suat'] = $validated['tong_tien_lai_suat'];
        }
        
        if (isset($validated['tong_tien_thanh_toan_con_lai'])) {
            $updateData['tong_tien_thanh_toan_con_lai'] = $validated['tong_tien_thanh_toan_con_lai'];
        }

        // Update the record if we have data to update
        if (!empty($updateData)) {
            DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->update($updateData);
            
            // NEW CODE: Update the disbursement requests with payment date sync
            // Get all disbursement codes associated with this payment request
            $disbursementCodes = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->whereNotNull('ma_de_nghi_giai_ngan')
                ->distinct()
                ->pluck('ma_de_nghi_giai_ngan')
                ->toArray();
                
            // Create update data for disbursement records
            $disbursementUpdateData = [];
            
            // Add payment date if available - ซิงค์วันที่ thanh toán
            if (isset($validated['ngay_thanh_toan'])) {
                $disbursementUpdateData['ngay_thanh_toan'] = $validated['ngay_thanh_toan'];
            }
            
            // Transfer current status from parent request if needed
            if (!empty($paymentRequest->trang_thai_thanh_toan)) {
                $disbursementUpdateData['trang_thai_thanh_toan'] = $paymentRequest->trang_thai_thanh_toan;
            }
            
            // Update disbursement records if we have data
            if (!empty($disbursementUpdateData) && !empty($disbursementCodes)) {
                DB::table('tb_de_nghi_thanhtoan_dv')
                    ->whereIn('ma_giai_ngan', $disbursementCodes)
                    ->update($disbursementUpdateData);
            }
            
            // Prepare financial data for disbursement requests
            $financialData = [
                'tong_tien' => $validated['tong_tien'] ?? null,
                'tong_tien_tam_giu' => $validated['tong_tien_tam_giu'] ?? null,
                'tong_tien_khau_tru' => $validated['tong_tien_khau_tru'] ?? null,
                'tong_tien_lai_suat' => $validated['tong_tien_lai_suat'] ?? null,
                'tong_tien_thanh_toan_con_lai' => $validated['tong_tien_thanh_toan_con_lai'] ?? null,
            ];
            
            // Add payment date if available
            if (isset($validated['ngay_thanh_toan'])) {
                $financialData['payment_date'] = $validated['ngay_thanh_toan'];
            }
            
            // Update financial information for all disbursement requests
            try {
                $disbursementController = app()->make('App\Http\Controllers\QuanlyTaichinh\PhieudenghithanhtoandvControllers');
                $updateFinancialRequest = new Request([
                    'payment_code' => $id,
                    'financial_data' => $financialData
                ]);
                $disbursementController->updateFinancialData($updateFinancialRequest);
            } catch (\Exception $e) {
                Log::warning('Failed to update financial data via controller: ' . $e->getMessage());
            }
        }

        // Get the updated record
        $updatedRecord = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin phiếu trình thanh toán thành công',
            'data' => $updatedRecord,
            'updated_disbursements_count' => count($disbursementCodes ?? [])
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating payment request basic info: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật phiếu trình thanh toán: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Delete a payment request
 * 
 * @param string $id Payment request code
 * @return \Illuminate\Http\JsonResponse
 */

public function destroy($id)
{
    try {
        DB::beginTransaction();
        
        // Find the payment request
        $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Check if payment request is in paid status - cannot delete if paid
        if ($paymentRequest->trang_thai_thanh_toan === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a paid payment request'
            ], 422);
        }
        
        // 1. Delete related logs first
        DB::table('Logs_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 2. Delete action records from Action_phieu_trinh_thanh_toan table
        DB::table('Action_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 3. Delete related payment requests from tb_de_nghi_thanhtoan_dv
        DB::table('tb_de_nghi_thanhtoan_dv')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 4. Finally delete the payment request itself
        $paymentRequest->delete();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment request deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error deleting payment request: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error deleting payment request',
            'error' => $e->getMessage()
        ], 500);
    }
}

//Timeline giải ngân
public function getProcessingHistory($paymentCode)
{
    try {
        $history = DB::table('Action_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $paymentCode)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching processing history',
            'error' => $e->getMessage()
        ], 500);
    }
}

//Time line giải ngân trang DNTTDV
public function getDisbursementProcessingHistory($id)
{
    try {
        // First, get the ma_trinh_thanh_toan from the disbursement request
        $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_dv')
            ->where('ma_giai_ngan', $id)
            ->first();
        
        if (!$disbursementRequest || !$disbursementRequest->ma_trinh_thanh_toan) {
            return response()->json([
                'success' => false,
                'message' => 'Disbursement request not found or has no associated payment request'
            ], 404);
        }
        
        // Now fetch the history using the ma_trinh_thanh_toan
        $history = DB::table('Action_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $disbursementRequest->ma_trinh_thanh_toan)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'history' => $history,
            'payment_code' => $disbursementRequest->ma_trinh_thanh_toan
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching disbursement processing history: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching processing history',
            'error' => $e->getMessage()
        ], 500);
    }
}

// ...existing code...

/**
 * Save note for payment request
 * 
 * @param Request $request
 * @param string $id Payment request code
 * @return \Illuminate\Http\JsonResponse
 */
public function saveNote(Request $request, $id)
{
    try {
        // Validate the request
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000' // Allow up to 1000 characters for note
        ]);

        // Find the payment request
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu trình thanh toán'
            ], 404);
        }

        // Update the note field
        $affected = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->update([
                'note' => $validated['note']
            ]);

        if ($affected) {
            return response()->json([
                'success' => true,
                'message' => 'Ghi chú đã được cập nhật thành công',
                'note' => $validated['note']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật ghi chú'
            ], 500);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error saving payment request note: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lưu ghi chú: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Search individual customers (farmers)
 */
public function searchIndividualCustomers(Request $request)
{
    try {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        $customers = DB::table('user_farmer')
            ->where('khach_hang_ca_nhan', 'LIKE', '%' . $query . '%')
            ->whereNotNull('khach_hang_ca_nhan')
            ->where('khach_hang_ca_nhan', '!=', '')
            ->select('id', 'khach_hang_ca_nhan', 'ma_kh_ca_nhan')
            ->distinct()
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error searching individual customers: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error searching customers: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Search corporate customers (farmers)
 */
public function searchCorporateCustomers(Request $request)
{
    try {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        $customers = DB::table('user_farmer')
            ->where('khach_hang_doanh_nghiep', 'LIKE', '%' . $query . '%')
            ->whereNotNull('khach_hang_doanh_nghiep')
            ->where('khach_hang_doanh_nghiep', '!=', '')
            ->select('id', 'khach_hang_doanh_nghiep', 'ma_kh_doanh_nghiep')
            ->distinct()
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error searching corporate customers: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error searching customers: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get customer details by name
 */
public function getCustomerByName(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|in:individual,corporate'
        ]);
        
        $name = $request->get('name');
        $type = $request->get('type');
        
        if ($type === 'individual') {
            $customer = DB::table('user_farmer')
                ->where('khach_hang_ca_nhan', $name)
                ->select('id', 'khach_hang_ca_nhan', 'ma_kh_ca_nhan')
                ->first();
        } else {
            $customer = DB::table('user_farmer')
                ->where('khach_hang_doanh_nghiep', $name)
                ->select('id', 'khach_hang_doanh_nghiep', 'ma_kh_doanh_nghiep')
                ->first();
        }
        
        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error getting customer by name: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error getting customer: ' . $e->getMessage()
        ], 500);
    }
}


}