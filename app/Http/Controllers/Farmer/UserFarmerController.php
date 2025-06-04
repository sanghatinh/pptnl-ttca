<?php
// filepath: f:\Webpoject\TTCA_PTNL\ttca_ptnl\app\Http\Controllers\Farmer\UserFarmerController.php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Farmer\UserFarmer;
use App\Models\Quanlytaichinh\Banking;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserFarmerController extends Controller
{
    /**
     * Get all farmer users with related data
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('user_farmer as uf')
                ->leftJoin('roles as r', 'uf.role_id', '=', 'r.id')
                ->leftJoin('banking as b', 'uf.ngan_hang', '=', 'b.code_banking')
                ->select(
                    'uf.id',
                   
                    'uf.tram',
                    'uf.supplier_number',
                    'uf.ma_kh_ca_nhan',
                    'uf.khach_hang_ca_nhan',
                    'uf.ma_kh_doanh_nghiep',
                    'uf.khach_hang_doanh_nghiep',
                    'uf.phone',
                   
                    'uf.dia_chi_thuong_tru',
                    'uf.chu_tai_khoan',
                    'uf.ngan_hang as bank_code',
                    'b.name_banking as bank_name',
                    'uf.so_tai_khoan',
                    'uf.role_id',
                    'r.name as role_name',
                    'uf.status',
                    'uf.created_at',
                    'uf.updated_at'
                );

            // Apply filters if provided
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                   $q->where('uf.khach_hang_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('uf.khach_hang_doanh_nghiep', 'like', "%{$search}%")
                      ->orWhere('uf.supplier_number', 'like', "%{$search}%")
                      ->orWhere('uf.ma_kh_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('uf.ma_kh_doanh_nghiep', 'like', "%{$search}%")
                      ->orWhere('uf.phone', 'like', "%{$search}%");
                    
                    
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('uf.status', $request->status);
            }

            // Filter by role
            if ($request->has('role_id') && !empty($request->role_id)) {
                $query->where('uf.role_id', $request->role_id);
            }

            // Filter by station
            if ($request->has('tram') && !empty($request->tram)) {
                $query->where('uf.tram', $request->tram);
            }

            $farmers = $query->orderBy('uf.id', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $farmers
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching farmer users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching farmer users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unique values for filters
     */
    public function getFilterOptions()
    {
        try {
            // Get unique stations
            $stations = DB::table('user_farmer')
                ->select('tram')
                ->whereNotNull('tram')
                ->where('tram', '!=', '')
                ->distinct()
                ->orderBy('tram')
                ->pluck('tram');

            // Get roles
            $roles = DB::table('roles')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // Get banks
            $banks = DB::table('banking')
                ->select('code_banking', 'name_banking')
                ->orderBy('name_banking')
                ->get();

            // Get status options
            $statuses = ['active', 'inactive'];

            return response()->json([
                'success' => true,
                'data' => [
                    'stations' => $stations,
                    'roles' => $roles,
                    'banks' => $banks,
                    'statuses' => $statuses
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching filter options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching filter options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific farmer user
     */
    public function show($id)
    {
        try {
            $farmer = DB::table('user_farmer as uf')
                ->leftJoin('roles as r', 'uf.role_id', '=', 'r.id')
                ->leftJoin('banking as b', 'uf.ngan_hang', '=', 'b.code_banking')
                ->select(
                    'uf.*',
                    'r.name as role_name',
                    'b.name_banking as bank_name'
                )
                ->where('uf.id', $id)
                ->first();

            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update farmer user
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'tram' => 'nullable|string|max:255',
                'supplier_number' => 'nullable|string|max:255',
                'ma_kh_ca_nhan' => 'nullable|string|max:255',
                'khach_hang_ca_nhan' => 'nullable|string|max:255',
                'ma_kh_doanh_nghiep' => 'nullable|string|max:255',
                'khach_hang_doanh_nghiep' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'dia_chi_thuong_tru' => 'nullable|string',
                'chu_tai_khoan' => 'nullable|string|max:255',
                'ngan_hang' => 'nullable|string|max:255',
                'so_tai_khoan' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer|exists:roles,id',
                'status' => 'required|in:active,inactive'
            ]);

            $farmer = UserFarmer::find($id);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            $farmer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farmer user updated successfully',
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete farmer user
     */
    public function destroy($id)
    {
        try {
            $farmer = UserFarmer::find($id);
            if (!$farmer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Farmer user not found'
                ], 404);
            }

            $farmer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Farmer user deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new farmer user
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'tram' => 'nullable|string|max:255',
                'supplier_number' => 'nullable|string|max:255|unique:user_farmer,supplier_number',
                'ma_kh_ca_nhan' => 'nullable|string|max:255|unique:user_farmer,ma_kh_ca_nhan',
                'khach_hang_ca_nhan' => 'nullable|string|max:255',
                'ma_kh_doanh_nghiep' => 'nullable|string|max:255|unique:user_farmer,ma_kh_doanh_nghiep',
                'khach_hang_doanh_nghiep' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:user_farmer,email',
                'dia_chi_thuong_tru' => 'nullable|string',
                'chu_tai_khoan' => 'nullable|string|max:255',
                'ngan_hang' => 'nullable|string|max:255',
                'so_tai_khoan' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer|exists:roles,id',
                'status' => 'required|in:active,inactive',
                'password' => 'required|string|min:6'
            ]);

            // Hash password
            $validated['password'] = bcrypt($validated['password']);

            $farmer = UserFarmer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Farmer user created successfully',
                'data' => $farmer
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating farmer user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating farmer user: ' . $e->getMessage()
            ], 500);
        }
    }

 /**
     * Import farmer users data from Excel/CSV
     */
    public function importData(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);

        // Generate a unique import ID
        $importId = uniqid('import_farmer_');
        
        try {
            $file = $request->file('file');
            $path = $file->storeAs('imports', $importId . '.' . $file->extension());
            
            // Store import info in cache for progress tracking
            Cache::put('import_farmer_users_' . $importId, [
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
                'importId' => $importId,
                'totalRecords' => 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Farmer users import error: ' . $e->getMessage());
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
        $importData = Cache::get('import_farmer_users_' . $importId);
        
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
 * Download CSV template for farmer users import with real data
 */
public function downloadTemplate()
{
    try {
        $headers = [
            'id',
            'tram',
            'supplier_number',
            'ma_kh_ca_nhan',
            'khach_hang_ca_nhan',
            'ma_kh_doanh_nghiep',
            'khach_hang_doanh_nghiep',
            'phone',
            'dia_chi_thuong_tru',
            'chu_tai_khoan',
            'ngan_hang',
            'so_tai_khoan',
            'ma_nhan_vien',
            'password',
            'role_id',
            'status'
        ];

        // ดึงข้อมูลจริงทั้งหมดจากตาราง user_farmer
        $farmers = DB::table('user_farmer')
            ->select([
                'id',
                'tram',
                'supplier_number',
                'ma_kh_ca_nhan',
                'khach_hang_ca_nhan',
                'ma_kh_doanh_nghiep',
                'khach_hang_doanh_nghiep',
                'phone',
                'dia_chi_thuong_tru',
                'chu_tai_khoan',
                'ngan_hang',
                'so_tai_khoan',
                'ma_nhan_vien',
                'password',
                'role_id',
                'status'
            ])
            ->orderBy('id')
            ->get();

        // สร้าง CSV content
        $csvContent = implode(',', $headers) . "\n";

        // เพิ่มข้อมูลจริงทั้งหมด
        foreach ($farmers as $farmer) {
            $rowData = [
                $farmer->id ?? '',
                '"' . str_replace('"', '""', $farmer->tram ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->supplier_number ?? '') . '"',
                // เพิ่ม = เพื่อบังคับให้ Excel แสดงเป็น text และไม่ตัด leading zeros
                '="' . str_replace('"', '""', $farmer->ma_kh_ca_nhan ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->khach_hang_ca_nhan ?? '') . '"',
                '="' . str_replace('"', '""', $farmer->ma_kh_doanh_nghiep ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->khach_hang_doanh_nghiep ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->phone ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->dia_chi_thuong_tru ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->chu_tai_khoan ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->ngan_hang ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->so_tai_khoan ?? '') . '"',
                // เพิ่ม = เพื่อบังคับให้ Excel แสดงเป็น text และไม่ตัด leading zeros
                '="' . str_replace('"', '""', $farmer->ma_nhan_vien ?? '') . '"',
                '"' . str_replace('"', '""', $farmer->password ?? '') . '"',
                $farmer->role_id ?? '',
                '"' . str_replace('"', '""', $farmer->status ?? 'active') . '"'
            ];
            
            $csvContent .= implode(',', $rowData) . "\n";
        }

        // กำหนดชื่อไฟล์ด้วยวันที่ปัจจุบัน
        $currentDate = date('Y-m-d_H-i-s');
        $filename = "farmer_users_data_{$currentDate}.csv";

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    } catch (\Exception $e) {
        Log::error('Error creating farmer users template: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error creating template: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Process the uploaded import file
 */
private function processImportFile($path, $importId)
{
    try {
        // Update status to processing
        $importData = Cache::get('import_farmer_users_' . $importId);
        $importData['status'] = 'processing';
        Cache::put('import_farmer_users_' . $importId, $importData, 3600);
        
        // Get file from storage
        $filePath = storage_path('app/' . $path);
        
        // Check if file exists
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
                throw new \Exception("File \"$path\" does not exist at any expected locations.");
            }
        }
        
        // Load the file
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        if ($extension == 'csv') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
            $reader->setDelimiter(',');
            $reader->setEnclosure('"');
        } else {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        }
        
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        if (empty($rows) || count($rows) < 2) {
            throw new \Exception('File is empty or does not contain data rows.');
        }
        
        $headers = array_map('trim', $rows[0]);
        Log::info('Headers found: ' . implode(', ', $headers));
        
        // Map columns
        $columnMap = $this->getColumnMapping($headers);
        
        if (empty($columnMap)) {
            throw new \Exception('Could not map columns from file to database. Required columns are missing.');
        }
        
        // Skip header row
        array_shift($rows);
        
        // Update total records count
        $importData = Cache::get('import_farmer_users_' . $importId);
        $importData['total'] = count($rows);
        Cache::put('import_farmer_users_' . $importId, $importData, 3600);
        
        $processedCount = 0;
        $errors = [];
        
        try {
            DB::beginTransaction();
            
            // Process records in batches
            $batchSize = 50;
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
                                
                                // Handle special formatting - รหัสผ่านไม่ต้อง hash
                                if ($dbColumn === 'password' && !empty($value)) {
                                    // เก็บรหัสผ่านตามค่าที่ import เข้ามา ไม่ต้อง hash
                                    $data[$dbColumn] = $value;
                                } elseif ($dbColumn === 'role_id' && !empty($value)) {
                                    $value = is_numeric($value) ? (int)$value : null;
                                    $data[$dbColumn] = $value;
                                } else {
                                    $data[$dbColumn] = $value;
                                }
                            }
                        }
                        
                        // Set default values
                        if (!isset($data['status']) || empty($data['status'])) {
                            $data['status'] = 'active';
                        }
                        
                        // Validate required fields
                        if (empty($data['supplier_number']) && empty($data['ma_kh_ca_nhan']) && empty($data['ma_kh_doanh_nghiep'])) {
                            $errors[] = "Row " . ($rowIndex + 2) . ": At least one identifier (supplier_number, ma_kh_ca_nhan, ma_kh_doanh_nghiep) is required";
                            continue;
                        }
                        
                        // Check for duplicates
                        $duplicateCheck = $this->checkForDuplicates($data, isset($data['id']) ? $data['id'] : null);
                        if ($duplicateCheck) {
                            $errors[] = "Row " . ($rowIndex + 2) . ": " . $duplicateCheck;
                            continue;
                        }
                        
                        // Update or create record
                        if (isset($data['id']) && !empty($data['id'])) {
                            // Update existing record
                            $existingRecord = UserFarmer::find($data['id']);
                            if ($existingRecord) {
                                $updateData = $data;
                                unset($updateData['id']); // Don't update ID
                                $existingRecord->update($updateData);
                                $processedCount++;
                            } else {
                                // Create new record with specified ID
                                UserFarmer::create($data);
                                $processedCount++;
                            }
                        } else {
                            // Create new record without ID
                            unset($data['id']);
                            UserFarmer::create($data);
                            $processedCount++;
                        }
                        
                    } catch (\Exception $e) {
                        $errors[] = "Error processing row " . ($rowIndex + 2) . ": " . $e->getMessage();
                        Log::error("Row processing error: " . $e->getMessage());
                    }
                }
                
                // Update progress after each batch
                $importData = Cache::get('import_farmer_users_' . $importId);
                $importData['processed'] = $processedCount;
                $importData['errors'] = $errors;
                Cache::put('import_farmer_users_' . $importId, $importData, 3600);
                
                // Give the database a moment to breathe
                usleep(10000); // 10ms pause between batches
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = 'Transaction error: ' . $e->getMessage();
            throw $e;
        }
        
        // Update final status
        $importData = Cache::get('import_farmer_users_' . $importId);
        $importData['status'] = 'completed';
        $importData['processed'] = $processedCount;
        $importData['errors'] = $errors;
        $importData['success'] = $processedCount > 0;
        $importData['finished'] = true;
        Cache::put('import_farmer_users_' . $importId, $importData, 3600);
        
        // Delete the temporary file
        Storage::delete($path);
        
    } catch (\Exception $e) {
        // Update error status
        $importData = Cache::get('import_farmer_users_' . $importId);
        $importData['status'] = 'failed';
        $importData['errors'] = array_merge($importData['errors'] ?? [], [$e->getMessage()]);
        $importData['success'] = false;
        $importData['finished'] = true;
        Cache::put('import_farmer_users_' . $importId, $importData, 3600);
        
        Log::error('Farmer users import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

    /**
     * Check for duplicate records
     */
    private function checkForDuplicates($data, $excludeId = null)
    {
        $query = UserFarmer::query();
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $duplicateFields = [];
        
        // Check supplier_number
        if (!empty($data['supplier_number'])) {
            if ($query->where('supplier_number', $data['supplier_number'])->exists()) {
                $duplicateFields[] = 'supplier_number';
            }
        }
        
        // Check ma_kh_ca_nhan
        if (!empty($data['ma_kh_ca_nhan'])) {
            if (UserFarmer::where('ma_kh_ca_nhan', $data['ma_kh_ca_nhan'])
                    ->when($excludeId, function($q) use ($excludeId) {
                        return $q->where('id', '!=', $excludeId);
                    })
                    ->exists()) {
                $duplicateFields[] = 'ma_kh_ca_nhan';
            }
        }
        
        // Check ma_kh_doanh_nghiep
        if (!empty($data['ma_kh_doanh_nghiep'])) {
            if (UserFarmer::where('ma_kh_doanh_nghiep', $data['ma_kh_doanh_nghiep'])
                    ->when($excludeId, function($q) use ($excludeId) {
                        return $q->where('id', '!=', $excludeId);
                    })
                    ->exists()) {
                $duplicateFields[] = 'ma_kh_doanh_nghiep';
            }
        }
        
        // Check phone
        if (!empty($data['phone'])) {
            if (UserFarmer::where('phone', $data['phone'])
                    ->when($excludeId, function($q) use ($excludeId) {
                        return $q->where('id', '!=', $excludeId);
                    })
                    ->exists()) {
                $duplicateFields[] = 'phone';
            }
        }
        
        if (!empty($duplicateFields)) {
            return 'Duplicate found for: ' . implode(', ', $duplicateFields);
        }
        
        return null;
    }

    /**
     * Map columns from Excel/CSV headers to database columns
     */
    private function getColumnMapping($headers)
    {
        $possibleHeaderMappings = [
            'id' => ['id', 'ID', 'รหัส'],
            'tram' => ['tram', 'trạm', 'station', 'สถานี'],
            'supplier_number' => ['supplier_number', 'supplier number', 'รหัสผู้จัดหา', 'mã nhà cung cấp'],
            'ma_kh_ca_nhan' => ['ma_kh_ca_nhan', 'mã kh cá nhân', 'mã khách hàng cá nhân', 'individual customer code'],
            'khach_hang_ca_nhan' => ['khach_hang_ca_nhan', 'khách hàng cá nhân', 'individual customer name'],
            'ma_kh_doanh_nghiep' => ['ma_kh_doanh_nghiep', 'mã kh doanh nghiệp', 'mã khách hàng doanh nghiệp', 'business customer code'],
            'khach_hang_doanh_nghiep' => ['khach_hang_doanh_nghiep', 'khách hàng doanh nghiệp', 'business customer name'],
            'phone' => ['phone', 'số điện thoại', 'sdt', 'điện thoại', 'telephone'],
            'dia_chi_thuong_tru' => ['dia_chi_thuong_tru', 'địa chỉ thường trú', 'address', 'địa chỉ'],
            'chu_tai_khoan' => ['chu_tai_khoan', 'chủ tài khoản', 'account holder', 'tên tài khoản'],
            'ngan_hang' => ['ngan_hang', 'ngân hàng', 'bank', 'bank code'],
            'so_tai_khoan' => ['so_tai_khoan', 'số tài khoản', 'account number', 'bank account'],
            'ma_nhan_vien' => ['ma_nhan_vien', 'mã nhân viên', 'employee code', 'staff id'],
            'password' => ['password', 'mật khẩu', 'mat khau', 'pass'],
            'role_id' => ['role_id', 'vai trò', 'role', 'quyền'],
            'status' => ['status', 'trạng thái', 'tình trạng', 'active/inactive']
        ];
        
        $columnMap = [];
        
        foreach ($possibleHeaderMappings as $dbColumn => $possibleHeaders) {
            $columnMap[$dbColumn] = false;
            
            foreach ($possibleHeaders as $possibleHeader) {
                // Try exact match first
                $exactMatchIndex = array_search(strtolower($possibleHeader), array_map('strtolower', $headers));
                
                if ($exactMatchIndex !== false) {
                    $columnMap[$dbColumn] = $exactMatchIndex;
                    break;
                }
                
                // Try partial match
                foreach ($headers as $index => $header) {
                    if (strpos(strtolower($header), strtolower($possibleHeader)) !== false) {
                        $columnMap[$dbColumn] = $index;
                        break 2;
                    }
                }
            }
        }
        
        return $columnMap;
    }

/**
     * Sync selected farmer users to userfarmer_role table
     */
    public function syncToUserFarmerRoles(Request $request)
    {
        try {
            $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:user_farmer,id'
            ]);

            $userIds = $request->input('user_ids');
            $syncedCount = 0;
            $duplicateCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($userIds as $userId) {
                try {
                    // Get farmer user data
                    $farmer = UserFarmer::find($userId);
                    
                    if (!$farmer) {
                        $errors[] = "User ID {$userId}: Farmer not found";
                        $errorCount++;
                        continue;
                    }

                    // Check if required fields exist
                    if (empty($farmer->supplier_number)) {
                        $errors[] = "User ID {$userId}: Missing supplier_number";
                        $errorCount++;
                        continue;
                    }

                    if (empty($farmer->role_id)) {
                        $errors[] = "User ID {$userId}: Missing role_id";
                        $errorCount++;
                        continue;
                    }

                    // Check if record already exists in userfarmer_role
                    $existingRole = DB::table('userfarmer_role')
                        ->where('supplier_number', $farmer->supplier_number)
                        ->first();

                    if ($existingRole) {
                        $duplicateCount++;
                        continue;
                    }

                    // Create new record in userfarmer_role
                    DB::table('userfarmer_role')->insert([
                        'supplier_number' => $farmer->supplier_number,
                        'role_id' => $farmer->role_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $syncedCount++;

                } catch (\Exception $e) {
                    $errors[] = "User ID {$userId}: " . $e->getMessage();
                    $errorCount++;
                    Log::error("Error syncing user {$userId}: " . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => [
                    'synced_count' => $syncedCount,
                    'duplicate_count' => $duplicateCount,
                    'error_count' => $errorCount,
                    'total_selected' => count($userIds),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in syncToUserFarmerRoles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error during sync process: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
 * Delete multiple farmer users by IDs
 */
public function deleteMultiple(Request $request)
{
    try {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:user_farmer,id'
        ]);

        $userIds = $request->input('user_ids');
        $deletedCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();

        foreach ($userIds as $userId) {
            try {
                $farmer = UserFarmer::find($userId);
                
                if (!$farmer) {
                    $errors[] = "User ID {$userId}: Farmer not found";
                    $errorCount++;
                    continue;
                }

                // Check if user has any related data that should prevent deletion
                // You can add additional checks here if needed
                
                // Delete the farmer user
                $farmer->delete();
                $deletedCount++;

            } catch (\Exception $e) {
                $errors[] = "User ID {$userId}: " . $e->getMessage();
                $errorCount++;
                Log::error("Error deleting user {$userId}: " . $e->getMessage());
            }
        }

        DB::commit();

        if ($deletedCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$deletedCount} người dùng thành công",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'error_count' => $errorCount,
                    'total_selected' => count($userIds),
                    'errors' => $errors
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa người dùng nào',
                'data' => [
                    'deleted_count' => 0,
                    'error_count' => $errorCount,
                    'total_selected' => count($userIds),
                    'errors' => $errors
                ]
            ], 400);
        }

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in deleteMultiple: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Lỗi trong quá trình xóa: ' . $e->getMessage()
        ], 500);
    }
}



}