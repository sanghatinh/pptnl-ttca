<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
class StatusDocumentDelivery extends Model
{
    protected $table = 'status_document_delivery';
    
    protected $fillable = [
        'id_status',
        'name_status'
    ];
}


