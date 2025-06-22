<?php

namespace App\Http\Controllers\QuanlyHS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QuanlyHS\ChitietNghiemthudv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChiTietBienBanNghiemThuController extends Controller
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
        $importId = uniqid('import_chitiet_');
        
        try {
            $file = $request->file('file');
            $path = $file->storeAs('imports', $importId . '.' . $file->extension());
            
            // Store import info in cache for progress tracking
            \Cache::put('import_chitiet_' . $importId, [
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
            Log::error('Chi tiết NT Import error: ' . $e->getMessage());
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
        $importData = \Cache::get('import_chitiet_' . $importId);
        
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
        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['status'] = 'processing';
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);
        
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
        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['total'] = count($rows);
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);
        
        $processedCount = 0;
        $errors = [];
        
        try {
            // Start transaction
            DB::beginTransaction();
            
            // Delete all existing records first (using delete() instead of truncate())
            ChitietNghiemthudv::query()->delete();
            
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
                                if (in_array($dbColumn, ['khoi_luong_thuc_hien', 'don_gia', 'thanh_tien', 'tien_thanh_toan', 'tien_con_lai', 'so_lan_thuc_hien'])) {
                                    // Remove currency symbols and formatting
                                    $value = preg_replace('/[^0-9.]/', '', $row[$excelIndex]);
                                    $data[$dbColumn] = $value ?: 0;
                                } else {
                                    $data[$dbColumn] = $row[$excelIndex];
                                }
                            }
                        }
                        
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
                    ChitietNghiemthudv::insert($batchData);
                }
                
                // Update progress after each batch
                $importData = \Cache::get('import_chitiet_' . $importId);
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                \Cache::put('import_chitiet_' . $importId, $importData, 3600);
                
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
            throw $e; // Re-throw to be caught by the outer catch block
        }
        
        // Update final status
        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['status'] = 'completed';
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['success'] = true;
        $importData['finished'] = true;
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);
        
        // Delete the temporary file
        Storage::delete($path);
        
    } catch (\Exception $e) {
        // Update error status
        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['status'] = 'failed';
        $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
        $importData['success'] = false;
        $importData['finished'] = true;
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);
        
        Log::error('Chi tiết NT Import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
            'ma_nghiem_thu' => ['Mã nghiệm thu (Nghiệm thu dịch vụ) (NT dịch vụ)', 'ma nghiem thu', 'ma_nghiem_thu', 'receipt id'],
            'chi_tiet_hd_mia' => ['Tiêu đề (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'chi tiet hd mia', 'hd_mia_details'],
            'khach_hang_doanh_nghiep' => ['Khách hàng doanh nghiệp (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'khach hang doanh nghiep', 'business customer'],
            'khach_hang_ca_nhan' => ['Khách hàng cá nhân (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'khach hang ca nhan', 'individual customer'],
            'doi_tac_ca_nhan_dv' => ['Đối tác cá nhân cung cấp dịch vụ (Nghiệm thu dịch vụ) (NT dịch vụ)', 'doi tac ca nhan dv', 'individual partner'],
            'doi_tac_cung_cap_dv' => ['Đối tác cung cấp dịch vụ (KHDN) (Nghiệm thu dịch vụ) (NT dịch vụ)', 'doi tac cung cap dv', 'service provider'],
            'ma_so_thua' => ['Mã số thửa (Thửa đất) (Thửa đất)', 'ma so thua', 'plot id'],
            'hinh_thuc_thuc_hien_dv' => ['Hình thức thực hiện DV (Nghiệm thu dịch vụ) (NT dịch vụ)', 'hinh thuc thuc hien dv', 'service type'],
            'dich_vu' => ['Dịch vụ', 'dich vu', 'service'],
            'nghiem_thu_dich_vu' => ['Nghiệm thu dịch vụ', 'nghiem thu dich vu', 'service acceptance'],
            'don_vi_tinh' => ['Đơn vị tính', 'don vi tinh', 'unit'],
            'khoi_luong_thuc_hien' => ['Khối lượng thực hiện', 'khoi luong thuc hien', 'volume', 'quantity'],
            'don_gia' => ['Đơn giá', 'don gia', 'price', 'unit price'],
            'thanh_tien' => ['Thành tiền', 'thanh tien', 'amount', 'total price'],
            'tien_thanh_toan' => ['Tiền thanh toán', 'tien thanh toan', 'payment amount'],
            'tien_con_lai' => ['Tiền thanh toán còn lại', 'tien con lai', 'remaining amount'],
            'so_lan_thuc_hien' => ['Số lần thực hiện', 'so lan thuc hien', 'frequency'],
            'da_thanh_toan' => ['Đã thanh toán (Nghiệm thu dịch vụ) (NT dịch vụ)', 'da thanh toan', 'paid'],
            'vu_dau_tu' => ['Vụ đầu tư (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'vu dau tu', 'investment season'],
            'tinh_trang_duyet' => ['Tình trạng duyệt (Nghiệm thu dịch vụ) (NT dịch vụ)', 'tinh trang duyet', 'approval status'],
            'can_bo_nong_vu' => ['Cán bộ nông vụ (Chi tiết HĐĐT mía) (Chi tiết HĐĐT mía)', 'can bo nong vu']
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