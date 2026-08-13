<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sede_id',
        'client_name',
        'ruc',
        'experience_rating',
        'service_quality_rating',
        'sede_rating',
        'tiene_consultor',
        'consultor_id',
        'consultor_desconocido',
        'consultor_rating',
        'productos_rating',
        'tiempos_entrega_rating',
        'promociones_rating',
        'comments',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'experience_rating'      => 'integer',
        'service_quality_rating' => 'integer',
        'sede_rating'             => 'integer',
        'tiene_consultor'         => 'boolean',
        'consultor_desconocido'   => 'boolean',
        'consultor_rating'        => 'integer',
        'productos_rating'        => 'integer',
        'tiempos_entrega_rating'  => 'integer',
        'promociones_rating'      => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // service_quality_rating es NOT NULL en BD y ya no se pregunta por
        // separado en el formulario nuevo (columna deprecada, se conserva
        // por compatibilidad con reportes/tiles existentes que la leen).
        // Se refleja igual a sede_rating, la pregunta que más se le parece.
        static::creating(function (self $survey) {
            if (is_null($survey->service_quality_rating) && !is_null($survey->sede_rating)) {
                $survey->service_quality_rating = $survey->sede_rating;
            }
        });
    }

    private const RATING_LABELS = [
        4 => 'MUY SATISFECHO',
        3 => 'SATISFECHO',
        2 => 'INSATISFECHO',
        1 => 'MUY INSATISFECHO',
    ];

    private const RATING_EMOJIS = [
        4 => '😊',
        3 => '🙂',
        2 => '😐',
        1 => '😞',
    ];

    private const RATING_COLORS = [
        4 => '#4CAF50',
        3 => '#2196F3',
        2 => '#FF9800',
        1 => '#F44336',
    ];

    public function userMarketing()
    {
        return $this->belongsTo(UsersMarketing::class, 'user_id', 'id');
    }

    public function selectedSede()
    {
        return $this->belongsTo(UsersMarketing::class, 'sede_id', 'id');
    }

    public function consultor()
    {
        return $this->belongsTo(UsersMarketing::class, 'consultor_id', 'id');
    }

    /**
     * Entidad a mostrar/atribuir: la sede seleccionada por el cliente
     * (encuesta de Trimax General) o, si no se eligió ninguna, el
     * dueño real del link (userMarketing).
     */
    public function getDisplayEntityAttribute()
    {
        return $this->selectedSede ?? $this->userMarketing;
    }

    /** true si la encuesta ya viene con el esquema nuevo (tiempos_entrega_rating siempre se pide). */
    public function getEsEsquemaNuevoAttribute(): bool
    {
        return !is_null($this->tiempos_entrega_rating);
    }

    public function getExperienceRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->experience_rating] ?? 'N/A';
    }

    public function getSedeRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->sede_rating] ?? 'N/A';
    }

    public function getConsultorRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->consultor_rating] ?? 'N/A';
    }

    // productos_rating queda deprecada (rediseño 2026-08-12, reemplazada por
    // tiempos_entrega/promociones) — se mantiene solo para leer encuestas
    // respondidas entre el 2026-08-11 y el 2026-08-12 con ese esquema.
    public function getProductosRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->productos_rating] ?? 'N/A';
    }

    public function getTiemposEntregaRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->tiempos_entrega_rating] ?? 'N/A';
    }

    public function getPromocionesRatingTextAttribute()
    {
        return self::RATING_LABELS[$this->promociones_rating] ?? 'N/A';
    }

    // Se mantiene por compatibilidad con vistas/consumidores existentes —
    // sigue reflejando experience_rating, como antes del rediseño.
    public function getRatingTextAttribute()
    {
        return $this->experience_rating_text;
    }

    public function getRatingEmojiAttribute()
    {
        return self::RATING_EMOJIS[$this->experience_rating] ?? '❓';
    }

    public function getRatingColorAttribute()
    {
        return self::RATING_COLORS[$this->experience_rating] ?? '#999';
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('experience_rating', $rating);
    }

    /** Encuestas ya respondidas con el flujo nuevo (post rediseño 2026-08-12). */
    public function scopeEsquemaNuevo($query)
    {
        return $query->whereNotNull('tiempos_entrega_rating');
    }

    /**
     * Encuestas atribuibles a una entidad (sede o Trimax General) — misma
     * regla que getDisplayEntityAttribute(): si el cliente eligió una sede
     * (sede_id), cuenta exclusivamente para esa sede; si no, cuenta para el
     * dueño del link. Antes de esto, calcularSedeStats() usaba
     * `user_id = sede OR sede_id = sede`, que contaba dos veces una encuesta
     * del link de una sede si el cliente la reetiquetó a otra sede distinta.
     */
    public function scopeParaEntidad($query, int $entidadId)
    {
        return $query->where(function ($q) use ($entidadId) {
            $q->where('sede_id', $entidadId)
                ->orWhere(function ($q2) use ($entidadId) {
                    $q2->whereNull('sede_id')->where('user_id', $entidadId);
                });
        });
    }

    /** Promedio de todas las preguntas de calificación respondidas (pool de valores, no promedio de promedios). */
    public static function promedioConsolidado($surveys): float
    {
        $ratings = collect($surveys)->flatMap(fn($s) => [
            $s->experience_rating,
            $s->sede_rating,
            $s->consultor_rating,
            $s->tiempos_entrega_rating,
            $s->promociones_rating,
        ])->filter(fn($r) => !is_null($r));

        return $ratings->isEmpty() ? 0.0 : round($ratings->avg(), 2);
    }

    /**
     * CSAT top-box: % de respuestas "satisfecho" (3) o "muy satisfecho" (4)
     * sobre el total de respuestas del mismo pool que promedioConsolidado().
     * Es el cálculo estándar de CSAT (a diferencia del promedio consolidado,
     * que es un promedio simple 1-4, no un porcentaje de satisfacción).
     */
    public static function csatConsolidado($surveys): float
    {
        $ratings = collect($surveys)->flatMap(fn($s) => [
            $s->experience_rating,
            $s->sede_rating,
            $s->consultor_rating,
            $s->tiempos_entrega_rating,
            $s->promociones_rating,
        ])->filter(fn($r) => !is_null($r));

        if ($ratings->isEmpty()) {
            return 0.0;
        }

        return round($ratings->filter(fn($r) => $r >= 3)->count() / $ratings->count() * 100, 1);
    }
}
