<?php

namespace App\Http\Controllers\QuanlyCongno;

use App\Http\Controllers\Controller;
use App\Models\QuanlyCongno\DeductibleServiceDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeductibleServiceDebtController extends Controller
{
    /**
     * Display a listing of the deductible service debts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    try {
        // Check if client requests all data (for client-side processing)
        if ($request->has('all') && $request->all) {
            // Get all data without pagination
            $query = DeductibleServiceDebt::query();
            
            // Apply basic filters if provided (optional for security/performance)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('invoicenumber', 'like', "%{$search}%")
                      ->orWhere('vu_dau_tu', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('tram', 'like', "%{$search}%")
                      ->orWhere('khach_hang_ca_nhan', 'like', "%{$search}%")
                      ->orWhere('khach_hang_doanh_nghiep', 'like', "%{$search}%");
                });
            }
            
            // Apply sorting
            $sortField = $request->input('sort_by', 'ngay_phat_sinh');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);
            
            // Limit data size if needed (optional for performance)
            // $query->limit(5000); // Uncomment if you want to set a maximum limit
            
            $allData = $query->get();

            // Get unique values for filters
            $uniqueFilters = [
                'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
                'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
                'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
                'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
                'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
                'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
                'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
            ];
            
            // Calculate overall totals
            $totals = [
                'total_debt' => $allData->sum('so_tien_no_goc_da_quy'),
                'total_paid' => $allData->sum('da_tra_goc'),
                'total_remaining' => $allData->sum('so_tien_con_lai'),
                'total_interest' => $allData->sum('tien_lai')
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'All data retrieved successfully',
                'data' => $allData,
                'unique_filters' => $uniqueFilters,
                'totals' => $totals
            ]);
        } else {
            // Original paginated code for server-side processing
            $query = DeductibleServiceDebt::query();
            
            // [Existing filter code remains the same]

            // Paginate the results
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            
            $debts = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Get unique values for filters
            $uniqueFilters = [
                'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
                'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
                'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
                'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
                'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
                'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
                'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
            ];
            
            // Calculate totals
            $totals = [
                'total_debt' => $query->sum('so_tien_no_goc_da_quy'),
                'total_paid' => $query->sum('da_tra_goc'),
                'total_remaining' => $query->sum('so_tien_con_lai'),
                'total_interest' => $query->sum('tien_lai')
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'data' => $debts,
                'unique_filters' => $uniqueFilters,
                'totals' => $totals
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Error fetching deductible service debts: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve data: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

private function getUniqueValues()
{
    // ใช้ Cache เพื่อไม่ต้อง query ใหม่ทุกครั้ง
    return \Cache::remember('deductible_service_unique_values', 3600, function() {
        return [
            'tram' => DeductibleServiceDebt::distinct()->pluck('tram')->filter()->values(),
            'vu_dau_tu' => DeductibleServiceDebt::distinct()->pluck('vu_dau_tu')->filter()->values(),
            'category_debt' => DeductibleServiceDebt::distinct()->pluck('category_debt')->filter()->values(),
            'loai_tien' => DeductibleServiceDebt::distinct()->pluck('loai_tien')->filter()->values(),
            'loai_lai_suat' => DeductibleServiceDebt::distinct()->pluck('loai_lai_suat')->filter()->values(),
            'vu_thanh_toan' => DeductibleServiceDebt::distinct()->pluck('vu_thanh_toan')->filter()->values(),
            'loai_dau_tu' => DeductibleServiceDebt::distinct()->pluck('loai_dau_tu')->filter()->values(),
        ];
    });
}

}