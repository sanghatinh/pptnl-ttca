<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentMapping extends Model
{
    use HasFactory;
    
    use HasFactory;

    protected $table = 'document_mapping'; // Specify the correct table name

    protected $fillable = [
        'document_code',
        'ma_nghiem_thu_bb',
    ];
}