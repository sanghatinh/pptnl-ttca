<?php
namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrintPhieuTrinhThanhtoanControllers extends Controller
{
    /**
     * Generate report data for payment request
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function generateReportPhieuTrinhTT(Request $request)
    {
        try {
            // Get payment code from request
            $paymentCode = $request->query('payment_code');
            
            if (empty($paymentCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment code is required'
                ], 400);
            }

            // Get payment request data
            $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentCode)
                ->first();
                
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            // Get payment request details (disbursements)
            $disbursements = DB::table('tb_de_nghi_thanhtoan_dv as dntd')
                ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'dntd.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
                ->leftJoin('tb_vudautu as vdt', 'vdt.Ma_Vudautu', '=', 'dntd.vu_dau_tu')
                ->where('dntd.ma_trinh_thanh_toan', $paymentCode)
                ->select(
                    'dntd.ma_giai_ngan as invoice_number',
                    'dntd.vu_dau_tu',
                    'vdt.Ten_Vudautu as ten_vu_dau_tu',
                    'dntd.loai_thanh_toan as loai_dau_tu',
                    'dntd.khach_hang_ca_nhan',
                    'dntd.ma_khach_hang_ca_nhan',
                    'dntd.khach_hang_doanh_nghiep',
                    'dntd.ma_khach_hang_doanh_nghiep',
                    'dntd.tong_tien',
                    'dntd.tong_tien_tam_giu',
                    'dntd.tong_tien_khau_tru',
                    'dntd.tong_tien_lai_suat',
                    'dntd.tong_tien_thanh_toan_con_lai',
                    'dntd.trang_thai_thanh_toan',
                    'dntd.ngay_thanh_toan',
                    'dntd.comment as noi_dung',
                    'pttt.so_to_trinh',
                    'pttt.so_dot_thanh_toan',
                    'pttt.ngay_tao as ngay_vay',
                    'pttt.loai_thanh_toan as payment_type'
                )
                ->orderBy('dntd.ma_giai_ngan', 'asc')
                ->get();

            // Format data for report
            $reportData = [];
            foreach ($disbursements as $item) {
                // Format customer info (prefer individual customer if available)
                $khachHang = !empty($item->khach_hang_ca_nhan) ? $item->khach_hang_ca_nhan : $item->khach_hang_doanh_nghiep;
                $maKhachHang = !empty($item->ma_khach_hang_ca_nhan) ? $item->ma_khach_hang_ca_nhan : $item->ma_khach_hang_doanh_nghiep;
                
                $reportData[] = [
                    'invoice_number' => $item->invoice_number,
                    'vu_dau_tu' => $item->ten_vu_dau_tu ?? $item->vu_dau_tu,
                    'loai_dau_tu' => $item->loai_dau_tu,
                    'khach_hang' => $khachHang,
                    'ma_khach_hang' => $maKhachHang,
                    'noi_dung' => $item->noi_dung ?? 'Thanh toán dịch vụ',
                    'da_tra_goc' => $item->tong_tien ?? 0,
                    'tien_lai' => $item->tong_tien_lai_suat ?? 0,
                    'tong_tien_tam_giu' => $item->tong_tien_tam_giu ?? 0,
                    'tong_tien_khau_tru' => $item->tong_tien_khau_tru ?? 0,
                    'tong_tien_thanh_toan_con_lai' => $item->tong_tien_thanh_toan_con_lai ?? 0,
                    'lai_suat' => 0, // Default value if not available
                    'ngay_vay' => $item->ngay_vay,
                    'ngay_tra' => $item->ngay_thanh_toan,
                    'so_dot_thanh_toan' => $item->so_dot_thanh_toan ?? 1,
                ];
            }

            // Get user name
            $userName = 'Hệ thống';
            if (auth()->check()) {
                $user = auth()->user();
                $userName = $user->full_name ?? $user->name ?? 'Hệ thống';
            }

            // Generate report info
            $reportInfo = [
                'title' => 'Báo cáo Phiếu trình thanh toán: ' . $paymentCode,
                'generated_date' => now()->format('d/m/Y H:i:s'),
                'total_records' => count($reportData),
                'report_type' => 'payment_request',
                'report_type_display' => 'Phiếu trình thanh toán',
                'generated_by' => $userName,
                'filter_summary' => 'Mã phiếu trình thanh toán: ' . $paymentCode,
                'so_to_trinh' => $paymentRequest->so_to_trinh ?? 'N/A',
                'so_dot_thanh_toan' => $paymentRequest->so_dot_thanh_toan ?? 'N/A',
                'loai_thanh_toan' => $paymentRequest->loai_thanh_toan ?? 'N/A'
            ];

            // Return view with data
           return view('Print.ReportTrinhTTDichvu', [
    'reportData' => $reportData,
    'reportInfo' => $reportInfo
]);

        } catch (\Exception $e) {
            Log::error('Error generating payment request report: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payment_code' => $request->query('payment_code')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
 * Generate report data for hom giong payment request
 * 
 * @param Request $request
 * @return \Illuminate\Contracts\View\View
 */
public function generateReportPhieuTrinhTTHomgiong(Request $request)
{
    try {
        // Get payment code from request
        $paymentCode = $request->query('payment_code');
        
        if (empty($paymentCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment code is required'
            ], 400);
        }

        // Get payment request data
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $paymentCode)
            ->first();
            
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }

        // Get payment request details (disbursements)
        $disbursements = DB::table('tb_de_nghi_thanhtoan_homgiong as dnth')
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'dnth.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->leftJoin('tb_vudautu as vdt', 'vdt.Ma_Vudautu', '=', 'dnth.vu_dau_tu')
            ->where('dnth.ma_trinh_thanh_toan', $paymentCode)
            ->select(
                'dnth.ma_giai_ngan as invoice_number',
                'dnth.vu_dau_tu',
                'vdt.Ten_Vudautu as ten_vu_dau_tu',
                'dnth.loai_thanh_toan as loai_dau_tu',
                'dnth.khach_hang_ca_nhan',
                'dnth.ma_khach_hang_ca_nhan',
                'dnth.khach_hang_doanh_nghiep',
                'dnth.ma_khach_hang_doanh_nghiep',
                'dnth.tong_tien',
                'dnth.tong_tien_tam_giu',
                'dnth.tong_tien_khau_tru',
                'dnth.tong_tien_lai_suat',
                'dnth.tong_tien_thanh_toan_con_lai',
                'dnth.trang_thai_thanh_toan',
                'dnth.ngay_thanh_toan',
                'dnth.comment as noi_dung',
                'dnth.thuc_nhan', // Added thuc_nhan column
                'pttt.so_to_trinh',
                'pttt.so_dot_thanh_toan',
                'pttt.ngay_tao as ngay_vay',
                'pttt.loai_thanh_toan as payment_type'
            )
            ->orderBy('dnth.ma_giai_ngan', 'asc')
            ->get();

        // Format data for report
        $reportData = [];
        foreach ($disbursements as $item) {
            // Format customer info (prefer individual customer if available)
            $khachHang = !empty($item->khach_hang_ca_nhan) ? $item->khach_hang_ca_nhan : $item->khach_hang_doanh_nghiep;
            $maKhachHang = !empty($item->ma_khach_hang_ca_nhan) ? $item->ma_khach_hang_ca_nhan : $item->ma_khach_hang_doanh_nghiep;
            
            $reportData[] = [
                'invoice_number' => $item->invoice_number,
                'vu_dau_tu' => $item->ten_vu_dau_tu ?? $item->vu_dau_tu,
                'loai_dau_tu' => $item->loai_dau_tu,
                'khach_hang' => $khachHang,
                'ma_khach_hang' => $maKhachHang,
                'noi_dung' => $item->noi_dung ?? 'Thanh toán hom giống',
                'da_tra_goc' => $item->tong_tien ?? 0,
                'thuc_nhan' => $item->thuc_nhan ?? 0, // Include thuc_nhan in report data
                'tien_lai' => $item->tong_tien_lai_suat ?? 0,
                'tong_tien_tam_giu' => $item->tong_tien_tam_giu ?? 0,
                'tong_tien_khau_tru' => $item->tong_tien_khau_tru ?? 0,
                'tong_tien_thanh_toan_con_lai' => $item->tong_tien_thanh_toan_con_lai ?? 0,
                'lai_suat' => 0, // Default value if not available
                'ngay_vay' => $item->ngay_vay,
                'ngay_tra' => $item->ngay_thanh_toan,
                'so_dot_thanh_toan' => $item->so_dot_thanh_toan ?? 1,
            ];
        }

        // Get user name
        $userName = 'Hệ thống';
        if (auth()->check()) {
            $user = auth()->user();
            $userName = $user->full_name ?? $user->name ?? 'Hệ thống';
        }

        // Generate report info
        $reportInfo = [
            'title' => 'Báo cáo Phiếu trình thanh toán hom giống: ' . $paymentCode,
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($reportData),
            'report_type' => 'payment_request_homgiong',
            'report_type_display' => 'Phiếu trình thanh toán hom giống',
            'generated_by' => $userName,
            'filter_summary' => 'Mã phiếu trình thanh toán: ' . $paymentCode,
            'so_to_trinh' => $paymentRequest->so_to_trinh ?? 'N/A',
            'so_dot_thanh_toan' => $paymentRequest->so_dot_thanh_toan ?? 'N/A',
            'loai_thanh_toan' => $paymentRequest->loai_thanh_toan ?? 'N/A'
        ];

        // Return view with data
        return view('Print.ReportTrinhTTHomgiong', [
            'reportData' => $reportData,
            'reportInfo' => $reportInfo
        ]);

    } catch (\Exception $e) {
        Log::error('Error generating payment request homgiong report: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'payment_code' => $request->query('payment_code')
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error generating report: ' . $e->getMessage()
        ], 500);
    }
}
}