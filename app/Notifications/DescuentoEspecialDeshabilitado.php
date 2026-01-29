<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DescuentoEspecial;

class DescuentoEspecialDeshabilitado extends Notification
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
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Descuento Especial Deshabilitado: ' . $this->descuento->numero_descuento)
            ->greeting('¡Hola!')
            ->line('Se ha deshabilitado un descuento especial.')
            ->line('**N° Descuento:** ' . $this->descuento->numero_descuento)
            ->line('**RUC:** ' . $this->descuento->ruc)
            ->line('**Razón Social:** ' . $this->descuento->razon_social)
            ->line('**Motivo:** ' . $this->motivo)
            ->line('**Deshabilitado por:** ' . ($this->descuento->deshabilitador ? $this->descuento->deshabilitador->name : '-'))
            ->action('Ver Descuento', url('/comercial/descuentos-especiales'))
            ->line('El descuento ya no está disponible.');
    }

    public function toArray($notifiable)
    {
        return [
            'descuento_id' => $this->descuento->id,
            'numero_descuento' => $this->descuento->numero_descuento,
            'mensaje' => 'Descuento especial deshabilitado',
            'motivo' => $this->motivo
        ];
    }
}
