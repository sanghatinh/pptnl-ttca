<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\PhieudenghithanhtoanHomgiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema; 
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


/**
 * Update hold amount for selected payment requests based on harvest costs
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function updateHoldAmount(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'disbursement_codes' => 'required|array|min:1',
            'disbursement_codes.*' => 'string',
            'don_gia_chat_mia' => 'required|numeric|min:0',
            'don_gia_gap_mia' => 'required|numeric|min:0',
            'don_gia_van_chuyen_mia' => 'required|numeric|min:0',
        ]);
        
        $disbursementCodes = $request->input('disbursement_codes');
        $donGiaChatMia = $request->input('don_gia_chat_mia');
        $donGiaGapMia = $request->input('don_gia_gap_mia');
        $donGiaVanChuyenMia = $request->input('don_gia_van_chuyen_mia');
        
        // Calculate total harvest cost per unit
        $tongChiPhiThuHoach = $donGiaChatMia + $donGiaGapMia + $donGiaVanChuyenMia;
        
        DB::beginTransaction();
        
        $updatedCount = 0;
        $updateResults = [];
        
        // Update each selected disbursement request
        foreach ($disbursementCodes as $disbursementCode) {
            // Get the disbursement request
            $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
                ->where('ma_giai_ngan', $disbursementCode)
                ->first();
            
            if (!$disbursementRequest) {
                continue;
            }
            
            // Calculate new hold amount: Chi phí thu hoạch * Thực nhận
            $newHoldAmount = $tongChiPhiThuHoach * $disbursementRequest->thuc_nhan;
            
            // Calculate new remaining amount
            $newRemainingAmount = $disbursementRequest->tong_tien 
                - $newHoldAmount 
                - $disbursementRequest->tong_tien_khau_tru 
                + $disbursementRequest->tong_tien_lai_suat;
            
            // Update the disbursement request - ลบ updated_at ออก
            $affected = DB::table('tb_de_nghi_thanhtoan_homgiong')
                ->where('ma_giai_ngan', $disbursementCode)
                ->update([
                    'tong_tien_tam_giu' => $newHoldAmount,
                    'tong_tien_thanh_toan_con_lai' => $newRemainingAmount
                    // ลบ 'updated_at' => now() ออก
                ]);
            
            if ($affected > 0) {
                $updatedCount++;
                $updateResults[] = [
                    'ma_giai_ngan' => $disbursementCode,
                    'thuc_nhan' => $disbursementRequest->thuc_nhan,
                    'old_hold_amount' => $disbursementRequest->tong_tien_tam_giu,
                    'new_hold_amount' => $newHoldAmount,
                    'new_remaining_amount' => $newRemainingAmount
                ];
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật thành công {$updatedCount} phiếu đề nghị",
            'updated_count' => $updatedCount,
            'calculation_details' => [
                'don_gia_chat_mia' => $donGiaChatMia,
                'don_gia_gap_mia' => $donGiaGapMia,
                'don_gia_van_chuyen_mia' => $donGiaVanChuyenMia,
                'tong_chi_phi_thu_hoach' => $tongChiPhiThuHoach
            ],
            'update_results' => $updateResults
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating hold amount for homgiong: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật tổng tiền tạm giữ: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Bulk delete payment requests for homgiong
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function bulkDelete(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'disbursement_codes' => 'required|array',
            'disbursement_codes.*' => 'string'
        ]);
        
        $disbursementCodes = $request->input('disbursement_codes');
        
        DB::beginTransaction();
        
        // Delete the payment requests and their associated logs
        $deletedCount = 0;
        foreach ($disbursementCodes as $code) {
            // Find the payment request
            $paymentRequest = PhieudenghithanhtoanHomgiong::where('ma_giai_ngan', $code)->first();
            
            if ($paymentRequest) {
                // Delete associated logs in logs_phieu_trinh_thanh_toan_homgiong
                DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                    ->where('ma_de_nghi_giai_ngan', $code)
                    ->update(['ma_de_nghi_giai_ngan' => null]);
                
                // Delete the payment request itself
                $paymentRequest->delete();
                $deletedCount++;
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} payment requests deleted successfully",
            'deleted_count' => $deletedCount
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error bulk deleting payment requests homgiong: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error deleting payment requests',
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
 * Bulk update payment requests for homgiong
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function bulkUpdate(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'disbursement_codes' => 'required|array',
            'disbursement_codes.*' => 'string',
            'update_data' => 'required|array',
        ]);
        
        $disbursementCodes = $request->input('disbursement_codes');
        $updateData = $request->input('update_data');
        
        DB::beginTransaction();
        
        $updatedCount = 0;
        
        foreach ($disbursementCodes as $code) {
            $paymentRequest = PhieudenghithanhtoanHomgiong::where('ma_giai_ngan', $code)->first();
            
            if ($paymentRequest) {
                // Handle disbursement code changes
                if (isset($updateData['ma_giai_ngan']) && $updateData['ma_giai_ngan'] !== $code) {
                    // Create new record with new disbursement code
                    $newPaymentRequestData = $paymentRequest->toArray();
                    unset($newPaymentRequestData['id']);
                    
                    // Update only the allowed fields
                    $allowedFields = [
                        'ma_giai_ngan',
                        'khach_hang_ca_nhan',
                        'ma_khach_hang_ca_nhan', 
                        'khach_hang_doanh_nghiep',
                        'ma_khach_hang_doanh_nghiep'
                    ];
                    
                    foreach ($allowedFields as $field) {
                        if (isset($updateData[$field])) {
                            $newPaymentRequestData[$field] = $updateData[$field];
                        }
                    }
                    
                    // Create new record
                    PhieudenghithanhtoanHomgiong::create($newPaymentRequestData);
                    
                    // Update logs to point to new disbursement code
                    DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                        ->where('ma_de_nghi_giai_ngan', $code)
                        ->update(['ma_de_nghi_giai_ngan' => $updateData['ma_giai_ngan']]);
                    
                    // Delete the old record
                    $paymentRequest->delete();
                    
                    $updatedCount = 1;
                } else {
                    // Regular update without changing disbursement code
                    $fieldsToUpdate = [];
                    
                    // Only allow specific fields to be updated
                    $allowedFields = [
                        'khach_hang_ca_nhan',
                        'ma_khach_hang_ca_nhan',
                        'khach_hang_doanh_nghiep', 
                        'ma_khach_hang_doanh_nghiep'
                    ];
                    
                    foreach ($allowedFields as $field) {
                        if (isset($updateData[$field]) && !is_null($updateData[$field]) && $updateData[$field] !== '') {
                            $fieldsToUpdate[$field] = $updateData[$field];
                        }
                    }
                    
                    if (!empty($fieldsToUpdate)) {
                        $paymentRequest->update($fieldsToUpdate);
                        $updatedCount++;
                    }
                }
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật thành công {$updatedCount} phiếu đề nghị",
            'updated_count' => $updatedCount
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error bulk updating payment requests homgiong: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật phiếu đề nghị: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Import payment requests from CSV/Excel file
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function import(Request $request)
{
    try {
        // เปลี่ยน validation rule เป็น custom function ตรวจสอบ extension
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    $allowedExtensions = ['csv', 'xlsx', 'xls'];
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('ไฟล์ต้องเป็นประเภท: ' . implode(', ', $allowedExtensions));
                    }
                }
            ],
            'payment_code' => 'required|string'
        ]);

        $file = $request->file('file');
        $paymentCode = $request->input('payment_code');

        // Check if parent payment request exists
        $parentPaymentRequest = \DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $paymentCode)
            ->first();

        if (!$parentPaymentRequest) {
            return response()->json([
                'errors' => ['Invalid payment code']
            ], 404);
        }

        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());

        // เพิ่มการตรวจสอบ extension อีกครั้งเพื่อความปลอดภัย
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'errors' => ['file' => ['ประเภทไฟล์ไม่ถูกต้อง']]
            ], 422);
        }

        // Copy file ไป temp path
        $importId = uniqid('import_dntthg_');
        $fileName = $importId . '.' . $extension;
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        if (!copy($file->getRealPath(), $tempPath)) {
            return response()->json([
                'errors' => ['Failed to copy file to temporary directory']
            ], 500);
        }

        // ใช้ specific reader classes และ fallback mechanism
        try {
            if ($extension === 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
            } elseif ($extension === 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($extension === 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                throw new \Exception("Unsupported file format: {$extension}");
            }
            $spreadsheet = $reader->load($tempPath);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            \Log::warning("Failed to read with {$extension} reader, trying alternative readers: " . $e->getMessage());
            $readersToTry = [];
            if ($extension !== 'csv') {
                $readersToTry[] = ['type' => 'Csv', 'class' => \PhpOffice\PhpSpreadsheet\Reader\Csv::class];
            }
            if ($extension !== 'xlsx') {
                $readersToTry[] = ['type' => 'Xlsx', 'class' => \PhpOffice\PhpSpreadsheet\Reader\Xlsx::class];
            }
            if ($extension !== 'xls') {
                $readersToTry[] = ['type' => 'Xls', 'class' => \PhpOffice\PhpSpreadsheet\Reader\Xls::class];
            }
            $spreadsheet = null;
            foreach ($readersToTry as $readerInfo) {
                try {
                    $reader = new $readerInfo['class']();
                    if ($readerInfo['type'] === 'Csv') {
                        $reader->setDelimiter(',');
                        $reader->setEnclosure('"');
                    }
                    $spreadsheet = $reader->load($tempPath);
                    \Log::info("Successfully read file using {$readerInfo['type']} reader");
                    break;
                } catch (\Exception $readerEx) {
                    \Log::warning("Failed to read with {$readerInfo['type']} reader: " . $readerEx->getMessage());
                    continue;
                }
            }
            if (!$spreadsheet) {
                throw new \Exception("Unable to read the uploaded file. Please ensure it's a valid Excel or CSV file.");
            }
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Get headers from first row
        if (empty($rows) || count($rows) < 2) {
            @unlink($tempPath);
            return response()->json([
                'errors' => ['Invalid file format']
            ], 400);
        }

        $headers = array_map('trim', $rows[0]);

        // Required columns for homgiong import
        $requiredColumns = [
            'ma_giai_ngan',
            'vu_dau_tu',
            'loai_thanh_toan'
        ];

        // Optional columns
        $optionalColumns = [
            'khach_hang_ca_nhan',
            'ma_khach_hang_ca_nhan',
            'khach_hang_doanh_nghiep',
            'ma_khach_hang_doanh_nghiep',
        ];

        // Find column indices
        $columnIndices = [];
        foreach (array_merge($requiredColumns, $optionalColumns) as $column) {
            $index = array_search(strtolower($column), array_map('strtolower', $headers));
            if ($index === false && in_array($column, $requiredColumns)) {
                @unlink($tempPath);
                return response()->json([
                    'errors' => ["Missing required column: {$column}"]
                ], 400);
            }
            $columnIndices[$column] = $index;
        }

        // Skip header row
        array_shift($rows);

        $importedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;
        $errors = [];

        \DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = [];
                    foreach ($requiredColumns as $column) {
                        $excelIndex = $columnIndices[$column];
                        $data[$column] = ($excelIndex !== false && isset($row[$excelIndex])) ? trim($row[$excelIndex]) : null;
                    }
                    foreach ($optionalColumns as $column) {
                        $excelIndex = $columnIndices[$column] ?? false;
                        $data[$column] = ($excelIndex !== false && isset($row[$excelIndex])) ? trim($row[$excelIndex]) : null;
                    }
                    $data['ma_trinh_thanh_toan'] = $paymentCode;

                    // Check for duplicate ma_giai_ngan
                    $exists = \DB::table('tb_de_nghi_thanhtoan_homgiong')
                        ->where('ma_giai_ngan', $data['ma_giai_ngan'])
                        ->exists();

                    if ($exists) {
                        $duplicateCount++;
                        $errors[] = "Row " . ($rowIndex + 2) . ": Duplicate ma_giai_ngan: " . $data['ma_giai_ngan'];
                        continue;
                    }

                    // Insert record
                    \DB::table('tb_de_nghi_thanhtoan_homgiong')->insert($data);
                    $importedCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }

            \DB::commit();
            @unlink($tempPath);

            $message = "Import completed: {$importedCount} records imported";
            if ($duplicateCount > 0) {
                $message .= ", {$duplicateCount} duplicates skipped";
            }
            if ($errorCount > 0) {
                $message .= ", {$errorCount} errors";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported_count' => $importedCount,
                'duplicate_count' => $duplicateCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            @unlink($tempPath);
            throw $e;
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Error importing payment requests homgiong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage(),
            'errors' => [$e->getMessage()]
        ], 500);
    }
}

/**
 * Download template file for import
 *
 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
 */
public function downloadTemplate()
{
    try {
        // Create workbook with headers
        $wb = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter(
            new \PhpOffice\PhpSpreadsheet\Spreadsheet(), 'Xlsx'
        );
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Define headers
        $headers = [
            'ma_giai_ngan',
            'vu_dau_tu', 
            'loai_thanh_toan',
            'khach_hang_ca_nhan',
            'ma_khach_hang_ca_nhan',
            'khach_hang_doanh_nghiep',
            'ma_khach_hang_doanh_nghiep',
            'tram'
        ];
        
        // Set headers
        foreach ($headers as $index => $header) {
            $worksheet->setCellValue(chr(65 + $index) . '1', $header);
        }
        
        // Add example data
        $exampleData = [
            'GN-HG-001',
            'VDT001',
            'Thanh toán hom giống',
            'Nguyễn Văn A',
            'KH001',
            '',
            '',
            'Trạm 1'
        ];
        
        foreach ($exampleData as $index => $value) {
            $worksheet->setCellValue(chr(65 + $index) . '2', $value);
        }
        
        // Style the headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ];
        
        $worksheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and save to temporary file
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempFile = tempnam(sys_get_temp_dir(), 'homgiong_import_template') . '.xlsx';
        $writer->save($tempFile);
        
        return response()->download($tempFile, 'phieu_de_nghi_thanh_toan_homgiong_template.xlsx')->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error('Error creating import template: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error creating template: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * อัพเดตสถานะของเอกสารขอเบิกเงิน homgiong
 *
 * @param  Request  $request
 * @param  string  $id
 * @return \Illuminate\Http\JsonResponse
 */
public function updateStatusHomgiong(Request $request, $id)
{
    try {
        // Validate the request - ลดการ validate เฉพาะข้อมูลที่จำเป็น
        $validationRules = [
            'status' => 'required|string|in:processing,submitted,paid,cancelled',
            'action_notes' => 'nullable|string|max:1000',
        ];
        
        // Add payment_date validation if status is 'paid'
        if ($request->input('status') === 'paid') {
            $validationRules['payment_date'] = 'required|date';
        }
        
        $validated = $request->validate($validationRules);

        // Find the payment request in homgiong table
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }

        // Create update data array for main payment request - เฉพาะข้อมูลพื้นฐาน
        $updateData = [
            'trang_thai_thanh_toan' => $validated['status'],
        ];
        
        // Add payment_date to update data if status is 'paid'
        if ($validated['status'] === 'paid' && isset($validated['payment_date'])) {
            $updateData['ngay_thanh_toan'] = $validated['payment_date'];
        }

        // Update the main payment request status
        DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->update($updateData);

        // Determine action_date: use payment_date if status is 'paid' and payment_date is provided, otherwise use now()
        $actionDate = ($validated['status'] === 'paid' && isset($validated['payment_date'])) 
            ? $validated['payment_date'] 
            : now();

        // Record the action in the action table for homgiong
        DB::table('action_phieu_trinh_thanh_toan_homgiong')->insert([
            'ma_trinh_thanh_toan' => $id,
            'action' => $validated['status'],
            'action_by' => Auth::id(),
            'action_date' => $actionDate,
            'comments' => $validated['action_notes'] ?? '',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Update status for all related disbursement requests in homgiong table
        $disbursementCodes = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->whereNotNull('ma_de_nghi_giai_ngan')
            ->pluck('ma_de_nghi_giai_ngan')
            ->unique()
            ->toArray();
            
        // Update status for all related disbursement requests - เฉพาะสถานะและวันที่
        if (!empty($disbursementCodes)) {
            $disbursementUpdateData = [
                'trang_thai_thanh_toan' => $validated['status']
            ];
            
            // Add payment_date if status is 'paid'
            if ($validated['status'] === 'paid' && isset($validated['payment_date'])) {
                $disbursementUpdateData['ngay_thanh_toan'] = $validated['payment_date'];
            }
            
            // Update all related disbursement requests
            DB::table('tb_de_nghi_thanhtoan_homgiong')
                ->whereIn('ma_giai_ngan', $disbursementCodes)
                ->update($disbursementUpdateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái phiếu trình thanh toán thành công',
            'status' => $validated['status'],
            'payment_date' => $validated['status'] === 'paid' ? $validated['payment_date'] : null,
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating payment request homgiong status: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error updating payment request status: ' . $e->getMessage(),
        ], 500);
    }
}


/**
 * ดึงข้อมูลประวัติการประมวลผลสำหรับ homgiong
 */
public function getProcessingHistoryHomgiong($paymentCode)
{
    try {
        $history = DB::table('action_phieu_trinh_thanh_toan_homgiong')
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
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Save note for payment request homgiong
 */
public function saveNoteHomgiong(Request $request, $id)
{
    try {
        // Validate the request
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000' // Allow up to 1000 characters for note
        ]);

        // Find the payment request
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }

        // Update the note field
        $affected = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->update(['note' => $validated['note']]);

        if ($affected) {
            return response()->json([
                'success' => true,
                'message' => 'Ghi chú đã được lưu thành công',
                'note' => $validated['note']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lưu ghi chú'
            ], 500);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error saving payment request homgiong note: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lưu ghi chú: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update basic info for payment request homgiong
 */
public function updateBasicInfoHomgiong(Request $request, $id)
{
    try {
        // Validate the request data
        $validated = $request->validate([
            'so_to_trinh' => 'nullable|string|max:255',
            'ngay_tao' => 'nullable|date',
            'so_dot_thanh_toan' => 'nullable|integer|min:1',
            'loai_thanh_toan' => 'nullable|string|max:255',
            'vu_dau_tu' => 'nullable|string|max:255',
            'ngay_thanh_toan' => 'nullable|date', // เพิ่ม validation สำหรับ ngay_thanh_toan
            'tong_tien_thanh_toan' => 'nullable|numeric|min:0',
            'tong_tien_tam_giu' => 'nullable|numeric|min:0',
            'tong_tien_khau_tru' => 'nullable|numeric|min:0',
            'tong_tien_lai_suat' => 'nullable|numeric|min:0',
            'tong_tien_thanh_toan_con_lai' => 'nullable|numeric|min:0',
        ]);

        // Find the payment request record
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
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
        
        // Add financial fields to update data
        if (isset($validated['tong_tien_thanh_toan'])) {
            $updateData['tong_tien_thanh_toan'] = $validated['tong_tien_thanh_toan'];
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
            // *** ลบ updated_at ออก เนื่องจากตารางไม่มี column นี้ ***
            
            DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                ->where('ma_trinh_thanh_toan', $id)
                ->update($updateData);
                
            // *** NEW: อัปเดต ngay_thanh_toan ใน tb_de_nghi_thanhtoan_homgiong ***
            if (isset($validated['ngay_thanh_toan'])) {
                // Get all disbursement codes associated with this payment request
                $disbursementCodes = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                    ->where('ma_trinh_thanh_toan', $id)
                    ->whereNotNull('ma_de_nghi_giai_ngan')
                    ->pluck('ma_de_nghi_giai_ngan')
                    ->unique()
                    ->toArray();
                    
                // Update payment date for all related disbursement requests
                if (!empty($disbursementCodes)) {
                    // *** ตรวจสอบก่อนว่าตาราง tb_de_nghi_thanhtoan_homgiong มี column updated_at หรือไม่ ***
                    $updateDisbursementData = [
                        'ngay_thanh_toan' => $validated['ngay_thanh_toan']
                    ];
                    
                    // ตรวจสอบว่ามี column updated_at หรือไม่
                    if (Schema::hasColumn('tb_de_nghi_thanhtoan_homgiong', 'updated_at')) {
                        $updateDisbursementData['updated_at'] = now();
                    }
                    
                    DB::table('tb_de_nghi_thanhtoan_homgiong')
                        ->whereIn('ma_giai_ngan', $disbursementCodes)
                        ->update($updateDisbursementData);
                }
            }
        }

        // Get the updated record
        $updatedRecord = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin phiếu trình thanh toán thành công',
            'data' => $updatedRecord
        ]);

    } catch (\Exception $e) {
        Log::error('Error updating payment request homgiong basic info: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật phiếu trình thanh toán: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Delete a payment request homgiong
 */
public function destroyHomgiong($id)
{
    try {
        DB::beginTransaction();
        
        // Find the payment request
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
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
        DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 2. Delete action records from action_phieu_trinh_thanh_toan_homgiong table
        DB::table('action_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 3. Delete related payment requests from tb_de_nghi_thanhtoan_homgiong
        DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        // 4. Finally delete the payment request itself
        DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->delete();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment request deleted successfully'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error deleting payment request homgiong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error deleting payment request',
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
 * Get all payment requests for homgiong
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getAllPaymentRequestsHomgiong(Request $request)
{
    try {
        // ดึงข้อมูล user type และ user ID จาก headers ที่ JwtMiddleware ส่งมา
        $userType = $request->header('X-User-Type');
        $userId = $request->header('X-User-ID');
        
        // Build query with joins to get all required data
        $query = DB::table('tb_de_nghi_thanhtoan_homgiong as dngn')
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'pttt.ma_trinh_thanh_toan', '=', 'dngn.ma_trinh_thanh_toan')
            ->leftJoin('tb_vudautu as vdt', 'vdt.Ma_Vudautu', '=', 'dngn.vu_dau_tu')
            ->select(
                'dngn.ma_giai_ngan',
                'dngn.vu_dau_tu',
                'vdt.Ten_Vudautu as ten_vu_dau_tu',
                'dngn.loai_thanh_toan',
                'dngn.khach_hang_ca_nhan',
                'dngn.ma_khach_hang_ca_nhan',
                'dngn.khach_hang_doanh_nghiep',
                'dngn.ma_khach_hang_doanh_nghiep',
                'dngn.thuc_nhan',
                'dngn.tong_tien',
                'dngn.tong_tien_tam_giu',
                'dngn.tong_tien_khau_tru',
                'dngn.tong_tien_lai_suat',
                'dngn.tong_tien_thanh_toan_con_lai',
                'dngn.ngay_thanh_toan',
                'dngn.trang_thai_thanh_toan',
                'pttt.so_to_trinh',
                'pttt.so_dot_thanh_toan',
                'pttt.ngay_tao'
            );

        // ถ้าเป็น farmer ให้กรองข้อมูลตาม customer ID
        if ($userType === 'farmer') {
            // ดึงข้อมูล farmer จากตาราง user_farmer
            $farmerData = DB::table('user_farmer')
                ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                ->where('id', $userId)
                ->first();
            
            if ($farmerData) {
                $query->where(function($q) use ($farmerData) {
                    // กรองตาม ma_khach_hang_ca_nhan หรือ ma_khach_hang_doanh_nghiep
                    if (!empty($farmerData->ma_kh_ca_nhan)) {
                        $q->where('dngn.ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                    }
                    if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                        $q->orWhere('dngn.ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                    }
                });
            } else {
                // ถ้าไม่พบข้อมูล farmer ให้คืนข้อมูลว่าง
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                    'unique_values' => [
                        'vu_dau_tu' => [],
                        'loai_thanh_toan' => [],
                        'trang_thai_thanh_toan' => []
                    ]
                ]);
            }
        }
        // สำหรับ employee ไม่ต้องกรองข้อมูล (แสดงทั้งหมด)

        // Apply filters based on request parameters
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('dngn.trang_thai_thanh_toan', $request->status);
        }
        
        // Individual column filters
        $filterableColumns = [
            'vu_dau_tu' => 'dngn.vu_dau_tu',
            'loai_thanh_toan' => 'dngn.loai_thanh_toan',
            'trang_thai_thanh_toan' => 'dngn.trang_thai_thanh_toan'
        ];
        
        foreach ($filterableColumns as $column => $dbColumn) {
            if ($request->has("filter_$column") && !empty($request->input("filter_$column"))) {
                $filterValues = explode(',', $request->input("filter_$column"));
                $query->whereIn($dbColumn, $filterValues);
            }
        }
        
        // Date filter for ngay_thanh_toan
        if ($request->has('filter_ngay_thanh_toan') && !empty($request->filter_ngay_thanh_toan)) {
            $dateRange = explode(',', $request->filter_ngay_thanh_toan);
            if (count($dateRange) === 2) {
                $query->whereBetween('dngn.ngay_thanh_toan', [$dateRange[0], $dateRange[1]]);
            }
        }
        
        // Global search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('dngn.ma_giai_ngan', 'like', $searchTerm)
                  ->orWhere('dngn.khach_hang_doanh_nghiep', 'like', $searchTerm)
                  ->orWhere('dngn.khach_hang_ca_nhan', 'like', $searchTerm);
            });
        }

        // Sort options (default to newest first)
        $sortField = $request->input('sort_field', 'dngn.id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Get all records instead of paginating
        $results = $query->get();
        $totalCount = count($results);

        // Also get all unique values for dropdown filters (ปรับปรุงให้กรองตาม user type ด้วย)
        $uniqueVuDauTu = DB::table('tb_de_nghi_thanhtoan_homgiong as dngn');
        $uniqueLoaiThanhToan = DB::table('tb_de_nghi_thanhtoan_homgiong as dngn');
        $uniqueTrangThaiThanhToan = DB::table('tb_de_nghi_thanhtoan_homgiong as dngn');

        // ถ้าเป็น farmer ให้กรองข้อมูล unique values ด้วย
        if ($userType === 'farmer' && isset($farmerData)) {
            $filterCondition = function($query) use ($farmerData) {
                if (!empty($farmerData->ma_kh_ca_nhan)) {
                    $query->where('ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                }
                if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                    $query->orWhere('ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                }
            };

            $uniqueVuDauTu = $uniqueVuDauTu->where($filterCondition);
            $uniqueLoaiThanhToan = $uniqueLoaiThanhToan->where($filterCondition);
            $uniqueTrangThaiThanhToan = $uniqueTrangThaiThanhToan->where($filterCondition);
        }

        $uniqueVuDauTu = $uniqueVuDauTu->distinct()->pluck('vu_dau_tu')->filter()->sort()->values();
        $uniqueLoaiThanhToan = $uniqueLoaiThanhToan->distinct()->pluck('loai_thanh_toan')->filter()->sort()->values();
        $uniqueTrangThaiThanhToan = $uniqueTrangThaiThanhToan->distinct()->pluck('trang_thai_thanh_toan')->filter()->sort()->values();

        return response()->json([
            'success' => true,
            'data' => $results,
            'total' => $totalCount,
            'unique_values' => [
                'vu_dau_tu' => $uniqueVuDauTu,
                'loai_thanh_toan' => $uniqueLoaiThanhToan,
                'trang_thai_thanh_toan' => $uniqueTrangThaiThanhToan
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching payment requests homgiong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error fetching payment requests homgiong',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Display the detailed information of a payment request for homgiong
 *
 * @param string $id
 * @return \Illuminate\Http\JsonResponse
 */
public function showDetailPayment($id)
{
    try {
        // Get the payment request details by ma_giai_ngan from homgiong table
        $paymentRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_giai_ngan', $id)
            ->first();

        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu đề nghị thanh toán'
            ], 404);
        }

        // Get the parent payment request information from tb_phieu_trinh_thanh_toan_homgiong
        $parentPaymentRequest = null;
        if (!empty($paymentRequest->ma_trinh_thanh_toan)) {
            $parentPaymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                ->where('ma_trinh_thanh_toan', $paymentRequest->ma_trinh_thanh_toan)
                ->first();
        }

        // Prepare response data with null checks for all properties
        $responseData = [
            'ma_giai_ngan' => $paymentRequest->ma_giai_ngan ?? '',
            'vu_dau_tu' => $paymentRequest->vu_dau_tu ?? '',
            'loai_thanh_toan' => $paymentRequest->loai_thanh_toan ?? '',
            'trang_thai_thanh_toan' => $paymentRequest->trang_thai_thanh_toan ?? '',
            'khach_hang_ca_nhan' => $paymentRequest->khach_hang_ca_nhan ?? '',
            'ma_khach_hang_ca_nhan' => $paymentRequest->ma_khach_hang_ca_nhan ?? '',
            'khach_hang_doanh_nghiep' => $paymentRequest->khach_hang_doanh_nghiep ?? '',
            'ma_khach_hang_doanh_nghiep' => $paymentRequest->ma_khach_hang_doanh_nghiep ?? '',
            'thuc_nhan' => $paymentRequest->thuc_nhan ?? 0,
            'tong_tien' => $paymentRequest->tong_tien ?? 0,
            'tong_tien_tam_giu' => $paymentRequest->tong_tien_tam_giu ?? 0,
            'tong_tien_khau_tru' => $paymentRequest->tong_tien_khau_tru ?? 0,
            'tong_tien_lai_suat' => $paymentRequest->tong_tien_lai_suat ?? 0,
            'tong_tien_thanh_toan_con_lai' => $paymentRequest->tong_tien_thanh_toan_con_lai ?? 0,
            'ngay_thanh_toan' => $paymentRequest->ngay_thanh_toan ?? '',
            'comment' => $paymentRequest->comment ?? '',
            'attachment_url' => $paymentRequest->attachment_url ?? '',
            'so_to_trinh' => $parentPaymentRequest->so_to_trinh ?? '',
            'so_dot_thanh_toan' => $parentPaymentRequest->so_dot_thanh_toan ?? '',
            'created_date' => $parentPaymentRequest->ngay_tao ?? '',
        ];

        return response()->json([
            'success' => true,
            'document' => $responseData
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching payment request homgiong details: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get phieu giao nhan hom giong data related to a payment request
 * 
 * @param string $id Disbursement code (ma_giai_ngan)
 * @return \Illuminate\Http\JsonResponse
 */
public function getphieugiaonhanhomgiong($id)
{
    try {
        // Check if the payment request exists
        $paymentRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_giai_ngan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu đề nghị thanh toán'
            ], 404);
        }
        
        // Get associated receipt IDs through logs_phieu_trinh_thanh_toan_homgiong
        $receiptIds = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_de_nghi_giai_ngan', $id)
            ->pluck('ma_so_phieu')
            ->toArray();
        
        if (empty($receiptIds)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'totals' => [
                    'total_thuc_nhan' => 0,
                    'total_amount' => 0
                ]
            ]);
        }
        
        // Get detailed information from bien_ban_nghiem_thu_hom_giong table
        $phieuGiaoNhanHG = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->select(
                'ma_so_phieu',
                'tram',
                'vu_dau_tu',
                'ten_phieu',
                'hop_dong_dau_tu_mia_ben_giao_hom',
                'khach_hang_ca_nhan',
                'khach_hang_doanh_nghiep',
                'tong_thuc_nhan',
                'tong_tien'
            )
            ->get();
        
        // Calculate the total amounts
        $totals = [
            'total_thuc_nhan' => $phieuGiaoNhanHG->sum('tong_thuc_nhan'),
            'total_amount' => $phieuGiaoNhanHG->sum('tong_tien')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $phieuGiaoNhanHG,
            'totals' => $totals
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching phieu giao nhan hom giong data: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
 * Get chi tiết giao nhan hom giong data related to a payment request
 * 
 * @param string $id Disbursement code (ma_giai_ngan)
 * @return \Illuminate\Http\JsonResponse
 */
public function getchitietgiaonhanhomgiong($id)
{
    try {
        // Check if the payment request exists
        $paymentRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_giai_ngan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu đề nghị thanh toán'
            ], 404);
        }
        
        // Get associated receipt IDs through logs_phieu_trinh_thanh_toan_homgiong
        $receiptIds = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_de_nghi_giai_ngan', $id)
            ->pluck('ma_so_phieu')
            ->toArray();
        
        if (empty($receiptIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No receipts found for this payment request',
                'data' => [],
                'totals' => [
                    'total_thuc_nhan' => 0,
                    'total_thanh_tien' => 0,
                ]
            ]);
        }
        
        // Get detailed information from tb_chitiet_giaonhan_homgiong table
        $chitietGiaoNhanHG = DB::table('tb_chitiet_giaonhan_homgiong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->select(
                'ma_so_phieu',
                'tram',
                'phieu_gn_hom_giong',
                'giong_mia',
                'thua_dat',
                'don_vi_tinh',
                'thuc_nhan',
                'don_gia',
                'thanh_tien_hom_giong'
            )
            ->get();
        
        // Calculate the total amounts
        $totals = [
            'total_thuc_nhan' => $chitietGiaoNhanHG->sum('thuc_nhan'),
            'total_thanh_tien' => $chitietGiaoNhanHG->sum('thanh_tien_hom_giong')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $chitietGiaoNhanHG,
            'totals' => $totals
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching chi tiết giao nhan hom giong data: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}


/**
* Update specific fields of the payment request for homgiong
*
* @param Request $request
* @param string $id
* @return \Illuminate\Http\JsonResponse
*/
public function updateDocumentHomgiong(Request $request, $id)
{
   try {
       // Validate request - remove ma_giai_ngan requirement
       $validated = $request->validate([
           'comment' => 'nullable|string',
       ]);
       
       // Begin transaction
       DB::beginTransaction();
       
       // Find the payment request
       $paymentRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
           ->where('ma_giai_ngan', $id)
           ->first();
       
       if (!$paymentRequest) {
           DB::rollBack();
           return response()->json([
               'message' => 'Payment request not found'
           ], 404);
       }
       
       // Prepare update data - include only provided fields
       $updateData = [];
       
       // Only include comment if it's provided
       if (isset($validated['comment'])) {
           $updateData['comment'] = $validated['comment'];
       }
       
       // Only update ngay_thanh_toan if it's provided
       if (isset($validated['ngay_thanh_toan'])) {
           $updateData['ngay_thanh_toan'] = $validated['ngay_thanh_toan'];
       }
       
       // Simple update
       DB::table('tb_de_nghi_thanhtoan_homgiong')
           ->where('ma_giai_ngan', $id)
           ->update($updateData);
       
       DB::commit();
       
       return response()->json([
        'success' => true,
           'message' => 'Payment request updated successfully'

       ]);
       
   } catch (\Exception $e) {
       DB::rollBack();
       
       return response()->json([
           'message' => 'Error updating payment request: ' . $e->getMessage()
       ], 500);
   }
}

/**
 * Check access permission for payment request details homgiong
 *
 * @param string $id
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function checkAccessHomgiong($id, Request $request)
{
    try {
        // Get user type and authentication info from headers set by JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        $userId = $request->header('X-User-ID');
        
        \Log::info('Check access for payment request homgiong details:', [
            'payment_request_id' => $id,
            'userType' => $userType,
            'userId' => $userId
        ]);
        
        // ค้นหาเอกสารที่ต้องการเข้าถึง
        $document = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_giai_ngan', $id)
            ->first();
            
        if (!$document) {
            return response()->json([
                'hasAccess' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        // ถ้าเป็น employee ให้เข้าถึงได้ทั้งหมด
        if ($userType === 'employee') {
            return response()->json([
                'hasAccess' => true,
                'userType' => 'employee'
            ]);
        }
        
        // ถ้าเป็น farmer ให้ตรวจสอบสิทธิ์การเข้าถึงตาม customer ID
        if ($userType === 'farmer') {
            // ดึงข้อมูล farmer จาก user_farmer table
            $farmer = DB::table('user_farmer')
                ->where('id', $userId)
                ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                ->first();
                
            if (!$farmer) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Farmer data not found'
                ], 404);
            }
            
            // ตรวจสอบว่า farmer มีสิทธิ์เข้าถึงเอกสารนี้หรือไม่
            $hasAccess = false;
            
            // ตรวจสอบ ma_khach_hang_ca_nhan
            if (!empty($farmer->ma_kh_ca_nhan) && 
                $document->ma_khach_hang_ca_nhan === $farmer->ma_kh_ca_nhan) {
                $hasAccess = true;
            }
            
            // ตรวจสอบ ma_khach_hang_doanh_nghiep
            if (!empty($farmer->ma_kh_doanh_nghiep) && 
                $document->ma_khach_hang_doanh_nghiep === $farmer->ma_kh_doanh_nghiep) {
                $hasAccess = true;
            }
            
            return response()->json([
                'hasAccess' => $hasAccess,
                'userType' => 'farmer',
                'debug_info' => [
                    'farmer_ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan,
                    'farmer_ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep,
                    'document_ma_kh_ca_nhan' => $document->ma_khach_hang_ca_nhan,
                    'document_ma_kh_doanh_nghiep' => $document->ma_khach_hang_doanh_nghiep
                ]
            ]);
        }
        
        // กรณีอื่นๆ ไม่อนุญาต
        return response()->json([
            'hasAccess' => false,
            'message' => 'Access denied'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error checking payment request homgiong access: ' . $e->getMessage(), [
            'payment_request_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'hasAccess' => false,
            'message' => 'Error checking access',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Timeline giải ngân trang DNTT Hom Giong
 * Get processing history for disbursement request (homgiong)
 *
 * @param string $id Disbursement code (ma_giai_ngan)
 * @return \Illuminate\Http\JsonResponse
 */
public function getDisbursementProcessingHistoryHomgiong($id)
{
    try {
        // First, get the ma_trinh_thanh_toan from the disbursement request homgiong
        $disbursementRequest = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where('ma_giai_ngan', $id)
            ->select('ma_trinh_thanh_toan')
            ->first();
        
        if (!$disbursementRequest || !$disbursementRequest->ma_trinh_thanh_toan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu đề nghị thanh toán hom giống hoặc không có mã trình thanh toán',
                'history' => [],
                'payment_code' => null
            ]);
        }
        
        // Now fetch the history using the ma_trinh_thanh_toan from homgiong action table
        $history = DB::table('action_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $disbursementRequest->ma_trinh_thanh_toan)
            ->orderBy('action_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'history' => $history,
            'payment_code' => $disbursementRequest->ma_trinh_thanh_toan
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching disbursement processing history homgiong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Lỗi khi tải lịch sử xử lý phiếu đề nghị thanh toán hom giống'
        ], 500);
    }
}

/**
 * Generate Report PDF for Payment Request Hom Giong
 */
public function generateReportTableDNTTHG(Request $request)
{
    try {
        // Handle authentication for GET requests with token in query parameter
        if ($request->isMethod('get') && $request->has('token')) {
            $token = $request->query('token');
            
            try {
                \JWTAuth::setToken($token);
                $user = \JWTAuth::authenticate();
                
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid token'
                    ], 401);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token authentication failed: ' . $e->getMessage()
                ], 401);
            }
        }

        // Handle both GET and POST requests
        if ($request->isMethod('get')) {
            $filterParamsJson = $request->query('filter_params');
            if (!$filterParamsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid filter parameters'
                ], 400);
            }
            $filterParams = json_decode($filterParamsJson, true);
        } else {
            $request->validate([
                'filter_params' => 'required|string'
            ]);
            $filterParams = json_decode($request->filter_params, true);
        }
        
        if (!$filterParams || !isset($filterParams['ma_giai_ngan_list'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filter parameters'
            ], 400);
        }

        $maGiaiNganList = $filterParams['ma_giai_ngan_list'];
        
        if (empty($maGiaiNganList)) {
            return response()->json([
                'success' => false,
                'message' => 'No records to generate report'
            ], 400);
        }

        // Log for debugging large datasets
        if (count($maGiaiNganList) > 100) {
            Log::info('Generating large report for Hom Giong', [
                'record_count' => count($maGiaiNganList),
                'report_type' => $filterParams['report_type'] ?? 'unknown'
            ]);
        }

        // Build the main query สำหรับ Hom Giong - แก้ไข orderBy
        $query = DB::table('tb_de_nghi_thanhtoan_homgiong as dngn')
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'dngn.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->whereIn('dngn.ma_giai_ngan', $maGiaiNganList)
            ->select([
                'dngn.ma_giai_ngan',
                'dngn.khach_hang_ca_nhan',
                'dngn.khach_hang_doanh_nghiep', 
                'dngn.ma_khach_hang_ca_nhan',
                'dngn.ma_khach_hang_doanh_nghiep',
                'dngn.thuc_nhan',
                'dngn.tong_tien',
                'dngn.tong_tien_tam_giu',
                'dngn.tong_tien_khau_tru',
                'dngn.tong_tien_lai_suat',
                'dngn.tong_tien_thanh_toan_con_lai',
                'dngn.vu_dau_tu',
                'dngn.ngay_thanh_toan',
                'pttt.so_dot_thanh_toan',
                'pttt.trang_thai_thanh_toan'
            ])
            ->orderBy('dngn.ma_giai_ngan', 'asc'); // เปลี่ยนจาก created_at เป็น ma_giai_ngan

        $reportData = $query->get();

        if ($reportData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected records'
            ], 404);
        }

        // Process each record to format data for Hom Giong
        $processedData = [];
        
        foreach ($reportData as $record) {
            // Get customer name (Priority: Individual -> Corporate)
            $doiTacNhanTien = $record->khach_hang_ca_nhan ?: $record->khach_hang_doanh_nghiep;
            
            // Get customer code (Priority: Individual -> Corporate)  
            $maKhachHang = $record->ma_khach_hang_ca_nhan ?: $record->ma_khach_hang_doanh_nghiep;
            
            // Format payment status for report display
            $trangThaiThanhToan = $this->formatPaymentStatusForReport($record->trang_thai_thanh_toan);

            $processedData[] = [
                'ma_giai_ngan' => $record->ma_giai_ngan ?: 'N/A',
                'doi_tac_nhan_tien' => $doiTacNhanTien ?: 'N/A',
                'ma_khach_hang' => $maKhachHang ?: 'N/A',
                'tong_thuc_nhan' => $record->thuc_nhan ?: 0,
                'tong_tien' => $record->tong_tien ?: 0,
                'tong_tien_tam_giu' => $record->tong_tien_tam_giu ?: 0,
                'tong_tien_khau_tru' => $record->tong_tien_khau_tru ?: 0,
                'tong_tien_lai_suat' => $record->tong_tien_lai_suat ?: 0,
                'tong_tien_thanh_toan_con_lai' => $record->tong_tien_thanh_toan_con_lai ?: 0,
                'vu_dau_tu' => $record->vu_dau_tu ?: 'N/A',
                'ngay_thanh_toan' => $record->ngay_thanh_toan ?: null,
                'so_dot_thanh_toan' => $record->so_dot_thanh_toan ?: 'N/A',
                'trang_thai_thanh_toan' => $trangThaiThanhToan
            ];
        }

        // Generate report info with enhanced details
        $reportInfo = [
            'title' => 'Báo cáo Phiếu đề nghị thanh toán hom giống',
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($processedData),
            'report_type' => $filterParams['report_type'] ?? 'selected_items',
            'report_type_display' => $this->getReportTypeDisplay($filterParams['report_type'] ?? 'selected_items'),
            'generated_by' => $filterParams['generated_by'] ?? 'Hệ thống',
            'filter_summary' => $this->buildFilterSummary($filterParams)
        ];

        // Return view for printing (sử้ template ReportDNTTHG.blade.php)
        return view('Print.ReportDNTTHG', [
            'reportData' => $processedData,
            'reportInfo' => $reportInfo
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid request data',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error generating DNTT Hom Giong report: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error generating report: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get display text for report type
 */
private function getReportTypeDisplay($reportType)
{
    $types = [
        'selected_items' => 'Mục đã chọn',
        'all_data' => 'Tất cả dữ liệu',
        'current_page' => 'Trang hiện tại'
    ];

    return $types[$reportType] ?? 'Không xác định';
}

/**
 * Build filter summary for report
 */
private function buildFilterSummary($filterParams)
{
    $summary = [];
    
    if (isset($filterParams['current_filters'])) {
        $filters = $filterParams['current_filters'];
        
        if (!empty($filters['search'])) {
            $summary[] = "Tìm kiếm: " . $filters['search'];
        }
        
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $summary[] = "Trạng thái: " . $filters['status'];
        }
    }
    
    return !empty($summary) ? implode(', ', $summary) : 'Không có bộ lọc';
}

/**
 * Format payment status for report display (สำหรับ Hom Giong)
 */
private function formatPaymentStatusForReport($status)
{
    if (!$status) {
        return 'Chưa thanh toán';
    }

    $statusMap = [
        'processing' => 'Đang xử lý',
        'submitted' => 'Đã nộp kế toán', 
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
        'rejected' => 'Từ chối'
    ];

    return $statusMap[$status] ?? $status;
}

}