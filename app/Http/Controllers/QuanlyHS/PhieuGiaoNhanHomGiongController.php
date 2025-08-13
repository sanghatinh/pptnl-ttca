<?php

namespace App\Http\Controllers\QuanlyHS;

use App\Http\Controllers\Controller;
use App\Models\BienBanNghiemThuHomGiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PhieuGiaoNhanHomGiongController extends Controller
{
 



public function index(Request $request)
{
    try {
        // Get user type and user info from headers set by JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        
        // Debug: Log headers received
        \Log::info('Headers received in PhieuGiaoNhanHomGiong index:', [
            'X-User-Type' => $request->header('X-User-Type'),
            'X-User-ID' => $request->header('X-User-ID'),
            'X-User-Position' => $request->header('X-User-Position'),
            'X-User-Station' => $request->header('X-User-Station'),
            'X-User-Ma-Nhan-Vien' => $request->header('X-User-Ma-Nhan-Vien'),
            'X-Supplier-Number' => $request->header('X-Supplier-Number')
        ]);
        
        // Start with base query on bien_ban_nghiem_thu_hom_giong
        $query = DB::table('bien_ban_nghiem_thu_hom_giong as hg')
            // Join with document_mapping_homgiong to get document_code
            ->leftJoin('document_mapping_homgiong as dmhg', 'hg.ma_so_phieu', '=', 'dmhg.ma_so_phieu')
            // Join with document_delivery to get creator_id, receiver_id, received_date, status
            ->leftJoin('document_delivery as dd', 'dmhg.document_code', '=', 'dd.document_code')
            // Join with users table for creator name (nguoi_giao_ho_so)
            ->leftJoin('users as creator', 'dd.creator_id', '=', 'creator.id')
            // Join with users table for receiver name (nguoi_nhan_ho_so)
            ->leftJoin('users as receiver', 'dd.receiver_id', '=', 'receiver.id')
            // Join for payment status information using the latest log entry
            ->leftJoin(\DB::raw("(
                SELECT l1.ma_so_phieu, l1.ma_trinh_thanh_toan, l1.ma_de_nghi_giai_ngan
                FROM logs_phieu_trinh_thanh_toan_homgiong l1
                JOIN (
                    SELECT ma_so_phieu, MAX(id) as max_id
                    FROM logs_phieu_trinh_thanh_toan_homgiong
                    GROUP BY ma_so_phieu
                ) l2 ON l1.ma_so_phieu = l2.ma_so_phieu AND l1.id = l2.max_id
            ) as logs"), 'hg.ma_so_phieu', '=', 'logs.ma_so_phieu')
            // Join with payment request table to get payment status
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan');

        // Apply role-based filtering based on user type
        if ($userType === 'farmer') {
            // For farmer users
            $supplierId = $request->header('X-Supplier-Number');
            
            \Log::info('Farmer authentication details:', [
                'supplierId' => $supplierId,
                'userType' => $userType
            ]);
            
            if (!$supplierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer identifier not found'
                ], 401);
            }
            
            // Debug: ดูข้อมูลทั้งหมดใน table ก่อน filter
            $totalRecords = \DB::table('bien_ban_nghiem_thu_hom_giong')->count();
            \Log::info('Total records in bien_ban_nghiem_thu_hom_giong: ' . $totalRecords);
            
            // Debug: ดูข้อมูล customer codes ที่มีในระบบ
            $sampleRecords = \DB::table('bien_ban_nghiem_thu_hom_giong')
                ->select('ma_so_phieu', 'ma_khach_hang_CN', 'ma_khach_hang_DN', 'ma_khach_hang_giao_hom', 'ma_khach_hang_giao_hom_DN')
                ->limit(5)
                ->get();
            \Log::info('Sample customer codes in database:', $sampleRecords->toArray());
            
            // Filter records based on farmer's customer codes
            $query->where(function($q) use ($supplierId) {
                $q->where('hg.ma_khach_hang_CN', $supplierId)
                  ->orWhere('hg.ma_khach_hang_DN', $supplierId)
                  ->orWhere('hg.ma_khach_hang_giao_hom', $supplierId)
                  ->orWhere('hg.ma_khach_hang_giao_hom_DN', $supplierId);
            });
            
            // Debug: ตรวจสอบ SQL query ที่จะถูกรัน
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            \Log::info('SQL Query for farmer filter:', [
                'sql' => $sql,
                'bindings' => $bindings
            ]);
            
            // Debug: ทดสอบการ query โดยตรงกับ supplierId
            $directMatchCount = \DB::table('bien_ban_nghiem_thu_hom_giong')
                ->where(function($q) use ($supplierId) {
                    $q->where('ma_khach_hang_CN', $supplierId)
                      ->orWhere('ma_khach_hang_DN', $supplierId)
                      ->orWhere('ma_khach_hang_giao_hom', $supplierId)
                      ->orWhere('ma_khach_hang_giao_hom_DN', $supplierId);
                })->count();
            \Log::info('Direct match count for supplierId ' . $supplierId . ': ' . $directMatchCount);
            
        } else {
            // For employee users - use JWT authentication data from headers
            $userId = $request->header('X-User-ID');
            $userPosition = $request->header('X-User-Position');
            $userStation = $request->header('X-User-Station');
            $userMaNhanVien = $request->header('X-User-Ma-Nhan-Vien');
            
            \Log::info('Employee authentication details:', [
                'userId' => $userId,
                'userPosition' => $userPosition,
                'userStation' => $userStation,
                'userMaNhanVien' => $userMaNhanVien
            ]);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee authentication required'
                ], 401);
            }
            
            // Apply role-based filtering for employees based on position
            switch ($userPosition) {
                case 'department_head':
                case 'office_workers':
                    \Log::info('User has department_head/office_workers access - no filtering');
                    // See all records - no filtering needed
                    break;
                    
                case 'Station_Chief':
                    \Log::info('User is Station_Chief, filtering by station: ' . $userStation);
                    // Filter by user's station
                    if ($userStation) {
                        $query->where('hg.tram', $userStation);
                    }
                    break;
                    
                case 'Farm_worker':
                    \Log::info('User is Farm_worker, filtering by employee code: ' . $userMaNhanVien);
                    // Filter by employee code
                    if ($userMaNhanVien) {
                        $query->where('hg.ma_nhan_vien', $userMaNhanVien);
                    }
                    break;
                    
                case null:
                case '':
                    // กรณีที่ position เป็น null หรือ empty string
                    \Log::warning('User position is null or empty for user ID: ' . $userId);
                    return response()->json([
                        'success' => false,
                        'message' => 'User position not set. Please contact administrator to set your position.',
                        'debug_info' => [
                            'userType' => $userType,
                            'userPosition' => $userPosition,
                            'userId' => $userId,
                            'note' => 'Position field is null or empty in database'
                        ]
                    ], 403);
                    
                default:
                    \Log::warning('Unknown user position: ' . $userPosition);
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized position: ' . $userPosition,
                        'debug_info' => [
                            'userType' => $userType,
                            'userPosition' => $userPosition,
                            'userId' => $userId,
                            'valid_positions' => ['department_head', 'office_workers', 'Station_Chief', 'Farm_worker']
                        ]
                    ], 403);
            }
        }

        // Select all fields from hom_giong table and additional fields from related tables
        $query->select(
            'hg.*',
            'creator.full_name as nguoi_giao_ho_so',
            'receiver.full_name as nguoi_nhan_ho_so',
            'dd.received_date as ngay_nhan_ho_so',
            'dd.status as tinh_trang_giao_nhan_ho_so',
            // เพิ่มข้อมูล payment status
            'logs.ma_trinh_thanh_toan',
            'logs.ma_de_nghi_giai_ngan',
            'pttt.trang_thai_thanh_toan as tinh_trang_thanh_toan'
        );

        // Get the filtered records
        $records = $query->get();
        
        \Log::info('Records retrieved: ' . count($records));
        
        // Debug: แสดงข้อมูลบางส่วนของผลลัพธ์
        if (count($records) > 0) {
            \Log::info('First record sample:', [
                'ma_so_phieu' => $records[0]->ma_so_phieu ?? 'N/A',
                'ma_khach_hang_CN' => $records[0]->ma_khach_hang_CN ?? 'N/A',
                'ma_khach_hang_DN' => $records[0]->ma_khach_hang_DN ?? 'N/A',
                'ma_khach_hang_giao_hom' => $records[0]->ma_khach_hang_giao_hom ?? 'N/A',
                'ma_khach_hang_giao_hom_DN' => $records[0]->ma_khach_hang_giao_hom_DN ?? 'N/A',
                'tinh_trang_thanh_toan' => $records[0]->tinh_trang_thanh_toan ?? 'N/A'
            ]);
        }

        // Add processing_status to each record based on payment data  
        foreach ($records as $record) {
            if ($record->tinh_trang_thanh_toan === 'paid') {
                $record->processing_status = 'paid';
            } elseif ($record->ma_trinh_thanh_toan) {
                $record->processing_status = 'submitted';
            } elseif ($record->ma_de_nghi_giai_ngan) {
                $record->processing_status = 'processing';
            } else {
                $record->processing_status = 'received';
            }
        }

        return response()->json([
            'success' => true,
            'data' => $records,
            'user_type' => $userType,
            'debug_info' => [
                'supplier_id' => $request->header('X-Supplier-Number'),
                'total_records' => count($records),
                'filter_applied' => $userType === 'farmer' ? 'customer_codes' : 'employee_filter'
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in PhieuGiaoNhanHomGiong index: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving records: ' . $e->getMessage()
        ], 500);
    }
}

    // Import data from Excel/CSV
    public function importData(Request $request)
{
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
        ]
    ]);

    $importId = uniqid('import_homgiong_');
    try {
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

        $path = $file->storeAs('imports', $importId . '.' . $extension);

        \Cache::put('import_homgiong_' . $importId, [
            'status' => 'uploading',
            'total' => 0,
            'processed' => 0,
            'errors' => [],
            'success' => false,
            'finished' => false
        ], 3600);

        // ประมวลผลทันที (ไม่ใช้ dispatch)
        $this->processImportFile($path, $importId);

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
        $importData = \Cache::get('import_homgiong_' . $importId);
        $importData['status'] = 'processing';
        \Cache::put('import_homgiong_' . $importId, $importData, 3600);

        // Get file from storage - fix path to match filesystem configuration
        $filePath = storage_path('app/' . $path);

        // Check if file exists, try alternative paths if not
        if (!file_exists($filePath)) {
            $alternatePaths = [
                storage_path('app/private/' . $path),
                storage_path('app/public/' . $path),
                storage_path($path)
            ];
            $fileFound = false;
            foreach ($alternatePaths as $altPath) {
                if (file_exists($altPath)) {
                    $filePath = $altPath;
                    $fileFound = true;
                    break;
                }
            }
            if (!$fileFound) {
                throw new \Exception("File \"$path\" does not exist at any expected locations. Please check file upload settings.");
            }
        }

        // ตรวจสอบ extension แบบง่ายๆ
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
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
                    $spreadsheet = $reader->load($filePath);
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

        if (empty($rows) || count($rows) < 2) {
            throw new \Exception('File is empty or does not contain data rows. Please check the file and try again.');
        }

        $headers = array_map('trim', $rows[0]);
        $columnMap = $this->getColumnMapping($headers);

        if (empty($columnMap)) {
            throw new \Exception('Could not map columns from file to database. Required columns are missing. Please verify your file has the following columns: ma_so_phieu, tram, vu_dau_tu, ten_phieu');
        }

        array_shift($rows);

        $importData = \Cache::get('import_homgiong_' . $importId);
        $importData['total'] = count($rows);
        \Cache::put('import_homgiong_' . $importId, $importData, 3600);

        $processedCount = 0;
        $errors = [];

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

            // Delete records from bien_ban_nghiem_thu_hom_giong that will be replaced by the import
            if (!empty($importedHGIds)) {
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                \DB::table('bien_ban_nghiem_thu_hom_giong')
                    ->whereIn('ma_so_phieu', $importedHGIds)
                    ->delete();
                \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            // Insert new records in batches
            $batchSize = 100;
            $batches = array_chunk($rows, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                $batchData = [];
                foreach ($batch as $index => $row) {
                    $rowIndex = ($batchIndex * $batchSize) + $index;
                    try {
                        if (empty(array_filter($row))) continue;
                        $data = [];
                        foreach ($columnMap as $dbColumn => $excelIndex) {
                            if ($excelIndex !== false && isset($row[$excelIndex])) {
                                $value = trim($row[$excelIndex]);
                                if (in_array($dbColumn, ['tong_thuc_nhan', 'tong_tien', 'tong_tien_hom', 'tong_tien_boc_xep', 'tong_tien_van_chuyen'])) {
                                    $value = is_numeric($value) ? $value : 0;
                                }
                                $data[$dbColumn] = $value;
                            }
                        }
                        if (empty($data['ma_so_phieu'])) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": Missing required field 'ma_so_phieu'");
                        }
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
                if (!empty($batchData)) {
                    \DB::table('bien_ban_nghiem_thu_hom_giong')->insert($batchData);
                }
                $importData = \Cache::get('import_homgiong_' . $importId);
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                \Cache::put('import_homgiong_' . $importId, $importData, 3600);
                usleep(10000);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $errors[] = 'Transaction error: ' . $e->getMessage();
            throw $e;
        }

        $importData = \Cache::get('import_homgiong_' . $importId);
        $importData['status'] = 'completed';
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['success'] = true;
        $importData['finished'] = true;
        \Cache::put('import_homgiong_' . $importId, $importData, 3600);

        \Storage::delete($path);

    } catch (\Exception $e) {
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
            // Join with latest log entry for this document (if applicable)
            ->leftJoin(\DB::raw("(
                SELECT l1.ma_so_phieu, l1.ma_trinh_thanh_toan, l1.ma_de_nghi_giai_ngan
                FROM logs_phieu_trinh_thanh_toan_homgiong l1
                JOIN (
                    SELECT ma_so_phieu, MAX(id) as max_id
                    FROM logs_phieu_trinh_thanh_toan_homgiong
                    GROUP BY ma_so_phieu
                ) l2 ON l1.ma_so_phieu = l2.ma_so_phieu AND l1.id = l2.max_id
            ) as logs"), 'hg.ma_so_phieu', '=', 'logs.ma_so_phieu')
            // Join with payment request table to get payment status
            ->leftJoin('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->select(
                'hg.*',
                'creator.full_name as nguoi_giao',
                'receiver.full_name as nguoi_nhan',
                'dd.received_date as ngay_nhan',
                'dd.status as trang_thai_nhan_hs',
                'dd.id as document_delivery_id', // Add this line to get document_delivery ID
                'dmhg.document_code', // Add document_code directly from mapping
                'logs.ma_trinh_thanh_toan',
                'logs.ma_de_nghi_giai_ngan',
                'pttt.trang_thai_thanh_toan',
                'pttt.link_url as attachment_url' // Add this line to get the attachment URL
            )
            ->where('hg.ma_so_phieu', $id)
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin phiếu giao nhận hom giống'
            ], 404);
        }

        // Determine processing status based on payment status
        if ($document->trang_thai_thanh_toan === 'paid') {
            $document->processing_status = 'paid';
        } elseif ($document->ma_trinh_thanh_toan) {
            $document->processing_status = 'submitted';
        } elseif ($document->ma_de_nghi_giai_ngan) {
            $document->processing_status = 'processing';
        } else {
            $document->processing_status = 'received';
        }

        // Fetch service details from tb_chitiet_giaonhan_homgiong
        $serviceDetails = \DB::table('tb_chitiet_giaonhan_homgiong')
            ->where('ma_so_phieu', $document->ma_so_phieu)
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

public function checkAccess($id, Request $request)
{
    try {
        // Get user type and authentication info from headers set by JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        
        // Debug: Log headers received
        \Log::info('Headers received in PhieuGiaoNhanHomGiong checkAccess:', [
            'X-User-Type' => $request->header('X-User-Type'),
            'X-User-ID' => $request->header('X-User-ID'),
            'X-User-Position' => $request->header('X-User-Position'),
            'X-User-Station' => $request->header('X-User-Station'),
            'X-User-Ma-Nhan-Vien' => $request->header('X-User-Ma-Nhan-Vien'),
            'X-Supplier-Number' => $request->header('X-Supplier-Number')
        ]);
        
        // ค้นหาเอกสารที่ต้องการเข้าถึง
        $document = \DB::table('bien_ban_nghiem_thu_hom_giong')
            ->where('ma_so_phieu', $id)
            ->first();
            
        if (!$document) {
            return response()->json([
                'hasAccess' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        if ($userType === 'farmer') {
            // For farmer users
            $supplierId = $request->header('X-Supplier-Number');
            
            if (!$supplierId) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Farmer identifier not found'
                ], 401);
            }
            
            // Check if farmer has access to this document based on customer codes
            $hasAccess = ($document->ma_khach_hang_CN === $supplierId) ||
                        ($document->ma_khach_hang_DN === $supplierId) ||
                        ($document->ma_khach_hang_giao_hom === $supplierId) ||
                        ($document->ma_khach_hang_giao_hom_DN === $supplierId);
                        
            return response()->json([
                'hasAccess' => $hasAccess
            ]);
            
        } else {
            // For employee users - use JWT authentication data from headers
            $userId = $request->header('X-User-ID');
            $userPosition = $request->header('X-User-Position');
            $userStation = $request->header('X-User-Station');
            $userMaNhanVien = $request->header('X-User-Ma-Nhan-Vien');
            
            if (!$userId) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Employee authentication required'
                ], 401);
            }
            
            // ตรวจสอบสิทธิ์ตามบทบาท
            switch ($userPosition) {
                case 'department_head':
                case 'office_workers':
                    // สามารถเข้าถึงได้ทั้งหมด
                    return response()->json([
                        'hasAccess' => true
                    ]);
                    
                case 'Station_Chief':
                    // เข้าถึงได้เฉพาะเอกสารของ station ตัวเอง
                    $hasAccess = $document->tram === $userStation;
                    return response()->json([
                        'hasAccess' => $hasAccess
                    ]);
                    
                case 'Farm_worker':
                    // เข้าถึงได้เฉพาะเอกสารที่เป็นของพนักงานคนนั้น
                    $hasAccess = $document->ma_nhan_vien === $userMaNhanVien;
                    return response()->json([
                        'hasAccess' => $hasAccess
                    ]);
                    
                default:
                    // กรณีไม่มีบทบาทที่กำหนด
                    \Log::warning('Unknown position in PhieuGiaoNhanHomGiong checkAccess: ' . $userPosition);
                    return response()->json([
                        'hasAccess' => false,
                        'message' => 'Unauthorized position: ' . $userPosition,
                        'debug_info' => [
                            'userType' => $userType,
                            'userPosition' => $userPosition,
                            'userId' => $userId
                        ]
                    ]);
            }
        }
    } catch (\Exception $e) {
        \Log::error('Error in PhieuGiaoNhanHomGiong checkAccess: ' . $e->getMessage());
        return response()->json([
            'hasAccess' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Update financial information for all payment requests associated with a payment document
 * 
 * @param string $paymentRequestId Payment request ID
 * @param array $financialData Financial data to update
 * @return void
 */
private function updateFinancialInfo($paymentRequestId, $financialData)
{
    try {
        // Get all payment requests (disbursement codes) for the given payment document
        $disbursementRequests = Phieudenghithanhtoandv::where('ma_trinh_thanh_toan', $paymentRequestId)->get();
        
        if ($disbursementRequests->isEmpty()) {
            Log::info('No disbursement requests found for payment ID: ' . $paymentRequestId);
            return;
        }
        
        // Calculate average values for each disbursement request
        $count = $disbursementRequests->count();
        
        // For each disbursement request, update the financial information
        foreach ($disbursementRequests as $request) {
            // Get associated receipt IDs
            $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_de_nghi_giai_ngan', $request->ma_giai_ngan)
                ->pluck('ma_nghiem_thu');
                
            if ($receiptIds->isEmpty()) {
                Log::info('No receipts found for disbursement code: ' . $request->ma_giai_ngan);
                continue;
            }
            
            // Calculate totals from the associated receipts
            $totals = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select(
                    DB::raw('SUM(tong_tien) as total_amount'),
                    DB::raw('SUM(tong_tien_tam_giu) as total_hold_amount')
                )
                ->first();
                
            // Set customer identifiers for debt deduction and interest calculation
            $customerRef = null;
            $customerField = null;
            
            if (!empty($request->ma_khach_hang_ca_nhan)) {
                $customerRef = $request->ma_khach_hang_ca_nhan;
                $customerField = 'Ma_Khach_Hang_Ca_Nhan';
            } elseif (!empty($request->ma_khach_hang_doanh_nghiep)) {
                $customerRef = $request->ma_khach_hang_doanh_nghiep;
                $customerField = 'Ma_Khach_Hang_Doanh_Nghiep';
            }
            
            // Get parent payment request for proposal number
            $parentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentRequestId)
                ->first();
                
            $proposalNumber = $parentRequest ? $parentRequest->so_to_trinh : null;
            
            // Calculate total debt deduction (Da_Tra_Goc) and interest (Tien_Lai)
            $debtAndInterest = [
                'total_deduction' => 0,
                'total_interest' => 0
            ];
            
            if ($customerRef && $proposalNumber) {
                $debtRecords = DB::table('Logs_Phieu_Tinh_Lai_dv')
                    ->where($customerField, $customerRef)
                    ->where('So_Tro_Trinh', $proposalNumber)
                    ->select(
                        DB::raw('SUM(Da_Tra_Goc) as total_deduction'),
                        DB::raw('SUM(Tien_Lai) as total_interest')
                    )
                    ->first();
                    
                if ($debtRecords) {
                    $debtAndInterest = [
                        'total_deduction' => $debtRecords->total_deduction ?? 0,
                        'total_interest' => $debtRecords->total_interest ?? 0
                    ];
                }
            }
            
            // Calculate remaining amount
            $totalAmount = $totals->total_amount ?? 0;
            $totalHoldAmount = $totals->total_hold_amount ?? 0;
            $totalDeduction = $debtAndInterest['total_deduction'];
            $totalInterest = $debtAndInterest['total_interest'];
            $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction + $totalInterest;
            
            // Update the financial information
            $request->tong_tien = $totalAmount;
            $request->tong_tien_tam_giu = $totalHoldAmount;
            $request->tong_tien_khau_tru = $totalDeduction;
            $request->tong_tien_lai_suat = $totalInterest;
            $request->tong_tien_thanh_toan_con_lai = $totalRemaining;
            
            // Update payment date if status is paid
            if (isset($financialData['payment_date'])) {
                $request->ngay_thanh_toan = $financialData['payment_date'];
            }
            
            $request->save();
            
            Log::info('Updated financial information for disbursement: ' . $request->ma_giai_ngan);
        }
    } catch (\Exception $e) {
        Log::error('Error updating financial information: ' . $e->getMessage());
    }
}



/**
 * ดึงประวัติการดำเนินการของเอกสาร
 */
public function getHistory($id)
{
    try {
        \Log::info("Processing history requested for phieu giao nhan hom giong ID: " . $id);
        // Array to store our history entries
        $historyItems = [];
        
        // Get the document info
        $document = \DB::table('bien_ban_nghiem_thu_hom_giong')
            ->where('ma_so_phieu', $id)
            ->first();
            
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin phiếu giao nhận hom giống'
            ], 404);
        }

        // ไม่ต้องสร้าง creating history อัตโนมัติ แต่ให้หาจากข้อมูลจริง
        $creatingDate = $document->created_at ?? $document->updated_at ?? now();
        
        // พยายามหาข้อมูลเพิ่มเติมจาก document_mapping_homgiong (ถ้ามี)
        $documentMapping = \DB::table('document_mapping_homgiong')
            ->where('ma_so_phieu', $id)
            ->first();
            
        if ($documentMapping) {
            $documentCode = $documentMapping->document_code;
            
            // Get creating info
            $creatingInfo = \DB::table('document_delivery as dd')
                ->join('users as u', 'dd.creator_id', '=', 'u.id')
                ->where('dd.document_code', $documentCode)
                ->select('dd.created_at as date', 'u.full_name as user')
                ->first();
                
            if ($creatingInfo) {
                // เพิ่มประวัติเฉพาะเมื่อมีข้อมูล creator จริงๆ
                $creatingDate = new \DateTime($creatingInfo->date);
                
                $historyItems['creating'] = [
                    'action' => 'creating',
                    'date' => $creatingInfo->date,
                    'user' => $creatingInfo->user,
                    'days_since_previous' => 0, // First step always has 0 days
                    'note' => 'Hồ sơ đang được tạo mới trong hệ thống.'
                ];
            }
            
            // Get received info
            $receivedInfo = \DB::table('document_delivery as dd')
                ->join('users as u', 'dd.receiver_id', '=', 'u.id')
                ->where('dd.document_code', $documentCode)
                ->select('dd.received_date as date', 'u.full_name as user')
                ->first();
                
            if ($receivedInfo) {
                // Fix: Ensure proper date handling for different formats
                $receivedDateStr = $receivedInfo->date;
                $receivedDate = null;
                
                // Handle different possible date formats
                try {
                    // First try with datetime format
                    $receivedDate = new \DateTime($receivedDateStr);
                } catch (\Exception $e) {
                    try {
                        $receivedDate = \DateTime::createFromFormat('Y-m-d', $receivedDateStr);
                    } catch (\Exception $e2) {
                        // Default to current date if all parsing fails
                        $receivedDate = new \DateTime();
                    }
                }
                
                // Calculate days between receiving and creating
                $daysSincePrevious = 0;
                if ($receivedDate instanceof \DateTime && isset($creatingDate) && $creatingDate instanceof \DateTime) {
                    $interval = $receivedDate->diff($creatingDate);
                    $daysSincePrevious = $interval->days;
                    // If dates are the same except for time, ensure we show at least 1 day if they're on different calendar days
                    if ($daysSincePrevious == 0 && $receivedDate->format('Y-m-d') != $creatingDate->format('Y-m-d')) {
                        $daysSincePrevious = 1;
                    }
                }
                
                $historyItems['received'] = [
                    'action' => 'received',
                    'date' => $receivedInfo->date,
                    'user' => $receivedInfo->user,
                    'days_since_previous' => $daysSincePrevious,
                    'note' => 'Hồ sơ đã được nhận và xác nhận trong hệ thống.'
                ];
            }
        } else {
            // หากไม่พบ document_mapping_homgiong แต่มีข้อมูลการรับเอกสารในตารางอื่น
            if (!empty($document->ngay_nhan) && !empty($document->nguoi_nhan)) {
                try {
                    $receivedDate = new \DateTime($document->ngay_nhan);
                    $creatingDateObj = new \DateTime($creatingDate);
                    $daysSincePrevious = $receivedDate->diff($creatingDateObj)->days;
                    
                    $historyItems['received'] = [
                        'action' => 'received',
                        'date' => $document->ngay_nhan,
                        'user' => $document->nguoi_nhan,
                        'days_since_previous' => $daysSincePrevious,
                        'note' => 'Hồ sơ đã được nhận trong hệ thống.'
                    ];
                } catch (\Exception $e) {
                    // ถ้าไม่สามารถแปลงวันที่ได้ ไม่ต้องเพิ่มประวัติ
                    \Log::warning("Could not parse received date: " . $e->getMessage());
                }
            }
        }
        
        // Get logs for this document from logs_phieu_trinh_thanh_toan_homgiong
        $logInfo = \DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_so_phieu', $id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($logInfo && $logInfo->ma_trinh_thanh_toan) {
            // Get processing info
            $processingInfo = \DB::table('action_phieu_trinh_thanh_toan_homgiong as apt')
                ->join('users', 'apt.action_by', '=', 'users.id')
                ->where('ma_trinh_thanh_toan', $logInfo->ma_trinh_thanh_toan)
                ->where('action', 'processing')
                ->orderBy('apt.id', 'desc')
                ->select('apt.action_date as date', 'users.full_name as user')
                ->first();
                
            if ($processingInfo) {
                $processingDate = new \DateTime($processingInfo->date);
                $previousDate = null;
                $previousStep = null;
                
                // หาวันที่ของขั้นตอนก่อนหน้า
                if (isset($historyItems['received'])) {
                    try {
                        $previousDate = new \DateTime($historyItems['received']['date']);
                        $previousStep = 'received';
                    } catch (\Exception $e) {
                        // ถ้าไม่สามารถแปลงวันที่ได้ ตรวจสอบขั้นตอน creating
                        if (isset($historyItems['creating'])) {
                            try {
                                $previousDate = new \DateTime($historyItems['creating']['date']);
                                $previousStep = 'creating';
                            } catch (\Exception $e2) {
                                $previousDate = null;
                            }
                        }
                    }
                } elseif (isset($historyItems['creating'])) {
                    try {
                        $previousDate = new \DateTime($historyItems['creating']['date']);
                        $previousStep = 'creating';
                    } catch (\Exception $e) {
                        $previousDate = null;
                    }
                }
                
                // เพิ่มประวัติ processing เมื่อมีข้อมูล
                $daysSincePrevious = 0;
                if ($previousDate) {
                    $daysSincePrevious = $processingDate->diff($previousDate)->days;
                }
                
                $historyItems['processing'] = [
                    'action' => 'processing',
                    'date' => $processingInfo->date,
                    'user' => $processingInfo->user,
                    'days_since_previous' => $daysSincePrevious,
                    'note' => 'Hồ sơ đang được xử lý, vui lòng chờ.'
                ];
                
                // เพิ่มข้อมูลขั้นตอนก่อนหน้า เพื่อใช้อ้างอิงในอนาคต
                if ($previousStep) {
                    $historyItems['processing']['previous_step'] = $previousStep;
                }
            }
            
            // Get submitted info
            $submittedInfo = \DB::table('action_phieu_trinh_thanh_toan_homgiong as apt')
                ->join('users', 'apt.action_by', '=', 'users.id')
                ->where('ma_trinh_thanh_toan', $logInfo->ma_trinh_thanh_toan)
                ->where('action', 'submitted')
                ->orderBy('apt.id', 'desc')
                ->select('apt.action_date as date', 'users.full_name as user')
                ->first();
                
            if ($submittedInfo) {
                $submittedDate = new \DateTime($submittedInfo->date);
                $previousDate = null;
                $previousStep = null;
                $daysSincePrevious = 0;
                $timeNote = "";
                
                // หาขั้นตอนก่อนหน้าที่มีอยู่จริง
                if (isset($historyItems['processing'])) {
                    $previousStep = 'processing';
                    try {
                        $previousDate = new \DateTime($historyItems['processing']['date']);
                        
                        // คำนวณเวลาระหว่าง processing และ submitted
                        $interval = $submittedDate->diff($previousDate);
                        $totalHours = ($interval->days * 24) + $interval->h;
                        $daysSincePrevious = $interval->days;
                        
                        // เพิ่มบันทึกเวลาเมื่ออยู่ในวันเดียวกัน
                        $sameDay = $submittedDate->format('Y-m-d') === $previousDate->format('Y-m-d');
                        if ($sameDay && $totalHours > 0) {
                            $timeNote = " (sau " . $totalHours . " giờ)";
                        }
                    } catch (\Exception $e) {
                        // ถ้าแปลงวันที่ไม่สำเร็จ ใช้ค่าเริ่มต้น
                        $daysSincePrevious = 0;
                    }
                } elseif (isset($historyItems['received'])) {
                    $previousStep = 'received';
                    try {
                        $previousDate = new \DateTime($historyItems['received']['date']);
                        $daysSincePrevious = $submittedDate->diff($previousDate)->days;
                    } catch (\Exception $e) {
                        $daysSincePrevious = 0;
                    }
                } elseif (isset($historyItems['creating'])) {
                    $previousStep = 'creating';
                    try {
                        $previousDate = new \DateTime($historyItems['creating']['date']);
                        $daysSincePrevious = $submittedDate->diff($previousDate)->days;
                    } catch (\Exception $e) {
                        $daysSincePrevious = 0;
                    }
                }
                
                // เพิ่มประวัติ submitted
                $historyItems['submitted'] = [
                    'action' => 'submitted',
                    'date' => $submittedInfo->date,
                    'user' => $submittedInfo->user,
                    'days_since_previous' => $daysSincePrevious,
                    'note' => 'Hồ sơ đã được chuyển đến phòง kế toán.' . $timeNote
                ];
                
                // เพิ่มข้อมูลขั้นตอนก่อนหน้า
                if ($previousStep) {
                    $historyItems['submitted']['previous_step'] = $previousStep;
                }
            }
            
            // Get paid info
            if ($logInfo->ma_de_nghi_giai_ngan) {
                $paidDateInfo = \DB::table('tb_de_nghi_thanhtoan_homgiong')
                    ->where('ma_giai_ngan', $logInfo->ma_de_nghi_giai_ngan)
                    ->select('ngay_thanh_toan as date')
                    ->first();
                    
                $paidUserInfo = \DB::table('action_phieu_trinh_thanh_toan_homgiong as apt')
                    ->join('users', 'apt.action_by', '=', 'users.id')
                    ->where('ma_trinh_thanh_toan', $logInfo->ma_trinh_thanh_toan)
                    ->where('action', 'paid')
                    ->orderBy('apt.id', 'desc')
                    ->select('users.full_name as user')
                    ->first();
                
                if ($paidDateInfo && $paidUserInfo) {
                    // หาขั้นตอนก่อนหน้า - อาจเป็น submitted, processing หรือ received
                    $previousStep = null;
                    $previousDate = null;
                    
                    foreach (['submitted', 'processing', 'received', 'creating'] as $step) {
                        if (isset($historyItems[$step])) {
                            $previousStep = $step;
                            try {
                                $previousDate = new \DateTime($historyItems[$step]['date']);
                                break;
                            } catch (\Exception $e) {
                                // ถ้าแปลงวันที่ไม่สำเร็จ ลองขั้นตอนถัดไป
                                continue;
                            }
                        }
                    }
                    
                    // คำนวณวันที่แตกต่าง
                    $daysSincePrevious = 0;
                    if ($previousDate) {
                        try {
                            $paidDate = new \DateTime($paidDateInfo->date);
                            $daysSincePrevious = $paidDate->diff($previousDate)->days;
                        } catch (\Exception $e) {
                            // ในกรณีที่มีปัญหากับการคำนวณวันที่ ใช้ค่าเริ่มต้น
                            $daysSincePrevious = 0;
                        }
                    }
                    
                    // เพิ่มประวัติ paid
                    $historyItems['paid'] = [
                        'action' => 'paid',
                        'date' => $paidDateInfo->date,
                        'user' => $paidUserInfo->user,
                        'days_since_previous' => $daysSincePrevious,
                        'note' => 'Thanh toán đã hoàn tất thông qua chuyển khoản ngân hàng.',
                        'amount' => $document->tong_tien ?? 0
                    ];
                    
                    // เพิ่มข้อมูลขั้นตอนก่อนหน้า
                    if ($previousStep) {
                        $historyItems['paid']['previous_step'] = $previousStep;
                    }
                }
            }
        }
        
        // Define the correct order of steps
        $stepOrder = ['creating', 'received', 'processing', 'submitted', 'paid'];
        
        // Create the final history array in the correct order
        $history = [];
        foreach ($stepOrder as $step) {
            if (isset($historyItems[$step])) {
                $history[] = $historyItems[$step];
            }
        }
        
        // บันทึก log ข้อมูลที่จะส่งกลับเพื่อช่วยในการตรวจสอบ
        \Log::info("History items to be returned for phieu giao nhan hom giong: " . count($history));
        
        // ถ้าไม่มีประวัติเลย ส่งกลับ array ว่างแทนที่จะสร้างข้อมูลเทียม
        return response()->json([
            'success' => true,
            'history' => $history // อาจเป็น array ว่างได้
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in getHistory for phieu giao nhan hom giong: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch sử xử lý: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Generate Report PDF for Phieu Giao Nhan Hom Giong
 */
public function generateReportTablePhieugiaonhanHG(Request $request)
{
    try {
        // Handle authentication for GET requests with token in query parameter
        if ($request->isMethod('get') && $request->has('token')) {
            $token = $request->query('token');
            
            try {
                // Validate token
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
            // For GET request, get filter_params from query string
            $filterParamsJson = $request->query('filter_params');
            if (!$filterParamsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid filter parameters'
                ], 400);
            }
            $filterParams = json_decode($filterParamsJson, true);
        } else {
            // For POST request, validate and get from form data
            $request->validate([
                'filter_params' => 'required|string'
            ]);
            $filterParams = json_decode($request->filter_params, true);
        }
        
        if (!$filterParams || !isset($filterParams['ma_so_phieu_list'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filter parameters'
            ], 400);
        }

        $maSoPhieuList = $filterParams['ma_so_phieu_list'];
        
        if (empty($maSoPhieuList)) {
            return response()->json([
                'success' => false,
                'message' => 'No records to generate report'
            ], 400);
        }

        // Build the main query
        $query = DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
            ->whereIn('bbhg.ma_so_phieu', $maSoPhieuList)
            ->select([
                'bbhg.ma_so_phieu',
                'bbhg.tram',
                'bbhg.doi_tac_giao_hom',
                'bbhg.doi_tac_giao_hom_DN',
                'bbhg.khach_hang_ca_nhan',
                'bbhg.khach_hang_doanh_nghiep',
                'bbhg.tong_thuc_nhan',
                'bbhg.tong_tien',
                'bbhg.vu_dau_tu'
            ]);

        $reportData = $query->get();

        if ($reportData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected records'
            ], 404);
        }

        // Process each record to get additional information
        $processedData = [];
        
        foreach ($reportData as $record) {
            // Chủ mía bên giao hom (Priority: doi_tac_giao_hom -> doi_tac_giao_hom_DN)
            $chuMiaBenGiaoHom = $record->doi_tac_giao_hom ?: $record->doi_tac_giao_hom_DN;
            
            // Chủ mía bên nhận hom (Priority: khach_hang_ca_nhan -> khach_hang_doanh_nghiep)
            $chuMiaBenNhanHom = $record->khach_hang_ca_nhan ?: $record->khach_hang_doanh_nghiep;

            // Tên Công việc - ค่าตายตัว
            $tenCongViec = 'Hom giống';

            // Get Mã giải ngân from logs_phieu_trinh_thanh_toan_homgiong
            $maGiaiNgan = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                ->where('ma_so_phieu', $record->ma_so_phieu)
                ->value('ma_de_nghi_giai_ngan');

            // Get Đợt thanh toán and Trạng thái thanh toán
            $dotThanhToan = 'N/A';
            $trangThaiThanhToan = 'Chưa thanh toán';

            if ($maGiaiNgan) {
                // Get ma_trinh_thanh_toan from logs_phieu_trinh_thanh_toan_homgiong
                $maTrinh = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                    ->where('ma_de_nghi_giai_ngan', $maGiaiNgan)
                    ->value('ma_trinh_thanh_toan');

                if ($maTrinh) {
                    // Get payment info from tb_phieu_trinh_thanh_toan_homgiong
                    $paymentInfo = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                        ->where('ma_trinh_thanh_toan', $maTrinh)
                        ->select('so_dot_thanh_toan', 'trang_thai_thanh_toan')
                        ->first();

                    if ($paymentInfo) {
                        $dotThanhToan = $paymentInfo->so_dot_thanh_toan ?: 'N/A';
                        $trangThaiThanhToan = $this->formatPaymentStatusForReport($paymentInfo->trang_thai_thanh_toan);
                    }
                }
            }

            $processedData[] = [
                'ma_so_phieu' => $record->ma_so_phieu,
                'tram' => $record->tram ?: 'N/A',
                'chu_mia_ben_giao_hom' => $chuMiaBenGiaoHom ?: 'N/A',
                'chu_mia_ben_nhan_hom' => $chuMiaBenNhanHom ?: 'N/A',
                'ten_cong_viec' => $tenCongViec,
                'tong_thuc_nhan' => $record->tong_thuc_nhan ?: 0,
                'tong_tien' => $record->tong_tien ?: 0,
                'vu_dau_tu' => $record->vu_dau_tu ?: 'N/A',
                'ma_giai_ngan' => $maGiaiNgan ?: 'N/A',
                'dot_thanh_toan' => $dotThanhToan,
                'trang_thai_thanh_toan' => $trangThaiThanhToan
            ];
        }

        // Generate report info
        $reportInfo = [
            'title' => 'Báo cáo Phiếu giao nhận hom giống',
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($processedData),
            'report_type' => $filterParams['report_type'] ?? 'all_pages'
        ];

        // Return view for printing (instead of PDF download)
        return view('Print.ReportPGNHG', [
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
        Log::error('Error generating PGNHG report: ' . $e->getMessage(), [
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
 * Format payment status for report display
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
