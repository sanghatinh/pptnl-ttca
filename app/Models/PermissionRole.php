<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    use HasFactory;

    // Define the table if it's not the plural form of the model name
    protected $table = 'permission_role';

    // Define the fillable fields
    protected $fillable = ['role_id', 'permission_id'];
}
