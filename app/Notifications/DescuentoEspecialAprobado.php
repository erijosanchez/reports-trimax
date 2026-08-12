<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DescuentoEspecial;
use App\Notifications\Concerns\RespetaPreferenciaCorreo;

class DescuentoEspecialAprobado extends Notification
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
            ->subject('Descuento Especial Aprobado: ' . $this->descuento->numero_descuento)
            ->greeting('¡Hola!')
            ->line('El descuento especial ha sido completamente aprobado.')
            ->line('**N° Descuento:** ' . $this->descuento->numero_descuento)
            ->line('**RUC:** ' . $this->descuento->ruc)
            ->line('**Razón Social:** ' . $this->descuento->razon_social)
            ->line('**Tipo:** ' . $this->descuento->tipo)
            ->line('**Detalle del Descuento:** ' . $this->descuento->descuento_especial)
            ->line('**Validado por:** ' . ($this->descuento->validador ? $this->descuento->validador->name : '-'))
            ->line('**Aprobado por:** ' . ($this->descuento->aprobador ? $this->descuento->aprobador->name : '-'))
            ->action('Ver Descuento', url('/comercial/descuentos-especiales'))
            ->line('El descuento ya puede ser aplicado.');
    }

    public function toArray($notifiable)
    {
        return [
            'tipo' => 'descuento_especial_aprobado',
            'titulo' => 'Descuento Especial Aprobado',
            'mensaje' => "Descuento {$this->descuento->numero_descuento} — {$this->descuento->razon_social}",
            'url' => url('/comercial/descuentos-especiales'),
            'descuento_id' => $this->descuento->id,
            'numero_descuento' => $this->descuento->numero_descuento,
        ];
    }
}
