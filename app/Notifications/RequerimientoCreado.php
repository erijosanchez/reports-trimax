<?php

namespace App\Notifications;

use App\Models\RequerimientoPersonal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequerimientoCreado extends Notification
{
    use Queueable;

    public function __construct(public RequerimientoPersonal $requerimiento) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("📋 Nuevo Requerimiento - {$this->requerimiento->codigo} | {$this->requerimiento->puesto}")
            ->view('emails.rrhh.requerimiento_creado', [
                'requerimiento' => $this->requerimiento,
                'notifiable'    => $notifiable,
                'url'           => route('rrhh.requerimientos.show', $this->requerimiento->id),
            ]);
    }
}
