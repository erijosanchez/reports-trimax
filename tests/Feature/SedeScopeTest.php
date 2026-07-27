<?php

namespace Tests\Feature;

use App\Models\Scopes\SedeScope;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre A1 (ARQUITECTURA.md): la frontera de datos por sede debe ser el
 * comportamiento por defecto de Eloquent para los modelos donde se aplica
 * (piloto: Voucher), no una comprobación manual que alguien puede olvidar.
 * Es el test que "convierte A1 en algo verificable" que pide A8.
 */
class SedeScopeTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol, ?string $sede = null): User
    {
        Role::findOrCreate($rol, 'web');

        $user = User::factory()->create(['sede' => $sede]);
        $user->assignRole($rol);

        return $user;
    }

    private function voucherDeSede(string $sede, string $codigo): Voucher
    {
        return Voucher::create([
            'codigo' => $codigo,
            'sede'   => $sede,
            'status' => 'pendiente',
            'total'  => 100,
        ]);
    }

    public function test_usuario_de_sede_solo_ve_vouchers_de_su_propia_sede(): void
    {
        $this->voucherDeSede('Lima', 'V-LIMA-1');
        $this->voucherDeSede('Huánuco', 'V-HCO-1');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $codigos = Voucher::all()->pluck('codigo')->all();

        $this->assertEquals(['V-LIMA-1'], $codigos);
    }

    public function test_usuario_de_sede_no_puede_leer_un_voucher_de_otra_sede_por_id(): void
    {
        $voucherOtraSede = $this->voucherDeSede('Huánuco', 'V-HCO-2');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Voucher::findOrFail($voucherOtraSede->id);
    }

    public function test_finanzas_ve_vouchers_de_todas_las_sedes(): void
    {
        $this->voucherDeSede('Lima', 'V-LIMA-2');
        $this->voucherDeSede('Huánuco', 'V-HCO-3');

        $user = $this->userConRol('finanzas');
        $this->actingAs($user);

        $codigos = Voucher::all()->pluck('codigo')->sort()->values()->all();

        $this->assertEquals(['V-HCO-3', 'V-LIMA-2'], $codigos);
    }

    public function test_super_admin_ve_vouchers_de_todas_las_sedes(): void
    {
        $this->voucherDeSede('Lima', 'V-LIMA-3');
        $this->voucherDeSede('Huánuco', 'V-HCO-4');

        $user = $this->userConRol('super_admin');
        $this->actingAs($user);

        $this->assertCount(2, Voucher::all());
    }

    public function test_se_puede_saltar_el_scope_a_proposito(): void
    {
        $this->voucherDeSede('Lima', 'V-LIMA-4');
        $this->voucherDeSede('Huánuco', 'V-HCO-5');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $todos = Voucher::withoutGlobalScope(SedeScope::class)->get();

        $this->assertCount(2, $todos);
    }

    public function test_gate_ver_vouchers_refleja_el_helper_puedeVerVouchers(): void
    {
        $sedeUser = $this->userConRol('sede', 'Lima');
        $sinPermiso = $this->userConRol('consultor');

        $this->assertTrue(Gate::forUser($sedeUser)->allows('ver-vouchers'));
        $this->assertFalse(Gate::forUser($sinPermiso)->allows('ver-vouchers'));
    }
}
