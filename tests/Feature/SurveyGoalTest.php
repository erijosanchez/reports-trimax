<?php

namespace Tests\Feature;

use App\Models\Survey;
use App\Models\SurveyGoal;
use App\Models\User;
use App\Models\UsersMarketing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Metas semanales por sede (nuevo, validado con Gerencia 2026-08-11): no
 * existía ningún concepto de meta en el sistema. El dashboard "Por Sede"
 * calcula cumplimiento = encuestas de la semana en curso ÷ meta vigente, y
 * solo considera encuestas del esquema nuevo (con productos_rating).
 */
class SurveyGoalTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUsuarioAutenticado(): User
    {
        Role::findOrCreate('user', 'web');
        $authUser = User::factory()->create();
        $authUser->assignRole('user');
        $this->actingAs($authUser);
        return $authUser;
    }

    private function crearSede(string $nombre = 'Sede Arequipa'): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => $nombre,
            'role'      => 'sede',
            'location'  => 'Arequipa',
            'is_active' => true,
        ]);
    }

    private function crearEncuestaEsquemaNuevo(UsersMarketing $sede, $createdAt = null): Survey
    {
        $survey = Survey::create([
            'user_id'           => $sede->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
            'tiene_consultor'   => false,
            'productos_rating'  => 4,
        ]);

        if ($createdAt) {
            $survey->created_at = $createdAt;
            $survey->save();
        }

        return $survey;
    }

    public function test_marketing_puede_definir_meta_semanal_de_una_sede(): void
    {
        $this->actingAsUsuarioAutenticado();
        $sede = $this->crearSede();

        $response = $this->postJson(route('marketing.metas.store'), [
            'sede_id'      => $sede->id,
            'meta_semanal' => 20,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $goal = SurveyGoal::where('sede_id', $sede->id)->first();
        $this->assertNotNull($goal);
        $this->assertSame(20, $goal->meta_semanal);
        $this->assertSame(now('America/Lima')->toDateString(), $goal->vigente_desde->toDateString());
    }

    public function test_meta_vigente_toma_la_mas_reciente_segun_vigente_desde(): void
    {
        $sede = $this->crearSede();

        SurveyGoal::create([
            'sede_id'       => $sede->id,
            'meta_semanal'  => 10,
            'vigente_desde' => now()->subMonth()->toDateString(),
        ]);
        SurveyGoal::create([
            'sede_id'       => $sede->id,
            'meta_semanal'  => 25,
            'vigente_desde' => now()->toDateString(),
        ]);

        $vigente = SurveyGoal::vigentePara($sede->id);

        $this->assertSame(25, $vigente->meta_semanal);
    }

    public function test_dashboard_calcula_cumplimiento_como_porcentaje_de_la_meta(): void
    {
        $this->actingAsUsuarioAutenticado();
        $sede = $this->crearSede();

        SurveyGoal::create([
            'sede_id'       => $sede->id,
            'meta_semanal'  => 10,
            'vigente_desde' => now()->startOfWeek()->toDateString(),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->crearEncuestaEsquemaNuevo($sede);
        }

        $response = $this->get(route('marketing.index'));

        $response->assertOk();
        $sedeStats = collect($response->viewData('sedeStats'))->firstWhere('id', $sede->id);

        $this->assertSame(5, $sedeStats['obtenidas_semana']);
        $this->assertSame(10, $sedeStats['meta_semanal']);
        $this->assertSame(50, $sedeStats['cumplimiento_pct']);
    }

    public function test_dashboard_ignora_encuestas_de_esquema_antiguo_en_metricas_por_sede(): void
    {
        $this->actingAsUsuarioAutenticado();
        $sede = $this->crearSede();

        SurveyGoal::create([
            'sede_id'       => $sede->id,
            'meta_semanal'  => 10,
            'vigente_desde' => now()->startOfWeek()->toDateString(),
        ]);

        // Encuesta del esquema anterior: sin productos_rating.
        Survey::create([
            'user_id'                => $sede->id,
            'client_name'            => 'Cliente antiguo',
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $this->crearEncuestaEsquemaNuevo($sede);

        $response = $this->get(route('marketing.index'));

        $sedeStats = collect($response->viewData('sedeStats'))->firstWhere('id', $sede->id);

        $this->assertSame(1, $sedeStats['obtenidas_semana']);
        $this->assertSame(1, $sedeStats['total_historico']);
        $this->assertSame(10, $sedeStats['cumplimiento_pct']);
    }

    public function test_sede_sin_meta_definida_queda_sin_cumplimiento(): void
    {
        $this->actingAsUsuarioAutenticado();
        $sede = $this->crearSede();
        $this->crearEncuestaEsquemaNuevo($sede);

        $response = $this->get(route('marketing.index'));

        $sedeStats = collect($response->viewData('sedeStats'))->firstWhere('id', $sede->id);

        $this->assertNull($sedeStats['meta_semanal']);
        $this->assertNull($sedeStats['cumplimiento_pct']);
    }
}
