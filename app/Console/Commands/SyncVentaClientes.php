<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncVentaClientes extends Command
{
    protected $signature = 'trimax:sync-venta-clientes';

    protected $description = 'Sincroniza venta por cliente desde Google Sheets (Venta_Historica) a MySQL (venta_clientes_historico)';

    private array $meses = [
        'ENERO' => 1,
        'FEBRERO' => 2,
        'MARZO' => 3,
        'ABRIL' => 4,
        'MAYO' => 5,
        'JUNIO' => 6,
        'JULIO' => 7,
        'AGOSTO' => 8,
        'SETIEMBRE' => 9,
        'SEPTIEMBRE' => 9,
        'OCTUBRE' => 10,
        'NOVIEMBRE' => 11,
        'DICIEMBRE' => 12,
    ];

    public function __construct(protected GoogleSheetsService $sheets)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $inicio = microtime(true);

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $this->info('🔄 Iniciando sincronización de venta por cliente...');

        try {
            $spreadsheetId = config('google.venta_clientes_spreadsheet_id');

            if (!$spreadsheetId) {
                $this->error('❌ GOOGLE_VENTA_CLIENTES_SPREADSHEET_ID no configurado');
                return self::FAILURE;
            }

            $this->info('📡 Leyendo Google Sheets (Venta_Historica A:F)...');
            $rows = $this->sheets->getRawRowsFromSpreadsheet($spreadsheetId, 'Venta_Historica', 'A:F');

            if (empty($rows)) {
                $this->error('❌ No se obtuvieron datos del sheet');
                return self::FAILURE;
            }

            $this->info('📊 Filas obtenidas: ' . count($rows));

            // Se agrega en memoria por clave natural porque el sheet puede traer
            // varias filas para el mismo sede+ruc+año+mes (se suman, no se pisan).
            $acumulado = [];

            foreach ($rows as $row) {
                $sede    = strtoupper(trim($row[0] ?? ''));
                $ruc     = trim($row[1] ?? '');
                $razon   = trim($row[2] ?? '');
                $anioR   = trim($row[3] ?? '');
                $mesR    = strtoupper(trim($row[4] ?? ''));
                $importe = $this->limpiarNumero($row[5] ?? 0);

                if (!$sede || !$ruc || !is_numeric($anioR) || !isset($this->meses[$mesR])) {
                    continue;
                }

                $anio = (int) $anioR;
                $mes  = $this->meses[$mesR];
                $key  = "{$sede}|{$ruc}|{$anio}|{$mes}";

                if (!isset($acumulado[$key])) {
                    $acumulado[$key] = [
                        'sede'         => $sede,
                        'ruc'          => $ruc,
                        'razon_social' => $razon ?: null,
                        'anio'         => $anio,
                        'mes'          => $mes,
                        'importe'      => 0,
                        'updated_at'   => now(),
                        'created_at'   => now(),
                    ];
                }

                $acumulado[$key]['importe'] += $importe;
                if ($razon) {
                    $acumulado[$key]['razon_social'] = $razon;
                }
            }

            unset($rows);

            $total = 0;
            foreach (array_chunk(array_values($acumulado), 500) as $lote) {
                DB::table('venta_clientes_historico')->upsert(
                    $lote,
                    ['sede', 'ruc', 'anio', 'mes'],
                    ['razon_social', 'importe', 'updated_at']
                );
                $total += count($lote);
            }

            $seg = round(microtime(true) - $inicio, 1);
            $this->info("✅ venta_clientes_historico: {$total} registros upserted en {$seg}s");

            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('❌ SyncVentaClientes: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function limpiarNumero($valor): float
    {
        if (empty($valor)) return 0.0;
        $v = str_replace([',', ' '], ['.', ''], trim((string) $valor));
        return floatval(preg_replace('/[^0-9.\-]/', '', $v));
    }
}
