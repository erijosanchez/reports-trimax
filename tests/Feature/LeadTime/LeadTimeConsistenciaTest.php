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
 * Objetivo + ubicaba las órdenes por TIME (fecha de entrega), exigiendo que
 * exista. Las pendientes-ya-vencidas (sin TIME, ubicadas por LEAD_TIME en
 * las otras 2 vistas) quedaban fuera por completo.
 *
 * El filtro de tipo de trabajo válido (NOX/TD/DEVABLUE/BLANCO/COLOREADO)
 * aplica solo dentro de las tarjetas por categoría — el conteo general
 * (Cumplimiento General) de las 3 vistas cuenta TODAS las órdenes, sin
 * importar el tipo.
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
            // 5) Tipo inválido, FUERA DE TIEMPO -> cuenta en el general de las
            //    3 vistas (no en ninguna tarjeta de categoría).
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

        // Dashboard mensual (Cumplimiento General): cuenta las 5 filas, tipo
        // inválido incluido.
        $this->assertSame(5, $mensual['total']);
        $this->assertSame(2, $mensual['total_en_tiempo']); // 1 y 3
        $this->assertSame(3, $mensual['total_fuera']);     // 2, 4 y 5

        // Semanal: mismo total que el mensual.
        $this->assertSame(5, array_sum($semanal['dayOrders']));

        // Objetivo+: acumulado "fuera de tiempo" del año (solo hay datos de
        // este mes en el fixture) -> debe dar exactamente 3, igual que el
        // total_fuera del dashboard mensual (filas 2, 4 y 5). Antes del fix
        // de LEAD_TIME daba 1 (perdía la fila 4, la pendiente-ya-vencida).
        $this->assertSame(3, $objetivo['general']['totales']['acum']['cant']);

        // Pero la tarjeta de categoría "TD" en Objetivo+ solo ve su fila (2),
        // no la de tipo inválido "SIN" (5) ni las de otras categorías.
        $this->assertSame(1, $objetivo['categorias']['TD']['totales']['acum']['cant']);
    }
}
