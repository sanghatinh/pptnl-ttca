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
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth; // เพิ่ม import นี้

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
            // ดึงข้อมูล user type และ user ID จาก headers ที่ JwtMiddleware ส่งมา
            $userType = $request->header('X-User-Type');
            $userId = $request->header('X-User-ID');
            
            // Initialize base query
            $query = DeductibleServiceDebt::query();
            
            // Apply user-based filtering
            if ($userType === 'farmer' && $userId) {
                // ดึงข้อมูล farmer จาก user_farmer table
                $farmer = DB::table('user_farmer')
                    ->where('id', $userId)
                    ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                    ->first();
                    
                if ($farmer) {
                    $query->where(function($q) use ($farmer) {
                        // Filter by individual customer ID if exists
                        if (!empty($farmer->ma_kh_ca_nhan)) {
                            $q->where('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                        }
                        // Filter by business customer ID if exists
                        if (!empty($farmer->ma_kh_doanh_nghiep)) {
                            $q->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                        }
                    });
                } else {
                    // If farmer not found, return empty result with proper structure
                    return response()->json([
                        'status' => 'success',
                        'message' => 'No data found for this farmer',
                        'data' => [],
                        'unique_filters' => [
                            'tram' => [],
                            'vu_dau_tu' => [],
                            'category_debt' => [],
                            'loai_tien' => [],
                            'loai_lai_suat' => [],
                            'vu_thanh_toan' => [],
                            'loai_dau_tu' => [],
                        ],
                        'totals' => [
                            'total_debt' => 0,
                            'total_paid' => 0,
                            'total_remaining' => 0,
                            'total_interest' => 0
                        ]
                    ]);
                }
            }
            // สำหรับ employee ไม่ต้องกรองข้อมูล (แสดงทั้งหมด)
            
            // Check if client requests all data (for client-side processing)
            if ($request->has('all') && $request->all) {
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
                
                // Get all filtered data
                $allData = $query->get();

                // Get unique values for filters (ปรับปรุงให้กรองตาม user type ด้วย)
                $uniqueTram = DeductibleServiceDebt::query();
                $uniqueVuDauTu = DeductibleServiceDebt::query();
                $uniqueCategoryDebt = DeductibleServiceDebt::query();
                $uniqueLoaiTien = DeductibleServiceDebt::query();
                $uniqueLoaiLaiSuat = DeductibleServiceDebt::query();
                $uniqueVuThanhToan = DeductibleServiceDebt::query();
                $uniqueLoaiDauTu = DeductibleServiceDebt::query();

                // ถ้าเป็น farmer ให้กรองข้อมูล unique values ด้วย
                if ($userType === 'farmer' && isset($farmer)) {
                    $filterCondition = function($query) use ($farmer) {
                        $query->where(function($q) use ($farmer) {
                            if (!empty($farmer->ma_kh_ca_nhan)) {
                                $q->where('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                            }
                            if (!empty($farmer->ma_kh_doanh_nghiep)) {
                                $q->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                            }
                        });
                    };

                    $uniqueTram = $uniqueTram->where($filterCondition);
                    $uniqueVuDauTu = $uniqueVuDauTu->where($filterCondition);
                    $uniqueCategoryDebt = $uniqueCategoryDebt->where($filterCondition);
                    $uniqueLoaiTien = $uniqueLoaiTien->where($filterCondition);
                    $uniqueLoaiLaiSuat = $uniqueLoaiLaiSuat->where($filterCondition);
                    $uniqueVuThanhToan = $uniqueVuThanhToan->where($filterCondition);
                    $uniqueLoaiDauTu = $uniqueLoaiDauTu->where($filterCondition);
                }

                $uniqueFilters = [
                    'tram' => $uniqueTram->distinct()->pluck('tram')->filter()->values(),
                    'vu_dau_tu' => $uniqueVuDauTu->distinct()->pluck('vu_dau_tu')->filter()->values(),
                    'category_debt' => $uniqueCategoryDebt->distinct()->pluck('category_debt')->filter()->values(),
                    'loai_tien' => $uniqueLoaiTien->distinct()->pluck('loai_tien')->filter()->values(),
                    'loai_lai_suat' => $uniqueLoaiLaiSuat->distinct()->pluck('loai_lai_suat')->filter()->values(),
                    'vu_thanh_toan' => $uniqueVuThanhToan->distinct()->pluck('vu_thanh_toan')->filter()->values(),
                    'loai_dau_tu' => $uniqueLoaiDauTu->distinct()->pluck('loai_dau_tu')->filter()->values(),
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
                // Apply additional filters for paginated results
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
                
                // Paginate the results
                $perPage = $request->input('per_page', 15);
                $page = $request->input('page', 1);
                
                $debts = $query->paginate($perPage, ['*'], 'page', $page);
                
                // Get unique values for filters (ปรับปรุงให้กรองตาม user type ด้วย)
                $baseQuery = DeductibleServiceDebt::query();
                if ($userType === 'farmer' && isset($farmer)) {
                    $baseQuery->where(function($q) use ($farmer) {
                        if (!empty($farmer->ma_kh_ca_nhan)) {
                            $q->where('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                        }
                        if (!empty($farmer->ma_kh_doanh_nghiep)) {
                            $q->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                        }
                    });
                }
                
                $uniqueFilters = [
                    'tram' => $baseQuery->distinct()->pluck('tram')->filter()->values(),
                    'vu_dau_tu' => $baseQuery->distinct()->pluck('vu_dau_tu')->filter()->values(),
                    'category_debt' => $baseQuery->distinct()->pluck('category_debt')->filter()->values(),
                    'loai_tien' => $baseQuery->distinct()->pluck('loai_tien')->filter()->values(),
                    'loai_lai_suat' => $baseQuery->distinct()->pluck('loai_lai_suat')->filter()->values(),
                    'vu_thanh_toan' => $baseQuery->distinct()->pluck('vu_thanh_toan')->filter()->values(),
                    'loai_dau_tu' => $baseQuery->distinct()->pluck('loai_dau_tu')->filter()->values(),
                ];
                
                // Calculate totals based on current query
                $totalsQuery = clone $query;
                $totals = [
                    'total_debt' => $totalsQuery->sum('so_tien_no_goc_da_quy'),
                    'total_paid' => $totalsQuery->sum('da_tra_goc'),
                    'total_remaining' => $totalsQuery->sum('so_tien_con_lai'),
                    'total_interest' => $totalsQuery->sum('tien_lai')
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
                'data' => null,
                'unique_filters' => [
                    'tram' => [],
                    'vu_dau_tu' => [],
                    'category_debt' => [],
                    'loai_tien' => [],
                    'loai_lai_suat' => [],
                    'vu_thanh_toan' => [],
                    'loai_dau_tu' => [],
                ],
                'totals' => [
                    'total_debt' => 0,
                    'total_paid' => 0,
                    'total_remaining' => 0,
                    'total_interest' => 0
                ]
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
        // เปลี่ยน validation rule เป็น custom function ตรวจสอบ extension
        $validator = Validator::make($request->all(), [
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
            ]
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
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());

        // เพิ่มการตรวจสอบ extension อีกครั้งเพื่อความปลอดภัย
        if (!in_array($extension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'ประเภทไฟล์ไม่ได้รับอนุญาต. อนุญาตเฉพาะ: CSV, XLSX, XLS',
                'errors' => ['file' => ['ประเภทไฟล์ไม่ถูกต้อง']]
            ], 422);
        }
        
        // สร้างชื่อไฟล์ชั่วคราว
        $fileName = $importId . '.' . $extension;
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
        dispatch(function() use ($tempPath, $importId, $extension) {
            try {
                // ตรวจสอบไฟล์อีกครั้งก่อนเริ่มประมวลผลในพื้นหลัง
                if (!file_exists($tempPath)) {
                    throw new \Exception("Temp file not found: $tempPath");
                }
                
                $this->processImportFile($tempPath, $importId, $extension);
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
private function processImportFile($filePath, $importId, $extension = null)
{
    try {
        // ตรวจสอบว่าไฟล์มีอยู่จริงหรือไม่
        if (!file_exists($filePath)) {
            throw new \Exception("File \"$filePath\" does not exist.");
        }

        // ตรวจสอบ extension แบบง่ายๆ
        if ($extension === null) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Unsupported file extension: {$extension}. Allowed: " . implode(', ', $allowedExtensions));
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
            $spreadsheet = $reader->load($filePath);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::warning("Failed to read with {$extension} reader, trying alternative readers: " . $e->getMessage());
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
                    $spreadsheet = $reader->load($filePath);
                    Log::info("Successfully read file using {$readerInfo['type']} reader");
                    break;
                } catch (\Exception $readerEx) {
                    Log::warning("Failed to read with {$readerInfo['type']} reader: " . $readerEx->getMessage());
                    continue;
                }
            }
            if (!$spreadsheet) {
                throw new \Exception("Unable to read the uploaded file. Please ensure it's a valid Excel or CSV file.");
            }
        }

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
                            if (is_numeric($row[$excelIndex])) {
                                $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$excelIndex]);
                                $rowData[$dbField] = $dateValue->format('Y-m-d');
                            } else {
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
                        $rowData[$field] = preg_replace('/[^0-9.-]/', '', $rowData[$field]);
                        if ($rowData[$field] === '' || $rowData[$field] === null) {
                            $rowData[$field] = 0;
                        }
                    }
                }

                // Check for required fields
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
                    $exists = DeductibleServiceDebt::where('invoicenumber', $rowData['invoicenumber'])->exists();
                    if ($exists) {
                        DeductibleServiceDebt::where('invoicenumber', $rowData['invoicenumber'])
                            ->update($rowData);
                    } else {
                        DeductibleServiceDebt::create($rowData);
                    }
                    $processedCount++;
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

        Cache::forget('deductible_service_unique_values');

        $importData = Cache::get('import_congno_dichvu_khautru_' . $importId);
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['finished'] = true;
        $importData['success'] = $processedCount > 0;
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
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function showDetails($invoicenumber, Request $request)
{
    try {
        // ดึงข้อมูล user type และ user ID จาก headers ที่ JwtMiddleware ส่งมา
        $userType = $request->header('X-User-Type');
        $userId = $request->header('X-User-ID');
        
        \Log::info('DeductibleServiceDebt showDetails - Headers received:', [
            'X-User-Type' => $userType,
            'X-User-ID' => $userId,
            'invoicenumber' => $invoicenumber
        ]);
        
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
        
        // ตัวแปรสำหรับเก็บข้อมูล farmer (จะใช้ในการกรองประวัติการชำระเงิน)
        $farmer = null;
        
        // ถ้าเป็น farmer ให้ตรวจสอบสิทธิ์การเข้าถึง
        if ($userType === 'farmer' && $userId) {
            // ดึงข้อมูล farmer จาก user_farmer table
            $farmer = DB::table('user_farmer')
                ->where('id', $userId)
                ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                ->first();
                
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer data not found'
                ], 404);
            }
            
            // ตรวจสอบว่า farmer มีสิทธิ์เข้าถึงข้อมูลนี้หรือไม่
            $hasAccess = false;
            
            // ตรวจสอบ ma_khach_hang_ca_nhan
            if (!empty($farmer->ma_kh_ca_nhan) && 
                $debt->ma_khach_hang_ca_nhan === $farmer->ma_kh_ca_nhan) {
                $hasAccess = true;
            }
            
            // ตรวจสอบ ma_khach_hang_doanh_nghiep
            if (!empty($farmer->ma_kh_doanh_nghiep) && 
                $debt->ma_khach_hang_doanh_nghiep === $farmer->ma_kh_doanh_nghiep) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                \Log::warning('Farmer access denied for debt details:', [
                    'farmer_id' => $userId,
                    'farmer_ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan,
                    'farmer_ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep,
                    'debt_ma_khach_hang_ca_nhan' => $debt->ma_khach_hang_ca_nhan,
                    'debt_ma_khach_hang_doanh_nghiep' => $debt->ma_khach_hang_doanh_nghiep
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập thông tin công nợ này.'
                ], 403);
            }
        }
        
        // Fetch payment history with additional information
        $paymentHistoryQuery = DB::table('Logs_Phieu_Tinh_Lai_dv as l')
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
            });
            
        // ถ้าเป็น farmer ให้กรองประวัติการชำระเงินด้วย
        if ($userType === 'farmer' && $farmer) {
            $paymentHistoryQuery->where(function($q) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $q->where('dv.ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                }
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $q->orWhere('dv.ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            });
        }
        
        // Log query สำหรับ debug
        \Log::info('Payment history query debug:', [
            'invoicenumber' => $invoicenumber,
            'debt_ma_khach_hang_ca_nhan' => $debt->ma_khach_hang_ca_nhan ?? 'null',
            'debt_ma_khach_hang_doanh_nghiep' => $debt->ma_khach_hang_doanh_nghiep ?? 'null',
            'farmer_ma_kh_ca_nhan' => $farmer->ma_kh_ca_nhan ?? 'null',
            'farmer_ma_kh_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep ?? 'null',
            'user_type' => $userType
        ]);
        
        $paymentHistory = $paymentHistoryQuery
            ->select(
                'l.Ma_So_Phieu_PDN_Thu_No as receipt_code',
                'l.Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
                'l.Da_Tra_Goc as principal_paid',
                'l.Tien_Lai as interest_paid',
                'l.Ngay_Tra as payment_date',
                'l.Category_Debt as category_debt',
                'l.So_Tro_Trinh as proposal_number',
                'dv.ma_giai_ngan as disbursement_code'
            )
            ->orderBy('l.Ngay_Tra', 'desc')
            ->get();
        
        // Log ผลลัพธ์ payment history สำหรับ debug
        \Log::info('Payment history result:', [
            'count' => $paymentHistory->count(),
            'first_record' => $paymentHistory->first()
        ]);
        
        // ถ้าไม่มีข้อมูลใน payment history ให้ลองค้นหาแบบง่ายกว่า
        if ($paymentHistory->isEmpty()) {
            \Log::info('No payment history found, trying simple query');
            
            $simplePaymentHistory = DB::table('Logs_Phieu_Tinh_Lai_dv')
                ->where('Invoice_Number_Phan_Bo_Dau_Tu', $invoicenumber)
                ->select(
                    'Ma_So_Phieu_PDN_Thu_No as receipt_code',
                    'Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
                    'Da_Tra_Goc as principal_paid',
                    'Tien_Lai as interest_paid',
                    'Ngay_Tra as payment_date',
                    'Category_Debt as category_debt',
                    'So_Tro_Trinh as proposal_number',
                    DB::raw('NULL as disbursement_code')
                )
                ->orderBy('Ngay_Tra', 'desc')
                ->get();
            
            \Log::info('Simple payment history result:', [
                'count' => $simplePaymentHistory->count(),
                'first_record' => $simplePaymentHistory->first()
            ]);
            
            // ถ้าเป็น farmer ให้กรองผลลัพธ์ที่ได้
            if ($userType === 'farmer' && $farmer && $simplePaymentHistory->isNotEmpty()) {
                // สำหรับ simple query ที่ไม่มี join ให้ใช้ข้อมูลจาก debt record เพื่อกรอง
                // เนื่องจากไม่สามารถ join กับ table dv ได้ในขั้นตอนนี้
                $paymentHistory = $simplePaymentHistory;
            } else {
                $paymentHistory = $simplePaymentHistory;
            }
        }
            
        return response()->json([
            'success' => true,
            'document' => $debt,
            'paymentHistory' => $paymentHistory,
            'user_type' => $userType,
            'debug_info' => [
                'payment_history_count' => $paymentHistory->count(),
                'debt_customer_ca_nhan' => $debt->ma_khach_hang_ca_nhan ?? 'null',
                'debt_customer_doanh_nghiep' => $debt->ma_khach_hang_doanh_nghiep ?? 'null',
                'farmer_ca_nhan' => $farmer->ma_kh_ca_nhan ?? 'null',
                'farmer_doanh_nghiep' => $farmer->ma_kh_doanh_nghiep ?? 'null'
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error retrieving debt details: ' . $e->getMessage(), [
            'invoicenumber' => $invoicenumber,
            'user_type' => $userType ?? 'unknown',
            'user_id' => $userId ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi tải dữ liệu công nợ.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Check access permission for debt details
 *
 * @param string $invoicenumber
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function checkAccess($invoicenumber, Request $request)
{
    try {
        // Get user type and authentication info from headers set by JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        $userId = $request->header('X-User-ID');
        
        \Log::info('Check access for debt details:', [
            'invoicenumber' => $invoicenumber,
            'userType' => $userType,
            'userId' => $userId
        ]);
        
        // ค้นหาเอกสารที่ต้องการเข้าถึง
        $document = DB::table('deductible_service_debt')
            ->where('invoicenumber', $invoicenumber)
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
                    'document_ma_khach_hang_ca_nhan' => $document->ma_khach_hang_ca_nhan,
                    'document_ma_khach_hang_doanh_nghiep' => $document->ma_khach_hang_doanh_nghiep
                ]
            ]);
        }
        
        // กรณีอื่นๆ ไม่อนุญาต
        return response()->json([
            'hasAccess' => false,
            'message' => 'Access denied'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error checking debt access: ' . $e->getMessage(), [
            'invoicenumber' => $invoicenumber,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'hasAccess' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Generate Report PDF for Congno Phaithu
 */
public function generateReportTableCongnoPhaithu(Request $request)
{
    try {
        // Handle authentication for GET requests with token in query parameter
        if ($request->isMethod('get') && $request->has('token')) {
            $token = $request->query('token');
            
            try {
                // Verify JWT token for GET requests
                $user = JWTAuth::setToken($token)->authenticate();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid authentication token'
                    ], 401);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed: ' . $e->getMessage()
                ], 401);
            }
        }

        // Handle both GET and POST requests
        if ($request->isMethod('get')) {
            $filterParamsJson = $request->query('filter_params');
            if (!$filterParamsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing filter parameters'
                ], 400);
            }
            $filterParams = json_decode($filterParamsJson, true);
        } else {
            $request->validate([
                'filter_params' => 'required|string'
            ]);
            $filterParams = json_decode($request->filter_params, true);
        }
        
        if (!$filterParams || !isset($filterParams['invoice_numbers'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filter parameters'
            ], 400);
        }

        $invoiceNumbers = $filterParams['invoice_numbers'];
        
        if (empty($invoiceNumbers)) {
            return response()->json([
                'success' => false,
                'message' => 'No records to generate report'
            ], 400);
        }

        // Log for debugging large datasets
        if (count($invoiceNumbers) > 100) {
            Log::info('Generating large Congno Phaithu report', [
                'record_count' => count($invoiceNumbers),
                'report_type' => $filterParams['report_type'] ?? 'unknown'
            ]);
        }

        // Build the main query
        $query = DB::table('deductible_service_debt')
            ->whereIn('invoicenumber', $invoiceNumbers)
            ->select([
                'invoicenumber',
                'vu_dau_tu',
                'category_debt',
                'khach_hang_ca_nhan',
                'ma_khach_hang_ca_nhan',
                'khach_hang_doanh_nghiep',
                'ma_khach_hang_doanh_nghiep',
                'description',
                'so_tien_no_goc_da_quy',
                'da_tra_goc',
                'so_tien_con_lai',
                'tien_lai',
                'ngay_phat_sinh',
                'loai_tien',
                'tram'
            ])
            ->orderBy('invoicenumber', 'asc');

        $reportData = $query->get();

        if ($reportData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected records'
            ], 404);
        }

        // Process each record to format data for Congno Phaithu
        $processedData = [];
        
        foreach ($reportData as $record) {
            // Get customer name (Priority: Individual -> Corporate)
            $khachHang = $record->khach_hang_ca_nhan ?: $record->khach_hang_doanh_nghiep;
            
            // Get customer code (Priority: Individual -> Corporate)
            $maKhachHang = $record->ma_khach_hang_ca_nhan ?: $record->ma_khach_hang_doanh_nghiep;

            $processedData[] = [
                'invoice_number' => $record->invoicenumber ?: 'N/A',
                'vu_dau_tu' => $record->vu_dau_tu ?: 'N/A',
                'loai_dau_tu' => $record->category_debt ?: 'N/A',
                'khach_hang' => $khachHang ?: 'N/A',
                'ma_khach_hang' => $maKhachHang ?: 'N/A',
                'noi_dung' => $record->description ?: 'N/A',
                'tong_no_goc' => $record->so_tien_no_goc_da_quy ?: 0,
                'da_tra_goc' => $record->da_tra_goc ?: 0,
                'so_tien_con_lai' => $record->so_tien_con_lai ?: 0,
                'tien_lai' => $record->tien_lai ?: 0,
                'ngay_phat_sinh' => $record->ngay_phat_sinh ?: null,
                'loai_tien' => $record->loai_tien ?: 'KIP',
                'tram' => $record->tram ?: 'N/A'
            ];
        }

        // Generate report info with enhanced details
        $reportInfo = [
            'title' => 'Báo cáo Công nợ dịch vụ phải thu',
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($processedData),
            'report_type' => $filterParams['report_type'] ?? 'selected_items',
            'report_type_display' => $this->getReportTypeDisplay($filterParams['report_type'] ?? 'selected_items'),
            'generated_by' => $filterParams['generated_by'] ?? 'Hệ thống',
            'filter_summary' => $this->buildFilterSummary($filterParams)
        ];

        // Return view for printing (using ReportCongNoDvPhaithu.blade.php template)
        return view('Print.ReportCongNoDvPhaithu', [
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
        Log::error('Error generating Congno Phaithu report: ' . $e->getMessage(), [
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
        
        // Add other filter summaries as needed
        if (!empty($filters['selected_filter_values'])) {
            foreach ($filters['selected_filter_values'] as $field => $values) {
                if (!empty($values)) {
                    $fieldNames = [
                        'tram' => 'Trạm',
                        'vu_dau_tu' => 'Vụ đầu tư',
                        'category_debt' => 'Loại nợ',
                        'loai_tien' => 'Loại tiền'
                    ];
                    $fieldName = $fieldNames[$field] ?? $field;
                    $summary[] = $fieldName . ": " . implode(', ', $values);
                }
            }
        }
    }
    
    return !empty($summary) ? implode(', ', $summary) : 'Không có bộ lọc';
}

}