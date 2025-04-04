<?php

namespace App\Http\Controllers\QuanlyHS;

use Illuminate\Http\Request;
use App\Models\BienBanNghiemThu;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller; // Add this import

class BienBanNghiemThuController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tb_bien_ban_nghiemthu_dv');
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ma_nghiem_thu', 'LIKE', "%{$search}%")
                  ->orWhere('tieu_de', 'LIKE', "%{$search}%")
                  ->orWhere('hop_dong_dau_tu_mia', 'LIKE', "%{$search}%")
                  ->orWhere('tram', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status) {
            if ($request->status === 'approved') {
                $query->where('tinh_trang_duyet', 1);
            } elseif ($request->status === 'pending') {
                $query->where('tinh_trang_duyet', 0);
            } elseif ($request->status === 'rejected') {
                $query->where('tinh_trang_duyet', 2);
            }
        }
        
        // Apply investment project filter
        if ($request->has('investment_project') && $request->investment_project) {
            $query->where('vu_dau_tu', $request->investment_project);
        }
        
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $bienBanList = $query->paginate($perPage);
        
        return response()->json($bienBanList);
    }
}