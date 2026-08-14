<?php

namespace Tests\Feature;

use App\Models\Entrega;
use App\Models\GpsRuta;
use App\Models\Motorizado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cubre la asignación de múltiples órdenes a una entrega y el detalle
 * de entrega (tracking/entregas), agregados sobre el buscador de órdenes
 * reales existente (ver TrackingBuscarOrdenesTest).
 */
class TrackingEntregaOrdenesTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConPermiso(): User
    {
        return User::factory()->create(['puede_ver_motorizados' => true]);
    }

    private function motorizadoConRuta(): array
    {
        $motorizado = Motorizado::create([
            'nombre' => 'Juan Perez', 'sede' => 'LIMA', 'tipo' => 'motorizado',
            'email' => 'juan.' . fake()->unique()->numerify('####') . '@trimax.test',
            'password' => 'password123', 'estado' => 'activo',
        ]);

        $ruta = GpsRuta::create([
            'motorizado_id' => $motorizado->id, 'fecha' => today(), 'status' => 'activa',
        ]);

        return [$motorizado, $ruta];
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

    public function test_crea_entrega_con_multiples_ordenes(): void
    {
        [$motorizado, $ruta] = $this->motorizadoConRuta();

        $payload = [
            'motorizado_id'  => $motorizado->id,
            'ruta_id'        => $ruta->id,
            'cliente_nombre' => 'Cliente Uno',
            'direccion'      => 'Av. Siempre Viva 123',
            'sede'           => 'LIMA',
            'ordenes'        => [
                ['numero_orden' => 'ORD-5001', 'cliente' => 'Cliente Uno'],
                ['numero_orden' => 'ORD-5002', 'cliente' => 'Cliente Uno'],
            ],
        ];

        $response = $this->actingAs($this->usuarioConPermiso())
            ->postJson(route('tracking.entregas.store'), $payload)
            ->assertOk();

        $entregaId = $response->json('entrega.id');
        $entrega = Entrega::with('ordenes')->findOrFail($entregaId);

        $this->assertCount(2, $entrega->ordenes);
        $this->assertStringContainsString('ORD-5001', $entrega->referencia);
        $this->assertStringContainsString('ORD-5002', $entrega->referencia);
    }

    public function test_rechaza_orden_ya_ocupada_al_guardar(): void
    {
        [$motorizado, $ruta] = $this->motorizadoConRuta();

        $existente = Entrega::create([
            'motorizado_id' => $motorizado->id, 'ruta_id' => $ruta->id, 'cliente_nombre' => 'Ya asignado',
            'direccion' => 'Dir', 'orden_secuencia' => 1, 'estado' => 'pendiente', 'sede' => 'LIMA',
        ]);
        $existente->ordenes()->create(['numero_orden' => 'ORD-6001']);

        $payload = [
            'motorizado_id'  => $motorizado->id,
            'ruta_id'        => $ruta->id,
            'cliente_nombre' => 'Cliente Dos',
            'direccion'      => 'Otra dirección',
            'sede'           => 'LIMA',
            'ordenes'        => [
                ['numero_orden' => 'ORD-6001'],
            ],
        ];

        $this->actingAs($this->usuarioConPermiso())
            ->postJson(route('tracking.entregas.store'), $payload)
            ->assertStatus(422);

        $this->assertSame(1, Entrega::count());
    }

    public function test_detalle_de_entrega_incluye_sus_ordenes(): void
    {
        [$motorizado, $ruta] = $this->motorizadoConRuta();

        $entrega = Entrega::create([
            'motorizado_id' => $motorizado->id, 'ruta_id' => $ruta->id, 'cliente_nombre' => 'Cliente Tres',
            'direccion' => 'Dir', 'orden_secuencia' => 1, 'estado' => 'pendiente', 'sede' => 'LIMA',
        ]);
        $entrega->ordenes()->create(['numero_orden' => 'ORD-7001', 'cliente' => 'Cliente Tres']);
        $entrega->ordenes()->create(['numero_orden' => 'ORD-7002', 'cliente' => 'Cliente Tres']);

        $response = $this->actingAs($this->usuarioConPermiso())
            ->getJson(route('tracking.entregas.show', $entrega->id))
            ->assertOk();

        $numeros = collect($response->json('entrega.ordenes'))->pluck('numero_orden')->all();

        $this->assertCount(2, $numeros);
        $this->assertContains('ORD-7001', $numeros);
        $this->assertContains('ORD-7002', $numeros);
    }

    public function test_usuario_sin_permiso_no_ve_detalle(): void
    {
        [$motorizado, $ruta] = $this->motorizadoConRuta();

        $entrega = Entrega::create([
            'motorizado_id' => $motorizado->id, 'ruta_id' => $ruta->id, 'cliente_nombre' => 'Cliente Cuatro',
            'direccion' => 'Dir', 'orden_secuencia' => 1, 'estado' => 'pendiente', 'sede' => 'LIMA',
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('tracking.entregas.show', $entrega->id))
            ->assertForbidden();
    }
}
