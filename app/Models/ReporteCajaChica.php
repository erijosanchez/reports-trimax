<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteCajaChica extends Model
{
    protected $table = 'reportes_caja_chica';

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
        'semana_inicio'        => 'date',
        'semana_fin'           => 'date',
        'fecha_limite'         => 'datetime',
        'fecha_envio_original' => 'datetime',
        'fecha_ultimo_envio'   => 'datetime',
        'archivos'             => 'array',
        'kpi_porcentaje'       => 'decimal:2',
        'editado_tarde'        => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── KPI ───────────────────────────────────────────────────────

    /**
     * KPI de Caja Chica:
     *  Enviado el mismo sábado de la semana → 100%
     *  Cualquier otro día (antes o después) → 0%
     */
    public static function calcularKpi(Carbon $fechaEnvio, Carbon $fechaLimite): float
    {
        $sabadoSemana = $fechaLimite->copy()->startOfDay();

        return $fechaEnvio->isSameDay($sabadoSemana) ? 100.0 : 0.0;
    }

    public function recalcularKpi(): void
    {
        if (!$this->fecha_ultimo_envio) {
            $this->kpi_porcentaje = 0;
            $this->estado         = 'no_enviado';
            $this->save();
            return;
        }

        $fechaEfectiva = $this->editado_tarde
            ? $this->fecha_ultimo_envio
            : $this->fecha_envio_original;

        $kpi = self::calcularKpi($fechaEfectiva, $this->fecha_limite);

        $this->kpi_porcentaje = $kpi;
        $this->estado         = $kpi >= 100 ? 'en_tiempo' : 'con_atraso';
        $this->save();
    }

    // ── Helpers ───────────────────────────────────────────────────

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
     * Deadline: sábado 2:00 PM hora Lima.
     */
    public static function datosSemanActual(): array
    {
        $hoy    = Carbon::now('America/Lima');
        $lunes  = $hoy->copy()->startOfWeek(Carbon::MONDAY);
        $sabado = $lunes->copy()->addDays(5);
        $limite = $sabado->copy()->setTime(14, 0, 0); // 2:00 PM

        return [
            (int) $hoy->isoWeek(),
            (int) $hoy->isoWeekYear(),
            $lunes->toDateString(),
            $sabado->toDateString(),
            $limite,
        ];
    }

    public function kpiLabel(): string
    {
        if (is_null($this->kpi_porcentaje)) return '—';
        return number_format($this->kpi_porcentaje, 0) . '%';
    }

    public function kpiColor(): string
    {
        return ((float) $this->kpi_porcentaje) >= 100 ? 'success' : 'danger';
    }

    public function estaVencido(): bool
    {
        return Carbon::now('America/Lima')->greaterThan($this->fecha_limite);
    }

    public function minutosRestantes(): int
    {
        return (int) Carbon::now('America/Lima')->diffInMinutes($this->fecha_limite, false);
    }
}
