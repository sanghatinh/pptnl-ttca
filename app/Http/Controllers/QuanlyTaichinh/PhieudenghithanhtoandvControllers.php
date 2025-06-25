<?php

namespace App\Http\Controllers\QuanlyTaichinh;

use App\Http\Controllers\Controller;
use App\Models\Quanlytaichinh\Phieudenghithanhtoandv;
use App\Models\Quanlytaichinh\Phieuthunodichvu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\QuanlyHS\PaymentRequestAction; // เพิ่ม model สำหรับตาราง Action
use App\Models\QuanlyHS\PaymentRequest;
use App\Models\QuanlyHS\PaymentRequestLog;


class PhieudenghithanhtoandvControllers extends Controller
{
    /**
     * Display a listing of the payment requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $paymentRequestId = $request->input('payment_request_id');
            
            if (!$paymentRequestId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request ID is required'
                ], 400);
            }
            
            // First, get all disbursement requests for the given payment request
            $disbursementRequests = DB::table('tb_de_nghi_thanhtoan_dv as dntd')
                ->where('dntd.ma_trinh_thanh_toan', $paymentRequestId)
                ->get();
                
            if ($disbursementRequests->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            // Get parent payment request details to get the proposal number (so_to_trinh)
            $parentPaymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentRequestId)
                ->first();
                
            $proposalNumber = $parentPaymentRequest ? $parentPaymentRequest->so_to_trinh : null;
            
            // Process each disbursement request to collect all required information
            $result = [];
            foreach ($disbursementRequests as $request) {
                // Get associated receipt IDs through Logs_phieu_trinh_thanh_toan
                $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                    ->where('ma_de_nghi_giai_ngan', $request->ma_giai_ngan)
                    ->pluck('ma_nghiem_thu');
                    
                // Calculate totals from the associated receipts
                $totals = DB::table('tb_bien_ban_nghiemthu_dv')
                    ->whereIn('ma_nghiem_thu', $receiptIds)
                    ->select(
                        DB::raw('SUM(tong_tien_dich_vu) as total_amount'),
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
                
                // Get payment date from Action_phieu_trinh_thanh_toan
                $paymentAction = DB::table('Action_phieu_trinh_thanh_toan')
                    ->where('ma_trinh_thanh_toan', $request->ma_trinh_thanh_toan)
                    ->where('action', 'paid')
                    ->orderBy('action_date', 'desc')
                    ->first();
                    
                // Calculate the remaining amount
                $totalAmount = $totals->total_amount ?? 0;
                $totalHoldAmount = $totals->total_hold_amount ?? 0;
                $totalDeduction = $debtAndInterest['total_deduction'];
                $totalInterest = $debtAndInterest['total_interest'];
                $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction - $totalInterest;
                
                // Create the result item with all required fields
                $result[] = [
                    'disbursement_code' => $request->ma_giai_ngan,
                    'investment_project' => $request->vu_dau_tu,
                    'payment_type' => $request->loai_thanh_toan,
                    'tram' => $request->tram ?? null,
                    'individual_customer' => $request->khach_hang_ca_nhan,
                    'individual_customer_code' => $request->ma_khach_hang_ca_nhan,
                    'corporate_customer' => $request->khach_hang_doanh_nghiep,
                    'corporate_customer_code' => $request->ma_khach_hang_doanh_nghiep,
                    'total_amount' => $totalAmount,
                    'total_hold_amount' => $totalHoldAmount,
                    'total_deduction' => $totalDeduction,
                    'total_interest' => $totalInterest,
                    'total_remaining' => $totalRemaining,
                    'payment_date' => $paymentAction ? $paymentAction->action_date : null,
                    'proposal_number' => $proposalNumber,
                    'installment' => $parentPaymentRequest ? $parentPaymentRequest->so_dot_thanh_toan : null,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching payment requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payment request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'ma_giai_ngan' => 'required|string|unique:tb_de_nghi_thanhtoan_dv,ma_giai_ngan',
                'vu_dau_tu' => 'required|string',
                'loai_thanh_toan' => 'required|string',
                'ma_trinh_thanh_toan' => 'required|string',
                // Other fields can be optional
            ]);
            
            // Create new payment request
            $paymentRequest = Phieudenghithanhtoandv::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request created successfully',
                'data' => $paymentRequest
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating payment request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment request
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Get the payment request by ID
            $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Get associated receipt IDs through Logs_phieu_trinh_thanh_toan
            $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_de_nghi_giai_ngan', $paymentRequest->ma_giai_ngan)
                ->pluck('ma_nghiem_thu');
                
            // Calculate totals from the associated receipts
            $totals = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select(
                    DB::raw('SUM(tong_tien) as total_amount'),
                    DB::raw('SUM(tong_tien_tam_giu) as total_hold_amount')
                )
                ->first();
            
            // Get payment date from Action_phieu_trinh_thanh_toan
            $paymentAction = DB::table('Action_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentRequest->ma_trinh_thanh_toan)
                ->where('action', 'paid')
                ->orderBy('action_date', 'desc')
                ->first();
            
            // Get payment request details from tb_phieu_trinh_thanh_toan
            $parentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentRequest->ma_trinh_thanh_toan)
                ->first();
            
            // Calculate the remaining amount
            $totalAmount = $totals->total_amount ?? 0;
            $totalHoldAmount = $totals->total_hold_amount ?? 0;
            $totalDeduction = 0; // For future implementation
            $totalInterest = 0; // For future implementation
            $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction - $totalInterest;
            
            // Prepare the response data
            $result = [
                'disbursement_code' => $paymentRequest->ma_giai_ngan,
                'investment_project' => $paymentRequest->vu_dau_tu,
                'payment_type' => $paymentRequest->loai_thanh_toan,
                'individual_customer' => $paymentRequest->khach_hang_ca_nhan,
                'individual_customer_code' => $paymentRequest->ma_khach_hang_ca_nhan,
                'corporate_customer' => $paymentRequest->khach_hang_doanh_nghiep,
                'corporate_customer_code' => $paymentRequest->ma_khach_hang_doanh_nghiep,
                'total_amount' => $totalAmount,
                'total_hold_amount' => $totalHoldAmount,
                'total_deduction' => $totalDeduction,
                'total_interest' => $totalInterest,
                'total_remaining' => $totalRemaining,
                'payment_date' => $paymentAction ? $paymentAction->action_date : null,
                'proposal_number' => $parentRequest ? $parentRequest->so_to_trinh : null,
                'installment' => $parentRequest ? $parentRequest->so_dot_thanh_toan : null,
                'receipts' => $receiptIds
            ];
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching payment request details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment request details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the detailed information of a payment request
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function showDetailPayment($id)
    {
        try {
            // Get the payment request details by ma_giai_ngan
            $paymentRequest = DB::table('tb_de_nghi_thanhtoan_dv')
                ->where('ma_giai_ngan', $id)
                ->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
    
            // Get the parent payment request information from tb_phieu_trinh_thanh_toan
            $parentPaymentRequest = null;
            if (!empty($paymentRequest->ma_trinh_thanh_toan)) {
                $parentPaymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                    ->where('ma_trinh_thanh_toan', $paymentRequest->ma_trinh_thanh_toan)
                    ->first();
            }
    
            // Prepare response data with null checks for all properties
            $responseData = [
                'ma_giai_ngan' => $paymentRequest->ma_giai_ngan ?? null,
                'vu_dau_tu' => $paymentRequest->vu_dau_tu ?? null,
                'loai_thanh_toan' => $paymentRequest->loai_thanh_toan ?? null,
                'trang_thai_thanh_toan' => $paymentRequest->trang_thai_thanh_toan ?? ($parentPaymentRequest ? $parentPaymentRequest->trang_thai_thanh_toan : null),
                'khach_hang_ca_nhan' => $paymentRequest->khach_hang_ca_nhan ?? null,
                'ma_khach_hang_ca_nhan' => $paymentRequest->ma_khach_hang_ca_nhan ?? null,
                'khach_hang_doanh_nghiep' => $paymentRequest->khach_hang_doanh_nghiep ?? null,
                'ma_khach_hang_doanh_nghiep' => $paymentRequest->ma_khach_hang_doanh_nghiep ?? null,
                'tong_tien' => $paymentRequest->tong_tien ?? 0,
                'tong_tien_tam_giu' => $paymentRequest->tong_tien_tam_giu ?? 0,
                'tong_tien_khau_tru' => $paymentRequest->tong_tien_khau_tru ?? 0,
                'tong_tien_lai_suat' => $paymentRequest->tong_tien_lai_suat ?? 0,
                'tong_tien_thanh_toan_con_lai' => $paymentRequest->tong_tien_thanh_toan_con_lai ?? 0,
                'ngay_thanh_toan' => $paymentRequest->ngay_thanh_toan ?? null,
                // Get these values from the parent payment request
                'attachment_url' => $parentPaymentRequest ? $parentPaymentRequest->link_url : null,
                'so_to_trinh' => $parentPaymentRequest ? $parentPaymentRequest->so_to_trinh : null,
                'so_dot_thanh_toan' => $parentPaymentRequest ? $parentPaymentRequest->so_dot_thanh_toan : null,
                'comment' => $paymentRequest->comment ?? null, 
            ];
    
            return response()->json([
                'success' => true,
                'document' => $responseData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching payment request details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment request details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified payment request
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the payment request
            $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Update payment request fields
            $paymentRequest->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request updated successfully',
                'data' => $paymentRequest
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating payment request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment request
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Find the payment request
            $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $id)->first();
            
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Delete the payment request
            $paymentRequest->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting payment request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment requests by parent payment request ID
     *
     * @param string $paymentRequestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByPaymentRequest($paymentRequestId)
    {
        try {
            // This method delegates to index with the necessary parameter
            // It's just a convenience wrapper for a cleaner API
            $request = new Request();
            $request->merge(['payment_request_id' => $paymentRequestId]);
            
            return $this->index($request);
            
        } catch (\Exception $e) {
            Log::error('Error fetching payment requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export payment requests to Excel
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            $paymentRequestId = $request->input('payment_request_id');
            
            if (!$paymentRequestId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request ID is required'
                ], 400);
            }
            
            // Get all payment requests for export
            $request = new Request();
            $request->merge(['payment_request_id' => $paymentRequestId]);
            $response = $this->index($request);
            
            // Convert the response to array
            $data = json_decode($response->getContent(), true);
            
            if (!$data['success'] || empty($data['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data to export'
                ], 404);
            }
            
            // Process data for export (you'll implement the actual Excel export)
            // ...
            
            return response()->json([
                'success' => true,
                'message' => 'Export functionality to be implemented',
                'data' => $data['data']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting payment requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting payment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import payment requests from Excel/CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
                'payment_code' => 'required|string'
            ]);

            $file = $request->file('file');
            $paymentCode = $request->input('payment_code');
            
            // Check if payment request exists
            $paymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentCode)
                ->first();
                
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found',
                    'errors' => ['Invalid payment request code']
                ], 404);
            }

            // Process the file
            $path = $file->getRealPath();
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            $headers = array_shift($rows);
            
            // Map headers to database columns
            $columnMap = [
                'ma_giai_ngan' => array_search('ma_giai_ngan', array_map('strtolower', $headers)),
                'vu_dau_tu' => array_search('vu_dau_tu', array_map('strtolower', $headers)),
                'loai_thanh_toan' => array_search('loai_thanh_toan', array_map('strtolower', $headers)),
                'khach_hang_ca_nhan' => array_search('khach_hang_ca_nhan', array_map('strtolower', $headers)),
                'ma_khach_hang_ca_nhan' => array_search('ma_khach_hang_ca_nhan', array_map('strtolower', $headers)),
                'khach_hang_doanh_nghiep' => array_search('khach_hang_doanh_nghiep', array_map('strtolower', $headers)),
                'ma_khach_hang_doanh_nghiep' => array_search('ma_khach_hang_doanh_nghiep', array_map('strtolower', $headers))
            ];
            
            // Validate required columns
            if ($columnMap['ma_giai_ngan'] === false || 
                $columnMap['vu_dau_tu'] === false || 
                $columnMap['loai_thanh_toan'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required columns missing in the import file',
                    'errors' => ['File must contain ma_giai_ngan, vu_dau_tu, and loai_thanh_toan columns']
                ], 400);
            }
            
            // Process rows
            $importedCount = 0;
            $errors = [];
            $importedIds = [];
            
            DB::beginTransaction();
            try {
                foreach ($rows as $index => $row) {
                    $rowNum = $index + 2; // +2 because of 0-indexing and header row
                    
                    // Skip empty rows
                    if (empty($row[$columnMap['ma_giai_ngan']])) {
                        continue;
                    }
                    
                    $ma_giai_ngan = trim($row[$columnMap['ma_giai_ngan']]);
                    $vu_dau_tu = trim($row[$columnMap['vu_dau_tu']]);
                    $loai_thanh_toan = trim($row[$columnMap['loai_thanh_toan']]);
                    
                    // Check for required fields
                    if (empty($ma_giai_ngan) || empty($vu_dau_tu) || empty($loai_thanh_toan)) {
                        $errors[] = "Row {$rowNum}: Required fields cannot be empty";
                        continue;
                    }
                    
                    // Prepare data for insert/update
                    $data = [
                        'ma_giai_ngan' => $ma_giai_ngan,
                        'vu_dau_tu' => $vu_dau_tu,
                        'loai_thanh_toan' => $loai_thanh_toan,
                        'ma_trinh_thanh_toan' => $paymentCode,
                        'khach_hang_ca_nhan' => $columnMap['khach_hang_ca_nhan'] !== false && !empty($row[$columnMap['khach_hang_ca_nhan']]) 
                            ? trim($row[$columnMap['khach_hang_ca_nhan']]) : null,
                        'ma_khach_hang_ca_nhan' => $columnMap['ma_khach_hang_ca_nhan'] !== false && !empty($row[$columnMap['ma_khach_hang_ca_nhan']]) 
                            ? trim($row[$columnMap['ma_khach_hang_ca_nhan']]) : null,
                        'khach_hang_doanh_nghiep' => $columnMap['khach_hang_doanh_nghiep'] !== false && !empty($row[$columnMap['khach_hang_doanh_nghiep']]) 
                            ? trim($row[$columnMap['khach_hang_doanh_nghiep']]) : null,
                        'ma_khach_hang_doanh_nghiep' => $columnMap['ma_khach_hang_doanh_nghiep'] !== false && !empty($row[$columnMap['ma_khach_hang_doanh_nghiep']]) 
                            ? trim($row[$columnMap['ma_khach_hang_doanh_nghiep']]) : null
                    ];
                    
                    // Check if record already exists
                    $existingRecord = Phieudenghithanhtoandv::where('ma_giai_ngan', $ma_giai_ngan)->first();
                    
                    if ($existingRecord) {
                        // Update existing record
                        $existingRecord->update($data);
                    } else {
                        // Create new record
                        Phieudenghithanhtoandv::create($data);
                    }
                    
                    $importedIds[] = $ma_giai_ngan;
                    $importedCount++;
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error importing payment requests: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error importing payment requests',
                    'errors' => [$e->getMessage()]
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$importedCount} payment requests imported successfully",
                'imported_count' => $importedCount,
                'errors' => $errors,
                'imported_ids' => $importedIds
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in import process: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing import file',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Bulk delete payment requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'disbursement_codes' => 'required|array',
                'disbursement_codes.*' => 'string'
            ]);
            
            $disbursementCodes = $request->input('disbursement_codes');
            
            DB::beginTransaction();
            
            // Delete the payment requests and their associated logs
            $deletedCount = 0;
            foreach ($disbursementCodes as $code) {
                // Find the payment request
                $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $code)->first();
                
                if ($paymentRequest) {
                    // Delete associated logs in Logs_phieu_trinh_thanh_toan
                    DB::table('Logs_phieu_trinh_thanh_toan')
                        ->where('ma_de_nghi_giai_ngan', $code)
                        ->update(['ma_de_nghi_giai_ngan' => null]);
                    
                    // Delete the payment request itself
                    $paymentRequest->delete();
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} payment requests deleted successfully",
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk deleting payment requests: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update payment requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'disbursement_codes' => 'required|array',
                'disbursement_codes.*' => 'string',
                'data' => 'required|array',
                'original_code' => 'nullable|string',
                'financial_data' => 'nullable|array', // Add validation for financial data
            ]);
            
            $disbursementCodes = $request->input('disbursement_codes');
            $updateData = $request->input('data');
            $originalCode = $request->input('original_code');
            $financialData = $request->input('financial_data', []); // Get financial data
            
            // Remove empty values from update data
            $updateData = array_filter($updateData, function($value) {
                return $value !== null && $value !== '';
            });
            
            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided for update'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Update the payment requests
            $updatedCount = 0;
            
            // ตรวจสอบว่าเป็นการเลือกแค่รายการเดียวและมีการเปลี่ยน ma_giai_ngan หรือไม่
            if (count($disbursementCodes) === 1 && $originalCode && 
                isset($updateData['ma_giai_ngan']) && 
                $updateData['ma_giai_ngan'] !== $originalCode) {
                
                // ต้องการเปลี่ยนรหัสใหม่
                $newCode = $updateData['ma_giai_ngan'];
                
                // ตรวจสอบว่ารหัสใหม่ซ้ำกับอันที่มีอยู่หรือไม่
                $existingWithNewCode = Phieudenghithanhtoandv::where('ma_giai_ngan', $newCode)->first();
                if ($existingWithNewCode) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "รหัสเบิกจ่าย {$newCode} มีอยู่ในระบบแล้ว ไม่สามารถใช้รหัสซ้ำได้",
                    ], 400);
                }
                
                // Get the payment request with old code
                $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $originalCode)->first();
                
                if ($paymentRequest) {
                    // Update ma_giai_ngan in logs table first
                    DB::table('Logs_phieu_trinh_thanh_toan')
                        ->where('ma_de_nghi_giai_ngan', $originalCode)
                        ->update(['ma_de_nghi_giai_ngan' => $newCode]);
                    
                    // สร้างเรคอร์ดใหม่แบบไม่คัดลอก id เดิม
                    $newPaymentRequestData = $paymentRequest->toArray();
                    unset($newPaymentRequestData['id']); // ลบ id ออกเพื่อให้ระบบสร้าง id ใหม่
                    $newPaymentRequestData['ma_giai_ngan'] = $newCode;
                    
                    // Update financial information if provided
                    if (!empty($financialData)) {
                        $newPaymentRequestData['tong_tien'] = $financialData['tong_tien'] ?? $paymentRequest->tong_tien;
                        $newPaymentRequestData['tong_tien_tam_giu'] = $financialData['tong_tien_tam_giu'] ?? $paymentRequest->tong_tien_tam_giu;
                        $newPaymentRequestData['tong_tien_khau_tru'] = $financialData['tong_tien_khau_tru'] ?? $paymentRequest->tong_tien_khau_tru;
                        $newPaymentRequestData['tong_tien_lai_suat'] = $financialData['tong_tien_lai_suat'] ?? $paymentRequest->tong_tien_lai_suat;
                        $newPaymentRequestData['tong_tien_thanh_toan_con_lai'] = $financialData['tong_tien_thanh_toan_con_lai'] ?? $paymentRequest->tong_tien_thanh_toan_con_lai;
                        if (isset($financialData['payment_date'])) {
                            $newPaymentRequestData['ngay_thanh_toan'] = $financialData['payment_date'];
                        }
                    }
                    
                    // Update other fields from the form
                    foreach ($updateData as $key => $value) {
                        if ($key != 'ma_giai_ngan') {
                            $newPaymentRequestData[$key] = $value;
                        }
                    }
                    
                    // สร้างเรคอร์ดใหม่แทนการใช้ replicate()
                    Phieudenghithanhtoandv::create($newPaymentRequestData);
                    
                    // Delete the old record
                    $paymentRequest->delete();
                    
                    $updatedCount = 1;
                }
            } else {
                // Normal bulk update for multiple records (without changing ma_giai_ngan)
                // Remove ma_giai_ngan from update data if it exists
                if (isset($updateData['ma_giai_ngan'])) {
                    unset($updateData['ma_giai_ngan']);
                }
                
                foreach ($disbursementCodes as $code) {
                    // Find the payment request
                    $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $code)->first();
                    
                    if ($paymentRequest) {
                        // Update basic fields
                        $paymentRequest->update($updateData);
                        
                        // Update financial information if provided
                        if (!empty($financialData)) {
                            $paymentRequest->tong_tien = $financialData['tong_tien'] ?? $paymentRequest->tong_tien;
                            $paymentRequest->tong_tien_tam_giu = $financialData['tong_tien_tam_giu'] ?? $paymentRequest->tong_tien_tam_giu;
                            $paymentRequest->tong_tien_khau_tru = $financialData['tong_tien_khau_tru'] ?? $paymentRequest->tong_tien_khau_tru;
                            $paymentRequest->tong_tien_lai_suat = $financialData['tong_tien_lai_suat'] ?? $paymentRequest->tong_tien_lai_suat;
                            $paymentRequest->tong_tien_thanh_toan_con_lai = $financialData['tong_tien_thanh_toan_con_lai'] ?? $paymentRequest->tong_tien_thanh_toan_con_lai;
                            if (isset($financialData['payment_date'])) {
                                $paymentRequest->ngay_thanh_toan = $financialData['payment_date'];
                            }
                            $paymentRequest->save();
                        }
                        
                        $updatedCount++;
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} payment requests updated successfully",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk updating payment requests: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating payment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new payment request with multiple receipts assignment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addWithReceipts(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'payment_request_data' => 'required|array',
                'receipt_ids' => 'required|array',
                'receipt_ids.*' => 'string'
            ]);
            
            $paymentRequestData = $request->input('payment_request_data');
            $receiptIds = $request->input('receipt_ids');
            
            // Required fields validation
            if (empty($paymentRequestData['ma_giai_ngan']) || 
                empty($paymentRequestData['vu_dau_tu']) || 
                empty($paymentRequestData['loai_thanh_toan']) ||
                empty($paymentRequestData['ma_trinh_thanh_toan'])) {
                
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields for payment request'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Create the payment request
            $paymentRequest = Phieudenghithanhtoandv::create($paymentRequestData);
            
            // Assign receipts to this payment request
            foreach ($receiptIds as $receiptId) {
                DB::table('Logs_phieu_trinh_thanh_toan')
                    ->where('ma_trinh_thanh_toan', $paymentRequestData['ma_trinh_thanh_toan'])
                    ->where('ma_nghiem_thu', $receiptId)
                    ->update(['ma_de_nghi_giai_ngan' => $paymentRequestData['ma_giai_ngan']]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment request created and assigned to receipts successfully',
                'data' => $paymentRequest
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating payment request with receipts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update financial information for all payment requests in a payment document
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFinancialData(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'payment_code' => 'required|string',
                'financial_data' => 'required|array',
                'payment_date' => 'nullable|date'
            ]);
            
            $paymentCode = $request->input('payment_code');
            $financialData = $request->input('financial_data');
            
            DB::beginTransaction();
            
            // ตรวจสอบว่ามีเอกสารหรือไม่
            $parentRequest = DB::table('tb_phieu_trinh_thanh_toan')
                ->where('ma_trinh_thanh_toan', $paymentCode)
                ->first();
                
            if (!$parentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // เรียกใช้ฟังก์ชันอัปเดตข้อมูลทางการเงิน
            $this->updateFinancialInfo($paymentCode, $financialData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Financial information updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating financial information: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating financial information',
                'error' => $e->getMessage()
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
                        DB::raw('SUM(tong_tien_dich_vu) as total_amount'),
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
                $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction - $totalInterest;
                
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
     * Get all payment requests with filtering and pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function getAllPaymentRequests(Request $request)
{
    try {
        // ดึงข้อมูล user type และ user ID จาก headers ที่ JwtMiddleware ส่งมา
        $userType = $request->header('X-User-Type');
        $userId = $request->header('X-User-ID');
        
        // Build query with joins to get all required data
        $query = DB::table('tb_de_nghi_thanhtoan_dv as dngn')
            ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'dngn.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->leftJoin('tb_vudautu as vdt', 'vdt.Ma_Vudautu', '=', 'dngn.vu_dau_tu')
            ->select(
                'dngn.id',
                'dngn.ma_giai_ngan',
                'dngn.vu_dau_tu',
                'vdt.Ten_Vudautu as ten_vu_dau_tu',
                'dngn.loai_thanh_toan',
                'dngn.khach_hang_ca_nhan',
                'dngn.ma_khach_hang_ca_nhan',
                'dngn.khach_hang_doanh_nghiep',
                'dngn.ma_khach_hang_doanh_nghiep',
                'dngn.tong_tien',
                'dngn.tong_tien_tam_giu',
                'dngn.tong_tien_khau_tru',
                'dngn.tong_tien_lai_suat',
                'dngn.tong_tien_thanh_toan_con_lai',
                'dngn.trang_thai_thanh_toan',
                'dngn.ngay_thanh_toan',
                'dngn.comment',
                'pttt.so_to_trinh as proposal_number',
                'pttt.so_dot_thanh_toan as payment_installment',
                'pttt.ngay_tao as created_date'
            );

        // ถ้าเป็น farmer ให้กรองข้อมูลตาม customer ID
        if ($userType === 'farmer') {
            // ดึงข้อมูล farmer จาก user_farmer table เพื่อเอา ma_kh_ca_nhan หรือ ma_kh_doanh_nghiep
            $farmerData = DB::table('user_farmer')
                ->where('id', $userId)
                ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                ->first();
            
            if ($farmerData) {
                // สร้างเงื่อนไขสำหรับการกรองข้อมูล
                $query->where(function($q) use ($farmerData) {
                    // ถ้ามี ma_kh_ca_nhan ให้กรองตาม ma_khach_hang_ca_nhan
                    if (!empty($farmerData->ma_kh_ca_nhan)) {
                        $q->where('dngn.ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                    }
                    
                    // ถ้ามี ma_kh_doanh_nghiep ให้กรองตาม ma_khach_hang_doanh_nghiep
                    if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                        $q->orWhere('dngn.ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                    }
                });
            } else {
                // ถ้าไม่พบข้อมูล farmer ให้ return ข้อมูลว่าง
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No farmer data found'
                ]);
            }
        }
        // สำหรับ employee ไม่ต้องกรองข้อมูล (แสดงทั้งหมด)

        // Apply filters based on request parameters
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('dngn.trang_thai_thanh_toan', $request->status);
        }
        
        // Individual column filters
        $filterableColumns = [
            'filter_ma_giai_ngan' => 'dngn.ma_giai_ngan',
            'filter_vu_dau_tu' => 'dngn.vu_dau_tu',
            'filter_loai_thanh_toan' => 'dngn.loai_thanh_toan',
            'filter_khach_hang_ca_nhan' => 'dngn.khach_hang_ca_nhan',
            'filter_khach_hang_doanh_nghiep' => 'dngn.khach_hang_doanh_nghiep',
        ];
        
        foreach ($filterableColumns as $filterParam => $dbColumn) {
            if ($request->has($filterParam) && !empty($request->$filterParam)) {
                $query->where($dbColumn, 'LIKE', '%' . $request->$filterParam . '%');
            }
        }
        
        // Date filter for ngay_thanh_toan
        if ($request->has('filter_ngay_thanh_toan') && !empty($request->filter_ngay_thanh_toan)) {
            $query->whereDate('dngn.ngay_thanh_toan', $request->filter_ngay_thanh_toan);
        }
        
        // Global search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('dngn.ma_giai_ngan', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('dngn.vu_dau_tu', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('dngn.loai_thanh_toan', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('dngn.khach_hang_ca_nhan', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('dngn.khach_hang_doanh_nghiep', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('vdt.Ten_Vudautu', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('pttt.so_to_trinh', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Sort options (default to newest first)
        $sortField = $request->input('sort_field', 'dngn.id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Get all records instead of paginating
        $results = $query->get();
        $totalCount = count($results);

        // Also get all unique values for dropdown filters (ปรับปรุงให้กรองตาม user type ด้วย)
        $uniqueVuDauTu = DB::table('tb_de_nghi_thanhtoan_dv as dngn');
        $uniqueLoaiThanhToan = DB::table('tb_de_nghi_thanhtoan_dv as dngn');
        $uniqueTrangThaiThanhToan = DB::table('tb_de_nghi_thanhtoan_dv as dngn');

        // ถ้าเป็น farmer ให้กรองข้อมูล unique values ด้วย
        if ($userType === 'farmer' && isset($farmerData)) {
            $uniqueVuDauTu->where(function($q) use ($farmerData) {
                if (!empty($farmerData->ma_kh_ca_nhan)) {
                    $q->where('dngn.ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                }
                if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                    $q->orWhere('dngn.ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                }
            });

            $uniqueLoaiThanhToan->where(function($q) use ($farmerData) {
                if (!empty($farmerData->ma_kh_ca_nhan)) {
                    $q->where('dngn.ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                }
                if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                    $q->orWhere('dngn.ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                }
            });

            $uniqueTrangThaiThanhToan->where(function($q) use ($farmerData) {
                if (!empty($farmerData->ma_kh_ca_nhan)) {
                    $q->where('dngn.ma_khach_hang_ca_nhan', $farmerData->ma_kh_ca_nhan);
                }
                if (!empty($farmerData->ma_kh_doanh_nghiep)) {
                    $q->orWhere('dngn.ma_khach_hang_doanh_nghiep', $farmerData->ma_kh_doanh_nghiep);
                }
            });
        }

        $uniqueVuDauTu = $uniqueVuDauTu->distinct()->pluck('vu_dau_tu')->filter()->values();
        $uniqueLoaiThanhToan = $uniqueLoaiThanhToan->distinct()->pluck('loai_thanh_toan')->filter()->values();
        $uniqueTrangThaiThanhToan = $uniqueTrangThaiThanhToan->distinct()->pluck('trang_thai_thanh_toan')->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $results,
            'total_count' => $totalCount,
            'filter_options' => [
                'unique_vu_dau_tu' => $uniqueVuDauTu,
                'unique_loai_thanh_toan' => $uniqueLoaiThanhToan,
                'unique_trang_thai_thanh_toan' => $uniqueTrangThaiThanhToan,
            ],
            'user_info' => [
                'user_type' => $userType,
                'user_id' => $userId,
                'is_farmer' => $userType === 'farmer'
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching payment requests: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving payment requests',
            'error' => $e->getMessage()
        ], 500);
    }
}



    /**
     * Get nghiệm thu dịch vụ data related to a payment request
     * 
     * @param string $id Disbursement code (ma_giai_ngan)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getbienbannghiemthudv($id)
    {
        try {
            // Check if the payment request exists
            $paymentRequest = DB::table('tb_de_nghi_thanhtoan_dv')
                ->where('ma_giai_ngan', $id)
                ->first();
                
            if (!$paymentRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }
            
            // Get associated receipt IDs through Logs_phieu_trinh_thanh_toan
            $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
                ->where('ma_de_nghi_giai_ngan', $id)
                ->pluck('ma_nghiem_thu');
                
            if ($receiptIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            // Get detailed information from tb_bien_ban_nghiemthu_dv table
            $bienbans = DB::table('tb_bien_ban_nghiemthu_dv')
                ->whereIn('ma_nghiem_thu', $receiptIds)
                ->select(
                    'ma_nghiem_thu',
                    'tram',
                    'vu_dau_tu',
                    'khach_hang_ca_nhan_dt_mia',
                    'khach_hang_doanh_nghiep_dt_mia',
                    'hop_dong_dau_tu_mia',
                    'hinh_thuc_thuc_hien_dv',
                    'hop_dong_cung_ung_dich_vu',
                    'tong_tien',
                    'tong_tien_tam_giu',
                    'tong_tien_thanh_toan'
                )
                ->get();
                
            // Calculate the total amounts
            $totals = [
                'total_amount' => $bienbans->sum('tong_tien'),
                'total_hold_amount' => $bienbans->sum('tong_tien_tam_giu'),
                'total_payment_amount' => $bienbans->sum('tong_tien_thanh_toan')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $bienbans,
                'totals' => $totals
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching nghiệm thu dịch vụ data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving nghiệm thu dịch vụ data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Check access permission for payment request details
 *
 * @param string $id
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function checkAccess($id, Request $request)
{
    try {
        // Get user type and authentication info from headers set by JwtMiddleware
        $userType = $request->header('X-User-Type', 'employee');
        $userId = $request->header('X-User-ID');
        
        \Log::info('Check access for payment request details:', [
            'payment_request_id' => $id,
            'userType' => $userType,
            'userId' => $userId
        ]);
        
        // ค้นหาเอกสารที่ต้องการเข้าถึง
        $document = DB::table('tb_de_nghi_thanhtoan_dv')
            ->where('ma_giai_ngan', $id)
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
        \Log::error('Error checking payment request access: ' . $e->getMessage(), [
            'payment_request_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'hasAccess' => false,
            'message' => 'Error checking access',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Get chi tiết nghiệm thu dịch vụ data related to a payment request
 * 
 * @param string $id Disbursement code (ma_giai_ngan)
 * @return \Illuminate\Http\JsonResponse
 */
public function getchitietbienbannghiemthudv($id)
{
    try {
        // Check if the payment request exists
        $paymentRequest = DB::table('tb_de_nghi_thanhtoan_dv')
            ->where('ma_giai_ngan', $id)
            ->first();
            
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Get associated receipt IDs through Logs_phieu_trinh_thanh_toan
        $receiptIds = DB::table('Logs_phieu_trinh_thanh_toan')
            ->where('ma_de_nghi_giai_ngan', $id)
            ->pluck('ma_nghiem_thu');
            
        if ($receiptIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'totals' => [
                    'total_amount' => 0,
                    'total_hold_amount' => 0,
                    'total_payment_amount' => 0
                ]
            ]);
        }
        
        // Get detailed information from tb_chitiet_dichvu_nt table
        $chitietDichVu = DB::table('tb_chitiet_dichvu_nt')
            ->whereIn('ma_nghiem_thu', $receiptIds)
            ->select(
                'ma_nghiem_thu',
                'tram',
                'dich_vu',
                'ma_so_thua',
                'don_vi_tinh',
                'so_lan_thuc_hien',
                'khoi_luong_thuc_hien',
                'don_gia',
                'thanh_tien',
                'tien_con_lai as tien_tam_giu', // Using tien_con_lai for Số tiền tạm giữ
                'tien_thanh_toan'
            )
            ->get();
            
        // Calculate the total amounts
        $totals = [
            'total_amount' => $chitietDichVu->sum('thanh_tien'),
            'total_hold_amount' => $chitietDichVu->sum('tien_tam_giu'),
            'total_payment_amount' => $chitietDichVu->sum('tien_thanh_toan')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $chitietDichVu,
            'totals' => $totals
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching chi tiết nghiệm thu dịch vụ data: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving chi tiết nghiệm thu dịch vụ data',
            'error' => $e->getMessage()
        ], 500);
    }
}
  


/**
 * Get phieu thu no dich vu data related to a payment request
 * 
 * @param string $id Disbursement code (ma_giai_ngan)
 * @return \Illuminate\Http\JsonResponse
 */
public function getphieuthunodv($id)
{
    try {
        // Check if the payment request exists
        $paymentRequest = DB::table('tb_de_nghi_thanhtoan_dv')
            ->where('ma_giai_ngan', $id)
            ->first();
            
        if (!$paymentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }
        
        // Get the parent payment request information from tb_phieu_trinh_thanh_toan to get so_to_trinh
        $parentPaymentRequest = DB::table('tb_phieu_trinh_thanh_toan')
            ->where('ma_trinh_thanh_toan', $paymentRequest->ma_trinh_thanh_toan)
            ->first();
            
        if (!$parentPaymentRequest || empty($parentPaymentRequest->so_to_trinh)) {
            return response()->json([
                'success' => true,
                'data' => [],
                'totals' => [
                    'total_deduction' => 0,
                    'total_interest' => 0
                ]
            ]);
        }
        
        // Determine which customer ID field to use
        $customerRef = null;
        $customerField = null;
        
        if (!empty($paymentRequest->ma_khach_hang_ca_nhan)) {
            $customerRef = $paymentRequest->ma_khach_hang_ca_nhan;
            $customerField = 'Ma_Khach_Hang_Ca_Nhan';
        } elseif (!empty($paymentRequest->ma_khach_hang_doanh_nghiep)) {
            $customerRef = $paymentRequest->ma_khach_hang_doanh_nghiep;
            $customerField = 'Ma_Khach_Hang_Doanh_Nghiep';
        } else {
            return response()->json([
                'success' => true,
                'data' => [],
                'totals' => [
                    'total_deduction' => 0,
                    'total_interest' => 0
                ]
            ]);
        }
        
        // Get debt deduction records from Logs_Phieu_Tinh_Lai_dv based on so_to_trinh and customer ID
        $phieuThuno = DB::table('Logs_Phieu_Tinh_Lai_dv')
            ->where('So_Tro_Trinh', $parentPaymentRequest->so_to_trinh)
            ->where($customerField, $customerRef)
            ->select(
                'Ma_So_Phieu_PDN_Thu_No as ma_so_phieu',
                'Invoice_Number_Phan_Bo_Dau_Tu as invoice_number',
                'Da_Tra_Goc as da_tra_goc',
                'Ngay_Vay as ngay_vay',
                'Ngay_Tra as ngay_tra',
                'Lai_Suat_Phan_Bo_Dau_Tu as lai_suat',
                'Tien_Lai as tien_lai',
                'Vu_Dau_Tu_Phan_Bo_Dau_Tu as vu_dau_tu',
                'Vu_Thanh_Toan_Phan_Bo_Dau_Tu as vu_thanh_toan',
                'Khach_Hang_Ca_Nhan_PDN_Thu_No as khach_hang_ca_nhan',
                'Khach_Hang_Doanh_Nghiep_PDN_Thu_No as khach_hang_doanh_nghiep',
                'So_Tro_Trinh as so_tro_trinh',
                'Category_Debt as category_debt',
                'Description as description'
            )
            ->get();
            
        // Calculate totals
        $totals = [
            'total_deduction' => $phieuThuno->sum('da_tra_goc'),
            'total_interest' => $phieuThuno->sum('tien_lai')
        ];
        
        return response()->json([
            'success' => true,
            'data' => $phieuThuno,
            'totals' => $totals
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching Phieu Thu No Dich Vu data: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving Phieu Thu No Dich Vu data',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
* Update specific fields of the payment request
*
* @param Request $request
* @param string $id
* @return \Illuminate\Http\JsonResponse
*/
public function updateDocument(Request $request, $id)
{
   try {
       // Validate request - remove ma_giai_ngan requirement
       $validated = $request->validate([
           'ngay_thanh_toan' => 'nullable|date',
           'comment' => 'nullable|string',
       ]);
       
       // Begin transaction
       DB::beginTransaction();
       
       // Find the payment request
       $paymentRequest = Phieudenghithanhtoandv::where('ma_giai_ngan', $id)->first();
       
       if (!$paymentRequest) {
           return response()->json([
               'success' => false,
               'message' => 'Payment request not found'
           ], 404);
       }
       
       // Prepare update data - include only provided fields
       $updateData = [];
       
       // Only include comment if it's provided
       if (isset($validated['comment'])) {
           $updateData['comment'] = $validated['comment'];
       }
       
       // Only update ngay_thanh_toan if it's provided
       if (isset($validated['ngay_thanh_toan'])) {
           $updateData['ngay_thanh_toan'] = $validated['ngay_thanh_toan'];
       }
       
       // Simple update
       $paymentRequest->update($updateData);
       
       DB::commit();
       
       return response()->json([
           'success' => true,
           'message' => 'Payment request updated successfully'
       ]);
       
   } catch (\Exception $e) {
       DB::rollBack();
       
       return response()->json([
           'success' => false,
           'message' => 'Error updating payment request: ' . $e->getMessage()
       ], 500);
   }
}


/**
 * Generate Report PDF for Payment Request DV
 */
/**
 * Generate Report PDF for Payment Request DV (Updated)
 */
public function generateReportTableDNTTDV(Request $request)
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
        
        if (!$filterParams || !isset($filterParams['ma_giai_ngan_list'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid filter parameters'
            ], 400);
        }

        $maGiaiNganList = $filterParams['ma_giai_ngan_list'];
        
        if (empty($maGiaiNganList)) {
            return response()->json([
                'success' => false,
                'message' => 'No records to generate report'
            ], 400);
        }

        // Log for debugging large datasets
        if (count($maGiaiNganList) > 100) {
            Log::info('Generating large report for DNTTDV', [
                'record_count' => count($maGiaiNganList),
                'report_type' => $filterParams['report_type'] ?? 'unknown'
            ]);
        }

        // Build the main query
        $query = DB::table('tb_de_nghi_thanhtoan_dv as dntd')
            ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'dntd.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->whereIn('dntd.ma_giai_ngan', $maGiaiNganList)
            ->select([
                'dntd.ma_giai_ngan',
                'dntd.khach_hang_ca_nhan',
                'dntd.khach_hang_doanh_nghiep',
                'dntd.ma_khach_hang_ca_nhan',
                'dntd.ma_khach_hang_doanh_nghiep',
                'dntd.tong_tien',
                'dntd.tong_tien_tam_giu',
                'dntd.tong_tien_khau_tru',
                'dntd.tong_tien_lai_suat',
                'dntd.tong_tien_thanh_toan_con_lai',
                'dntd.vu_dau_tu',
                'dntd.ngay_thanh_toan',
                'pttt.so_dot_thanh_toan',
                'pttt.trang_thai_thanh_toan'
            ])
            ->orderBy('dntd.ma_giai_ngan', 'asc'); // เพิ่ม orderBy

        $reportData = $query->get();

        if ($reportData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the selected records'
            ], 404);
        }

        // Process each record to format data
        $processedData = [];
        
        foreach ($reportData as $record) {
            // Get customer name (Priority: Individual -> Corporate)
            $doiTacNhanTien = $record->khach_hang_ca_nhan ?: $record->khach_hang_doanh_nghiep;
            
            // Get customer code (Priority: Individual -> Corporate)
            $maKhachHang = $record->ma_khach_hang_ca_nhan ?: $record->ma_khach_hang_doanh_nghiep;
            
            // Format payment status for report display
            $trangThaiThanhToan = $this->formatPaymentStatusForReport($record->trang_thai_thanh_toan);

            $processedData[] = [
                'ma_giai_ngan' => $record->ma_giai_ngan ?: 'N/A',
                'doi_tac_nhan_tien' => $doiTacNhanTien ?: 'N/A',
                'ma_khach_hang' => $maKhachHang ?: 'N/A',
                'tong_tien' => $record->tong_tien ?: 0,
                'tong_tien_tam_giu' => $record->tong_tien_tam_giu ?: 0,
                'tong_tien_khau_tru' => $record->tong_tien_khau_tru ?: 0,
                'tong_tien_lai_suat' => $record->tong_tien_lai_suat ?: 0,
                'tong_tien_thanh_toan_con_lai' => $record->tong_tien_thanh_toan_con_lai ?: 0,
                'vu_dau_tu' => $record->vu_dau_tu ?: 'N/A',
                'ngay_thanh_toan' => $record->ngay_thanh_toan ?: null,
                'so_dot_thanh_toan' => $record->so_dot_thanh_toan ?: 'N/A',
                'trang_thai_thanh_toan' => $trangThaiThanhToan
            ];
        }

        // Generate report info with enhanced details
        $reportInfo = [
            'title' => 'Báo cáo Phiếu đề nghị thanh toán dịch vụ',
            'generated_date' => now()->format('d/m/Y H:i:s'),
            'total_records' => count($processedData),
            'report_type' => $filterParams['report_type'] ?? 'selected_items',
            'report_type_display' => $this->getReportTypeDisplay($filterParams['report_type'] ?? 'selected_items'),
            'generated_by' => $filterParams['generated_by'] ?? 'Hệ thống',
            'filter_summary' => $this->buildFilterSummary($filterParams)
        ];

        // Return view for printing (using existing template structure)
        return view('Print.ReportDNTTDV', [
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
        Log::error('Error generating DNTTDV report: ' . $e->getMessage(), [
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
    }
    
    return !empty($summary) ? implode(', ', $summary) : 'Không có bộ lọc';
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