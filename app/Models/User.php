<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
  'username',
  'password',
  'full_name',
  'position',
  'department',
  'phone',
  'email',
  'role_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
           
        ];
    }

    // ເພີ່ມ

    /**
                    * Get the identifier that will be stored in the subject claim of the JWT.
                    *
                    * @return mixed
                    */
                    public function getJWTIdentifier()
                    {
                        return $this->getKey();
                    }

                    /**
                    * Return a key value array, containing any custom claims to be added to the JWT.
                    *
                    * @return array
                    */
                    public function getJWTCustomClaims()
                    {
                        return [];
                    }

  // ...

  public function roles()
  {
      return $this->belongsToMany(Role::class);
  }
  
  public function hasPermission($permission)
  {
      return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
          $query->where('name', $permission);
      })->exists();
  }

}
