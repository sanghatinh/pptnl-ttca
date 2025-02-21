<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMapping extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'document_code',
        'ma_nghiem_thu_bb',
    ];
}