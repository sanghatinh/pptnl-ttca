<?php

namespace App\Models\QuanlyHS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChitietgiaonhanHomgiong extends Model
{
    use HasFactory;

    protected $table = 'tb_chitiet_giaonhan_homgiong';
    
    protected $primaryKey = 'id';
    
    public $timestamps = false; // Vì bảng không có created_at, updated_at
    
    protected $fillable = [
        'tram',
        'ma_so_phieu',
        'tieu_de',
        'doi_tac_giao_hom_kh',
        'doi_tac_giao_hom_khdn',
        'khach_hang_ca_nhan',
        'khach_hang_doanh_nghiep',
        'phieu_gn_hom_giong',
        'chitiet_hd_dt_mia',
        'hang_muc_cong_viec',
        'giong_mia',
        'tinh_trang_duyet',
        'so_luong_dk',
        'ngay_nhan',
        'thuc_nhan',
        'don_gia',
        'thanh_tien_hom_giong',
        'hop_dong_thu_hoach',
        'don_gia_don_chat',
        'thanh_tien_don_chat',
        'hop_dong_boc_xep',
        'don_gia_boc_xep',
        'thanh_tien_boc_xep',
        'hop_dong_van_chuyen',
        'don_gia_van_chuyen',
        'thanh_tien_van_chuyen',
        'don_vi_tinh',
        'so_tien_dau_tu_hl',
        'so_tien_dau_tu_khl',
        'doi_tac_thu_hoach_kh',
        'doi_tac_thu_hoach_khdn',
        'doi_tac_van_chuyen_kh',
        'doi_tac_van_chuyen_khdn',
        'doi_tac_boc_xep',
        'can_bo_nong_vu',
        'chitiet_hd_dt_mia_doitac',
        'vu_dau_tu',
        'thua_dat',
    ];

    protected $casts = [
        'so_luong_dk' => 'decimal:2',
        'thuc_nhan' => 'decimal:2',
        'don_gia' => 'decimal:2',
        'thanh_tien_hom_giong' => 'decimal:2',
        'don_gia_don_chat' => 'decimal:2',
        'thanh_tien_don_chat' => 'decimal:2',
        'don_gia_boc_xep' => 'decimal:2',
        'thanh_tien_boc_xep' => 'decimal:2',
        'don_gia_van_chuyen' => 'decimal:2',
        'thanh_tien_van_chuyen' => 'decimal:2',
        'so_tien_dau_tu_hl' => 'decimal:2',
        'so_tien_dau_tu_khl' => 'decimal:2',
        'ngay_nhan' => 'date',
    ];

    protected $attributes = [
        'tinh_trang_duyet' => 'Chưa duyệt',
        'don_vi_tinh' => 'tấn',
    ];

    // Scope methods
    public function scopeByTram($query, $tram)
    {
        return $query->where('tram', $tram);
    }

    public function scopeByTinhTrangDuyet($query, $tinhTrang)
    {
        return $query->where('tinh_trang_duyet', $tinhTrang);
    }

    public function scopeByNgayNhan($query, $tuNgay, $denNgay = null)
    {
        if ($denNgay) {
            return $query->whereBetween('ngay_nhan', [$tuNgay, $denNgay]);
        }
        return $query->whereDate('ngay_nhan', $tuNgay);
    }

    // Accessors
    public function getTongThanhTienAttribute()
    {
        return $this->thanh_tien_hom_giong + 
               $this->thanh_tien_don_chat + 
               $this->thanh_tien_boc_xep + 
               $this->thanh_tien_van_chuyen;
    }

    public function getTinhTrangDuyetBadgeAttribute()
    {
        $badges = [
            'Đã duyệt' => 'success',
            'Chưa duyệt' => 'warning',
            'Từ chối' => 'danger',
        ];
        
        return $badges[$this->tinh_trang_duyet] ?? 'secondary';
    }

    // Mutators
    public function setMaSoPhieuAttribute($value)
    {
        $this->attributes['ma_so_phieu'] = strtoupper($value);
    }

    // Static methods
    public static function getNextMaSoPhieu($prefix = 'GNHG')
    {
        $lastRecord = static::where('ma_so_phieu', 'like', $prefix . '%')
                           ->orderBy('ma_so_phieu', 'desc')
                           ->first();
        
        if (!$lastRecord) {
            return $prefix . '0001';
        }
        
        $lastNumber = intval(substr($lastRecord->ma_so_phieu, strlen($prefix)));
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'tram' => 'required|string|max:100',
            'ma_so_phieu' => 'required|string|max:100|unique:tb_chitiet_giaonhan_homgiong,ma_so_phieu,' . $id,
            'tieu_de' => 'required|string|max:255',
            'ngay_nhan' => 'required|date',
            'so_luong_dk' => 'required|numeric|min:0',
            'don_gia' => 'required|numeric|min:0',
            'tinh_trang_duyet' => 'required|in:Chưa duyệt,Đã duyệt,Từ chối',
        ];
    }
}