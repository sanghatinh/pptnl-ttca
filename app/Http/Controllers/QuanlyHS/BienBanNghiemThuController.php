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
        $query = DB::table('tb_bien_ban_nghiemthu_dv as bb')
            ->select(
                'bb.*',
                'u_creator.full_name as nguoi_giao',
                'u_receiver.full_name as nguoi_nhan',
                'dd.received_date as ngay_nhan',
                'dd.status as trang_thai_nhan_hs'
            )
            ->leftJoin('document_mapping as dm', 'bb.ma_nghiem_thu', '=', 'dm.ma_nghiem_thu_bb')
            ->leftJoin('document_delivery as dd', 'dm.document_code', '=', 'dd.document_code')
            ->leftJoin('users as u_creator', 'dd.creator_id', '=', 'u_creator.id')
            ->leftJoin('users as u_receiver', 'dd.receiver_id', '=', 'u_receiver.id');
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bb.ma_nghiem_thu', 'LIKE', "%{$search}%")
                  ->orWhere('bb.tieu_de', 'LIKE', "%{$search}%")
                  ->orWhere('bb.hop_dong_dau_tu_mia', 'LIKE', "%{$search}%")
                  ->orWhere('bb.tram', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status) {
            if ($request->status === 'approved') {
                $query->where('bb.tinh_trang_duyet', 1);
            } elseif ($request->status === 'pending') {
                $query->where('bb.tinh_trang_duyet', 0);
            } elseif ($request->status === 'rejected') {
                $query->where('bb.tinh_trang_duyet', 2);
            }
        }
        
        // Apply investment project filter
        if ($request->has('investment_project') && $request->investment_project) {
            $query->where('bb.vu_dau_tu', $request->investment_project);
        }
        
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $bienBanList = $query->paginate($perPage);
        
        return response()->json($bienBanList);
    }
}