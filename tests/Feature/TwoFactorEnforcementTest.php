<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre S4 (SEGURIDAD.md): 2FA obligatorio solo para super_admin, admin y
 * finanzas — el resto de roles no se ve afectado. Se registra una ruta
 * protegida ad-hoc con el mismo middleware que routes/web.php, para no
 * depender de la lógica de negocio de HomeController.
 */
class TwoFactorEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', '2fa.verified'])
            ->get('/test-protegida', fn () => 'ok')
            ->name('test.protegida');
    }

    private function userConRol(string $rol): User
    {
        Role::findOrCreate($rol, 'web');

        $user = User::factory()->create();
        $user->assignRole($rol);

        return $user;
    }

    public function test_finanzas_sin_2fa_configurado_es_redirigido_a_setup(): void
    {
        $user = $this->userConRol('finanzas');

        $this->actingAs($user)
            ->get('/test-protegida')
            ->assertRedirect(route('2fa.setup'));
    }

    public function test_finanzas_con_2fa_configurado_pero_sin_verificar_en_sesion_es_redirigido_a_verify(): void
    {
        $user = $this->userConRol('finanzas');
        $user->update([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/test-protegida')
            ->assertRedirect(route('2fa.verify'));
    }

    public function test_finanzas_con_2fa_verificado_en_sesion_puede_pasar(): void
    {
        $user = $this->userConRol('finanzas');
        $user->update([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession(['2fa_verified' => true])
            ->get('/test-protegida')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_rol_sede_no_requiere_2fa(): void
    {
        $user = $this->userConRol('sede');

        $this->actingAs($user)
            ->get('/test-protegida')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_super_admin_sin_2fa_es_redirigido_a_setup(): void
    {
        $user = $this->userConRol('super_admin');

        $this->actingAs($user)
            ->get('/test-protegida')
            ->assertRedirect(route('2fa.setup'));
    }

    public function test_admin_sin_2fa_es_redirigido_a_setup(): void
    {
        $user = $this->userConRol('admin');

        $this->actingAs($user)
            ->get('/test-protegida')
            ->assertRedirect(route('2fa.setup'));
    }

    public function test_no_hay_loop_de_redirecciones_al_acceder_a_las_rutas_2fa(): void
    {
        $user = $this->userConRol('finanzas');

        // Sin 2FA configurado, /2fa/setup debe responder directamente, no
        // redirigir de nuevo a sí misma.
        $this->actingAs($user)
            ->get(route('2fa.setup'))
            ->assertOk();
    }
}
