<?php

namespace App\Models\QuanlyHS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChitietNghiemthudv extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tb_chitiet_dichvu_nt';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tram',
        'ma_nghiem_thu',
        'chi_tiet_hd_mia',
        'khach_hang_doanh_nghiep',
        'khach_hang_ca_nhan',
        'doi_tac_ca_nhan_dv',
        'doi_tac_cung_cap_dv',
        'ma_so_thua',
        'hinh_thuc_thuc_hien_dv',
        'dich_vu',
        'nghiem_thu_dich_vu',
        'don_vi_tinh',
        'khoi_luong_thuc_hien',
        'don_gia',
        'thanh_tien',
        'tien_thanh_toan',
        'tien_con_lai',
        'so_lan_thuc_hien',
        'da_thanh_toan',
        'vu_dau_tu',
        'tinh_trang_duyet',
        'can_bo_nong_vu',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'khoi_luong_thuc_hien' => 'decimal:2',
        'don_gia' => 'decimal:2',
        'thanh_tien' => 'decimal:2',
        'tien_thanh_toan' => 'decimal:2',
        'tien_con_lai' => 'decimal:2',
        'so_lan_thuc_hien' => 'integer',
    ];

    /**
     * Get the acceptance document associated with the detail.
     */
    public function nghiemThu()
    {
        // Assuming you have a NghiemThu model that links via ma_nghiem_thu
        return $this->belongsTo(NghiemThu::class, 'ma_nghiem_thu', 'ma_nghiem_thu');
    }
}