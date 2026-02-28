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

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $req = $this->requerimiento;
        $url = route('rrhh.requerimientos.show', $req->id);

        return (new MailMessage)
            ->subject("📋 Nuevo Requerimiento de Personal - {$req->codigo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se ha registrado un nuevo requerimiento de personal.")
            ->line("**Código:** {$req->codigo}")
            ->line("**Puesto:** {$req->puesto}")
            ->line("**Sede:** {$req->sede}")
            ->line("**Jefe Directo:** {$req->jefe_directo}")
            ->line("**Tipo:** {$req->tipo}")
            ->line("**Solicitante:** {$req->solicitante->name}")
            ->line("**Fecha:** {$req->fecha_solicitud->format('d/m/Y H:i')}")
            ->when($req->condiciones_oferta, fn($m) => $m->line("**Condiciones:** {$req->condiciones_oferta}"))
            ->when($req->comentarios, fn($m) => $m->line("**Comentarios:** {$req->comentarios}"))
            ->action('Ver Requerimiento', $url)
            ->line('CRM Trimax - Módulo RRHH');
    }
}
