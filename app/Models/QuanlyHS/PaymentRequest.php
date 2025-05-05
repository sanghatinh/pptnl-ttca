<?php

namespace App\Models\QuanlyHS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QuanlyHS\PaymentRequestLog; // เพิ่มการใช้โมเดล PaymentRequestLog เพื่อให้สามารถเข้าถึงข้อมูลการบันทึกการขอชำระเงินได้
use App\Models\User; // เพิ่มการใช้โมเดล User เพื่อให้สามารถเข้าถึงข้อมูลผู้ใช้ได้
class PaymentRequest extends Model
{
    use HasFactory;

    protected $table = 'tb_phieu_trinh_thanh_toan';
    public $timestamps = false; // ตารางไม่มี created_at และ updated_at
    
    protected $fillable = [
        'ma_trinh_thanh_toan',
        'tieu_de',
        'vu_dau_tu',
        'loai_thanh_toan',
        'trang_thai_thanh_toan',  // แก้จาก trinh_trang_thanh_toan เป็น trang_thai_thanh_toan
        'so_to_trinh',
        'ngay_tao',
        'tong_tien_thanh_toan',
        'so_dot_thanh_toan',
        'ngay_thanh_toan',
        'tong_tien_tam_giu',
        'tong_tien_khau_tru',
        'tong_tien_lai_suat',
        'tong_tien_thanh_toan_con_lai',
    ];
    
    // Relationship กับ logs
    public function logs()
    {
        return $this->hasMany(PaymentRequestLog::class, 'ma_trinh_thanh_toan', 'ma_trinh_thanh_toan');
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'creator_id');
}
}