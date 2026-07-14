<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudDesbloqueo extends Model
{
    protected $table = 'solicitudes_desbloqueo';

    protected $fillable = [
        'user_id',
        'sede',
        'ruc',
        'razon_social',
        'comentarios',
        'archivos',
        'revision_estado',
        'revision_motivo',
        'revision_kpi_penalidad',
        'revision_archivos',
        'revision_user_id',
        'revision_at',
    ];

    protected $casts = [
        'revision_kpi_penalidad' => 'decimal:2',
        'archivos'               => 'array',
        'revision_archivos'      => 'array',
        'revision_at'            => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_user_id');
    }

    // ── KPI de la SEDE (conformidad) ──────────────────────────────

    /**
     * KPI que se atribuye a la SEDE solicitante.
     *   sin revisar         → null (no cuenta en el promedio)
     *   conforme            → 100%
     *   conforme_observado  → 100 - penalidad (80% o 50%)
     *   rechazado           → 0%
     */
    public function kpiSede(): ?float
    {
        return match ($this->revision_estado) {
            'conforme'           => 100.0,
            'conforme_observado' => round(100.0 * (1 - ((float) $this->revision_kpi_penalidad / 100)), 2),
            'rechazado'          => 0.0,
            default              => null,
        };
    }

    // ── KPI de FINANZAS (tiempo de respuesta) ─────────────────────

    /**
     * KPI que se atribuye al personal de FINANZAS que revisa.
     *   sin revisar          → null
     *   rechazado            → 100%
     *   conforme_observado   → 100%
     *   conforme             → según horas reales (solicitud → revisión):
     *        ≤ 1 h → 100% | ≤ 2 h → 75% | ≤ 3 h → 50% | > 3 h → 0%
     */
    public function kpiFinanzas(): ?float
    {
        if (is_null($this->revision_estado)) {
            return null;
        }
        if ($this->revision_estado === 'rechazado' || $this->revision_estado === 'conforme_observado') {
            return 100.0;
        }

        // conforme → basado en tiempo de respuesta
        if (!$this->revision_at || !$this->created_at) {
            return 100.0;
        }
        $horas = $this->created_at->diffInMinutes($this->revision_at) / 60.0;

        if ($horas <= 1) return 100.0;
        if ($horas <= 2) return 75.0;
        if ($horas <= 3) return 50.0;
        return 0.0;
    }

    // ── Helpers de presentación ───────────────────────────────────

    public function kpiSedeLabel(): string
    {
        $k = $this->kpiSede();
        return is_null($k) ? 'Pendiente rev.' : number_format($k, 0) . '%';
    }

    public function kpiSedeColor(): string
    {
        return self::colorPorValor($this->kpiSede());
    }

    public function kpiFinanzasLabel(): string
    {
        $k = $this->kpiFinanzas();
        return is_null($k) ? '—' : number_format($k, 0) . '%';
    }

    public function kpiFinanzasColor(): string
    {
        return self::colorPorValor($this->kpiFinanzas());
    }

    /** Color Bootstrap según el valor del KPI (null = pendiente). */
    public static function colorPorValor(?float $k): string
    {
        if (is_null($k)) return 'dark';
        if ($k >= 100)   return 'success';
        if ($k >= 75)    return 'info';
        if ($k >= 50)    return 'warning';
        return 'danger';
    }
}
