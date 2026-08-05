<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre la hora de envío del voucher (created_at) mostrada en el historial
 * (filaHistorial) y en el modal de "inspeccionar" (getFacturas) — no existía
 * ningún campo con hora en ninguna de las dos respuestas antes de esto.
 */
class VoucherHoraEnvioTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sedeUser(string $sede): User
    {
        Role::findOrCreate('sede', 'web');

        $user = User::factory()->create(['sede' => $sede]);
        $user->assignRole('sede');

        return $user;
    }

    /**
     * En el historial la hora va SOLO como hora (H:i) — se combina con
     * 'solicitado_at' en la misma celda de la tabla en vez de abrir una
     * columna aparte, que sería redundante (ambas fechas son el mismo
     * instante: solicitado_at se fija a now() en store()).
     */
    public function test_historial_incluye_la_hora_de_envio_junto_a_solicitado(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 18, 20, 15, 'America/Lima'));

        $voucher = Voucher::create([
            'codigo'        => 'V-HORA-1',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(), // igual que hace store() en producción
        ]);

        $user = $this->sedeUser('Lima');

        $resp = $this->actingAs($user)->getJson(route('vouchers.historial'));

        $resp->assertOk();
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertNotNull($fila);
        $this->assertSame('28/05/2026', $fila['solicitado_at']);
        $this->assertSame('18:20', $fila['hora_envio']);
    }

    public function test_detalle_al_inspeccionar_incluye_hora_de_envio_y_quien_envio(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 24, 14, 7, 0, 'America/Lima'));

        $creador = $this->sedeUser('Lima');

        $voucher = Voucher::create([
            'codigo'     => 'V-HORA-2',
            'sede'       => 'Lima',
            'status'     => 'pendiente',
            'total'      => 100,
            'created_by' => $creador->id,
        ]);

        $resp = $this->actingAs($creador)->getJson(route('vouchers.facturas', ['id' => $voucher->id]));

        $resp->assertOk();
        $resp->assertJson([
            'hora_envio'   => '24/07/2026 14:07',
            'creator_name' => $creador->name,
        ]);
    }

    public function test_hora_de_envio_es_null_cuando_no_hay_creator_asociado(): void
    {
        $voucher = Voucher::create([
            'codigo' => 'V-HORA-3',
            'sede'   => 'Lima',
            'status' => 'pendiente',
            'total'  => 100,
        ]);

        $user = $this->sedeUser('Lima');

        $resp = $this->actingAs($user)->getJson(route('vouchers.facturas', ['id' => $voucher->id]));

        $resp->assertOk();
        $this->assertNull($resp->json('creator_name'));
        $this->assertNotNull($resp->json('hora_envio')); // created_at siempre existe, aunque no haya creator_by
    }
}
