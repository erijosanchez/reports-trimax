<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Feriado extends Model
{
    protected $table = 'feriados';

    const TIPOS   = ['nacional', 'regional'];
    const FUENTES = ['manual', 'gob.pe'];

    protected $fillable = [
        'fecha',
        'motivo',
        'tipo',
        'fuente',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /** Fechas (Y-m-d) de feriados nacionales del año, cacheadas. */
    public static function fechasDelAnio(int $anio): array
    {
        return Cache::remember("feriados:{$anio}", now()->addHours(6), function () use ($anio) {
            return self::whereYear('fecha', $anio)
                ->where('tipo', 'nacional')
                ->pluck('fecha')
                ->map(fn($f) => $f->toDateString())
                ->all();
        });
    }

    /** Indica si la fecha dada es feriado nacional. Null (fecha desconocida) → false. */
    public static function esFeriado(Carbon|string|null $fecha): bool
    {
        if ($fecha === null) {
            return false;
        }

        $fecha = $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);

        return in_array($fecha->toDateString(), self::fechasDelAnio($fecha->year), true);
    }

    /** Día laborable = no domingo y no feriado nacional. */
    public static function esDiaLaborable(Carbon|string $fecha): bool
    {
        $fecha = $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);

        return $fecha->dayOfWeek !== Carbon::SUNDAY && !self::esFeriado($fecha);
    }

    /** Siguiente día laborable a partir de (sin incluir) la fecha dada. */
    public static function siguienteDiaHabil(Carbon $fecha): Carbon
    {
        $dia = $fecha->copy();
        do {
            $dia->addDay();
        } while (!self::esDiaLaborable($dia));

        return $dia;
    }

    public static function invalidarCache(int $anio): void
    {
        Cache::forget("feriados:{$anio}");
    }

    protected static function booted(): void
    {
        static::saved(fn(Feriado $f) => self::invalidarCache($f->fecha->year));
        static::deleted(fn(Feriado $f) => self::invalidarCache($f->fecha->year));
    }
}
