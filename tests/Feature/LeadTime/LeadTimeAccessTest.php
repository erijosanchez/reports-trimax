<?php

namespace Tests\Feature\LeadTime;

use App\Models\User;
use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre el control de acceso de LeadTimeController.
 *
 * Las 3 vistas (index/semanal/objetivoMas) ya validaban puedeVerLeadTime(),
 * pero los 4 endpoints que realmente devuelven datos (getData,
 * getSemanalData, getObjetivoMasData, getAvailableYears) no lo hacían:
 * cualquier usuario autenticado con 2FA verificado podía pegarles directo
 * sin el permiso, aunque no pudiera abrir la página. Este test fija el
 * comportamiento correcto tras agregar el chequeo a los 4 endpoints.
 */
class LeadTimeAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * super_admin/admin requieren 2FA (ver TwoFactorVerifiedMiddleware).
     * Se deja la sesión ya verificada aquí para no volver a probar eso en
     * cada test — mismo patrón que TopClientesTest.
     */
    private function userConRol(string $rol, array $atributos = []): User
    {
        Role::findOrCreate($rol, 'web');
        $user = User::factory()->create(array_merge([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ], $atributos));
        $user->assignRole($rol);
        $this->withSession(['2fa_verified' => true]);
        return $user;
    }

    /** Evita pegarle a la API real de Google Sheets en los casos "con permiso". */
    private function mockSheetVacio(): void
    {
        $this->mock(GoogleSheetsService::class, function ($mock) {
            $mock->shouldReceive('getSheetDataFromSpreadsheet')->andReturn([]);
        });
    }

    public function test_usuario_sin_permiso_no_accede_a_las_vistas(): void
    {
        $user = $this->userConRol('marketing');

        $this->actingAs($user)->get(route('produccion.lead-time.index'))->assertForbidden();
        $this->actingAs($user)->get(route('produccion.lead-time.semanal'))->assertForbidden();
        $this->actingAs($user)->get(route('produccion.lead-time.objetivo-mas'))->assertForbidden();
    }

    public function test_usuario_sin_permiso_no_accede_a_ningun_endpoint_de_datos(): void
    {
        $user = $this->userConRol('marketing');

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.data'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.semanal-data'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.objetivo-mas.data'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.years'))
            ->assertStatus(403);
    }

    public function test_usuario_con_permiso_explicito_si_accede_a_los_endpoints_de_datos(): void
    {
        $this->mockSheetVacio();
        $user = $this->userConRol('marketing', ['puede_ver_lead_time' => true]);

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.data'))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.semanal-data'))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.objetivo-mas.data'))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('produccion.lead-time.years'))
            ->assertOk();
    }

    /**
     * isSuperAdmin()/isAdmin()/isConsultor() otorgan acceso implícito
     * (puedeVerLeadTime() en User.php), sin necesitar el flag explícito.
     */
    public function test_usuario_consultor_accede_sin_flag_explicito(): void
    {
        $this->mockSheetVacio();
        $consultor = $this->userConRol('consultor');

        $this->actingAs($consultor)
            ->getJson(route('produccion.lead-time.data'))
            ->assertOk();
    }
}
