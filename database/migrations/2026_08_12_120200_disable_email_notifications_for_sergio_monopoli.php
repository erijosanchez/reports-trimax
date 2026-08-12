<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A pedido explícito: Sergio Monopoli deja de recibir las notificaciones
 * por correo que le llegan hoy (Acuerdos Comerciales, Descuentos Especiales,
 * Desbloqueo, alertas de encuesta de Marketing — todas le llegan porque es
 * super_admin o porque su correo está en la lista de destinatarios de esos
 * módulos). Sigue recibiéndolas por la bandeja in-app nueva.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'smonopoli@trimaxperu.com')
            ->update(['email_notifications_enabled' => false]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'smonopoli@trimaxperu.com')
            ->update(['email_notifications_enabled' => true]);
    }
};
