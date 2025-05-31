<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPhieutrinhTTHomgiong extends Model
{
    use HasFactory;

    protected $table = 'action_phieu_trinh_thanh_toan_homgiong';

    protected $fillable = [
        'ma_trinh_thanh_toan',
        'action',
        'action_by',
        'action_date',
        'comments'
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the possible action values
     */
    public static function getActionOptions()
    {
        return [
            'processing' => 'Đang xử lý',
            'submitted' => 'Đã nộp',
            'paid' => 'Đã thanh toán',
            'cancelled' => 'Đã hủy'
        ];
    }

    /**
     * Get action label in Vietnamese
     */
    public function getActionLabelAttribute()
    {
        $options = self::getActionOptions();
        return $options[$this->action] ?? $this->action;
    }

    /**
     * Relationship with User who performed the action
     */
    public function actionUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'action_by');
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('action_date', [$startDate, $endDate]);
    }
}