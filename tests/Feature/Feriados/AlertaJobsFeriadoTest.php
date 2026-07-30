<?php

namespace Tests\Feature\Feriados;

use App\Jobs\AlertaCajaChicaVencimientoJob;
use App\Jobs\AlertaCobranzaVencimientoJob;
use App\Jobs\AlertaComentariosVencimientoJob;
use App\Models\Feriado;
use App\Models\User;
use App\Notifications\CajaChicaAlertaVencimiento;
use App\Notifications\CobranzaAlertaVencimiento;
use App\Notifications\ComentariosAlertaVencimiento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre los 3 jobs de alerta de vencimiento: no deben notificar a las sedes
 * en un día feriado (nadie debe enviar reporte ese día, así que recordárselo
 * es ruido/confuso).
 */
class AlertaJobsFeriadoTest extends TestCase
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

    public function test_alerta_cobranza_no_notifica_si_hoy_es_feriado(): void
    {
        Notification::fake();
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-06 09:00:00', 'America/Lima'));

        (new AlertaCobranzaVencimientoJob())->handle();

        Notification::assertNothingSent();
    }

    public function test_alerta_cobranza_notifica_en_dia_normal(): void
    {
        Notification::fake();
        $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-07 09:00:00', 'America/Lima'));

        (new AlertaCobranzaVencimientoJob())->handle();

        Notification::assertSentTo(User::where('sede', 'Lima')->first(), CobranzaAlertaVencimiento::class);
    }

    public function test_alerta_caja_chica_no_notifica_si_el_sabado_es_feriado(): void
    {
        Notification::fake();
        Feriado::create(['fecha' => '2026-08-08', 'motivo' => 'Feriado de prueba', 'tipo' => 'nacional']);
        $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-08 19:00:00', 'America/Lima'));

        (new AlertaCajaChicaVencimientoJob())->handle();

        Notification::assertNothingSent();
    }

    public function test_alerta_comentarios_no_notifica_si_el_jueves_es_feriado(): void
    {
        Notification::fake();
        Feriado::create(['fecha' => '2026-08-06', 'motivo' => 'Batalla de Junín', 'tipo' => 'nacional']);
        $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-06 19:00:00', 'America/Lima'));

        (new AlertaComentariosVencimientoJob())->handle();

        Notification::assertNothingSent();
    }

    public function test_alerta_comentarios_notifica_en_jueves_normal(): void
    {
        Notification::fake();
        $this->usuarioSede('Lima');

        Carbon::setTestNow(Carbon::parse('2026-08-13 19:00:00', 'America/Lima')); // jueves sin feriado

        (new AlertaComentariosVencimientoJob())->handle();

        Notification::assertSentTo(User::where('sede', 'Lima')->first(), ComentariosAlertaVencimiento::class);
    }
}
