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

    public function sedes()
    {
        return $this->belongsToMany(
            UsersMarketing::class,
            'consultor_sede',
            'consultor_id',
            'sede_id'
        )->wherePivot('is_active', true);
    }

    public function consultores()
    {
        return $this->belongsToMany(
            UsersMarketing::class,
            'consultor_sede', // Nombre de la tabla pivot
            'sede_id',         // Foreign key de la sede en la tabla pivot
            'consultor_id'     // Foreign key del consultor en la tabla pivot
        )->wherePivot('consultor_id', '!=', null);
    }

    public function getSurveyUrlAttribute()
    {
        return url("/encuesta/{$this->unique_token}");
    }

    public function isConsultor()
    {
        return $this->role === 'consultor';
    }

    public function isTrimax()
    {
        return $this->role === 'trimax';
    }

    public function isSede()
    {
        return $this->role === 'sede';
    }

    public function getAverageRatingAttribute()
    {
        return round($this->surveys()->avg('experience_rating') ?? 0, 2);
    }

    // Obtener promedio INCLUYENDO sedes asignadas (solo para consultores)
    public function getAverageRatingWithSedesAttribute()
    {
        if (!$this->isConsultor()) {
            return $this->average_rating;
        }

        // Obtener IDs de las sedes asignadas
        $sedeIds = $this->sedes()->pluck('users_marketing.id')->toArray();

        // Incluir el ID del consultor
        $allIds = array_merge([$this->id], $sedeIds);

        // Calcular promedio de todas las encuestas
        $average = Survey::whereIn('user_id', $allIds)
            ->avg('experience_rating');

        return round($average ?? 0, 2);
    }

    public function getTotalSurveysAttribute()
    {
        return $this->surveys()->count();
    }

    // Obtener total de encuestas INCLUYENDO sedes (solo para consultores)
    public function getTotalSurveysWithSedesAttribute()
    {
        if (!$this->isConsultor()) {
            return $this->total_surveys;
        }

        $sedeIds = $this->sedes()->pluck('users_marketing.id')->toArray();
        $allIds = array_merge([$this->id], $sedeIds);

        return Survey::whereIn('user_id', $allIds)->count();
    }

    // Obtener estadísticas detalladas del consultor con sus sedes
    public function getConsultorStatsAttribute()
    {
        if (!$this->isConsultor()) {
            return null;
        }

        $sedeIds = $this->sedes()->pluck('users_marketing.id')->toArray();
        $allIds = array_merge([$this->id], $sedeIds);

        $surveys = Survey::whereIn('user_id', $allIds)->get();

        return [
            'total_surveys' => $surveys->count(),
            'average_rating' => round($surveys->avg('experience_rating') ?? 0, 2),
            'muy_feliz' => $surveys->where('experience_rating', 4)->count(),
            'feliz' => $surveys->where('experience_rating', 3)->count(),
            'insatisfecho' => $surveys->where('experience_rating', 2)->count(),
            'muy_insatisfecho' => $surveys->where('experience_rating', 1)->count(),
            'por_sede' => $this->sedes->map(function ($sede) {
                return [
                    'sede_id' => $sede->id,
                    'sede_name' => $sede->name,
                    'sede_location' => $sede->location,
                    'total_surveys' => $sede->surveys->count(),
                    'average_rating' => $sede->average_rating,
                ];
            }),
            'propio' => [
                'total_surveys' => $this->surveys->count(),
                'average_rating' => $this->average_rating,
            ]
        ];
    }
}
