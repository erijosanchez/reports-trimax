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

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🚨 ALERTA SLA — {$this->requerimiento->codigo} lleva {$this->diasTranscurridos} días")
            ->view('emails.rrhh.requerimiento_alerta_sla', [
                'requerimiento'    => $this->requerimiento,
                'notifiable'       => $notifiable,
                'url'              => route('rrhh.requerimientos.show', $this->requerimiento->id),
                'diasTranscurridos' => $this->diasTranscurridos,
            ]);
    }
}
