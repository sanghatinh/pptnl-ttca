<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AttendanceLogs extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_logs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'users_id',
        'date',
        'checkin_morning',
        'photo_morning',
        'lat_morning',
        'lng_morning',
        'note_morning',
        'checkin_evening',
        'photo_evening',
        'lat_evening',
        'lng_evening',
        'note_evening',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'checkin_morning' => 'datetime',
        'checkin_evening' => 'datetime',
        'lat_morning' => 'decimal:6',
        'lng_morning' => 'decimal:6',
        'lat_evening' => 'decimal:6',
        'lng_evening' => 'decimal:6',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Validation rules
     */
    public static function rules($id = null)
{
    return [
        'users_id' => 'required|integer|exists:users,id',
        'date' => 'required|date',
        'checkin_morning' => 'nullable|date',
        'photo_morning' => 'nullable|string',
        'lat_morning' => 'nullable|numeric|between:-90,90',
        'lng_morning' => 'nullable|numeric|between:-180,180',
        'note_morning' => 'nullable|string|max:500',
        'checkin_evening' => 'nullable|date',
        'photo_evening' => 'nullable|string',
        'lat_evening' => 'nullable|numeric|between:-90,90',
        'lng_evening' => 'nullable|numeric|between:-180,180',
        'note_evening' => 'nullable|string|max:500',
    ];
}

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('users_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if user has morning check-in for specific date
     */
    public function hasMorningCheckin()
    {
        return !is_null($this->checkin_morning);
    }

    /**
     * Check if user has evening check-in for specific date
     */
    public function hasEveningCheckin()
    {
        return !is_null($this->checkin_evening);
    }

    /**
     * Get attendance status for the day
     */
    public function getAttendanceStatus()
    {
        if ($this->hasMorningCheckin() && $this->hasEveningCheckin()) {
            return 'full_day';
        } elseif ($this->hasMorningCheckin()) {
            return 'morning_only';
        } elseif ($this->hasEveningCheckin()) {
            return 'evening_only';
        }
        return 'no_checkin';
    }
}