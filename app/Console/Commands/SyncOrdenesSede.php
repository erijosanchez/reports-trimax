<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SyncOrdenesSede extends Command
{
    protected $signature = 'trimax:sync-ordenes-sede
                                {--meses=2 : Cuántos meses hacia atrás procesar en ordenes_sede_stats}
                                {--full : Forzar sincronización completa del histórico}';

    protected $description = 'Sincroniza órdenes desde Google Sheets a MySQL (ordenes_sede_stats + ordenes_historico)';

    // Usuarios excluidos del semáforo (ordenes_sede_stats)
    private array $excluidos = [
        'yeny sarmiento calero',
        'ruth vasquez alcántara', 
        'rafael ml',
        'rafael mendez',
    ];

    // Estados excluidos del conteo CANT en el semáforo
    // Anulado y Merma no cuentan como órdenes reales del día
    private array $estadosExcluidos = [
        'ANULADO',
        'MERMA',
    ];

    public function __construct(protected GoogleSheetsService $sheets)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $inicio = microtime(true);

        ini_set('memory_limit',       '1024M');
        ini_set('max_execution_time', '600');

        $this->info('🔄 Iniciando sincronización de órdenes...');

        try {
            // ── Leer sheet completo A:Q ──────────────────────────────────────────
            $this->info('📡 Leyendo Google Sheets (Historico A:Q)...');
            $rows = $this->sheets->getRawRows('Historico', 'A:Q');

            if (empty($rows)) {
                $this->error('❌ No se obtuvieron datos del sheet');
                return self::FAILURE;
            }

            $this->info('📊 Filas obtenidas: ' . count($rows));

            // ── Rango para ordenes_sede_stats ────────────────────────────────────
            $meses      = (int) $this->option('meses');
            $fechaDesde = Carbon::now()->subMonths($meses)->startOfMonth();
            $this->info("📅 Stats desde: {$fechaDesde->format('d/m/Y')}");

            // ── Variables ────────────────────────────────────────────────────────
            $conteosSede    = [];
            $ordenesInsert  = [];
            $batchSize      = 500;
            $totalHistorico = 0;
            $totalStats     = 0;

            // ── Loop principal ───────────────────────────────────────────────────
            foreach ($rows as $row) {
                $sede        = trim($row[0]  ?? '');  // A
                $numOrden    = trim($row[1]  ?? '');  // B
                $ruc         = trim($row[2]  ?? '');  // C
                $cliente     = trim($row[3]  ?? '');  // D
                $diseno      = trim($row[4]  ?? '');  // E
                $descProd    = trim($row[5]  ?? '');  // F
                $importeRaw  = trim($row[6]  ?? '');  // G
                $ordenCompra = trim($row[7]  ?? '');  // H
                $fechaRaw    = trim($row[8]  ?? '');  // I
                $horaOrden   = trim($row[9]  ?? '');  // J
                $tipoOrden   = trim($row[10] ?? '');  // K
                $usuario     = trim($row[11] ?? '');  // L
                $estado      = trim($row[12] ?? '');  // M
                $ubicacion   = trim($row[13] ?? '');  // N
                $descTallado = trim($row[14] ?? '');  // O
                $tratamiento = trim($row[15] ?? '');  // P
                $leadTimeRaw = trim($row[16] ?? '');  // Q

                if (!$numOrden || !$fechaRaw || $fechaRaw === '-') continue;

                $timestamp = $this->parsearFecha($fechaRaw);
                if (!$timestamp) continue;

                $fechaMySQL   = date('Y-m-d', $timestamp);
                $mes          = (int) date('n', $timestamp);
                $anio         = (int) date('Y', $timestamp);
                $usuarioLower = strtolower($usuario);
                $estadoUpper  = strtoupper($estado);

                // ── ordenes_historico (TODAS las filas sin excepción) ────────────
                $ordenesInsert[] = [
                    'descripcion_sede'     => $sede         ?: null,
                    'numero_orden'         => $numOrden,
                    'ruc'                  => $ruc           ?: null,
                    'cliente'              => $cliente        ?: null,
                    'diseno'               => $diseno         ?: null,
                    'descripcion_producto' => $descProd       ?: null,
                    'importe'              => is_numeric(str_replace(',', '.', $importeRaw))
                        ? (float) str_replace(',', '.', $importeRaw)
                        : null,
                    'orden_compra'         => $ordenCompra    ?: null,
                    'fecha_orden'          => $fechaMySQL,
                    'hora_orden'           => $horaOrden      ?: null,
                    'tipo_orden'           => $tipoOrden      ?: null,
                    'nombre_usuario'       => $usuario         ?: null,
                    'estado_orden'         => $estado          ?: null,
                    'ubicacion_orden'      => $ubicacion       ?: null,
                    'descripcion_tallado'  => $descTallado     ?: null,
                    'tratamiento'          => $tratamiento     ?: null,
                    'lead_time'            => is_numeric($leadTimeRaw) ? (int) $leadTimeRaw : null,
                    'mes'                  => $mes,
                    'anio'                 => $anio,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                if (count($ordenesInsert) >= $batchSize) {
                    $this->upsertHistorico($ordenesInsert);
                    $totalHistorico += count($ordenesInsert);
                    $ordenesInsert   = [];
                    $this->output->write('.');
                }

                // ── ordenes_sede_stats (con filtros del semáforo) ────────────────
                if (!$sede)                                           continue; // sin sede
                if (in_array($usuarioLower, $this->excluidos))       continue; // usuario excluido
                if ($timestamp < $fechaDesde->timestamp)             continue; // fuera de rango
                if (in_array($estadoUpper, $this->estadosExcluidos)) continue; // ✅ excluir Anulado y Merma

                $key = "{$sede}|{$fechaMySQL}";

                if (!isset($conteosSede[$key])) {
                    $conteosSede[$key] = [
                        'sede'       => $sede,
                        'fecha'      => $fechaMySQL,
                        'mes'        => $mes,
                        'anio'       => $anio,
                        'cant'       => 0,
                        'facturadas' => 0,
                    ];
                }

                $conteosSede[$key]['cant']++;

                if ($estadoUpper === 'FACTURADO') {
                    $conteosSede[$key]['facturadas']++;
                }
            }

            // Flush último lote historico
            if (!empty($ordenesInsert)) {
                $this->upsertHistorico($ordenesInsert);
                $totalHistorico += count($ordenesInsert);
            }

            $this->newLine();
            unset($rows, $ordenesInsert);
            gc_collect_cycles();

            $this->info("✅ ordenes_historico: {$totalHistorico} registros procesados");

            // ── Upsert ordenes_sede_stats ────────────────────────────────────────
            if (!empty($conteosSede)) {
                foreach (array_chunk(array_values($conteosSede), $batchSize) as $lote) {
                    DB::table('ordenes_sede_stats')->upsert(
                        $lote,
                        ['sede', 'fecha'],
                        ['cant', 'facturadas', 'mes', 'anio', 'updated_at']
                    );
                    $totalStats += count($lote);
                }
            }

            unset($conteosSede);

            $this->info("✅ ordenes_sede_stats: {$totalStats} registros upserted");

            // ── Limpiar caché semáforo ───────────────────────────────────────────
            foreach (range(1, 12) as $m) {
                foreach ([date('Y'), date('Y') - 1] as $a) {
                    Cache::store('file')->forget("ordenes_por_sede_{$m}_{$a}");
                }
            }

            $seg = round(microtime(true) - $inicio, 1);
            $this->info("🏁 Sync completo en {$seg}s");

            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('❌ SyncOrdenesSede: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function upsertHistorico(array $lote): void
    {
        DB::table('ordenes_historico')->upsert(
            $lote,
            ['numero_orden'],
            [
                'descripcion_sede',
                'ruc',
                'cliente',
                'diseno',
                'descripcion_producto',
                'importe',
                'orden_compra',
                'fecha_orden',
                'hora_orden',
                'tipo_orden',
                'nombre_usuario',
                'estado_orden',
                'ubicacion_orden',
                'descripcion_tallado',
                'tratamiento',
                'lead_time',
                'mes',
                'anio',
                'updated_at',
            ]
        );
    }

    private function parsearFecha(string $fechaStr): ?int
    {
        $s = trim($fechaStr);

        // DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]);
        }
        // YYYY-MM-DD (con o sin hora)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[3], (int)$m[1]);
        }

        $ts = strtotime($s);
        return ($ts && $ts > 0) ? $ts : null;
    }
}
