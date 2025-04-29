<?php

namespace App\Models\QuanlyHS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // เพิ่มการใช้โมเดล User เพื่อให้สามารถเข้าถึงข้อมูลผู้ใช้ได้
use App\Models\QuanlyHS\PaymentRequest; // เพิ่มการใช้โมเดล PaymentRequest เพื่อให้สามารถเข้าถึงข้อมูลการขอชำระเงินได้


    class PaymentRequestLog extends Model
    {
        use HasFactory;
    
        protected $table = 'Logs_phieu_trinh_thanh_toan';
        
        protected $fillable = [
            'ma_trinh_thanh_toan',
            'ma_nghiem_thu',
            'ma_de_nghi_giai_ngan'
        ];
    
        public function paymentRequest()
        {
            return $this->belongsTo(PaymentRequest::class, 'ma_trinh_thanh_toan', 'ma_trinh_thanh_toan');
        }
        
        // Relationship กับ user
        public function user()
        {
            return $this->belongsTo(User::class, 'action_by');
        }
}