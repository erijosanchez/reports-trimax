<?php

namespace App\Console\Commands;

use App\Models\Feriado;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Importación asistida de feriados nacionales desde gob.pe/feriados.
 * No hay API pública: se parsea la tabla HTML de la página oficial.
 * Pensado para correr manualmente o desde el botón "Sincronizar" del panel
 * admin — no es un cron ciego, el resultado se revisa antes de confiar en él.
 */
class SyncFeriadosGobPe extends Command
{
    protected $signature = 'feriados:sync {--anio=} {--historicos} {--dry-run}';

    protected $description = 'Sincroniza feriados nacionales desde gob.pe/feriados';

    private const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
        'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    public function handle(): int
    {
        $url = $this->option('historicos')
            ? 'https://www.gob.pe/feriados/historicos'
            : 'https://www.gob.pe/feriados';

        $response = Http::withUserAgent('Mozilla/5.0 (compatible; TrimaxReportsBot/1.0; +https://trimaxperu.com)')
            ->timeout(15)
            ->get($url);

        if (!$response->successful()) {
            $this->error("No se pudo obtener {$url} (HTTP {$response->status()}).");
            return self::FAILURE;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->body());
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $anio = (int) ($this->option('anio') ?: $this->detectarAnio($xpath));
        if (!$anio) {
            $this->error('No se pudo determinar el año de la página. Especifica --anio=2027.');
            return self::FAILURE;
        }

        $filas = $xpath->query("//table[contains(@class,'table-holidays')]/tbody/tr");

        if ($filas === false || $filas->length === 0) {
            $this->warn('No se encontraron filas de feriados. La página pudo haber cambiado de estructura.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sincronizados = 0;
        $omitidos      = 0;

        foreach ($filas as $fila) {
            $celdas = $fila->getElementsByTagName('td');
            if ($celdas->length < 3) {
                continue;
            }

            $tipoTexto  = trim($celdas->item(0)->textContent);
            $fechaTexto = trim($celdas->item(1)->textContent);
            $motivo     = trim($celdas->item(2)->textContent);

            $fecha = $this->parsearFecha($fechaTexto, $anio);
            if (!$fecha) {
                $this->warn("No se pudo interpretar la fecha \"{$fechaTexto}\" ({$motivo}) — omitido.");
                $omitidos++;
                continue;
            }

            $tipo = str_contains(mb_strtolower($tipoTexto), 'regional') ? 'regional' : 'nacional';

            if ($dryRun) {
                $this->line("{$fecha->toDateString()} — {$motivo} ({$tipo})");
                continue;
            }

            Feriado::guardar($fecha->toDateString(), $motivo, $tipo, 'gob.pe');
            $sincronizados++;
        }

        if ($dryRun) {
            $this->info("Vista previa: {$filas->length} filas leídas, {$omitidos} no interpretadas.");
        } else {
            $this->info("Sincronizados {$sincronizados} feriados del {$anio} desde gob.pe ({$omitidos} omitidos).");
        }

        return self::SUCCESS;
    }

    private function detectarAnio(DOMXPath $xpath): ?int
    {
        $h1 = $xpath->query("//h1[contains(@class,'holidays__title')]");
        if ($h1 && $h1->length > 0 && preg_match('/(\d{4})/', $h1->item(0)->textContent, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /** Interpreta "Jueves 6 de agosto" / "Domingo 30 de agosto" + año → Carbon. */
    private function parsearFecha(string $texto, int $anio): ?Carbon
    {
        if (!preg_match('/(\d{1,2})\s+de\s+([a-záéíóú]+)/iu', $texto, $m)) {
            return null;
        }

        $dia = (int) $m[1];
        $mes = self::MESES[mb_strtolower($m[2])] ?? null;
        if (!$mes) {
            return null;
        }

        try {
            return Carbon::create($anio, $mes, $dia);
        } catch (\Exception) {
            return null;
        }
    }
}
