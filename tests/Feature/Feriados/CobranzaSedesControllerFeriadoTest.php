<?php

namespace Tests\Feature\Feriados;

use App\Models\Feriado;
use App\Models\ReporteCobranza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre el bloqueo de envío en CobranzaSedesController cuando hoy es feriado:
 * store(), update() y sinDeposito() deben rechazar con 422 en vez de aceptar
 * un envío que no correspondía.
 */
class CobranzaSedesControllerFeriadoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function usuarioSede(string $sede): User
    {
        Role::findOrCreate('sede', 'web');
        $user = User::factory()->create(['sede' => $sede, 'is_active' => true]);
        $user->assignRole('sede');

        return $user;
    }

    public function test_store_es_rechazado_si_hoy_es_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-06 08:00:00', 'America/Lima'));

        $user = $this->usuarioSede('Lima');

        $response = $this->actingAs($user)->postJson(route('productividad.cobranza-sedes.cobranza.store'), [
            'archivos' => [UploadedFile::fake()->create('deposito.pdf', 100, 'application/pdf')],
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Hoy es feriado, no se requiere envío.']);

        $reporte = ReporteCobranza::where('sede', 'Lima')->whereDate('semana_inicio', '2026-08-06')->first();
        $this->assertNotNull($reporte);
        $this->assertNull($reporte->fecha_envio_original);
    }

    public function test_store_funciona_normalmente_en_dia_habil(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-07 08:00:00', 'America/Lima')); // viernes normal

        $user = $this->usuarioSede('Lima');

        $response = $this->actingAs($user)->postJson(route('productividad.cobranza-sedes.cobranza.store'), [
            'archivos' => [UploadedFile::fake()->create('deposito.pdf', 100, 'application/pdf')],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $reporte = ReporteCobranza::where('sede', 'Lima')->whereDate('semana_inicio', '2026-08-07')->first();
        $this->assertNotNull($reporte->fecha_envio_original);
    }

    public function test_sin_deposito_es_rechazado_si_hoy_es_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-06 08:00:00', 'America/Lima'));

        $user = $this->usuarioSede('Lima');
        $reporte = ReporteCobranza::obtenerOCrearSemanaActual($user->id, 'Lima');

        $response = $this->actingAs($user)->putJson(
            route('productividad.cobranza-sedes.cobranza.sin-deposito', $reporte->id),
            ['motivo' => 'No hubo facturación en efectivo']
        );

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Hoy es feriado, no se requiere envío.']);
    }

    public function test_update_es_rechazado_si_el_reporte_es_de_un_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-06 08:00:00', 'America/Lima'));

        $user = $this->usuarioSede('Lima');
        $reporte = ReporteCobranza::obtenerOCrearSemanaActual($user->id, 'Lima');
        $this->assertSame('feriado', $reporte->estado);

        $response = $this->actingAs($user)->putJson(
            route('productividad.cobranza-sedes.cobranza.update', $reporte->id),
            ['notas' => 'Intento de edición en feriado']
        );

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Hoy es feriado, no se requiere envío.']);

        $reporte->refresh();
        $this->assertNull($reporte->fecha_envio_original);
        $this->assertNull($reporte->notas);
    }

    public function test_historial_no_ofrece_enviar_atrasado_en_filas_de_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-06 08:00:00', 'America/Lima'));

        $user = $this->usuarioSede('Lima');
        ReporteCobranza::obtenerOCrearSemanaActual($user->id, 'Lima');

        $response = $this->actingAs($user)->getJson(route('productividad.cobranza-sedes.cobranza.historial'));

        $response->assertOk();
        $fila = collect($response->json('data'))->firstWhere('semana_inicio_iso', '2026-08-06');
        $this->assertNotNull($fila);
        $this->assertSame('feriado', $fila['estado']);
        $this->assertFalse($fila['puede_enviar_atrasado']);
    }
}
