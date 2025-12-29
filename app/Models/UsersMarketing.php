<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class UsersMarketing extends Model
{
    use HasFactory;

    protected $table = 'users_marketing';

    protected $fillable = [
        'name',
        'role',
        'location',
        'unique_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Generar token único al crear usuario
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($usersMarketing) {
            if (empty($usersMarketing->unique_token)) {
                $usersMarketing->unique_token = Str::random(32);
            }
        });
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class, 'user_id');
    }

    public function getSurveyUrlAttribute()
    {
        return url("/encuesta/{$this->unique_token}");
    }

    public function isConsultor()
    {
        return $this->role === 'consultor';
    }

    public function isSede()
    {
        return $this->role === 'sede';
    }

    public function getAverageRatingAttribute()
    {
        return $this->surveys()->avg('experience_rating') ?? 0;
    }

    public function getTotalSurveysAttribute()
    {
        return $this->surveys()->count();
    }
}
