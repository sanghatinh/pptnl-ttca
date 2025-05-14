<?php

namespace App\Http\Controllers\QuanlyCongno;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeductibleServiceDebt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        
        // Process the file in the background
        dispatch(function() use ($request, $importId) {
            $this->processImportFile($request->file('file'), $importId);
        })->afterResponse();
        
        return response()->json([
            'success' => true,
            'message' => 'Import process started',
            'import_id' => $importId
        ]);
    } catch (\Exception $e) {
        Log::error('Error starting import process: ' . $e->getMessage());
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
private function processImportFile($file, $importId)
{
    try {
        $spreadsheet = IOFactory::load($file);
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
                
                // Check for required fields
                $requiredFields = ['tram', 'invoice_number', 'vu_dau_tu', 'so_tien_no_goc_da_quy'];
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
                
                // Check if record exists by invoice_number
                $existingRecord = DeductibleServiceDebt::where('invoice_number', $rowData['invoice_number'])->first();
                
                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update($rowData);
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
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                Log::error("Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage());
            }
        }
        
        // Update final status
        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['finished'] = true;
        
        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        
    } catch (\Exception $e) {
        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
        $importData['success'] = false;
        $importData['finished'] = true;
        Cache::put('import_congno_dichvu_khautru_' . $importId, $importData, 3600);
        
        Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

/**
 * Map columns from Excel/CSV headers to database columns
 */
private function getColumnMapping($headers)
{
    // Define mapping from common header names to database columns
    $possibleHeaderMappings = [
        'tram' => ['tram', 'Tram', 'TRAM', 'station', 'Station', 'STATION'],
        'invoice_number' => ['invoice_number', 'Invoice_Number', 'invoicenumber', 'ma_hoa_don', 'invoice', 'so_hoa_don'],
        'vu_dau_tu' => ['vu_dau_tu', 'Vu_Dau_Tu', 'VuDauTu', 'investment_project', 'crop'],
        'category_debt' => ['category_debt', 'Category_Debt', 'loai_no', 'debt_type'],
        'description' => ['description', 'Description', 'mo_ta', 'note', 'ghi_chu'],
        'ngay_phat_sinh' => ['ngay_phat_sinh', 'Ngay_Phat_Sinh', 'date', 'transaction_date'],
        'loai_tien' => ['loai_tien', 'Loai_Tien', 'currency', 'don_vi_tien', 'currency_type'],
        'ty_gia_quy_doi' => ['ty_gia_quy_doi', 'Ty_Gia_Quy_Doi', 'exchange_rate', 'ti_gia'],
        'so_tien_theo_gia_tri_dau_tu' => ['so_tien_theo_gia_tri_dau_tu', 'So_Tien_Theo_Gia_Tri_Dau_Tu', 'investment_value'],
        'so_tien_no_goc_da_quy' => ['so_tien_no_goc_da_quy', 'So_Tien_No_Goc_Da_Quy', 'principal_amount', 'so_tien_no_goc'],
        'da_tra_goc' => ['da_tra_goc', 'Da_Tra_Goc', 'paid_amount', 'paid_principal', 'so_tien_da_tra'],
        'so_tien_con_lai' => ['so_tien_con_lai', 'So_Tien_Con_Lai', 'remaining_amount', 'so_tien_du_no'],
        'tien_lai' => ['tien_lai', 'Tien_Lai', 'interest_amount', 'lai'],
        'ma_khach_hang_ca_nhan' => ['ma_khach_hang_ca_nhan', 'Ma_Khach_Hang_Ca_Nhan', 'individual_customer_code'],
        'khach_hang_ca_nhan' => ['khach_hang_ca_nhan', 'Khach_Hang_Ca_Nhan', 'individual_customer_name'],
        'ma_khach_hang_doanh_nghiep' => ['ma_khach_hang_doanh_nghiep', 'Ma_Khach_Hang_Doanh_Nghiep', 'business_customer_code'],
        'khach_hang_doanh_nghiep' => ['khach_hang_doanh_nghiep', 'Khach_Hang_Doanh_Nghiep', 'business_customer_name'],
        'lai_suat' => ['lai_suat', 'Lai_Suat', 'interest_rate', 'rate'],
        'loai_lai_suat' => ['loai_lai_suat', 'Loai_Lai_Suat', 'interest_type', 'rate_type'],
        'vu_thanh_toan' => ['vu_thanh_toan', 'Vu_Thanh_Toan', 'payment_crop', 'payment_season'],
        'loai_dau_tu' => ['loai_dau_tu', 'Loai_Dau_Tu', 'investment_type', 'type']
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

}