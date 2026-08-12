<?php

namespace App\Notifications;

use App\Models\SolicitudDesbloqueo;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Concerns\RespetaPreferenciaCorreo;

class DesbloqueoCreado extends Notification
{
    use RespetaPreferenciaCorreo;

    public function __construct(protected SolicitudDesbloqueo $solicitud) {}

    public function toMail(object $notifiable): MailMessage
    {
        $s = $this->solicitud;

        $mail = (new MailMessage)
            ->subject("🔓 Nueva solicitud de desbloqueo — {$s->razon_social}")
            ->greeting('Hola,')
            ->line('Una sede registró una nueva solicitud de desbloqueo de cliente pendiente de revisión.')
            ->line("**Sede:** {$s->sede}")
            ->line("**Solicitante:** " . ($s->user?->name ?? '—'))
            ->line("**RUC:** {$s->ruc}")
            ->line("**Razón Social:** {$s->razon_social}");

        if (!empty($s->comentarios)) {
            $mail->line("**Comentarios:** {$s->comentarios}");
        }

        return $mail
            ->action('Revisar solicitud', url('/desbloqueo'))
            ->line('Recuerda que el tiempo de respuesta afecta el KPI de finanzas.');
    }

    public function toArray(object $notifiable): array
    {
        $s = $this->solicitud;

        return [
            'tipo' => 'desbloqueo_creado',
            'titulo' => 'Nueva Solicitud de Desbloqueo',
            'mensaje' => "{$s->razon_social} (RUC {$s->ruc}) — sede {$s->sede}",
            'url' => url('/desbloqueo'),
            'solicitud_id' => $s->id,
        ];
    }
}
