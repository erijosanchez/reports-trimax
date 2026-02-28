<?php

namespace App\Notifications;

use App\Models\RequerimientoPersonal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequerimientoRhAsignado extends Notification
{
    use Queueable;

    public function __construct(public RequerimientoPersonal $requerimiento) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $req = $this->requerimiento;
        $url = route('rrhh.requerimientos.show', $req->id);

        return (new MailMessage)
            ->subject("👤 Has sido asignado como responsable - {$req->codigo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Has sido asignado como responsable del siguiente requerimiento de personal:")
            ->line("**Código:** {$req->codigo}")
            ->line("**Puesto:** {$req->puesto}")
            ->line("**Sede:** {$req->sede}")
            ->line("**Tipo:** {$req->tipo}")
            ->line("**Fecha de solicitud:** {$req->fecha_solicitud->format('d/m/Y')}")
            ->action('Ver Requerimiento', $url)
            ->line('CRM Trimax - Módulo RRHH');
    }
}
