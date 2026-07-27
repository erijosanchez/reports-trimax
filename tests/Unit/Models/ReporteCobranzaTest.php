<?php

namespace Tests\Unit\Models;

use App\Models\ReporteCobranza;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Cubre A8 (ARQUITECTURA.md): reglas de negocio que ya causaron un incidente
 * real. Este archivo cubre horaLimitePara()/esSedeLimite11()/normalizarSede()
 * — hubo un bug de hora límite en la sede Huánuco (commit 9ea05b5): la lista
 * de sedes con límite de 11 AM no llevaba tildes ('HUANUCO') pero la BD
 * guarda la sede con tilde ('HUÁNUCO'), así que el in_array() directo nunca
 * hacía match y Huánuco recibía el límite de 10 AM en vez de 11 AM.
 */
class ReporteCobranzaTest extends TestCase
{
    // ── normalizarSede() ────────────────────────────────────────────

    public function test_normaliza_sede_quitando_tildes_y_pasando_a_mayusculas(): void
    {
        $this->assertSame('HUANUCO', ReporteCobranza::normalizarSede('HUÁNUCO'));
        $this->assertSame('HUANUCO', ReporteCobranza::normalizarSede('huánuco'));
        $this->assertSame('HUANUCO', ReporteCobranza::normalizarSede('  Huánuco  '));
        $this->assertSame('ICA', ReporteCobranza::normalizarSede('ICA'));
    }

    public function test_normaliza_sede_nula_o_vacia_a_cadena_vacia(): void
    {
        $this->assertSame('', ReporteCobranza::normalizarSede(null));
        $this->assertSame('', ReporteCobranza::normalizarSede(''));
    }

    // ── esSedeLimite11() / horaLimitePara() — regresión del bug real ──

    public function test_huanuco_con_tilde_como_se_guarda_en_bd_tiene_limite_11am(): void
    {
        // Este es exactamente el caso que fallaba antes de 9ea05b5: la BD
        // guarda 'HUÁNUCO' con tilde.
        $this->assertTrue(ReporteCobranza::esSedeLimite11('HUÁNUCO'));
        $this->assertSame([11, 0], ReporteCobranza::horaLimitePara('HUÁNUCO'));
    }

    public function test_huanuco_sin_tilde_tambien_tiene_limite_11am(): void
    {
        $this->assertTrue(ReporteCobranza::esSedeLimite11('HUANUCO'));
        $this->assertSame([11, 0], ReporteCobranza::horaLimitePara('HUANUCO'));
    }

    public function test_ica_y_ate_tienen_limite_11am(): void
    {
        $this->assertTrue(ReporteCobranza::esSedeLimite11('ICA'));
        $this->assertTrue(ReporteCobranza::esSedeLimite11('ATE'));
    }

    public function test_sedes_sin_excepcion_tienen_limite_10am(): void
    {
        $this->assertFalse(ReporteCobranza::esSedeLimite11('LIMA'));
        $this->assertSame([10, 0], ReporteCobranza::horaLimitePara('LIMA'));
        $this->assertSame([10, 0], ReporteCobranza::horaLimitePara(null));
    }

    // ── calcularKpi() ─────────────────────────────────────────────

    public function test_kpi_100_si_envio_a_tiempo_o_antes(): void
    {
        $limite = Carbon::parse('2026-07-27 10:00:00');

        $this->assertSame(100.0, ReporteCobranza::calcularKpi($limite->copy()->subMinutes(5), $limite));
        $this->assertSame(100.0, ReporteCobranza::calcularKpi($limite->copy(), $limite));
    }

    public function test_kpi_90_si_atraso_es_de_hasta_una_hora(): void
    {
        $limite = Carbon::parse('2026-07-27 10:00:00');

        $this->assertSame(90.0, ReporteCobranza::calcularKpi($limite->copy()->addMinutes(1), $limite));
        $this->assertSame(90.0, ReporteCobranza::calcularKpi($limite->copy()->addHour(), $limite));
    }

    public function test_kpi_80_si_atraso_es_de_mas_de_una_hora_hasta_dos(): void
    {
        $limite = Carbon::parse('2026-07-27 10:00:00');

        $this->assertSame(80.0, ReporteCobranza::calcularKpi($limite->copy()->addHour()->addMinute(), $limite));
        $this->assertSame(80.0, ReporteCobranza::calcularKpi($limite->copy()->addHours(2), $limite));
    }

    public function test_kpi_50_si_atraso_es_de_mas_de_dos_horas_hasta_tres(): void
    {
        $limite = Carbon::parse('2026-07-27 10:00:00');

        $this->assertSame(50.0, ReporteCobranza::calcularKpi($limite->copy()->addHours(2)->addMinute(), $limite));
        $this->assertSame(50.0, ReporteCobranza::calcularKpi($limite->copy()->addHours(3), $limite));
    }

    public function test_kpi_0_si_atraso_supera_tres_horas(): void
    {
        $limite = Carbon::parse('2026-07-27 10:00:00');

        $this->assertSame(0.0, ReporteCobranza::calcularKpi($limite->copy()->addHours(3)->addMinute(), $limite));
        $this->assertSame(0.0, ReporteCobranza::calcularKpi($limite->copy()->addDay(), $limite));
    }
}
