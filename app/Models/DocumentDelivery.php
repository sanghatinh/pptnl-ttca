<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDelivery extends Model
{
    use HasFactory;

    protected $table = 'document_delivery';

    protected $fillable = [
        'document_code', 'created_date', 'title', 'investment_project', 'creator_id', 'station', 'document_type', 'file_count', 'received_date', 'receiver_id', 'status', 'loan_status'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public static function generateDocumentCode($investmentName) {
        // หา Ma_Vudautu จาก Ten_Vudautu
        $investmentCode = \DB::table('tb_vudautu')
            ->where('Ten_Vudautu', $investmentName)
            ->value('Ma_Vudautu');
    
        if (!$investmentCode) {
            throw new \Exception("Investment code not found for: " . $investmentName);
        }
    
        $latest = static::orderBy('id', 'desc')->first();
        $number = $latest ? sprintf("%06d", $latest->id + 1) : '000001';
        return 'PGNHS-' . $investmentCode . '-' . $number;
    }

    // Add relationship for document mappings
    public function documentMappings()
    {
        return $this->hasMany(DocumentMapping::class, 'document_code', 'document_code');
    }

    // Add relationship for document mappings hom giong
    public function documentMappingsHomgiong()
    {
        return $this->hasMany(DocumentMappingHomgiong::class, 'document_code', 'document_code');
    }



}