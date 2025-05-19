<?php


namespace App\Models\Farmer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Supplier;

class UserFamerRoles extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'userfarmer_role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_number',
        'role_id',
    ];

    /**
     * Get the role associated with the user farmer role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the supplier associated with the user farmer role.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_number', 'supplier_number');
    }
}