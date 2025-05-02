<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phieuthunodichvu extends Model
{
    use HasFactory;

    protected $table = 'Logs_Phieu_Tinh_Lai_dv';
    protected $primaryKey = 'Ma_So_Phieu_PDN_Thu_No';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'Ma_So_Phieu_PDN_Thu_No',
        'Ma_So_Phieu_Phan_Bo_Dau_Tu',
        'Phan_Bo_Dau_Tu',
        'So_Phieu_Phan_Bo_Dau_Tu',
        'Invoice_Number_Phan_Bo_Dau_Tu',
        'Da_Tra_Goc',
        'Ngay_Vay',
        'Ngay_Tra',
        'Lai_Suat_Phan_Bo_Dau_Tu',
        'Tien_Lai',
        'Tinh_Trang_PDN_Thu_No',
        'Tinh_Trang_Duyet_PDN_Thu_No',
        'Da_Ho_Tro_Lai',
        'Phieu_Tinh_Tien_Mia_PDN_Thu_No',
        'Created_On',
        'Vu_Thanh_Toan_Phan_Bo_Dau_Tu',
        'Khach_Hang_PDN_Thu_No',
        'Khach_Hang_Ca_Nhan_PDN_Thu_No',
        'Khach_Hang_Doanh_Nghiep_PDN_Thu_No',
        'Xoa_No_Phan_Bo_Dau_Tu',
        'Vu_Dau_Tu_Phan_Bo_Dau_Tu',
        'Owner_PDN_Thu_No',
        'So_Tro_Trinh',
        'Category_Debt',
        'Description',
        'Ma_Khach_Hang_Ca_Nhan',
        'Ma_Khach_Hang_Doanh_Nghiep'
    ];

    protected $casts = [
        'Da_Tra_Goc' => 'decimal:2',
        'Lai_Suat_Phan_Bo_Dau_Tu' => 'decimal:2',
        'Tien_Lai' => 'decimal:2',
        'Xoa_No_Phan_Bo_Dau_Tu' => 'decimal:2',
        'Ngay_Vay' => 'date',
        'Ngay_Tra' => 'date',
        'Created_On' => 'datetime'
    ];
}