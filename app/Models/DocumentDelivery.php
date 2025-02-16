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
}