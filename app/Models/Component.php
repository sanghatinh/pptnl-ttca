<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    // Define the table if it's not the plural form of the model name
    protected $table = 'components';

    // Define the fillable fields
    protected $fillable = ['name'];
}
