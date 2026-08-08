<?php

namespace Tests\Feature\LeadTime;

use App\Models\User;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre LeadTimeController::processLeadTimeData (endpoint getData) — en
 * particular el bug donde las órdenes PENDIENTES (sin TIME) cuyo LEAD_TIME
 * cae en el mes consultado se descartaban del total salvo que el Sheet ya
 * las marcara CONCLUSION=FUERA DE TIEMPO. El Sheet en realidad evalúa
 * DENTRO DE TIEMPO / FUERA DE TIEMPO también mientras siguen pendientes
 * (no espera a que se entreguen), así que deben contar igual que las
 * entregadas: se ubican por LEAD_TIME y basta con que caigan en el mes.
 */
class LeadTimeCalculoTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'NUMERO_ORDEN', 'SEDE', 'TIPO', 'PRODUCTO', 'TIPO_DE_TRABAJO',
        'META', 'SOLICITADO', 'LEAD_TIME', 'TIME', 'ATRASO', 'CONCLUSION',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // El driver de caché en tests es 'array' (in-memory, persiste entre
        // métodos de la misma clase): se limpia para que cada test arme su
        // propio Sheet sin arrastrar el resultado cacheado de otro.
        Cache::flush();
        Carbon::setTestNow(Carbon::now()->startOfMonth()->addDays(14)->setTime(10, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin(): User
    {
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('admin');
        $this->withSession(['2fa_verified' => true]);
        return $user;
    }

    private function fila(
        string $conclusion,
        string $time,
        string $leadTime,
        string $tipo = 'NOX',
        string $atraso = '0',
        string $solicitado = '',
        string $meta = '5',
    ): array {
        return [
            'ORD-' . uniqid(), 'AREQUIPA', 'LUNA', 'ARMAZON', $tipo,
            $meta, $solicitado, $leadTime, $time, $atraso, $conclusion,
        ];
    }

    private function mockSheet(array $filas): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) use ($filas) {
            $mock->shouldReceive('getSheetDataFromSpreadsheet')
                ->andReturn(array_merge([self::HEADERS], $filas));
        });
    }

    public function test_pendiente_dentro_de_tiempo_con_lead_time_en_el_mes_cuenta_en_el_total(): void
    {
        $hoy = Carbon::now();
        $year = $hoy->year;
        $month = $hoy->month;
        $leadTimeEnMes = $hoy->copy()->endOfMonth()->format('Y-m-d');

        $this->mockSheet([
            // 1) Entregada, en tiempo, TIME en el mes -> cuenta.
            $this->fila('DENTRO DE TIEMPO', $hoy->copy()->startOfMonth()->addDays(2)->format('Y-m-d'), $hoy->copy()->startOfMonth()->addDays(3)->format('Y-m-d')),
            // 2) Entregada, fuera de tiempo, TIME en el mes -> cuenta.
            $this->fila('FUERA DE TIEMPO', $hoy->copy()->startOfMonth()->addDays(5)->format('Y-m-d'), $hoy->copy()->startOfMonth()->addDays(3)->format('Y-m-d'), atraso: '2'),
            // 3) PENDIENTE, DENTRO DE TIEMPO, LEAD_TIME en el mes -> antes se
            //    descartaba por completo; ahora debe contar como en_tiempo.
            $this->fila('DENTRO DE TIEMPO', '', $leadTimeEnMes),
            // 4) PENDIENTE, FUERA DE TIEMPO, LEAD_TIME en el mes -> ya contaba
            //    antes del fix, se mantiene igual.
            $this->fila('FUERA DE TIEMPO', 'PENDIENTE', $leadTimeEnMes, atraso: '3'),
            // 5) PENDIENTE, DENTRO DE TIEMPO, pero LEAD_TIME es del mes
            //    siguiente -> no debe contar en este mes.
            $this->fila('DENTRO DE TIEMPO', '', $hoy->copy()->addMonthNoOverflow()->format('Y-m-d')),
            // 6) Entregada, pero TIME es de otro mes -> no debe contar.
            $this->fila('DENTRO DE TIEMPO', $hoy->copy()->subMonthNoOverflow()->format('Y-m-d'), $hoy->copy()->subMonthNoOverflow()->format('Y-m-d')),
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.data', ['year' => $year, 'month' => $month]));

        $resp->assertOk();
        $general = $resp->json('data.general');

        // Solo las filas 1-4 caen en el mes consultado.
        $this->assertSame(4, $general['total']);
        $this->assertSame(2, $general['total_en_tiempo']); // filas 1 y 3
        $this->assertSame(2, $general['total_fuera']);      // filas 2 y 4
        $this->assertEqualsWithDelta(50.0, $general['porcentaje'], 0.01);
    }

    public function test_pendiente_sin_conclusion_evaluable_no_rompe_el_conteo(): void
    {
        $hoy = Carbon::now();
        $leadTimeEnMes = $hoy->copy()->endOfMonth()->format('Y-m-d');

        // CONCLUSION vacía/no evaluable (ni DENTRO ni FUERA DE TIEMPO): la
        // orden se ubica igual en el mes por su LEAD_TIME (queda en el
        // total), pero no suma ni a en_tiempo ni a fuera.
        $this->mockSheet([
            $this->fila('', '', $leadTimeEnMes),
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.data', ['year' => $hoy->year, 'month' => $hoy->month]));

        $resp->assertOk();
        $general = $resp->json('data.general');

        $this->assertSame(1, $general['total']);
        $this->assertSame(0, $general['total_en_tiempo']);
        $this->assertSame(0, $general['total_fuera']);
    }

    /**
     * CONCLUSION="SIN DATOS": el Sheet no pudo calcular el veredicto (falta
     * LEAD_TIME/META) para una orden que YA se entregó. Sin evidencia de
     * atraso, cuenta como en tiempo — confirmado con negocio.
     */
    public function test_sin_datos_cuenta_como_en_tiempo(): void
    {
        $hoy  = Carbon::now();
        $dia3 = $hoy->copy()->startOfMonth()->addDays(2)->format('Y-m-d');

        $this->mockSheet([
            $this->fila('SIN DATOS', $dia3, '', tipo: 'NOX'),
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.data', ['year' => $hoy->year, 'month' => $hoy->month]));

        $resp->assertOk();
        $data = $resp->json('data');

        $this->assertSame(1, $data['general']['total']);
        $this->assertSame(1, $data['general']['total_en_tiempo']);
        $this->assertSame(0, $data['general']['total_fuera']);

        // También en la tarjeta de su categoría (NOX).
        $this->assertSame(1, $data['categorias']['NOX']['total']);
        $this->assertEquals(100.0, $data['categorias']['NOX']['porcentaje_cumplimiento']);
    }

    /**
     * El filtro de tipo de trabajo válido solo aplica dentro de las
     * tarjetas por categoría (NOX/NOXLS/TD/TDLS/DEVABLUE/BLANCO/COLOREADO)
     * — el Cumplimiento General cuenta TODAS las órdenes, incluyendo "SIN"
     * (sin tratamiento, que no pertenece a ninguna categoría). "NOXLS" y
     * "TDLS" son categorías propias, NO variantes de NOX/TD — cada una
     * tiene su propia tarjeta (solo "BLANCOS" es alias real de "BLANCO").
     */
    public function test_tipos_de_trabajo_invalidos_cuentan_en_general_pero_sin_no_entra_a_ninguna_categoria(): void
    {
        $hoy = Carbon::now();
        $leadTimeEnMes = $hoy->copy()->endOfMonth()->format('Y-m-d');

        $this->mockSheet([
            $this->fila('DENTRO DE TIEMPO', '', $leadTimeEnMes, tipo: 'NOX'),
            $this->fila('FUERA DE TIEMPO', '', $leadTimeEnMes, tipo: 'SIN'),      // sin tratamiento
            $this->fila('DENTRO DE TIEMPO', '', $leadTimeEnMes, tipo: 'TDLS'),    // categoría propia
            $this->fila('DENTRO DE TIEMPO', '', $leadTimeEnMes, tipo: 'NOXLS'),   // categoría propia
            $this->fila('DENTRO DE TIEMPO', '', $leadTimeEnMes, tipo: 'BLANCOS'), // alias válido de BLANCO
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.data', ['year' => $hoy->year, 'month' => $hoy->month]));

        $resp->assertOk();
        $data = $resp->json('data');

        // Cumplimiento General: cuenta las 5, sin importar el tipo.
        $this->assertSame(5, $data['general']['total']);
        $this->assertSame(4, $data['general']['total_en_tiempo']); // NOX, TDLS, NOXLS, BLANCOS
        $this->assertSame(1, $data['general']['total_fuera']);     // SIN

        // Cada tarjeta ve solo su tipo exacto: NOXLS y TDLS NO suman a NOX/TD.
        $this->assertSame(1, $data['categorias']['NOX']['total']);
        $this->assertSame(1, $data['categorias']['NOXLS']['total']);
        $this->assertSame(0, $data['categorias']['TD']['total']);
        $this->assertSame(1, $data['categorias']['TDLS']['total']);
        $this->assertSame(1, $data['categorias']['BLANCO']['total']);
        $this->assertSame(0, $data['categorias']['DEVABLUE']['total']);
        $this->assertSame(0, $data['categorias']['COLOREADO']['total']);
    }
}
