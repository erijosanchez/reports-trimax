<?php

namespace Tests\Feature;

use App\Models\UsersMarketing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rama nueva de la encuesta (validada con Gerencia, 2026-08-11):
 * "¿Actualmente es atendido por un consultor comercial de Trimax?" (Sí/No).
 * Si responde Sí, elige un consultor específico (o "No sabe / no tiene
 * consultor") y, solo si eligió uno específico, lo califica.
 */
class SurveyConsultorFlowTest extends TestCase
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

    private function crearConsultor(string $nombre = 'Consultor Test'): UsersMarketing
    {
        return UsersMarketing::create([
            'name'      => $nombre,
            'role'      => 'consultor',
            'is_active' => true,
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

    public function test_no_requiere_datos_de_consultor_cuando_tiene_consultor_es_no(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson(
            "/api/encuesta/{$trimax->unique_token}",
            $this->payloadBase(['tiene_consultor' => false])
        );

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id'          => $trimax->id,
            'tiene_consultor'  => 0,
            'consultor_id'     => null,
            'consultor_rating' => null,
        ]);
    }

    public function test_requiere_elegir_consultor_o_no_sabe_cuando_tiene_consultor_es_si(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson(
            "/api/encuesta/{$trimax->unique_token}",
            $this->payloadBase(['tiene_consultor' => true])
        );

        $response->assertStatus(422);
        $this->assertDatabaseMissing('surveys', ['user_id' => $trimax->id]);
    }

    public function test_guarda_el_consultor_elegido_y_su_calificacion(): void
    {
        $trimax    = $this->crearTrimax();
        $consultor = $this->crearConsultor('Ana Consultora');

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase([
            'tiene_consultor'  => true,
            'consultor_id'     => $consultor->id,
            'consultor_rating' => 3,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id'               => $trimax->id,
            'tiene_consultor'       => 1,
            'consultor_id'          => $consultor->id,
            'consultor_desconocido' => 0,
            'consultor_rating'      => 3,
        ]);
    }

    public function test_permite_no_sabe_no_tiene_consultor_sin_calificarlo(): void
    {
        $trimax = $this->crearTrimax();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase([
            'tiene_consultor'       => true,
            'consultor_desconocido' => true,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id'               => $trimax->id,
            'tiene_consultor'       => 1,
            'consultor_id'          => null,
            'consultor_desconocido' => 1,
            'consultor_rating'      => null,
        ]);
    }

    public function test_requiere_calificar_al_consultor_si_se_eligio_uno_especifico(): void
    {
        $trimax    = $this->crearTrimax();
        $consultor = $this->crearConsultor();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase([
            'tiene_consultor' => true,
            'consultor_id'    => $consultor->id,
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseMissing('surveys', ['user_id' => $trimax->id]);
    }

    public function test_rechaza_un_consultor_id_que_no_es_un_consultor_valido(): void
    {
        $trimax = $this->crearTrimax();
        $otraSede = UsersMarketing::create([
            'name'      => 'Sede X',
            'role'      => 'sede',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase([
            'tiene_consultor'  => true,
            'consultor_id'     => $otraSede->id,
            'consultor_rating' => 4,
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseMissing('surveys', ['user_id' => $trimax->id]);
    }

    public function test_ignora_datos_de_consultor_enviados_por_error_cuando_tiene_consultor_es_no(): void
    {
        $trimax    = $this->crearTrimax();
        $consultor = $this->crearConsultor();

        $response = $this->postJson("/api/encuesta/{$trimax->unique_token}", $this->payloadBase([
            'tiene_consultor'  => false,
            'consultor_id'     => $consultor->id,
            'consultor_rating' => 4,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('surveys', [
            'user_id'          => $trimax->id,
            'tiene_consultor'  => 0,
            'consultor_id'     => null,
            'consultor_rating' => null,
        ]);
    }
}
