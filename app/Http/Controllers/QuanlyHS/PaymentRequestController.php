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
            
            // 3. คำนวณยอดเงินรวมจาก receipt_ids
            $totalAmount = $this->calculateTotalAmount($request->receipt_ids);
            
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

            // บันทึกประวัติการดำเนินการสร้างใบเบิกเงิน
            PaymentRequestAction::create([
                'ma_trinh_thanh_toan' => $maTrinh,
                'action' => 'processing', // สถานะเริ่มต้น
                'action_by' => Auth::id(),
                'action_date' => now(),
                'comments' => 'Created payment request'
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
            ->sum('tong_tien_thanh_toan');
            
        return $totalAmount;
    }

    /**
     * ดึงรายการเอกสารขอเบิกเงินทั้งหมด
     */
    public function index()
    {
        try {
            $paymentRequests = PaymentRequest::orderBy('id', 'desc')->get();
            
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
            $paymentDetails = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select(
                    'ma_nghiem_thu as document_code',
                    'tieu_de as title',
                    'vu_dau_tu as investment_project',
                    'khach_hang_ca_nhan_dt_mia',
                    'khach_hang_doanh_nghiep_dt_mia',
                    'hop_dong_dau_tu_mia as contract_number',
                    'hinh_thuc_thuc_hien_dv as service_type',
                    'hop_dong_cung_ung_dich_vu',
                    'tram',
                    'tong_tien as amount'
                )
                ->get();
            
            // Map the payment details and add the disbursement_code from our mapping
            $mappedPaymentDetails = $paymentDetails->map(function($item) use ($disbursementCodeMap) {
                $item->disbursement_code = $disbursementCodeMap[$item->document_code] ?? '';
                $item->installment = 1; // Set default installment value
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
                    'total_amount' => $document->tong_tien_thanh_toan,
                    'creator_name' => $creatorInfo ? $creatorInfo->full_name : 'Unknown',
                    // 'notes' => $document->ghi_chu
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
            $validated = $request->validate([
                'status' => 'required|string|in:processing,submitted,paid,cancelled',
                'action_notes' => 'nullable|string|max:255',
            ]);
    
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
    
            // Update the payment request status - ลบฟิลด์ updated_at ออก
            DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->update([
                    'trang_thai_thanh_toan' => $validated['status'],
                    // ลบบรรทัด 'updated_at' => now(), เนื่องจากคอลัมน์นี้ไม่มีในตาราง
                ]);
    
            // Record the action in the action table
            DB::table('Action_phieu_trinh_thanh_toan')->insert([
                'ma_trinh_thanh_toan' => $id,
                'action' => $validated['status'],
                'action_by' => auth()->id(),
                'action_date' => now(),
                'comments' => $validated['action_notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái phiếu trình thanh toán thành công',
                'status' => $validated['status'],
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
        // Validate the request data
        $validated = $request->validate([
            'so_to_trinh' => 'nullable|string|max:100', // Số tờ trình
            'ngay_tao' => 'nullable|date', // Ngày tạo
            'so_dot_thanh_toan' => 'nullable|integer|min:1', // Số đợt thanh toán
            'loai_thanh_toan' => 'nullable|string|max:100', // Loại thanh toán
            'vu_dau_tu' => 'nullable|string|max:100', // Vụ đầu tư
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

        // Update the record if we have data to update
        if (!empty($updateData)) {
            DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->update($updateData);
            
            // Add an action record to track the update
            // Use 'processing' instead of 'update_info' as the action value
            // 'processing' is likely already an accepted value in the action column
            DB::table('Action_phieu_trinh_thanh_toan')->insert([
                'ma_trinh_thanh_toan' => $id,
                'action' => 'processing', // Changed from 'update_info' to 'processing'
                'action_by' => Auth::id(),
                'action_date' => now(),
                'comments' => 'Cập nhật thông tin cơ bản phiếu trình thanh toán',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Get the updated record
        $updatedRecord = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin phiếu trình thanh toán thành công',
            'data' => $updatedRecord
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
        
        // 3. Skip updating tb_bien_ban_nghiemthu_dv as the column doesn't exist
        Log::info("Skipping update of ma_trinh_thanh_toan in tb_bien_ban_nghiemthu_dv as the column doesn't exist");
        
        // 4. Skip deleting from tb_phieu_de_nghi_giai_ngan as the table doesn't exist
        Log::info("Skipping deletion from tb_phieu_de_nghi_giai_ngan as the table doesn't exist");
        
        // 5. Finally delete the payment request itself
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
}