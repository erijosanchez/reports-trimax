<?php

namespace Tests\Feature\VentaCliente;

use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cubre el comando trimax:sync-venta-clientes — lee Google Sheets
 * (Venta_Historica) vía GoogleSheetsService y hace upsert a
 * venta_clientes_historico agregando por clave sede+ruc+anio+mes.
 * No pega a la red real: mockea GoogleSheetsService directamente
 * (el comando lo recibe por inyección de dependencia en el constructor).
 */
class SyncVentaClientesTest extends TestCase
{
    use RefreshDatabase;

    private const SPREADSHEET_ID = 'test-spreadsheet-id';

    protected function setUp(): void
    {
        parent::setUp();
        config(['google.venta_clientes_spreadsheet_id' => self::SPREADSHEET_ID]);
    }

    private function mockSheet(array $filas): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) use ($filas) {
            $mock->shouldReceive('getRawRowsFromSpreadsheet')
                ->once()
                ->with(self::SPREADSHEET_ID, 'Venta_Historica', 'A:F')
                ->andReturn($filas);
        });
    }

    public function test_sincroniza_filas_validas_con_decimal_por_coma_o_por_punto(): void
    {
        $this->mockSheet([
            ['AREQUIPA', '20123456789', 'Cliente Uno', '2026', 'JUNIO', '1500.50'],
            ['LINCE', '10000000009', 'Cliente Dos', '2026', 'JUNIO', '1500,50'],
        ]);

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);

        $this->assertDatabaseHas('venta_clientes_historico', [
            'sede' => 'AREQUIPA', 'ruc' => '20123456789', 'razon_social' => 'Cliente Uno',
            'anio' => 2026, 'mes' => 6, 'importe' => 1500.50,
        ]);
        $this->assertDatabaseHas('venta_clientes_historico', [
            'sede' => 'LINCE', 'ruc' => '10000000009', 'razon_social' => 'Cliente Dos',
            'anio' => 2026, 'mes' => 6, 'importe' => 1500.50,
        ]);
    }

    public function test_suma_filas_duplicadas_de_la_misma_clave_en_vez_de_pisarlas(): void
    {
        $this->mockSheet([
            ['LINCE', '10000000001', 'Cliente Dup', '2026', 'MAYO', '100'],
            ['LINCE', '10000000001', 'Cliente Dup', '2026', 'MAYO', '50'],
        ]);

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);

        $this->assertSame(1, DB::table('venta_clientes_historico')->count());
        $this->assertDatabaseHas('venta_clientes_historico', [
            'sede' => 'LINCE', 'ruc' => '10000000001', 'anio' => 2026, 'mes' => 5, 'importe' => 150,
        ]);
    }

    public function test_descarta_filas_sin_sede_ruc_anio_o_mes_valido(): void
    {
        $this->mockSheet([
            ['', '10000000002', 'Sin Sede', '2026', 'JUNIO', '100'],
            ['LINCE', '', 'Sin Ruc', '2026', 'JUNIO', '100'],
            ['LINCE', '10000000003', 'Anio Invalido', 'ABC', 'JUNIO', '100'],
            ['LINCE', '10000000004', 'Mes Invalido', '2026', 'MESRARO', '100'],
            ['LINCE', '10000000005', 'Cliente Valido', '2026', 'JUNIO', '200'],
        ]);

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);

        $this->assertSame(1, DB::table('venta_clientes_historico')->count());
        $this->assertDatabaseHas('venta_clientes_historico', ['ruc' => '10000000005', 'importe' => 200]);
    }

    public function test_reconoce_setiembre_con_o_sin_la_p(): void
    {
        $this->mockSheet([
            ['LINCE', '10000000007', 'Cliente Set', '2026', 'SETIEMBRE', '100'],
            ['LINCE', '10000000008', 'Cliente Sept', '2026', 'SEPTIEMBRE', '100'],
        ]);

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);

        $this->assertDatabaseHas('venta_clientes_historico', ['ruc' => '10000000007', 'mes' => 9]);
        $this->assertDatabaseHas('venta_clientes_historico', ['ruc' => '10000000008', 'mes' => 9]);
    }

    public function test_falla_sin_spreadsheet_id_configurado(): void
    {
        config(['google.venta_clientes_spreadsheet_id' => '']);

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(1);
        $this->assertSame(0, DB::table('venta_clientes_historico')->count());
    }

    public function test_resincronizar_actualiza_el_valor_en_vez_de_acumularlo(): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) {
            $mock->shouldReceive('getRawRowsFromSpreadsheet')
                ->twice()
                ->with(self::SPREADSHEET_ID, 'Venta_Historica', 'A:F')
                ->andReturn(
                    [['LINCE', '10000000006', 'Cliente Update', '2026', 'JUNIO', '100']],
                    [['LINCE', '10000000006', 'Cliente Update', '2026', 'JUNIO', '999']],
                );
        });

        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);
        $this->artisan('trimax:sync-venta-clientes')->assertExitCode(0);

        $this->assertSame(1, DB::table('venta_clientes_historico')->count());
        $this->assertDatabaseHas('venta_clientes_historico', ['ruc' => '10000000006', 'importe' => 999]);
    }
}
