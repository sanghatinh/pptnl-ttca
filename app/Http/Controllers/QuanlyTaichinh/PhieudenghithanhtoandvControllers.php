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
                $totalRemaining = $totalAmount - $totalHoldAmount - $totalDeduction + $totalInterest;
                
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

    public function getAllPaymentRequests(Request $request)
    {
        try {
            // Build query with joins to get all required data
            $query = DB::table('tb_de_nghi_thanhtoan_dv as dngn')
                ->leftJoin('tb_phieu_trinh_thanh_toan as pttt', 'dngn.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
                ->select(
                    'dngn.id',
                    'dngn.ma_giai_ngan',
                    'dngn.vu_dau_tu',
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
                    'pttt.trang_thai_thanh_toan',
                    'pttt.ngay_thanh_toan',
                    'pttt.so_to_trinh',
                    'pttt.so_dot_thanh_toan as dot_thanh_toan'
                );
                
            // Apply filters based on request parameters
            
            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('pttt.trang_thai_thanh_toan', $request->status);
            }
            
            // Individual column filters
            $filterableColumns = [
                'ma_giai_ngan', 'vu_dau_tu', 'loai_thanh_toan',
                'khach_hang_ca_nhan', 'ma_khach_hang_ca_nhan', 
                'khach_hang_doanh_nghiep', 'ma_khach_hang_doanh_nghiep',
                'so_to_trinh'
            ];
            
            foreach ($filterableColumns as $column) {
                $paramName = 'filter_' . $column;
                if ($request->has($paramName) && !empty($request->$paramName)) {
                    $tablePrefix = in_array($column, ['so_to_trinh']) ? 'pttt.' : 'dngn.';
                    $query->where($tablePrefix . $column, 'like', '%' . $request->$paramName . '%');
                }
            }
            
            // Date filter for ngay_thanh_toan
            if ($request->has('filter_ngay_thanh_toan') && !empty($request->filter_ngay_thanh_toan)) {
                $query->whereDate('pttt.ngay_thanh_toan', $request->filter_ngay_thanh_toan);
            }
            
            // Global search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('dngn.ma_giai_ngan', 'like', $searchTerm)
                        ->orWhere('dngn.vu_dau_tu', 'like', $searchTerm)
                        ->orWhere('dngn.loai_thanh_toan', 'like', $searchTerm)
                        ->orWhere('dngn.khach_hang_ca_nhan', 'like', $searchTerm)
                        ->orWhere('dngn.ma_khach_hang_ca_nhan', 'like', $searchTerm)
                        ->orWhere('dngn.khach_hang_doanh_nghiep', 'like', $searchTerm)
                        ->orWhere('dngn.ma_khach_hang_doanh_nghiep', 'like', $searchTerm)
                        ->orWhere('pttt.so_to_trinh', 'like', $searchTerm);
                });
            }
    
            // Sort options (default to newest first)
            $sortField = $request->input('sort_field', 'dngn.id');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
    
            // Get all records instead of paginating
            $results = $query->get();
            $totalCount = count($results);
    
            // Also get all unique values for dropdown filters
            $uniqueVuDauTu = DB::table('tb_de_nghi_thanhtoan_dv')
                ->select('vu_dau_tu')
                ->distinct()
                ->whereNotNull('vu_dau_tu')
                ->pluck('vu_dau_tu');
                
            $uniqueLoaiThanhToan = DB::table('tb_de_nghi_thanhtoan_dv')
                ->select('loai_thanh_toan')
                ->distinct()
                ->whereNotNull('loai_thanh_toan')
                ->pluck('loai_thanh_toan');
                
            $uniqueTrangThaiThanhToan = DB::table('tb_phieu_trinh_thanh_toan')
                ->select('trang_thai_thanh_toan')
                ->distinct()
                ->whereNotNull('trang_thai_thanh_toan')
                ->pluck('trang_thai_thanh_toan');
    
            return response()->json([
                'success' => true,
                'data' => $results,
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $totalCount, // Set per_page to total count to indicate all records
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => 1,
                    'to' => $totalCount,
                ],
                'unique_filters' => [
                    'vu_dau_tu' => $uniqueVuDauTu,
                    'loai_thanh_toan' => $uniqueLoaiThanhToan,
                    'trang_thai_thanh_toan' => $uniqueTrangThaiThanhToan
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




}