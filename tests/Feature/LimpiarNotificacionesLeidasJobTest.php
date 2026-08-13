<?php

namespace Tests\Feature;

use App\Jobs\LimpiarNotificacionesLeidasJob;
use App\Models\Aviso;
use App\Models\User;
use App\Notifications\AvisoEnviado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Carbon;
use Tests\TestCase;

/**
 * Limpieza de notificaciones leídas con más de 3 días (2026-08-12) — evita
 * que la tabla `notifications` crezca sin control. Las no leídas nunca se
 * tocan, sin importar la antigüedad.
 */
class LimpiarNotificacionesLeidasJobTest extends TestCase
{
    use RefreshDatabase;

    private function crearNotificacion(User $usuario): string
    {
        $emisor = User::factory()->create();
        $aviso = Aviso::create([
            'titulo' => 'Test', 'mensaje' => 'Test', 'roles' => null,
            'user_id' => $emisor->id, 'total_destinatarios' => 1,
        ]);
        $usuario->notify(new AvisoEnviado($aviso));

        return $usuario->notifications()->latest()->first()->id;
    }

    public function test_borra_leidas_con_mas_de_3_dias(): void
    {
        $usuario = User::factory()->create();
        $id = $this->crearNotificacion($usuario);
        $usuario->notifications()->where('id', $id)->update(['read_at' => now()->subDays(4)]);

        (new LimpiarNotificacionesLeidasJob())->handle();

        $this->assertSame(0, $usuario->notifications()->count());
    }

    public function test_no_borra_leidas_con_menos_de_3_dias(): void
    {
        $usuario = User::factory()->create();
        $id = $this->crearNotificacion($usuario);
        $usuario->notifications()->where('id', $id)->update(['read_at' => now()->subDays(1)]);

        (new LimpiarNotificacionesLeidasJob())->handle();

        $this->assertSame(1, $usuario->notifications()->count());
    }

    public function test_no_borra_no_leidas_sin_importar_la_antiguedad(): void
    {
        $usuario = User::factory()->create();
        $id = $this->crearNotificacion($usuario);
        $usuario->notifications()->where('id', $id)->update(['created_at' => now()->subDays(30)]);

        (new LimpiarNotificacionesLeidasJob())->handle();

        $this->assertSame(1, $usuario->notifications()->count());
    }
}
