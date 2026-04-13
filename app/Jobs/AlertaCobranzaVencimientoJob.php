<?php

namespace App\Jobs;

use App\Models\ReporteCobranza;
use App\Models\User;
use App\Notifications\CobranzaAlertaVencimiento;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Se ejecuta lunes–sábado a las 11:00 AM (Lima) — 1 hora antes del límite diario (12:00 PM).
 * Solo envía correo a usuarios sede que AÚN NO enviaron el reporte de HOY (anti-spam por check interno).
 */
class AlertaCobranzaVencimientoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        [$semanaNumero, $anio] = array_slice(ReporteCobranza::datosSemanActual(), 0, 2);

        // Usuarios sede activos
        $usuariosSede = User::role('sede')->where('is_active', true)->get();

        foreach ($usuariosSede as $usuario) {
            if (!$usuario->sede) continue;

            // Si ya envió su reporte esta semana, no alertar
            $yaEnvio = ReporteCobranza::where('sede', $usuario->sede)
                ->where('semana_numero', $semanaNumero)
                ->where('anio', $anio)
                ->whereNotNull('fecha_envio_original')
                ->exists();

            if ($yaEnvio) continue;

            // Obtener o crear el reporte (para pasar datos del límite)
            $reporte = ReporteCobranza::obtenerOCrearSemanaActual($usuario->id, $usuario->sede);

            Notification::send($usuario, new CobranzaAlertaVencimiento($reporte));
        }
    }
}
