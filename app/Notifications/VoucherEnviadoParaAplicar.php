<?php

namespace App\Notifications;

use App\Models\Voucher;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoucherEnviadoParaAplicar extends Notification
{
    public function __construct(protected Voucher $voucher) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $facturas = $this->voucher->facturas->map(
            fn($f) => "• {$f->factura}: S/ " . number_format($f->monto, 2)
        )->implode("\n");

        return (new MailMessage)
            ->subject("Nuevo Voucher Pendiente — {$this->voucher->codigo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se ha registrado un nuevo voucher pendiente de aplicación.")
            ->line("**Código:** {$this->voucher->codigo}")
            ->line("**Sede:** {$this->voucher->sede}")
            ->line("**Total:** S/ " . number_format($this->voucher->total, 2))
            ->line("**Solicitado:** " . $this->voucher->solicitado_at?->format('d/m/Y'))
            ->line("**Facturas incluidas:**")
            ->line($facturas)
            ->action('Ver en el sistema', url('/vouchers'))
            ->line("Por favor, revisa y aplica el voucher cuando sea posible.");
    }
}
