<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieutrinhthanhtoanHomgiong extends Model
{
    use HasFactory;

    protected $table = 'tb_phieu_trinh_thanh_toan_homgiong';

    public $timestamps = false;

    protected $fillable = [
        'ma_trinh_thanh_toan',
        'tieu_de',
        'vu_dau_tu',
        'loai_thanh_toan',
        'trang_thai_thanh_toan',
        'so_to_trinh',
        'ngay_tao',
        'tong_tien_thanh_toan',
        'so_dot_thanh_toan',
        'ngay_thanh_toan',
        'tong_tien_tam_giu',
        'tong_tien_khau_tru',
        'tong_tien_lai_suat',
        'tong_tien_thanh_toan_con_lai',
        'link_url',
        'note'
    ];

    protected $casts = [
        'ngay_tao' => 'date',
        'ngay_thanh_toan' => 'date',
        'tong_tien_thanh_toan' => 'decimal:2',
        'tong_tien_tam_giu' => 'decimal:2',
        'tong_tien_khau_tru' => 'decimal:2',
        'tong_tien_lai_suat' => 'decimal:2',
        'tong_tien_thanh_toan_con_lai' => 'decimal:2',
        'so_dot_thanh_toan' => 'integer'
    ];

    protected $attributes = [
        'trang_thai_thanh_toan' => 'processing'
    ];

    // Constants for status values
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    // Scope methods
    public function scopeByStatus($query, $status)
    {
        return $query->where('trang_thai_thanh_toan', $status);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('ngay_tao', $year);
    }

    // Accessor methods
    public function getTrangThaiTextAttribute()
    {
        $statuses = [
            'processing' => 'Đang xử lý',
            'submitted' => 'Đã gửi',
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy'
        ];
        
        return $statuses[$this->trang_thai_thanh_toan] ?? 'Không xác định';
    }

    // Mutator methods
    public function setTongTienThanhToanAttribute($value)
    {
        $this->attributes['tong_tien_thanh_toan'] = number_format($value, 2, '.', '');
    }
}