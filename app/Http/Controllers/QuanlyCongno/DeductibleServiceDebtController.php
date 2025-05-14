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
            $query = DeductibleServiceDebt::query();
            
            // Apply search filters if provided
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
            
            // Apply date filters
            if ($request->has('from_date') && $request->from_date) {
                $query->where('ngay_phat_sinh', '>=', $request->from_date);
            }
            
            if ($request->has('to_date') && $request->to_date) {
                $query->where('ngay_phat_sinh', '<=', $request->to_date);
            }
            
            // Apply column filters
            $columnFilters = $request->only([
                'tram', 'invoicenumber', 'vu_dau_tu', 'category_debt', 
                'description', 'ngay_phat_sinh', 'loai_tien', 'ty_gia_quy_doi',
                'so_tien_theo_gia_tri_dau_tu', 'so_tien_no_goc_da_quy', 'da_tra_goc',
                'so_tien_con_lai', 'tien_lai', 'ma_khach_hang_ca_nhan', 'khach_hang_ca_nhan',
                'ma_khach_hang_doanh_nghiep', 'khach_hang_doanh_nghiep', 'lai_suat',
                'loai_lai_suat', 'vu_thanh_toan', 'loai_dau_tu'
            ]);
            
            foreach ($columnFilters as $column => $value) {
                if (!empty($value)) {
                    // Handle array values for multiple selections
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    } else {
                        // Handle date field differently
                        if ($column === 'ngay_phat_sinh') {
                            $query->whereDate($column, $value);
                        } 
                        // Handle numeric fields for exact or partial matches
                        elseif (in_array($column, ['ty_gia_quy_doi', 'so_tien_theo_gia_tri_dau_tu', 
                                                  'so_tien_no_goc_da_quy', 'da_tra_goc', 
                                                  'so_tien_con_lai', 'tien_lai', 'lai_suat'])) {
                            $query->where($column, 'like', "%{$value}%");
                        }
                        // Text fields
                        else {
                            $query->where($column, 'like', "%{$value}%");
                        }
                    }
                }
            }
            
            // Apply sorting
            $sortField = $request->input('sort_by', 'ngay_phat_sinh');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);
            
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
        } catch (\Exception $e) {
            Log::error('Error fetching deductible service debts: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}