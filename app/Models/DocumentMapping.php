<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMapping extends Model
{
    use HasFactory;

    protected $table = 'document_mapping';

    protected $fillable = [
        'document_code',
        'ma_nghiem_thu_bb',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Additional validation could be added here if needed
        });
    }

    // Add relationship to DocumentDelivery
    public function documentDelivery()
    {
        return $this->belongsTo(DocumentDelivery::class, 'document_code', 'document_code');
    }

    // Add relationship to BienBanNghiemThu
    public function bienBanNghiemThu()
    {
        return $this->belongsTo(BienBanNghiemThu::class, 'ma_nghiem_thu_bb', 'ma_nghiem_thu');
    }
    
}

