<?php

namespace App\Notifications;

use App\Models\RequerimientoPersonal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RequerimientoEstadoActualizado extends Notification
{
    use Queueable;

    public function __construct(
        public RequerimientoPersonal $requerimiento,
        public ?string $estadoAnterior,
        public ?string $estadoNuevo,
        public string  $tipo  = 'cambio_estado',
        public ?string $extra = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $asunto = match ($this->tipo) {
            'cambio_estado' => "🔄 Estado actualizado a {$this->estadoNuevo} — {$this->requerimiento->codigo}",
            'asignacion_rh' => "👤 Responsable RH asignado — {$this->requerimiento->codigo}",
            'etapa'         => "📌 Nuevo avance en proceso — {$this->requerimiento->codigo}",
            default         => "Actualización — {$this->requerimiento->codigo}",
        };

        return (new MailMessage)
            ->subject($asunto)
            ->view('emails.rrhh.requerimiento_estado', [
                'requerimiento'  => $this->requerimiento,
                'notifiable'     => $notifiable,
                'url'            => route('rrhh.requerimientos.show', $this->requerimiento->id),
                'tipo'           => $this->tipo,
                'estadoAnterior' => $this->estadoAnterior,
                'estadoNuevo'    => $this->estadoNuevo,
                'extra'          => $this->extra,
            ]);
    }
}
