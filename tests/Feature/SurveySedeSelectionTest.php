<?php

namespace Tests\Feature;

use App\Models\Survey;
use App\Models\User;
use App\Models\UsersMarketing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Encuesta de "Trimax General" con sede opcional: si el cliente selecciona
 * una sede, la respuesta pasa a contar exclusivamente para esa sede; si no
 * selecciona nada, se comporta igual que antes (cuenta solo para Trimax
 * General). El link público (unique_token) no cambia.
 */
class SurveySedeSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function crearTrimax(): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => 'Trimax General',
            'role'      => 'trimax',
            'is_active' => true,
        ]);
    }

    private function crearSede(string $nombre, string $ubicacion): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => $nombre,
            'role'      => 'sede',
            'location'  => $ubicacion,
            'is_active' => true,
        ]);
    }

    private function crearConsultor(): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => 'Consultor Test',
            'role'      => 'consultor',
            'is_active' => true,
        ]);
    }

    // ── GET /api/encuesta/{token} ────────────────────────────────

    public function test_get_data_incluye_lista_de_sedes_solo_para_trimax_general(): void
    {
        $trimax = $this->crearTrimax();
        $this->crearSede('Sede Arequipa', 'Arequipa');
        $this->crearSede('Sede Lima', 'Lima');

        $response = $this->getJson("/api/encuesta/{$trimax->unique_token}");

        $response->assertOk();
        $response->assertJsonCount(2, 'data.sedes');
    }

    public function test_get_data_no_incluye_sedes_para_un_token_de_sede(): void
    {
        $sede = $this->crearSede('Sede Arequipa', 'Arequipa');

        $response = $this->getJson("/api/encuesta/{$sede->unique_token}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.sedes');
    }

    public function test_get_data_no_incluye_sedes_para_un_token_de_consultor(): void
    {
        $consultor = $this->crearConsultor();

        $response = $this->getJson("/api/encuesta/{$consultor->unique_token}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.sedes');
    }

    // ── POST /api/encuesta/{token} ───────────────────────────────

    public function test_encuesta_de_trimax_general_se_guarda_sin_sede_si_no_se_selecciona(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", [
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id' => $trimax->id,
            'sede_id' => null,
        ]);
    }

    public function test_encuesta_de_trimax_general_se_guarda_con_la_sede_seleccionada(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", [
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
            'sede_id'                => $sede->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id' => $trimax->id,
            'sede_id' => $sede->id,
        ]);
    }

    public function test_encuesta_rechaza_un_sede_id_que_no_corresponde_a_una_sede(): void
    {
        $trimax    = $this->crearTrimax();
        $consultor = $this->crearConsultor();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", [
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
            'sede_id'                => $consultor->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('surveys', ['user_id' => $trimax->id]);
    }

    public function test_encuesta_de_una_sede_ignora_sede_id_enviado_por_error(): void
    {
        $sedeA = $this->crearSede('Sede Arequipa', 'Arequipa');
        $sedeB = $this->crearSede('Sede Lima', 'Lima');

        $response = $this->postJson("/api/encuesta/{$sedeA->unique_token}", [
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
            'sede_id'                => $sedeB->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id' => $sedeA->id,
            'sede_id' => null,
        ]);
    }

    // ── Agregación de estadísticas ───────────────────────────────

    public function test_encuesta_etiquetada_cuenta_para_la_sede_y_no_para_trimax_general(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        Survey::create([
            'user_id'                => $trimax->id,
            'sede_id'                => $sede->id,
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $this->assertSame(1, $sede->fresh()->total_surveys);
        $this->assertSame(0, $trimax->fresh()->total_surveys);
    }

    public function test_encuesta_sin_sede_se_comporta_como_antes(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        Survey::create([
            'user_id'                => $trimax->id,
            'experience_rating'      => 3,
            'service_quality_rating' => 3,
        ]);

        $this->assertSame(1, $trimax->fresh()->total_surveys);
        $this->assertSame(0, $sede->fresh()->total_surveys);
    }

    public function test_display_entity_resuelve_a_la_sede_cuando_hay_seleccion(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        $survey = Survey::create([
            'user_id'                => $trimax->id,
            'sede_id'                => $sede->id,
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $this->assertTrue($survey->display_entity->is($sede));
    }

    public function test_display_entity_resuelve_al_dueno_del_link_sin_seleccion(): void
    {
        $trimax = $this->crearTrimax();

        $survey = Survey::create([
            'user_id'                => $trimax->id,
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $this->assertTrue($survey->display_entity->is($trimax));
    }

    public function test_consultor_incluye_en_sus_stats_las_encuestas_etiquetadas_de_sus_sedes(): void
    {
        $trimax    = $this->crearTrimax();
        $sede      = $this->crearSede('Sede Arequipa', 'Arequipa');
        $consultor = $this->crearConsultor();

        DB::table('consultor_sede')->insert([
            'consultor_id' => $consultor->id,
            'sede_id'      => $sede->id,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        Survey::create([
            'user_id'                => $trimax->id,
            'sede_id'                => $sede->id,
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $this->assertSame(1, $consultor->fresh()->total_surveys_with_sedes);
    }

    // ── Vista de detalle (dashboard) ─────────────────────────────

    public function test_encuestas_ajax_muestra_la_sede_seleccionada_en_vez_de_trimax_general(): void
    {
        Role::findOrCreate('user', 'web');
        $authUser = User::factory()->create();
        $authUser->assignRole('user');
        $this->actingAs($authUser);

        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        Survey::create([
            'user_id'                => $trimax->id,
            'sede_id'                => $sede->id,
            'experience_rating'      => 4,
            'service_quality_rating' => 4,
        ]);

        $response = $this->getJson(route('marketing.encuestas.ajax'));

        $response->assertOk();
        $response->assertJsonFragment([
            'evaluado_name' => 'Sede Arequipa',
            'evaluado_role' => 'sede',
        ]);
    }
}
