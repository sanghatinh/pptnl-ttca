<?php


namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\Farmer\UserFarmer;

class DashboardFarmerReportControllers extends Controller
{
   public function getFinancialSummary(Request $request)
    {
       
        try {
             Log::info('Headers: ' . json_encode($request->headers->all()));
    Log::info('X-Supplier-Number: ' . $request->header('X-Supplier-Number'));
    Log::info('x-supplier-number: ' . $request->header('x-supplier-number'));
    Log::info('HTTP_X_SUPPLIER_NUMBER: ' . $request->server('HTTP_X_SUPPLIER_NUMBER'));
            // Get supplier ID from request header
           $supplierId = $request->header('X-Supplier-Number');
            
            // Get selected investment project from request
            $selectedInvestmentProject = $request->input('investment_project', 'all');
            
            // Try to get supplier ID from JWT token if not in header
            if (!$supplierId) {
                try {
                    $user = JWTAuth::parseToken()->authenticate();
                    if (isset($user->supplier_number)) {
                        $supplierId = $user->supplier_number;
                        Log::info('Using supplier ID from JWT token: ' . $supplierId);
                    } else {
                        // Try to get from user_farmer relation if exists
                        $farmer = DB::table('user_farmer')
                            ->where('user_id', $user->id)
                            ->first();
                            
                        if ($farmer && isset($farmer->supplier_number)) {
                            $supplierId = $farmer->supplier_number;
                            Log::info('Using supplier ID from user_farmer relation: ' . $supplierId);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not get supplier ID from JWT token: ' . $e->getMessage());
                }
            }
            
            if (!$supplierId) {
                Log::warning('No supplier ID provided in header or JWT token');
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier ID not provided in header or JWT token'
                ], 400);
            }
            
            // Add debug logging
            Log::info('Looking up farmer with supplier number: ' . $supplierId);
            Log::info('Investment project filter: ' . $selectedInvestmentProject);

            // Get farmer user data from user_farmer table - check in multiple fields
            $farmer = DB::table('user_farmer')
                ->where(function($q) use ($supplierId) {
                    $q->where('supplier_number', $supplierId)
                      ->orWhere('ma_kh_ca_nhan', $supplierId)
                      ->orWhere('ma_kh_doanh_nghiep', $supplierId);
                })
                ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep', 'id')
                ->first();

            if (!$farmer) {
                Log::warning('Farmer not found with any ID matching: ' . $supplierId);
                
                // Get the available supplier numbers for debugging
                $availableSuppliers = DB::table('user_farmer')
                    ->select('supplier_number', 'ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
                    ->whereNotNull('supplier_number')
                    ->orWhereNotNull('ma_kh_ca_nhan')
                    ->orWhereNotNull('ma_kh_doanh_nghiep')
                    ->limit(10)
                    ->get();
                
                Log::info('Available IDs (sample): ' . json_encode($availableSuppliers));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy nông dân với mã số: ' . $supplierId,
                    'debug_info' => [
                        'supplier_number' => $supplierId,
                        'sample_available_suppliers' => $availableSuppliers
                    ]
                ], 404);
            }
            
            Log::info('Found farmer with ID: ' . $farmer->id);

            // Calculate totals for service payments (dich vu)
            $servicePayments = $this->getServicePaymentsTotal($farmer, $selectedInvestmentProject);
            
            // Calculate totals for seed payments (hom giong)
            $seedPayments = $this->getSeedPaymentsTotal($farmer, $selectedInvestmentProject);
            
            // Return the financial summary
            return response()->json([
                'success' => true,
                'data' => [
                    'processing' => $servicePayments['processing'] + $seedPayments['processing'],
                    'pending' => $servicePayments['pending'] + $seedPayments['pending'],
                    'received' => $servicePayments['received'] + $seedPayments['received']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting farmer financial summary: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve financial summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service payments totals by status
     * 
     * @param object $farmer
     * @param string $selectedInvestmentProject
     * @return array
     */
    private function getServicePaymentsTotal($farmer, $selectedInvestmentProject = 'all')
    {
        // Initialize totals
        $totals = [
            'processing' => 0,
            'pending' => 0,
            'received' => 0,
        ];
        
        // Build query based on farmer's customer IDs
        $query = DB::table('tb_de_nghi_thanhtoan_dv');
        
        // Apply filtering by farmer's customer IDs
        $query->where(function($q) use ($farmer) {
            if (!empty($farmer->ma_kh_ca_nhan)) {
                $q->where('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
            }
            
            if (!empty($farmer->ma_kh_doanh_nghiep)) {
                $q->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
            }
        });
        
        // Apply investment project filter if not "all"
        if ($selectedInvestmentProject !== 'all') {
            $query->where('vu_dau_tu', $selectedInvestmentProject);
        }
        
        // Get processing total (status = 'processing')
        $totals['processing'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'processing')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        // Get pending total (status = 'submitted')
        $totals['pending'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'submitted')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        // Get received total (status = 'paid')
        $totals['received'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'paid')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        return $totals;
    }

    /**
     * Get seed payments totals by status
     * 
     * @param object $farmer
     * @param string $selectedInvestmentProject
     * @return array
     */
    private function getSeedPaymentsTotal($farmer, $selectedInvestmentProject = 'all')
    {
        // Initialize totals
        $totals = [
            'processing' => 0,
            'pending' => 0,
            'received' => 0,
        ];
        
        // Build query based on farmer's customer IDs
        $query = DB::table('tb_de_nghi_thanhtoan_homgiong');
        
        // Apply filtering by farmer's customer IDs
        $query->where(function($q) use ($farmer) {
            if (!empty($farmer->ma_kh_ca_nhan)) {
                $q->where('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
            }
            
            if (!empty($farmer->ma_kh_doanh_nghiep)) {
                $q->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
            }
        });
        
        // Apply investment project filter if not "all"
        if ($selectedInvestmentProject !== 'all') {
            $query->where('vu_dau_tu', $selectedInvestmentProject);
        }
        
        // Get processing total (status = 'processing')
        $totals['processing'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'processing')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        // Get pending total (status = 'submitted')
        $totals['pending'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'submitted')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        // Get received total (status = 'paid')
        $totals['received'] = $query->clone()
            ->where('trang_thai_thanh_toan', 'paid')
            ->sum('tong_tien_thanh_toan_con_lai');
            
        return $totals;
    }

/**
 * Get debt payment vs interest data for farmer dashboard
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getDebtPaymentVsInterestData(Request $request)
{
    try {
        // Get the requested year (default to current year if not provided)
        $year = $request->input('year', date('Y'));
        
        // Get supplier ID from request header
        $supplierId = $request->header('X-Supplier-Number');
        
        // Try to get supplier ID from JWT token if not in header
        if (!$supplierId) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (isset($user->supplier_number)) {
                    $supplierId = $user->supplier_number;
                    Log::info('Using supplier ID from JWT token: ' . $supplierId);
                } else {
                    // Try to get from user_farmer relation if exists
                    $farmer = DB::table('user_farmer')
                        ->where('user_id', $user->id)
                        ->first();
                        
                    if ($farmer && isset($farmer->supplier_number)) {
                        $supplierId = $farmer->supplier_number;
                        Log::info('Using supplier ID from user_farmer relation: ' . $supplierId);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not get supplier ID from JWT token: ' . $e->getMessage());
            }
        }
        
        if (!$supplierId) {
            Log::warning('No supplier ID provided in header or JWT token');
            return response()->json([
                'success' => false,
                'message' => 'Supplier ID not provided in header or JWT token'
            ], 400);
        }
        
        // Get farmer user data from user_farmer table - check in multiple fields
        $farmer = DB::table('user_farmer')
            ->where(function($q) use ($supplierId) {
                $q->where('supplier_number', $supplierId)
                  ->orWhere('ma_kh_ca_nhan', $supplierId)
                  ->orWhere('ma_kh_doanh_nghiep', $supplierId);
            })
            ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
            ->first();

        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer not found'
            ], 404);
        }
        
        // Initialize arrays for all months
        $months = range(1, 12);
        $labels = [];
        $principalData = array_fill(0, 12, 0);
        $interestData = array_fill(0, 12, 0);
        
        // Generate labels for each month (e.g. "T1/2023")
        foreach ($months as $month) {
            $labels[] = "T{$month}/{$year}";
        }
        
        // Build query to get payment data grouped by month
        $query = DB::table('Logs_Phieu_Tinh_Lai_dv')
            ->whereYear('Ngay_Tra', $year)
            ->where(function($q) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $q->where('Ma_Khach_Hang_Ca_Nhan', $farmer->ma_kh_ca_nhan);
                }
                
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $q->orWhere('Ma_Khach_Hang_Doanh_Nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            })
            ->select(
                DB::raw('MONTH(Ngay_Tra) as month'),
                DB::raw('SUM(Da_Tra_Goc) as principal_paid'),
                DB::raw('SUM(Tien_Lai) as interest_paid')
            )
            ->groupBy(DB::raw('MONTH(Ngay_Tra)'))
            ->orderBy(DB::raw('MONTH(Ngay_Tra)'));
        
        $results = $query->get();
        
        // Fill in the data arrays with actual values
        foreach ($results as $result) {
            $monthIndex = $result->month - 1; // Arrays are 0-indexed
            $principalData[$monthIndex] = (float)$result->principal_paid;
            $interestData[$monthIndex] = (float)$result->interest_paid;
        }
        
        // Prepare the response
        $response = [
            'success' => true,
            'data' => [
                'labels' => $labels,
                'principalPaid' => $principalData,
                'interestPaid' => $interestData,
                'year' => $year
            ]
        ];
        
        // Also fetch available years for dropdown selection
        $availableYears = DB::table('Logs_Phieu_Tinh_Lai_dv')
            ->where(function($q) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $q->where('Ma_Khach_Hang_Ca_Nhan', $farmer->ma_kh_ca_nhan);
                }
                
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $q->orWhere('Ma_Khach_Hang_Doanh_Nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            })
            ->select(DB::raw('DISTINCT YEAR(Ngay_Tra) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
            
        $response['data']['availableYears'] = $availableYears;
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        Log::error('Error getting debt payment vs interest data: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get debt payment type distribution for farmer dashboard
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getDebtPaymentTypeDistribution(Request $request)
{
    try {
        // Get the requested year (default to current year if not provided)
        $year = $request->input('year', date('Y'));
        
        // Get requested month (optional, if user clicked on a specific month in the chart)
        $month = $request->input('month');
        
        // Get supplier ID from request header
        $supplierId = $request->header('X-Supplier-Number');
        
        // Try to get supplier ID from JWT token if not in header
        if (!$supplierId) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (isset($user->supplier_number)) {
                    $supplierId = $user->supplier_number;
                    Log::info('Using supplier ID from JWT token: ' . $supplierId);
                } else {
                    // Try to get from user_farmer relation if exists
                    $farmer = DB::table('user_farmer')
                        ->where('user_id', $user->id)
                        ->first();
                        
                    if ($farmer && isset($farmer->supplier_number)) {
                        $supplierId = $farmer->supplier_number;
                        Log::info('Using supplier ID from user_farmer relation: ' . $supplierId);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not get supplier ID from JWT token: ' . $e->getMessage());
            }
        }
        
        if (!$supplierId) {
            Log::warning('No supplier ID provided in header or JWT token');
            return response()->json([
                'success' => false,
                'message' => 'Supplier ID not provided in header or JWT token'
            ], 400);
        }
        
        // Get farmer user data from user_farmer table - check in multiple fields
        $farmer = DB::table('user_farmer')
            ->where(function($q) use ($supplierId) {
                $q->where('supplier_number', $supplierId)
                  ->orWhere('ma_kh_ca_nhan', $supplierId)
                  ->orWhere('ma_kh_doanh_nghiep', $supplierId);
            })
            ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep')
            ->first();

        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer not found'
            ], 404);
        }
        
        // Build query to get payment type distribution
        $query = DB::table('Logs_Phieu_Tinh_Lai_dv')
            ->whereYear('Ngay_Tra', $year)
            ->where(function($q) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $q->where('Ma_Khach_Hang_Ca_Nhan', $farmer->ma_kh_ca_nhan);
                }
                
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $q->orWhere('Ma_Khach_Hang_Doanh_Nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            })
            ->select(
                'Category_Debt',
                DB::raw('SUM(Da_Tra_Goc) as total_paid'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('Category_Debt');
        
        // Apply month filter if specified
        if ($month) {
            $query->whereMonth('Ngay_Tra', $month);
        }
        
        $results = $query->get();
        
        // Format results for pie chart
        $pieChartData = [];
        $totalAmount = 0;
        $colorPalette = [
            'rgba(75, 192, 192, 0.8)',    // teal
            'rgba(255, 159, 64, 0.8)',    // orange
            'rgba(255, 99, 132, 0.8)',    // red
            'rgba(54, 162, 235, 0.8)',    // blue
            'rgba(153, 102, 255, 0.8)',   // purple
            'rgba(255, 205, 86, 0.8)',    // yellow
            'rgba(201, 203, 207, 0.8)'    // grey
        ];
        $colorIndex = 0;
        
        foreach ($results as $result) {
            // Skip empty categories
            if (empty($result->Category_Debt)) continue;
            
            $amount = (float)$result->total_paid;
            $totalAmount += $amount;
            
            $pieChartData[] = [
                'label' => $result->Category_Debt,
                'value' => $amount,
                'count' => $result->transaction_count,
                'color' => $colorPalette[$colorIndex % count($colorPalette)]
            ];
            
            $colorIndex++;
        }
        
        // Calculate percentages
        foreach ($pieChartData as &$item) {
            $item['percentage'] = $totalAmount > 0 ? round(($item['value'] / $totalAmount) * 100, 1) : 0;
        }
        
        // Prepare the response
        $response = [
            'success' => true,
            'data' => [
                'items' => $pieChartData,
                'totalAmount' => $totalAmount,
                'year' => $year,
                'month' => $month
            ]
        ];
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        Log::error('Error getting debt payment type distribution: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Get payment requests for farmer dashboard
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getPaymentRequests(Request $request)
{
    try {
        // Get supplier ID from request header
        $supplierId = $request->header('X-Supplier-Number');
        
        // Try to get supplier ID from JWT token if not in header
        if (!$supplierId) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (isset($user->supplier_number)) {
                    $supplierId = $user->supplier_number;
                    Log::info('Using supplier ID from JWT token: ' . $supplierId);
                } else {
                    // Try to get from user_farmer relation if exists
                    $farmer = DB::table('user_farmer')
                        ->where('user_id', $user->id)
                        ->first();
                        
                    if ($farmer && isset($farmer->supplier_number)) {
                        $supplierId = $farmer->supplier_number;
                        Log::info('Using supplier ID from user_farmer relation: ' . $supplierId);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not get supplier ID from JWT token: ' . $e->getMessage());
            }
        }
        
        if (!$supplierId) {
            Log::warning('No supplier ID provided in header or JWT token');
            return response()->json([
                'success' => false,
                'message' => 'Supplier ID not provided in header or JWT token'
            ], 400);
        }
        
        // Get farmer user data from user_farmer table - check in multiple fields
        $farmer = DB::table('user_farmer')
            ->where(function($q) use ($supplierId) {
                $q->where('supplier_number', $supplierId)
                  ->orWhere('ma_kh_ca_nhan', $supplierId)
                  ->orWhere('ma_kh_doanh_nghiep', $supplierId);
            })
            ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep', 'id')
            ->first();

        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer not found'
            ], 404);
        }
        
        // Query service payment requests
        $servicePayments = DB::table('tb_de_nghi_thanhtoan_dv')
            ->where(function($query) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $query->orWhere('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                }
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $query->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            })
            ->select(
                'ma_giai_ngan as code',
                'loai_thanh_toan as type',
                'tong_tien_thanh_toan_con_lai as amount',
                'trang_thai_thanh_toan as status'
            )
            ->get();
        
        // Query seed payment requests
        $seedPayments = DB::table('tb_de_nghi_thanhtoan_homgiong')
            ->where(function($query) use ($farmer) {
                if (!empty($farmer->ma_kh_ca_nhan)) {
                    $query->orWhere('ma_khach_hang_ca_nhan', $farmer->ma_kh_ca_nhan);
                }
                if (!empty($farmer->ma_kh_doanh_nghiep)) {
                    $query->orWhere('ma_khach_hang_doanh_nghiep', $farmer->ma_kh_doanh_nghiep);
                }
            })
            ->select(
                'ma_giai_ngan as code',
                'loai_thanh_toan as type',
                'tong_tien_thanh_toan_con_lai as amount',
                'trang_thai_thanh_toan as status'
            )
            ->get();
        
        // Combine results
        $allPayments = $servicePayments->concat($seedPayments);
        
        // Map status values from English to Vietnamese
        foreach ($allPayments as $payment) {
            switch ($payment->status) {
                case 'processing':
                    $payment->status = 'Đang xử lý';
                    break;
                case 'submitted':
                    $payment->status = 'Chờ thanh toán';
                    break;
                case 'paid':
                    $payment->status = 'Đã thanh toán';
                    break;
                case 'rejected':
                    $payment->status = 'Từ chối';
                    break;
                default:
                    // Keep original status if not matched
                    break;
            }
        }
        
        // Sort by code (descending order to show newest first)
        $sorted = $allPayments->sortByDesc('code')->values()->all();
        
        return response()->json([
            'success' => true,
            'data' => $sorted
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error getting farmer payment requests: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve payment requests: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get work items for farmer dashboard
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getWorkItems(Request $request)
{
    try {
        $supplierId = $request->header('X-Supplier-Number');
        $selectedInvestmentProject = $request->input('investment_project', 'all');

        // Try to get supplier ID from JWT token if not in header
        if (!$supplierId) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (isset($user->supplier_number)) {
                    $supplierId = $user->supplier_number;
                } else {
                    $farmer = \DB::table('user_farmer')->where('user_id', $user->id)->first();
                    if ($farmer && isset($farmer->supplier_number)) {
                        $supplierId = $farmer->supplier_number;
                    }
                }
            } catch (\Exception $e) {}
        }

        if (!$supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier ID not provided'
            ], 400);
        }

        // Get farmer user data from user_farmer table - check in multiple fields
        $farmer = \DB::table('user_farmer')
            ->where(function($q) use ($supplierId) {
                $q->where('supplier_number', $supplierId)
                  ->orWhere('ma_kh_ca_nhan', $supplierId)
                  ->orWhere('ma_kh_doanh_nghiep', $supplierId);
            })
            ->select('ma_kh_ca_nhan', 'ma_kh_doanh_nghiep', 'id')
            ->first();

        if (!$farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer not found'
            ], 404);
        }

        // --- Service Work Items (Dịch vụ) ---
        $serviceCacheKey = "farmer_service_workitems_{$farmer->id}_{$selectedInvestmentProject}";
        if (function_exists('apcu_exists') && apcu_exists($serviceCacheKey)) {
            $serviceWorkItems = apcu_fetch($serviceCacheKey);
        } else {
            $serviceQuery = \DB::table('tb_chitiet_dichvu_nt as ct')
                ->join('tb_bien_ban_nghiemthu_dv as bb', 'ct.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
                ->where(function($q) use ($farmer) {
                    if (!empty($farmer->ma_kh_ca_nhan)) {
                        $q->where('bb.MaKH_Chumia_Canhan', $farmer->ma_kh_ca_nhan);
                    }
                    if (!empty($farmer->ma_kh_doanh_nghiep)) {
                        $q->orWhere('bb.MaKH_Chumia_DN', $farmer->ma_kh_doanh_nghiep);
                    }
                });
            if ($selectedInvestmentProject !== 'all') {
                $serviceQuery->where('bb.vu_dau_tu', $selectedInvestmentProject);
            }
            $serviceQuery->select(
                'ct.dich_vu as name',
                'ct.don_vi_tinh as unit',
                \DB::raw('SUM(ct.khoi_luong_thuc_hien) as quantity'),
                \DB::raw('SUM(ct.thanh_tien) as amount')
            )
            ->groupBy('ct.dich_vu', 'ct.don_vi_tinh');

            $serviceWorkItems = $serviceQuery->get()->map(function($item, $idx) {
                return [
                    'id' => 'service_' . md5($item->name . $item->unit),
                    'name' => $item->name,
                    'quantity' => (float)$item->quantity,
                    'unit' => $item->unit,
                    'amount' => (float)$item->amount,
                ];
            })->toArray();

            if (function_exists('apcu_store')) {
                apcu_store($serviceCacheKey, $serviceWorkItems, 600);
            }
        }

        // --- Seed Work Items (Hom giống) ---
        $seedCacheKey = "farmer_seed_workitems_{$farmer->id}_{$selectedInvestmentProject}";
        if (function_exists('apcu_exists') && apcu_exists($seedCacheKey)) {
            $seedWorkItems = apcu_fetch($seedCacheKey);
        } else {
            $seedQuery = \DB::table('bien_ban_nghiem_thu_hom_giong as bb')
                ->where(function($q) use ($farmer) {
                    if (!empty($farmer->ma_kh_ca_nhan)) {
                        $q->where('bb.ma_khach_hang_giao_hom', $farmer->ma_kh_ca_nhan);
                    }
                    if (!empty($farmer->ma_kh_doanh_nghiep)) {
                        $q->orWhere('bb.ma_khach_hang_giao_hom_DN', $farmer->ma_kh_doanh_nghiep);
                    }
                });
            if ($selectedInvestmentProject !== 'all') {
                $seedQuery->where('bb.vu_dau_tu', $selectedInvestmentProject);
            }
            $seedResult = $seedQuery->select(
                \DB::raw('SUM(bb.tong_thuc_nhan) as quantity'),
                \DB::raw('SUM(bb.tong_tien) as amount')
            )->first();

            $seedWorkItems = [];
            if ($seedResult && ($seedResult->quantity > 0 || $seedResult->amount > 0)) {
                $seedWorkItems[] = [
                    'id' => 'seed_homgiong',
                    'name' => 'Hom giống',
                    'quantity' => (float)$seedResult->quantity,
                    'unit' => 'Tấn',
                    'amount' => (float)$seedResult->amount,
                ];
            }

            if (function_exists('apcu_store')) {
                apcu_store($seedCacheKey, $seedWorkItems, 600);
            }
        }

        // Merge and return
        $allWorkItems = array_merge($serviceWorkItems, $seedWorkItems);

        return response()->json([
            'success' => true,
            'data' => $allWorkItems
        ]);
    } catch (\Exception $e) {
        \Log::error('Error getting farmer work items: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve work items: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get service work items for farmer
 * 
 * @param object $farmer
 * @param string $selectedInvestmentProject
 * @return array
 */
private function getServiceWorkItems($farmer, $selectedInvestmentProject)
{
    $query = DB::table('tb_chitiet_dichvu_nt as chitiet')
        ->join('tb_bien_ban_nghiemthu_dv as bb', 'chitiet.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
        ->join('Logs_phieu_trinh_thanh_toan as logs', 'bb.ma_nghiem_thu', '=', 'logs.ma_nghiem_thu')
        ->join('tb_phieu_trinh_thanh_toan as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
        ->where(function($q) use ($farmer) {
            if (!empty($farmer->ma_kh_ca_nhan)) {
                $q->where('bb.MaKH_Chumia_Canhan', $farmer->ma_kh_ca_nhan);
            }
            
            if (!empty($farmer->ma_kh_doanh_nghiep)) {
                $q->orWhere('bb.MaKH_Chumia_DN', $farmer->ma_kh_doanh_nghiep);
            }
        })
        ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);
        
    // Apply investment project filter if not "all"
    if ($selectedInvestmentProject !== 'all') {
        $query->where('bb.vu_dau_tu', $selectedInvestmentProject);
    }
    
    $results = $query->select(
            'chitiet.dich_vu',
            'chitiet.don_vi_tinh',
            DB::raw('SUM(chitiet.khoi_luong_thuc_hien) as total_quantity'),
            DB::raw('SUM(chitiet.thanh_tien) as total_amount')
        )
        ->groupBy('chitiet.dich_vu', 'chitiet.don_vi_tinh')
        ->get();
    
    $workItems = [];
    foreach ($results as $result) {
        $workItems[] = [
            'id' => 'service_' . md5($result->dich_vu),
            'name' => $result->dich_vu,
            'quantity' => (float)$result->total_quantity,
            'unit' => $result->don_vi_tinh,
            'amount' => (float)$result->total_amount
        ];
    }
    
    return $workItems;
}

/**
 * Get seed work items for farmer (Hom giống)
 * 
 * @param object $farmer
 * @param string $selectedInvestmentProject
 * @return array
 */
private function getSeedWorkItems($farmer, $selectedInvestmentProject)
{
    $query = DB::table('bien_ban_nghiem_thu_hom_giong as bb')
        ->join('logs_phieu_trinh_thanh_toan_homgiong as logs', 'bb.ma_so_phieu', '=', 'logs.ma_so_phieu')
        ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'logs.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
        ->where(function($q) use ($farmer) {
            if (!empty($farmer->ma_kh_ca_nhan)) {
                $q->where('bb.ma_khach_hang_giao_hom', $farmer->ma_kh_ca_nhan);
            }
            
            if (!empty($farmer->ma_kh_doanh_nghiep)) {
                $q->orWhere('bb.ma_khach_hang_giao_hom_DN', $farmer->ma_kh_doanh_nghiep);
            }
        })
        ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);
        
    // Apply investment project filter if not "all"
    if ($selectedInvestmentProject !== 'all') {
        $query->where('bb.vu_dau_tu', $selectedInvestmentProject);
    }
    
    $result = $query->select(
            DB::raw('SUM(bb.tong_thuc_nhan) as total_quantity'),
            DB::raw('SUM(bb.tong_tien) as total_amount')
        )
        ->first();
    
    $workItems = [];
    
    // Only add if there's data
    if ($result && ($result->total_quantity > 0 || $result->total_amount > 0)) {
        $workItems[] = [
            'id' => 'seed_homgiong',
            'name' => 'Hom giống',
            'quantity' => (float)$result->total_quantity,
            'unit' => 'Tấn',
            'amount' => (float)$result->total_amount
        ];
    }
    
    return $workItems;
}
}