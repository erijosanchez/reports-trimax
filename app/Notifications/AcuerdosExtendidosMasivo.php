<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use App\Notifications\Concerns\RespetaPreferenciaCorreo;

class AcuerdosExtendidosMasivo extends Notification
{
    use Queueable, RespetaPreferenciaCorreo;

    public function __construct(
        private Collection $acuerdos,
        private string $motivo,
        private string $nuevaFecha,
        private string $extendidoPor,
        private bool $esAdmin = false
    ) {}

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

    public function toArray($notifiable): array
    {
        $total = $this->acuerdos->count();

        return [
            'tipo' => 'acuerdos_extendidos_masivo',
            'titulo' => $this->esAdmin
                ? "Resumen: {$total} acuerdo(s) extendidos masivamente"
                : "Tus acuerdos comerciales fueron extendidos ({$total})",
            'mensaje' => "Nueva fecha de fin: {$this->nuevaFecha} — extendido por {$this->extendidoPor}",
            'url' => url('/comercial/acuerdos'),
            'total_acuerdos' => $total,
            'nueva_fecha' => $this->nuevaFecha,
            'motivo' => $this->motivo,
        ];
    }
}
