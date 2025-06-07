<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\PhieudenghithanhtoanHomgiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PhieuDNTTHomgiongControllers extends Controller
{
    /**
     * Add new payment request with multiple receipts assignment for Hom Giong
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addWithReceipts(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'payment_request_data' => 'required|array',
            'payment_request_data.ma_giai_ngan' => 'required|string|max:255',
            'payment_request_data.ma_trinh_thanh_toan' => 'required|string|max:255',
            'payment_request_data.vu_dau_tu' => 'required|string|max:255',
            'payment_request_data.loai_thanh_toan' => 'required|string|max:255',
            'receipt_ids' => 'required|array|min:1',
            'receipt_ids.*' => 'string'
        ]);
        
        $paymentRequestData = $request->input('payment_request_data');
        $receiptIds = $request->input('receipt_ids');
        
        DB::beginTransaction();
        
        // Check if ma_giai_ngan already exists
        $existingRequest = PhieudenghithanhtoanHomgiong::where('ma_giai_ngan', $paymentRequestData['ma_giai_ngan'])->first();
        if ($existingRequest) {
            throw new \Exception('Mã giải ngân đã tồn tại trong hệ thống');
        }
        
        // Calculate total amount and thuc_nhan from selected receipts
        $totals = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->select(
                DB::raw('SUM(tong_tien) as total_amount'),
                DB::raw('SUM(tong_thuc_nhan) as total_thuc_nhan')
            )
            ->first();
        
        $totalAmount = $totals->total_amount ?? 0;
        $totalThucNhan = $totals->total_thuc_nhan ?? 0;
        
        // Set default financial values
        $totalHoldAmount = 0; // Tổng tiền tạm giữ - ค่าเริ่มต้น 0
        $totalDeduction = 0;  // Tổng tiền khấu trừ - ค่าเริ่มต้น 0
        $totalInterest = 0;   // Tổng tiền lãi suất - ค่าเริ่มต้น 0
        
        // Calculate remaining amount: Tổng tiền - Tổng tiền tạm giữ - Tổng tiền khấu trừ - Tổng tiền lãi suất
        $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction - $totalInterest;
        
        // Prepare payment request data
        $createData = [
            'ma_giai_ngan' => $paymentRequestData['ma_giai_ngan'],
            'ma_trinh_thanh_toan' => $paymentRequestData['ma_trinh_thanh_toan'],
            'vu_dau_tu' => $paymentRequestData['vu_dau_tu'],
            'loai_thanh_toan' => $paymentRequestData['loai_thanh_toan'],
            'khach_hang_ca_nhan' => $paymentRequestData['khach_hang_ca_nhan'] ?? null,
            'ma_khach_hang_ca_nhan' => $paymentRequestData['ma_khach_hang_ca_nhan'] ?? null,
            'khach_hang_doanh_nghiep' => $paymentRequestData['khach_hang_doanh_nghiep'] ?? null,
            'ma_khach_hang_doanh_nghiep' => $paymentRequestData['ma_khach_hang_doanh_nghiep'] ?? null,
            'thuc_nhan' => $totalThucNhan,
            'tong_tien' => $totalAmount,
            'tong_tien_tam_giu' => $totalHoldAmount,      // เพิ่ม: ค่าเริ่มต้น 0
            'tong_tien_khau_tru' => $totalDeduction,      // เพิ่ม: ค่าเริ่มต้น 0
            'tong_tien_lai_suat' => $totalInterest,       // เพิ่ม: ค่าเริ่มต้น 0
            'tong_tien_thanh_toan_con_lai' => $totalRemaining, // เพิ่ม: คำนวณจากสูตร
            'trang_thai_thanh_toan' => 'processing',
        ];
        
        // Create the payment request
        $paymentRequest = PhieudenghithanhtoanHomgiong::create($createData);
        
        // Assign receipts to this payment request in logs table
        $logsData = [];
        foreach ($receiptIds as $receiptId) {
            $logsData[] = [
                'ma_trinh_thanh_toan' => $paymentRequestData['ma_trinh_thanh_toan'],
                'ma_so_phieu' => $receiptId,
                'ma_de_nghi_giai_ngan' => $paymentRequestData['ma_giai_ngan'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Insert logs
        DB::table('logs_phieu_trinh_thanh_toan_homgiong')->insert($logsData);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Phiếu đề nghị thanh toán hom giống được tạo thành công',
            'data' => $paymentRequest
        ], 201);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating payment request homgiong with receipts: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    
    /**
     * Get all payment requests for a specific payment request ID (for modal display)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPaymentRequest($paymentRequestId)
{
    try {
        // Get all disbursement requests for the given payment request with additional data
        $disbursementRequests = DB::table('tb_de_nghi_thanhtoan_homgiong as dnh')
            ->leftJoin('bien_ban_nghiem_thu_hom_giong as bbnt', function($join) use ($paymentRequestId) {
                $join->on('bbnt.ma_so_phieu', '=', DB::raw("(
                    SELECT ma_so_phieu 
                    FROM logs_phieu_trinh_thanh_toan_homgiong 
                    WHERE ma_trinh_thanh_toan = '{$paymentRequestId}' 
                    AND ma_de_nghi_giai_ngan = dnh.ma_giai_ngan 
                    LIMIT 1
                )"));
            })
            // Join with parent payment request to get so_to_trinh and so_dot_thanh_toan
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'pttt.ma_trinh_thanh_toan', '=', 'dnh.ma_trinh_thanh_toan')
            // Join with investment projects table to get project name
            ->leftJoin('tb_vudautu as vdt', 'vdt.Ma_Vudautu', '=', 'dnh.vu_dau_tu')
            ->where('dnh.ma_trinh_thanh_toan', $paymentRequestId)
            ->select(
                'dnh.ma_giai_ngan as disbursement_code',
                'dnh.vu_dau_tu as investment_project_code',
                'vdt.Ten_Vudautu as investment_project_name', // เพิ่มชื่อโครงการ
                'dnh.loai_thanh_toan as payment_type',
                'dnh.khach_hang_ca_nhan as individual_customer',
                'dnh.ma_khach_hang_ca_nhan as individual_customer_code',
                'dnh.khach_hang_doanh_nghiep as corporate_customer',
                'dnh.ma_khach_hang_doanh_nghiep as corporate_customer_code',
                'dnh.thuc_nhan as actual_received',
                'dnh.tong_tien as total_amount',
                'dnh.tong_tien_tam_giu as total_hold_amount',
                'dnh.tong_tien_khau_tru as total_deduction',
                'dnh.tong_tien_lai_suat as total_interest',
                'dnh.tong_tien_thanh_toan_con_lai as total_remaining',
                'dnh.ngay_thanh_toan as payment_date',
                'dnh.trang_thai_thanh_toan as payment_status',
                'dnh.comment',
                'bbnt.tram',
                // เพิ่มข้อมูลจาก parent payment request
                'pttt.so_to_trinh as proposal_number',
                'pttt.so_dot_thanh_toan as payment_installment'
            )
            ->orderBy('dnh.id', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $disbursementRequests
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching payment requests homgiong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching payment requests: ' . $e->getMessage()
        ], 500);
    }
}
}