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
        'supervisa_a',
        'num_vacantes',
        'info_confidencial',
        'tipo',
        'tipo_vacante',
        'permanencia',
        'disponibilidad_viaje',
        'jornada',
        'condiciones_oferta',
        'comentarios',
        'motivo',
        'candidatos',
        'herramientas',
        'responsable_rh_id',
        'responsable_rh_externo',
        'estado',
        'sla',
        'fecha_solicitud',
        'fecha_cierre',
        // Sección RRHH
        'fecha_estimada_contratacion',
        'tipo_contrato',
        'duracion_contrato',
        'remuneracion_prevista',
        'horario_trabajo',
        'beneficios',
        // Firmas
        'firma_solicitante_data',
        'firma_solicitante_at',
        'firma_solicitante_nombre',
        'firma_rrhh_data',
        'firma_rrhh_at',
        'firma_rrhh_nombre',
        'firma_gerente_data',
        'firma_gerente_at',
        'firma_gerente_nombre',
    ];

    protected $casts = [
        'fecha_solicitud'              => 'datetime',
        'fecha_cierre'                 => 'datetime',
        'fecha_estimada_contratacion'  => 'date',
        'firma_solicitante_at'         => 'datetime',
        'firma_rrhh_at'                => 'datetime',
        'firma_gerente_at'             => 'datetime',
        'candidatos'                   => 'array',
        'herramientas'                 => 'array',
        'info_confidencial'            => 'boolean',
        'disponibilidad_viaje'         => 'boolean',
        'remuneracion_prevista'        => 'decimal:2',
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
        'en_capacitacion'       => '🎓 En capacitación',
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
     * Total = días transcurridos del proceso según estado.
     * - En Proceso  → hoy − fecha_solicitud
     * - Cancelado   → 0
     * - Contratado  → fecha_estimada_contratacion − fecha_solicitud
     */
    public function getTotalAttribute(): int
    {
        if ($this->estado === self::ESTADO_EN_PROCESO) {
            return (int) $this->fecha_solicitud->diffInDays(now());
        } elseif ($this->estado === self::ESTADO_CANCELADO) {
            return 0;
        } elseif ($this->estado === self::ESTADO_CONTRATADO) {
            return $this->fecha_estimada_contratacion
                ? (int) $this->fecha_solicitud->diffInDays($this->fecha_estimada_contratacion)
                : 0;
        }

        return 0; // Pendiente
    }

    /**
     * KPI = % de cumplimiento basado en el total de días vs SLA.
     * - Total < SLA  → 100%
     * - 46–50 días   → 95%
     * - 51–60 días   → 90%
     * - 61–70 días   → 85%
     * - 71–80 días   → 80%
     * - > 80 días    → 50%
     */
    public function getKpiAttribute(): int
    {
        if (in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_CANCELADO])) {
            return 0;
        }

        $total = $this->total;
        $sla   = $this->sla ?: 45;

        if ($total < $sla)   return 100;
        if ($total <= 50)    return 95;
        if ($total <= 60)    return 90;
        if ($total <= 70)    return 85;
        if ($total <= 80)    return 80;
        return 50;
    }

    public function getSemaforoAttribute(): string
    {
        if ($this->estado === self::ESTADO_PENDIENTE) return 'pendiente';
        if ($this->estado === self::ESTADO_CANCELADO) return 'cancelado';

        return match (true) {
            $this->kpi === 100  => 'optimo',
            $this->kpi >= 80    => 'riesgo',
            default             => 'critico',
        };
    }

    public function getColorSemaforoAttribute(): string
    {
        return match ($this->semaforo) {
            'pendiente', 'cancelado' => 'gray',
            'optimo'                 => 'green',
            'riesgo'                 => 'orange',
            'critico'                => 'red',
        };
    }

    public function slaVencido(): bool
    {
        return $this->estado === self::ESTADO_EN_PROCESO
            && $this->total >= ($this->sla ?: 45);
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
