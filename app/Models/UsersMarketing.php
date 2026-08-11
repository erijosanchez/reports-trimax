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

    /**
     * Sede a precargar en el formulario público según el rol del link:
     * - Sede: ella misma.
     * - Consultor: su única sede asignada en consultor_sede (si tiene más de
     *   una, no se precarga — el cliente elige, igual que en el link de
     *   Trimax General).
     * - Trimax: null, siempre se elige desde cero.
     */
    public function sedePreseleccionadaId(): ?int
    {
        if ($this->isSede()) {
            return $this->id;
        }

        if ($this->isConsultor()) {
            $sedeIds = $this->sedes()->pluck('users_marketing.id');
            return $sedeIds->count() === 1 ? $sedeIds->first() : null;
        }

        return null;
    }

    /**
     * Query de encuestas a considerar para las stats "propias" de esta entidad.
     * - Sede: sus encuestas directas + las de Trimax General etiquetadas con esta sede.
     * - Trimax: solo las que el cliente dejó sin sede seleccionada.
     * - Consultor: únicamente sus encuestas directas — las calificaciones de
     *   una sede ya NO se heredan hacia sus consultores asignados (decisión
     *   validada 2026-08-11: cada consultor responde solo por su propio link).
     */
    protected function surveyQueryForStats()
    {
        if ($this->isSede()) {
            return Survey::where('user_id', $this->id)->orWhere('sede_id', $this->id);
        }

        if ($this->isTrimax()) {
            return Survey::where('user_id', $this->id)->whereNull('sede_id');
        }

        return $this->surveys();
    }

    public function getAverageRatingAttribute()
    {
        return round($this->surveyQueryForStats()->avg('experience_rating') ?? 0, 2);
    }

    public function getTotalSurveysAttribute()
    {
        return $this->surveyQueryForStats()->count();
    }

    // Obtener estadísticas detalladas del consultor (solo sus encuestas propias).
    public function getConsultorStatsAttribute()
    {
        if (!$this->isConsultor()) {
            return null;
        }

        $surveys = $this->surveys;

        return [
            'total_surveys' => $surveys->count(),
            'average_rating' => round($surveys->avg('experience_rating') ?? 0, 2),
            'muy_feliz' => $surveys->where('experience_rating', 4)->count(),
            'feliz' => $surveys->where('experience_rating', 3)->count(),
            'insatisfecho' => $surveys->where('experience_rating', 2)->count(),
            'muy_insatisfecho' => $surveys->where('experience_rating', 1)->count(),
        ];
    }
}
