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

    // Generar token único al crear usuario — los consultores ya no tienen
    // link propio (2026-08-12): solo se les asigna a sedes y su calificación
    // sale de la pregunta de consultor en la encuesta maestra.
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($usersMarketing) {
            if (empty($usersMarketing->unique_token) && $usersMarketing->role !== 'consultor') {
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
        return $this->unique_token ? url("/encuesta/{$this->unique_token}") : null;
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
     * - Consultor: encuestas de la encuesta maestra donde lo eligieron como su
     *   consultor (`consultor_id`) — ya no tiene link propio (2026-08-12), así
     *   que `user_id` nunca será suyo para encuestas nuevas.
     */
    protected function surveyQueryForStats()
    {
        if ($this->isSede()) {
            return Survey::where('user_id', $this->id)->orWhere('sede_id', $this->id);
        }

        if ($this->isTrimax()) {
            return Survey::where('user_id', $this->id)->whereNull('sede_id');
        }

        if ($this->isConsultor()) {
            return Survey::where('consultor_id', $this->id);
        }

        return $this->surveys();
    }

    public function getAverageRatingAttribute()
    {
        $column = $this->isConsultor() ? 'consultor_rating' : 'experience_rating';

        return round($this->surveyQueryForStats()->avg($column) ?? 0, 2);
    }

    public function getTotalSurveysAttribute()
    {
        return $this->surveyQueryForStats()->count();
    }

    // Estadísticas detalladas del consultor: encuestas de la encuesta maestra
    // donde lo eligieron (consultor_id), calificadas con consultor_rating —
    // ya no tiene link propio, así que no hay "sus encuestas" vía user_id.
    public function getConsultorStatsAttribute()
    {
        if (!$this->isConsultor()) {
            return null;
        }

        $surveys = Survey::where('consultor_id', $this->id)->get();

        return [
            'total_surveys' => $surveys->count(),
            'average_rating' => round($surveys->avg('consultor_rating') ?? 0, 2),
            'muy_feliz' => $surveys->where('consultor_rating', 4)->count(),
            'feliz' => $surveys->where('consultor_rating', 3)->count(),
            'insatisfecho' => $surveys->where('consultor_rating', 2)->count(),
            'muy_insatisfecho' => $surveys->where('consultor_rating', 1)->count(),
        ];
    }
}
