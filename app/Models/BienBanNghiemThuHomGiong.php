<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienBanNghiemThuHomGiong extends Model
{
    protected $table = 'bien_ban_nghiem_thu_hom_giong';
    
    protected $fillable = [
        'tram',
        'ma_so_phieu',
        'can_bo_nong_vu',
        'ten_phieu',
        'hop_dong_dau_tu_mia',
        'hop_dong_dau_tu_mia_ben_giao_hom',
        'ma_hop_dong',
        'khach_hang_ca_nhan',
        'ma_khach_hang_CN',
        'khach_hang_doanh_nghiep',
        'ma_khach_hang_DN',
        'doi_tac_giao_hom',
        'ma_khach_hang_giao_hom',
        'doi_tac_giao_hom_DN',
        'ma_khach_hang_giao_hom_DN',
        'tong_thuc_nhan',
        'tong_so_tien_HL',
        'tong_tien_hom',
        'tong_tien',
        'tong_tien_boc_xep',
        'tong_tien_cong_don',
        'tong_tien_van_chuyen',
        'vu_dau_tu',
        'tinh_trang_duyet'
    ];

    // Add scope for searching
    public function scopeSearch($query, $investment_project)
    {
        return $query->when($investment_project, function($q) use ($investment_project) {
            return $q->where('vu_dau_tu', $investment_project);
        });
    }

    // Add relationship with document mapping
    public function documentMappings()
    {
        return $this->hasMany(DocumentMappingHomGiong::class, 'ma_so_phieu', 'ma_so_phieu');
    }
}