<?php

namespace App\Notifications;

use App\Models\Aviso;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Aviso manual "dentro del sistema" — a propósito solo usa el canal
 * database, nunca correo (a diferencia de las notificaciones automáticas de
 * eventos de negocio, que sí usan RespetaPreferenciaCorreo).
 */
class AvisoEnviado extends Notification
{
    use Queueable;

    public function __construct(protected Aviso $aviso) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo'      => 'aviso',
            'titulo'    => $this->aviso->titulo,
            'mensaje'   => $this->aviso->mensaje,
            'url'       => null,
            'aviso_id'  => $this->aviso->id,
            'enviado_por' => $this->aviso->creador?->name,
        ];
    }
}
