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
 * Rediseño de la encuesta (validado con Gerencia, 2026-08-11): "sede con la
 * que trabaja" es un dato del cliente, no un detalle exclusivo del link
 * Trimax General — se pregunta siempre, en cualquier link, y precarga según
 * el rol del token (editable). Si el cliente selecciona/edita una sede, la
 * respuesta cuenta exclusivamente para esa sede; si no, cuenta para el
 * dueño del link. Las calificaciones de una sede ya NO se heredan hacia los
 * consultores que tiene asignados.
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

    private function crearConsultor(string $nombre = 'Consultor Test'): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => $nombre,
            'role'      => 'consultor',
            'is_active' => true,
        ]);
    }

    private function asignarSede(UsersMarketing $consultor, UsersMarketing $sede): void
    {
        DB::table('consultor_sede')->insert([
            'consultor_id' => $consultor->id,
            'sede_id'      => $sede->id,
            'is_active'    => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function payloadBase(array $overrides = []): array
    {
        return array_merge([
            'client_name'       => 'Cliente Test SAC',
            'experience_rating' => 4,
            'sede_rating'       => 4,
            'tiene_consultor'   => false,
            'productos_rating'  => 4,
        ], $overrides);
    }

    // ── GET /api/encuesta/{token} — sede siempre visible, precarga según rol ──

    public function test_get_data_incluye_sedes_para_token_trimax(): void
    {
        $trimax = $this->crearTrimax();
        $this->crearSede('Sede Arequipa', 'Arequipa');
        $this->crearSede('Sede Lima', 'Lima');

        $response = $this->getJson("/api/encuesta/{$trimax->unique_token}");

        $response->assertOk();
        $response->assertJsonCount(2, 'data.sedes');
        $response->assertJsonPath('data.sede_preseleccionada_id', null);
    }

    public function test_get_data_incluye_sedes_para_token_de_sede(): void
    {
        $sede = $this->crearSede('Sede Arequipa', 'Arequipa');
        $this->crearSede('Sede Lima', 'Lima');

        $response = $this->getJson("/api/encuesta/{$sede->unique_token}");

        $response->assertOk();
        $response->assertJsonCount(2, 'data.sedes');
    }

    public function test_get_data_incluye_sedes_para_token_de_consultor(): void
    {
        $consultor = $this->crearConsultor();
        $this->crearSede('Sede Arequipa', 'Arequipa');

        $response = $this->getJson("/api/encuesta/{$consultor->unique_token}");

        $response->assertOk();
        $response->assertJsonCount(1, 'data.sedes');
    }

    public function test_get_data_precarga_la_propia_sede_para_un_token_de_sede(): void
    {
        $sede = $this->crearSede('Sede Arequipa', 'Arequipa');

        $response = $this->getJson("/api/encuesta/{$sede->unique_token}");

        $response->assertOk();
        $response->assertJsonPath('data.sede_preseleccionada_id', $sede->id);
    }

    public function test_get_data_precarga_la_unica_sede_asignada_de_un_consultor(): void
    {
        $consultor = $this->crearConsultor();
        $sede      = $this->crearSede('Sede Arequipa', 'Arequipa');
        $this->asignarSede($consultor, $sede);

        $response = $this->getJson("/api/encuesta/{$consultor->unique_token}");

        $response->assertOk();
        $response->assertJsonPath('data.sede_preseleccionada_id', $sede->id);
    }

    public function test_get_data_no_precarga_sede_de_un_consultor_con_varias_sedes(): void
    {
        $consultor = $this->crearConsultor();
        $sedeA     = $this->crearSede('Sede Arequipa', 'Arequipa');
        $sedeB     = $this->crearSede('Sede Lima', 'Lima');
        $this->asignarSede($consultor, $sedeA);
        $this->asignarSede($consultor, $sedeB);

        $response = $this->getJson("/api/encuesta/{$consultor->unique_token}");

        $response->assertOk();
        $response->assertJsonPath('data.sede_preseleccionada_id', null);
    }

    public function test_get_data_incluye_consultores_activos_con_sus_sedes(): void
    {
        $trimax    = $this->crearTrimax();
        $sede      = $this->crearSede('Sede Arequipa', 'Arequipa');
        $consultor = $this->crearConsultor('Ana Consultora');
        $this->asignarSede($consultor, $sede);

        $response = $this->getJson("/api/encuesta/{$trimax->unique_token}");

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Ana Consultora']);
        $response->assertJsonPath('data.consultores.0.sede_ids.0', $sede->id);
    }

    // ── POST /api/encuesta/{token} ───────────────────────────────

    public function test_encuesta_requiere_razon_social(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson(
            "/api/encuesta/{$trimax->unique_token}",
            $this->payloadBase(['client_name' => ''])
        );

        $response->assertStatus(422);
    }

    public function test_encuesta_de_trimax_general_se_guarda_sin_sede_si_no_se_selecciona(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase());

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

        $response = $this->postJson(
            "/api/encuesta/{$trimax->unique_token}",
            $this->payloadBase(['sede_id' => $sede->id])
        );

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

        $response = $this->postJson(
            "/api/encuesta/{$trimax->unique_token}",
            $this->payloadBase(['sede_id' => $consultor->id])
        );

        $response->assertStatus(422);
        $this->assertDatabaseMissing('surveys', ['user_id' => $trimax->id]);
    }

    public function test_encuesta_de_una_sede_respeta_la_sede_editada_por_el_cliente(): void
    {
        // Antes del rediseño, el link de una sede ignoraba cualquier sede_id
        // enviado. Ahora el campo es editable en cualquier link — si el
        // cliente cambia la sede precargada, se respeta su elección.
        $sedeA = $this->crearSede('Sede Arequipa', 'Arequipa');
        $sedeB = $this->crearSede('Sede Lima', 'Lima');

        $response = $this->postJson(
            "/api/encuesta/{$sedeA->unique_token}",
            $this->payloadBase(['sede_id' => $sedeB->id])
        );

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id' => $sedeA->id,
            'sede_id' => $sedeB->id,
        ]);
    }

    // ── Agregación de estadísticas ───────────────────────────────

    public function test_encuesta_etiquetada_cuenta_para_la_sede_y_no_para_trimax_general(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        Survey::create([
            'user_id'           => $trimax->id,
            'sede_id'           => $sede->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
            'tiene_consultor'   => false,
            'productos_rating'  => 4,
        ]);

        $this->assertSame(1, $sede->fresh()->total_surveys);
        $this->assertSame(0, $trimax->fresh()->total_surveys);
    }

    public function test_encuesta_sin_sede_se_comporta_como_antes(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        Survey::create([
            'user_id'           => $trimax->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 3,
            'sede_rating'       => 3,
            'tiene_consultor'   => false,
            'productos_rating'  => 3,
        ]);

        $this->assertSame(1, $trimax->fresh()->total_surveys);
        $this->assertSame(0, $sede->fresh()->total_surveys);
    }

    public function test_display_entity_resuelve_a_la_sede_cuando_hay_seleccion(): void
    {
        $trimax = $this->crearTrimax();
        $sede   = $this->crearSede('Sede Arequipa', 'Arequipa');

        $survey = Survey::create([
            'user_id'           => $trimax->id,
            'sede_id'           => $sede->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
        ]);

        $this->assertTrue($survey->display_entity->is($sede));
    }

    public function test_display_entity_resuelve_al_dueno_del_link_sin_seleccion(): void
    {
        $trimax = $this->crearTrimax();

        $survey = Survey::create([
            'user_id'           => $trimax->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
        ]);

        $this->assertTrue($survey->display_entity->is($trimax));
    }

    public function test_consultor_ya_no_hereda_en_sus_stats_las_encuestas_etiquetadas_de_sus_sedes(): void
    {
        // Confirma la restricción fija validada 2026-08-11: las
        // calificaciones de una sede dejan de sumarse al promedio de sus
        // consultores asignados. El pivot consultor_sede sigue existiendo
        // solo para filtrar el selector de consultores del formulario.
        $trimax    = $this->crearTrimax();
        $sede      = $this->crearSede('Sede Arequipa', 'Arequipa');
        $consultor = $this->crearConsultor();
        $this->asignarSede($consultor, $sede);

        Survey::create([
            'user_id'           => $trimax->id,
            'sede_id'           => $sede->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
        ]);

        $this->assertSame(0, $consultor->fresh()->total_surveys);
        $this->assertSame(1, $sede->fresh()->total_surveys);
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
            'user_id'           => $trimax->id,
            'sede_id'           => $sede->id,
            'client_name'       => 'Cliente Test',
            'experience_rating' => 4,
            'sede_rating'       => 4,
        ]);

        $response = $this->getJson(route('marketing.encuestas.ajax'));

        $response->assertOk();
        $response->assertJsonFragment([
            'evaluado_name' => 'Sede Arequipa',
            'evaluado_role' => 'sede',
        ]);
    }
}
