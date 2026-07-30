<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre A1 (ARQUITECTURA.md): un Gate por cada puedeX() de User, registrado
 * en AuthServiceProvider, en vez de que cada controlador llame al helper
 * directamente. Los helpers siguen existiendo — el Gate es la fachada.
 */
class GatesTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol, array $atributos = []): User
    {
        Role::findOrCreate($rol, 'web');

        $user = User::factory()->create($atributos);
        $user->assignRole($rol);

        return $user;
    }

    /** Los 20 Gates registrados en AuthServiceProvider — todos true para super_admin (Gate::before). */
    public function test_super_admin_pasa_los_20_gates(): void
    {
        $gates = [
            'ver-vouchers', 'revisar-vouchers', 'ver-ventas-consolidadas',
            'ver-descuentos-especiales', 'ver-consultar-orden', 'ver-acuerdos-comerciales',
            'ver-lead-time', 'ver-venta-clientes', 'ver-ordenes-x-sede',
            'ver-asignacion-bases', 'crear-requerimientos', 'ver-todos-requerimientos',
            'gestionar-requerimientos', 'ver-cobranza-sedes', 'revisar-reportes-sedes',
            'ver-productivy-total', 'acceder-productivy', 'ver-pendiente-entrega-montura',
            'ver-motorizados', 'ver-retiros-ordenes', 'ver-desbloqueo',
        ];

        $superAdmin = $this->userConRol('super_admin');

        foreach ($gates as $gate) {
            $this->assertTrue(
                Gate::forUser($superAdmin)->allows($gate),
                "super_admin debería pasar el gate '$gate'"
            );
        }
    }

    public function test_usuario_sin_rol_ni_flags_no_pasa_los_gates_de_solo_columna(): void
    {
        $user = $this->userConRol('consultor');

        $this->assertFalse(Gate::forUser($user)->allows('ver-ventas-consolidadas'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-descuentos-especiales'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-venta-clientes'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-ordenes-x-sede'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-asignacion-bases'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-productivy-total'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-pendiente-entrega-montura'));
        $this->assertFalse(Gate::forUser($user)->allows('ver-motorizados'));
    }

    public function test_flag_explicito_en_columna_habilita_el_gate(): void
    {
        $user = $this->userConRol('consultor', ['puede_ver_ventas_consolidadas' => true]);

        $this->assertTrue(Gate::forUser($user)->allows('ver-ventas-consolidadas'));
    }

    public function test_rol_sede_pasa_cobranza_y_desbloqueo_pero_no_revisar(): void
    {
        $sede = $this->userConRol('sede', ['sede' => 'Lima']);

        $this->assertTrue(Gate::forUser($sede)->allows('ver-cobranza-sedes'));
        $this->assertTrue(Gate::forUser($sede)->allows('ver-desbloqueo'));
        $this->assertTrue(Gate::forUser($sede)->allows('ver-consultar-orden'));
        $this->assertFalse(Gate::forUser($sede)->allows('revisar-reportes-sedes'));
    }

    public function test_rol_finanzas_pasa_revisar_reportes_sedes(): void
    {
        $finanzas = $this->userConRol('finanzas');

        $this->assertTrue(Gate::forUser($finanzas)->allows('revisar-reportes-sedes'));
        $this->assertTrue(Gate::forUser($finanzas)->allows('ver-cobranza-sedes'));
        $this->assertTrue(Gate::forUser($finanzas)->allows('ver-vouchers'));
    }

    public function test_rol_rrhh_pasa_los_gates_de_requerimientos(): void
    {
        $rrhh = $this->userConRol('rrhh');

        $this->assertTrue(Gate::forUser($rrhh)->allows('crear-requerimientos'));
        $this->assertTrue(Gate::forUser($rrhh)->allows('ver-todos-requerimientos'));
        $this->assertTrue(Gate::forUser($rrhh)->allows('gestionar-requerimientos'));
    }

    // ── Endpoints migrados a Gate (uno por controlador, misma demostración que Vouchers) ──

    public function test_index_cobranza_deniega_sin_el_gate(): void
    {
        $user = $this->userConRol('consultor');

        $this->actingAs($user)
            ->get(route('productividad.cobranza-sedes.cobranza.index'))
            ->assertForbidden();
    }

    public function test_index_cobranza_permite_con_el_gate(): void
    {
        $sede = $this->userConRol('sede', ['sede' => 'Lima']);

        $this->actingAs($sede)
            ->get(route('productividad.cobranza-sedes.cobranza.index'))
            ->assertOk();
    }

    public function test_index_caja_chica_deniega_sin_el_gate(): void
    {
        $user = $this->userConRol('consultor');

        $this->actingAs($user)
            ->get(route('productividad.cobranza-sedes.caja-chica.index'))
            ->assertForbidden();
    }

    public function test_index_comentarios_deniega_sin_el_gate(): void
    {
        $user = $this->userConRol('consultor');

        $this->actingAs($user)
            ->get(route('productividad.cobranza-sedes.comentarios.index'))
            ->assertForbidden();
    }

    public function test_index_desbloqueo_deniega_sin_el_gate(): void
    {
        $user = $this->userConRol('consultor');

        $this->actingAs($user)
            ->get(route('desbloqueo.index'))
            ->assertForbidden();
    }

    public function test_index_desbloqueo_permite_con_el_gate(): void
    {
        $sede = $this->userConRol('sede', ['sede' => 'Lima']);

        $this->actingAs($sede)
            ->get(route('desbloqueo.index'))
            ->assertOk();
    }
}
