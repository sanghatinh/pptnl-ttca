<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentProject extends Model
{
    use HasFactory;

    protected $table = 'tb_vudautu';
    protected $fillable = ['Ma_Vudautu', 'Ten_Vudautu'];
}

