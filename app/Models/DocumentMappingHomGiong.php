<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentMappingHomGiong extends Model
{
    protected $table = 'document_mapping_homgiong';
    
    protected $fillable = [
        'document_code',
        'ma_so_phieu'
    ];

    // Add relationship with document delivery
    public function documentDelivery()
    {
        return $this->belongsTo(DocumentDelivery::class, 'document_code', 'document_code');
    }

    // Add relationship with bien ban hom giong
    public function bienBanHomGiong()
    {
        return $this->belongsTo(BienBanNghiemThuHomGiong::class, 'ma_so_phieu', 'ma_so_phieu');
    }
}
