<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cubre el buscador de órdenes reales (ordenes_historico) usado para
 * autocompletar la "referencia" al crear una entrega en tracking/entregas.
 */
class TrackingBuscarOrdenesTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConPermiso(): User
    {
        return User::factory()->create(['puede_ver_motorizados' => true]);
    }

    private function orden(array $overrides = []): void
    {
        DB::table('ordenes_historico')->insert(array_merge([
            'numero_orden'     => 'ORD-' . fake()->unique()->numerify('####'),
            'descripcion_sede' => 'Lima',
            'cliente'          => 'Cliente de prueba',
            'estado_orden'     => 'En proceso',
            'ubicacion_orden'  => 'EN SEDE',
            'fecha_orden'      => now()->toDateString(),
        ], $overrides));
    }

    public function test_filtra_ordenes_por_sede(): void
    {
        $this->orden(['numero_orden' => 'ORD-1001', 'descripcion_sede' => 'Lima']);
        $this->orden(['numero_orden' => 'ORD-1002', 'descripcion_sede' => 'Trujillo']);

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson(route('tracking.ordenes.buscar', ['sede' => 'Lima', 'q' => 'ORD-100']))
            ->assertOk();

        $numeros = collect($response->json())->pluck('numero_orden')->all();

        $this->assertSame(['ORD-1001'], $numeros);
    }

    public function test_excluye_ordenes_anuladas_y_ya_entregadas(): void
    {
        $this->orden(['numero_orden' => 'ORD-2001', 'estado_orden' => 'En proceso', 'ubicacion_orden' => 'EN SEDE']);
        $this->orden(['numero_orden' => 'ORD-2002', 'estado_orden' => 'Anulado', 'ubicacion_orden' => 'EN SEDE']);
        $this->orden(['numero_orden' => 'ORD-2003', 'estado_orden' => 'En proceso', 'ubicacion_orden' => 'FACTURADO Y ENTREGADO']);

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson(route('tracking.ordenes.buscar', ['sede' => 'Lima', 'q' => 'ORD-200']))
            ->assertOk();

        $numeros = collect($response->json())->pluck('numero_orden')->all();

        $this->assertSame(['ORD-2001'], $numeros);
    }

    public function test_excluye_ordenes_con_entrega_pendiente_o_completada_pero_incluye_las_de_entrega_fallida(): void
    {
        $this->orden(['numero_orden' => 'ORD-3001']);
        $this->orden(['numero_orden' => 'ORD-3002']);
        $this->orden(['numero_orden' => 'ORD-3003']);

        Entrega::create([
            'motorizado_id' => 1, 'ruta_id' => 1, 'cliente_nombre' => 'A',
            'referencia' => 'ORD-3001', 'direccion' => 'Dir', 'orden_secuencia' => 1,
            'estado' => 'pendiente', 'sede' => 'Lima',
        ]);
        Entrega::create([
            'motorizado_id' => 1, 'ruta_id' => 1, 'cliente_nombre' => 'B',
            'referencia' => 'ORD-3002', 'direccion' => 'Dir', 'orden_secuencia' => 2,
            'estado' => 'completado', 'sede' => 'Lima',
        ]);
        Entrega::create([
            'motorizado_id' => 1, 'ruta_id' => 1, 'cliente_nombre' => 'C',
            'referencia' => 'ORD-3003', 'direccion' => 'Dir', 'orden_secuencia' => 3,
            'estado' => 'fallido', 'sede' => 'Lima',
        ]);

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson(route('tracking.ordenes.buscar', ['sede' => 'Lima', 'q' => 'ORD-300']))
            ->assertOk();

        $numeros = collect($response->json())->pluck('numero_orden')->all();

        $this->assertSame(['ORD-3003'], $numeros);
    }

    public function test_usuario_sin_permiso_no_accede(): void
    {
        $this->orden(['numero_orden' => 'ORD-4001']);

        $this->actingAs(User::factory()->create())
            ->getJson(route('tracking.ordenes.buscar', ['sede' => 'Lima', 'q' => 'ORD-400']))
            ->assertForbidden();
    }
}
