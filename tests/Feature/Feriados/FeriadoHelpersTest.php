<?php

namespace Tests\Feature\Feriados;

use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre los helpers centrales de feriados (Feriado::esFeriado/esDiaLaborable/
 * siguienteDiaHabil) que usan los 3 controladores, los 4 jobs y
 * ProductivyController para no exigir envíos en feriado ni contarlo en KPI.
 */
class FeriadoHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_es_feriado_es_verdadero_para_una_fecha_registrada(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);

        $this->assertTrue(Feriado::esFeriado('2026-08-06'));
        $this->assertTrue(Feriado::esFeriado(Carbon::parse('2026-08-06 15:30:00')));
    }

    public function test_es_feriado_es_falso_para_una_fecha_no_registrada(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);

        $this->assertFalse(Feriado::esFeriado('2026-08-07'));
    }

    public function test_es_feriado_con_fecha_nula_no_lanza_excepcion_y_devuelve_falso(): void
    {
        $this->assertFalse(Feriado::esFeriado(null));
    }

    public function test_solo_cuentan_los_feriados_de_tipo_nacional(): void
    {
        Feriado::create(['fecha' => '2026-09-15', 'motivo' => 'Aniversario regional', 'tipo' => 'regional']);

        $this->assertFalse(Feriado::esFeriado('2026-09-15'));
    }

    public function test_dia_laborable_excluye_domingo_aunque_no_sea_feriado(): void
    {
        // 2026-08-30 es domingo y además feriado (Santa Rosa) — probamos con un domingo sin feriado.
        $domingoSinFeriado = Carbon::parse('2026-07-05'); // domingo
        $this->assertSame(Carbon::SUNDAY, $domingoSinFeriado->dayOfWeek);

        $this->assertFalse(Feriado::esDiaLaborable($domingoSinFeriado));
    }

    public function test_dia_laborable_excluye_feriado_entre_semana(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);

        $this->assertFalse(Feriado::esDiaLaborable(Carbon::parse('2026-08-06'))); // jueves feriado
        $this->assertTrue(Feriado::esDiaLaborable(Carbon::parse('2026-08-07')));  // viernes normal
    }

    public function test_siguiente_dia_habil_salta_feriados_consecutivos_y_domingo(): void
    {
        // 2026-07-28 y 29 son feriados (Fiestas Patrias), 2026-08-02 es domingo.
        Feriado::create(['fecha' => '2026-07-28', 'motivo' => 'Fiestas Patrias', 'tipo' => 'nacional']);
        Feriado::create(['fecha' => '2026-07-29', 'motivo' => 'Fiestas Patrias', 'tipo' => 'nacional']);

        // Desde el 27 (lunes), el siguiente hábil es el 30 (jueves), saltando 28 y 29.
        $siguiente = Feriado::siguienteDiaHabil(Carbon::parse('2026-07-27'));

        $this->assertSame('2026-07-30', $siguiente->toDateString());
    }

    public function test_cache_de_fechas_del_anio_se_invalida_al_crear_y_eliminar(): void
    {
        $this->assertFalse(Feriado::esFeriado('2026-10-08'));

        $feriado = Feriado::create(['fecha' => '2026-10-08', 'motivo' => 'Combate de Angamos', 'tipo' => 'nacional']);
        $this->assertTrue(Feriado::esFeriado('2026-10-08'));

        $feriado->delete();
        $this->assertFalse(Feriado::esFeriado('2026-10-08'));
    }
}
