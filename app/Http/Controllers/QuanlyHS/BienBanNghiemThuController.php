<?php

namespace App\Http\Controllers\QuanlyHS;

use Illuminate\Http\Request;
use App\Models\BienBanNghiemThu;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller; // Add this import

use App\Models\QuanlyHS\PaymentRequestAction; // เพิ่ม model สำหรับตาราง Action
use App\Models\QuanlyHS\PaymentRequest;
use App\Models\QuanlyHS\PaymentRequestLog;
use App\Models\User;

use PhpOffice\PhpSpreadsheet\IOFactory;


class BienBanNghiemThuController extends Controller
{
   
 public function index(Request $request)
{
    try {
        $user = auth()->user();
        
        // Start with base query on bien ban table
        $query = \DB::table('tb_bien_ban_nghiemthu_dv as bb')
            // Join with document_mapping to get document_code
            ->leftJoin('document_mapping as dm', 'bb.ma_nghiem_thu', '=', 'dm.ma_nghiem_thu_bb')
            // Join with document_delivery to get creator_id, receiver_id, received_date, status
            ->leftJoin('document_delivery as dd', 'dm.document_code', '=', 'dd.document_code')
            // Join with users table for creator name (nguoi_giao)
            ->leftJoin('users as creator', 'dd.creator_id', '=', 'creator.id')
            // Join with users table for receiver name (nguoi_nhan)
            ->leftJoin('users as receiver', 'dd.receiver_id', '=', 'receiver.id');
            
        // Join for payment status information using the latest log entry
        $query->leftJoin(\DB::raw("(
                  SELECT l1.ma_nghiem_thu, l1.ma_trinh_thanh_toan, l1.ma_de_nghi_giai_ngan
                  FROM Logs_phieu_trinh_thanh_toan l1
                  JOIN (
                      SELECT ma_nghiem_thu, MAX(id) as max_id
                      FROM Logs_phieu_trinh_thanh_toan
                      GROUP BY ma_nghiem_thu
                  ) l2 ON l1.ma_nghiem_thu = l2.ma_nghiem_thu AND l1.id = l2.max_id
             ) as logs"), 
             'bb.ma_nghiem_thu', '=', 'logs.ma_nghiem_thu')
              ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan');

        // Apply role-based filtering
        switch ($user->position) {
            case 'department_head':
            case 'office_workers':
                // See all records - no filtering needed
                break;
                
            case 'Station_Chief':
                // Filter by user's station
                $query->where('bb.tram', $user->station);
                break;
                
            case 'Farm_worker':
                // Filter by employee code
                $query->where('bb.ma_nhan_vien', $user->ma_nhan_vien);
                break;
                
            default:
                // Default case - return no records
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized position'
                ], 403);
        }

        // Select all fields from bien_ban table and additional fields from related tables
        $query->select(
            'bb.*',
            'creator.full_name as nguoi_giao',
            'receiver.full_name as nguoi_nhan',
            'dd.received_date as ngay_nhan',
            'dd.status as trang_thai_nhan_hs',
            'logs.ma_trinh_thanh_toan',
            'logs.ma_de_nghi_giai_ngan',
            'pttt.trang_thai_thanh_toan'
        );

        // Get the filtered records
        $records = $query->get();
        
        // Add processing_status to each record based on payment data
        foreach ($records as $record) {
            if ($record->trang_thai_thanh_toan === 'paid') {
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
            'data' => $records
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in BienBanNghiemThu index: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving records: ' . $e->getMessage()
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
            \Cache::put('import_' . $importId, [
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
        $importData = \Cache::get('import_' . $importId);
        
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
            $importData = \Cache::get('import_' . $importId);
            $importData['status'] = 'processing';
            \Cache::put('import_' . $importId, $importData, 3600);
            
            // Get file from storage - fix path to match filesystem configuration
            $filePath = storage_path('app/private/' . $path);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                // Try alternative path if the file doesn't exist at the expected location
                $alternativePath = storage_path('app/' . $path);
                if (file_exists($alternativePath)) {
                    $filePath = $alternativePath;
                } else {
                    throw new \Exception("File \"$path\" does not exist at expected locations.");
                }
            }
            
            // Determine file type and process accordingly
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
            
            // Map Excel/CSV columns to database columns
            $columnMap = $this->getColumnMapping($headers);
            
            if (empty($columnMap)) {
                throw new \Exception('Could not map columns from file to database. Required columns are missing. Please verify your file has the following columns: ma_nghiem_thu, tram, vu_dau_tu, tieu_de');
            }
            
            // Skip header row
            array_shift($rows);
            
            // Update total records count
            $importData = \Cache::get('import_' . $importId);
            $importData['total'] = count($rows);
            \Cache::put('import_' . $importId, $importData, 3600);
            
            $processedCount = 0;
            $errors = [];
            
            // Use a simple try-catch instead of the callback-style transaction
            try {
                \DB::beginTransaction();
                
                // Store existing ma_nghiem_thu values that have mappings
                $existingMappedBienBan = \DB::table('document_mapping')
                    ->whereNotNull('ma_nghiem_thu_bb')
                    ->pluck('ma_nghiem_thu_bb')
                    ->toArray();
                
                // Extract ma_nghiem_thu values from the import file
                $importedBienBanIds = [];
                foreach ($rows as $row) {
                    $maIndex = $columnMap['ma_nghiem_thu'];
                    if ($maIndex !== false && isset($row[$maIndex]) && !empty($row[$maIndex])) {
                        $importedBienBanIds[] = $row[$maIndex];
                    }
                }
                
                // Get only bienban IDs that will be affected but preserve mappings
                $preserveMappings = array_intersect($existingMappedBienBan, $importedBienBanIds);
                
                // First temporarily disable foreign key checks 
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                
                // Delete records from tb_bien_ban_nghiemthu_dv that will be replaced by the import
                // but preserve records referenced in document_mapping that aren't in the import
                if (!empty($importedBienBanIds)) {
                    \DB::table('tb_bien_ban_nghiemthu_dv')
                        ->whereIn('ma_nghiem_thu', $importedBienBanIds)
                        ->delete();
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
                                    // For numeric fields, ensure proper formatting
                                    if (in_array($dbColumn, ['tong_tien', 'tong_tien_dich_vu', 'tong_tien_tam_giu', 'tong_tien_thanh_toan'])) {
                                        // Remove currency symbols and formatting
                                        $value = preg_replace('/[^0-9.]/', '', $row[$excelIndex]);
                                        $data[$dbColumn] = $value ?: 0;
                                    } else {
                                        $data[$dbColumn] = $row[$excelIndex];
                                    }
                                }
                            }
                            
                            // Set required fields if missing
                            if (empty($data['ma_nghiem_thu'])) {
                                throw new \Exception('Row ' . ($rowIndex + 2) . ': Missing required field: ma_nghiem_thu');
                            }
                            
                            if (empty($data['tram'])) {
                                $data['tram'] = '';
                            }
                            
                            if (empty($data['vu_dau_tu'])) {
                                $data['vu_dau_tu'] = '';
                            }
                            
                            if (empty($data['tieu_de'])) {
                                $data['tieu_de'] = '';
                            }
                            
                            // Set timestamps
                            $data['created_at'] = now();
                            $data['updated_at'] = now();
                            
                            // Add to batch data
                            $batchData[] = $data;
                            
                            $processedCount++;
                            
                        } catch (\Exception $e) {
                            // Log error but continue processing
                            $errors[] = 'Row ' . ($rowIndex + 2) . ': ' . $e->getMessage();
                        }
                    }
                    
                    // Insert batch if not empty
                    if (!empty($batchData)) {
                        \DB::table('tb_bien_ban_nghiemthu_dv')->insert($batchData);
                    }
                    
                    // Update progress after each batch
                    $importData = \Cache::get('import_' . $importId);
                    $importData['processed'] = $processedCount;
                    $importData['errors'] = $errors;
                    \Cache::put('import_' . $importId, $importData, 3600);
                    
                    // Give the database a moment to breathe
                    usleep(10000); // 10ms pause between batches
                }
                
                // If we get here, commit the transaction
                \DB::commit();
            } catch (\Exception $e) {
                // Rollback the transaction
                \DB::rollBack();
                // Add the error to our errors array
                $errors[] = 'Transaction error: ' . $e->getMessage();
                throw $e; // Re-throw to be caught by the outer catch block
            }
            
            // Update final status
            $importData = \Cache::get('import_' . $importId);
            $importData['status'] = 'completed';
            $importData['processed'] = $processedCount;
            $importData['errors'] = $errors;
            $importData['success'] = true;
            $importData['finished'] = true;
            \Cache::put('import_' . $importId, $importData, 3600);
            
            // Delete the temporary file
            \Storage::delete($path);
            
        } catch (\Exception $e) {
            // Update error status
            $importData = \Cache::get('import_' . $importId);
            $importData['status'] = 'failed';
            $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
            $importData['success'] = false;
            $importData['finished'] = true;
            \Cache::put('import_' . $importId, $importData, 3600);
            
            \Log::error('Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    // Map columns from Excel/CSV headers to database columns
    private function getColumnMapping($headers)
    {
        // Define mapping from common header names to database columns
        $possibleHeaderMappings = [
            'ma_nghiem_thu' => ['mã nghiệm thu', 'ma nghiem thu', 'ma_nghiem_thu', 'mã biên bản', 'ma bien ban', 'bien ban'],
            'tram' => ['trạm', 'tram', 'nha may', 'nhà máy', 'station'],
            'vu_dau_tu' => ['vụ đầu tư', 'vu dau tu', 'vu_dau_tu', 'investment', 'vụ', 'vu'],
            'tieu_de' => ['tiêu đề', 'tieu de', 'tieu_de', 'title', 'nội dung', 'noi dung'],
            'khach_hang_ca_nhan_dt_mia' => ['khách hàng cá nhân', 'khach hang ca nhan', 'khách hàng', 'khach hang', 'individual customer'],
            'khach_hang_doanh_nghiep_dt_mia' => ['doanh nghiệp', 'doanh nghiep', 'khách hàng doanh nghiệp', 'khach hang doanh nghiep', 'business customer', 'company'],
            'hop_dong_dau_tu_mia' => ['hợp đồng đầu tư mía', 'hop dong dau tu mia', 'hợp đồng', 'hop dong', 'contract'],
            'hinh_thuc_thuc_hien_dv' => ['hình thức thực hiện dịch vụ', 'hinh thuc thuc hien dich vu', 'hình thức', 'hinh thuc', 'service type'],
            'hop_dong_cung_ung_dich_vu' => ['hợp đồng cung ứng dịch vụ', 'hop dong cung ung dich vu', 'hợp đồng dịch vụ', 'hop dong dich vu', 'service contract'],
            'tong_tien' => ['tổng tiền', 'tong tien', 'total amount', 'total'],
            'tong_tien_dich_vu' => ['tổng tiền dịch vụ', 'tong tien dich vu', 'service amount', 'tiền dịch vụ'],
            'tong_tien_tam_giu' => ['tổng tiền tạm giữ', 'tong tien tam giu', 'retention amount', 'tiền tạm giữ', 'Tổng tiền thanh toán còn lại'],
            'tong_tien_thanh_toan' => ['tổng tiền thanh toán', 'tong tien thanh toan', 'payment amount', 'tiền thanh toán'],
            'can_bo_nong_vu' => ['cán bộ nông vụ', 'can bo nong vu', 'officer', 'agricultural officer'],
            'tinh_trang' => ['tình trạng', 'tinh trang', 'status'],
            'tinh_trang_duyet' => ['tình trạng duyệt', 'tinh trang duyet', 'approval status', 'approval'],
            //เพี่ม column ma_nhan_vien
            'ma_nhan_vien' => ['mã nhân viên', 'ma nhan vien', 'employee code', 'employee id', 'Mã nhân viên (Cán bộ nông vụ) (Nhân viên)'],
            // New columns for customer IDs
            'MaKH_DTDV_Canhan' => ['Mã khách hàng (Đối tác cá nhân cung cấp dịch vụ) (Khách hàng)'],
            'MaKH_DTDV_DN' => ['Mã khách hàng (Đối tác cung cấp dịch vụ (KHDN)) (Khách hàng doanh nghiệp)'],
            'MaKH_Chumia_Canhan' => ['Mã khách hàng (Khách hàng cá nhân ĐT mía) (Khách hàng)'],
            'MaKH_Chumia_DN' => ['Mã khách hàng (Khách hàng doanh nghiệp ĐT mía) (Khách hàng doanh nghiệp)']
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
        $requiredColumns = ['ma_nghiem_thu', 'tram', 'vu_dau_tu', 'tieu_de'];
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
                $columnMap['ma_nghiem_thu'] = $columnMap['ma_nghiem_thu'] !== false ? $columnMap['ma_nghiem_thu'] : 0;
                $columnMap['tram'] = $columnMap['tram'] !== false ? $columnMap['tram'] : 1;
                $columnMap['vu_dau_tu'] = $columnMap['vu_dau_tu'] !== false ? $columnMap['vu_dau_tu'] : 2;
                $columnMap['tieu_de'] = $columnMap['tieu_de'] !== false ? $columnMap['tieu_de'] : 3;
            }
        }
        
        return $columnMap;
    }

public function show($id)
{
    try {
        // Get the main document info from bien ban table
        $document = \DB::table('tb_bien_ban_nghiemthu_dv as bb')
            ->leftJoin('document_mapping as dm', 'bb.ma_nghiem_thu', '=', 'dm.ma_nghiem_thu_bb')
            ->leftJoin('document_delivery as dd', 'dm.document_code', '=', 'dd.document_code')
            ->leftJoin('users as creator', 'dd.creator_id', '=', 'creator.id')
            ->leftJoin('users as receiver', 'dd.receiver_id', '=', 'receiver.id')
            // Join with latest log entry for this document
            ->leftJoin(\DB::raw("(
                SELECT l1.ma_nghiem_thu, l1.ma_trinh_thanh_toan, l1.ma_de_nghi_giai_ngan
                FROM Logs_phieu_trinh_thanh_toan l1
                JOIN (
                    SELECT ma_nghiem_thu, MAX(id) as max_id
                    FROM Logs_phieu_trinh_thanh_toan
                    GROUP BY ma_nghiem_thu
                ) l2 ON l1.ma_nghiem_thu = l2.ma_nghiem_thu AND l1.id = l2.max_id
            ) as logs"), 'bb.ma_nghiem_thu', '=', 'logs.ma_nghiem_thu')
            // Join with payment request table to get payment status
            ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->select(
                'bb.*',
                'creator.full_name as nguoi_giao',
                'receiver.full_name as nguoi_nhan',
                'dd.received_date as ngay_nhan',
                'dd.status as trang_thai_nhan_hs',
                'logs.ma_trinh_thanh_toan',
                'logs.ma_de_nghi_giai_ngan',
                'pttt.trang_thai_thanh_toan',
                'pttt.link_url as attachment_url' // Add this line to get the attachment URL
            )
            ->where('bb.ma_nghiem_thu', $id)
            ->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin biên bản nghiệm thu'
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

        // Fetch service details from tb_chitiet_dichvu_nt
        $serviceDetails = \DB::table('tb_chitiet_dichvu_nt')
            ->where('nghiem_thu_dich_vu', $document->tieu_de)
            ->get();

        return response()->json([
            'success' => true,
            'document' => $document,
            'serviceDetails' => $serviceDetails
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in BienBanNghiemThu show: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin biên bản: ' . $e->getMessage()
        ], 500);
    }
}

    public function checkAccess($id)
    {
        try {
            // ดึงข้อมูล user ที่กำลังเข้าใช้งาน
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            // ค้นหาเอกสารที่ต้องการเข้าถึง
            $document = \DB::table('tb_bien_ban_nghiemthu_dv')
                ->where('ma_nghiem_thu', $id)
                ->first();
                
            if (!$document) {
                return response()->json([
                    'hasAccess' => false,
                    'message' => 'Document not found'
                ], 404);
            }
            
            // ตรวจสอบสิทธิ์ตามบทบาท
            switch ($user->position) {
                case 'department_head':
                case 'office_workers':
                    // สามารถเข้าถึงได้ทั้งหมด
                    return response()->json([
                        'hasAccess' => true
                    ]);
                    
                case 'Station_Chief':
                    // เข้าถึงได้เฉพาะเอกสารของ station ตัวเอง
                    $hasAccess = $document->tram === $user->station;
                    return response()->json([
                        'hasAccess' => $hasAccess
                    ]);
                    
                case 'Farm_worker':
                    // เข้าถึงได้เฉพาะเอกสารที่เป็นของพนักงานคนนั้น
                    $hasAccess = $document->ma_nhan_vien === $user->ma_nhan_vien;
                    return response()->json([
                        'hasAccess' => $hasAccess
                    ]);
                    
                default:
                    // กรณีไม่มีบทบาทที่กำหนด
                    return response()->json([
                        'hasAccess' => false,
                        'message' => 'Undefined role permission'
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in checkAccess: ' . $e->getMessage());
            return response()->json([
                'hasAccess' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }


public function processingHistoryNghiemthuDV($id)
{
    try {
        \Log::info("Processing history requested for ID: " . $id);
        // Array to store our history entries
        $historyItems = [];
        
        // Get the document info
        $document = \DB::table('tb_bien_ban_nghiemthu_dv')
            ->where('ma_nghiem_thu', $id)
            ->first();
            
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin biên bản nghiệm thu'
            ], 404);
        }

        // Không tự động thêม creating history ที่นี่อีกต่อไป
        // แต่จะเพิ่มเฉพาะเมื่อพบข้อมูลจริงๆ จาก document_mapping หรือแหล่งอื่น
        $creatingDate = $document->created_at ?? $document->updated_at ?? now();
        
        // พยายามหาข้อมูลเพิ่มเติมจาก document_mapping (ถ้ามี)
        $documentMapping = \DB::table('document_mapping')
            ->where('ma_nghiem_thu_bb', $id)
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
            // หากไม่พบ document_mapping แต่มีข้อมูลการรับเอกสารในตารางอื่น
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
        
        // Get logs for this document from Logs_phieu_trinh_thanh_toan
        $logInfo = \DB::table('Logs_phieu_trinh_thanh_toan')
            ->where('ma_nghiem_thu', $id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($logInfo && $logInfo->ma_trinh_thanh_toan) {
            // Get processing info
            $processingInfo = \DB::table('Action_phieu_trinh_thanh_toan as apt')
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
            $submittedInfo = \DB::table('Action_phieu_trinh_thanh_toan as apt')
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
                    'note' => 'Hồ sơ đã được chuyển đến phòng kế toán.' . $timeNote
                ];
                
                // เพิ่มข้อมูลขั้นตอนก่อนหน้า
                if ($previousStep) {
                    $historyItems['submitted']['previous_step'] = $previousStep;
                }
            }
            
            // Get paid info
            if ($logInfo->ma_de_nghi_giai_ngan) {
                $paidDateInfo = \DB::table('tb_de_nghi_thanhtoan_dv')
                    ->where('ma_giai_ngan', $logInfo->ma_de_nghi_giai_ngan)
                    ->select('ngay_thanh_toan as date')
                    ->first();
                    
                $paidUserInfo = \DB::table('Action_phieu_trinh_thanh_toan as apt')
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
                        'amount' => $document->tong_tien_thanh_toan ?? 0
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
        \Log::info("History items to be returned: " . count($history));
        
        // ถ้าไม่มีประวัติเลย ส่งกลับ array ว่างแทนที่จะสร้างข้อมูลเทียม
        return response()->json([
            'success' => true,
            'history' => $history // อาจเป็น array ว่างได้
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in processingHistoryNghiemthuDV: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch sử xử lý: ' . $e->getMessage()
        ], 500);
    }
}


}