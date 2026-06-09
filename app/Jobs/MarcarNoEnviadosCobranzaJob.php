<?php

namespace App\Jobs;

use App\Models\ReporteCobranza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Se ejecuta diariamente a las 02:00 AM (Lima).
 * Crea registros "no_enviado" para todas las sedes que no enviaron ayer.
 */
class MarcarNoEnviadosCobranzaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $ayer = Carbon::now('America/Lima')->subDay();

        if ($ayer->dayOfWeek === Carbon::SUNDAY) {
            return;
        }

        $fechaAyer = $ayer->toDateString();

        $usuariosPorSede = User::role('sede')
            ->where('is_active', true)
            ->whereNotNull('sede')
            ->get()
            ->unique('sede');

        foreach ($usuariosPorSede as $usuario) {
            $sede = $usuario->sede;

            $existe = ReporteCobranza::where('sede', $sede)
                ->where('semana_inicio', $fechaAyer)
                ->exists();

            if ($existe) continue;

            [$hora, $min] = ReporteCobranza::horaLimitePara($sede);
            $fechaLimite  = $ayer->copy()->setTime($hora, $min, 0);

            ReporteCobranza::create([
                'user_id'              => $usuario->id,
                'sede'                 => $sede,
                'semana_numero'        => (int) $ayer->dayOfYear,
                'anio'                 => (int) $ayer->year,
                'semana_inicio'        => $fechaAyer,
                'semana_fin'           => $fechaAyer,
                'fecha_limite'         => $fechaLimite,
                'fecha_envio_original' => null,
                'fecha_ultimo_envio'   => null,
                'archivos'             => null,
                'notas'                => null,
                'kpi_porcentaje'       => 0.0,
                'editado_tarde'        => false,
                'estado'               => 'no_enviado',
            ]);
        }
    }
}
