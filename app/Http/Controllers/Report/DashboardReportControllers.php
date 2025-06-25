<?php


namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardReportControllers extends Controller
{

    public function __construct()
{
    // Set query timeout to prevent long-running queries
    DB::statement('SET SESSION MAX_EXECUTION_TIME=5000'); // 5 seconds timeout
}
    public function getReportChartSection(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get period from request (week, month, year)
            $period = $request->input('period', 'week');
            
            // Calculate date range based on period
            $dateRange = $this->getDateRange($period);
            
            // Get user position
            $userPosition = $user->position;
            $userStation = $user->station;
            $userMaNhanVien = $user->ma_nhan_vien;

            // Initialize result array for stations
            $stationsData = [];
            
            // Define station mapping
            $stationMapping = [
                1 => ['name' => 'TRẠM 1', 'code' => 'TTCA-TRAM01'],
                2 => ['name' => 'TRẠM 2', 'code' => 'TTCA-TRAM02'],
                3 => ['name' => 'TRẠM 3', 'code' => 'TTCA-TRAM03'],
                4 => ['name' => 'TRẠM 4', 'code' => 'TTCA-TRAM04'],
                5 => ['name' => 'TRẠM 5', 'code' => 'TTCA-TRAM05'],
                6 => ['name' => 'TRẠM 6', 'code' => 'TTCA-TRAM06']
            ];

            // Check user role and get data accordingly
            if (in_array($userPosition, ['department_head', 'office_workers'])) {
                // Get all stations data
                foreach ($stationMapping as $stationNumber => $stationInfo) {
                    $stationData = $this->getStationData($stationInfo['code'], $period, $dateRange);
                    $stationsData[] = [
                        'name' => $stationInfo['name'],
                        'quantity' => $stationData['quantity'],
                        'weeklyAmount' => $stationData['periodAmount'],
                        'cumulativeAmount' => $stationData['cumulativeAmount']
                    ];
                }
            } elseif ($userPosition === 'Station_Chief') {
                // Get only user's station data using station code directly
                $userStationCode = $userStation; // Assuming userStation already contains station code like 'TTCA-TRAM01'
                
                // Find station name from mapping
                $stationName = 'TRẠM';
                foreach ($stationMapping as $stationNumber => $stationInfo) {
                    if ($stationInfo['code'] === $userStationCode) {
                        $stationName = $stationInfo['name'];
                        break;
                    }
                }
                
                $stationData = $this->getStationData($userStationCode, $period, $dateRange);
                $stationsData[] = [
                    'name' => $stationName,
                    'quantity' => $stationData['quantity'],
                    'weeklyAmount' => $stationData['periodAmount'],
                    'cumulativeAmount' => $stationData['cumulativeAmount']
                ];
            } elseif ($userPosition === 'Farm_worker') {
                // Get data filtered by ma_nhan_vien
                $stationData = $this->getFarmWorkerData($userMaNhanVien, $period, $dateRange);
                $stationsData[] = [
                    'name' => 'Dữ liệu của tôi',
                    'quantity' => $stationData['quantity'],
                    'weeklyAmount' => $stationData['periodAmount'],
                    'cumulativeAmount' => $stationData['cumulativeAmount']
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'stations' => $stationsData,
                    'period' => $period,
                    'dateRange' => $dateRange
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch chart data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'week':
                return [
                    'start' => $now->startOfWeek()->format('Y-m-d'),
                    'end' => $now->endOfWeek()->format('Y-m-d')
                ];
            case 'month':
                return [
                    'start' => $now->startOfMonth()->format('Y-m-d'),
                    'end' => $now->endOfMonth()->format('Y-m-d')
                ];
            case 'year':
                return [
                    'start' => $now->startOfYear()->format('Y-m-d'),
                    'end' => $now->endOfYear()->format('Y-m-d')
                ];
            default:
                return [
                    'start' => $now->startOfWeek()->format('Y-m-d'),
                    'end' => $now->endOfWeek()->format('Y-m-d')
                ];
        }
    }

    private function getStationData($stationCode, $period, $dateRange)
    {
        // Get service data (bien_ban_nghiemthu_dv)
        $serviceData = $this->getServiceData($stationCode, $period, $dateRange);
        
        // Get seed data (bien_ban_nghiem_thu_hom_giong)
        $seedData = $this->getSeedData($stationCode, $period, $dateRange);
        
        return [
            'quantity' => $serviceData['quantity'] + $seedData['quantity'],
            'periodAmount' => $serviceData['periodAmount'] + $seedData['periodAmount'],
            'cumulativeAmount' => $serviceData['cumulativeAmount'] + $seedData['cumulativeAmount']
        ];
    }

    private function getServiceData($stationCode, $period, $dateRange)
{
    // Use a single raw SQL query with conditional aggregation
    $result = DB::select("
        SELECT 
            COUNT(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN 1 ELSE NULL END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bbnt.tong_tien ELSE 0 END) as period_amount,
            COUNT(*) as total_quantity,
            SUM(bbnt.tong_tien) as cumulative_amount
        FROM tb_bien_ban_nghiemthu_dv as bbnt
        JOIN Logs_phieu_trinh_thanh_toan as log ON bbnt.ma_nghiem_thu = log.ma_nghiem_thu
        JOIN tb_phieu_trinh_thanh_toan as pttt ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE bbnt.tram = ?
        AND pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')",
        [
            $dateRange['start'], $dateRange['end'],
            $dateRange['start'], $dateRange['end'],
            $stationCode
        ]
    );
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'total_quantity' => 0,
        'cumulative_amount' => 0
    ];

    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}

    private function getSeedData($stationCode, $period, $dateRange)
{
    // Use a single raw SQL query with conditional aggregation
    $result = DB::select("
        SELECT 
            COUNT(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN 1 ELSE NULL END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bbhg.tong_tien ELSE 0 END) as period_amount,
            COUNT(*) as total_quantity,
            SUM(bbhg.tong_tien) as cumulative_amount
        FROM bien_ban_nghiem_thu_hom_giong as bbhg
        JOIN logs_phieu_trinh_thanh_toan_homgiong as log ON bbhg.ma_so_phieu = log.ma_so_phieu
        JOIN tb_phieu_trinh_thanh_toan_homgiong as pttt ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE bbhg.tram = ?
        AND pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')",
        [
            $dateRange['start'], $dateRange['end'],
            $dateRange['start'], $dateRange['end'],
            $stationCode
        ]
    );
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'total_quantity' => 0,
        'cumulative_amount' => 0
    ];

    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}

    private function getFarmWorkerData($maNhanVien, $period, $dateRange)
    {
        // Get service data for specific farm worker
        $serviceData = $this->getFarmWorkerServiceData($maNhanVien, $period, $dateRange);
        
        // Get seed data for specific farm worker
        $seedData = $this->getFarmWorkerSeedData($maNhanVien, $period, $dateRange);
        
        return [
            'quantity' => $serviceData['quantity'] + $seedData['quantity'],
            'periodAmount' => $serviceData['periodAmount'] + $seedData['periodAmount'],
            'cumulativeAmount' => $serviceData['cumulativeAmount'] + $seedData['cumulativeAmount']
        ];
    }

    private function getFarmWorkerServiceData($maNhanVien, $period, $dateRange)
{
    // Use a single query with conditional aggregation to get both period and cumulative data
    $result = DB::select("
        SELECT 
            COUNT(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN 1 ELSE NULL END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bbnt.tong_tien ELSE 0 END) as period_amount,
            COUNT(*) as total_quantity,
            SUM(bbnt.tong_tien) as cumulative_amount
        FROM tb_bien_ban_nghiemthu_dv as bbnt
        JOIN Logs_phieu_trinh_thanh_toan as log ON bbnt.ma_nghiem_thu = log.ma_nghiem_thu
        JOIN tb_phieu_trinh_thanh_toan as pttt ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE bbnt.ma_nhan_vien = ?
        AND pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')",
        [
            $dateRange['start'], $dateRange['end'],
            $dateRange['start'], $dateRange['end'],
            $maNhanVien
        ]
    );
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'total_quantity' => 0,
        'cumulative_amount' => 0
    ];

    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}
    private function getFarmWorkerSeedData($maNhanVien, $period, $dateRange)
{
    // Use a single query with conditional aggregation to get both period and cumulative data
    $result = DB::select("
        SELECT 
            COUNT(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN 1 ELSE NULL END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bbhg.tong_tien ELSE 0 END) as period_amount,
            COUNT(*) as total_quantity,
            SUM(bbhg.tong_tien) as cumulative_amount
        FROM bien_ban_nghiem_thu_hom_giong as bbhg
        JOIN logs_phieu_trinh_thanh_toan_homgiong as log ON bbhg.ma_so_phieu = log.ma_so_phieu
        JOIN tb_phieu_trinh_thanh_toan_homgiong as pttt ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE bbhg.ma_nhan_vien = ?
        AND pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')",
        [
            $dateRange['start'], $dateRange['end'],
            $dateRange['start'], $dateRange['end'],
            $maNhanVien
        ]
    );
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'total_quantity' => 0,
        'cumulative_amount' => 0
    ];

    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}
// Get report table section data
// This method fetches detailed job categories data for the report table section
public function getAvailableStationsApi(Request $request)
{
    return $this->getAvailableStations($request);
}
public function getReportTableSection(Request $request)
{
    try {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get period and station filter from request
        $period = $request->input('period', 'week');
        $stationFilter = $request->input('station', null);
        
        // Create a unique cache key based on user and request parameters
        $cacheKey = "report_table_{$user->id}_{$period}_" . ($stationFilter ?: 'all');
        
        // Try to get data from cache first with improved handling
        if (function_exists('apcu_exists') && apcu_exists($cacheKey)) {
            $cachedData = apcu_fetch($cacheKey);
            if ($cachedData) {
                return response()->json([
                    'success' => true,
                    'data' => $cachedData,
                    'fromCache' => true
                ]);
            }
        }
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($period);
        
        // Get user position and details
        $userPosition = $user->position;
        $userStation = $user->station;
        $userMaNhanVien = $user->ma_nhan_vien;

        // Add performance tracking
        $startTime = microtime(true);

        // Get job categories data based on user role
        $jobCategories = [];
        
        if (in_array($userPosition, ['department_head', 'office_workers'])) {
            // Get all data, but apply station filter if provided
            $effectiveStation = $stationFilter;
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, $effectiveStation);
        } elseif ($userPosition === 'Station_Chief') {
            // Filter by user's station (ignore station filter for Station_Chief)
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, $userStation);
        } elseif ($userPosition === 'Farm_worker') {
            // Filter by user's ma_nhan_vien (ignore station filter for Farm_worker)
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, null, $userMaNhanVien);
        }
        
        // Get available stations (with caching)
        $stationsForUserCacheKey = "stations_for_user_{$userPosition}_{$userStation}";
        if (function_exists('apcu_exists') && apcu_exists($stationsForUserCacheKey)) {
            $availableStations = apcu_fetch($stationsForUserCacheKey);
        } else {
            $availableStations = $this->getStationsForUser($userPosition, $userStation);
            if (function_exists('apcu_store')) {
                apcu_store($stationsForUserCacheKey, $availableStations, 86400); // Cache for 24 hours
            }
        }
        
        $responseData = [
            'jobCategories' => $jobCategories,
            'period' => $period,
            'dateRange' => $dateRange,
            'userPosition' => $userPosition,
            'stationFilter' => $stationFilter,
            'availableStations' => $availableStations
        ];

        // Calculate execution time
        $executionTime = round(microtime(true) - $startTime, 2);
        $responseData['executionTime'] = $executionTime;

        // Store in cache with longer TTL to reduce database load
        if (function_exists('apcu_store')) {
            $ttl = 1800; // 30 minutes
            apcu_store($cacheKey, $responseData, $ttl);
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch table data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API endpoint to get available stations for the current user
 * This implementation uses APCu caching for significant performance boost
 * 
 * @return \Illuminate\Http\JsonResponse
 */
public function getAvailableStations(Request $request)
{
    try {
        // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $userPosition = $user->position;
        $userStation = $user->station;
        
        // Create a cache key based on user position and station
        $cacheKey = 'available_stations_' . $userPosition . '_' . $userStation;
        
        // Check if data exists in cache
        if (function_exists('apcu_exists') && apcu_exists($cacheKey)) {
            $stations = apcu_fetch($cacheKey);
        } else {
            // Get stations based on user position
            $stations = $this->getStationsForUser($userPosition, $userStation);
            
            // Store in cache for 24 hours if APCu is available
            if (function_exists('apcu_store')) {
                apcu_store($cacheKey, $stations, 86400); // 24 hours
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $stations
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch available stations: ' . $e->getMessage()
        ], 500);
    }
}


private function getStationsForUser($userPosition, $userStation)
{
    if (in_array($userPosition, ['department_head', 'office_workers'])) {
        // ผู้บริหารและเจ้าหน้าที่สำนักงานสามารถดูทุก station
        return [
            ['code' => null, 'name' => 'Tất cả các trạm'],
            ['code' => 'TTCA-TRAM01', 'name' => 'TRẠM 1'],
            ['code' => 'TTCA-TRAM02', 'name' => 'TRẠM 2'],
            ['code' => 'TTCA-TRAM03', 'name' => 'TRẠM 3'],
            ['code' => 'TTCA-TRAM04', 'name' => 'TRẠM 4'],
            ['code' => 'TTCA-TRAM05', 'name' => 'TRẠM 5'],
            ['code' => 'TTCA-TRAM06', 'name' => 'TRẠM 6']
        ];
    } elseif ($userPosition === 'Station_Chief') {
        // หัวหน้าสถานีเห็นแค่สถานีของตัวเอง
        $stationMapping = [
            'TTCA-TRAM01' => 'TRẠM 1',
            'TTCA-TRAM02' => 'TRẠM 2',
            'TTCA-TRAM03' => 'TRẠM 3',
            'TTCA-TRAM04' => 'TRẠM 4',
            'TTCA-TRAM05' => 'TRẠM 5',
            'TTCA-TRAM06' => 'TRẠM 6'
        ];
        
        return [
            ['code' => $userStation, 'name' => $stationMapping[$userStation] ?? 'TRẠM']
        ];
    } else {
        // พนักงานไร่ไม่มี filter
        return [];
    }
}



private function getJobCategoriesData($period, $dateRange, $station = null, $maNhanVien = null)
{
    $jobCategories = [];
    
    // Get service job categories
    $serviceJobs = $this->getServiceJobCategories($period, $dateRange, $station, $maNhanVien);
    $jobCategories = array_merge($jobCategories, $serviceJobs);
    
    // Add "Hom giống" job category
    $homGiongJob = $this->getHomGiongJobCategory($period, $dateRange, $station, $maNhanVien);
    $jobCategories[] = $homGiongJob;
    
    return $jobCategories;
}

private function getServiceJobCategories($period, $dateRange, $station = null, $maNhanVien = null)
{
    // Create a cache key for this specific query
    $cacheKey = "service_jobs_{$period}_{$dateRange['start']}_{$dateRange['end']}_" . ($station ?: 'all') . "_" . ($maNhanVien ?: 'all');
    
    // Try to get from cache first
    if (function_exists('apcu_exists') && apcu_exists($cacheKey)) {
        $jobCategories = apcu_fetch($cacheKey);
        if ($jobCategories !== false) {
            \Log::info('Using cached service job categories');
            return $jobCategories;
        }
    }
    
    // Build query parameters
    $params = [
        $dateRange['start'], 
        $dateRange['end'],
        $dateRange['start'], 
        $dateRange['end']
    ];
    
    // Build WHERE clause
    $whereConditions = ["pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')"];
    
    if ($station) {
        $whereConditions[] = "bb.tram = ?";
        $params[] = $station;
    }
    
    if ($maNhanVien) {
        $whereConditions[] = "bb.ma_nhan_vien = ?";
        $params[] = $maNhanVien;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Execute optimized query with LIMIT to prevent too many rows
    $sql = "
        SELECT 
            ct.dich_vu,
            ct.don_vi_tinh,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN ct.khoi_luong_thuc_hien ELSE 0 END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN ct.thanh_tien ELSE 0 END) as period_amount,
            SUM(ct.thanh_tien) as cumulative_amount
        FROM tb_chitiet_dichvu_nt as ct
        JOIN tb_bien_ban_nghiemthu_dv as bb 
            ON ct.ma_nghiem_thu = bb.ma_nghiem_thu
        JOIN Logs_phieu_trinh_thanh_toan as log 
            ON bb.ma_nghiem_thu = log.ma_nghiem_thu
        JOIN tb_phieu_trinh_thanh_toan as pttt 
            ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE $whereClause
        GROUP BY ct.dich_vu, ct.don_vi_tinh
        LIMIT 100
    ";
    
    try {
        $services = DB::select($sql, $params);
    } catch (\Exception $e) {
        \Log::error('Error in getServiceJobCategories: ' . $e->getMessage());
        return [];
    }
    
    $jobCategories = [];
    foreach ($services as $service) {
        $jobCategories[] = [
            'name' => $service->dich_vu,
            'quantity' => $service->period_quantity ?: 0,
            'unit' => $service->don_vi_tinh ?: 'Đơn vị',
            'weeklyAmount' => $service->period_amount ?: 0,
            'cumulativeAmount' => $service->cumulative_amount ?: 0
        ];
    }
    
    // Store in cache for 15 minutes
    if (function_exists('apcu_store')) {
        apcu_store($cacheKey, $jobCategories, 900);
    }
    
    return $jobCategories;
}
private function getHomGiongJobCategory($period, $dateRange, $station = null, $maNhanVien = null)
{
    // Create a cache key for this specific query
    $cacheKey = "hom_giong_{$period}_{$dateRange['start']}_{$dateRange['end']}_" . ($station ?: 'all') . "_" . ($maNhanVien ?: 'all');
    
    // Try to get from cache first
    if (function_exists('apcu_exists') && apcu_exists($cacheKey)) {
        $homGiongJob = apcu_fetch($cacheKey);
        if ($homGiongJob !== false) {
            \Log::info('Using cached hom giong data');
            return $homGiongJob;
        }
    }
    
    // Build query parameters
    $params = [
        $dateRange['start'], 
        $dateRange['end'],
        $dateRange['start'], 
        $dateRange['end']
    ];
    
    // Build WHERE clause
    $whereConditions = ["pttt.trang_thai_thanh_toan IN ('processing', 'submitted', 'paid')"];
    
    if ($station) {
        $whereConditions[] = "bb.tram = ?";
        $params[] = $station;
    }
    
    if ($maNhanVien) {
        $whereConditions[] = "bb.ma_nhan_vien = ?";
        $params[] = $maNhanVien;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Execute optimized query
    $sql = "
        SELECT 
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bb.tong_thuc_nhan ELSE 0 END) as period_quantity,
            SUM(CASE WHEN pttt.ngay_tao BETWEEN ? AND ? THEN bb.tong_tien ELSE 0 END) as period_amount,
            SUM(bb.tong_tien) as cumulative_amount
        FROM bien_ban_nghiem_thu_hom_giong as bb
        JOIN logs_phieu_trinh_thanh_toan_homgiong as log ON bb.ma_so_phieu = log.ma_so_phieu
        JOIN tb_phieu_trinh_thanh_toan_homgiong as pttt ON log.ma_trinh_thanh_toan = pttt.ma_trinh_thanh_toan
        WHERE $whereClause
    ";
    
    try {
        $result = DB::select($sql, $params);
    } catch (\Exception $e) {
        \Log::error('Error in getHomGiongJobCategory: ' . $e->getMessage());
        return [
            'name' => 'Hom giống',
            'quantity' => 0,
            'unit' => 'Tấn',
            'weeklyAmount' => 0,
            'cumulativeAmount' => 0
        ];
    }
    
    // Handle null result
    if (empty($result) || !isset($result[0])) {
        return [
            'name' => 'Hom giống',
            'quantity' => 0,
            'unit' => 'Tấn',
            'weeklyAmount' => 0,
            'cumulativeAmount' => 0
        ];
    }
    
    $homGiongJob = [
        'name' => 'Hom giống',
        'quantity' => $result[0]->period_quantity ?: 0,
        'unit' => 'Tấn',
        'weeklyAmount' => $result[0]->period_amount ?: 0,
        'cumulativeAmount' => $result[0]->cumulative_amount ?: 0
    ];
    
    // Store in cache for 15 minutes
    if (function_exists('apcu_store')) {
        apcu_store($cacheKey, $homGiongJob, 900);
    }
    
    return $homGiongJob;
}

}