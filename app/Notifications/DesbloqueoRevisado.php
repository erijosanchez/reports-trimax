<?php

namespace App\Notifications;

use App\Models\SolicitudDesbloqueo;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DesbloqueoRevisado extends Notification
{
    public function __construct(protected SolicitudDesbloqueo $solicitud) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $s      = $this->solicitud;
        $estado = $s->revision_estado;

        $meta = [
            'conforme'           => ['emoji' => '✅', 'txt' => 'CONFORME (aprobada)'],
            'conforme_observado' => ['emoji' => '📝', 'txt' => 'CONFORME OBSERVADO'],
            'rechazado'          => ['emoji' => '⚠️', 'txt' => 'RECHAZADA'],
        ][$estado] ?? ['emoji' => 'ℹ️', 'txt' => strtoupper((string) $estado)];

        $mail = (new MailMessage)
            ->subject("{$meta['emoji']} Solicitud de desbloqueo {$meta['txt']} — {$s->razon_social}")
            ->greeting('Hola ' . ($notifiable->name ?? '') . ',')
            ->line("Tu solicitud de desbloqueo fue revisada por finanzas con el resultado: **{$meta['txt']}**.")
            ->line("**RUC:** {$s->ruc}")
            ->line("**Razón Social:** {$s->razon_social}");

        if (!empty($s->revision_motivo)) {
            $mail->line("**Motivo/observación:** {$s->revision_motivo}");
        }

        if ($estado === 'conforme_observado' && $s->revision_kpi_penalidad !== null) {
            $mail->line("**Penalización de KPI:** −" . number_format((float) $s->revision_kpi_penalidad, 0) . '%');
        }

        return $mail
            ->action('Ver en el sistema', url('/desbloqueo'));
    }
}
