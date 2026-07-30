<?php

namespace Tests\Feature;

use App\Models\DescuentoEspecial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Cubre A5 (ARQUITECTURA.md): DescuentosEspecialesController tenía 9
 * validaciones inline, varias repetidas letra por letra entre métodos
 * (crearDescuento/editarDescuento comparten ~15 campos; aplicarDescuento/
 * aprobarDescuento comparten 'accion'; deshabilitarDescuento/
 * rehabilitarDescuento comparten 'motivo'; cambiarAplicacion/
 * cambiarAprobacion comparten 'nuevo_estado'). Se extrajeron a Form
 * Requests. Efecto colateral deseado: antes, una validación fallida caía
 * dentro del try/catch del controlador y salía como 500 genérico; al mover
 * la validación al Form Request (se resuelve antes del cuerpo del método),
 * ahora es un 422 estándar de Laravel con errores por campo.
 */
class DescuentoEspecialFormRequestTest extends TestCase
{
    use RefreshDatabase;

    private function datosDescuentoValidos(array $overrides = []): array
    {
        return array_merge([
            'sede' => 'Lima',
            'ruc' => '20123456789',
            'razon_social' => 'Cliente SAC',
            'consultor' => 'Juan Pérez',
            'ciudad' => 'Lima',
            'descuento_especial' => '10%',
            'tipo' => 'DESCUENTO ADICIONAL',
            'marca' => 'MarcaX',
            'comentarios' => 'Justificación del descuento',
        ], $overrides);
    }

    public function test_crear_descuento_con_datos_validos_responde_ok(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('comercial.descuentos.crear'), $this->datosDescuentoValidos());

        $response->assertOk();
        $this->assertDatabaseHas('descuentos_especiales', ['ruc' => '20123456789']);
    }

    public function test_crear_descuento_sin_campos_requeridos_da_422_no_500(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('comercial.descuentos.crear'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['sede', 'ruc', 'razon_social', 'comentarios']);
    }

    public function test_editar_descuento_acepta_comentarios_nulo_a_diferencia_de_crear(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $descuento = DescuentoEspecial::create(array_merge($this->datosDescuentoValidos(), [
            'numero_descuento' => DescuentoEspecial::generarNumeroDescuento(),
            'user_id' => $user->id,
        ]));

        $datos = $this->datosDescuentoValidos(['comentarios' => null]);

        $this->actingAs($user)
            ->putJson(route('comercial.descuentos.editar', ['id' => $descuento->id]), $datos)
            ->assertOk();
    }

    public function test_aplicar_y_aprobar_descuento_comparten_la_validacion_de_accion(): void
    {
        Notification::fake();
        $auditorJunior = User::factory()->create(['email' => 'auditor.junior@trimaxperu.com']);
        $sergio = User::factory()->create(['email' => 'smonopoli@trimaxperu.com']);
        $creador = User::factory()->create();

        $descuento = DescuentoEspecial::create(array_merge($this->datosDescuentoValidos(), [
            'numero_descuento' => DescuentoEspecial::generarNumeroDescuento(),
            'user_id' => $creador->id,
        ]));

        $this->actingAs($auditorJunior)
            ->postJson(route('comercial.descuentos.aplicar', ['id' => $descuento->id]), ['accion' => 'no-es-valido'])
            ->assertStatus(422);

        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.aprobar', ['id' => $descuento->id]), ['accion' => 'no-es-valido'])
            ->assertStatus(422);

        $this->actingAs($auditorJunior)
            ->postJson(route('comercial.descuentos.aplicar', ['id' => $descuento->id]), ['accion' => 'Aprobado'])
            ->assertOk();
    }

    public function test_deshabilitar_y_rehabilitar_descuento_comparten_la_validacion_de_motivo(): void
    {
        Notification::fake();
        $sergio = User::factory()->create(['email' => 'smonopoli@trimaxperu.com']);
        $creador = User::factory()->create();

        $descuento = DescuentoEspecial::create(array_merge($this->datosDescuentoValidos(), [
            'numero_descuento' => DescuentoEspecial::generarNumeroDescuento(),
            'user_id' => $creador->id,
        ]));

        // Motivo corto (< 10 caracteres) debe rechazarse en ambos endpoints.
        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.deshabilitar', ['id' => $descuento->id]), ['motivo' => 'corto'])
            ->assertStatus(422);

        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.deshabilitar', ['id' => $descuento->id]), ['motivo' => 'Motivo suficientemente largo'])
            ->assertOk();

        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.rehabilitar', ['id' => $descuento->id]), ['motivo' => 'corto'])
            ->assertStatus(422);

        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.rehabilitar', ['id' => $descuento->id]), ['motivo' => 'Motivo suficientemente largo'])
            ->assertOk();
    }

    public function test_cambiar_aplicacion_y_aprobacion_comparten_la_validacion_de_nuevo_estado(): void
    {
        Notification::fake();
        $auditorJunior = User::factory()->create(['email' => 'auditor.junior@trimaxperu.com']);
        $sergio = User::factory()->create(['email' => 'smonopoli@trimaxperu.com']);
        $creador = User::factory()->create();

        $descuento = DescuentoEspecial::create(array_merge($this->datosDescuentoValidos(), [
            'numero_descuento' => DescuentoEspecial::generarNumeroDescuento(),
            'user_id' => $creador->id,
        ]));

        $this->actingAs($auditorJunior)
            ->postJson(route('comercial.descuentos.cambiar-aplicacion', ['id' => $descuento->id]), ['nuevo_estado' => 'no-es-valido'])
            ->assertStatus(422);

        $this->actingAs($sergio)
            ->postJson(route('comercial.descuentos.cambiar-aprobacion', ['id' => $descuento->id]), ['nuevo_estado' => 'no-es-valido'])
            ->assertStatus(422);

        $this->actingAs($auditorJunior)
            ->postJson(route('comercial.descuentos.cambiar-aplicacion', ['id' => $descuento->id]), ['nuevo_estado' => 'Pendiente'])
            ->assertOk();
    }
}
