<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DescuentoEspecial;

class DescuentoEspecialCreado extends Notification
{
    use Queueable;

    protected $descuento;

    public function __construct(DescuentoEspecial $descuento)
    {
        $this->descuento = $descuento;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nuevo Descuento Especial: ' . $this->descuento->numero_descuento)
            ->greeting('¡Hola!')
            ->line('Se ha creado un nuevo descuento especial.')
            ->line('**N° Descuento:** ' . $this->descuento->numero_descuento)
            ->line('**RUC:** ' . $this->descuento->ruc)
            ->line('**Razón Social:** ' . $this->descuento->razon_social)
            ->line('**Tipo:** ' . $this->descuento->tipo)
            ->line('**Sede:** ' . $this->descuento->sede)
            ->line('**Creado por:** ' . $this->descuento->creador->name)
            ->action('Ver Descuento', url('/comercial/descuentos-especiales'))
            ->line('Gracias por usar nuestro sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'descuento_id' => $this->descuento->id,
            'numero_descuento' => $this->descuento->numero_descuento,
            'mensaje' => 'Nuevo descuento especial creado'
        ];
    }
}
