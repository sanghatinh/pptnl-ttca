<?php


namespace App\Http\Controllers\QuanlyHS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QuanlyHS\ChitietgiaonhanHomgiong;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChiTietPhieugiaonhanHGController extends Controller
{
    /**
     * Import data from Excel/CSV file
     */
    public function importData(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);

        // Generate a unique import ID
        $importId = uniqid('import_chitiet_hg_');
        
        try {
            $file = $request->file('file');
            $path = $file->storeAs('imports', $importId . '.' . $file->extension());
            
            // Store import info in cache for progress tracking
            \Cache::put('import_chitiet_hg_' . $importId, [
                'status' => 'uploading',
                'total' => 0,
                'processed' => 0,
                'errors' => [],
                'success' => false,
                'finished' => false
            ], 3600); // Cache for 1 hour
            
            // Process file in background
            dispatch(function() use ($path, $importId) {
                $this->processImportFile($path, $importId);
            })->afterResponse();
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully. Processing started.',
                'importId' => $importId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chi tiết HG Import error: ' . $e->getMessage());
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
        $importData = \Cache::get('import_chitiet_hg_' . $importId);
        
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
        $importData = \Cache::get('import_chitiet_hg_' . $importId);
        $importData['status'] = 'processing';
        \Cache::put('import_chitiet_hg_' . $importId, $importData, 3600);
        
        // Get file from storage
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
            throw new \Exception('Could not map columns from file to database. Required columns are missing.');
        }
        
        // Skip header row
        array_shift($rows);
        
        // Update total records count
        $importData = \Cache::get('import_chitiet_hg_' . $importId);
        $importData['total'] = count($rows);
        \Cache::put('import_chitiet_hg_' . $importId, $importData, 3600);
        
        $processedCount = 0;
        $errors = [];
        
        try {
            // Get the model's fillable fields to ensure consistent column count
            $model = new ChitietgiaonhanHomgiong();
            $fillableColumns = $model->getFillable();
            
            // Start transaction
            DB::beginTransaction();
            
            // Delete all existing records first (using delete() instead of truncate())
            ChitietgiaonhanHomgiong::query()->delete();
            
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
                        
                        // Create data array with all fillable fields initialized to default values
                        $data = [];
                        foreach ($fillableColumns as $column) {
                            if (in_array($column, [
                                'so_luong_dk', 'thuc_nhan', 'don_gia', 'thanh_tien_hom_giong',
                                'don_gia_don_chat', 'thanh_tien_don_chat', 'don_gia_boc_xep',
                                'thanh_tien_boc_xep', 'don_gia_van_chuyen', 'thanh_tien_van_chuyen',
                                'so_tien_dau_tu_hl', 'so_tien_dau_tu_khl'
                            ])) {
                                $data[$column] = 0;
                            } else if ($column === 'ngay_nhan') {
                                $data[$column] = null;
                            } else if ($column === 'tinh_trang_duyet') {
                                $data[$column] = 'Chưa duyệt';
                            } else if ($column === 'don_vi_tinh') {
                                $data[$column] = 'tấn';
                            } else {
                                $data[$column] = '';
                            }
                        }
                        
                        // Map data from Excel/CSV to database columns
                        foreach ($columnMap as $dbColumn => $excelIndex) {
                            if ($excelIndex !== false && isset($row[$excelIndex])) {
                                // For numeric fields, ensure proper formatting
                                if (in_array($dbColumn, [
                                    'so_luong_dk', 'thuc_nhan', 'don_gia', 'thanh_tien_hom_giong',
                                    'don_gia_don_chat', 'thanh_tien_don_chat', 'don_gia_boc_xep',
                                    'thanh_tien_boc_xep', 'don_gia_van_chuyen', 'thanh_tien_van_chuyen',
                                    'so_tien_dau_tu_hl', 'so_tien_dau_tu_khl'
                                ])) {
                                    // Remove currency symbols and formatting
                                    $value = preg_replace('/[^0-9.]/', '', $row[$excelIndex]);
                                    $data[$dbColumn] = $value ?: 0;
                                } 
                                // Handle date fields
                                else if ($dbColumn === 'ngay_nhan' && !empty($row[$excelIndex])) {
                                    // Try to parse the date
                                    try {
                                        if (is_numeric($row[$excelIndex])) {
                                            // Excel date format
                                            $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$excelIndex]);
                                            $data[$dbColumn] = $dateValue->format('Y-m-d');
                                        } else {
                                            $data[$dbColumn] = date('Y-m-d', strtotime($row[$excelIndex]));
                                        }
                                    } catch (\Exception $e) {
                                        $data[$dbColumn] = null;
                                    }
                                } else {
                                    // For string values, properly escape and handle quotes
                                    $value = $row[$excelIndex];
                                    if (is_string($value)) {
                                        // Ensure string values are properly encoded
                                        $value = mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value));
                                    }
                                    $data[$dbColumn] = $value;
                                }
                            }
                        }
                        
                        // Validate row data
                        if ($rowIndex + 2 === 56) {
                            // Special logging for row 56 where the error occurs
                            Log::debug("Row 56 data:", $data);
                        }
                        
                        // Add to batch data
                        $batchData[] = $data;
                        
                        $processedCount++;
                        
                    } catch (\Exception $e) {
                        // Log error but continue processing
                        $errors[] = 'Row ' . ($rowIndex + 2) . ': ' . $e->getMessage();
                        Log::error('Error processing row ' . ($rowIndex + 2) . ': ' . $e->getMessage());
                    }
                }
                
                // Insert batch if not empty - use chunking for better error handling
                if (!empty($batchData)) {
                    foreach (array_chunk($batchData, 10) as $chunk) {
                        try {
                            ChitietgiaonhanHomgiong::insert($chunk);
                        } catch (\Exception $e) {
                            $errors[] = 'Insert error: ' . $e->getMessage();
                            Log::error('Insert error (batch ' . $batchIndex . '): ' . $e->getMessage());
                            throw $e;
                        }
                    }
                }
                
                // Update progress after each batch
                $importData = \Cache::get('import_chitiet_hg_' . $importId);
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                \Cache::put('import_chitiet_hg_' . $importId, $importData, 3600);
                
                // Give the database a moment to breathe
                usleep(10000); // 10ms pause between batches
            }
            
            // Check if there is still an active transaction before committing
            if (DB::transactionLevel() > 0) {
                // Commit transaction
                DB::commit();
            }
            
        } catch (\Exception $e) {
            // Check if there is still an active transaction before rolling back
            if (DB::transactionLevel() > 0) {
                // Rollback the transaction
                DB::rollBack();
            }
            // Add the error to our errors array
            $errors[] = 'Transaction error: ' . $e->getMessage();
            Log::error('Transaction error: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by the outer catch block
        }
        
        // Update final status
        $importData = \Cache::get('import_chitiet_hg_' . $importId);
        $importData['status'] = 'completed';
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['success'] = true;
        $importData['finished'] = true;
        \Cache::put('import_chitiet_hg_' . $importId, $importData, 3600);
        
        // Delete the temporary file
        Storage::delete($path);
        
    } catch (\Exception $e) {
        // Update error status
        $importData = \Cache::get('import_chitiet_hg_' . $importId);
        $importData['status'] = 'failed';
        $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
        $importData['success'] = false;
        $importData['finished'] = true;
        \Cache::put('import_chitiet_hg_' . $importId, $importData, 3600);
        
        Log::error('Chi tiết HG Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

    /**
     * Map columns from Excel/CSV headers to database columns
     */
    private function getColumnMapping($headers)
    {
        // Define mapping from common header names to database columns
        $possibleHeaderMappings = [
            'tram' => ['Trạm (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'tram', 'station'],
            'ma_so_phieu' => ['Mã số phiếu (Phiếu GN hom giống) (PGN hom giống)', 'ma_so_phieu', 'receipt id'],
            'tieu_de' => ['Tiêu đề', 'tieu_de', 'title'],
            'doi_tac_giao_hom_kh' => ['Đối tác giao hom (KH) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_giao_hom_kh'],
            'doi_tac_giao_hom_khdn' => ['Đối tác giao hom (KHDN) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_giao_hom_khdn'],
            'khach_hang_ca_nhan' => ['Khách hàng cá nhân (Phiếu GN hom giống) (PGN hom giống)', 'khach_hang_ca_nhan'],
            'khach_hang_doanh_nghiep' => ['Khách hàng doanh nghiệp (Phiếu GN hom giống) (PGN hom giống)', 'khach_hang_doanh_nghiep'],
            'phieu_gn_hom_giong' => ['Phiếu GN hom giống', 'phieu_gn_hom_giong'],
            'chitiet_hd_dt_mia' => ['Chi tiết HĐ ĐT mía', 'chitiet_hd_dt_mia'],
            'hang_muc_cong_viec' => ['Hạng mục công việc', 'hang_muc_cong_viec'],
            'giong_mia' => ['Giống mía', 'giong_mia'],
            'tinh_trang_duyet' => ['Tình trạng duyệt (Phiếu GN hom giống) (PGN hom giống)', 'tinh_trang_duyet'],
            'so_luong_dk' => ['Số lượng ĐK', 'so_luong_dk'],
            'ngay_nhan' => ['Ngày nhận', 'ngay_nhan'],
            'thuc_nhan' => ['Thực nhận', 'thuc_nhan'],
            'don_gia' => ['Đơn giá', 'don_gia'],
            'thanh_tien_hom_giong' => ['Thành tiền hom giống', 'thanh_tien_hom_giong'],
            'hop_dong_thu_hoach' => ['Hợp đồng thu hoạch (Phiếu GN hom giống) (PGN hom giống)', 'hop_dong_thu_hoach'],
            'don_gia_don_chat' => ['Đơn giá đốn chặt (đồng/tấn)', 'don_gia_don_chat'],
            'thanh_tien_don_chat' => ['Thành tiền đốn chặt', 'thanh_tien_don_chat'],
            'hop_dong_boc_xep' => ['Hợp đồng bốc xếp (Phiếu GN hom giống) (PGN hom giống)', 'hop_dong_boc_xep'],
            'don_gia_boc_xep' => ['Đơn giá bốc xếp', 'don_gia_boc_xep'],
            'thanh_tien_boc_xep' => ['Thành tiền bốc xếp', 'thanh_tien_boc_xep'],
            'hop_dong_van_chuyen' => ['Hợp đồng vận chuyển (Phiếu GN hom giống) (PGN hom giống)', 'hop_dong_van_chuyen'],
            'don_gia_van_chuyen' => ['Đơn giá vận chuyển', 'don_gia_van_chuyen'],
            'thanh_tien_van_chuyen' => ['Thành tiền vận chuyển', 'thanh_tien_van_chuyen'],
            'don_vi_tinh' => ['Đơn vị tính', 'don_vi_tinh'],
            'so_tien_dau_tu_hl' => ['Số tiền đầu tư HL', 'so_tien_dau_tu_hl'],
            'so_tien_dau_tu_khl' => ['Số tiền đầu tư KHL', 'so_tien_dau_tu_khl'],
            'doi_tac_thu_hoach_kh' => ['Đối tác thu hoạch (KH) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_thu_hoach_kh'],
            'doi_tac_thu_hoach_khdn' => ['Đối tác thu hoạch (KHDN) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_thu_hoach_khdn'],
            'doi_tac_van_chuyen_kh' => ['Đối tác vận chuyển (KH) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_van_chuyen_kh'],
            'doi_tac_van_chuyen_khdn' => ['Đối tác vận chuyển (KHDN) (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_van_chuyen_khdn'],
            'doi_tac_boc_xep' => ['Đối tác bốc xếp (Phiếu GN hom giống) (PGN hom giống)', 'doi_tac_boc_xep'],
            'can_bo_nong_vu' => ['Cán bộ nông vụ (Phiếu GN hom giống) (PGN hom giống)', 'can_bo_nong_vu'],
            'chitiet_hd_dt_mia_doitac' => ['Chi tiết HĐĐT mía (Đối tác) (Phiếu GN hom giống) (PGN hom giống)', 'chitiet_hd_dt_mia_doitac'],
            'vu_dau_tu' => ['Vụ đầu tư (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'vu_dau_tu'],
            'thua_dat' => ['Thửa đất (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'thua_dat'],
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
        
        // Add support for fallback to column index if header isn't found
        // This can be useful for simple Excel templates
        if (count($headers) >= count($possibleHeaderMappings)) {
            $index = 0;
            foreach ($possibleHeaderMappings as $dbColumn => $possibleHeaders) {
                if ($columnMap[$dbColumn] === false && isset($headers[$index])) {
                    $columnMap[$dbColumn] = $index;
                }
                $index++;
            }
        }
        
        return $columnMap;
    }
}