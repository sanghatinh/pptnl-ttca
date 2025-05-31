<?php


namespace App\Models\Quanlytaichinh;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPhieutrinhTTHomgiong extends Model
{
    use HasFactory;

    protected $table = 'logs_phieu_trinh_thanh_toan_homgiong';

    protected $fillable = [
        'ma_trinh_thanh_toan',
        'ma_so_phieu',
        'ma_de_nghi_giai_ngan',
    ];

     public $timestamps = false;



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}