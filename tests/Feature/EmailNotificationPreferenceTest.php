<?php

namespace Tests\Feature;

use App\Models\SolicitudDesbloqueo;
use App\Models\User;
use App\Notifications\DesbloqueoCreado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Preferencia de correo por usuario (2026-08-12): un usuario puede apagar el
 * correo de las notificaciones del sistema sin dejar de recibirlas del todo
 * — siguen llegando por la bandeja in-app (canal "database"). Se prueba con
 * DesbloqueoCreado como representante de las notificaciones que usan el
 * trait RespetaPreferenciaCorreo.
 *
 * Nota: las notificaciones de este CRM usan MailMessage (no Mailable), así
 * que Mail::fake()/assertSent no las detecta — Notification::fake() sí, vía
 * el segundo argumento de assertSentTo (los canales que via() devolvió).
 */
class EmailNotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    private function crearSolicitud(): SolicitudDesbloqueo
    {
        $sede = User::factory()->create();

        return SolicitudDesbloqueo::create([
            'user_id'      => $sede->id,
            'sede'         => 'Arequipa',
            'ruc'          => '20123456789',
            'razon_social' => 'Cliente Test SAC',
        ]);
    }

    public function test_via_devuelve_solo_database_cuando_el_correo_esta_apagado(): void
    {
        $solicitud = $this->crearSolicitud();
        $notificacion = new DesbloqueoCreado($solicitud);

        $usuarioSinCorreo = User::factory()->make(['email_notifications_enabled' => false]);
        $usuarioConCorreo = User::factory()->make(['email_notifications_enabled' => true]);

        $this->assertSame(['database'], $notificacion->via($usuarioSinCorreo));
        $this->assertSame(['mail', 'database'], $notificacion->via($usuarioConCorreo));
    }

    public function test_usuario_sin_preferencia_definida_recibe_correo_por_defecto(): void
    {
        // Simula el estado antes de correr la migración (columna sin tocar
        // en memoria, sin persistir) — debe comportarse como si el correo
        // estuviera activado (opt-out, no opt-in), nunca silenciar a todos
        // por accidente mientras la migración no se ha corrido.
        $usuario = User::factory()->make(['email_notifications_enabled' => null]);

        $this->assertTrue($usuario->wantsEmailNotifications());
        $this->assertSame(['mail', 'database'], (new DesbloqueoCreado($this->crearSolicitud()))->via($usuario));
    }

    public function test_notificacion_a_usuario_con_correo_desactivado_solo_usa_canal_database(): void
    {
        Notification::fake();
        $usuario = User::factory()->create(['email_notifications_enabled' => false]);
        $solicitud = $this->crearSolicitud();

        $usuario->notify(new DesbloqueoCreado($solicitud));

        Notification::assertSentTo(
            $usuario,
            DesbloqueoCreado::class,
            fn($notification, $channels) => $channels === ['database']
        );
    }

    public function test_notificacion_a_usuario_con_correo_activado_usa_mail_y_database(): void
    {
        Notification::fake();
        $usuario = User::factory()->create(['email_notifications_enabled' => true]);
        $solicitud = $this->crearSolicitud();

        $usuario->notify(new DesbloqueoCreado($solicitud));

        Notification::assertSentTo(
            $usuario,
            DesbloqueoCreado::class,
            fn($notification, $channels) => $channels === ['mail', 'database']
        );
    }

    public function test_la_notificacion_in_app_queda_guardada_de_verdad_cuando_el_correo_esta_apagado(): void
    {
        // Sin fake: se ejecuta el canal "database" real para confirmar que
        // la bandeja in-app efectivamente recibe la notificación, no solo
        // que via() diga que debería.
        $usuario = User::factory()->create(['email_notifications_enabled' => false]);
        $solicitud = $this->crearSolicitud();

        $usuario->notify(new DesbloqueoCreado($solicitud));

        $this->assertSame(1, $usuario->notifications()->count());
        $this->assertSame(1, $usuario->unreadNotifications()->count());
        $this->assertSame('desbloqueo_creado', $usuario->notifications()->first()->data['tipo']);
    }
}
