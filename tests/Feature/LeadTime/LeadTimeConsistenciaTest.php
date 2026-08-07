<?php

namespace Tests\Feature\LeadTime;

use App\Models\User;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verifica que las 3 vistas de Lead Time (dashboard mensual, semanal y
 * Objetivo +) reporten el MISMO total de "fuera de tiempo" para un mes dado,
 * usando el mismo Sheet — no deberían mostrar números distintos entre sí.
 *
 * Objetivo + tenía dos causas de desalineación, corregidas juntas:
 *   1. Ubicaba las órdenes por TIME (fecha de entrega), exigiendo que exista.
 *      Las pendientes-ya-vencidas (sin TIME, ubicadas por LEAD_TIME en las
 *      otras 2 vistas) quedaban fuera por completo.
 *   2. No filtraba por tipo de trabajo válido (NOX/TD/DEVABLUE/BLANCO/
 *      COLOREADO), a diferencia de las otras 2 vistas.
 */
class LeadTimeConsistenciaTest extends TestCase
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

    private function fila(string $conclusion, string $time, string $leadTime, string $tipo, string $atraso = '0'): array
    {
        return ['ORD-' . uniqid(), 'AREQUIPA', 'LUNA', 'ARMAZON', $tipo, '5', '', $leadTime, $time, $atraso, $conclusion];
    }

    private function mockSheet(array $filas): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) use ($filas) {
            $mock->shouldReceive('getSheetDataFromSpreadsheet')
                ->andReturn(array_merge([self::HEADERS], $filas));
        });
    }

    public function test_dashboard_mensual_semanal_y_objetivo_mas_reportan_el_mismo_total_fuera_de_tiempo(): void
    {
        $hoy   = Carbon::now();
        $year  = $hoy->year;
        $month = $hoy->month;
        $dia3  = Carbon::create($year, $month, 3)->format('Y-m-d');
        $dia5  = Carbon::create($year, $month, 5)->format('Y-m-d');
        $dia8  = Carbon::create($year, $month, 8)->format('Y-m-d');  // <= "hoy" (día 15)
        $dia20 = Carbon::create($year, $month, 20)->format('Y-m-d'); // > "hoy"

        $this->mockSheet([
            // 1) Entregada, en tiempo -> cuenta en total/en_tiempo, no en fuera.
            $this->fila('DENTRO DE TIEMPO', $dia3, $dia3, 'NOX'),
            // 2) Entregada, fuera de tiempo -> cuenta en total/fuera en las 3 vistas.
            $this->fila('FUERA DE TIEMPO', $dia5, $dia5, 'TD', atraso: '3'),
            // 3) PENDIENTE, dentro de tiempo, LEAD_TIME futuro dentro del mes ->
            //    cuenta en total/en_tiempo (mensual/semanal), pero NUNCA debe
            //    aparecer en "fuera de tiempo" de ninguna vista (no lo es).
            $this->fila('DENTRO DE TIEMPO', '', $dia20, 'DEVABLUE'),
            // 4) PENDIENTE, ya FUERA DE TIEMPO (venció su LEAD_TIME sin
            //    entregarse) -> el caso que Objetivo+ perdía antes del fix;
            //    debe contar como "fuera" en las 3 vistas por igual.
            $this->fila('FUERA DE TIEMPO', '', $dia8, 'BLANCO'),
            // 5) Tipo inválido, FUERA DE TIEMPO -> no debe contar en ninguna vista.
            $this->fila('FUERA DE TIEMPO', $dia5, $dia5, 'SIN'),
        ]);

        $admin = $this->admin();

        $mensual = $this->actingAs($admin)
            ->getJson(route('produccion.lead-time.data', ['year' => $year, 'month' => $month]))
            ->json('data.general');

        $semanal = $this->actingAs($admin)
            ->getJson(route('produccion.lead-time.semanal-data', ['year' => $year, 'month' => $month]))
            ->json('data');

        $objetivo = $this->actingAs($admin)
            ->getJson(route('produccion.lead-time.objetivo-mas.data', ['year' => $year]))
            ->json('data');

        // Dashboard mensual: filas 1-4 cuentan (5 se descarta por tipo inválido).
        $this->assertSame(4, $mensual['total']);
        $this->assertSame(2, $mensual['total_en_tiempo']); // 1 y 3
        $this->assertSame(2, $mensual['total_fuera']);     // 2 y 4

        // Semanal: mismo total que el mensual.
        $this->assertSame(4, array_sum($semanal['dayOrders']));

        // Objetivo+: acumulado "fuera de tiempo" del año (solo hay datos de
        // este mes en el fixture) -> debe dar exactamente 2, igual que el
        // total_fuera del dashboard mensual. Antes del fix daba 1 (perdía la
        // fila 4, la pendiente-ya-vencida) y sin el filtro de tipo hubiera
        // sumado también la fila 5 (tipo inválido).
        $this->assertSame(2, $objetivo['general']['totales']['acum']['cant']);
    }
}
