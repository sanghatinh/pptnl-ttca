<?php

namespace App\Http\Controllers\QuanlyCongno;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuanlyCongno\DeductibleServiceDebt;
use App\Models\Quanlytaichinh\Phieuthunodichvu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DeductibleServiceDebtController extends Controller
{
    /**
     * Display a listing of the deductible service debts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    try {
        // Check if client requests all data (for client-side processing)
        if ($request->has('all') && $request->all) {
            // Get all data without pagination
            $query = DeductibleServiceDebt::query();
            
            // Apply basic filters if provided (optional for security/performance)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('invoicenumber', 'like', "%{$search}%")
                      ->orWhere('vu_dau_tu', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('tram', 'like', "%{$search}%")
                      ->orWhere('khach_hang_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('khach_hang_doanh_nghiep', 'like', "%{$search}%");
                });
            }
            
            // Apply sorting
            $sortField = $request->input('sort_by', 'ngay_phat_sinh');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);
            
            // Limit data size if needed (optional for performance)
            // $query->limit(5000); // Uncomment if you want to set a maximum limit
            
            $allData = $query->get();

            // Get unique values for filters
            $uniqueFilters = [
                'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
                'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
                'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
                'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
                'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
                'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
                'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
            ];
            
            // Calculate overall totals
            $totals = [
                'total_debt' => $allData->sum('so_tien_no_goc_da_quy'),
                'total_paid' => $allData->sum('da_tra_goc'),
                'total_remaining' => $allData->sum('so_tien_con_lai'),
                'total_interest' => $allData->sum('tien_lai')
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'All data retrieved successfully',
                'data' => $allData,
                'unique_filters' => $uniqueFilters,
                'totals' => $totals
            ]);
        } else {
            // Original paginated code for server-side processing
            $query = DeductibleServiceDebt::query();
            
            // [Existing filter code remains the same]

            // Paginate the results
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            
            $debts = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Get unique values for filters
            $uniqueFilters = [
                'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
                'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
                'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
                'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
                'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
                'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
                'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
            ];
            
            // Calculate totals
            $totals = [
                'total_debt' => $query->sum('so_tien_no_goc_da_quy'),
                'total_paid' => $query->sum('da_tra_goc'),
                'total_remaining' => $query->sum('so_tien_con_lai'),
                'total_interest' => $query->sum('tien_lai')
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'data' => $debts,
                'unique_filters' => $uniqueFilters,
                'totals' => $totals
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Error fetching deductible service debts: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve data: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

private function getUniqueValues()
{
    // ใช้ Cache เพื่อไม่ต้อง query ใหม่ทุกครั้ง
    return \Cache::remember('deductible_service_unique_values', 3600, function() {
        return [
            'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
            'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
            'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
            'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
            'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
            'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
            'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
        ];
    });
}



/**
 * Start the import process
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function startImport(Request $request)
{
    try {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        // Generate a unique import ID
        $importId = Str::uuid()->toString();
        
        // Store initial import data in cache
        $importData = [
            'processed' => 0,
            'total' => 0,
            'success' => true,
            'finished' => false,
            'errors' => []
        ];
        
        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        
        // ดึงไฟล์ที่อัปโหลด
        $file = $request->file('file');
        
        // ตรวจสอบว่าไฟล์ถูกอัพโหลดอย่างถูกต้อง
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid upload file'
            ], 400);
        }
        
        // สร้างชื่อไฟล์ชั่วคราว
        $fileName = $importId . '.' . $file->getClientOriginalExtension();
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        
        // ย้ายไฟล์ไปยังโฟลเดอร์ temporary ของระบบโดยตรง
        if (!copy($file->getRealPath(), $tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy file to temporary directory'
            ], 500);
        }
        
        // ตรวจสอบว่าไฟล์สามารถเข้าถึงได้หรือไม่
        if (!file_exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File stored but not accessible: ' . $tempPath
            ], 500);
        }
        
        // Process the file in background using the temporary path
        dispatch(function() use ($tempPath, $importId) {
            try {
                // ตรวจสอบไฟล์อีกครั้งก่อนเริ่มประมวลผลในพื้นหลัง
                if (!file_exists($tempPath)) {
                    throw new \Exception("Temp file not found: $tempPath");
                }
                
                $this->processImportFile($tempPath, $importId);
            } catch (\Exception $e) {
                Log::error('Error in dispatch callback: ' . $e->getMessage());
                $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
                if ($importData) {
                    $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
                    $importData['success'] = false;
                    $importData['finished'] = true;
                    Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
                }
            } finally {
                // ลบไฟล์ชั่วคราวหลังจากประมวลผลเสร็จ
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
        })->afterResponse();
        
        return response()->json([
            'success' => true,
            'message' => 'Import process started',
            'import_id' => $importId
        ]);
    } catch (\Exception $e) {
        Log::error('Error starting import process: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error starting import process: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Process the uploaded file
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string $importId
 */
private function processImportFile($filePath, $importId)
{
    try {
        // ตรวจสอบว่าไฟล์มีอยู่จริงหรือไม่
        if (!file_exists($filePath)) {
            throw new \Exception("File \"$filePath\" does not exist.");
        }
        
        // Log file information for debugging
        Log::info('Starting to process import file', [
            'path' => $filePath,
            'exists' => file_exists($filePath),
            'size' => file_exists($filePath) ? filesize($filePath) : 'N/A',
            'import_id' => $importId
        ]);
        
        // Load the file using the file path
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // First row is headers
        $headers = array_shift($rows);
        
        // Map Excel column headers to database fields
        $columnMapping = $this->getColumnMapping($headers);
        
        // Update total count
        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        $importData['total'] = count($rows);
        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        
        // Process rows
        $processedCount = 0;
        $errors = [];
        
        foreach ($rows as $rowIndex => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                $rowData = [];
                
                // Map data from Excel columns to database fields
                foreach ($columnMapping as $dbField => $excelIndex) {
                    if ($excelIndex !== null) {
                        // Handle special case for date fields
                        if ($dbField === 'ngay_phat_sinh' && !empty($row[$excelIndex])) {
                            // Try to convert Excel date to Y-m-d format
                            if (is_numeric($row[$excelIndex])) {
                                // Excel date stored as numeric value
                                $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$excelIndex]);
                                $rowData[$dbField] = $dateValue->format('Y-m-d');
                            } else {
                                // Try to parse as date string
                                try {
                                    $date = new \DateTime($row[$excelIndex]);
                                    $rowData[$dbField] = $date->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $rowData[$dbField] = null;
                                    $errors[] = "Row " . ($rowIndex + 2) . ": Invalid date format in 'Ngay_Phat_Sinh' column";
                                }
                            }
                        } else {
                            $rowData[$dbField] = $row[$excelIndex];
                        }
                    }
                }
                
                // Validate numeric fields
                $numericFields = [
                    'ty_gia_quy_doi', 'so_tien_theo_gia_tri_dau_tu', 'so_tien_no_goc_da_quy', 
                    'da_tra_goc', 'so_tien_con_lai', 'tien_lai', 'lai_suat'
                ];
                
                foreach ($numericFields as $field) {
                    if (isset($rowData[$field])) {
                        // Convert from formatted string (e.g., "1,234.56") to numeric
                        $rowData[$field] = preg_replace('/[^0-9.-]/', '', $rowData[$field]);
                        
                        // If empty after removing non-numeric chars, set to 0
                        if ($rowData[$field] === '' || $rowData[$field] === null) {
                            $rowData[$field] = 0;
                        }
                    }
                }
                
                // Check for required fields - ใช้ field ที่ถูกต้องตามฐานข้อมูล 'invoicenumber'
                $requiredFields = ['tram', 'invoicenumber', 'vu_dau_tu', 'so_tien_no_goc_da_quy'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (empty($rowData[$field])) {
                        $missingFields[] = $field;
                    }
                }
                
                if (!empty($missingFields)) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Missing required fields: " . implode(', ', $missingFields);
                    continue;
                }
                
                try {
                    // Check if record exists by invoicenumber
                    $exists = DeductibleServiceDebt::where('invoicenumber', $rowData['invoicenumber'])->exists();
                    
                    if ($exists) {
                        // แก้ไขเป็นการใช้ query builder เพื่อหลีกเลี่ยงปัญหา primary key
                        DeductibleServiceDebt::where('invoicenumber', $rowData['invoicenumber'])
                            ->update($rowData);
                    } else {
                        // Create new record
                        DeductibleServiceDebt::create($rowData);
                    }
                    
                    $processedCount++;
                    
                    // Update progress every 10 records
                    if ($processedCount % 10 === 0) {
                        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
                        $importData['processed'] = $processedCount;
                        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Database error: " . $e->getMessage();
                    Log::error("Database error processing row " . ($rowIndex + 2) . ": " . $e->getMessage());
                }
                
            } catch (\Exception $e) {
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                Log::error("Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage());
            }
        }
        
        // Clear cache to ensure fresh data after import
        Cache::forget('deductible_service_unique_values');
        
        // Update final status
        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['finished'] = true;
        $importData['success'] = $processedCount > 0; // ตั้งเป็น true ถ้าอย่างน้อยนำเข้าได้ 1 รายการ
        
        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        
    } catch (\Exception $e) {
        Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        
        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        if ($importData) {
            $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
            $importData['success'] = false;
            $importData['finished'] = true;
            Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        } else {
            // ถ้าไม่พบข้อมูลใน Cache สร้างใหม่
            Cache::put('import_congno_dichvu_khautru_' . $importId, [
                'processed' => 0,
                'total' => 0,
                'success' => false,
                'finished' => true,
                'errors' => [$e->getMessage()]
            ], 3600);
        }
    }
}

/**
 * Map columns from Excel/CSV headers to database columns
 */
private function getColumnMapping($headers)
{
    // Define mapping from common header names to database columns
    $possibleHeaderMappings = [
        'tram' => ['tram', 'Trạm', 'TRAM', 'station', 'Station', 'STATION'],
        'invoicenumber' => ['Invoice Number', 'Invoice_Number', 'invoicenumber', 'ma_hoa_don', 'invoice', 'so_hoa_don'],
        'vu_dau_tu' => ['Vụ đầu tư', 'Vu_Dau_Tu', 'VuDauTu', 'investment_project', 'crop'],
        'category_debt' => ['category_debt', 'Category_Debt', 'loai_no', 'debt_type'],
        'description' => ['description', 'Description', 'mo_ta', 'note', 'ghi_chu'],
        'ngay_phat_sinh' => ['Ngày phát sinh', 'Ngay_Phat_Sinh', 'date', 'transaction_date'],
        'loai_tien' => ['Loại tiền', 'Loai_Tien', 'currency', 'don_vi_tien', 'currency_type'],
        'ty_gia_quy_doi' => ['Tỷ giá quy đổi', 'Ty_Gia_Quy_Doi', 'exchange_rate', 'ti_gia'],
        'so_tien_theo_gia_tri_dau_tu' => ['Số tiền theo giá trị đầu tư', 'So_Tien_Theo_Gia_Tri_Dau_Tu', 'investment_value'],
        'so_tien_no_goc_da_quy' => ['Số tiền nợ gốc đã quy', 'So_Tien_No_Goc_Da_Quy', 'principal_amount', 'so_tien_no_goc'],
        'da_tra_goc' => ['Đã trả gốc', 'Da_Tra_Goc', 'paid_amount', 'paid_principal', 'so_tien_da_tra'],
        'so_tien_con_lai' => ['Số tiền còn lại', 'So_Tien_Con_Lai', 'remaining_amount', 'so_tien_du_no'],
        'tien_lai' => ['Tiền lãi', 'Tien_Lai', 'interest_amount', 'lai'],
        'ma_khach_hang_ca_nhan' => ['Mã khách hàng cá nhân', 'Ma_Khach_Hang_Ca_Nhan', 'individual_customer_code'],
        'khach_hang_ca_nhan' => ['Khách hàng cá nhân', 'Khach_Hang_Ca_Nhan', 'individual_customer_name'],
        'ma_khach_hang_doanh_nghiep' => ['Mã Khách hàng doanh nghiệp', 'Ma_Khach_Hang_Doanh_Nghiep', 'business_customer_code'],
        'khach_hang_doanh_nghiep' => ['Khách hàng doanh nghiệp', 'Khach_Hang_Doanh_Nghiep', 'business_customer_name'],
        'lai_suat' => ['Lãi suất', 'Lai_Suat', 'interest_rate', 'rate'],
        'loai_lai_suat' => ['Loại lãi suất', 'Loai_Lai_Suat', 'interest_type', 'rate_type'],
        'vu_thanh_toan' => ['Vụ thanh toán', 'Vu_Thanh_Toan', 'payment_crop', 'payment_season'],
        'loai_dau_tu' => ['Loại đầu tư', 'Loai_Dau_Tu', 'investment_type', 'type'],
        'ma_nhan_vien' => ['Mã nhân viên']
    ];
    
    $columnMapping = [];
    
    // Initialize all database fields to null
    foreach ($possibleHeaderMappings as $dbField => $possibleHeaders) {
        $columnMapping[$dbField] = null;
    }
    
    // Find matching headers
    foreach ($headers as $colIndex => $header) {
        $header = trim($header);
        
        // Skip empty headers
        if (empty($header)) {
            continue;
        }
        
        foreach ($possibleHeaderMappings as $dbField => $possibleHeaders) {
            if (in_array($header, $possibleHeaders, true)) {
                $columnMapping[$dbField] = $colIndex;
                break;
            }
        }
    }
    
    return $columnMapping;
}

/**
 * Check import progress
 *
 * @param Request $request
 * @param string $importId
 * @return \Illuminate\Http\JsonResponse
 */
public function checkImportProgress($importId)
{
    $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
    
    if (!$importData) {
        return response()->json([
            'success' => false,
            'message' => 'Import process not found or expired'
        ], 404);
    }
    
    return response()->json($importData);
}



/**
 * Show the specified debt details by invoicenumber.
 *
 * @param  string  $invoicenumber
 * @return \Illuminate\Http\Response
 */
public function showDetails($invoicenumber)
{
    try {
        // Fetch debt details
        $debt = DB::table('deductible_service_debt')
            ->where('invoicenumber', $invoicenumber)
            ->first();
            
        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin công nợ.'
            ], 404);
        }
        
        // Fetch payment history with additional information
        $paymentHistory = DB::table('Logs_Phieu_Tinh_Lai_dv as l')
            ->where('l.Invoice_Number_Phan_Bo_Dau_Tu', $invoicenumber)
            ->leftJoin('tb_phieu_trinh_thanh_toan as pt', 'l.So_Tro_Trinh', '=', 'pt.so_to_trinh')
            ->leftJoin('tb_de_nghi_thanhtoan_dv as dv', function($join) use ($debt) {
                $join->on('pt.ma_trinh_thanh_toan', '=', 'dv.ma_trinh_thanh_toan');
                
                // Choose the correct customer ID field based on what's available in the debt record
                if (!empty($debt->ma_khach_hang_ca_nhan)) {
                    $join->where('dv.ma_khach_hang_ca_nhan', '=', $debt->ma_khach_hang_ca_nhan);
                } else if (!empty($debt->ma_khach_hang_doanh_nghiep)) {
                    $join->where('dv.ma_khach_hang_doanh_nghiep', '=', $debt->ma_khach_hang_doanh_nghiep);
                }
            })
            ->select(
                'l.Ma_So_Phieu_PDN_Thu_No as receipt_code',
                'l.Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
                'l.Da_Tra_Goc as principal_paid',
                'l.Tien_Lai as interest_paid',
                'l.Ngay_Tra as payment_date',
                'l.Category_Debt as category_debt',
                'l.So_Tro_Trinh as proposal_number', // Added field for Số tờ trình
                'dv.ma_giai_ngan as disbursement_code' // Added field for Mã giải ngân
            )
            ->orderBy('l.Ngay_Tra', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'document' => $debt,
            'paymentHistory' => $paymentHistory
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error retrieving debt details: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi tải dữ liệu công nợ.',
            'error' => $e->getMessage()
        ], 500);
    }
}


}