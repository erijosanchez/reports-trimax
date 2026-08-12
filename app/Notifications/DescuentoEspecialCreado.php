<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DescuentoEspecial;
use App\Notifications\Concerns\RespetaPreferenciaCorreo;

class DescuentoEspecialCreado extends Notification
{
    use Queueable, RespetaPreferenciaCorreo;

    protected $descuento;

    public function __construct(DescuentoEspecial $descuento)
    {
        $this->descuento = $descuento;
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
            ->line('**Detalle del Descuento:** ' . $this->descuento->descuento_especial)
            ->line('**Creado por:** ' . $this->descuento->creador->name)
            ->action('Ver Descuento', url('/comercial/descuentos-especiales'))
            ->line('Gracias por usar nuestro sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'tipo' => 'descuento_especial_creado',
            'titulo' => 'Nuevo Descuento Especial',
            'mensaje' => "Descuento {$this->descuento->numero_descuento} — {$this->descuento->razon_social}",
            'url' => url('/comercial/descuentos-especiales'),
            'descuento_id' => $this->descuento->id,
            'numero_descuento' => $this->descuento->numero_descuento,
        ];
    }
}
