<?php

namespace Tests\Feature;

use App\Jobs\MarcarNoEnviadosCajaChicaJob;
use App\Models\ReporteCajaChica;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre MarcarNoEnviadosCajaChicaJob — equivalente semanal de
 * MarcarNoEnviadosCobranzaJob para Caja Chica (deadline sábado 11:59 PM).
 * Necesario para que exista una fila (y por lo tanto un id de reporte) sobre
 * la que un revisor pueda rechazar/comentar una sede que nunca envió.
 */
class MarcarNoEnviadosCajaChicaJobTest extends TestCase
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

    public function test_crea_no_enviado_el_domingo_para_la_semana_que_vencio_el_sabado(): void
    {
        $this->usuarioSede('Lima');

        // Semana lunes 10 a domingo 16 de agosto de 2026; límite sábado 15
        // 23:59:59. Corre domingo 16 a las 02:00.
        Carbon::setTestNow(Carbon::parse('2026-08-16 02:00:00', 'America/Lima'));

        (new MarcarNoEnviadosCajaChicaJob())->handle();

        $reporte = ReporteCajaChica::where('sede', 'Lima')
            ->where('semana_numero', Carbon::parse('2026-08-10')->isoWeek())
            ->where('anio', 2026)
            ->first();

        $this->assertNotNull($reporte);
        $this->assertSame('no_enviado', $reporte->estado);
        $this->assertSame('0.00', $reporte->kpi_porcentaje);
        $this->assertNull($reporte->fecha_envio_original);
    }

    public function test_no_marca_nada_antes_de_que_venza_el_limite_del_sabado(): void
    {
        $this->usuarioSede('Lima');

        // Miércoles de la misma semana, todavía dentro de plazo.
        Carbon::setTestNow(Carbon::parse('2026-08-12 10:00:00', 'America/Lima'));

        (new MarcarNoEnviadosCajaChicaJob())->handle();

        $this->assertDatabaseCount('reportes_caja_chica', 0);
    }

    public function test_no_duplica_si_la_sede_ya_tiene_fila_esa_semana(): void
    {
        $sede = $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-16 02:00:00', 'America/Lima'));

        ReporteCajaChica::create([
            'user_id'       => $sede->id,
            'sede'          => 'Lima',
            'semana_numero' => Carbon::parse('2026-08-10')->isoWeek(),
            'anio'          => 2026,
            'semana_inicio' => '2026-08-10',
            'semana_fin'    => '2026-08-15',
            'fecha_limite'  => Carbon::parse('2026-08-15 23:59:59'),
            'estado'        => 'pendiente',
        ]);

        (new MarcarNoEnviadosCajaChicaJob())->handle();

        $this->assertDatabaseCount('reportes_caja_chica', 1);
        $this->assertSame('pendiente', ReporteCajaChica::first()->estado);
    }
}
