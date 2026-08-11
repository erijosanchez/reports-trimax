<?php

namespace App\Jobs;

use App\Models\ReporteComentarios;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Se ejecuta semanalmente el viernes 02:00 AM (Lima), un par de horas
 * después del límite del jueves 11:59 PM. Crea registros "no_enviado" para
 * las sedes que no enviaron su reporte de Comentarios esa semana — mismo
 * patrón que MarcarNoEnviadosCobranzaJob, adaptado a la cadencia semanal de
 * este módulo (Cobranza es diaria).
 *
 * datosSemanActual() ya corre el límite al siguiente día hábil si el jueves
 * es feriado; el chequeo de abajo evita marcar "no_enviado" antes de que
 * venza ese límite corrido. Caso borde: si el límite corrido cae después del
 * día en que corre este job, esa semana no queda marcada aquí (no hay
 * reintento posterior) — situación poco frecuente, no se resuelve en esta
 * primera versión.
 */
class MarcarNoEnviadosComentariosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        [$semanaNumero, $anio, $inicio, $fin, $limite] = ReporteComentarios::datosSemanActual();

        if (Carbon::now('America/Lima')->lessThan($limite)) {
            return;
        }

        $usuariosPorSede = User::role('sede')
            ->where('is_active', true)
            ->whereNotNull('sede')
            ->get()
            ->unique('sede');

        foreach ($usuariosPorSede as $usuario) {
            $sede = $usuario->sede;

            $existe = ReporteComentarios::where('sede', $sede)
                ->where('semana_numero', $semanaNumero)
                ->where('anio', $anio)
                ->exists();

            if ($existe) continue;

            ReporteComentarios::create([
                'user_id'              => $usuario->id,
                'sede'                 => $sede,
                'semana_numero'        => $semanaNumero,
                'anio'                 => $anio,
                'semana_inicio'        => $inicio,
                'semana_fin'           => $fin,
                'fecha_limite'         => $limite,
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
