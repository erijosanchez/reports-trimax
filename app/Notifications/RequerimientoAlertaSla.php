<?php

namespace App\Notifications;

use App\Models\RequerimientoPersonal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequerimientoAlertaSla extends Notification
{
    use Queueable;

    public function __construct(
        public RequerimientoPersonal $requerimiento,
        public int $diasTranscurridos
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $req          = $this->requerimiento;
        $url          = route('rrhh.requerimientos.show', $req->id);
        $diasExcedidos = $this->diasTranscurridos - 45;

        return (new MailMessage)
            ->subject("🚨 ALERTA SLA - {$req->codigo}: {$this->diasTranscurridos} días sin cerrar")
            ->greeting("Atención {$notifiable->name},")
            ->line("⚠️ El siguiente requerimiento ha **superado el SLA de 45 días** y continúa En Proceso.")
            ->line("**Código:** {$req->codigo}")
            ->line("**Puesto:** {$req->puesto}")
            ->line("**Sede:** {$req->sede}")
            ->line("**Solicitante:** {$req->solicitante->name}")
            ->line("**Días transcurridos:** {$this->diasTranscurridos} días (+{$diasExcedidos} sobre el SLA)")
            ->line("**Fecha de solicitud:** {$req->fecha_solicitud->format('d/m/Y')}")
            ->action('Ver y Gestionar', $url)
            ->line('Este correo se enviará diariamente hasta que el requerimiento sea Contratado o Cancelado.');
    }
}

