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
  'status',
  'ma_nhan_vien',
  'image',
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
        // return $this->belongsToMany(Role::class);
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasPermission($permission)
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    public function userCanViewComponent($componentName)
    {
        foreach ($this->roles as $role) {
            if ($role->components()->where('name', $componentName)
                ->wherePivot('can_view', true)
                ->exists()) {
                return true;
            }
        }
        return false;
    }

    public function positionInfo()
    {
        return $this->belongsTo(ListPosition::class, 'position', 'id_position');
    }


/**
 * Get the Cloudinary image URL from public_id
 * 
 * @param string|null $transformation
 * @return string|null
 */
public function getImageUrlAttribute($transformation = null)
{
    if (!$this->image) {
        return null;
    }
    
    // ถ้า image เป็น public_id ให้สร้าง URL
    if (!str_contains($this->image, 'http')) {
        $cloudinaryService = new \App\Services\CloudinaryService();
        
        $defaultTransformation = [
            'width' => 200,
            'height' => 200,
            'crop' => 'fill',
            'gravity' => 'face',
            'quality' => 'auto:good'
        ];
        
        $transform = $transformation ?? $defaultTransformation;
        
        return $cloudinaryService->getTransformationUrl($this->image, $transform);
    }
    
    // ถ้าเป็น URL อยู่แล้วให้ return ตรงๆ
    return $this->image;
}

/**
 * Get different sizes of the image
 */
public function getImageThumbnailAttribute()
{
    return $this->getImageUrlAttribute([
        'width' => 100,
        'height' => 100,
        'crop' => 'fill',
        'gravity' => 'face'
    ]);
}

public function getImageMediumAttribute()
{
    return $this->getImageUrlAttribute([
        'width' => 300,
        'height' => 300,
        'crop' => 'fill',
        'gravity' => 'face'
    ]);
}

public function getImageLargeAttribute()
{
    return $this->getImageUrlAttribute([
        'width' => 600,
        'height' => 600,
        'crop' => 'fill',
        'gravity' => 'face'
    ]);
}





}
