<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\ActionPhieutrinhTTHomgiong;
use App\Models\Quanlytaichinh\LogPhieutrinhTTHomgiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhieutrinhthanhtoanHomgiongControllers extends Controller
{
    /**
     * ดึงรายการเอกสารขอเบิกเงินทั้งหมด
     */
    public function index()
    {
        try {
            // Query payment requests with their latest action data
            $paymentRequests = DB::table('tb_phieu_trinh_thanh_toan_homgiong as pr')
                ->leftJoin(DB::raw('(
                    SELECT DISTINCT
                        ma_trinh_thanh_toan,
                        FIRST_VALUE(action_by) OVER (PARTITION BY ma_trinh_thanh_toan ORDER BY created_at DESC) as latest_action_by
                    FROM action_phieu_trinh_thanh_toan_homgiong
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
            
            $duplicates = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                ->whereIn('ma_so_phieu', $receiptIds)
                ->select('ma_so_phieu', 'ma_trinh_thanh_toan')
                ->get();
            
            return response()->json([
                'duplicates' => $duplicates
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment request homgiong duplicates: ' . $e->getMessage());
            return response()->json([
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
            // 1. สร้าง ID ในรูปแบบ PTTTHG-TTCA-{Ma_Vudautu}-{ID}
            $lastId = DB::table('tb_phieu_trinh_thanh_toan_homgiong')->max('id') ?? 0;
            $newId = $lastId + 1;
            $codeId = str_pad($newId, 6, '0', STR_PAD_LEFT);
            $maTrinh = "PTTTHG-TTCA-{$request->investment_project}-{$codeId}";
            
            // 2. สร้างชื่อเรื่อง (Tiêu đề) ในรูปแบบ PTTTHG-MTTT-TTCA-{Ma_Vudautu}-{ID}
            $tieuDe = "PTTTHG-{$request->payment_type}-{$request->investment_project}-{$codeId}";
            
            // 3. กำหนดค่าเริ่มต้นของยอดเงินรวมเป็น 0 ก่อน
            $totalAmount = 0;
            
            // 4. สร้างเอกสารขอเบิกเงิน
            $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')->insertGetId([
                'ma_trinh_thanh_toan' => $maTrinh,
                'tieu_de' => $tieuDe,
                'vu_dau_tu' => $request->investment_project,
                'loai_thanh_toan' => $request->payment_type,
                'trang_thai_thanh_toan' => 'processing',
                'so_to_trinh' => $request->proposal_number,
                'ngay_tao' => $request->created_date,
                'tong_tien_thanh_toan' => $totalAmount,
                'so_dot_thanh_toan' => $request->payment_installment,
             
            ]);
            
            // 5. สร้างประวัติ (logs) สำหรับแต่ละรายการที่เลือก
            foreach ($request->receipt_ids as $receiptId) {
                LogPhieutrinhTTHomgiong::create([
                    'ma_trinh_thanh_toan' => $maTrinh,
                    'ma_so_phieu' => $receiptId,
                    'ma_de_nghi_giai_ngan' => null,
                ]);
            }

            // 6. คำนวณยอดเงินรวมจาก receipt_ids
            $totalAmount = $this->calculateTotalAmount($request->receipt_ids);
            
            // 7. อัปเดตยอดเงินรวมในเอกสารขอเบิกเงิน
            DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                ->where('ma_trinh_thanh_toan', $maTrinh)
                ->update(['tong_tien_thanh_toan' => $totalAmount]);

            // บันทึกประวัติการดำเนินการสร้างใบเบิกเงิน
            ActionPhieutrinhTTHomgiong::create([
                'ma_trinh_thanh_toan' => $maTrinh,
                'action' => 'processing',
                'action_by' => Auth::id(),
                'action_date' => now(),
                'comments' => 'Đã tạo hồ sơ trình thanh toán hom giống'
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request created successfully',
                'payment_request_id' => $maTrinh
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment request homgiong: ' . $e->getMessage());
            
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
        $totalAmount = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->sum('tong_tien');
            
        return $totalAmount;
    }
}