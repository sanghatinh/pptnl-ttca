<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleComponent extends Model
{
    use HasFactory;

    // Define the table if it's not the plural form of the model name
    protected $table = 'role_component';

    // Define the fillable fields
    protected $fillable = ['role_id', 'component_id', 'can_view'];
}