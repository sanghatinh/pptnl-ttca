<?php
<?php

namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banking';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code_banking',
        'name_banking',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'code_banking' => 'string',
        'name_banking' => 'string',
    ];

    /**
     * Get the banking code.
     *
     * @return string
     */
    public function getCodeBankingAttribute($value)
    {
        return $value;
    }

    /**
     * Get the banking name.
     *
     * @return string
     */
    public function getNameBankingAttribute($value)
    {
        return $value;
    }

    /**
     * Scope a query to search by code or name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('code_banking', 'like', '%' . $search . '%')
                    ->orWhere('name_banking', 'like', '%' . $search . '%');
    }
}