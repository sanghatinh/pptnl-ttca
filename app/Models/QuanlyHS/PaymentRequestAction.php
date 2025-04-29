<?php

namespace App\Models\QuanlyHS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PaymentRequestAction extends Model
{
    use HasFactory;
    
    protected $table = 'Action_phieu_trinh_thanh_toan';
    
    protected $fillable = [
        'ma_trinh_thanh_toan',
        'action',
        'action_by',
        'action_date',
        'comments',
    ];

    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class, 'ma_trinh_thanh_toan', 'ma_trinh_thanh_toan');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}