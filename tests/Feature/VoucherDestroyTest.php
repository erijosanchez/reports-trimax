<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Las sedes (incluida la creadora del voucher) nunca pueden eliminar un
 * voucher, sin importar el estado — antes sí podían mientras estuviera
 * pendiente (created_by === user->id), y el backend ni siquiera validaba el
 * estado en destroy(), así que una sede podía borrar por API un voucher ya
 * aplicado aunque el botón estuviera oculto en la UI. Ahora solo
 * admin/superadmin pueden eliminar, y solo si sigue pendiente.
 */
class VoucherDestroyTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol, array $atributos = []): User
    {
        Role::findOrCreate($rol, 'web');
        // super_admin/admin/finanzas exigen 2FA verificado (TwoFactorVerifiedMiddleware);
        // 'sede' no, así que este setup no le afecta.
        $user = User::factory()->create(array_merge([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ], $atributos));
        $user->assignRole($rol);
        $this->withSession(['2fa_verified' => true]);

        return $user;
    }

    public function test_sede_no_puede_eliminar_ni_siquiera_su_propio_voucher_pendiente(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-DEL-1',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->deleteJson(route('vouchers.destroy', ['id' => $voucher->id]));

        $resp->assertStatus(403);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);
    }

    public function test_sede_no_puede_eliminar_por_api_aunque_el_voucher_este_aplicado(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-DEL-2',
            'sede'          => 'Lima',
            'status'        => 'aplicado',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'aplicado_at'   => now(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->deleteJson(route('vouchers.destroy', ['id' => $voucher->id]));

        $resp->assertStatus(403);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);
    }

    public function test_admin_puede_eliminar_un_voucher_pendiente(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $admin   = $this->userConRol('admin');
        $voucher = Voucher::create([
            'codigo'        => 'V-DEL-3',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($admin)->deleteJson(route('vouchers.destroy', ['id' => $voucher->id]));

        $resp->assertOk();
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_admin_no_puede_eliminar_un_voucher_ya_aplicado(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $admin   = $this->userConRol('admin');
        $voucher = Voucher::create([
            'codigo'        => 'V-DEL-4',
            'sede'          => 'Lima',
            'status'        => 'aplicado',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'aplicado_at'   => now(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($admin)->deleteJson(route('vouchers.destroy', ['id' => $voucher->id]));

        $resp->assertStatus(422);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);
    }

    public function test_el_boton_eliminar_no_se_muestra_a_la_sede_en_el_historial(): void
    {
        $sede    = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-DEL-5',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->getJson(route('vouchers.historial'));
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertFalse($fila['puede_eliminar']);
    }
}
