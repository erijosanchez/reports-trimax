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
 * KPI de finanzas (días promedio hasta revisar), pensado para vivir junto
 * al KPI de conformidad por sede pero con alcance invertido: cada finanzas
 * ve solo lo suyo, solo admin/superadmin pueden ver a los demás.
 *
 * A diferencia de kpiSemanal() (agrupa por semana de SOLICITUD), este agrupa
 * por semana de REVISIÓN — un voucher pedido una semana pero revisado la
 * siguiente debe contar en la semana en que se revisó, no en la que se pidió.
 */
class VoucherKpiFinanzasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Día 15 fijo: evita casos límite de fin de semana/mes en los rangos ISO.
        Carbon::setTestNow(Carbon::now()->startOfMonth()->addDays(14)->setTime(10, 0, 0, 0));
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

    private function voucherRevisado(string $sede, ?int $revisorId, string $solicitadoAt, ?string $revisionAt, string $estado = 'conforme'): Voucher
    {
        return Voucher::create([
            'codigo'            => 'V-' . uniqid(),
            'sede'              => $sede,
            'status'            => 'pendiente',
            'total'             => 100,
            'solicitado_at'     => $solicitadoAt,
            'revision_estado'   => $revisionAt ? $estado : null,
            'revision_user_id'  => $revisionAt ? $revisorId : null,
            'revision_at'       => $revisionAt,
        ]);
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

    public function test_finanzas_no_admin_solo_ve_su_propio_promedio_aunque_pida_otro_revisor(): void
    {
        $finanzasA = $this->userConRol('finanzas');
        $finanzasB = $this->userConRol('finanzas');

        $hoy = Carbon::now();

        // Voucher revisado por A: 2 días de demora, esta semana.
        $this->voucherRevisado('LIMA', $finanzasA->id, $hoy->copy()->subDays(2)->toDateString(), $hoy->copy()->toDateTimeString());
        // Voucher revisado por B: 8 días de demora, esta semana. Si el filtro
        // de "propio usuario" no funcionara, este contaminaría el promedio de A.
        $this->voucherRevisado('LIMA', $finanzasB->id, $hoy->copy()->subDays(8)->toDateString(), $hoy->copy()->toDateTimeString());

        $resp = $this->actingAs($finanzasA)
            ->getJson(route('vouchers.kpiFinanzasSemanal', ['revisor' => $finanzasB->id]));

        $resp->assertOk();
        // Ignora el ?revisor= ajeno: sigue siendo el promedio de A, no el combinado.
        // 2.4 y no 2.0: revision_at lleva la hora real (hoy quedó fijo a las
        // 10:00), mientras solicitado_at es solo fecha (medianoche) -> 2 días
        // y 10 horas = 2.41666.. -> redondeado a 1 decimal.
        $this->assertEquals(2.4, $resp->json('promedio_actual'));
        $this->assertSame(1, $resp->json('revisados_actual'));

        // revisoresDisponibles tampoco es accesible para finanzas sin ser admin/superadmin.
        $this->actingAs($finanzasA)
            ->getJson(route('vouchers.revisores'))
            ->assertStatus(403);
    }

    public function test_admin_puede_ver_un_revisor_puntual_o_el_combinado_de_todos(): void
    {
        $finanzasA = $this->userConRol('finanzas');
        $finanzasB = $this->userConRol('finanzas');
        $admin     = $this->userConRol('admin');

        $hoy = Carbon::now();

        $this->voucherRevisado('LIMA', $finanzasA->id, $hoy->copy()->subDays(2)->toDateString(), $hoy->copy()->toDateTimeString());
        $this->voucherRevisado('LIMA', $finanzasB->id, $hoy->copy()->subDays(8)->toDateString(), $hoy->copy()->toDateTimeString());

        // Revisor puntual: solo el de A (2 días y 10h -> 2.4, ver test anterior).
        $soloA = $this->actingAs($admin)
            ->getJson(route('vouchers.kpiFinanzasSemanal', ['revisor' => $finanzasA->id]))
            ->json();
        $this->assertEquals(2.4, $soloA['promedio_actual']);
        $this->assertSame(1, $soloA['revisados_actual']);

        // Sin filtro ("Todos"): promedio combinado de A (2.41666..) y B (8.41666..) = 5.41666.. -> 5.4.
        $todos = $this->actingAs($admin)
            ->getJson(route('vouchers.kpiFinanzasSemanal'))
            ->json();
        $this->assertEquals(5.4, $todos['promedio_actual']);
        $this->assertSame(2, $todos['revisados_actual']);

        // Selector de revisores: admin sí puede listarlos.
        $revisores = $this->actingAs($admin)->getJson(route('vouchers.revisores'));
        $revisores->assertOk();
        $ids = collect($revisores->json())->pluck('id')->all();
        $this->assertContains($finanzasA->id, $ids);
        $this->assertContains($finanzasB->id, $ids);
    }

    public function test_vouchers_sin_revisar_no_cuentan_en_el_promedio(): void
    {
        $finanzas = $this->userConRol('finanzas');
        $hoy = Carbon::now();

        $this->voucherRevisado('LIMA', $finanzas->id, $hoy->copy()->subDays(2)->toDateString(), $hoy->copy()->toDateTimeString());
        // Sin revisar: revision_at null -> no debe entrar al conteo ni al promedio.
        $this->voucherRevisado('LIMA', null, $hoy->copy()->subDays(1)->toDateString(), null);

        $resp = $this->actingAs($finanzas)->getJson(route('vouchers.kpiFinanzasSemanal'));

        $resp->assertOk();
        $this->assertSame(1, $resp->json('revisados_actual'));
        $this->assertEquals(2.4, $resp->json('promedio_actual'));
    }

    /**
     * El voucher se ubica en la semana en que se REVISÓ, no en la que se
     * solicitó — a propósito, distinto del KPI de sede (kpiSemanal), que sí
     * agrupa por solicitud. Aquí se mide el trabajo de finanzas de esa semana.
     */
    public function test_agrupa_por_semana_de_revision_no_de_solicitud(): void
    {
        $finanzas = $this->userConRol('finanzas');
        $hoy = Carbon::now();

        // Solicitado hace 2 semanas, pero recién revisado esta semana.
        $this->voucherRevisado(
            'LIMA',
            $finanzas->id,
            $hoy->copy()->subWeeks(2)->toDateString(),
            $hoy->copy()->toDateTimeString()
        );

        $resp = $this->actingAs($finanzas)->getJson(route('vouchers.kpiFinanzasSemanal'));

        $resp->assertOk();
        // Cuenta en la semana actual (la de revisión), no en la de -2 semanas.
        // 14 días y 10h -> 14.41666.. -> 14.4.
        $this->assertSame(1, $resp->json('revisados_actual'));
        $this->assertEquals(14.4, $resp->json('promedio_actual'));
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
            ->assertDontSee('Revisor:');

        $admin = $this->userConRol('admin');
        $this->actingAs($admin)->get(route('vouchers.index'))
            ->assertOk()
            ->assertSee('KPI Semanal de Finanzas')
            ->assertSee('Revisor:');
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
