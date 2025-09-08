<?php


namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardReportControllers extends Controller
{

     public function __construct()
    {
        // Set query timeout to prevent long-running queries (optimized for shared hosting)
        $this->setOptimalQueryTimeout();

        // Cache stations data for better performance on shared hosting
        $this->warmupBasicCache();
    }

    /**
     * Set optimal query timeout for shared hosting environments
     */
    private function setOptimalQueryTimeout()
    {
        try {
            // Try to set MySQL 5.7+ execution time limit
            DB::statement('SET SESSION MAX_EXECUTION_TIME=30000'); // 30 วินาที
        } catch (\Exception $e) {
            // Fallback for older MySQL versions or restricted environments
            try {
                // Try alternative method for MySQL 5.6 and below
                DB::statement('SET SESSION wait_timeout=30');
                DB::statement('SET SESSION interactive_timeout=30');
            } catch (\Exception $e2) {
                // Log the issue but continue execution
                \Log::warning('Cannot set query timeout on this hosting environment: ' . $e2->getMessage());
                
                // Set PHP execution time limit as additional safety
                $this->setPhpExecutionTimeout();
            }
        }
    }

    /**
     * Set PHP execution timeout as fallback for shared hosting
     */
    private function setPhpExecutionTimeout()
    {
        try {
            // Increase PHP execution time for report generation (if allowed)
            if (function_exists('set_time_limit')) {
                set_time_limit(60); // 60 seconds for report generation
            }
        } catch (\Exception $e) {
            \Log::info('PHP execution time limit cannot be modified on this hosting environment');
        }
    }

    /**
     * Warmup basic cache data to improve performance on shared hosting
     */
    private function warmupBasicCache()
    {
        if (!Cache::has('all_stations')) {
            $stations = [
                ['code' => 'TTCA-TRAM01', 'name' => 'TRẠM 1'],
                ['code' => 'TTCA-TRAM02', 'name' => 'TRẠM 2'],
                ['code' => 'TTCA-TRAM03', 'name' => 'TRẠM 3'],
                ['code' => 'TTCA-TRAM04', 'name' => 'TRẠM 4'],
                ['code' => 'TTCA-TRAM05', 'name' => 'TRẠM 5'],
                ['code' => 'TTCA-TRAM06', 'name' => 'TRẠM 6'],
            ];
            // ใช้ TTL ที่เหมาะสมกับ shared hosting (30 นาที)
            Cache::put('all_stations', $stations, 1800);
        }
    }
   public function getReportChartSection(Request $request)
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $period = $request->input('period', 'week');
        $cacheKey = "chart_data_{$user->id}_{$period}";

        // ใช้ cache ก่อน
        $cachedData = $this->safeCache($cacheKey, function() use ($user, $period) {
            return $this->generateChartDataOptimized($user, $period);
        }, 'report_data');

        return response()->json([
            'success' => true,
            'data' => $cachedData,
            'fromCache' => true
        ]);
    } catch (\Exception $e) {
        \Log::error('Chart data generation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch chart data: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * ใช้ stored procedure เดียวดึงข้อมูลทุกสถานี (หรือ station เดียว/พนักงานไร่) ในครั้งเดียว
 */
private function generateChartDataOptimized($user, $period)
{
    $startTime = microtime(true);
    $dateRange = $this->getDateRange($period);
    $userPosition = $user->position;
    $userStation = $user->station;
    $userMaNhanVien = $user->ma_nhan_vien;

    $stationMapping = [
        'TTCA-TRAM01' => 'TRẠM 1',
        'TTCA-TRAM02' => 'TRẠM 2',
        'TTCA-TRAM03' => 'TRẠM 3',
        'TTCA-TRAM04' => 'TRẠM 4',
        'TTCA-TRAM05' => 'TRẠM 5',
        'TTCA-TRAM06' => 'TRẠM 6'
    ];

    $stationsData = [];

    if (in_array($userPosition, ['department_head', 'office_workers'])) {
        // เรียก procedure เดียวดึงข้อมูลทุกสถานี
        $allData = $this->safeStoredProcedure('get_chart_all_stations_data', [
            $dateRange['start'],
            $dateRange['end']
        ]);
        // Map ข้อมูลให้อยู่ในรูปแบบที่ frontend ต้องการ
        $grouped = [];
        foreach ($allData as $row) {
            $stationCode = $row->station_code;
            if (!isset($grouped[$stationCode])) {
                $grouped[$stationCode] = [
                    'quantity' => 0,
                    'weeklyAmount' => 0,
                    'cumulativeAmount' => 0
                ];
            }
            $grouped[$stationCode]['quantity'] += $row->period_quantity ?? 0;
            $grouped[$stationCode]['weeklyAmount'] += $row->period_amount ?? 0;
            $grouped[$stationCode]['cumulativeAmount'] += $row->cumulative_amount ?? 0;
        }
        foreach ($stationMapping as $code => $name) {
            $stationsData[] = [
                'name' => $name,
                'quantity' => $grouped[$code]['quantity'] ?? 0,
                'weeklyAmount' => $grouped[$code]['weeklyAmount'] ?? 0,
                'cumulativeAmount' => $grouped[$code]['cumulativeAmount'] ?? 0
            ];
        }
    } elseif ($userPosition === 'Station_Chief') {
        // เรียก procedure สำหรับสถานีเดียว
        $service = $this->safeStoredProcedure('get_chart_service_data', [
            $dateRange['start'],
            $dateRange['end'],
            $userStation
        ]);
        $seed = $this->safeStoredProcedure('get_chart_seed_data', [
            $dateRange['start'],
            $dateRange['end'],
            $userStation
        ]);
        $stationsData[] = [
            'name' => $stationMapping[$userStation] ?? 'TRẠM',
            'quantity' => ($service[0]->period_quantity ?? 0) + ($seed[0]->period_quantity ?? 0),
            'weeklyAmount' => ($service[0]->period_amount ?? 0) + ($seed[0]->period_amount ?? 0),
            'cumulativeAmount' => ($service[0]->cumulative_amount ?? 0) + ($seed[0]->cumulative_amount ?? 0)
        ];
    } elseif ($userPosition === 'Farm_worker') {
        // เรียก procedure สำหรับพนักงานไร่
        $service = $this->safeStoredProcedure('get_chart_farm_worker_service_data', [
            $dateRange['start'],
            $dateRange['end'],
            $userMaNhanVien
        ]);
        $seed = $this->safeStoredProcedure('get_chart_farm_worker_seed_data', [
            $dateRange['start'],
            $dateRange['end'],
            $userMaNhanVien
        ]);
        $stationsData[] = [
            'name' => 'Dữ liệuของฉัน',
            'quantity' => ($service[0]->period_quantity ?? 0) + ($seed[0]->period_quantity ?? 0),
            'weeklyAmount' => ($service[0]->period_amount ?? 0) + ($seed[0]->period_amount ?? 0),
            'cumulativeAmount' => ($service[0]->cumulative_amount ?? 0) + ($seed[0]->cumulative_amount ?? 0)
        ];
    }

    $executionTime = round(microtime(true) - $startTime, 2);

    return [
        'stations' => $stationsData,
        'period' => $period,
        'dateRange' => $dateRange,
        'executionTime' => $executionTime
    ];
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

    // DEPRECATED: Replaced by stored procedures for better performance
    // This method is kept for compatibility but will be removed in future versions

   // DEPRECATED: Now replaced by stored procedure get_chart_service_data for better performance
   private function getServiceData($stationCode, $period, $dateRange)
{
    try {
        $result = $this->safeStoredProcedure('get_chart_service_data', [
            $dateRange['start'],
            $dateRange['end'],
            $stationCode
        ]);
    } catch (\Exception $e) {
        \Log::error('Error calling get_chart_service_data SP: ' . $e->getMessage());
        return [
            'quantity' => 0,
            'periodAmount' => 0,
            'cumulativeAmount' => 0
        ];
    }
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'cumulative_amount' => 0
    ];
    
    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}

    // DEPRECATED: Now replaced by stored procedure get_chart_seed_data for better performance
    private function getSeedData($stationCode, $period, $dateRange)
{
    // Use stored procedure instead of raw SQL for better performance
    try {
        $result = $this->safeStoredProcedure('get_chart_seed_data', [
            $dateRange['start'],
            $dateRange['end'],
            $stationCode
        ]);
    } catch (\Exception $e) {
        \Log::error('Error calling get_chart_seed_data SP: ' . $e->getMessage());
        return [
            'quantity' => 0,
            'periodAmount' => 0,
            'cumulativeAmount' => 0
        ];
    }
    
    $data = !empty($result) ? $result[0] : (object)[
        'period_quantity' => 0,
        'period_amount' => 0,
        'cumulative_amount' => 0
    ];

    return [
        'quantity' => $data->period_quantity,
        'periodAmount' => $data->period_amount ?: 0,
        'cumulativeAmount' => $data->cumulative_amount ?: 0
    ];
}

    // DEPRECATED: Now replaced by stored procedures for better performance  
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

    // DEPRECATED: Now replaced by stored procedure get_chart_farm_worker_service_data for better performance
    private function getFarmWorkerServiceData($maNhanVien, $period, $dateRange)
{
    // Use a single query with conditional aggregation to get both period and cumulative data
    $result = $this->safeDbQuery("
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
    // DEPRECATED: Now replaced by stored procedure get_chart_farm_worker_seed_data for better performance
    private function getFarmWorkerSeedData($maNhanVien, $period, $dateRange)
{
    // Use a single query with conditional aggregation to get both period and cumulative data
    $result = $this->safeDbQuery("
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
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return response()->json([
                'success' => true,
                'data' => $cachedData,
                'fromCache' => true
            ]);
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
        $availableStations = Cache::get($stationsForUserCacheKey);
        if (!$availableStations) {
            $availableStations = $this->getStationsForUser($userPosition, $userStation);
            Cache::put($stationsForUserCacheKey, $availableStations, 1800); // 30 นาทีสำหรับ shared hosting
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
        Cache::put($cacheKey, $responseData, 1200); // 20 นาทีสำหรับ shared hosting

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
        $stations = Cache::get($cacheKey);
        if (!$stations) {
            $stations = $this->getStationsForUser($userPosition, $userStation);
            Cache::put($cacheKey, $stations, 1800); // 30 นาทีสำหรับ shared hosting
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
    // Create a cache key for this specific query (ยังคงใช้ caching เดิม)
    $cacheKey = "service_jobs_{$period}_{$dateRange['start']}_{$dateRange['end']}_" . ($station ?: 'all') . "_" . ($maNhanVien ?: 'all');
    
    // Try to get from cache first
    $jobCategories = Cache::get($cacheKey);
    if ($jobCategories) {
        \Log::info('Using cached service job categories');
        return $jobCategories;
    }
    
    // เรียก Stored Procedure แทนการสร้าง SQL ใน PHP
    try {
        $services = $this->safeStoredProcedure('get_service_job_categories', [
            $dateRange['start'],
            $dateRange['end'],
            $station,
            $maNhanVien
        ]);
    } catch (\Exception $e) {
        \Log::error('Error calling get_service_job_categories SP: ' . $e->getMessage());
        return [];
    }
    
    // จัดรูปแบบผลลัพธ์ให้ตรงกับ format เดิม
    $jobCategories = [];
    foreach ($services as $service) {
        $jobCategories[] = [
            'name' => $service->name,
            'quantity' => $service->quantity ?: 0,
            'unit' => $service->unit ?: 'Đơn vị',
            'weeklyAmount' => $service->weeklyAmount ?: 0,
            'cumulativeAmount' => $service->cumulativeAmount ?: 0
        ];
    }
    
        // Store in cache with optimized TTL for shared hosting
        Cache::put($cacheKey, $jobCategories, 900); // 15 นาที
        return $jobCategories;
}
private function getHomGiongJobCategory($period, $dateRange, $station = null, $maNhanVien = null)
{
    // Create a cache key for this specific query
    $cacheKey = "hom_giong_{$period}_{$dateRange['start']}_{$dateRange['end']}_" . ($station ?: 'all') . "_" . ($maNhanVien ?: 'all');
    
    // Try to get from cache first
    $homGiongJob = Cache::get($cacheKey);
    if ($homGiongJob) {
        \Log::info('Using cached hom giong data');
        return $homGiongJob;
    }
    
    // เรียก Stored Procedure แทนการสร้าง SQL ใน PHP
    try {
        $result = $this->safeStoredProcedure('get_hom_giong_job_category', [
            $dateRange['start'],
            $dateRange['end'],
            $station,
            $maNhanVien
        ]);
    } catch (\Exception $e) {
        \Log::error('Error calling get_hom_giong_job_category SP: ' . $e->getMessage());
        return [
            'name' => 'Hom giống',
            'quantity' => 0,
            'unit' => 'Tấn',
            'weeklyAmount' => 0,
            'cumulativeAmount' => 0
        ];
    }
    
    // จัดรูปแบบผลลัพธ์ให้ตรงกับ format เดิม
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
    
    // Store in cache with optimized TTL for shared hosting
    Cache::put($cacheKey, $homGiongJob, 900); // 15 นาที
    return $homGiongJob;
}

/**
 * Get optimized cache TTL based on data type for shared hosting
 */
private function getCacheTTL($dataType = 'default')
{
    // Optimized TTL values for shared hosting performance
    $ttlMap = [
        'stations' => 1800,      // 30 นาที - ข้อมูลสถานีไม่เปลี่ยนบ่อย
        'report_data' => 1200,   // 20 นาที - ข้อมูลรายงานหลัก
        'job_categories' => 900, // 15 นาที - ข้อมูลหมวดงาน
        'user_permissions' => 1800, // 30 นาที - สิทธิ์ผู้ใช้
        'default' => 600         // 10 นาที - ค่าเริ่มต้น
    ];
    
    return $ttlMap[$dataType] ?? $ttlMap['default'];
}

/**
 * Safe cache get with fallback for shared hosting
 */
private function safeCache($key, $callback, $dataType = 'default')
{
    try {
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        if ($data !== null) {
            Cache::put($key, $data, $this->getCacheTTL($dataType));
        }
        
        return $data;
    } catch (\Exception $e) {
        \Log::warning("Cache operation failed for key: {$key}. Error: " . $e->getMessage());
        // Fallback to direct execution if cache fails
        return $callback();
    }
}

/**
 * Execute database query with timeout protection for shared hosting
 */
private function safeDbQuery($query, $bindings = [], $timeoutSeconds = 30)
{
    try {
        // Set a reasonable timeout for the query
        $startTime = microtime(true);
        
        $result = DB::select($query, $bindings);
        
        $executionTime = microtime(true) - $startTime;
        
        // Log slow queries for optimization
        if ($executionTime > 5) {
            \Log::warning("Slow query detected (${executionTime}s): " . substr($query, 0, 100));
        }
        
        return $result;
        
    } catch (\Exception $e) {
        \Log::error("Database query failed: " . $e->getMessage() . " Query: " . substr($query, 0, 100));
        
        // Return empty result for graceful degradation
        return [];
    }
}

/**
 * Execute stored procedure with error handling for shared hosting
 */
private function safeStoredProcedure($procedureName, $parameters = [])
{
    try {
        $placeholders = str_repeat('?,', count($parameters) - 1) . '?';
        $query = "CALL {$procedureName}({$placeholders})";
        
        return $this->safeDbQuery($query, $parameters);
        
    } catch (\Exception $e) {
        \Log::error("Stored procedure '{$procedureName}' failed: " . $e->getMessage());
        return [];
    }
}
    private function generateChartData($user, $period)
    {
        $startTime = microtime(true);
        
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
            'TTCA-TRAM01' => 'TRẠM 1',
            'TTCA-TRAM02' => 'TRẠM 2',
            'TTCA-TRAM03' => 'TRẠM 3',
            'TTCA-TRAM04' => 'TRẠM 4',
            'TTCA-TRAM05' => 'TRẠM 5',
            'TTCA-TRAM06' => 'TRẠM 6'
        ];

        // Check user role and get data accordingly using optimized procedures
        if (in_array($userPosition, ['department_head', 'office_workers'])) {
            // Use single procedure call to get all stations data at once
            $stationsData = $this->getAllStationsChartData($dateRange, $stationMapping);
        } elseif ($userPosition === 'Station_Chief') {
            // Get only user's station data using optimized procedures
            $stationData = $this->getStationChartDataOptimized($userStation, $dateRange);
            $stationName = $stationMapping[$userStation] ?? 'TRẠM';
            
            $stationsData[] = [
                'name' => $stationName,
                'quantity' => $stationData['quantity'],
                'weeklyAmount' => $stationData['periodAmount'],
                'cumulativeAmount' => $stationData['cumulativeAmount']
            ];
        } elseif ($userPosition === 'Farm_worker') {
            // Get data filtered by ma_nhan_vien using optimized procedures
            $stationData = $this->getFarmWorkerChartDataOptimized($userMaNhanVien, $dateRange);
            $stationsData[] = [
                'name' => 'Dữ liệu của tôi',
                'quantity' => $stationData['quantity'],
                'weeklyAmount' => $stationData['periodAmount'],
                'cumulativeAmount' => $stationData['cumulativeAmount']
            ];
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        
        return [
            'stations' => $stationsData,
            'period' => $period,
            'dateRange' => $dateRange,
            'executionTime' => $executionTime
        ];
    }

    private function getAllStationsChartData($dateRange, $stationMapping)
    {
        try {
            // Single procedure call to get all stations data
            $allData = $this->safeStoredProcedure('get_chart_all_stations_data', [
                $dateRange['start'],
                $dateRange['end']
            ]);
            
            // Process and group data by station
            $stationResults = [];
            
            foreach ($allData as $row) {
                $stationCode = $row->station_code;
                $dataType = $row->data_type;
                
                if (!isset($stationResults[$stationCode])) {
                    $stationResults[$stationCode] = [
                        'service' => [
                            'quantity' => 0,
                            'periodAmount' => 0,
                            'cumulativeAmount' => 0
                        ],
                        'seed' => [
                            'quantity' => 0,
                            'periodAmount' => 0,
                            'cumulativeAmount' => 0
                        ]
                    ];
                }
                
                $stationResults[$stationCode][$dataType] = [
                    'quantity' => $row->period_quantity ?: 0,
                    'periodAmount' => $row->period_amount ?: 0,
                    'cumulativeAmount' => $row->cumulative_amount ?: 0
                ];
            }
            
            // Format for output
            $stationsData = [];
            foreach ($stationMapping as $stationCode => $stationName) {
                $serviceData = $stationResults[$stationCode]['service'] ?? ['quantity' => 0, 'periodAmount' => 0, 'cumulativeAmount' => 0];
                $seedData = $stationResults[$stationCode]['seed'] ?? ['quantity' => 0, 'periodAmount' => 0, 'cumulativeAmount' => 0];
                
                $stationsData[] = [
                    'name' => $stationName,
                    'quantity' => $serviceData['quantity'] + $seedData['quantity'],
                    'weeklyAmount' => $serviceData['periodAmount'] + $seedData['periodAmount'],
                    'cumulativeAmount' => $serviceData['cumulativeAmount'] + $seedData['cumulativeAmount']
                ];
            }
            
            return $stationsData;
            
        } catch (\Exception $e) {
            \Log::error('Error in getAllStationsChartData: ' . $e->getMessage());
            return [];
        }
    }

    private function getStationChartDataOptimized($stationCode, $dateRange)
    {
        try {
            // Get service data using stored procedure
            $serviceResult = $this->safeStoredProcedure('get_chart_service_data', [
                $dateRange['start'],
                $dateRange['end'],
                $stationCode
            ]);
            
            // Get seed data using stored procedure
            $seedResult = $this->safeStoredProcedure('get_chart_seed_data', [
                $dateRange['start'],
                $dateRange['end'],
                $stationCode
            ]);
            
            $serviceData = !empty($serviceResult) ? $serviceResult[0] : (object)[
                'period_quantity' => 0,
                'period_amount' => 0,
                'cumulative_amount' => 0
            ];
            
            $seedData = !empty($seedResult) ? $seedResult[0] : (object)[
                'period_quantity' => 0,
                'period_amount' => 0,
                'cumulative_amount' => 0
            ];
            
            return [
                'quantity' => ($serviceData->period_quantity ?: 0) + ($seedData->period_quantity ?: 0),
                'periodAmount' => ($serviceData->period_amount ?: 0) + ($seedData->period_amount ?: 0),
                'cumulativeAmount' => ($serviceData->cumulative_amount ?: 0) + ($seedData->cumulative_amount ?: 0)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getStationChartDataOptimized: ' . $e->getMessage());
            return [
                'quantity' => 0,
                'periodAmount' => 0,
                'cumulativeAmount' => 0
            ];
        }
    }

    private function getFarmWorkerChartDataOptimized($maNhanVien, $dateRange)
    {
        try {
            // Get service data using stored procedure
            $serviceResult = $this->safeStoredProcedure('get_chart_farm_worker_service_data', [
                $dateRange['start'],
                $dateRange['end'],
                $maNhanVien
            ]);
            
            // Get seed data using stored procedure
            $seedResult = $this->safeStoredProcedure('get_chart_farm_worker_seed_data', [
                $dateRange['start'],
                $dateRange['end'],
                $maNhanVien
            ]);
            
            $serviceData = !empty($serviceResult) ? $serviceResult[0] : (object)[
                'period_quantity' => 0,
                'period_amount' => 0,
                'cumulative_amount' => 0
            ];
            
            $seedData = !empty($seedResult) ? $seedResult[0] : (object)[
                'period_quantity' => 0,
                'period_amount' => 0,
                'cumulative_amount' => 0
            ];
            
            return [
                'quantity' => ($serviceData->period_quantity ?: 0) + ($seedData->period_quantity ?: 0),
                'periodAmount' => ($serviceData->period_amount ?: 0) + ($seedData->period_amount ?: 0),
                'cumulativeAmount' => ($serviceData->cumulative_amount ?: 0) + ($seedData->cumulative_amount ?: 0)
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in getFarmWorkerChartDataOptimized: ' . $e->getMessage());
            return [
                'quantity' => 0,
                'periodAmount' => 0,
                'cumulativeAmount' => 0
            ];
        }
    }
}