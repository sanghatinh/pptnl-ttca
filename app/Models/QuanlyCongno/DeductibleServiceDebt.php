<?php

namespace App\Models\QuanlyCongno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductibleServiceDebt extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deductible_service_debt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoicenumber',
        'vu_dau_tu',
        'category_debt',
        'ngay_phat_sinh',
        'loai_tien',
        'ty_gia_quy_doi',
        'so_tien_theo_gia_tri_dau_tu',
        'so_tien_no_goc_da_quy',
        'da_tra_goc',
        'so_tien_con_lai',
        'tien_lai',
        'ma_khach_hang_ca_nhan',
        'khach_hang_ca_nhan',
        'ma_khach_hang_doanh_nghiep',
        'khach_hang_doanh_nghiep',
        'lai_suat',
        'loai_lai_suat',
        'vu_thanh_toan',
        'loai_dau_tu',
        'tram',
        'ma_nhan_vien',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ngay_phat_sinh' => 'date',
        'ty_gia_quy_doi' => 'decimal:4',
        'so_tien_theo_gia_tri_dau_tu' => 'decimal:2',
        'so_tien_no_goc_da_quy' => 'decimal:2',
        'da_tra_goc' => 'decimal:2',
        'so_tien_con_lai' => 'decimal:2',
        'tien_lai' => 'decimal:2',
        'lai_suat' => 'decimal:2'
    ];
}