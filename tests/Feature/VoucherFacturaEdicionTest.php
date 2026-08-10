<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Un voucher ya aplicado no debe poder editarse: addFactura() ya lo
 * bloqueaba, pero removeFactura() no tenía el mismo guard — se podía borrar
 * una factura de un voucher aplicado, lo que además recalculaba 'total' vía
 * update() sobre esa fila. Antes de que 'aplicado_at' fuera datetime real,
 * ese update() corrompía en silencio hora_aplicado/demora (que dependían de
 * updated_at); ahora que dependen de aplicado_at directamente ya no hay
 * corrupción de datos, pero editar un voucher pagado sigue sin tener
 * sentido de negocio, así que el guard se agrega igual.
 */
class VoucherFacturaEdicionTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol, array $atributos = []): User
    {
        Role::findOrCreate($rol, 'web');
        $user = User::factory()->create($atributos);
        $user->assignRole($rol);

        return $user;
    }

    public function test_no_se_puede_agregar_factura_a_un_voucher_ya_aplicado(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-EDIT-1',
            'sede'          => 'Lima',
            'status'        => 'aplicado',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'aplicado_at'   => now(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->postJson(route('vouchers.addFactura', ['id' => $voucher->id]), [
            'factura' => 'F-001',
            'ruc'     => '12345678901',
            'monto'   => 50,
        ]);

        $resp->assertStatus(422);
        $this->assertSame(0, $voucher->facturas()->count());
    }

    public function test_no_se_puede_eliminar_una_factura_de_un_voucher_ya_aplicado(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-EDIT-2',
            'sede'          => 'Lima',
            'status'        => 'aplicado',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'aplicado_at'   => now(),
            'created_by'    => $sede->id,
        ]);
        $factura = $voucher->facturas()->create([
            'factura' => 'F-002',
            'ruc'     => '12345678901',
            'monto'   => 100,
        ]);

        $resp = $this->actingAs($sede)->deleteJson(route('vouchers.removeFactura', ['id' => $factura->id]));

        $resp->assertStatus(422);
        $this->assertDatabaseHas('voucher_facturas', ['id' => $factura->id]);
    }

    public function test_si_se_puede_eliminar_una_factura_de_un_voucher_pendiente(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-EDIT-3',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);
        $factura = $voucher->facturas()->create([
            'factura' => 'F-003',
            'ruc'     => '12345678901',
            'monto'   => 100,
        ]);

        $resp = $this->actingAs($sede)->deleteJson(route('vouchers.removeFactura', ['id' => $factura->id]));

        $resp->assertOk();
        $this->assertDatabaseMissing('voucher_facturas', ['id' => $factura->id]);
    }
}
