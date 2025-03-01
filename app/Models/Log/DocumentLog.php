<?php

namespace App\Models\log;
use App\Models\User; // Add this line to properly import the User model
use App\Models\DocumentDelivery; // Also add this for the document relationship
use Illuminate\Database\Eloquent\Model;

class DocumentLog extends Model
{
    protected $table = 'document_logs';
    
    protected $fillable = [
        'document_id',
        'action',
        'action_by',
        'creator_id',
        'action_date',
        'comments'
    ];

    protected $casts = [
        'action_date' => 'datetime:Y-m-d'
    ];


    public function document()
    {
        return $this->belongsTo(DocumentDelivery::class, 'document_id');
    }

    public function actionUser()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}