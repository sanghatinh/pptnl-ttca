<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\Phieuthunodichvu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class PhieuthunodichvuController extends Controller
{
    /**
     * Display a listing of all records
     */

  /**
     * Display a listing of all records
     */
    public function index(Request $request)
    {
        try {
            // ดึงข้อมูล user type และ user ID จาก headers ที่ JwtMiddleware ส่งมา
            $userType = $request->header('X-User-Type');
            $userId = $request->header('X-User-ID');
            
            // Initialize base query
            $query = DB::table('Logs_Phieu_Tinh_Lai_dv');
            
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
                            $q->where('Ma_Khach_Hang_Ca_Nhan', $farmer->ma_kh_ca_nhan);
                        }
                        // Filter by business customer ID if exists
                        if (!empty($farmer->ma_kh_doanh_nghiep)) {
                            $q->orWhere('Ma_Khach_Hang_Doanh_Nghiep', $farmer->ma_kh_doanh_nghiep);
                        }
                    });
                } else {
                    // If farmer not found, return empty result with proper structure
                    return response()->json([
                        'success' => true,
                        'message' => 'No data found for this farmer',
                        'data' => []
                    ]);
                }
            }
            // สำหรับ employee ไม่ต้องกรองข้อมูล (แสดงทั้งหมด)
            
            // Get all filtered records
            $phieuthunoRecords = $query->get();
            
            // Extract all So_Tro_Trinh values to use for mapping
            $soToTrinhValues = $phieuthunoRecords->pluck('So_Tro_Trinh')->filter()->unique()->toArray();
            
            // Get the ma_trinh_thanh_toan values associated with so_to_trinh
            $trinhThanhToanMapping = [];
            if (!empty($soToTrinhValues)) {
                $trinhThanhToanRecords = DB::table('tb_phieu_trinh_thanh_toan')
                    ->whereIn('so_to_trinh', $soToTrinhValues)
                    ->select('so_to_trinh', 'ma_trinh_thanh_toan')
                    ->get();
                    
                foreach ($trinhThanhToanRecords as $record) {
                    $trinhThanhToanMapping[$record->so_to_trinh] = $record->ma_trinh_thanh_toan;
                }
            }
            
            // Get all ma_trinh_thanh_toan values to fetch ma_giai_ngan
            $maTrinhThanhToanValues = array_values($trinhThanhToanMapping);
            
            // Prepare mapping for ma_giai_ngan
            $giaiNganMapping = [];
            if (!empty($maTrinhThanhToanValues)) {
                // Get all de_nghi_thanhtoan_dv records with matching ma_trinh_thanh_toan
                $deNghiThanhToanRecords = DB::table('tb_de_nghi_thanhtoan_dv')
                    ->whereIn('ma_trinh_thanh_toan', $maTrinhThanhToanValues)
                    ->select('ma_trinh_thanh_toan', 'ma_giai_ngan', 'ma_khach_hang_ca_nhan', 'ma_khach_hang_doanh_nghiep')
                    ->get();
                    
                foreach ($deNghiThanhToanRecords as $record) {
                    $giaiNganMapping[$record->ma_trinh_thanh_toan] = $record->ma_giai_ngan;
                }
            }
            
            // Map to final response format
            $records = $phieuthunoRecords->map(function ($item) use ($trinhThanhToanMapping, $giaiNganMapping) {
                // Get ma_trinh_thanh_toan from so_to_trinh
                $maTrinhThanhToan = $trinhThanhToanMapping[$item->So_Tro_Trinh] ?? null;
                
                // Get ma_giai_ngan from ma_trinh_thanh_toan
                $maGiaiNgan = null;
                if ($maTrinhThanhToan) {
                    $maGiaiNgan = $giaiNganMapping[$maTrinhThanhToan] ?? null;
                }
                
                return [
                    'id' => $item->Ma_So_Phieu_PDN_Thu_No,
                    'ma_so_phieu' => $item->Ma_So_Phieu_PDN_Thu_No,
                    'ma_so_phieu_phan_bo' => $item->Ma_So_Phieu_Phan_Bo_Dau_Tu,
                    'phan_bo_dau_tu' => $item->Phan_Bo_Dau_Tu,
                    'so_phieu_phan_bo_dau_tu' => $item->So_Phieu_Phan_Bo_Dau_Tu,
                    'invoice_number' => $item->Invoice_Number_Phan_Bo_Dau_Tu,
                    'da_tra_goc' => $item->Da_Tra_Goc,
                    'ngay_vay' => $item->Ngay_Vay,
                    'ngay_tra' => $item->Ngay_Tra,
                    'lai_suat' => $item->Lai_Suat_Phan_Bo_Dau_Tu,
                    'tien_lai' => $item->Tien_Lai,
                    'tinh_trang' => $item->Tinh_Trang_PDN_Thu_No,
                    'tinh_trang_duyet' => $item->Tinh_Trang_Duyet_PDN_Thu_No,
                    'da_ho_tro_lai' => $item->Da_Ho_Tro_Lai,
                    'phieu_tinh_tien_mia' => $item->Phieu_Tinh_Tien_Mia_PDN_Thu_No,
                    'created_on' => $item->Created_On,
                    'vu_thanh_toan' => $item->Vu_Thanh_Toan_Phan_Bo_Dau_Tu,
                    'khach_hang' => $item->Khach_Hang_PDN_Thu_No,
                    'khach_hang_ca_nhan' => $item->Khach_Hang_Ca_Nhan_PDN_Thu_No,
                    'khach_hang_doanh_nghiep' => $item->Khach_Hang_Doanh_Nghiep_PDN_Thu_No,
                    'xoa_no' => $item->Xoa_No_Phan_Bo_Dau_Tu,
                    'vu_dau_tu' => $item->Vu_Dau_Tu_Phan_Bo_Dau_Tu,
                    'owner' => $item->Owner_PDN_Thu_No,
                    'so_tro_trinh' => $item->So_Tro_Trinh,
                    'category_debt' => $item->Category_Debt,
                    'description' => $item->Description,
                    'ma_khach_hang_ca_nhan' => $item->Ma_Khach_Hang_Ca_Nhan,
                    'ma_khach_hang_doanh_nghiep' => $item->Ma_Khach_Hang_Doanh_Nghiep,
                    'ma_giai_ngan' => $maGiaiNgan // New field added
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $records
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch phieu thu no dich vu: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show details for a specific record
     */
    public function show($id)
    {
        try {
            $phieu = Phieuthunodichvu::where('Ma_So_Phieu_PDN_Thu_No', $id)->first();
            
            if (!$phieu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }
            
            $data = [
                'id' => $phieu->Ma_So_Phieu_PDN_Thu_No,
                'ma_so_phieu' => $phieu->Ma_So_Phieu_PDN_Thu_No,
                'ma_so_phieu_phan_bo' => $phieu->Ma_So_Phieu_Phan_Bo_Dau_Tu,
                'phan_bo_dau_tu' => $phieu->Phan_Bo_Dau_Tu,
                'so_phieu_phan_bo_dau_tu' => $phieu->So_Phieu_Phan_Bo_Dau_Tu,
                'invoice_number' => $phieu->Invoice_Number_Phan_Bo_Dau_Tu,
                'da_tra_goc' => $phieu->Da_Tra_Goc,
                'ngay_vay' => $phieu->Ngay_Vay,
                'ngay_tra' => $phieu->Ngay_Tra,
                'lai_suat' => $phieu->Lai_Suat_Phan_Bo_Dau_Tu,
                'tien_lai' => $phieu->Tien_Lai,
                'tinh_trang' => $phieu->Tinh_Trang_PDN_Thu_No,
                'tinh_trang_duyet' => $phieu->Tinh_Trang_Duyet_PDN_Thu_No,
                'da_ho_tro_lai' => $phieu->Da_Ho_Tro_Lai,
                'phieu_tinh_tien_mia' => $phieu->Phieu_Tinh_Tien_Mia_PDN_Thu_No,
                'created_on' => $phieu->Created_On,
                'vu_thanh_toan' => $phieu->Vu_Thanh_Toan_Phan_Bo_Dau_Tu,
                'khach_hang' => $phieu->Khach_Hang_PDN_Thu_No,
                'khach_hang_ca_nhan' => $phieu->Khach_Hang_Ca_Nhan_PDN_Thu_No,
                'khach_hang_doanh_nghiep' => $phieu->Khach_Hang_Doanh_Nghiep_PDN_Thu_No,
                'xoa_no' => $phieu->Xoa_No_Phan_Bo_Dau_Tu,
                'vu_dau_tu' => $phieu->Vu_Dau_Tu_Phan_Bo_Dau_Tu,
                'owner' => $phieu->Owner_PDN_Thu_No,
                'so_tro_trinh' => $phieu->So_Tro_Trinh,
                'category_debt' => $phieu->Category_Debt,
                'description' => $phieu->Description,
                'ma_khach_hang_ca_nhan' => $phieu->Ma_Khach_Hang_Ca_Nhan,
                'ma_khach_hang_doanh_nghiep' => $phieu->Ma_Khach_Hang_Doanh_Nghiep,
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch phieu thu no detail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch record details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if the user has access to a specific record
     */
    public function checkAccess($id)
    {
        try {
            $phieu = Phieuthunodichvu::where('Ma_So_Phieu_PDN_Thu_No', $id)->first();
            
            if (!$phieu) {
                return response()->json([
                    'success' => false,
                    'hasAccess' => false,
                    'message' => 'Record not found'
                ], 404);
            }
            
            // Here you can implement your permission logic
            // For now, we'll just return true
            $hasAccess = true;
            
            return response()->json([
                'success' => true,
                'hasAccess' => $hasAccess
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check access: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'hasAccess' => false,
                'message' => 'Failed to check access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import data from Excel/CSV
     */
    public function import(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);

        // Generate a unique import ID
        $importId = uniqid('import_');
        
        try {
            $file = $request->file('file');
            $path = $file->storeAs('imports', $importId . '.' . $file->extension());
            
            // Store import info in cache for progress tracking
            \Cache::put('import_phieutinhlai_' . $importId, [
                'status' => 'uploading',
                'total' => 0,
                'processed' => 0,
                'errors' => [],
                'success' => false,
                'finished' => false
            ], 3600); // Cache for 1 hour
            
            // Process file in background (queue this job for large files)
            dispatch(function() use ($path, $importId) {
                $this->processImportFile($path, $importId);
            })->afterResponse();
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing started.',
                'importId' => $importId
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Check import progress
     */
    public function importProgress($importId)
    {
        $importData = \Cache::get('import_phieutinhlai_' . $importId);
        
        if (!$importData) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found or expired',
                'finished' => true
            ]);
        }
        
        return response()->json($importData);
    }

    /**
     * Process the uploaded import file
     */
    private function processImportFile($path, $importId)
    {
        try {
            // Update status to processing
            $importData = \Cache::get('import_phieutinhlai_' . $importId);
            $importData['status'] = 'processing';
            \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
            
            // Get file from storage - fix path to match filesystem configuration
            $filePath = storage_path('app/' . $path);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                // Try alternative paths if the file doesn't exist at the expected location
                $alternatePaths = [
                    storage_path('app/private/' . $path),
                    storage_path('app/public/' . $path),
                    storage_path($path)
                ];
                
                $fileFound = false;
                foreach ($alternatePaths as $altPath) {
                    \Log::info('Trying alternative path: ' . $altPath);
                    if (file_exists($altPath)) {
                        $filePath = $altPath;
                        $fileFound = true;
                        \Log::info('File found at: ' . $filePath);
                        break;
                    }
                }
                
                if (!$fileFound) {
                    throw new \Exception("File \"$filePath\" does not exist. Please check file upload settings.");
                }
            }
            
            \Log::info('Processing file: ' . $filePath);
            
            // Load the Excel/CSV file
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            
            // Load the file based on extension
            if ($extension == 'csv') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            }
            
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Get headers from first row
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('File is empty or does not contain data rows. Please check the file and try again.');
            }
            
            $headers = array_map('trim', $rows[0]);
            \Log::info('Headers found: ' . implode(', ', $headers));
            
            // Map Excel/CSV columns to database columns
            $columnMap = $this->getColumnMapping($headers);
            
            if (empty($columnMap)) {
                throw new \Exception('Could not map columns from file to database. Required columns are missing. Please verify your file format.');
            }
            
            // Skip header row
            array_shift($rows);
            
            // Update total records count
            $importData = \Cache::get('import_phieutinhlai_' . $importId);
            $importData['total'] = count($rows);
            \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
            
            $processedCount = 0;
            $errors = [];
            
            // Start database transaction
            \DB::beginTransaction();
            
            try {
                // Extract Ma_So_Phieu_PDN_Thu_No values from the import file
                $importedIds = [];
                foreach ($rows as $row) {
                    $maIndex = $columnMap['Ma_So_Phieu_PDN_Thu_No'];
                    if ($maIndex !== false && isset($row[$maIndex]) && !empty($row[$maIndex])) {
                        $importedIds[] = $row[$maIndex];
                    }
                }
                
                // Temporarily disable foreign key checks if needed
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                
                // Delete existing records that will be replaced by the import
                if (!empty($importedIds)) {
                    $deleted = \DB::table('Logs_Phieu_Tinh_Lai_dv')
                        ->whereIn('Ma_So_Phieu_PDN_Thu_No', $importedIds)
                        ->delete();
                    
                    \Log::info('Deleted ' . $deleted . ' existing records');
                }
                
                // Re-enable foreign key checks
                \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                
                // Insert new records in batches for better performance
                $batchSize = 100; // Process 100 records at a time
                $batches = array_chunk($rows, $batchSize);
                
                foreach ($batches as $batchIndex => $batch) {
                    $batchData = [];
                    
                    foreach ($batch as $index => $row) {
                        $rowIndex = ($batchIndex * $batchSize) + $index;
                        
                        try {
                            // Skip empty rows
                            if (empty(array_filter($row))) {
                                continue;
                            }
                            
                            $data = [];
                            
                            // Map data from Excel/CSV to database columns
                            foreach ($this->getDbColumns() as $dbColumn) {
                                $excelIndex = $columnMap[$dbColumn] ?? false;
                                $data[$dbColumn] = ($excelIndex !== false && isset($row[$excelIndex])) 
                                    ? $this->formatValue($dbColumn, $row[$excelIndex]) 
                                    : null;
                            }
                            
                            // Validate required fields
                            if (empty($data['Ma_So_Phieu_PDN_Thu_No'])) {
                                $errors[] = "Row " . ($rowIndex + 2) . ": Ma_So_Phieu_PDN_Thu_No is required";
                                continue;
                            }
                            
                            $batchData[] = $data;
                            $processedCount++;
                            
                        } catch (\Exception $e) {
                            $errors[] = "Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage();
                        }
                    }
                    
                    // Insert batch if not empty
                    if (!empty($batchData)) {
                        \DB::table('Logs_Phieu_Tinh_Lai_dv')->insert($batchData);
                    }
                    
                    // Update progress after each batch
                    $importData = \Cache::get('import_phieutinhlai_' . $importId);
                    $importData['processed'] = $processedCount;
                    $importData['errors'] = $errors;
                    \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
                }
                
                // Commit transaction
                \DB::commit();
                
                // Update final status
                $importData = \Cache::get('import_phieutinhlai_' . $importId);
                $importData['status'] = 'completed';
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                $importData['success'] = true;
                $importData['finished'] = true;
                \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollBack();
                
                // Update error status
                $importData = \Cache::get('import_phieutinhlai_' . $importId);
                $importData['status'] = 'failed';
                $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
                $importData['success'] = false;
                $importData['finished'] = true;
                \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
                
                \Log::error('Import transaction error: ' . $e->getMessage());
            }
            
            // Delete the temporary file
            \Storage::delete($path);
            
        } catch (\Exception $e) {
            // Update error status
            $importData = \Cache::get('import_phieutinhlai_' . $importId);
            $importData['status'] = 'failed';
            $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
            $importData['success'] = false;
            $importData['finished'] = true;
            \Cache::put('import_phieutinhlai_' . $importId, $importData, 3600);
            
            \Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Map columns from Excel/CSV headers to database columns
     */
    private function getColumnMapping($headers)
    {
        // Define mapping from common header names to database columns
        $possibleHeaderMappings = [
            'Ma_So_Phieu_PDN_Thu_No' => ['mã số phiếu', 'ma so phieu', 'ma_so_phieu', 'pđn thu nợ', 'pdn thu no', 'mã phiếu', 'ma phieu', 'ma_phieu', 'phieu pdn'],
            'Ma_So_Phieu_Phan_Bo_Dau_Tu' => ['mã số phiếu phân bổ', 'ma so phieu phan bo', 'phân bổ đầu tư', 'phan bo dau tu', 'ma_phieu_phan_bo'],
            'Phan_Bo_Dau_Tu' => ['phân bổ', 'phan bo', 'đầu tư', 'dau tu'],
            'So_Phieu_Phan_Bo_Dau_Tu' => ['số phiếu phân bổ', 'so phieu phan bo', 'so_phieu_phan_bo'],
            'Invoice_Number_Phan_Bo_Dau_Tu' => ['invoice number', 'invoice', 'số hóa đơn', 'so hoa don', 'hóa đơn'],
            'Da_Tra_Goc' => ['da tra goc', 'da_tra_goc', 'đã trả gốc', 'tra goc', 'goc', 'gốc', 'số tiền gốc'],
            'Ngay_Vay' => ['ngay vay', 'ngay_vay', 'ngày vay', 'thoi gian vay', 'ngày mượn', 'ngay muon', 'ngày tạo'],
            'Ngay_Tra' => ['ngay tra', 'ngay_tra', 'ngày trả', 'thoi gian tra', 'ngày hoàn thành', 'ngay hoan thanh'],
            'Lai_Suat_Phan_Bo_Dau_Tu' => ['lãi suất', 'lai suat', 'lai_suat', '% lãi', '% lai', 'tỷ lệ', 'ty le'],
            'Tien_Lai' => ['Tiền lãi'],
            'Tinh_Trang_PDN_Thu_No' => ['tình trạng', 'tinh trang', 'trạng thái', 'trang thai', 'tình trạng thu nợ', 'status'],
            'Tinh_Trang_Duyet_PDN_Thu_No' => ['tình trạng duyệt', 'tinh trang duyet', 'duyệt', 'duyet', 'approval status'],
            'Da_Ho_Tro_Lai' => ['da ho tro lai', 'da_ho_tro_lai', 'đã hỗ trợ lãi', 'ho tro lai', 'hỗ trợ'],
            'Phieu_Tinh_Tien_Mia_PDN_Thu_No' => ['phiếu tính tiền mía', 'phieu tinh tien mia'],
            'Created_On' => ['created on', 'created_on', 'ngày tạo', 'ngay tao', 'date created', 'creation date'],
            'Vu_Thanh_Toan_Phan_Bo_Dau_Tu' => ['vụ thanh toán', 'vu thanh toan', 'thanh toán', 'payment'],
            'Khach_Hang_PDN_Thu_No' => ['khách hàng', 'khach hang', 'customer', 'tên khách hàng'],
            'Khach_Hang_Ca_Nhan_PDN_Thu_No' => ['khách hàng cá nhân', 'khach hang ca nhan', 'cá nhân', 'ca nhan'],
            'Khach_Hang_Doanh_Nghiep_PDN_Thu_No' => ['khách hàng doanh nghiệp', 'khach hang doanh nghiep', 'doanh nghiệp', 'doanh nghiep'],
            'Xoa_No_Phan_Bo_Dau_Tu' => ['xóa nợ', 'xoa no', 'hủy nợ', 'huy no'],
            'Vu_Dau_Tu_Phan_Bo_Dau_Tu' => ['vụ đầu tư', 'vu dau tu', 'đầu tư', 'dau tu'],
            'Owner_PDN_Thu_No' => ['owner', 'người sở hữu', 'nguoi so huu', 'người phụ trách', 'phu trach'],
            'So_Tro_Trinh' => ['so tro trinh', 'số trờ trình', 'số trình', 'so trinh', 'report number'],
            'Category_Debt' => ['category debt', 'category_debt', 'loại nợ', 'loai no', 'debt type', 'phân loại nợ'],
            'Description' => ['description', 'mo ta', 'mô tả', 'ghi chú', 'ghi chu', 'note', 'notes'],
            'Ma_Khach_Hang_Ca_Nhan' => ['ma khach hang ca nhan', 'ma_khach_hang_ca_nhan', 'mã khách hàng cá nhân', 'mã cá nhân', 'ma ca nhan'],
            'Ma_Khach_Hang_Doanh_Nghiep' => ['ma khach hang doanh nghiep', 'ma_khach_hang_doanh_nghiep', 'mã khách hàng doanh nghiệp', 'mã doanh nghiệp', 'ma doanh nghiep']
        ];
        
        $columnMap = [];
        
        // Debug log to see what headers we're working with
        \Log::info('Original headers: ' . json_encode($headers));
        
        // Find index for each database column in the headers
        foreach ($possibleHeaderMappings as $dbColumn => $possibleHeaders) {
            $columnMap[$dbColumn] = false;
            
            foreach ($headers as $index => $header) {
                // Convert to lowercase and trim
                $headerLower = mb_strtolower(trim($header), 'UTF-8');
                \Log::debug("Checking header: '$headerLower'");
                
                // Direct match check
                if (in_array($headerLower, array_map('mb_strtolower', $possibleHeaders))) {
                    $columnMap[$dbColumn] = $index;
                    \Log::debug("Direct match found for '$dbColumn' -> '$headerLower'");
                    break;
                }
                
                // Partial match check - if the header contains one of our possible header strings
                foreach ($possibleHeaders as $possibleHeader) {
                    $possibleHeaderLower = mb_strtolower($possibleHeader, 'UTF-8');
                    if (strpos($headerLower, $possibleHeaderLower) !== false) {
                        $columnMap[$dbColumn] = $index;
                        \Log::debug("Partial match found for '$dbColumn' -> '$headerLower' contains '$possibleHeaderLower'");
                        break 2; // Break out of both loops
                    }
                }
            }
        }
        
        // Add special handling for the primary key field which may have many variations
        if ($columnMap['Ma_So_Phieu_PDN_Thu_No'] === false) {
            foreach ($headers as $index => $header) {
                $headerLower = mb_strtolower(trim($header), 'UTF-8');
                if (strpos($headerLower, 'mã') !== false && 
                   (strpos($headerLower, 'phiếu') !== false || strpos($headerLower, 'phieu') !== false)) {
                    $columnMap['Ma_So_Phieu_PDN_Thu_No'] = $index;
                    \Log::debug("Generic match found for Ma_So_Phieu_PDN_Thu_No -> '$headerLower'");
                    break;
                }
                // Also try English field names
                if ((strpos($headerLower, 'id') !== false || strpos($headerLower, 'code') !== false) &&
                   (strpos($headerLower, 'debt') !== false || strpos($headerLower, 'ticket') !== false)) {
                    $columnMap['Ma_So_Phieu_PDN_Thu_No'] = $index;
                    \Log::debug("English match found for Ma_So_Phieu_PDN_Thu_No -> '$headerLower'");
                    break;
                }
            }
        }
        
        // Log the column mapping for debugging
        \Log::info('Column mapping: ' . json_encode($columnMap));
        
        // Check if we have at least the required primary key column
        if ($columnMap['Ma_So_Phieu_PDN_Thu_No'] === false) {
            // If we can't find the primary key, try to use the first column
            if (count($headers) > 0) {
                \Log::warning('Required column Ma_So_Phieu_PDN_Thu_No not found, using first column as fallback');
                $columnMap['Ma_So_Phieu_PDN_Thu_No'] = 0;
            } else {
                \Log::error('Required column Ma_So_Phieu_PDN_Thu_No not found and no columns available');
                return [];
            }
        }
        
        return $columnMap;
    }

    /**
     * Get the list of database columns for the Logs_Phieu_Tinh_Lai_dv table
     */
    private function getDbColumns()
    {
        return [
            'Ma_So_Phieu_PDN_Thu_No',
            'Ma_So_Phieu_Phan_Bo_Dau_Tu',
            'Phan_Bo_Dau_Tu',
            'So_Phieu_Phan_Bo_Dau_Tu',
            'Invoice_Number_Phan_Bo_Dau_Tu',
            'Da_Tra_Goc',
            'Ngay_Vay',
            'Ngay_Tra',
            'Lai_Suat_Phan_Bo_Dau_Tu',
            'Tien_Lai',
            'Tinh_Trang_PDN_Thu_No',
            'Tinh_Trang_Duyet_PDN_Thu_No',
            'Da_Ho_Tro_Lai',
            'Phieu_Tinh_Tien_Mia_PDN_Thu_No',
            'Created_On',
            'Vu_Thanh_Toan_Phan_Bo_Dau_Tu',
            'Khach_Hang_PDN_Thu_No',
            'Khach_Hang_Ca_Nhan_PDN_Thu_No',
            'Khach_Hang_Doanh_Nghiep_PDN_Thu_No',
            'Xoa_No_Phan_Bo_Dau_Tu',
            'Vu_Dau_Tu_Phan_Bo_Dau_Tu',
            'Owner_PDN_Thu_No',
            'So_Tro_Trinh',
            'Category_Debt',
            'Description',
            'Ma_Khach_Hang_Ca_Nhan',
            'Ma_Khach_Hang_Doanh_Nghiep'
        ];
    }

    /**
     * Format value from Excel/CSV to database format
     */
    private function formatValue($column, $value)
    {
        // Handle date columns
        if (in_array($column, ['Ngay_Vay', 'Ngay_Tra', 'Created_On'])) {
            // Skip if already null
            if ($value === null || $value === '') {
                return null;
            }
            
            // Try to parse date
            try {
                if (is_numeric($value)) {
                    // Handle Excel date numbers
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                    return $date->format('Y-m-d');
                } else {
                    // Try to parse as string date
                    return date('Y-m-d', strtotime($value));
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        
        // Handle numeric columns
        if (in_array($column, ['Da_Tra_Goc', 'Lai_Suat_Phan_Bo_Dau_Tu', 'Tien_Lai', 'Xoa_No_Phan_Bo_Dau_Tu'])) {
            if ($value === '' || $value === null) {
                return null;
            }
            
            // Convert to numeric value
            return is_numeric($value) ? (float)$value : null;
        }
        
        // Handle boolean columns like Da_Ho_Tro_Lai as string
        if ($column === 'Da_Ho_Tro_Lai') {
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }
            
            if (is_string($value)) {
                $lower = strtolower(trim($value));
                if (in_array($lower, ['true', 'yes', 'có', 'co', '1'])) {
                    return '1';
                } elseif (in_array($lower, ['false', 'no', 'không', 'khong', '0'])) {
                    return '0';
                }
            }
            
            if (is_numeric($value)) {
                return $value > 0 ? '1' : '0';
            }
            
            return null;
        }
        
        // Default: return as string
        return $value !== null ? (string)$value : null;
    }
    /**
 * Generate Report PDF for Phieu Thu No Dich Vu
 */
public function generateReportTablePhieuthuno(Request $request)
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
        
        if (!$filterParams || !isset($filterParams['record_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filter parameters'
            ], 400);
        }

        $recordIds = $filterParams['record_ids'];
        
        if (empty($recordIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No records to generate report'
            ], 400);
        }

        // Log for debugging large datasets
        if (count($recordIds) > 100) {
            Log::info('Generating large Phieu Thu No report', [
                'record_count' => count($recordIds),
                'report_type' => $filterParams['report_type'] ?? 'unknown'
            ]);
        }

        // Build the main query to get data from Logs_Phieu_Tinh_Lai_dv
        $query = DB::table('Logs_Phieu_Tinh_Lai_dv as logs')
            ->whereIn('logs.Ma_So_Phieu_PDN_Thu_No', $recordIds)
            ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'logs.So_Tro_Trinh', '=', 'pttt.so_to_trinh')
            ->select([
                'logs.Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
                'logs.Vu_Dau_Tu_Phan_Bo_Dau_Tu as vu_dau_tu', 
                'logs.Category_Debt as loai_dau_tu',
                'logs.Khach_Hang_Ca_Nhan_PDN_Thu_No as khach_hang_ca_nhan',
                'logs.Khach_Hang_Doanh_Nghiep_PDN_Thu_No as khach_hang_doanh_nghiep',
                'logs.Ma_Khach_Hang_Ca_Nhan as ma_khach_hang_ca_nhan',
                'logs.Ma_Khach_Hang_Doanh_Nghiep as ma_khach_hang_doanh_nghiep',
                'logs.Description as noi_dung',
                'logs.Da_Tra_Goc as da_tra_goc',
                'logs.Tien_Lai as tien_lai',
                'logs.Lai_Suat_Phan_Bo_Dau_Tu as lai_suat',
                'logs.Ngay_Vay as ngay_vay',
                'logs.Ngay_Tra as ngay_tra',
                'logs.So_Tro_Trinh as so_tro_trinh',
                'pttt.so_dot_thanh_toan as so_dot_thanh_toan'
            ])
            ->orderBy('logs.Invoice_Number_Phan_Bo_Dau_Tu', 'asc');

        $reportData = $query->get();

        if ($reportData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected records'
            ], 404);
        }

        // Process each record to format data for report
        $processedData = [];
        
        foreach ($reportData as $record) {
            // Get customer name (Priority: Individual -> Corporate)
            $khachHang = $record->khach_hang_ca_nhan ?: $record->khach_hang_doanh_nghiep;
            
            // Get customer code (Priority: Individual -> Corporate)  
            $maKhachHang = $record->ma_khach_hang_ca_nhan ?: $record->ma_khach_hang_doanh_nghiep;

            $processedData[] = [
                'invoice_number' => $record->invoice_number ?: 'N/A',
                'vu_dau_tu' => $record->vu_dau_tu ?: 'N/A',
                'loai_dau_tu' => $record->loai_dau_tu ?: 'N/A',
                'khach_hang' => $khachHang ?: 'N/A',
                'ma_khach_hang' => $maKhachHang ?: 'N/A',
                'noi_dung' => $record->noi_dung ?: 'N/A',
                'da_tra_goc' => $record->da_tra_goc ?: 0,
                'tien_lai' => $record->tien_lai ?: 0,
                'lai_suat' => $record->lai_suat ?: 0,
                'ngay_vay' => $record->ngay_vay ?: null,
                'ngay_tra' => $record->ngay_tra ?: null,
                'so_dot_thanh_toan' => $record->so_dot_thanh_toan ?: 'N/A'
            ];
        }

        // Generate report info with enhanced details
        $reportInfo = [
            'title' => 'Báo cáo Phiếu thu nợ dịch vụ',
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($processedData),
            'report_type' => $filterParams['report_type'] ?? 'selected_items',
            'report_type_display' => $this->getReportTypeDisplay($filterParams['report_type'] ?? 'selected_items'),
            'generated_by' => $filterParams['generated_by'] ?? 'Hệ thống',
            'filter_summary' => $this->buildFilterSummary($filterParams)
        ];

        // Return view for printing (using ReportPhieuthuno.blade.php template)
        return view('Print.ReportPhieuthuno', [
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
        Log::error('Error generating Phieu Thu No report: ' . $e->getMessage(), [
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
        
        if (!empty($filters['status_filter']) && $filters['status_filter'] !== 'all') {
            $summary[] = "Trạng thái: " . $filters['status_filter'];
        }
        
        // Add other filter summaries as needed
        if (!empty($filters['selected_filter_values'])) {
            foreach ($filters['selected_filter_values'] as $field => $values) {
                if (!empty($values)) {
                    $fieldNames = [
                        'tinh_trang' => 'Tình trạng',
                        'vu_dau_tu' => 'Vụ đầu tư',
                        'category_debt' => 'Loại nợ',
                        'khach_hang' => 'Khách hàng'
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