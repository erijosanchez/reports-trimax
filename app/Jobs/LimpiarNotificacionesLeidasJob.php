<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Se ejecuta diariamente. Borra notificaciones in-app ya LEÍDAS con más de
 * 3 días — evita que la tabla `notifications` crezca sin control. Las no
 * leídas nunca se tocan aquí, sin importar la antigüedad, para no perder
 * algo que el usuario todavía no vio.
 */
class LimpiarNotificacionesLeidasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        DatabaseNotification::whereNotNull('read_at')
            ->where('read_at', '<', Carbon::now('America/Lima')->subDays(3))
            ->delete();
    }
}
