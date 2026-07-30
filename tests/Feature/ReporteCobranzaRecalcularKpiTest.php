<?php

namespace Tests\Feature;

use App\Models\ReporteCobranza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre A8 (ARQUITECTURA.md): ReporteCobranza::recalcularKpi(), que combina
 * la hora de envío, si hubo edición tardía, y la penalidad de revisión de
 * finanzas para fijar kpi_porcentaje y estado.
 */
class ReporteCobranzaRecalcularKpiTest extends TestCase
{
    use RefreshDatabase;

    private function reporte(array $attrs = []): ReporteCobranza
    {
        $user = User::factory()->create();

        return ReporteCobranza::create(array_merge([
            'user_id'       => $user->id,
            'sede'          => 'Lima',
            'semana_numero' => 30,
            'anio'          => 2026,
            'fecha_limite'  => Carbon::parse('2026-07-27 10:00:00'),
        ], $attrs));
    }

    public function test_sin_envio_el_kpi_queda_en_cero_y_estado_no_enviado(): void
    {
        $reporte = $this->reporte();

        $reporte->recalcularKpi();

        $this->assertSame('0.00', $reporte->kpi_porcentaje);
        $this->assertSame('no_enviado', $reporte->estado);
    }

    public function test_usa_fecha_envio_original_cuando_no_hubo_edicion_tardia(): void
    {
        $reporte = $this->reporte([
            'fecha_envio_original' => Carbon::parse('2026-07-27 09:00:00'), // a tiempo
            'fecha_ultimo_envio'   => Carbon::parse('2026-07-27 13:00:00'), // tardísimo, no debe usarse
            'editado_tarde'        => false,
        ]);

        $reporte->recalcularKpi();

        $this->assertSame('100.00', $reporte->kpi_porcentaje);
        $this->assertSame('en_tiempo', $reporte->estado);
    }

    public function test_usa_fecha_ultimo_envio_cuando_hubo_edicion_tardia(): void
    {
        $reporte = $this->reporte([
            'fecha_envio_original' => Carbon::parse('2026-07-27 09:00:00'), // a tiempo
            'fecha_ultimo_envio'   => Carbon::parse('2026-07-27 11:00:00'), // 1h tarde
            'editado_tarde'        => true,
        ]);

        $reporte->recalcularKpi();

        $this->assertSame('90.00', $reporte->kpi_porcentaje);
        $this->assertSame('con_atraso', $reporte->estado);
    }

    public function test_revision_rechazado_fuerza_el_kpi_a_cero_aunque_llego_a_tiempo(): void
    {
        $reporte = $this->reporte([
            'fecha_envio_original' => Carbon::parse('2026-07-27 09:00:00'),
            'fecha_ultimo_envio'   => Carbon::parse('2026-07-27 09:00:00'), // recalcularKpi() corta a 0 si esto falta
            'editado_tarde'        => false,
            'revision_estado'      => 'rechazado',
        ]);

        $reporte->recalcularKpi();

        $this->assertSame('0.00', $reporte->kpi_porcentaje);
    }

    public function test_conforme_observado_aplica_la_penalidad_proporcional_sobre_el_kpi_ya_ganado(): void
    {
        $reporte = $this->reporte([
            'fecha_envio_original'    => Carbon::parse('2026-07-27 09:00:00'), // 100% antes de penalidad
            'fecha_ultimo_envio'      => Carbon::parse('2026-07-27 09:00:00'), // recalcularKpi() corta a 0 si esto falta
            'editado_tarde'           => false,
            'revision_estado'         => 'conforme_observado',
            'revision_kpi_penalidad'  => 20,
        ]);

        $reporte->recalcularKpi();

        // 100 * (1 - 20/100) = 80
        $this->assertSame('80.00', $reporte->kpi_porcentaje);
    }
}
