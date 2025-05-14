<?php

namespace App\Models\QuanlyCongno;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductibleServiceDebt extends Model
{
    use HasFactory;

    /**
     * ชื่อตารางที่เชื่อมต่อกับโมเดล
     *
     * @var string
     */
    protected $table = 'deductible_service_debt';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key for the model.
     * แม้ว่าตารางจะไม่มี primary key แต่เราต้องระบุเพื่อให้ Laravel ทำงานได้ถูกต้อง
     * 
     * @var string
     */
    protected $primaryKey = 'invoicenumber';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ngay_phat_sinh' => 'date',
        'ty_gia_quy_doi' => 'float',
        'so_tien_theo_gia_tri_dau_tu' => 'float',
        'so_tien_no_goc_da_quy' => 'float',
        'da_tra_goc' => 'float',
        'so_tien_con_lai' => 'float',
        'tien_lai' => 'float',
        'lai_suat' => 'float',
    ];
}