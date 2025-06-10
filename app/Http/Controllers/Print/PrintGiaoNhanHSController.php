<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentDelivery;
use App\Models\DocumentMapping;
use App\Models\DocumentMappingHomgiong;
use App\Models\Log\DocumentLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;  // Make sure this points to the correct User model namespace

class PrintGiaoNhanHSController extends Controller
{
    
    public function print($document_code)
    {
        try {
            // Get document with related data
            $document = DocumentDelivery::with([
                'creator',
                'receiver',
                'documentMappings.bienBanNghiemThu',
                'documentMappingsHomgiong.bienBanHomGiong'
            ])->where('document_code', $document_code)->first();
    
            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document not found'
                ], 404);
            }
    
            // Only add receiver info if status is not 'creating' or 'sending'
            if (!in_array($document->status, ['creating', 'sending'])) {
                // Get receiver info from DocumentLog
                $receiverInfo = DocumentLog::where('document_id', $document->id)
                    ->where('action', 'received')
                    ->with('actionUser')
                    ->latest()
                    ->first();
    
                // Add receiver information to document
                $document->receiver_info = [
                    'name' => $document->receiver ? $document->receiver->full_name : 
                             ($receiverInfo ? $receiverInfo->actionUser->full_name : null),
                    'date' => $document->received_date ?? 
                             ($receiverInfo ? $receiverInfo->created_at : null)
                ];
            } else {
                // Set empty receiver info for creating/sending status
                $document->receiver_info = [
                    'name' => null,
                    'date' => null
                ];
            }
    
            $nghiemThuDocuments = $document->documentMappings;
            $homGiongDocuments = $document->documentMappingsHomgiong;
    
            return view('Print.PrintgiaonhanHS', compact('document', 'nghiemThuDocuments', 'homGiongDocuments'));
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }



// เพิ่มเหนือ function print($document_code)

/**
 * Print Phiếu đề nghị thanh toán Hom giống
 */
public function printPDNTTHG(Request $request)
{
    try {
        $request->validate([
            'disbursement_codes' => 'required|array',
            'disbursement_codes.*' => 'string'
        ]);

        $disbursementCodes = $request->disbursement_codes;
        $printData = [];

        foreach ($disbursementCodes as $disbursementCode) {
            // ทำความสะอาดรหัส (ลบช่องว่าง)
            $disbursementCode = trim($disbursementCode);
            
            if (empty($disbursementCode)) {
                continue;
            }

            // ดึงข้อมูลพื้นฐานจาก tb_de_nghi_thanhtoan_homgiong
            $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
                ->where('ma_giai_ngan', $disbursementCode)
                ->first();

            if (!$disbursementRequest) {
                Log::warning("Disbursement request not found for code: {$disbursementCode}");
                continue;
            }

            // ใช้ข้อความแบบตายตัว
            $serviceText = 'Thanh toán tiền Hom giống';

            // กำหนดลูกค้า (ลำดับความสำคัญ: cá nhân -> doanh nghiệp)
            $customerName = $disbursementRequest->khach_hang_ca_nhan ?: $disbursementRequest->khach_hang_doanh_nghiep ?: 'Khách hàng không xác định';
            $customerCode = $disbursementRequest->ma_khach_hang_ca_nhan ?: $disbursementRequest->ma_khach_hang_doanh_nghiep;

            // ดึงข้อมูลบัญชีธนาคาร
            $bankingInfo = $this->getBankingInfoHG($customerCode);

            // ดึงข้อมูล proposal number
            $proposalNumber = '';
            if ($disbursementRequest->ma_trinh_thanh_toan) {
                $parentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                    ->where('ma_trinh_thanh_toan', $disbursementRequest->ma_trinh_thanh_toan)
                    ->value('so_to_trinh');
                $proposalNumber = $parentRequest ?: '';
            }

            $printData[] = [
                'ma_giai_ngan' => $disbursementCode,
                'ngay_print' => now()->format('d/m/Y'),
                'noi_dung_thanh_toan' => $serviceText,
                'don_vi_dich_vu' => $customerName,
                'so_tien_de_nghi' => $disbursementRequest->tong_tien ?: 0,
                'ca_nhan_nhan_tien' => $customerName,
                'so_tai_khoan' => $bankingInfo['so_tai_khoan'] ?? '',
                'ten_ngan_hang' => $bankingInfo['ten_ngan_hang'] ?? '',
                'so_to_trinh' => $proposalNumber,
                'tong_tien' => $disbursementRequest->tong_tien ?: 0
            ];
        }

        if (empty($printData)) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลสำหรับการพิมพ์'
            ], 404);
        }

        // ส่งกลับ view สำหรับการพิมพ์
        return view('Print.PrintPhieuDNTTHG', compact('printData'));

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error in printPDNTTHG function: ' . json_encode($e->errors()));
        
        return response()->json([
            'success' => false,
            'message' => 'ข้อมูลไม่ถูกต้อง',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error in printPDNTTHG function: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการสร้างเอกสาร: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Get banking info for hom giong customers
     */
    private function getBankingInfoHG($customerCode)
    {
        if (!$customerCode) {
            return ['so_tai_khoan' => '', 'ten_ngan_hang' => ''];
        }

        // ลองค้นหาในตาราง user_farmer ก่อน
        $customer = DB::table('user_farmer')
            ->where('ma_kh_ca_nhan', $customerCode)
            ->orWhere('ma_kh_doanh_nghiep', $customerCode)
            ->first();

        if ($customer) {
            $bankName = '';
            if ($customer->ngan_hang) {
                $banking = DB::table('banking')
                    ->where('code_banking', $customer->ngan_hang)
                    ->first();
                $bankName = $banking ? $banking->name_banking : '';
            }

            return [
                'so_tai_khoan' => $customer->so_tai_khoan ?? '',
                'ten_ngan_hang' => $bankName
            ];
        }

        return ['so_tai_khoan' => '', 'ten_ngan_hang' => ''];
    }
    /**
 * Get print preview data for Phiếu đề nghị thanh toán Hom giống
 */
public function getPrintPreviewPDNTTHG(Request $request)
{
    try {
        $request->validate([
            'disbursement_codes' => 'required|array',
            'disbursement_codes.*' => 'string'
        ]);

        $disbursementCodes = $request->disbursement_codes;
        $previewData = [];

        foreach ($disbursementCodes as $disbursementCode) {
            $disbursementCode = trim($disbursementCode);
            
            if (empty($disbursementCode)) {
                continue;
            }

            // ดึงข้อมูลพื้นฐาน
            $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
                ->where('ma_giai_ngan', $disbursementCode)
                ->first();

            if (!$disbursementRequest) {
                continue;
            }

            $customerName = $disbursementRequest->khach_hang_ca_nhan ?: $disbursementRequest->khach_hang_doanh_nghiep ?: 'Khách hàng không xác định';

            $previewData[] = [
                'ma_giai_ngan' => $disbursementCode,
                'khach_hang' => $customerName,
                'so_tien' => $disbursementRequest->tong_tien ?: 0,
                'noi_dung' => 'Thanh toán tiền Hom giống'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $previewData
        ]);

    } catch (\Exception $e) {
        Log::error('Error in getPrintPreviewPDNTTHG: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการโหลดตัวอย่าง: ' . $e->getMessage()
        ], 500);
    }

}


/**
 * Print Report DNTT DV with selected sections
 */
public function printDocumentReportDNTTDV(Request $request)
{
    try {
        $request->validate([
            'payment_request_id' => 'required|string',
            'sections' => 'required|array',
            'sections.*' => 'in:bienbannghiemthu,chitietdichvu,phieuthuno'
        ]);

        $paymentRequestId = $request->payment_request_id;
        $selectedSections = $request->sections;

        // ดึงข้อมูลหลักของ Payment Request พร้อมข้อมูลจาก tb_phieu_trinh_thanh_toan
        $document = DB::table('tb_de_nghi_thanhtoan_dv as dntt')
            ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'dntt.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('dntt.ma_giai_ngan', $paymentRequestId)
            ->select([
                'dntt.*',
                'pttt.so_to_trinh',
                'pttt.so_dot_thanh_toan'
            ])
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลเอกสาร'
            ], 404);
        }

        // ข้อมูลหลักที่แสดงเสมอ
        $reportData = [
            'document_info' => [
                'ma_giai_ngan' => $document->ma_giai_ngan ?? '',
                'vu_dau_tu' => $document->vu_dau_tu ?? '',
                'loai_thanh_toan' => $document->loai_thanh_toan ?? '',
                'trang_thai_thanh_toan' => $document->trang_thai_thanh_toan ?? '',
                'khach_hang_ca_nhan' => $document->khach_hang_ca_nhan ?? '',
                'ma_khach_hang_ca_nhan' => $document->ma_khach_hang_ca_nhan ?? '',
                'khach_hang_doanh_nghiep' => $document->khach_hang_doanh_nghiep ?? '',
                'ma_khach_hang_doanh_nghiep' => $document->ma_khach_hang_doanh_nghiep ?? '',
                'so_to_trinh' => $document->so_to_trinh ?? '', // ดึงจาก tb_phieu_trinh_thanh_toan
                'so_dot_thanh_toan' => $document->so_dot_thanh_toan ?? '', // ดึงจาก tb_phieu_trinh_thanh_toan
            ],
            'financial_info' => [
                'tong_tien' => $document->tong_tien ?? 0,
                'tong_tien_tam_giu' => $document->tong_tien_tam_giu ?? 0,
                'tong_tien_khau_tru' => $document->tong_tien_khau_tru ?? 0,
                'tong_tien_lai_suat' => $document->tong_tien_lai_suat ?? 0,
                'tong_tien_thanh_toan_con_lai' => $document->tong_tien_thanh_toan_con_lai ?? 0,
                'ngay_thanh_toan' => $document->ngay_thanh_toan ?? '',
            ],
            'selected_sections' => $selectedSections,
            'sections_data' => []
        ];

        // ดึงข้อมูลตาม sections ที่เลือก
        foreach ($selectedSections as $section) {
            switch ($section) {
                case 'bienbannghiemthu':
                    $reportData['sections_data']['bienbannghiemthu'] = $this->getBienBanNghiemThuData($paymentRequestId);
                    break;
                case 'chitietdichvu':
                    $reportData['sections_data']['chitietdichvu'] = $this->getChiTietDichVuData($paymentRequestId);
                    break;
                case 'phieuthuno':
                    $reportData['sections_data']['phieuthuno'] = $this->getPhieuThuNoData($paymentRequestId);
                    break;
            }
        }

        return view('Print.PrintReportDNTTDV', compact('reportData'));

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'ข้อมูลไม่ถูกต้อง',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error in printDocumentReportDNTTDV: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการสร้างรายงาน: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper method to get Bien Ban Nghiem Thu data
 */
private function getBienBanNghiemThuData($paymentRequestId)
{
    // ใช้ตาราง Logs_phieu_trinh_thanh_toan แทน tb_mapping_pttt_dntt_dv
    return DB::table('tb_bien_ban_nghiemthu_dv as bb')
        ->join('Logs_phieu_trinh_thanh_toan as logs', 'bb.ma_nghiem_thu', '=', 'logs.ma_nghiem_thu')
        ->join('tb_de_nghi_thanhtoan_dv as dntt', 'logs.ma_de_nghi_giai_ngan', '=', 'dntt.ma_giai_ngan')
        ->where('dntt.ma_giai_ngan', $paymentRequestId)
        ->select([
            'bb.ma_nghiem_thu',
            'bb.tram',
            'bb.vu_dau_tu',
            'bb.khach_hang_ca_nhan_dt_mia',
            'bb.khach_hang_doanh_nghiep_dt_mia',
            'bb.hop_dong_dau_tu_mia',
            'bb.hinh_thuc_thuc_hien_dv',
            'bb.hop_dong_cung_ung_dich_vu',
            'bb.tong_tien',
            'bb.tong_tien_tam_giu',
            'bb.tong_tien_thanh_toan'
        ])
        ->get();
}

/**
 * Helper method to get Chi Tiet Dich Vu data
 */
/**
 * Helper method to get Chi Tiet Dich Vu data
 */
private function getChiTietDichVuData($paymentRequestId)
{
    // ใช้ tb_chitiet_dichvu_nt แทน tb_chitiet_nghiemthu_dv ตาม #DB.TXT
    return DB::table('tb_chitiet_dichvu_nt as ct')
        ->join('tb_bien_ban_nghiemthu_dv as bb', 'ct.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
        ->join('Logs_phieu_trinh_thanh_toan as logs', 'bb.ma_nghiem_thu', '=', 'logs.ma_nghiem_thu')
        ->join('tb_de_nghi_thanhtoan_dv as dntt', 'logs.ma_de_nghi_giai_ngan', '=', 'dntt.ma_giai_ngan')
        ->where('dntt.ma_giai_ngan', $paymentRequestId)
        ->select([
            'ct.ma_nghiem_thu',
            'bb.tram',
            'ct.dich_vu',
            'ct.ma_so_thua',
            'ct.don_vi_tinh',
            'ct.so_lan_thuc_hien',
            'ct.khoi_luong_thuc_hien',
            'ct.don_gia',
            'ct.thanh_tien',
            'ct.tien_con_lai',
            'ct.tien_thanh_toan'
        ])
        ->get();
}


/**
 * Helper method to get Phieu Thu No data
 */
private function getPhieuThuNoData($paymentRequestId)
{
    // ดึงข้อมูลลูกค้าจาก payment request ก่อน
    $document = DB::table('tb_de_nghi_thanhtoan_dv')
        ->where('ma_giai_ngan', $paymentRequestId)
        ->first();

    if (!$document) {
        return collect();
    }

    // ดึงข้อมูล so_to_trinh จาก tb_phieu_trinh_thanh_toan ผ่าน ma_trinh_thanh_toan
    $parentPaymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
        ->where('ma_trinh_thanh_toan', $document->ma_trinh_thanh_toan)
        ->first();

    if (!$parentPaymentRequest || !$parentPaymentRequest->so_to_trinh) {
        return collect();
    }

    // สร้าง query สำหรับค้นหาข้อมูลลูกค้าที่ตรงกัน จาก Logs_Phieu_Tinh_Lai_dv
    $query = DB::table('Logs_Phieu_Tinh_Lai_dv as logs');

    // เงื่อนไขสำหรับหาข้อมูลลูกค้าที่ตรงกันและ so_to_trinh ที่ตรงกัน
    $query->where(function($q) use ($document) {
        if (!empty($document->ma_khach_hang_ca_nhan)) {
            $q->where('logs.Ma_Khach_Hang_Ca_Nhan', $document->ma_khach_hang_ca_nhan);
        }
        if (!empty($document->ma_khach_hang_doanh_nghiep)) {
            $q->orWhere('logs.Ma_Khach_Hang_Doanh_Nghiep', $document->ma_khach_hang_doanh_nghiep);
        }
    })
    // เพิ่มเงื่อนไขเชื่อมกับ so_to_trinh
    ->where('logs.So_Tro_Trinh', $parentPaymentRequest->so_to_trinh);

    return $query->select([
        'logs.Ma_So_Phieu_PDN_Thu_No as ma_so_phieu',
        'logs.Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
        'logs.Da_Tra_Goc as da_tra_goc',
        'logs.Ngay_Vay as ngay_vay',
        'logs.Ngay_Tra as ngay_tra',
        'logs.Lai_Suat_Phan_Bo_Dau_Tu as lai_suat',
        'logs.Tien_Lai as tien_lai',
        'logs.Vu_Dau_Tu_Phan_Bo_Dau_Tu as vu_dau_tu',
        'logs.Vu_Thanh_Toan_Phan_Bo_Dau_Tu as vu_thanh_toan',
        'logs.Khach_Hang_Ca_Nhan_PDN_Thu_No as khach_hang_ca_nhan',
        'logs.Khach_Hang_Doanh_Nghiep_PDN_Thu_No as khach_hang_doanh_nghiep',
        'logs.So_Tro_Trinh as so_tro_trinh',
        'logs.Category_Debt as category_debt',
        'logs.Description as description',
        'logs.Xoa_No_Phan_Bo_Dau_Tu as xoa_no_phan_bo',
        'logs.Created_On as ngay_tao',
        'logs.Tinh_Trang_PDN_Thu_No as tinh_trang_pdn',
        'logs.Tinh_Trang_Duyet_PDN_Thu_No as tinh_trang_duyet'
    ])->get();
}


/**
 * Print Report DNTT HG with selected sections (for Hom Giong)
 */
public function printDocumentReportDNTTHG(Request $request)
{
    try {
        $request->validate([
            'payment_request_id' => 'required|string',
            'sections' => 'required|array',
            'sections.*' => 'in:phieugiaonhan,chitietgiaonhan'
        ]);

        $paymentRequestId = $request->payment_request_id;
        $selectedSections = $request->sections;

        // ดึงข้อมูลหลักของ Payment Request พร้อมข้อมูลจาก tb_phieu_trinh_thanh_toan_homgiong
        $document = DB::table('tb_de_nghi_thanhtoan_homgiong as dnhg')
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as ptthg', 'dnhg.ma_trinh_thanh_toan', '=', 'ptthg.ma_trinh_thanh_toan')
            ->where('dnhg.ma_giai_ngan', $paymentRequestId)
            ->select([
                'dnhg.*',
                'ptthg.so_to_trinh',
                'ptthg.so_dot_thanh_toan'
            ])
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลเอกสาร'
            ], 404);
        }

        // ข้อมูลหลักที่แสดงเสมอ
        $reportData = [
            'document_info' => [
                'ma_giai_ngan' => $document->ma_giai_ngan ?? '',
                'vu_dau_tu' => $document->vu_dau_tu ?? '',
                'loai_thanh_toan' => $document->loai_thanh_toan ?? '',
                'trang_thai_thanh_toan' => $document->trang_thai_thanh_toan ?? '',
                'khach_hang_ca_nhan' => $document->khach_hang_ca_nhan ?? '',
                'ma_khach_hang_ca_nhan' => $document->ma_khach_hang_ca_nhan ?? '',
                'khach_hang_doanh_nghiep' => $document->khach_hang_doanh_nghiep ?? '',
                'ma_khach_hang_doanh_nghiep' => $document->ma_khach_hang_doanh_nghiep ?? '',
                'so_to_trinh' => $document->so_to_trinh ?? '',
                'so_dot_thanh_toan' => $document->so_dot_thanh_toan ?? '',
            ],
            'financial_info' => [
                'thuc_nhan' => $document->thuc_nhan ?? 0,
                'tong_tien' => $document->tong_tien ?? 0,
                'tong_tien_tam_giu' => $document->tong_tien_tam_giu ?? 0,
                'tong_tien_khau_tru' => $document->tong_tien_khau_tru ?? 0,
                'tong_tien_lai_suat' => $document->tong_tien_lai_suat ?? 0,
                'tong_tien_thanh_toan_con_lai' => $document->tong_tien_thanh_toan_con_lai ?? 0,
                'ngay_thanh_toan' => $document->ngay_thanh_toan ?? '',
            ],
            'selected_sections' => $selectedSections,
            'sections_data' => []
        ];

        // ดึงข้อมูลตาม sections ที่เลือก
        foreach ($selectedSections as $section) {
            switch ($section) {
                case 'phieugiaonhan':
                    $reportData['sections_data']['phieugiaonhan'] = $this->getPhieugiaonhanhomgiong($paymentRequestId);
                    break;
                case 'chitietgiaonhan':
                    $reportData['sections_data']['chitietgiaonhan'] = $this->getChitietPhieugiaonhanhomgiong($paymentRequestId);
                    break;
            }
        }

        return view('Print.PrintReportDNTTHG', compact('reportData'));

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'ข้อมูลไม่ถูกต้อง',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error in printDocumentReportDNTTHG: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการสร้างรายงาน: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper method to get Phieu giao nhan hom giong data
 */
private function getPhieugiaonhanhomgiong($paymentRequestId)
{
    // ใช้ตาราง logs_phieu_trinh_thanh_toan_homgiong แทน tb_mapping_pttt_dntt_hg
    return DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
        ->join('logs_phieu_trinh_thanh_toan_homgiong as logs', 'bbhg.ma_so_phieu', '=', 'logs.ma_so_phieu')
        ->join('tb_de_nghi_thanhtoan_homgiong as dnhg', 'logs.ma_de_nghi_giai_ngan', '=', 'dnhg.ma_giai_ngan')
        ->where('dnhg.ma_giai_ngan', $paymentRequestId)
        ->select([
            'bbhg.ma_so_phieu',
            'bbhg.tram',
            'bbhg.vu_dau_tu',
            'bbhg.ten_phieu',
            'bbhg.hop_dong_dau_tu_mia_ben_giao_hom',
            'bbhg.khach_hang_ca_nhan',
            'bbhg.khach_hang_doanh_nghiep',
            'bbhg.tong_thuc_nhan',
            'bbhg.tong_tien'
        ])
        ->get();
}


/**
 * Helper method to get Chi tiet Phieu giao nhan hom giong data
 */
private function getChitietPhieugiaonhanhomgiong($paymentRequestId)
{
    // ใช้ตาราง logs_phieu_trinh_thanh_toan_homgiong แทน tb_mapping_pttt_dntt_hg
    return DB::table('tb_chitiet_giaonhan_homgiong as cthg')
        ->join('bien_ban_nghiem_thu_hom_giong as bbhg', 'cthg.ma_so_phieu', '=', 'bbhg.ma_so_phieu')
        ->join('logs_phieu_trinh_thanh_toan_homgiong as logs', 'bbhg.ma_so_phieu', '=', 'logs.ma_so_phieu')
        ->join('tb_de_nghi_thanhtoan_homgiong as dnhg', 'logs.ma_de_nghi_giai_ngan', '=', 'dnhg.ma_giai_ngan')
        ->where('dnhg.ma_giai_ngan', $paymentRequestId)
        ->select([
            'cthg.ma_so_phieu',
            'bbhg.tram',
            'cthg.phieu_gn_hom_giong',
            'cthg.giong_mia',
            'cthg.thua_dat',
            'cthg.don_vi_tinh',
            'cthg.thuc_nhan',
            'cthg.don_gia',
            'cthg.thanh_tien_hom_giong'
        ])
        ->get();
}

}