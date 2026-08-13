<?php

namespace Tests\Feature;

use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoEnviado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bandeja de notificaciones in-app (2026-08-12). El campo `tipo` es lo que
 * usa el navbar para decidir si una notificación se muestra como modal
 * grande (avisos) o solo en la lista de la campanita.
 */
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_incluye_tipo_y_enviado_por(): void
    {
        $emisor = User::factory()->create(['name' => 'Erick Sánchez']);
        $usuario = User::factory()->create();

        $aviso = Aviso::create([
            'titulo' => 'Mantenimiento', 'mensaje' => 'El sistema estará caído', 'roles' => null,
            'user_id' => $emisor->id, 'total_destinatarios' => 1,
        ]);
        $usuario->notify(new AvisoEnviado($aviso));

        $response = $this->actingAs($usuario)->getJson(route('notificaciones.index'));

        $response->assertOk();
        $response->assertJsonPath('notificaciones.0.tipo', 'aviso');
        $response->assertJsonPath('notificaciones.0.titulo', 'Mantenimiento');
        $response->assertJsonPath('notificaciones.0.enviado_por', 'Erick Sánchez');
        $response->assertJsonPath('notificaciones.0.leida', false);
        $response->assertJsonPath('no_leidas', 1);
    }

    public function test_marcar_como_leida_actualiza_no_leidas(): void
    {
        $emisor = User::factory()->create();
        $usuario = User::factory()->create();
        $aviso = Aviso::create([
            'titulo' => 'Test', 'mensaje' => 'Test', 'roles' => null,
            'user_id' => $emisor->id, 'total_destinatarios' => 1,
        ]);
        $usuario->notify(new AvisoEnviado($aviso));
        $notifId = $usuario->notifications()->first()->id;

        $this->actingAs($usuario)
            ->postJson(route('notificaciones.leer', $notifId))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(0, $usuario->fresh()->unreadNotifications()->count());
    }
}
