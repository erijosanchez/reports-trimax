<?php

namespace Tests\Feature;

use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoEnviado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Avisos manuales dentro del sistema (2026-08-12): un usuario con permiso
 * (asignable por super_admin desde el panel de usuarios) redacta un aviso y
 * elige a qué roles llega, o a todos. Se entrega solo por la bandeja in-app
 * — nunca por correo, es "dentro del sistema".
 */
class AvisoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('sede', 'web');
        Role::findOrCreate('consultor', 'web');
        Role::findOrCreate('finanzas', 'web');
    }

    public function test_usuario_sin_permiso_no_puede_acceder_al_modulo(): void
    {
        $user = User::factory()->create(['puede_enviar_avisos' => false]);

        $this->actingAs($user)->get(route('avisos.index'))->assertForbidden();
        $this->actingAs($user)->postJson(route('avisos.store'), [
            'titulo' => 'Prueba',
            'mensaje' => 'Mensaje de prueba',
        ])->assertForbidden();
    }

    public function test_super_admin_siempre_tiene_el_permiso_aunque_el_flag_este_apagado(): void
    {
        $admin = User::factory()->create([
            'puede_enviar_avisos'     => false,
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->get(route('avisos.index'))
            ->assertOk();
    }

    public function test_usuario_con_permiso_puede_enviar_aviso_a_todos(): void
    {
        Notification::fake();

        $emisor = User::factory()->create(['puede_enviar_avisos' => true]);
        $sede = User::factory()->create(['is_active' => true]);
        $sede->assignRole('sede');
        $consultor = User::factory()->create(['is_active' => true]);
        $consultor->assignRole('consultor');
        $inactivo = User::factory()->create(['is_active' => false]);
        $inactivo->assignRole('sede');

        $response = $this->actingAs($emisor)->post(route('avisos.store'), [
            'titulo'  => 'Corte de sistema',
            'mensaje' => 'El sistema estará en mantenimiento el sábado.',
            'todos'   => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('avisos', [
            'titulo'  => 'Corte de sistema',
            'user_id' => $emisor->id,
            'roles'   => null,
        ]);

        $aviso = Aviso::first();
        // Todos los usuarios ACTIVOS: emisor + sede + consultor (el inactivo no).
        $this->assertSame(3, $aviso->total_destinatarios);

        Notification::assertSentTo($sede, AvisoEnviado::class);
        Notification::assertSentTo($consultor, AvisoEnviado::class);
        Notification::assertNotSentTo($inactivo, AvisoEnviado::class);
    }

    public function test_usuario_con_permiso_puede_enviar_aviso_solo_a_roles_especificos(): void
    {
        Notification::fake();

        $emisor = User::factory()->create(['puede_enviar_avisos' => true]);
        $sede = User::factory()->create(['is_active' => true]);
        $sede->assignRole('sede');
        $finanzas = User::factory()->create(['is_active' => true]);
        $finanzas->assignRole('finanzas');

        $this->actingAs($emisor)->post(route('avisos.store'), [
            'titulo'  => 'Nueva política de cobranza',
            'mensaje' => 'A partir de mañana...',
            'roles'   => ['finanzas'],
        ]);

        $this->assertDatabaseHas('avisos', ['roles' => json_encode(['finanzas'])]);
        Notification::assertSentTo($finanzas, AvisoEnviado::class);
        Notification::assertNotSentTo($sede, AvisoEnviado::class);
    }

    public function test_el_aviso_solo_usa_el_canal_database_nunca_correo(): void
    {
        $creador = User::factory()->create();
        $aviso = Aviso::create([
            'titulo' => 'Test', 'mensaje' => 'Test', 'roles' => null, 'user_id' => $creador->id, 'total_destinatarios' => 0,
        ]);
        $notification = new AvisoEnviado($aviso);
        $destinatario = User::factory()->make();

        $this->assertSame(['database'], $notification->via($destinatario));
    }

    public function test_la_notificacion_in_app_queda_guardada_de_verdad(): void
    {
        $emisor = User::factory()->create(['puede_enviar_avisos' => true]);
        $sede = User::factory()->create(['is_active' => true]);
        $sede->assignRole('sede');

        $this->actingAs($emisor)->post(route('avisos.store'), [
            'titulo'  => 'Aviso real',
            'mensaje' => 'Contenido real',
            'roles'   => ['sede'],
        ]);

        $this->assertSame(1, $sede->notifications()->count());
        $this->assertSame('aviso', $sede->notifications()->first()->data['tipo']);
        $this->assertSame('Aviso real', $sede->notifications()->first()->data['titulo']);
    }

    public function test_solo_el_dueno_o_super_admin_puede_eliminar_un_aviso(): void
    {
        $emisor = User::factory()->create(['puede_enviar_avisos' => true]);
        $otro = User::factory()->create(['puede_enviar_avisos' => true]);
        $aviso = Aviso::create([
            'titulo' => 'Test', 'mensaje' => 'Test', 'roles' => null,
            'user_id' => $emisor->id, 'total_destinatarios' => 0,
        ]);

        $this->actingAs($otro)->delete(route('avisos.destroy', $aviso->id))->assertForbidden();
        $this->assertDatabaseHas('avisos', ['id' => $aviso->id]);

        $this->actingAs($emisor)->delete(route('avisos.destroy', $aviso->id))->assertRedirect();
        $this->assertDatabaseMissing('avisos', ['id' => $aviso->id]);
    }
}
