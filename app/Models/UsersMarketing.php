<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class UsersMarketing extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'users_marketing';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'location',
        'unique_token',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
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

    // CORREGIDO: Especificar explícitamente la foreign key 'user_id'
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'user_id');
    }

    // Obtener URL de la encuesta
    public function getSurveyUrlAttribute()
    {
        return url("/encuesta/{$this->unique_token}");
    }

    // Verificar si es consultor
    public function isConsultor()
    {
        return $this->role === 'consultor';
    }

    // Verificar si es sede
    public function isSede()
    {
        return $this->role === 'sede';
    }

    // Obtener promedio de calificaciones
    public function getAverageRatingAttribute()
    {
        return $this->surveys()->avg('experience_rating') ?? 0;
    }

    // Obtener total de encuestas
    public function getTotalSurveysAttribute()
    {
        return $this->surveys()->count();
    }
}
