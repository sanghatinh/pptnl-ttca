<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieudenghithanhtoanHomgiong extends Model
{
    use HasFactory;

    protected $table = 'tb_de_nghi_thanhtoan_homgiong';
    public $timestamps = false;

    protected $fillable = [
        'ma_giai_ngan',
        'vu_dau_tu',
        'loai_thanh_toan',
        'ma_trinh_thanh_toan',
        'khach_hang_ca_nhan',
        'ma_khach_hang_ca_nhan',
        'khach_hang_doanh_nghiep',
        'ma_khach_hang_doanh_nghiep',
        'thuc_nhan', // เพิ่ม column thuc_nhan
        'tong_tien',
        'tong_tien_tam_giu',
        'tong_tien_khau_tru',
        'tong_tien_lai_suat',
        'tong_tien_thanh_toan_con_lai',
        'ngay_thanh_toan',
        'trang_thai_thanh_toan',
        'comment',
    ];

    protected $casts = [
        'thuc_nhan' => 'decimal:2', // เพิ่ม cast สำหรับ thuc_nhan
        'tong_tien' => 'decimal:2',
        'tong_tien_tam_giu' => 'decimal:2',
        'tong_tien_khau_tru' => 'decimal:2',
        'tong_tien_lai_suat' => 'decimal:2',
        'tong_tien_thanh_toan_con_lai' => 'decimal:2',
        'ngay_thanh_toan' => 'date',
    ];

    // Accessor để format tiền tệ
    public function getFormattedThucNhanAttribute()
    {
        return number_format($this->thuc_nhan, 0, '.', ',') . ' VNĐ';
    }

    public function getFormattedTongTienAttribute()
    {
        return number_format($this->tong_tien, 0, '.', ',') . ' VNĐ';
    }

    public function getFormattedTongTienTamGiuAttribute()
    {
        return number_format($this->tong_tien_tam_giu, 0, '.', ',') . ' VNĐ';
    }

    public function getFormattedTongTienKhauTruAttribute()
    {
        return number_format($this->tong_tien_khau_tru, 0, '.', ',') . ' VNĐ';
    }

    public function getFormattedTongTienLaiSuatAttribute()
    {
        return number_format($this->tong_tien_lai_suat, 0, '.', ',') . ' VNĐ';
    }

    public function getFormattedTongTienThanhToanConLaiAttribute()
    {
        return number_format($this->tong_tien_thanh_toan_con_lai, 0, '.', ',') . ' VNĐ';
    }

    // Scope methods
    public function scopeByTrangThai($query, $trangThai)
    {
        return $query->where('trang_thai_thanh_toan', $trangThai);
    }

    public function scopeByLoaiThanhToan($query, $loai)
    {
        return $query->where('loai_thanh_toan', $loai);
    }

    public function scopeByNgayThanhToan($query, $tuNgay, $denNgay)
    {
        return $query->whereBetween('ngay_thanh_toan', [$tuNgay, $denNgay]);
    }

    // Validation rules
    public static function rules()
    {
        return [
            'ma_giai_ngan' => 'required|string|max:255',
            'vu_dau_tu' => 'required|string|max:255',
            'loai_thanh_toan' => 'required|string|max:255',
            'ma_trinh_thanh_toan' => 'required|string|max:255',
            'khach_hang_ca_nhan' => 'nullable|string|max:255',
            'ma_khach_hang_ca_nhan' => 'nullable|string|max:255',
            'khach_hang_doanh_nghiep' => 'nullable|string|max:255',
            'ma_khach_hang_doanh_nghiep' => 'nullable|string|max:255',
            'thuc_nhan' => 'nullable|numeric|min:0', // เพิ่ม validation สำหรับ thuc_nhan
            'tong_tien' => 'nullable|numeric|min:0',
            'tong_tien_tam_giu' => 'nullable|numeric|min:0',
            'tong_tien_khau_tru' => 'nullable|numeric|min:0',
            'tong_tien_lai_suat' => 'nullable|numeric|min:0',
            'tong_tien_thanh_toan_con_lai' => 'nullable|numeric|min:0',
            'ngay_thanh_toan' => 'nullable|date',
            'trang_thai_thanh_toan' => 'nullable|string|max:50',
            'comment' => 'nullable|string',
        ];
    }
}