<?php

namespace Tests\Feature\Feriados;

use App\Models\Feriado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Cubre el comando feriados:sync — parseo de la tabla HTML de gob.pe/feriados
 * (sin API oficial) hacia registros Feriado. No pega a la red real: simula
 * la respuesta con un fragmento representativo de la estructura real de la
 * página (ver investigación previa: <table class="table-holidays">).
 */
class SyncFeriadosGobPeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * assertDatabaseHas compara 'fecha' como string exacto — el cast 'date'
     * guarda 'Y-m-d 00:00:00', así que un valor 'Y-m-d' plano nunca matchea.
     * whereDate() sí normaliza correctamente.
     */
    private function assertFeriadoExiste(string $fecha, string $motivo, string $tipo, string $fuente): void
    {
        $this->assertTrue(
            Feriado::whereDate('fecha', $fecha)->where('motivo', $motivo)->where('tipo', $tipo)->where('fuente', $fuente)->exists(),
            "No se encontró el feriado {$fecha} ({$motivo})."
        );
    }

    private function assertFeriadoNoExiste(string $fecha): void
    {
        $this->assertFalse(Feriado::whereDate('fecha', $fecha)->exists(), "No debía existir un feriado en {$fecha}.");
    }

    private function htmlDeMuestra(): string
    {
        return <<<'HTML'
<!DOCTYPE html><html><head><title>Feriados 2026</title></head>
<body>
<h1 class="holidays__title">Feriados 2026</h1>
<table class="table-holidays">
<thead><tr><th>Tipo</th><th>Fecha</th><th>Motivo</th></tr></thead>
<tbody>
<tr><td>Feriado nacional</td><td><span>Domingo 30 de agosto</span></td><td>Santa Rosa de Lima</td></tr>
<tr><td>Feriado nacional</td><td><span>Jueves 8 de octubre</span></td><td>Combate de Angamos</td></tr>
<tr><td>Feriado regional</td><td><span>Viernes 15 de mayo</span></td><td>Aniversario regional</td></tr>
</tbody>
</table>
</body></html>
HTML;
    }

    public function test_sync_crea_feriados_desde_la_tabla_html_con_el_anio_detectado_del_titulo(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response($this->htmlDeMuestra(), 200),
        ]);

        $this->artisan('feriados:sync')->assertExitCode(0);

        $this->assertFeriadoExiste('2026-08-30', 'Santa Rosa de Lima', 'nacional', 'gob.pe');
        $this->assertFeriadoExiste('2026-10-08', 'Combate de Angamos', 'nacional', 'gob.pe');
        $this->assertFeriadoExiste('2026-05-15', 'Aniversario regional', 'regional', 'gob.pe');
        $this->assertSame(3, Feriado::count());
    }

    public function test_sync_respeta_el_anio_explicito_por_encima_del_detectado(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response($this->htmlDeMuestra(), 200),
        ]);

        $this->artisan('feriados:sync', ['--anio' => 2027])->assertExitCode(0);

        $this->assertFeriadoExiste('2027-08-30', 'Santa Rosa de Lima', 'nacional', 'gob.pe');
        $this->assertFeriadoNoExiste('2026-08-30');
    }

    public function test_dry_run_no_escribe_nada_en_la_base_de_datos(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response($this->htmlDeMuestra(), 200),
        ]);

        $this->artisan('feriados:sync', ['--dry-run' => true])->assertExitCode(0);

        $this->assertSame(0, Feriado::count());
    }

    public function test_actualiza_por_fecha_en_vez_de_duplicar_si_ya_existia(): void
    {
        Feriado::create(['fecha' => '2026-08-30', 'motivo' => 'Nombre viejo', 'tipo' => 'nacional', 'fuente' => 'manual']);

        Http::fake([
            'www.gob.pe/feriados' => Http::response($this->htmlDeMuestra(), 200),
        ]);

        $this->artisan('feriados:sync')->assertExitCode(0);

        $this->assertSame(3, Feriado::count());
        $this->assertFeriadoExiste('2026-08-30', 'Santa Rosa de Lima', 'nacional', 'gob.pe');
    }

    public function test_falla_con_codigo_distinto_de_cero_si_la_pagina_no_responde_ok(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response('', 500),
        ]);

        $this->artisan('feriados:sync')->assertExitCode(1);
        $this->assertSame(0, Feriado::count());
    }

    public function test_falla_si_no_encuentra_la_tabla_de_feriados_en_el_html(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response('<html><body>Página cambió de estructura</body></html>', 200),
        ]);

        // --anio explícito para aislar el chequeo de "sin tabla" del de "sin año detectado".
        $this->artisan('feriados:sync', ['--anio' => 2026])->assertExitCode(1);
        $this->assertSame(0, Feriado::count());
    }

    public function test_falla_si_no_puede_detectar_el_anio_y_no_se_paso_explicito(): void
    {
        Http::fake([
            'www.gob.pe/feriados' => Http::response('<html><body>Página cambió de estructura</body></html>', 200),
        ]);

        $this->artisan('feriados:sync')->assertExitCode(1);
        $this->assertSame(0, Feriado::count());
    }
}
