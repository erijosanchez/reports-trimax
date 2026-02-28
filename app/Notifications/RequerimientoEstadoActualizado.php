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
        $req = $this->requerimiento;
        $url = route('rrhh.requerimientos.show', $req->id);

        $asunto = match ($this->tipo) {
            'cambio_estado' => "🔄 Estado Actualizado - {$req->codigo}: {$this->estadoNuevo}",
            'asignacion_rh' => "👤 Responsable RH Asignado - {$req->codigo}",
            'nota'          => "💬 Nueva nota en Requerimiento - {$req->codigo}",
            default         => "Actualización - {$req->codigo}",
        };

        $mail = (new MailMessage)
            ->subject($asunto)
            ->greeting("Hola {$notifiable->name},")
            ->line("**Código:** {$req->codigo} | **Puesto:** {$req->puesto} | **Sede:** {$req->sede}");

        match ($this->tipo) {
            'cambio_estado' => $mail->line("El estado cambió de **{$this->estadoAnterior}** a **{$this->estadoNuevo}**."),
            'asignacion_rh' => $mail->line("Se asignó a **{$this->extra}** como responsable RH."),
            'nota'          => $mail->line("RRHH agregó la siguiente nota:")->line("> {$this->extra}"),
            default         => null,
        };

        return $mail->action('Ver Detalle', $url)->line('CRM Trimax - Módulo RRHH');
    }
}
