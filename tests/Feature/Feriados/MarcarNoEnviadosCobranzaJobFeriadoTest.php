<?php

namespace Tests\Feature\Feriados;

use App\Jobs\MarcarNoEnviadosCobranzaJob;
use App\Models\Feriado;
use App\Models\ReporteCobranza;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre MarcarNoEnviadosCobranzaJob: antes solo excluía domingo; ahora también
 * debe excluir feriado — y en vez de omitir el día, debe dejar un registro
 * explícito en estado 'feriado' (kpi null) para que el historial no quede
 * con un hueco ambiguo.
 */
class MarcarNoEnviadosCobranzaJobFeriadoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function usuarioSede(string $sede): User
    {
        Role::findOrCreate('sede', 'web');
        $user = User::factory()->create(['sede' => $sede, 'is_active' => true]);
        $user->assignRole('sede');

        return $user;
    }

    public function test_crea_registro_en_estado_feriado_si_ayer_fue_feriado(): void
    {
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        $this->usuarioSede('Lima');

        // "Hoy" 07 de agosto → "ayer" es 06 (feriado).
        Carbon::setTestNow(Carbon::parse('2026-08-07 02:00:00', 'America/Lima'));

        (new MarcarNoEnviadosCobranzaJob())->handle();

        $reporte = ReporteCobranza::where('sede', 'Lima')->whereDate('semana_inicio', '2026-08-06')->first();

        $this->assertNotNull($reporte);
        $this->assertSame('feriado', $reporte->estado);
        $this->assertNull($reporte->kpi_porcentaje);
    }

    public function test_crea_registro_no_enviado_si_ayer_fue_dia_normal(): void
    {
        $this->usuarioSede('Lima');

        // "Hoy" 08 de agosto → "ayer" es 07 (viernes normal).
        Carbon::setTestNow(Carbon::parse('2026-08-08 02:00:00', 'America/Lima'));

        (new MarcarNoEnviadosCobranzaJob())->handle();

        $reporte = ReporteCobranza::where('sede', 'Lima')->whereDate('semana_inicio', '2026-08-07')->first();

        $this->assertNotNull($reporte);
        $this->assertSame('no_enviado', $reporte->estado);
        $this->assertSame('0.00', $reporte->kpi_porcentaje);
    }

    public function test_sigue_sin_crear_nada_si_ayer_fue_domingo(): void
    {
        $this->usuarioSede('Lima');

        // "Hoy" lunes 2026-08-10 → "ayer" domingo 2026-08-09.
        Carbon::setTestNow(Carbon::parse('2026-08-10 02:00:00', 'America/Lima'));

        (new MarcarNoEnviadosCobranzaJob())->handle();

        $this->assertDatabaseCount('reportes_cobranza', 0);
    }
}
