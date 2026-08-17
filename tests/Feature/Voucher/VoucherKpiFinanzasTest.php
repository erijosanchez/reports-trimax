<?php

namespace Tests\Feature\Voucher;

use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre VoucherController::kpiFinanzasSemanal y revisoresDisponibles — el
 * KPI de finanzas (horas promedio hasta APLICAR, no hasta revisar), pensado
 * para vivir junto al KPI de conformidad por sede. Por defecto todos ven el
 * combinado del equipo; solo admin/superadmin pueden acotarlo a un
 * aplicador puntual vía el selector "Aplicado por".
 *
 * Se agrupa por semana de SOLICITUD (a pedido de negocio, igual que
 * kpiSemanal()): un voucher pedido una semana cuenta en esa semana aunque
 * finanzas tarde varias semanas en aplicarlo — así el atraso queda visible
 * en la semana que lo originó, no en la semana en que por fin se resolvió.
 *
 * Reusa VoucherController::demoraEnMinutos(), la misma fórmula que la
 * columna "Demora" del historial: created_at (envío real) → aplicado_at
 * (aplicación real).
 */
class VoucherKpiFinanzasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Domingo fijo, 10:00: es el último día de la semana ISO
        // (lunes–domingo), así que "hoy menos N días" con N entre 0 y 6
        // siempre cae dentro de la MISMA semana — evita que un offset se
        // cruce a la semana anterior según en qué día caiga "hoy" al correr
        // el test.
        Carbon::setTestNow(Carbon::now()->next(Carbon::SUNDAY)->setTime(10, 0, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function userConRol(string $rol, array $atributos = []): User
    {
        Role::findOrCreate($rol, 'web');
        $user = User::factory()->create(array_merge([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ], $atributos));
        $user->assignRole($rol);
        $this->withSession(['2fa_verified' => true]);
        return $user;
    }

    /**
     * Crea un voucher "solicitado" $diasAntes días antes de "hoy" (el
     * domingo fijo del setUp) y, si $aplicar es true, lo aplica exactamente
     * "hoy" — congelando Carbon::setTestNow en cada paso para que created_at
     * y aplicado_at queden con la hora real que demoraEnMinutos() necesita
     * (created_at no se puede asignar por mass-assignment, solo vía reloj).
     */
    private function voucherAplicado(string $sede, ?int $aplicadorId, int $diasAntes, bool $aplicar = true): Voucher
    {
        $hoy = Carbon::now();

        Carbon::setTestNow($hoy->copy()->subDays($diasAntes));
        $voucher = Voucher::create([
            'codigo'        => 'V-' . uniqid(),
            'sede'          => $sede,
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
        ]);

        Carbon::setTestNow($hoy);
        if ($aplicar) {
            $voucher->update([
                'status'      => 'aplicado',
                'applied_by'  => $aplicadorId,
                'aplicado_at' => now(),
            ]);
        }

        return $voucher->fresh();
    }

    public function test_sede_no_accede_al_kpi_de_finanzas(): void
    {
        Role::findOrCreate('sede', 'web');
        $user = User::factory()->create(['sede' => 'LIMA']);
        $user->assignRole('sede');

        $this->actingAs($user)
            ->getJson(route('vouchers.kpiFinanzasSemanal'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson(route('vouchers.revisores'))
            ->assertStatus(403);
    }

    public function test_finanzas_no_admin_ve_el_combinado_del_equipo_ignorando_aplicador(): void
    {
        $finanzasA = $this->userConRol('finanzas');
        $finanzasB = $this->userConRol('finanzas');

        // Ambos solicitados y aplicados dentro de la semana actual (domingo
        // -2 y -6 días siguen cayendo en el mismo lunes-domingo).
        $this->voucherAplicado('LIMA', $finanzasA->id, 2); // 48h de demora
        $this->voucherAplicado('LIMA', $finanzasB->id, 6); // 144h de demora

        $resp = $this->actingAs($finanzasA)
            ->getJson(route('vouchers.kpiFinanzasSemanal', ['aplicador' => $finanzasB->id]));

        $resp->assertOk();
        // Finanzas no tiene selector de aplicador: el ?aplicador= se ignora
        // y siempre ve el combinado de todo el equipo, igual que admin sin filtro.
        $this->assertEquals(96.0, $resp->json('promedio_actual'));
        $this->assertSame(2, $resp->json('aplicados_actual'));

        // revisoresDisponibles tampoco es accesible para finanzas sin ser admin/superadmin.
        $this->actingAs($finanzasA)
            ->getJson(route('vouchers.revisores'))
            ->assertStatus(403);
    }

    public function test_admin_puede_ver_un_aplicador_puntual_o_el_combinado_de_todos(): void
    {
        $finanzasA = $this->userConRol('finanzas');
        $finanzasB = $this->userConRol('finanzas');
        $admin     = $this->userConRol('admin');

        $this->voucherAplicado('LIMA', $finanzasA->id, 2); // 48h
        $this->voucherAplicado('LIMA', $finanzasB->id, 6); // 144h

        // Aplicador puntual: solo el de A.
        $soloA = $this->actingAs($admin)
            ->getJson(route('vouchers.kpiFinanzasSemanal', ['aplicador' => $finanzasA->id]))
            ->json();
        $this->assertEquals(48.0, $soloA['promedio_actual']);
        $this->assertSame(1, $soloA['aplicados_actual']);

        // Sin filtro ("Todos"): promedio combinado de A (48h) y B (144h) = 96h.
        $todos = $this->actingAs($admin)
            ->getJson(route('vouchers.kpiFinanzasSemanal'))
            ->json();
        $this->assertEquals(96.0, $todos['promedio_actual']);
        $this->assertSame(2, $todos['aplicados_actual']);

        // Selector: admin sí puede listar quiénes han aplicado vouchers.
        $revisores = $this->actingAs($admin)->getJson(route('vouchers.revisores'));
        $revisores->assertOk();
        $ids = collect($revisores->json())->pluck('id')->all();
        $this->assertContains($finanzasA->id, $ids);
        $this->assertContains($finanzasB->id, $ids);
    }

    public function test_vouchers_sin_aplicar_no_cuentan_en_el_promedio(): void
    {
        $finanzas = $this->userConRol('finanzas');

        $this->voucherAplicado('LIMA', $finanzas->id, 2); // aplicado -> 48h
        $this->voucherAplicado('LIMA', null, 1, aplicar: false); // sigue pendiente -> no debe entrar

        $resp = $this->actingAs($finanzas)->getJson(route('vouchers.kpiFinanzasSemanal'));

        $resp->assertOk();
        $this->assertSame(1, $resp->json('aplicados_actual'));
        $this->assertEquals(48.0, $resp->json('promedio_actual'));
    }

    /**
     * El voucher se ubica en la semana en que se SOLICITÓ, no en la que se
     * aplicó — a pedido de negocio: si finanzas destraba tarde un voucher
     * viejo, el atraso debe quedar visible en la semana que lo originó, no
     * "explotar" en la semana en que por fin se resolvió.
     */
    public function test_agrupa_por_semana_de_solicitud_no_de_aplicacion(): void
    {
        $finanzas = $this->userConRol('finanzas');

        // Solicitado hace 2 semanas exactas, pero recién aplicado hoy.
        $this->voucherAplicado('LIMA', $finanzas->id, 14);

        $resp = $this->actingAs($finanzas)->getJson(route('vouchers.kpiFinanzasSemanal'));

        $resp->assertOk();
        // No cuenta en la semana actual (la de aplicación)...
        $this->assertSame(0, $resp->json('aplicados_actual'));
        $this->assertNull($resp->json('promedio_actual'));

        // ...sino en el bucket de hace 2 semanas (la de solicitud): 14 días
        // exactos entre created_at y aplicado_at = 336 horas. ultimasSemanas()
        // arma 8 semanas de la más antigua (índice 0) a la actual (índice 7),
        // así que "hace 2 semanas" es el índice 5.
        $data = $resp->json('data');
        $this->assertEquals(14 * 24, $data[5]);
        foreach ($data as $i => $valor) {
            if ($i !== 5) {
                $this->assertNull($valor, "El índice $i debería estar vacío");
            }
        }
    }

    /**
     * El id "kpiFinanzasChart"/"kpi-revisor" también aparece dentro del
     * <script> (siempre presente, con guardas en JS tipo "if (!canvas)
     * return"), así que no sirve para distinguir por rol con assertSee. Se
     * usa el texto de la tarjeta/label, que solo vive dentro del bloque
     * Blade condicional (@if($esRevisor) / @if($puedeVerTodosLosRevisores)).
     */
    public function test_la_tarjeta_de_kpi_de_finanzas_respeta_visibilidad_por_rol(): void
    {
        Role::findOrCreate('sede', 'web');
        $sede = User::factory()->create(['sede' => 'LIMA']);
        $sede->assignRole('sede');
        $this->withSession(['2fa_verified' => true]);

        $this->actingAs($sede)->get(route('vouchers.index'))
            ->assertOk()
            ->assertDontSee('KPI Semanal de Finanzas');

        $finanzas = $this->userConRol('finanzas');
        $this->actingAs($finanzas)->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('KPI Semanal de Finanzas')
            ->assertDontSee('Aplicado por:');

        $admin = $this->userConRol('admin');
        $this->actingAs($admin)->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('KPI Semanal de Finanzas')
            ->assertSee('Aplicado por:');
    }

    /**
     * El KPI de Conformidad mide el desempeño de la SEDE, no el de finanzas
     * (para eso está el KPI de Finanzas de arriba) — finanzas puro no debe
     * verlo, ni en la vista ni pegándole directo al endpoint.
     */
    public function test_finanzas_puro_no_ve_el_kpi_de_sede_pero_admin_y_sede_si(): void
    {
        $finanzas = $this->userConRol('finanzas');
        $this->actingAs($finanzas)->get(route('vouchers.index'))
            ->assertOk()
            ->assertDontSee('KPI Semanal de Conformidad');

        $this->actingAs($finanzas)
            ->getJson(route('vouchers.kpiSemanal'))
            ->assertStatus(403);

        $admin = $this->userConRol('admin');
        $this->actingAs($admin)->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('KPI Semanal de Conformidad');
        $this->actingAs($admin)
            ->getJson(route('vouchers.kpiSemanal'))
            ->assertOk();

        Role::findOrCreate('sede', 'web');
        $sede = User::factory()->create(['sede' => 'LIMA']);
        $sede->assignRole('sede');
        $this->withSession(['2fa_verified' => true]);
        $this->actingAs($sede)->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('KPI Semanal de Conformidad');
        $this->actingAs($sede)
            ->getJson(route('vouchers.kpiSemanal'))
            ->assertOk();
    }
}
