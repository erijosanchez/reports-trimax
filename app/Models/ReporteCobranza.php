<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteCobranza extends Model
{
    protected $table = 'reportes_cobranza';

    protected $fillable = [
        'user_id',
        'sede',
        'semana_numero',
        'anio',
        'semana_inicio',
        'semana_fin',
        'fecha_limite',
        'fecha_envio_original',
        'fecha_ultimo_envio',
        'archivos',
        'notas',
        'kpi_porcentaje',
        'editado_tarde',
        'estado',
    ];

    protected $casts = [
        'semana_inicio'       => 'date',
        'semana_fin'          => 'date',
        'fecha_limite'        => 'datetime',
        'fecha_envio_original'=> 'datetime',
        'fecha_ultimo_envio'  => 'datetime',
        'archivos'            => 'array',
        'kpi_porcentaje'      => 'decimal:2',
        'editado_tarde'       => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── KPI ───────────────────────────────────────────────────────

    /**
     * Calcula el KPI según la hora de entrega respecto al límite (sábado 12:00 PM).
     *  En tiempo           → 100%
     *  Atraso ≤ 1 hora     → 90%
     *  Atraso ≤ 2 horas    → 80%
     *  Atraso ≤ 3 horas    → 50%
     *  Atraso > 3 horas    → 0%
     *  No enviado          → 0%
     */
    public static function calcularKpi(Carbon $fechaEnvio, Carbon $fechaLimite): float
    {
        if ($fechaEnvio->lessThanOrEqualTo($fechaLimite)) {
            return 100.0;
        }

        $horasAtraso = $fechaLimite->diffInMinutes($fechaEnvio) / 60.0;

        if ($horasAtraso <= 1) return 90.0;
        if ($horasAtraso <= 2) return 80.0;
        if ($horasAtraso <= 3) return 50.0;

        return 0.0;
    }

    /**
     * Recalcula y persiste el KPI del reporte usando la fecha de envío más tardía.
     * Si se editó después del límite, se penaliza con la fecha de edición.
     */
    public function recalcularKpi(): void
    {
        if (!$this->fecha_ultimo_envio) {
            $this->kpi_porcentaje = 0;
            $this->estado         = 'no_enviado';
            $this->save();
            return;
        }

        // Si hay una edición tardía, el KPI se basa en fecha_ultimo_envio
        $fechaEfectiva = $this->editado_tarde
            ? $this->fecha_ultimo_envio
            : $this->fecha_envio_original;

        $kpi = self::calcularKpi($fechaEfectiva, $this->fecha_limite);

        $this->kpi_porcentaje = $kpi;
        $this->estado         = $kpi >= 100 ? 'en_tiempo' : 'con_atraso';
        $this->save();
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Devuelve (o crea) el reporte de la semana actual para una sede dada.
     */
    public static function obtenerOCrearSemanaActual(int $userId, string $sede): self
    {
        [$semanaNumero, $anio, $inicio, $fin, $limite] = self::datosSemanActual();

        return self::firstOrCreate(
            ['sede' => $sede, 'semana_numero' => $semanaNumero, 'anio' => $anio],
            [
                'user_id'       => $userId,
                'semana_inicio' => $inicio,
                'semana_fin'    => $fin,
                'fecha_limite'  => $limite,
                'estado'        => 'pendiente',
            ]
        );
    }

    /**
     * Calcula los datos de la semana actual (Lima UTC-5).
     * Retorna [semana, anio, inicio(lunes), fin(sábado), límite(sáb 12:00)].
     */
    public static function datosSemanActual(): array
    {
        $hoy    = Carbon::now('America/Lima');
        $lunes  = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $sabado = $lunes->copy()->addDays(5);
        $limite = $sabado->copy()->setTime(12, 0, 0);

        return [
            (int) $hoy->isoWeek(),
            (int) $hoy->isoWeekYear(),
            $lunes->toDateString(),
            $sabado->toDateString(),
            $limite,
        ];
    }

    /** Etiqueta visual del KPI */
    public function kpiLabel(): string
    {
        if (is_null($this->kpi_porcentaje)) return '—';
        return number_format($this->kpi_porcentaje, 0) . '%';
    }

    /** Color Bootstrap del KPI */
    public function kpiColor(): string
    {
        $k = (float) $this->kpi_porcentaje;
        if ($k >= 100) return 'success';
        if ($k >= 80)  return 'info';
        if ($k >= 50)  return 'warning';
        return 'danger';
    }

    /** Indica si la semana ya venció (pasó del límite) */
    public function estaVencido(): bool
    {
        return Carbon::now('America/Lima')->greaterThan($this->fecha_limite);
    }

    /** Minutos restantes hasta el límite (negativo si ya venció) */
    public function minutosRestantes(): int
    {
        return (int) Carbon::now('America/Lima')->diffInMinutes($this->fecha_limite, false);
    }
}
