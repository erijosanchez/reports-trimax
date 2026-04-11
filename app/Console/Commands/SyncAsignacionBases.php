<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SyncAsignacionBases extends Command
{
    protected $signature = 'trimax:sync-asignacion-bases
                                {--full : Forzar sincronización completa ignorando último sync}';

    protected $description = 'Sincroniza Asignación de Bases desde Google Sheets a MySQL (asignacion_bases)';

    // ID del spreadsheet de Asignación de Bases
    private string $spreadsheetId = '1y_uncIs1INpj3qyI0Z8C-X52JeTtZBxKO2_n2EYpOgg';

    public function __construct(protected GoogleSheetsService $sheets)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $inicio = microtime(true);

        ini_set('memory_limit',       '1024M');
        ini_set('max_execution_time', '600');

        $this->info('🔄 Iniciando sincronización de Asignación de Bases...');

        try {
            // ── Leer sheet DATA completo ─────────────────────────────────────────
            $this->info('📡 Leyendo Google Sheets (Asignación_de_bases → DATA A:K)...');

            // Usamos el mismo GoogleSheetsService pero apuntando al sheet de bases
            $data = $this->sheets->getSheetDataFromSpreadsheet(
                $this->spreadsheetId,
                'DATA',
                'A:K'
            );

            // Saltar header
            array_shift($data);
            $rows = $data;
            unset($data);

            if (empty($rows)) {
                $this->error('❌ No se obtuvieron datos del sheet');
                return self::FAILURE;
            }

            $this->info('📊 Filas obtenidas: ' . count($rows));

            // ── Limpiar tabla antes de reinsertar ────────────────────────────────
            $this->info('🗑️  Limpiando tabla asignacion_bases...');
            DB::table('asignacion_bases')->truncate();

            // ── Variables ────────────────────────────────────────────────────────
            $registros  = [];
            $batchSize  = 200;
            $total      = 0;
            $omitidas   = 0;
            $razonesOmision = [
                'sin_orden_o_fecha' => 0,
                'fecha_invalida'    => 0,
                'estado_invalido'   => 0,
            ];

            // ── Loop principal ───────────────────────────────────────────────────
            foreach ($rows as $row) {
                $fechaRaw    = trim($row[0]  ?? '');  // A: fecha_asignacion
                $numOrden    = trim($row[1]  ?? '');  // B: numero_orden
                $codigoPt    = trim($row[2]  ?? '');  // C: Codigo_PT
                $productoPt  = trim($row[3]  ?? '');  // D: Producto_PT
                $idCatalogo  = trim($row[4]  ?? '');  // E: id_catalogo_base
                $descBase    = trim($row[5]  ?? '');  // F: Descripcion_base
                $cantidad    = trim($row[6]  ?? '');  // G: cantidad
                $ojo         = strtoupper(trim($row[7]  ?? '')); // H: ojo (D/I)
                $estado      = strtoupper(trim($row[8]  ?? '')); // I: estado_asignacion (R/N)
                $descArt     = trim($row[9]  ?? '');  // J: Descripción del artículo
                $precioRaw   = trim($row[10] ?? '');  // K: Precio de artículo

                // Saltar filas sin datos clave
                if (!$numOrden || !$fechaRaw) {
                    $omitidas++;
                    $razonesOmision['sin_orden_o_fecha']++;
                    continue;
                }

                // Parsear fecha
                $fecha = $this->parsearFecha($fechaRaw);
                if (!$fecha) {
                    $omitidas++;
                    $razonesOmision['fecha_invalida']++;
                    continue;
                }

                // Solo R y N válidos
                if (!in_array($estado, ['R', 'N'])) {
                    $omitidas++;
                    $razonesOmision['estado_invalido']++;
                    continue;
                }

                // Limpiar precio "S/ 5.73" → 5.73
                $precio = null;
                if ($precioRaw) {
                    $precioLimpio = preg_replace('/[^0-9.]/', '', str_replace(',', '', $precioRaw));
                    $precio = is_numeric($precioLimpio) ? (float) $precioLimpio : null;
                }

                $mes  = (int) $fecha->format('n');
                $anio = (int) $fecha->format('Y');

                $registros[] = [
                    'fecha_asignacion'  => $fecha->format('Y-m-d'),
                    'numero_orden'      => $numOrden,
                    'codigo_pt'         => $codigoPt   ?: null,
                    'producto_pt'       => $productoPt ?: null,
                    'id_catalogo_base'  => $idCatalogo ?: null,
                    'descripcion_base'  => $descBase   ?: null,
                    'cantidad'          => is_numeric($cantidad) ? (int) $cantidad : 1,
                    'ojo'               => in_array($ojo, ['D', 'I']) ? $ojo : null,
                    'estado_asignacion' => $estado,
                    'descripcion_art'   => $descArt    ?: null,
                    'precio'            => $precio,
                    'mes'               => $mes,
                    'anio'              => $anio,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                // Flush por lotes
                if (count($registros) >= $batchSize) {
                    $this->insertBases($registros);
                    $total     += count($registros);
                    $registros  = [];
                    $this->output->write('.');
                }
            }

            // Flush último lote
            if (!empty($registros)) {
                $this->insertBases($registros);
                $total += count($registros);
            }

            $this->newLine();
            unset($rows, $registros);
            gc_collect_cycles();

            $this->info("✅ asignacion_bases: {$total} registros procesados");
            $this->info("⏭️  Filas omitidas: {$omitidas}");
            if ($omitidas > 0) {
                $this->info("   ├─ Sin número orden o fecha: {$razonesOmision['sin_orden_o_fecha']}");
                $this->info("   ├─ Fecha con formato inválido: {$razonesOmision['fecha_invalida']}");
                $this->info("   └─ Estado inválido (no R/N): {$razonesOmision['estado_invalido']}");
            }

            // ── Limpiar caché del módulo ─────────────────────────────────────────
            $this->limpiarCache();
            $this->info('🧹 Caché limpiada');

            $seg = round(microtime(true) - $inicio, 1);
            $this->info("🏁 Sync completo en {$seg}s");

            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('❌ SyncAsignacionBases: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function insertBases(array $lote, int $intentos = 3): void
    {
        for ($i = 1; $i <= $intentos; $i++) {
            try {
                DB::table('asignacion_bases')->insert($lote);
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                // 1213 = deadlock, reintentamos
                if ($e->getCode() === '40001' && $i < $intentos) {
                    usleep(200000 * $i);
                    continue;
                }
                throw $e;
            }
        }
    }

    private function parsearFecha(string $fechaStr): ?Carbon
    {
        $s = trim($fechaStr);
        if (!$s || $s === '-') return null;

        // YYYY-MM-DD (formato del sheet de bases)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m)) {
            try {
                return Carbon::createFromFormat('Y-m-d', "{$m[1]}-{$m[2]}-{$m[3]}");
            } catch (\Exception $e) {
                return null;
            }
        }

        // DD/MM/YYYY (por si acaso)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            try {
                return Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}");
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            $ts = strtotime($s);
            return ($ts && $ts > 0) ? Carbon::createFromTimestamp($ts) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function limpiarCache(): void
    {
        $anios = DB::table('asignacion_bases')
            ->distinct()
            ->pluck('anio')
            ->toArray();

        foreach ($anios as $anio) {
            Cache::forget("asignacion_evolutivo_semanal_{$anio}");
            Cache::forget("asignacion_evolutivo_mensual_{$anio}");
            Cache::forget("asignacion_grafico_diario_{$anio}");
            Cache::forget("asignacion_grafico_barras_{$anio}");
            Cache::forget("asignacion_demanda_mensual_{$anio}");

            for ($m = 1; $m <= 12; $m++) {
                Cache::forget("asignacion_demanda_semanal_{$anio}_{$m}");
            }
        }

        Cache::forget('asignacion_bases_raw');
    }
}
