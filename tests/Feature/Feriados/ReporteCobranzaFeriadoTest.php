<?php

namespace Tests\Feature\Feriados;

use App\Models\Feriado;
use App\Models\ReporteCobranza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre cómo ReporteCobranza (reporte diario de Depósito de Efectivo) trata
 * un día feriado: no penaliza el KPI, no exige envío, y obtenerOCrearSemanaActual()
 * crea el registro directamente en estado 'feriado'.
 */
class ReporteCobranzaFeriadoTest extends TestCase
{
    use RefreshDatabase;

    private function reporte(array $attrs = []): ReporteCobranza
    {
        $user = User::factory()->create();

        return ReporteCobranza::create(array_merge([
            'user_id'       => $user->id,
            'sede'          => 'Lima',
            'semana_numero' => 32,
            'anio'          => 2026,
            'semana_inicio' => '2026-08-06', // jueves, Batalla de Junín
            'fecha_limite'  => Carbon::parse('2026-08-06 10:00:00'),
        ], $attrs));
    }

    public function test_recalcularKpi_marca_feriado_sin_penalizar_aunque_no_haya_envio(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);

        $reporte = $this->reporte();
        $reporte->recalcularKpi();

        $this->assertSame('feriado', $reporte->estado);
        $this->assertNull($reporte->kpi_porcentaje);
    }

    public function test_recalcularKpi_no_marca_feriado_en_un_dia_normal(): void
    {
        $reporte = $this->reporte(['semana_inicio' => '2026-08-07']); // viernes normal

        $reporte->recalcularKpi();

        $this->assertSame('no_enviado', $reporte->estado);
        $this->assertSame('0.00', $reporte->kpi_porcentaje);
    }

    public function test_recalcularKpi_sin_semana_inicio_no_falla_y_no_marca_feriado(): void
    {
        // semana_inicio nullable a nivel de esquema — Feriado::esFeriado(null) debe
        // devolver false en vez de lanzar un TypeError.
        $user = User::factory()->create();
        $reporte = ReporteCobranza::create([
            'user_id'       => $user->id,
            'sede'          => 'Lima',
            'semana_numero' => 30,
            'anio'          => 2026,
            'fecha_limite'  => Carbon::parse('2026-07-27 10:00:00'),
        ]);

        $reporte->recalcularKpi();

        $this->assertSame('no_enviado', $reporte->estado);
    }

    public function test_kpi_color_y_label_muestran_feriado(): void
    {
        $reporte = $this->reporte(['estado' => 'feriado', 'kpi_porcentaje' => null]);

        $this->assertSame('secondary', $reporte->kpiColor());
        $this->assertSame('Feriado', $reporte->kpiLabel());
    }

    public function test_obtenerOCrearSemanaActual_crea_en_estado_feriado_si_hoy_es_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-06 08:00:00', 'America/Lima'));

        $user = User::factory()->create(['sede' => 'Lima']);
        $reporte = ReporteCobranza::obtenerOCrearSemanaActual($user->id, 'Lima');

        $this->assertSame('feriado', $reporte->estado);

        Carbon::setTestNow();
    }

    public function test_obtenerOCrearSemanaActual_crea_pendiente_en_dia_normal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-07 08:00:00', 'America/Lima'));

        $user = User::factory()->create(['sede' => 'Lima']);
        $reporte = ReporteCobranza::obtenerOCrearSemanaActual($user->id, 'Lima');

        $this->assertSame('pendiente', $reporte->estado);

        Carbon::setTestNow();
    }
}
