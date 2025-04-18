<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienBanNghiemThu extends Model
{
    //
    protected $table = 'tb_bien_ban_nghiemthu_dv';
    
    protected $fillable = [
        'ma_nghiem_thu',
        'tram',
        'vu_dau_tu',
        'tieu_de',
        'khach_hang_ca_nhan_dt_mia',
        'khach_hang_doanh_nghiep_dt_mia',
        'hop_dong_dau_tu_mia',
        'hinh_thuc_thuc_hien_dv',
        'hop_dong_cung_ung_dich_vu',
        'tong_tien',
        'tong_tien_dich_vu',
        'tong_tien_tam_giu',
        'tong_tien_thanh_toan',
        'can_bo_nong_vu',
        'tinh_trang',
        'tinh_trang_duyet',
        'ma_nhan_vien',
    ];

    // เพิ่ม scope สำหรับการค้นหา
    public function scopeSearch($query, $search, $investment_project, $station)
    {
        return $query->when($search, function($q) use ($search) {
                    return $q->where('ma_nghiem_thu', '=', $search);
                })
                ->when($investment_project, function($q) use ($investment_project) {
                    return $q->where('vu_dau_tu', '=', $investment_project);
                })
                ->when($station, function($q) use ($station) {
                    return $q->where('tram', '=', $station);
                });
    }


}
