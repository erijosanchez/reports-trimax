<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class AcuerdosExtendidosMasivo extends Notification
{
    use Queueable;

    public function __construct(
        private Collection $acuerdos,
        private string $motivo,
        private string $nuevaFecha,
        private string $extendidoPor,
        private bool $esAdmin = false
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $total = $this->acuerdos->count();
        $subject = $this->esAdmin
            ? "📅 Resumen: {$total} acuerdo(s) extendidos masivamente"
            : "📅 Tus acuerdos comerciales han sido extendidos ({$total})";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.comercial.acuerdos_extendidos_masivo', [
                'acuerdos'     => $this->acuerdos,
                'motivo'       => $this->motivo,
                'nuevaFecha'   => $this->nuevaFecha,
                'extendidoPor' => $this->extendidoPor,
                'esAdmin'      => $this->esAdmin,
                'destinatario' => $notifiable->name,
            ]);
    }
}
