<?php


namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardReportControllers extends Controller
{
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
        // Period data (for week/month/year filter)
        $periodQuery = DB::table('tb_bien_ban_nghiemthu_dv as bbnt')
            ->join('Logs_phieu_trinh_thanh_toan as log', 'bbnt.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
            ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbnt.tram', $stationCode)  // ใช้ station code แทน number
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
            ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

        $periodQuantity = $periodQuery->count();
        $periodAmount = $periodQuery->sum('bbnt.tong_tien');

        // Cumulative data (all time)
        $cumulativeQuery = DB::table('tb_bien_ban_nghiemthu_dv as bbnt')
            ->join('Logs_phieu_trinh_thanh_toan as log', 'bbnt.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
            ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbnt.tram', $stationCode)  // ใช้ station code แทน number
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

        $cumulativeAmount = $cumulativeQuery->sum('bbnt.tong_tien');

        return [
            'quantity' => $periodQuantity,
            'periodAmount' => $periodAmount ?: 0,
            'cumulativeAmount' => $cumulativeAmount ?: 0
        ];
    }

    private function getSeedData($stationCode, $period, $dateRange)
    {
        // Period data (for week/month/year filter)
        $periodQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
            ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bbhg.ma_so_phieu', '=', 'log.ma_so_phieu')
            ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbhg.tram', $stationCode)  // ใช้ station code แทน number
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
            ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

        $periodQuantity = $periodQuery->count();
        $periodAmount = $periodQuery->sum('bbhg.tong_tien');

        // Cumulative data (all time)
        $cumulativeQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
            ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bbhg.ma_so_phieu', '=', 'log.ma_so_phieu')
            ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbhg.tram', $stationCode)  // ใช้ station code แทน number
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

        $cumulativeAmount = $cumulativeQuery->sum('bbhg.tong_tien');

        return [
            'quantity' => $periodQuantity,
            'periodAmount' => $periodAmount ?: 0,
            'cumulativeAmount' => $cumulativeAmount ?: 0
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
        // Period data
        $periodQuery = DB::table('tb_bien_ban_nghiemthu_dv as bbnt')
            ->join('Logs_phieu_trinh_thanh_toan as log', 'bbnt.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
            ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbnt.ma_nhan_vien', $maNhanVien)
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
            ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

        $periodQuantity = $periodQuery->count();
        $periodAmount = $periodQuery->sum('bbnt.tong_tien');

        // Cumulative data
        $cumulativeQuery = DB::table('tb_bien_ban_nghiemthu_dv as bbnt')
            ->join('Logs_phieu_trinh_thanh_toan as log', 'bbnt.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
            ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbnt.ma_nhan_vien', $maNhanVien)
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

        $cumulativeAmount = $cumulativeQuery->sum('bbnt.tong_tien');

        return [
            'quantity' => $periodQuantity,
            'periodAmount' => $periodAmount ?: 0,
            'cumulativeAmount' => $cumulativeAmount ?: 0
        ];
    }

    private function getFarmWorkerSeedData($maNhanVien, $period, $dateRange)
    {
        // Period data
        $periodQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
            ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bbhg.ma_so_phieu', '=', 'log.ma_so_phieu')
            ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbhg.ma_nhan_vien', $maNhanVien)
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
            ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

        $periodQuantity = $periodQuery->count();
        $periodAmount = $periodQuery->sum('bbhg.tong_tien');

        // Cumulative data
        $cumulativeQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bbhg')
            ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bbhg.ma_so_phieu', '=', 'log.ma_so_phieu')
            ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('bbhg.ma_nhan_vien', $maNhanVien)
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

        $cumulativeAmount = $cumulativeQuery->sum('bbhg.tong_tien');

        return [
            'quantity' => $periodQuantity,
            'periodAmount' => $periodAmount ?: 0,
            'cumulativeAmount' => $cumulativeAmount ?: 0
        ];
    }
// Get report table section data
// This method fetches detailed job categories data for the report table section

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
        $stationFilter = $request->input('station', null); // เพิ่ม station filter
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($period);
        
        // Get user position and details
        $userPosition = $user->position;
        $userStation = $user->station;
        $userMaNhanVien = $user->ma_nhan_vien;

        // Get job categories data based on user role
        $jobCategories = [];
        
        if (in_array($userPosition, ['department_head', 'office_workers'])) {
            // Get all data, but apply station filter if provided
            $effectiveStation = $stationFilter; // ใช้ station filter ถ้ามี
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, $effectiveStation);
        } elseif ($userPosition === 'Station_Chief') {
            // Filter by user's station (ignore station filter for Station_Chief)
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, $userStation);
        } elseif ($userPosition === 'Farm_worker') {
            // Filter by user's ma_nhan_vien (ignore station filter for Farm_worker)
            $jobCategories = $this->getJobCategoriesData($period, $dateRange, null, $userMaNhanVien);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'jobCategories' => $jobCategories,
                'period' => $period,
                'dateRange' => $dateRange,
                'userPosition' => $userPosition,
                'stationFilter' => $stationFilter, // ส่งกลับ station filter ที่ใช้
                'availableStations' => $this->getAvailableStations($userPosition, $userStation) // เพิ่มรายการ stations ที่ใช้ได้
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Failed to fetch table data: ' . $e->getMessage()
        ], 500);
    }
}

// เพิ่ม method ใหม่สำหรับดึงรายการ stations ที่ใช้ได้
private function getAvailableStations($userPosition, $userStation)
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
    // Base query for service data
    $query = DB::table('tb_chitiet_dichvu_nt as ct')
        ->join('tb_bien_ban_nghiemthu_dv as bb', 'ct.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
        ->join('Logs_phieu_trinh_thanh_toan as log', 'bb.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
        ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
        ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
        ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

    // Add filters based on user role
    if ($station) {
        $query->where('bb.tram', $station);
    }
    if ($maNhanVien) {
        $query->where('bb.ma_nhan_vien', $maNhanVien);
    }

    // Get unique service names with aggregated data
    $services = $query->select(
            'ct.dich_vu',
            'ct.don_vi_tinh',
            DB::raw('SUM(ct.khoi_luong_thuc_hien) as total_quantity'),
            DB::raw('SUM(ct.thanh_tien) as period_amount')
        )
        ->groupBy('ct.dich_vu', 'ct.don_vi_tinh')
        ->get();

    $jobCategories = [];
    foreach ($services as $service) {
        // Get cumulative data (all time) for this service
        $cumulativeQuery = DB::table('tb_chitiet_dichvu_nt as ct')
            ->join('tb_bien_ban_nghiemthu_dv as bb', 'ct.ma_nghiem_thu', '=', 'bb.ma_nghiem_thu')
            ->join('Logs_phieu_trinh_thanh_toan as log', 'bb.ma_nghiem_thu', '=', 'log.ma_nghiem_thu')
            ->join('tb_phieu_trinh_thanh_toan as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
            ->where('ct.dich_vu', $service->dich_vu)
            ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

        // Add filters for cumulative data
        if ($station) {
            $cumulativeQuery->where('bb.tram', $station);
        }
        if ($maNhanVien) {
            $cumulativeQuery->where('bb.ma_nhan_vien', $maNhanVien);
        }

        $cumulativeAmount = $cumulativeQuery->sum('ct.thanh_tien');

        $jobCategories[] = [
            'name' => $service->dich_vu,
            'quantity' => $service->total_quantity ?: 0,
            'unit' => $service->don_vi_tinh ?: 'Đơn vị',
            'weeklyAmount' => $service->period_amount ?: 0,
            'cumulativeAmount' => $cumulativeAmount ?: 0
        ];
    }

    return $jobCategories;
}

private function getHomGiongJobCategory($period, $dateRange, $station = null, $maNhanVien = null)
{
    // Period data query for Hom Giong
    $periodQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bb')
        ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bb.ma_so_phieu', '=', 'log.ma_so_phieu')
        ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
        ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid'])
        ->whereBetween('pttt.ngay_tao', [$dateRange['start'], $dateRange['end']]);

    // Add filters based on user role
    if ($station) {
        $periodQuery->where('bb.tram', $station);
    }
    if ($maNhanVien) {
        $periodQuery->where('bb.ma_nhan_vien', $maNhanVien);
    }

    $periodQuantity = $periodQuery->sum('bb.tong_thuc_nhan');
    $periodAmount = $periodQuery->sum('bb.tong_tien');

    // Cumulative data query for Hom Giong
    $cumulativeQuery = DB::table('bien_ban_nghiem_thu_hom_giong as bb')
        ->join('logs_phieu_trinh_thanh_toan_homgiong as log', 'bb.ma_so_phieu', '=', 'log.ma_so_phieu')
        ->join('tb_phieu_trinh_thanh_toan_homgiong as pttt', 'log.ma_trinh_thanh_toan', '=', 'pttt.ma_trinh_thanh_toan')
        ->whereIn('pttt.trang_thai_thanh_toan', ['processing', 'submitted', 'paid']);

    // Add filters for cumulative data
    if ($station) {
        $cumulativeQuery->where('bb.tram', $station);
    }
    if ($maNhanVien) {
        $cumulativeQuery->where('bb.ma_nhan_vien', $maNhanVien);
    }

    $cumulativeAmount = $cumulativeQuery->sum('bb.tong_tien');

    return [
        'name' => 'Hom giống',
        'quantity' => $periodQuantity ?: 0,
        'unit' => 'Tấn',
        'weeklyAmount' => $periodAmount ?: 0,
        'cumulativeAmount' => $cumulativeAmount ?: 0
    ];
}


}