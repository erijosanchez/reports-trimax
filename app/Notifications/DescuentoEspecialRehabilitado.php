<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DescuentoEspecial;

class DescuentoEspecialRehabilitado extends Notification
{
    use Queueable;

    protected $descuento;
    protected $motivo;

    public function __construct(DescuentoEspecial $descuento, $motivo)
    {
        $this->descuento = $descuento;
        $this->motivo = $motivo;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Descuento Especial Rehabilitado: ' . $this->descuento->numero_descuento)
            ->greeting('¡Hola!')
            ->line('Se ha rehabilitado un descuento especial.')
            ->line('**N° Descuento:** ' . $this->descuento->numero_descuento)
            ->line('**RUC:** ' . $this->descuento->ruc)
            ->line('**Razón Social:** ' . $this->descuento->razon_social)
            ->line('**Motivo:** ' . $this->motivo)
            ->line('**Rehabilitado por:** ' . ($this->descuento->rehabilitador ? $this->descuento->rehabilitador->name : '-'))
            ->action('Ver Descuento', url('/comercial/descuentos-especiales'))
            ->line('El descuento está nuevamente disponible.');
    }

    public function toArray($notifiable)
    {
        return [
            'descuento_id' => $this->descuento->id,
            'numero_descuento' => $this->descuento->numero_descuento,
            'mensaje' => 'Descuento especial rehabilitado',
            'motivo' => $this->motivo
        ];
    }
}
