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

    private array $excluidos = [
        'yeny sarmiento calero',
        'ruth vasquez alcantara',
        'rafael ml',
        'rafael mendez',
        'juan loayza pacheco',
    ];

    private array $clienteExcluido = [
        'marketing general',
    ];

    private array $estadosExcluidos = [
        'ANULADO',
        'MERMA',
    ];

    public function __construct(protected GoogleSheetsService $sheets)
    {
        parent::__construct();

        // Normalizar arrays de exclusión
        $this->excluidos = array_map(fn($u) => $this->normalizarTexto($u), $this->excluidos);
        $this->clienteExcluido = array_map(fn($c) => $this->normalizarTexto($c), $this->clienteExcluido);
    }

    public function handle(): int
    {
        $inicio = microtime(true);

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '600');

        $this->info('🔄 Iniciando sincronización de órdenes...');

        try {

            $this->info('📡 Leyendo Google Sheets (Historico A:Q)...');
            $rows = $this->sheets->getRawRows('Historico', 'A:Q');

            if (empty($rows)) {
                $this->error('❌ No se obtuvieron datos del sheet');
                return self::FAILURE;
            }

            $this->info('📊 Filas obtenidas: ' . count($rows));

            $meses = (int) $this->option('meses');
            $fechaDesde = Carbon::now()->subMonths($meses)->startOfMonth();

            $this->info("📅 Stats desde: {$fechaDesde->format('d/m/Y')}");

            $conteosSede = [];
            $ordenesInsert = [];
            $batchSize = 500;
            $totalHistorico = 0;
            $totalStats = 0;

            foreach ($rows as $row) {

                $sede = trim($row[0] ?? '');
                $numOrden = trim($row[1] ?? '');
                $ruc = trim($row[2] ?? '');
                $cliente = trim($row[3] ?? '');
                $diseno = trim($row[4] ?? '');
                $descProd = trim($row[5] ?? '');
                $importeRaw = trim($row[6] ?? '');
                $ordenCompra = trim($row[7] ?? '');
                $fechaRaw = trim($row[8] ?? '');
                $horaOrden = trim($row[9] ?? '');
                $tipoOrden = trim($row[10] ?? '');
                $usuario = trim($row[11] ?? '');
                $estado = trim($row[12] ?? '');
                $ubicacion = trim($row[13] ?? '');
                $descTallado = trim($row[14] ?? '');
                $tratamiento = trim($row[15] ?? '');
                $leadTimeRaw = trim($row[16] ?? '');

                if (!$numOrden || !$fechaRaw || $fechaRaw === '-') {
                    continue;
                }

                $timestamp = $this->parsearFecha($fechaRaw);
                if (!$timestamp) continue;

                $fechaMySQL = date('Y-m-d', $timestamp);
                $mes = (int) date('n', $timestamp);
                $anio = (int) date('Y', $timestamp);
                $estadoUpper = strtoupper($estado);

                $usuarioNorm = $this->normalizarTexto($usuario);
                $clienteNorm = $this->normalizarTexto($cliente);

                // ===============================
                // HISTORICO (SIN FILTROS)
                // ===============================

                $ordenesInsert[] = [

                    'descripcion_sede' => $sede ?: null,
                    'numero_orden' => $numOrden,
                    'ruc' => $ruc ?: null,
                    'cliente' => $cliente ?: null,
                    'diseno' => $diseno ?: null,
                    'descripcion_producto' => $descProd ?: null,

                    'importe' => is_numeric(str_replace(',', '.', $importeRaw))
                        ? (float) str_replace(',', '.', $importeRaw)
                        : null,

                    'orden_compra' => $ordenCompra ?: null,
                    'fecha_orden' => $fechaMySQL,
                    'hora_orden' => $horaOrden ?: null,
                    'tipo_orden' => $tipoOrden ?: null,
                    'nombre_usuario' => $usuario ?: null,
                    'estado_orden' => $estado ?: null,
                    'ubicacion_orden' => $ubicacion ?: null,
                    'descripcion_tallado' => $descTallado ?: null,
                    'tratamiento' => $tratamiento ?: null,

                    'lead_time' => is_numeric($leadTimeRaw)
                        ? (int) $leadTimeRaw
                        : null,

                    'mes' => $mes,
                    'anio' => $anio,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($ordenesInsert) >= $batchSize) {

                    $this->upsertHistorico($ordenesInsert);
                    $totalHistorico += count($ordenesInsert);
                    $ordenesInsert = [];

                    $this->output->write('.');
                }

                // ===============================
                // SEMAFORO (CON FILTROS)
                // ===============================

                if (!$sede) continue;

                if (in_array($usuarioNorm, $this->excluidos, true)) continue;

                if (in_array($clienteNorm, $this->clienteExcluido, true)) continue;

                if ($timestamp < $fechaDesde->timestamp) continue;

                if (in_array($estadoUpper, $this->estadosExcluidos, true)) continue;

                $key = "{$sede}|{$fechaMySQL}";

                if (!isset($conteosSede[$key])) {

                    $conteosSede[$key] = [

                        'sede' => $sede,
                        'fecha' => $fechaMySQL,
                        'mes' => $mes,
                        'anio' => $anio,
                        'cant' => 0,
                        'facturadas' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),

                    ];
                }

                $conteosSede[$key]['cant']++;

                if ($estadoUpper === 'FACTURADO') {
                    $conteosSede[$key]['facturadas']++;
                }
            }

            if (!empty($ordenesInsert)) {

                $this->upsertHistorico($ordenesInsert);
                $totalHistorico += count($ordenesInsert);
            }

            unset($rows, $ordenesInsert);

            $this->info("✅ ordenes_historico: {$totalHistorico} registros procesados");

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

    private function normalizarTexto(string $texto): string
    {
        $texto = strtolower(trim($texto));

        $texto = preg_replace('/\s+/', ' ', $texto);

        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        return $texto;
    }

    private function parsearFecha(string $fechaStr): ?int
    {
        $s = trim($fechaStr);

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]);
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[3], (int)$m[1]);
        }

        $ts = strtotime($s);

        return ($ts && $ts > 0) ? $ts : null;
    }
}