<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\ActionPhieutrinhTTHomgiong;
use App\Models\Quanlytaichinh\LogPhieutrinhTTHomgiong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhieutrinhthanhtoanHomgiongControllers extends Controller
{
    /**
     * ดึงรายการเอกสารขอเบิกเงินทั้งหมด
     */
    public function index()
    {
        try {
            // Query payment requests with their latest action data
            $paymentRequests = DB::table('tb_phieu_trinh_thanh_toan_homgiong as pr')
                ->leftJoin(DB::raw('(
                    SELECT DISTINCT
                        ma_trinh_thanh_toan,
                        FIRST_VALUE(action_by) OVER (PARTITION BY ma_trinh_thanh_toan ORDER BY created_at DESC) as latest_action_by
                    FROM action_phieu_trinh_thanh_toan_homgiong
                ) as latest_actions'), 'latest_actions.ma_trinh_thanh_toan', '=', 'pr.ma_trinh_thanh_toan')
                ->leftJoin('users', 'latest_actions.latest_action_by', '=', 'users.id')
                ->select(
                    'pr.*', 
                    'latest_actions.latest_action_by as action_by',
                    'users.full_name as action_by_name'
                )
                ->orderBy('pr.id', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $paymentRequests
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment requests: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ตรวจสอบรายการซ้ำซ้อน
     */
    public function checkDuplicates(Request $request)
    {
        $request->validate([
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string'
        ]);

        try {
            $receiptIds = $request->receipt_ids;
            
            $duplicates = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                ->whereIn('ma_so_phieu', $receiptIds)
                ->select('ma_so_phieu', 'ma_trinh_thanh_toan')
                ->get();
            
            return response()->json([
                'duplicates' => $duplicates
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking payment request homgiong duplicates: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error checking for duplicates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * สร้างเอกสารขอเบิกเงินใหม่
     */
    public function createPaymentRequest(Request $request)
    {
        $request->validate([
            'investment_project' => 'required|string',
            'payment_type' => 'required|string',
            'payment_installment' => 'required|integer|min:1',
            'proposal_number' => 'required|string|max:50',
            'created_date' => 'required|date',
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'string',
        ]);

        DB::beginTransaction();
        try {
            // 1. สร้าง ID ในรูปแบบ PTTTHG-TTCA-{Ma_Vudautu}-{ID}
            $lastId = DB::table('tb_phieu_trinh_thanh_toan_homgiong')->max('id') ?? 0;
            $newId = $lastId + 1;
            $codeId = str_pad($newId, 6, '0', STR_PAD_LEFT);
            $maTrinh = "PTTTHG-TTCA-{$request->investment_project}-{$codeId}";
            
            // 2. สร้างชื่อเรื่อง (Tiêu đề) ในรูปแบบ PTTTHG-MTTT-TTCA-{Ma_Vudautu}-{ID}
            $tieuDe = "PTTTHG-{$request->payment_type}-{$request->investment_project}-{$codeId}";
            
            // 3. กำหนดค่าเริ่มต้นของยอดเงินรวมเป็น 0 ก่อน
            $totalAmount = 0;
            
            // 4. สร้างเอกสารขอเบิกเงิน
            $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')->insertGetId([
                'ma_trinh_thanh_toan' => $maTrinh,
                'tieu_de' => $tieuDe,
                'vu_dau_tu' => $request->investment_project,
                'loai_thanh_toan' => $request->payment_type,
                'trang_thai_thanh_toan' => 'processing',
                'so_to_trinh' => $request->proposal_number,
                'ngay_tao' => $request->created_date,
                'tong_tien_thanh_toan' => $totalAmount,
                'so_dot_thanh_toan' => $request->payment_installment,
             
            ]);
            
            // 5. สร้างประวัติ (logs) สำหรับแต่ละรายการที่เลือก
            foreach ($request->receipt_ids as $receiptId) {
                LogPhieutrinhTTHomgiong::create([
                    'ma_trinh_thanh_toan' => $maTrinh,
                    'ma_so_phieu' => $receiptId,
                    'ma_de_nghi_giai_ngan' => null,
                ]);
            }

            // 6. คำนวณยอดเงินรวมจาก receipt_ids
           $totalAmount = $this->calculateTotalAmount($request->receipt_ids);
$totalThucNhan = $this->calculateTotalThucNhan($request->receipt_ids);
            
            // 7. อัปเดตยอดเงินรวมในเอกสารขอเบิกเงิน
            DB::table('tb_phieu_trinh_thanh_toan_homgiong')
                ->where('ma_trinh_thanh_toan', $maTrinh)
               ->update([
        'tong_tien_thanh_toan' => $totalAmount,
        // 'thuc_nhan' => $totalThucNhan // เพิ่มการอัปเดต thuc_nhan
    ]);

            // บันทึกประวัติการดำเนินการสร้างใบเบิกเงิน
            ActionPhieutrinhTTHomgiong::create([
                'ma_trinh_thanh_toan' => $maTrinh,
                'action' => 'processing',
                'action_by' => Auth::id(),
                'action_date' => now(),
                'comments' => 'Đã tạo hồ sơ trình thanh toán hom giống'
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request created successfully',
                'payment_request_id' => $maTrinh
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment request homgiong: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * คำนวณยอดเงินรวมจาก receipt_ids
     */
    private function calculateTotalAmount(array $receiptIds)
    {
        $totalAmount = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->sum('tong_tien');
            
        return $totalAmount;
    }

    /**
 * คำนวณยอดรับจริงรวมจาก receipt_ids
 */
private function calculateTotalThucNhan(array $receiptIds)
{
    $totalThucNhan = DB::table('bien_ban_nghiem_thu_hom_giong')
        ->whereIn('ma_so_phieu', $receiptIds)
        ->sum('tong_thuc_nhan');
        
    return $totalThucNhan;
}


/**
 * ดึงรายละเอียดของเอกสารขอเบิกเงิน hom giong
 */
public function show($id)
{
    try {
        // Get the main payment request record
        $document = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Get the creator information by finding the 'processing' action
        $creatorInfo = DB::table('action_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->where('action', 'processing')
            ->join('users', 'action_phieu_trinh_thanh_toan_homgiong.action_by', '=', 'users.id')
            ->select('users.full_name')
            ->first();
        
        // Get all receipt IDs associated with this payment request from logs
        $receiptLogs = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->select('ma_so_phieu', 'ma_de_nghi_giai_ngan')
            ->get();
        
        $receiptIds = $receiptLogs->pluck('ma_so_phieu')->toArray();
        
        // Create a mapping of receipt IDs to disbursement codes
        $disbursementCodeMap = [];
        foreach ($receiptLogs as $log) {
            $disbursementCodeMap[$log->ma_so_phieu] = $log->ma_de_nghi_giai_ngan;
        }
        
        // Get detailed information for each receipt from bien_ban_nghiem_thu_hom_giong
        // เพิ่ม columns ใหม่: ma_de_nghi_giai_ngan และ dot_thanh_toan
        $paymentDetails = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $receiptIds)
            ->select(
                'ma_so_phieu as document_code',
                'tram',
                'vu_dau_tu as investment_project',
                'ten_phieu as title',
                'hop_dong_dau_tu_mia_ben_giao_hom',
                'tong_thuc_nhan',
                'tong_tien as amount'
            )
            ->get();
        
        // Map the payment details และเพิ่มข้อมูล disbursement_code และ installment
        $mappedPaymentDetails = $paymentDetails->map(function($item) use ($disbursementCodeMap, $document) {
            $item->disbursement_code = $disbursementCodeMap[$item->document_code] ?? '';
            $item->installment = $document->so_dot_thanh_toan ?? 1; // ใช้ข้อมูลจาก document หลัก
            return $item;
        });
        
        // Get processing history/logs from the Action table
        $processingHistory = DB::table('action_phieu_trinh_thanh_toan_homgiong')
            ->select(
                'action',
                'action_by',
                'action_date',
                'comments',
                'created_at'
            )
            ->where('ma_trinh_thanh_toan', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'document' => [
                'payment_code' => $document->ma_trinh_thanh_toan,
                'title' => $document->tieu_de,
                'investment_project' => $document->vu_dau_tu,
                'payment_type' => $document->loai_thanh_toan,
                'status' => $document->trang_thai_thanh_toan,
                'payment_installment' => $document->so_dot_thanh_toan,
                'proposal_number' => $document->so_to_trinh,
                'created_at' => $document->ngay_tao,
                'payment_date' => $document->ngay_thanh_toan ?? null,
                'total_amount' => $document->tong_tien_thanh_toan,
                'creator_name' => $creatorInfo->full_name ?? 'Unknown',
                'notes' => $document->note ?? '',
                // Add new financial fields if they exist in the table
                'total_hold_amount' => $document->tong_tien_tam_giu ?? 0,
                'total_deduction' => $document->tong_tien_khau_tru ?? 0,
                'total_interest' => $document->tong_tien_lai_suat ?? 0,
                'total_remaining' => $document->tong_tien_thanh_toan_con_lai ?? 0,
            ],
            'paymentDetails' => $mappedPaymentDetails,
            'processingHistory' => $processingHistory
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching payment request homgiong details: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error fetching payment request details: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * อัพเดตรายการ receipt records เพื่อแก้ไขข้อมูล ma_de_nghi_giai_ngan
 */
public function updateRecords(Request $request, $id)
{
    $request->validate([
        'receipt_ids' => 'required|array',
        'receipt_ids.*' => 'string',
        'disbursement_code' => 'required|string|max:255',
    ]);

    DB::beginTransaction();
    try {
        // Check if payment request exists
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Update ONLY the ma_de_nghi_giai_ngan field in the logs table
        $affected = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->whereIn('ma_so_phieu', $request->receipt_ids)
            ->update(['ma_de_nghi_giai_ngan' => $request->disbursement_code]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Records updated successfully',
            'affected_rows' => $affected
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating payment request homgiong records: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error updating records: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * ลบรายการ receipt records จากตาราง logs_phieu_trinh_thanh_toan_homgiong
 */
public function deleteRecords(Request $request, $id)
{
    $request->validate([
        'receipt_ids' => 'required|array',
        'receipt_ids.*' => 'string',
    ]);

    DB::beginTransaction();
    try {
        // Check if payment request exists
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Delete records from the logs table based on ma_so_phieu
        $affected = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->whereIn('ma_so_phieu', $request->receipt_ids)
            ->delete();
        
        // Recalculate total amount after deletion
        $remainingReceipts = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->pluck('ma_so_phieu')
            ->toArray();
        
        $newTotalAmount = $this->calculateTotalAmount($remainingReceipts);
        
        // Update the payment request with new total amount
        DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->update(['tong_tien_thanh_toan' => $newTotalAmount]);
        
        // ลบการบันทึกประวัติการดำเนินการในตาราง Action (เอาออก)
        // ActionPhieutrinhTTHomgiong::create([
        //     'ma_trinh_thanh_toan' => $id,
        //     'action' => 'delete_records',
        //     'action_by' => Auth::id(),
        //     'action_date' => now(),
        //     'comments' => 'Đã xóa ' . $affected . ' bản ghi khỏi hồ sơ trình thanh toán hom giống'
        // ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Records deleted successfully',
            'affected_rows' => $affected,
            'new_total_amount' => $newTotalAmount
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting payment request homgiong records: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error deleting records: ' . $e->getMessage()
        ], 500);
    }
}


public function addReceipts(Request $request, $id)
{
    $request->validate([
        'receipt_ids' => 'required|array',
        'receipt_ids.*' => 'string',
    ]);

    DB::beginTransaction();
    try {
        // Check if payment request exists
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Get receipt details to calculate total amount
        $receipts = DB::table('bien_ban_nghiem_thu_hom_giong')
            ->whereIn('ma_so_phieu', $request->receipt_ids)
            ->select('ma_so_phieu', 'tong_tien')
            ->get();
        
        $receiptMap = [];
        foreach ($receipts as $receipt) {
            $receiptMap[$receipt->ma_so_phieu] = $receipt->tong_tien;
        }
        
        // Add records to logs table
        $logsData = [];
        foreach ($request->receipt_ids as $receiptId) {
            $logsData[] = [
                'ma_trinh_thanh_toan' => $id,
                'ma_so_phieu' => $receiptId,
                'ma_de_nghi_giai_ngan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Insert new logs
        DB::table('logs_phieu_trinh_thanh_toan_homgiong')->insert($logsData);
        
        // Update total amount for payment request
        $totalAmount = $paymentRequest->tong_tien_thanh_toan;
        foreach ($request->receipt_ids as $receiptId) {
            $totalAmount += $receiptMap[$receiptId] ?? 0;
        }
        
        DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->update(['tong_tien_thanh_toan' => $totalAmount]);
        
        // บันทึกประวัติการดำเนินการ
        // ActionPhieutrinhTTHomgiong::create([
        //     'ma_trinh_thanh_toan' => $id,
        //     'action' => 'add_receipts',
        //     'action_by' => Auth::id(),
        //     'action_date' => now(),
        //     'comments' => 'Đã thêm ' . count($request->receipt_ids) . ' biên bản vào hồ sơ trình thanh toán hom giống'
        // ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Receipts added successfully',
            'new_total_amount' => $totalAmount
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error adding receipts to payment request homgiong: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error adding receipts: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Import data from CSV/Excel to update payment details
 */
public function importData(Request $request, $id)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
    ]);

    try {
        // Check if payment request exists
        $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan_homgiong')
            ->where('ma_trinh_thanh_toan', $id)
            ->first();
        
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        $file = $request->file('file');
        $filePath = $file->getPathname();
        $extension = $file->getClientOriginalExtension();
        
        // Process the file based on its extension
        if ($extension == 'csv') {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
            $reader->setSheetIndex(0);
        } else {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        }
        
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Get headers from first row
        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File must contain at least a header row and one data row',
                'errors' => ['Invalid file format']
            ], 400);
        }
        
        $headers = array_map('trim', $rows[0]);
        
        // Find indices of required columns
        $maIndex = array_search('ma_so_phieu', array_map('strtolower', $headers));
        $maGiaiNganIndex = array_search('ma_de_nghi_giai_ngan', array_map('strtolower', $headers));
        
        // Alternative column names that might be used
        if ($maIndex === false) {
            $maIndex = array_search('ma so phieu', array_map('strtolower', $headers));
        }
        
        if ($maGiaiNganIndex === false) {
            $maGiaiNganIndex = array_search('ma de nghi giai ngan', array_map('strtolower', $headers));
        }
        
        if ($maIndex === false || $maGiaiNganIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Required columns not found. File must contain "ma_so_phieu" and "ma_de_nghi_giai_ngan" columns.',
                'errors' => ['Missing required columns']
            ], 400);
        }
        
        // Skip header row
        array_shift($rows);
        
        $updatedCount = 0;
        $errorRows = [];
        $errors = [];
        
        // Process rows
        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows
                
                $maPhieu = trim($row[$maIndex] ?? '');
                $maGiaiNgan = trim($row[$maGiaiNganIndex] ?? '');
                
                if (empty($maPhieu)) {
                    $errorRows[] = $rowIndex + 2; // +2 because we start from 1 and skipped header
                    $errors[] = "Row " . ($rowIndex + 2) . ": Ma so phieu is required";
                    continue;
                }
                
                // Update the record in logs table
                $affected = DB::table('logs_phieu_trinh_thanh_toan_homgiong')
                    ->where('ma_trinh_thanh_toan', $id)
                    ->where('ma_so_phieu', $maPhieu)
                    ->update(['ma_de_nghi_giai_ngan' => $maGiaiNgan]);
                
                if ($affected > 0) {
                    $updatedCount++;
                } else {
                    $errorRows[] = $rowIndex + 2;
                    $errors[] = "Row " . ($rowIndex + 2) . ": Ma so phieu '{$maPhieu}' not found in payment request";
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} records" . 
                           (!empty($errorRows) ? " with " . count($errorRows) . " errors" : ""),
                'updated_count' => $updatedCount,
                'error_rows' => $errorRows,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        
    } catch (\Exception $e) {
        Log::error('Error in import process for homgiong: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage(),
            'errors' => [$e->getMessage()]
        ], 500);
    }
}
  
}