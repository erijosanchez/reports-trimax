<?php

namespace App\Jobs;

use App\Models\ReporteCajaChica;
use App\Models\User;
use App\Notifications\CajaChicaAlertaVencimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Ejecuta cada sábado a la 1:00 PM (Lima) — 1 hora antes del límite (2:00 PM).
 * Alerta a los usuarios sede que aún no enviaron su reporte de Caja Chica.
 */
class AlertaCajaChicaVencimientoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        [$semanaNumero, $anio] = array_slice(ReporteCajaChica::datosSemanActual(), 0, 2);

        $usuariosSede = User::role('sede')->where('is_active', true)->get();

        foreach ($usuariosSede as $usuario) {
            if (!$usuario->sede) continue;

            $yaEnvio = ReporteCajaChica::where('sede', $usuario->sede)
                ->where('semana_numero', $semanaNumero)
                ->where('anio', $anio)
                ->whereNotNull('fecha_envio_original')
                ->exists();

            if ($yaEnvio) continue;

            $reporte = ReporteCajaChica::obtenerOCrearSemanaActual($usuario->id, $usuario->sede);

            Notification::send($usuario, new CajaChicaAlertaVencimiento($reporte));
        }
    }
}
