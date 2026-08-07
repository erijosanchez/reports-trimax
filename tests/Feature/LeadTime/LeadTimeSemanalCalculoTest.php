<?php

namespace Tests\Feature\LeadTime;

use App\Models\User;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre LeadTimeController::processSemanalData (endpoint getSemanalData) —
 * mismo bug que LeadTimeCalculoTest fija para el dashboard mensual, pero acá
 * en la vista Semanal: las órdenes PENDIENTES (sin TIME) cuyo LEAD_TIME cae
 * en el mes consultado se descartaban del desglose diario/semanal/mensual
 * salvo que el Sheet ya las marcara FUERA DE TIEMPO. El Sheet evalúa
 * DENTRO/FUERA DE TIEMPO también mientras siguen pendientes, así que deben
 * contar igual que las entregadas, ubicadas por LEAD_TIME.
 */
class LeadTimeSemanalCalculoTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'NUMERO_ORDEN', 'SEDE', 'TIPO', 'PRODUCTO', 'TIPO_DE_TRABAJO',
        'META', 'SOLICITADO', 'LEAD_TIME', 'TIME', 'ATRASO', 'CONCLUSION',
    ];

    protected function setUp(): void
    {
        parent::setUp();
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
    ): array {
        return [
            'ORD-' . uniqid(), 'AREQUIPA', 'LUNA', 'ARMAZON', $tipo,
            '5', '', $leadTime, $time, $atraso, $conclusion,
        ];
    }

    private function mockSheet(array $filas): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) use ($filas) {
            $mock->shouldReceive('getSheetDataFromSpreadsheet')
                ->andReturn(array_merge([self::HEADERS], $filas));
        });
    }

    public function test_pendiente_dentro_de_tiempo_con_lead_time_en_el_mes_cuenta_en_dia_y_mes(): void
    {
        $hoy   = Carbon::now();
        $year  = $hoy->year;
        $month = $hoy->month;

        $diaPendiente = Carbon::create($year, $month, 5)->format('Y-m-d');
        $diaEntregada = Carbon::create($year, $month, 10)->format('Y-m-d');
        $mesSiguiente = $hoy->copy()->addMonthNoOverflow()->startOfMonth()->format('Y-m-d');

        $this->mockSheet([
            // Pendiente, DENTRO DE TIEMPO, LEAD_TIME cae el día 5 del mes
            // consultado -> antes se descartaba por completo; ahora debe
            // contar en ese día y en el acumulado del mes.
            $this->fila('DENTRO DE TIEMPO', '', $diaPendiente),
            // Entregada, DENTRO DE TIEMPO, TIME el día 10 -> ya contaba antes,
            // sirve de control positivo.
            $this->fila('DENTRO DE TIEMPO', $diaEntregada, $diaEntregada),
            // Pendiente, DENTRO DE TIEMPO, pero LEAD_TIME es del mes
            // siguiente -> no debe aparecer en este mes.
            $this->fila('DENTRO DE TIEMPO', '', $mesSiguiente),
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.semanal-data', ['year' => $year, 'month' => $month]));

        $resp->assertOk();
        $data = $resp->json('data');

        // Día 5 (índice 4, 0-based): la pendiente cuenta ahí.
        $this->assertSame(1, $data['dayOrders'][4]);
        $this->assertEquals(100.0, $data['dayKpi'][4]);

        // Día 10 (índice 9): la entregada, sin cambios respecto al bug.
        $this->assertSame(1, $data['dayOrders'][9]);
        $this->assertEquals(100.0, $data['dayKpi'][9]);

        // Solo esas 2 órdenes entran al mes -> el acumulado mensual da 100% sobre 2.
        $this->assertCount(1, $data['meses']);
        $this->assertEquals(100.0, $data['monthKpi'][0]);

        // La del mes siguiente no aparece en ningún día de este mes.
        $this->assertSame(2, array_sum($data['dayOrders']));
    }

    public function test_vouchers_sin_lead_time_ni_time_no_rompen_el_calculo(): void
    {
        $hoy   = Carbon::now();
        $year  = $hoy->year;
        $month = $hoy->month;

        // Fila basura: sin TIME ni LEAD_TIME parseable -> fechaDePeriodo()
        // devuelve null y debe descartarse sin errores.
        $this->mockSheet([
            $this->fila('DENTRO DE TIEMPO', '', ''),
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.semanal-data', ['year' => $year, 'month' => $month]));

        $resp->assertOk();
        $this->assertSame(0, array_sum($resp->json('data.dayOrders')));
    }

    /** Mismo criterio de tipos válidos que el dashboard mensual (LeadTimeCalculoTest). */
    public function test_tipos_de_trabajo_fuera_de_las_5_categorias_no_cuentan(): void
    {
        $hoy   = Carbon::now();
        $year  = $hoy->year;
        $month = $hoy->month;
        $dia5  = Carbon::create($year, $month, 5)->format('Y-m-d');

        $this->mockSheet([
            $this->fila('DENTRO DE TIEMPO', '', $dia5, tipo: 'NOX'), // válido -> cuenta
            $this->fila('FUERA DE TIEMPO', '', $dia5, tipo: 'SIN'),  // sin tratamiento -> no cuenta
            $this->fila('DENTRO DE TIEMPO', '', $dia5, tipo: 'TDLS'), // variante no mapeada -> no cuenta
        ]);

        $resp = $this->actingAs($this->admin())
            ->getJson(route('produccion.lead-time.semanal-data', ['year' => $year, 'month' => $month]));

        $resp->assertOk();
        $data = $resp->json('data');

        $this->assertSame(1, $data['dayOrders'][4]);
        $this->assertEquals(100.0, $data['dayKpi'][4]);
        $this->assertSame(1, array_sum($data['dayOrders']));
    }
}
