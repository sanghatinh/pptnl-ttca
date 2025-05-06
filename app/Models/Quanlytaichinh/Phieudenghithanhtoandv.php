<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phieudenghithanhtoandv extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tb_de_nghi_thanhtoan_dv';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ma_giai_ngan';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'ma_giai_ngan',
        'vu_dau_tu',
        'loai_thanh_toan',
        'khach_hang_ca_nhan',
        'ma_khach_hang_ca_nhan',
        'khach_hang_doanh_nghiep',
        'ma_khach_hang_doanh_nghiep',
        'ma_trinh_thanh_toan',
       
        'tong_tien',
        'tong_tien_tam_giu',
        'tong_tien_khau_tru',
        'tong_tien_lai_suat',
        'tong_tien_thanh_toan_con_lai',
        'ngay_thanh_toan'



    ];

    /**
     * Get the payment request (phieu trinh thanh toan) associated with this request
     */
    public function paymentRequest()
    {
        return $this->belongsTo('App\Models\QuanlyHS\PaymentRequest', 'ma_trinh_thanh_toan', 'ma_trinh_thanh_toan');
    }

    /**
     * Get the logs associated with this payment request
     */
    public function logs()
    {
        return $this->hasMany('App\Models\QuanlyHS\PaymentRequestLog', 'ma_de_nghi_giai_ngan', 'ma_giai_ngan');
    }

    /**
     * Get the action history associated with this payment request
     */
    public function actions()
    {
        return $this->hasMany('App\Models\QuanlyHS\PaymentRequestAction', 'ma_trinh_thanh_toan', 'ma_trinh_thanh_toan');
    }
}