<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RequerimientoPersonal extends Model
{
    use SoftDeletes;

    protected $table = 'requerimientos_personal';

    protected $fillable = [
        'codigo',
        'solicitante_id',
        'gerencia',
        'puesto',
        'sede',
        'jefe_directo',
        'tipo',
        'condiciones_oferta',
        'comentarios',
        'responsable_rh_id',
        'responsable_rh_externo',
        'estado',
        'sla',
        'fecha_solicitud',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_cierre'    => 'datetime',
    ];

    // ─── Constantes de estado ──────────────────────────────────
    const ESTADO_PENDIENTE   = 'Pendiente';
    const ESTADO_EN_PROCESO  = 'En Proceso';
    const ESTADO_CONTRATADO  = 'Contratado';
    const ESTADO_CANCELADO   = 'Cancelado';

    // ─── Etapas del proceso de selección (para el historial) ──
    const ETAPAS = [
        'publicacion_oferta'    => '📢 Publicación de oferta',
        'revision_cvs'          => '📋 Revisión de CVs',
        'entrevista_virtual'    => '🎥 Entrevista virtual',
        'entrevista_presencial' => '🤝 Entrevista presencial',
        'evaluacion'            => '📝 Evaluación',
        'oferta_candidato'      => '✅ Oferta al candidato',
        'nota'                  => '💬 Nota libre',
    ];

    // ─── Relaciones ────────────────────────────────────────────

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function responsableRh()
    {
        return $this->belongsTo(User::class, 'responsable_rh_id');
    }

    public function historial()
    {
        return $this->hasMany(RequerimientoHistorial::class, 'requerimiento_id')
            ->orderBy('created_at', 'asc');
    }

    // ─── Accessors ─────────────────────────────────────────────

    /**
     * KPI = días transcurridos. En Pendiente siempre es 0.
     */
    public function getKpiAttribute(): int
    {
        if ($this->estado === self::ESTADO_PENDIENTE) return 0;
        $fin = $this->fecha_cierre ?? now();
        return (int) $this->fecha_solicitud->diffInDays($fin);
    }

    public function getTiempoTotalAttribute(): int
    {
        return $this->sla + $this->kpi;
    }

    public function getSemaforoAttribute(): string
    {
        if ($this->estado === self::ESTADO_PENDIENTE) return 'pendiente';
        return match (true) {
            $this->kpi <= 45 => 'optimo',
            $this->kpi <= 60 => 'riesgo',
            default          => 'critico',
        };
    }

    public function getColorSemaforoAttribute(): string
    {
        return match ($this->semaforo) {
            'pendiente' => 'gray',
            'optimo'    => 'green',
            'riesgo'    => 'orange',
            'critico'   => 'red',
        };
    }

    public function slaVencido(): bool
    {
        return $this->estado === self::ESTADO_EN_PROCESO && $this->kpi >= 45;
    }

    public function estaActivo(): bool
    {
        return in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO]);
    }

    // ─── Generador de código ───────────────────────────────────

    public static function generarCodigo(): string
    {
        $meses = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];
        $now     = Carbon::now();
        $prefijo = $meses[$now->month] . $now->format('y');
        $ultimo  = self::whereYear('fecha_solicitud', $now->year)
            ->whereMonth('fecha_solicitud', $now->month)
            ->orderBy('id', 'desc')->lockForUpdate()->first();
        $correlativo = $ultimo ? ((int) substr($ultimo->codigo, -4) + 1) : 1;
        return $prefijo . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
    }

    // ─── Scopes ────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }
    public function scopeEnProceso($query)
    {
        return $query->where('estado', self::ESTADO_EN_PROCESO);
    }
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PROCESO]);
    }
    public function scopeSlaVencidos($query)
    {
        return $query->where('estado', self::ESTADO_EN_PROCESO)
            ->whereRaw('DATEDIFF(NOW(), fecha_solicitud) >= 45');
    }
}
