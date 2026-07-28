<?php

namespace Tests\Feature;

use App\Models\ReporteCajaChica;
use App\Models\ReporteComentarios;
use App\Models\ReporteCobranza;
use App\Models\Scopes\SedeScope;
use App\Models\SolicitudDesbloqueo;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre A1 (ARQUITECTURA.md): la frontera de datos por sede debe ser el
 * comportamiento por defecto de Eloquent para los modelos donde se aplica
 * (piloto: Voucher; extendido a ReporteCobranza, ReporteCajaChica,
 * ReporteComentarios y SolicitudDesbloqueo — los 4 modelos que quedaron
 * documentados como candidatos sin aplicar), no una comprobación manual que
 * alguien puede olvidar. Es el test que "convierte A1 en algo verificable"
 * que pide A8.
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

    private function reporteCobranzaDeSede(string $sede, string $semanaInicio): ReporteCobranza
    {
        return ReporteCobranza::create([
            'user_id'       => User::factory()->create()->id,
            'sede'          => $sede,
            'semana_numero' => 1,
            'anio'          => 2026,
            'semana_inicio' => $semanaInicio,
        ]);
    }

    private function reporteCajaChicaDeSede(string $sede, int $semanaNumero): ReporteCajaChica
    {
        return ReporteCajaChica::create([
            'user_id'       => User::factory()->create()->id,
            'sede'          => $sede,
            'semana_numero' => $semanaNumero,
            'anio'          => 2026,
        ]);
    }

    private function reporteComentariosDeSede(string $sede, int $semanaNumero): ReporteComentarios
    {
        return ReporteComentarios::create([
            'user_id'       => User::factory()->create()->id,
            'sede'          => $sede,
            'semana_numero' => $semanaNumero,
            'anio'          => 2026,
        ]);
    }

    private function solicitudDesbloqueoDeSede(string $sede, string $ruc): SolicitudDesbloqueo
    {
        return SolicitudDesbloqueo::create([
            'user_id'      => User::factory()->create()->id,
            'sede'         => $sede,
            'ruc'          => $ruc,
            'razon_social' => 'Cliente de prueba',
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

    // ── ReporteCobranza ──────────────────────────────────────────

    public function test_usuario_de_sede_solo_ve_reportes_de_cobranza_de_su_sede(): void
    {
        $this->reporteCobranzaDeSede('Lima', '2026-07-20');
        $this->reporteCobranzaDeSede('Huánuco', '2026-07-20');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $sedes = ReporteCobranza::all()->pluck('sede')->all();

        $this->assertEquals(['Lima'], $sedes);
    }

    public function test_usuario_de_sede_no_puede_leer_un_reporte_de_cobranza_de_otra_sede_por_id(): void
    {
        $reporteOtraSede = $this->reporteCobranzaDeSede('Huánuco', '2026-07-21');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        ReporteCobranza::findOrFail($reporteOtraSede->id);
    }

    public function test_finanzas_ve_reportes_de_cobranza_de_todas_las_sedes(): void
    {
        $this->reporteCobranzaDeSede('Lima', '2026-07-22');
        $this->reporteCobranzaDeSede('Huánuco', '2026-07-22');

        $user = $this->userConRol('finanzas');
        $this->actingAs($user);

        $this->assertCount(2, ReporteCobranza::all());
    }

    // ── ReporteCajaChica ─────────────────────────────────────────

    public function test_usuario_de_sede_solo_ve_reportes_de_caja_chica_de_su_sede(): void
    {
        $this->reporteCajaChicaDeSede('Lima', 30);
        $this->reporteCajaChicaDeSede('Huánuco', 30);

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $sedes = ReporteCajaChica::all()->pluck('sede')->all();

        $this->assertEquals(['Lima'], $sedes);
    }

    public function test_usuario_de_sede_no_puede_leer_un_reporte_de_caja_chica_de_otra_sede_por_id(): void
    {
        $reporteOtraSede = $this->reporteCajaChicaDeSede('Huánuco', 31);

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        ReporteCajaChica::findOrFail($reporteOtraSede->id);
    }

    public function test_finanzas_ve_reportes_de_caja_chica_de_todas_las_sedes(): void
    {
        $this->reporteCajaChicaDeSede('Lima', 32);
        $this->reporteCajaChicaDeSede('Huánuco', 32);

        $user = $this->userConRol('finanzas');
        $this->actingAs($user);

        $this->assertCount(2, ReporteCajaChica::all());
    }

    // ── ReporteComentarios ───────────────────────────────────────

    public function test_usuario_de_sede_solo_ve_reportes_de_comentarios_de_su_sede(): void
    {
        $this->reporteComentariosDeSede('Lima', 30);
        $this->reporteComentariosDeSede('Huánuco', 30);

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $sedes = ReporteComentarios::all()->pluck('sede')->all();

        $this->assertEquals(['Lima'], $sedes);
    }

    public function test_usuario_de_sede_no_puede_leer_un_reporte_de_comentarios_de_otra_sede_por_id(): void
    {
        $reporteOtraSede = $this->reporteComentariosDeSede('Huánuco', 31);

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        ReporteComentarios::findOrFail($reporteOtraSede->id);
    }

    public function test_finanzas_ve_reportes_de_comentarios_de_todas_las_sedes(): void
    {
        $this->reporteComentariosDeSede('Lima', 32);
        $this->reporteComentariosDeSede('Huánuco', 32);

        $user = $this->userConRol('finanzas');
        $this->actingAs($user);

        $this->assertCount(2, ReporteComentarios::all());
    }

    // ── SolicitudDesbloqueo ──────────────────────────────────────

    public function test_usuario_de_sede_solo_ve_solicitudes_de_desbloqueo_de_su_sede(): void
    {
        $this->solicitudDesbloqueoDeSede('Lima', '10000000001');
        $this->solicitudDesbloqueoDeSede('Huánuco', '10000000002');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $sedes = SolicitudDesbloqueo::all()->pluck('sede')->all();

        $this->assertEquals(['Lima'], $sedes);
    }

    public function test_usuario_de_sede_no_puede_leer_una_solicitud_de_desbloqueo_de_otra_sede_por_id(): void
    {
        $solicitudOtraSede = $this->solicitudDesbloqueoDeSede('Huánuco', '10000000003');

        $user = $this->userConRol('sede', 'Lima');
        $this->actingAs($user);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        SolicitudDesbloqueo::findOrFail($solicitudOtraSede->id);
    }

    public function test_finanzas_ve_solicitudes_de_desbloqueo_de_todas_las_sedes(): void
    {
        $this->solicitudDesbloqueoDeSede('Lima', '10000000004');
        $this->solicitudDesbloqueoDeSede('Huánuco', '10000000005');

        $user = $this->userConRol('finanzas');
        $this->actingAs($user);

        $this->assertCount(2, SolicitudDesbloqueo::all());
    }
}
