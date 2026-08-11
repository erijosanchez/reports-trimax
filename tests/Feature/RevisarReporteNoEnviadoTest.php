<?php

namespace Tests\Feature;

use App\Models\ReporteCajaChica;
use App\Models\ReporteCobranza;
use App\Models\ReporteComentarios;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A pedido de negocio, un revisor debe poder rechazar/comentar un reporte de
 * sede aunque la sede nunca lo haya enviado (estado 'no_enviado',
 * fecha_envio_original null) — antes revisar() lo bloqueaba con 422 en los
 * tres módulos (Cobranza, Caja Chica, Comentarios). El guard se quitó sin
 * agregar restricciones nuevas: los tres estados de revisión (conforme,
 * conforme_observado, rechazado) quedan disponibles igual que para un
 * reporte sí enviado.
 */
class RevisarReporteNoEnviadoTest extends TestCase
{
    use RefreshDatabase;

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

    private function reporteNoEnviadoCobranza(string $fecha = '2026-08-10'): ReporteCobranza
    {
        return ReporteCobranza::create([
            'user_id'       => User::factory()->create(['sede' => 'Lima'])->id,
            'sede'          => 'Lima',
            'semana_numero' => Carbon::parse($fecha)->dayOfYear,
            'anio'          => 2026,
            'semana_inicio' => $fecha,
            'semana_fin'    => $fecha,
            'fecha_limite'  => Carbon::parse($fecha . ' 10:00:00'),
            'estado'        => 'no_enviado',
        ]);
    }

    private function reporteNoEnviadoCajaChica(): ReporteCajaChica
    {
        return ReporteCajaChica::create([
            'user_id'       => User::factory()->create(['sede' => 'Lima'])->id,
            'sede'          => 'Lima',
            'semana_numero' => 32,
            'anio'          => 2026,
            'semana_inicio' => '2026-08-10',
            'semana_fin'    => '2026-08-15',
            'fecha_limite'  => Carbon::parse('2026-08-15 23:59:59'),
            'estado'        => 'no_enviado',
        ]);
    }

    private function reporteNoEnviadoComentarios(): ReporteComentarios
    {
        return ReporteComentarios::create([
            'user_id'       => User::factory()->create(['sede' => 'Lima'])->id,
            'sede'          => 'Lima',
            'semana_numero' => 32,
            'anio'          => 2026,
            'semana_inicio' => '2026-08-10',
            'semana_fin'    => '2026-08-13',
            'fecha_limite'  => Carbon::parse('2026-08-13 23:59:59'),
            'estado'        => 'no_enviado',
        ]);
    }

    public function test_se_puede_rechazar_un_reporte_de_cobranza_no_enviado(): void
    {
        $reporte = $this->reporteNoEnviadoCobranza();
        $finanzas = $this->userConRol('finanzas');

        $resp = $this->actingAs($finanzas)->postJson(
            route('productividad.cobranza-sedes.cobranza.revisar', ['reporte' => $reporte->id]),
            ['estado' => 'rechazado', 'motivo' => 'No envió el reporte de depósito.']
        );

        $resp->assertOk();
        $reporte->refresh();
        $this->assertSame('rechazado', $reporte->revision_estado);
        $this->assertSame('0.00', $reporte->kpi_porcentaje);
    }

    public function test_se_puede_rechazar_un_reporte_de_caja_chica_no_enviado(): void
    {
        $reporte = $this->reporteNoEnviadoCajaChica();
        $finanzas = $this->userConRol('finanzas');

        $resp = $this->actingAs($finanzas)->postJson(
            route('productividad.cobranza-sedes.caja-chica.revisar', ['reporte' => $reporte->id]),
            ['estado' => 'rechazado', 'motivo' => 'No envió el reporte de caja chica.']
        );

        $resp->assertOk();
        $reporte->refresh();
        $this->assertSame('rechazado', $reporte->revision_estado);
    }

    public function test_se_puede_rechazar_un_reporte_de_comentarios_no_enviado(): void
    {
        $reporte = $this->reporteNoEnviadoComentarios();
        $finanzas = $this->userConRol('finanzas');

        $resp = $this->actingAs($finanzas)->postJson(
            route('productividad.cobranza-sedes.comentarios.revisar', ['reporte' => $reporte->id]),
            ['estado' => 'rechazado', 'motivo' => 'No envió el reporte de comentarios.']
        );

        $resp->assertOk();
        $reporte->refresh();
        $this->assertSame('rechazado', $reporte->revision_estado);
    }

    /** Los tres estados de revisión siguen disponibles, no solo "rechazado". */
    public function test_tambien_se_puede_marcar_conforme_o_conforme_observado_sin_envio(): void
    {
        $finanzas = $this->userConRol('finanzas');

        $conforme = $this->reporteNoEnviadoCobranza();
        $this->actingAs($finanzas)->postJson(
            route('productividad.cobranza-sedes.cobranza.revisar', ['reporte' => $conforme->id]),
            ['estado' => 'conforme']
        )->assertOk();
        $this->assertSame('conforme', $conforme->refresh()->revision_estado);

        $observado = $this->reporteNoEnviadoCobranza('2026-08-11');
        $this->actingAs($finanzas)->postJson(
            route('productividad.cobranza-sedes.cobranza.revisar', ['reporte' => $observado->id]),
            ['estado' => 'conforme_observado', 'motivo' => 'Se disculpó, se aceptó.', 'penalidad' => 20]
        )->assertOk();
        $this->assertSame('conforme_observado', $observado->refresh()->revision_estado);
    }
}
