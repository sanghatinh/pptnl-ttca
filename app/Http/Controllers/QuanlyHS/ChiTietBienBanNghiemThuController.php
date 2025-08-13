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

    $importId = uniqid('import_chitiet_');
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

        $fileName = $importId . '.' . $extension;
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        // Copy file ไป temp path
        if (!copy($file->getRealPath(), $tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy file to temporary directory'
            ], 500);
        }

        \Cache::put('import_chitiet_' . $importId, [
            'status' => 'uploading',
            'total' => 0,
            'processed' => 0,
            'errors' => [],
            'success' => false,
            'finished' => false
        ], 3600);

        Log::info('Start importData', ['env' => app()->environment(), 'path' => $tempPath, 'importId' => $importId]);

        // ประมวลผลทันที (ไม่ dispatch)
        try {
            $this->processImportFile($tempPath, $importId);
        } catch (\Exception $e) {
            Log::error('processImportFile error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $importData = \Cache::get('import_chitiet_' . $importId);
            $importData['status'] = 'failed';
            $importData['errors'][] = $e->getMessage();
            $importData['success'] = false;
            $importData['finished'] = true;
            \Cache::put('import_chitiet_' . $importId, $importData, 3600);
        } finally {
            // ลบไฟล์ temp หลังประมวลผล
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

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
    private function processImportFile($filePath, $importId)
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 300);
    try {
        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['status'] = 'processing';
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);

        if (!file_exists($filePath)) {
            throw new \Exception("File \"$filePath\" does not exist.");
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

        if (empty($rows) || count($rows) < 2) {
            throw new \Exception('File is empty or does not contain data rows. Please check the file and try again.');
        }

        $headers = array_map('trim', $rows[0]);
        $columnMap = $this->getColumnMapping($headers);

        if (empty($columnMap)) {
            throw new \Exception('Could not map columns from file to database. Required columns are missing.');
        }

        array_shift($rows);

        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['total'] = count($rows);
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);

        $processedCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();
            \App\Models\QuanlyHS\ChitietNghiemthudv::query()->delete();
            $batchSize = 50;
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
                                if (in_array($dbColumn, ['khoi_luong_thuc_hien', 'don_gia', 'thanh_tien', 'tien_thanh_toan', 'tien_con_lai', 'so_lan_thuc_hien'])) {
                                    $value = preg_replace('/[^0-9.]/', '', $row[$excelIndex]);
                                    $data[$dbColumn] = $value ?: 0;
                                } else {
                                    $data[$dbColumn] = $row[$excelIndex];
                                }
                            }
                        }
                        $batchData[] = $data;
                        $processedCount++;
                    } catch (\Exception $e) {
                        $errors[] = 'Row ' . ($rowIndex + 2) . ': ' . $e->getMessage();
                    }
                }
                if (!empty($batchData)) {
                    \App\Models\QuanlyHS\ChitietNghiemthudv::insert($batchData);
                }
                $importData = \Cache::get('import_chitiet_' . $importId);
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                \Cache::put('import_chitiet_' . $importId, $importData, 3600);
                usleep(10000);
            }
            if (DB::transactionLevel() > 0) {
                DB::commit();
            }
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $errors[] = 'Transaction error: ' . $e->getMessage();
            throw $e;
        }

        $importData = \Cache::get('import_chitiet_' . $importId);
        $importData['status'] = 'completed';
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['success'] = true;
        $importData['finished'] = true;
        \Cache::put('import_chitiet_' . $importId, $importData, 3600);

    } catch (\Exception $e) {
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