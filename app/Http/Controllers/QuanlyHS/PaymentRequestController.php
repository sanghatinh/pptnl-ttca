<?php

namespace App\Http\Controllers\QuanlyHS;
use App\Http\Controllers\Controller; // Add this import
use App\Models\QuanlyHS\PaymentRequest;
use App\Models\QuanlyHS\PaymentRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\QuanlyHS\BienBanNghiemThu;
use App\Models\User;
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
                PaymentRequestLog::create([
                    'ma_trinh_thanh_toan' => $maTrinh,
                    'ma_nghiem_thu' => $receiptId,
                    'action' => 'processing', // สถานะเริ่มต้น
                    'action_by' => Auth::id(),
                    'action_date' => now(),
                    'comments' => 'Created payment request'
                ]);
            }
            
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
            
            // Get the creator information (assuming we have a creator ID field)
            $creatorInfo = null;
            if (isset($document->nguoi_tao)) {
                // Check if the users table exists and has the correct columns
                if (Schema::hasTable('users') && Schema::hasColumn('users', 'id')) {
                    $creatorInfo = DB::table('users')
                        ->where('id', $document->nguoi_tao)
                        ->first();
                }
            }
            
            // Get all receipt IDs associated with this payment request from logs
            $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $id)
                ->pluck('ma_nghiem_thu')
                ->toArray();
            
            // Get detailed information for each receipt from tb_bien_ban_nghiemthu_dv
            // Adding the requested additional columns
            $paymentDetails = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select(
                    'ma_nghiem_thu as document_code',
                    'tieu_de as title',
                    'vu_dau_tu as investment_project',
                    'khach_hang_ca_nhan_dt_mia', // Added as requested
                    'khach_hang_doanh_nghiep_dt_mia', // Added as requested
                    'hop_dong_dau_tu_mia as contract_number',
                    'hinh_thuc_thuc_hien_dv as service_type',
                    'hop_dong_cung_ung_dich_vu', // Added as requested 
                    'tram', // Added new tram column
                    'tong_tien as amount'
                )
                ->get();
            
            // Map the payment details and add a default empty value for disbursement_code
            $mappedPaymentDetails = $paymentDetails->map(function($item) {
                $item->disbursement_code = ''; // Add default empty value
                return $item;
            });
            
            // Get processing history/logs - WITHOUT joining the users table
            $processingHistory = DB::table('Logs_phieu_trinh_thanh_toan')
                ->select(
                    'id',
                    'ma_trinh_thanh_toan',
                    'ma_nghiem_thu',
                    'action',
                    'comments as note',
                    'created_at',
                    'action_by' // Keep the action_by field for reference
                )
                ->where('ma_trinh_thanh_toan', $id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Add a default user_name for each log entry
            $processingHistory = $processingHistory->map(function($log) {
                $log->user_name = "Người dùng #" . $log->action_by; // Default user name with ID
                return $log;
            });
            
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
                    'creator_name' => $creatorInfo ? ($creatorInfo->full_name ?? $creatorInfo->name ?? "Người dùng #" . $document->nguoi_tao) : 'Unknown',
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
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:processing,submitted,paid,cancelled',
            'comments' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $paymentRequest = PaymentRequest::where('ma_trinh_thanh_toan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // อัพเดตสถานะของเอกสารขอเบิกเงิน - แก้ไขชื่อ field ให้ตรงกับโครงสร้าง SQL
            $paymentRequest->trang_thai_thanh_toan = $request->status;
            $paymentRequest->save();
            
            // เพิ่มประวัติการดำเนินการ
            PaymentRequestLog::create([
                'ma_trinh_thanh_toan' => $id,
                'action' => $request->status,
                'action_by' => Auth::id(),
                'action_date' => now(),
                'comments' => $request->comments ?? 'Updated status to ' . $request->status
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request status updated successfully',
                'payment_request' => $paymentRequest
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment request status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment request status: ' . $e->getMessage()
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
}