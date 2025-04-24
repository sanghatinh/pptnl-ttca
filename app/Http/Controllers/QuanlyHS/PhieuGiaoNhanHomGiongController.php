<?php

namespace App\Http\Controllers\QuanlyHS;

use App\Http\Controllers\Controller;
use App\Models\BienBanNghiemThuHomGiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PhieuGiaoNhanHomGiongController extends Controller
{
 

public function index(Request $request)
{
    try {
        $user = auth()->user();
        
        // Start with base query on bien_ban_nghiem_thu_hom_giong
        $query = DB::table('bien_ban_nghiem_thu_hom_giong as hg')
            // Join with document_mapping_homgiong to get document_code
            ->leftJoin('document_mapping_homgiong as dmhg', 'hg.ma_so_phieu', '=', 'dmhg.ma_so_phieu')
            // Join with document_delivery to get creator_id, receiver_id, received_date, status
            ->leftJoin('document_delivery as dd', 'dmhg.document_code', '=', 'dd.document_code')
            // Join with users table for creator name (nguoi_giao_ho_so)
            ->leftJoin('users as creator', 'dd.creator_id', '=', 'creator.id')
            // Join with users table for receiver name (nguoi_nhan_ho_so)
            ->leftJoin('users as receiver', 'dd.receiver_id', '=', 'receiver.id');

        // Access control based on user position
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                // See all records - no filtering needed
                break;
                
            case 'Station_Chief':
                // Filter by user's station
                $query->where('hg.tram', $user->station);
                break;
                
            case 'Farm_worker':
                // Filter by employee code
                $query->where('hg.ma_nhan_vien', $user->ma_nhan_vien);
                break;
                
            default:
                // Default case - return no records
                return response()->json([
                    'data' => [],
                    'message' => 'Unauthorized position'
                ], 403);
        }

        // Select all fields from hom_giong table and additional fields from related tables
        $query->select(
            'hg.*',
            'creator.full_name as nguoi_giao_ho_so',
            'receiver.full_name as nguoi_nhan_ho_so',
            'dd.received_date as ngay_nhan_ho_so',
            'dd.status as tinh_trang_giao_nhan_ho_so'
        );

        // Get the filtered records
        $records = $query->get();

        return response()->json([
            'success' => true,
            'data' => $records
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in PhieuGiaoNhan index: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving records',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Import data from Excel/CSV
    public function importData(Request $request)
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
            \Cache::put('import_homgiong_' . $importId, [
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

    // Check import progress
    public function importProgress($importId)
    {
        $importData = \Cache::get('import_homgiong_' . $importId);
        
        if (!$importData) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found or expired',
                'finished' => true
            ]);
        }
        
        return response()->json($importData);
    }

    // Process the uploaded import file
    private function processImportFile($path, $importId)
    {
        try {
            // Update status to processing
            $importData = \Cache::get('import_homgiong_' . $importId);
            $importData['status'] = 'processing';
            \Cache::put('import_homgiong_' . $importId, $importData, 3600);
            
            // Log the original path for debugging
            \Log::info('Import path provided: ' . $path);
            
            // Get file from storage - fix path to match filesystem configuration
            $filePath = storage_path('app/' . $path);
            
            // Log the attempted path
            \Log::info('Attempting to access file at: ' . $filePath);
            
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
                    throw new \Exception("File \"$path\" does not exist at any expected locations. Please check file upload settings.");
                }
            }
            
            // Determine file type and process accordingly
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            \Log::info('File extension detected: ' . $extension);
            
            // Load the file based on extension
            if ($extension == 'csv') {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            }
            
            try {
                $spreadsheet = $reader->load($filePath);
                \Log::info('File loaded successfully with PhpSpreadsheet');
            } catch (\Exception $e) {
                \Log::error('Error loading file with PhpSpreadsheet: ' . $e->getMessage());
                throw new \Exception('Failed to load file: ' . $e->getMessage());
            }
            
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
                throw new \Exception('Could not map columns from file to database. Required columns are missing. Please verify your file has the following columns: ma_so_phieu, tram, vu_dau_tu, ten_phieu');
            }
            
            // Skip header row
            array_shift($rows);
            
            // Update total records count
            $importData = \Cache::get('import_homgiong_' . $importId);
            $importData['total'] = count($rows);
            \Cache::put('import_homgiong_' . $importId, $importData, 3600);
            
            $processedCount = 0;
            $errors = [];
            
            // Use a simple try-catch instead of the callback-style transaction
            try {
                \DB::beginTransaction();
                
                // Store existing ma_so_phieu values that have mappings
                $existingMappedHG = \DB::table('document_mapping_homgiong')
                    ->whereNotNull('ma_so_phieu')
                    ->pluck('ma_so_phieu')
                    ->toArray();
                
                // Extract ma_so_phieu values from the import file
                $importedHGIds = [];
                foreach ($rows as $row) {
                    $maIndex = $columnMap['ma_so_phieu'];
                    if ($maIndex !== false && isset($row[$maIndex]) && !empty($row[$maIndex])) {
                        $importedHGIds[] = $row[$maIndex];
                    }
                }
                
                // Get only hom giong IDs that will be affected but preserve mappings
                $preserveMappings = array_intersect($existingMappedHG, $importedHGIds);
                
                // First temporarily disable foreign key checks 
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                
                // Log the IDs that will be deleted and preserved
                \Log::info('IDs to be imported: ' . implode(', ', $importedHGIds));
                \Log::info('Mappings to be preserved: ' . implode(', ', $preserveMappings));
                
                // Delete records from bien_ban_nghiem_thu_hom_giong that will be replaced by the import
                // but preserve records referenced in document_mapping that aren't in the import
                if (!empty($importedHGIds)) {
                    $deleted = \DB::table('bien_ban_nghiem_thu_hom_giong')
                        ->whereIn('ma_so_phieu', $importedHGIds)
                        ->delete();
                    \Log::info('Deleted ' . $deleted . ' existing records');
                }
                
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
                            foreach ($columnMap as $dbColumn => $excelIndex) {
                                if ($excelIndex !== false && isset($row[$excelIndex])) {
                                    $value = trim($row[$excelIndex]);
                                    
                                    // Handle special cases (e.g., dates, numbers)
                                    if (in_array($dbColumn, ['tong_thuc_nhan', 'tong_tien', 'tong_tien_hom', 'tong_tien_boc_xep', 'tong_tien_van_chuyen'])) {
                                        // Convert from string to number or handle formatting if needed
                                        $value = is_numeric($value) ? $value : 0;
                                    }
                                    
                                    $data[$dbColumn] = $value;
                                }
                            }
                            
                            // Set required fields if missing
                            if (empty($data['ma_so_phieu'])) {
                                throw new \Exception("Row " . ($rowIndex + 2) . ": Missing required field 'ma_so_phieu'");
                            }
                            
                            // Replace any empty strings with null for certain fields
                            foreach (['tram', 'vu_dau_tu', 'ten_phieu'] as $requiredField) {
                                if (!isset($data[$requiredField]) || $data[$requiredField] === '') {
                                    $data[$requiredField] = null;
                                }
                            }
                            
                            $batchData[] = $data;
                            $processedCount++;
                            
                        } catch (\Exception $e) {
                            $errors[] = "Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage();
                        }
                    }
                    
                    // Insert batch if not empty
                    if (!empty($batchData)) {
                        \Log::info('Inserting batch of ' . count($batchData) . ' records');
                        \DB::table('bien_ban_nghiem_thu_hom_giong')->insert($batchData);
                    }
                    
                    // Update progress after each batch
                    $importData = \Cache::get('import_homgiong_' . $importId);
                    $importData['processed'] = $processedCount;
                    $importData['errors'] = $errors;
                    \Cache::put('import_homgiong_' . $importId, $importData, 3600);
                    
                    // Give the database a moment to breathe
                    usleep(10000); // 10ms pause between batches
                }
                
                // If we get here, commit the transaction
                \DB::commit();
                \Log::info('Transaction committed successfully');
            } catch (\Exception $e) {
                // Rollback the transaction
                \DB::rollBack();
                \Log::error('Transaction rollback: ' . $e->getMessage());
                // Add the error to our errors array
                $errors[] = 'Transaction error: ' . $e->getMessage();
                throw $e; // Re-throw to be caught by the outer catch block
            }
            
            // Update final status
            $importData = \Cache::get('import_homgiong_' . $importId);
            $importData['status'] = 'completed';
            $importData['processed'] = $processedCount;
            $importData['errors'] = $errors;
            $importData['success'] = true;
            $importData['finished'] = true;
            \Cache::put('import_homgiong_' . $importId, $importData, 3600);
            
            // Delete the temporary file
            \Storage::delete($path);
            
        } catch (\Exception $e) {
            // Update error status
            $importData = \Cache::get('import_homgiong_' . $importId);
            $importData['status'] = 'failed';
            $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
            $importData['success'] = false;
            $importData['finished'] = true;
            \Cache::put('import_homgiong_' . $importId, $importData, 3600);
            
            \Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    // Map columns from Excel/CSV headers to database columns
    private function getColumnMapping($headers)
    {
        // Define mapping from common header names to database columns
        $possibleHeaderMappings = [
            'ma_so_phieu' => ['mã số phiếu', 'ma so phieu', 'ma_so_phieu', 'mã phiếu', 'ma phieu', 'phieu'],
            'tram' => ['trạm', 'tram', 'nha may', 'nhà máy', 'station'],
            'vu_dau_tu' => ['vụ đầu tư', 'vu dau tu', 'vu_dau_tu', 'investment', 'vụ', 'vu'],
            'ten_phieu' => ['tên phiếu', 'ten phieu', 'ten_phieu', 'title', 'nội dung', 'noi dung'],
            'hop_dong_dau_tu_mia' => ['hợp đồng đầu tư mía', 'hop dong dau tu mia', 'hợp đồng', 'hop dong', 'contract'],
            'hop_dong_dau_tu_mia_ben_giao_hom' => ['Hợp đồng đầu tư mía (Đối tác)', 'hop dong dau tu mia ben giao', 'hợp đồng bên giao', 'hop dong ben giao'],
            
            'ma_hop_dong' => ['mã hợp đồng', 'Mã hợp đồng (Hợp đồng đầu tư mía) (HĐ đầu tư mía)', 'contract code', 'contract id'],
            'khach_hang_ca_nhan' => ['khách hàng cá nhân', 'khach hang ca nhan', 'individual customer'],
            'ma_khach_hang_CN' => ['mã khách hàng CN', 'Mã khách hàng (Khách hàng cá nhân) (Khách hàng)', 'customer id'],
            'khach_hang_doanh_nghiep' => ['khách hàng doanh nghiệp', 'khach hang doanh nghiep', 'business customer', 'corporate customer'],
            'ma_khach_hang_DN' => ['mã khách hàng DN', 'Mã khách hàng (Khách hàng doanh nghiệp) (Khách hàng doanh nghiệp)', 'corporate id', 'business id'],
            'doi_tac_giao_hom' => ['đối tác giao hom', 'Đối tác giao hom (KH)', 'delivery partner', 'supplier'],
            'ma_khach_hang_giao_hom' => ['mã khách hàng giao hom', 'Mã khách hàng (Đối tác giao hom (KH)) (Khách hàng)', 'supplier id'],
            'doi_tac_giao_hom_DN' => ['đối tác giao hom DN', 'Đối tác giao hom (KHDN)', 'corporate supplier', 'business supplier'],
            'ma_khach_hang_giao_hom_DN' => ['mã khách hàng giao hom DN', 'Mã khách hàng (Đối tác giao hom (KHDN)) (Khách hàng doanh nghiệp)', 'corporate supplier id'],
            'tong_thuc_nhan' => ['tổng thực nhận', 'tong thuc nhan', 'total received', 'thực nhận'],
            'tong_so_tien_HL' => ['tổng số tiền HL', 'tong so tien HL', 'tổng tiền HL', 'tong tien HL'],
            'tong_tien_hom' => ['tổng tiền hom', 'tong tien hom', 'tiền hom', 'tien hom'],
            'tong_tien' => ['tổng tiền', 'tong tien', 'total amount', 'total'],
            'tong_tien_boc_xep' => ['tổng tiền bốc xếp', 'tong tien boc xep', 'tiền bốc xếp', 'tien boc xep'],
            'tong_tien_cong_don' => ['Tổng tiền công đốn', 'tong tien cong don', 'accumulated total', 'tích lũy'],
            'tong_tien_van_chuyen' => ['tổng tiền vận chuyển', 'tong tien van chuyen', 'tiền vận chuyển', 'tien van chuyen'],
            'can_bo_nong_vu' => ['cán bộ nông vụ', 'can bo nong vu', 'officer', 'agricultural officer'],
            'tinh_trang_duyet' => ['tình trạng duyệt', 'tinh trang duyet', 'approval status', 'approval'],
            'ma_nhan_vien' => ['mã nhân viên', 'Mã nhân viên (Cán bộ nông vụ) (Nhân viên)', 'employee id', 'staff id', 'employee code'],
        ];
        
        $columnMap = [];
        
        // Check each database column against possible headers
        foreach ($possibleHeaderMappings as $dbColumn => $possibleHeaders) {
            $columnMap[$dbColumn] = false; // Default to not found
            
            // Check each possible header name
            foreach ($possibleHeaders as $possibleHeader) {
                // Try to find an exact match first
                $exactMatchIndex = array_search(strtolower($possibleHeader), array_map('strtolower', $headers));
                
                if ($exactMatchIndex !== false) {
                    $columnMap[$dbColumn] = $exactMatchIndex;
                    break;
                }
                
                // If no exact match, try a partial match
                foreach ($headers as $index => $header) {
                    if (strpos(strtolower($header), strtolower($possibleHeader)) !== false) {
                        $columnMap[$dbColumn] = $index;
                        break 2; // Break both loops
                    }
                }
            }
        }
        
        // Check if we have the minimum required columns
        $requiredColumns = ['ma_so_phieu', 'tram', 'vu_dau_tu', 'ten_phieu'];
        $missingRequired = false;
        
        foreach ($requiredColumns as $required) {
            if ($columnMap[$required] === false) {
                $missingRequired = true;
                break;
            }
        }
        
        if ($missingRequired) {
            // If strict validation fails, try a more lenient approach for column indexes
            if (count($headers) >= count($requiredColumns)) {
                // Assume columns are in order
                $columnMap['ma_so_phieu'] = $columnMap['ma_so_phieu'] !== false ? $columnMap['ma_so_phieu'] : 0;
                $columnMap['tram'] = $columnMap['tram'] !== false ? $columnMap['tram'] : 1;
                $columnMap['vu_dau_tu'] = $columnMap['vu_dau_tu'] !== false ? $columnMap['vu_dau_tu'] : 2;
                $columnMap['ten_phieu'] = $columnMap['ten_phieu'] !== false ? $columnMap['ten_phieu'] : 3;
            }
        }
        
        return $columnMap;
    }

    public function show($id)
{
    try {
        // Get the main document info from bien_ban_nghiem_thu_hom_giong table
        $document = \DB::table('bien_ban_nghiem_thu_hom_giong as hg')
            ->leftJoin('document_mapping_homgiong as dmhg', 'hg.ma_so_phieu', '=', 'dmhg.ma_so_phieu')
            ->leftJoin('document_delivery as dd', 'dmhg.document_code', '=', 'dd.document_code')
            ->leftJoin('users as creator', 'dd.creator_id', '=', 'creator.id')
            ->leftJoin('users as receiver', 'dd.receiver_id', '=', 'receiver.id')
            ->select(
                'hg.ma_so_phieu',
                'hg.can_bo_nong_vu',
                'hg.vu_dau_tu',
                'hg.ten_phieu',
                'hg.hop_dong_dau_tu_mia_ben_giao_hom as hop_dong_dau_tu_mia_ben_giao',
                'hg.ma_hop_dong as hop_dong_dau_tu_mia_ben_nhan',
                'hg.tong_thuc_nhan',
                'hg.tong_tien',
                'creator.full_name as nguoi_giao',
                'receiver.full_name as nguoi_nhan',
                'dd.received_date as ngay_nhan',
                'dd.status as trang_thai_nhan_hs'
                
            )
            ->where('hg.ma_so_phieu', $id)
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin phiếu giao nhận hom giống'
            ], 404);
        }

        // Fetch service details from tb_chitiet_homgiong
        $serviceDetails = \DB::table('tb_chitiet_giaonhan_homgiong')
            ->where('phieu_gn_hom_giong', $document->ten_phieu)
            ->get();

        return response()->json([
            'success' => true,
            'document' => $document,
            'serviceDetails' => $serviceDetails
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in PhieuGiaoNhanHomGiong show: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin phiếu giao nhận: ' . $e->getMessage()
        ], 500);
    }
}
}
