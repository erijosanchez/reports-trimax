<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'codigo',
        'sede',
        'status',
        'archivos',
        'total',
        'solicitado_at',
        'aplicado_at',
        'created_by',
        'applied_by',
        'revision_estado',
        'revision_motivo',
        'revision_kpi_penalidad',
        'revision_archivos',
        'revision_user_id',
        'revision_at',
    ];

    protected $casts = [
        'archivos'      => 'array',
        'total'         => 'decimal:2',
        'solicitado_at' => 'date',
        'aplicado_at'   => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'revision_kpi_penalidad' => 'decimal:2',
        'revision_archivos'      => 'array',
        'revision_at'            => 'datetime',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(VoucherFactura::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aplicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_user_id');
    }

    // ── KPI de conformidad ────────────────────────────────────────

    /**
     * KPI de conformidad del voucher según la revisión de finanzas.
     *   sin revisar         → null (no cuenta en el promedio de la sede)
     *   conforme            → 100%
     *   conforme_observado  → 100 - penalidad (80% o 50%)
     *   rechazado           → 0%
     */
    public function conformidadKpi(): ?float
    {
        return match ($this->revision_estado) {
            'conforme'           => 100.0,
            'conforme_observado' => round(100.0 * (1 - ((float) $this->revision_kpi_penalidad / 100)), 2),
            'rechazado'          => 0.0,
            default              => null, // sin revisar
        };
    }

    /** Etiqueta visual del KPI de conformidad. */
    public function conformidadLabel(): string
    {
        $k = $this->conformidadKpi();
        return is_null($k) ? 'Pendiente rev.' : number_format($k, 0) . '%';
    }

    /** Color Bootstrap del KPI de conformidad. */
    public function conformidadColor(): string
    {
        $k = $this->conformidadKpi();
        if (is_null($k)) return 'dark';
        if ($k >= 100)   return 'success';
        if ($k >= 80)    return 'info';
        if ($k >= 50)    return 'warning';
        return 'danger';
    }
}
