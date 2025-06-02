<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrintPhieuDNTTControllers extends Controller
{
    /**
     * Handle GET request for printing
     */
    public function printGet(Request $request)
    {
        try {
            $codes = $request->get('codes');
            
            if (!$codes) {
                // แทนที่จะใช้ back() ให้แสดงข้อผิดพลาดในรูปแบบ JSON
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบรหัสเบิกจ่าย'
                ], 400);
            }
            
            // แปลง string เป็น array
            $disbursementCodes = explode(',', $codes);
            
            // Validate disbursement codes
            if (empty($disbursementCodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'รหัสเบิกจ่ายไม่ถูกต้อง'
                ], 400);
            }
            
            // ใช้ logic เดียวกันกับ print method
            return $this->processPrintData($disbursementCodes);
            
        } catch (\Exception $e) {
            Log::error('Error in printGet function: ' . $e->getMessage());
            
            // ส่งกลับข้อมูล JSON แทนการ redirect
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างเอกสาร: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle POST request for printing (เก็บไว้เพื่อความเข้ากันได้)
     */
    public function print(Request $request)
    {
        try {
            $request->validate([
                'disbursement_codes' => 'required|array',
                'disbursement_codes.*' => 'string'
            ]);

            $disbursementCodes = $request->disbursement_codes;
            
            return $this->processPrintData($disbursementCodes);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in print function: ' . json_encode($e->errors()));
            
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in print function: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างเอกสาร: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process print data for both GET and POST methods
     */
    private function processPrintData(array $disbursementCodes)
    {
        try {
            $printData = [];

            foreach ($disbursementCodes as $disbursementCode) {
                // ทำความสะอาดรหัส (ลบช่องว่าง)
                $disbursementCode = trim($disbursementCode);
                
                if (empty($disbursementCode)) {
                    continue;
                }

                // ดึงข้อมูลพื้นฐานจาก tb_de_nghi_thanhtoan_dv
                $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_dv')
                    ->where('ma_giai_ngan', $disbursementCode)
                    ->first();

                if (!$disbursementRequest) {
                    Log::warning("Disbursement request not found: {$disbursementCode}");
                    continue;
                }

                // ดึง ma_nghiem_thu จาก Logs_phieu_trinh_thanh_toan
                $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                    ->where('ma_de_nghi_giai_ngan', $disbursementCode)
                    ->pluck('ma_nghiem_thu')
                    ->toArray();

                // ดึงรายการ dich_vu จาก tb_chitiet_dichvu_nt
                $services = [];
                if (!empty($receiptIds)) {
                    $services = DB::table('tb_chitiet_dichvu_nt')
                        ->whereIn('ma_nghiem_thu', $receiptIds)
                        ->distinct()
                        ->pluck('dich_vu')
                        ->filter()
                        ->unique()
                        ->toArray();
                }

                $serviceText = !empty($services) ? implode(', ', $services) : 'Dịch vụ không xác định';

                // กำหนดลูกค้า (ลำดับความสำคัญ: cá nhân -> doanh nghiệp)
                $customerName = $disbursementRequest->khach_hang_ca_nhan ?: $disbursementRequest->khach_hang_doanh_nghiep ?: 'Khách hàng không xác định';
                $customerCode = $disbursementRequest->ma_khach_hang_ca_nhan ?: $disbursementRequest->ma_khach_hang_doanh_nghiep;

                // ดึงข้อมูลบัญชีธนาคาร
                $bankingInfo = $this->getBankingInfo($customerCode);

                // ดึงข้อมูล proposal number
                $proposalNumber = '';
                if ($disbursementRequest->ma_trinh_thanh_toan) {
                    $parentRequest = DB::table('tb_phieu_trinh_thanh_toan')
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
            return view('Print.PrintPhieuDNTTDV', compact('printData'));

        } catch (\Exception $e) {
            Log::error('Error processing print data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการประมวลผลข้อมูล: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getBankingInfo($customerCode)
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
                $bankName = $banking ? $banking->name_banking : $customer->ngan_hang;
            }

            return [
                'so_tai_khoan' => $customer->so_tai_khoan ?? '',
                'ten_ngan_hang' => $bankName
            ];
        }

        return ['so_tai_khoan' => '', 'ten_ngan_hang' => ''];
    }

    public function getPrintPreview(Request $request)
    {
        try {
            $request->validate([
                'disbursement_codes' => 'required|array',
                'disbursement_codes.*' => 'string'
            ]);

            $disbursementCodes = $request->disbursement_codes;
            $previewData = [];

            foreach ($disbursementCodes as $disbursementCode) {
                $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_dv')
                    ->where('ma_giai_ngan', $disbursementCode)
                    ->first();

                if ($disbursementRequest) {
                    $customerName = $disbursementRequest->khach_hang_ca_nhan ?: $disbursementRequest->khach_hang_doanh_nghiep;
                    
                    $previewData[] = [
                        'ma_giai_ngan' => $disbursementCode,
                        'customer_name' => $customerName,
                        'total_amount' => $disbursementRequest->tong_tien ?: 0,
                        'investment_project' => $disbursementRequest->vu_dau_tu,
                        'payment_type' => $disbursementRequest->loai_thanh_toan
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $previewData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting print preview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting preview data: ' . $e->getMessage()
            ], 500);
        }
    }
}