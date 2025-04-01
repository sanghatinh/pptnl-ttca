<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPosition extends Model
{
    protected $table = 'listposition';
    
    protected $primaryKey = 'id_position';
    
    public $incrementing = false;
    
    protected $fillable = [
        'id_position',
        'position',
        'department'
    ];
}