<?php

namespace Tests\Feature\Feriados;

use App\Models\Feriado;
use App\Models\ReporteCajaChica;
use App\Models\ReporteComentarios;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre la decisión de negocio confirmada: si el día de cierre semanal
 * (sábado para Caja Chica, jueves para Comentarios) cae feriado, el plazo
 * corre al siguiente día hábil — en vez de eximir la semana o dejar el
 * plazo fijo en un día no laborable.
 */
class DatosSemanaFeriadoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_caja_chica_corre_el_plazo_al_lunes_si_el_sabado_es_feriado(): void
    {
        // Semana lunes 2026-08-03 a sábado 2026-08-08 (sábado sintético como feriado para la prueba).
        Feriado::create(['fecha' => '2026-08-08', 'motivo' => 'Feriado de prueba', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00', 'America/Lima')); // martes de esa semana

        [, , , , $limite] = ReporteCajaChica::datosSemanActual();

        // Domingo 08-09 se salta (no laborable), el plazo cae en lunes 08-10.
        $this->assertSame('2026-08-10', $limite->toDateString());
        $this->assertSame(23, $limite->hour);
    }

    public function test_caja_chica_mantiene_el_sabado_si_no_es_feriado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00', 'America/Lima'));

        [, , , , $limite] = ReporteCajaChica::datosSemanActual();

        $this->assertSame('2026-08-08', $limite->toDateString());
    }

    public function test_comentarios_corre_el_plazo_al_viernes_si_el_jueves_es_feriado(): void
    {
        // Jueves 2026-08-06 (Batalla de Junín) — feriado real del calendario 2026.
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00', 'America/Lima')); // martes de esa semana

        [, , , , $limite] = ReporteComentarios::datosSemanActual();

        $this->assertSame('2026-08-07', $limite->toDateString());
    }

    public function test_comentarios_mantiene_el_jueves_si_no_es_feriado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-04 09:00:00', 'America/Lima'));

        [, , , , $limite] = ReporteComentarios::datosSemanActual();

        $this->assertSame('2026-08-06', $limite->toDateString());
    }
}
