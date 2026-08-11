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
        'experience_rating',
        'service_quality_rating',
        'sede_rating',
        'tiene_consultor',
        'consultor_id',
        'consultor_desconocido',
        'consultor_rating',
        'productos_rating',
        'comments',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'experience_rating'     => 'integer',
        'service_quality_rating' => 'integer',
        'sede_rating'            => 'integer',
        'tiene_consultor'        => 'boolean',
        'consultor_desconocido'  => 'boolean',
        'consultor_rating'       => 'integer',
        'productos_rating'       => 'integer',
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
        4 => 'MUY BUENA',
        3 => 'BUENA',
        2 => 'INSATISFECHO',
        1 => 'MUY INSATISFECHO',
    ];

    // "Productos" concuerda en género masculino plural ("MUY BUENOS/BUENOS"),
    // a diferencia del resto de preguntas ("MUY BUENA/BUENA").
    private const RATING_LABELS_PRODUCTOS = [
        4 => 'MUY BUENOS',
        3 => 'BUENOS',
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

    /** true si la encuesta ya viene con el esquema nuevo (productos_rating siempre se pide). */
    public function getEsEsquemaNuevoAttribute(): bool
    {
        return !is_null($this->productos_rating);
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

    public function getProductosRatingTextAttribute()
    {
        return self::RATING_LABELS_PRODUCTOS[$this->productos_rating] ?? 'N/A';
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

    /** Encuestas ya respondidas con el flujo nuevo (post rediseño 2026-08-11). */
    public function scopeEsquemaNuevo($query)
    {
        return $query->whereNotNull('productos_rating');
    }
}
