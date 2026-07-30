<?php

namespace Tests\Feature;

use App\Models\Motorizado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre S7 (SEGURIDAD.md): la política de contraseñas débil (min:8 sin
 * complejidad para usuarios reales, min:6 para motorizados). Http::fake()
 * evita golpear la API real de HaveIBeenPwned que usa uncompromised().
 */
class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * admin/super_admin quedan sujetos a 2FA obligatorio (S4), así que hay
     * que dejar la sesión ya verificada para probar S7 sin que el
     * middleware 2fa.verified redirija antes de llegar a la validación.
     */
    private function admin(): User
    {
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('admin');
        $this->withSession(['2fa_verified' => true]);

        return $user;
    }

    private function fakePwnedApiSinFiltraciones(): void
    {
        // Respuesta vacía = el sufijo del hash no aparece en ninguna
        // filtración conocida.
        Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    public function test_admin_users_store_rechaza_password_corta(): void
    {
        $this->fakePwnedApiSinFiltraciones();

        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name'     => 'Nuevo Usuario',
            'email'    => 'nuevo@trimaxperu.com',
            'password' => 'abc12345', // 8 chars, cumplía la regla vieja
            'password_confirmation' => 'abc12345',
            'role'     => 'consultor',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@trimaxperu.com']);
    }

    public function test_admin_users_store_rechaza_password_sin_numeros(): void
    {
        $this->fakePwnedApiSinFiltraciones();

        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name'     => 'Nuevo Usuario',
            'email'    => 'nuevo2@trimaxperu.com',
            'password' => 'solaletras',
            'password_confirmation' => 'solaletras',
            'role'     => 'consultor',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_users_store_acepta_password_fuerte(): void
    {
        $this->fakePwnedApiSinFiltraciones();
        Role::findOrCreate('consultor', 'web');

        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name'     => 'Nuevo Usuario',
            'email'    => 'nuevo3@trimaxperu.com',
            'password' => 'Trimax2026Seguro',
            'password_confirmation' => 'Trimax2026Seguro',
            'role'     => 'consultor',
        ])->assertSessionDoesntHaveErrors('password');

        $this->assertDatabaseHas('users', ['email' => 'nuevo3@trimaxperu.com']);
    }

    public function test_storeMotorizado_rechaza_password_corta(): void
    {
        $this->fakePwnedApiSinFiltraciones();

        Role::findOrCreate('admin', 'web');
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('tracking.motorizados.store'), [
            'nombre'   => 'Repartidor Uno',
            'sede'     => 'Lima',
            'tipo'     => 'motorizado',
            'email'    => 'motorizado1@trimaxperu.com',
            'password' => 'abc123', // 6 chars, cumplía la regla vieja
            'estado'   => 'activo',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('motorizados', ['email' => 'motorizado1@trimaxperu.com']);
    }

    public function test_storeMotorizado_acepta_password_fuerte(): void
    {
        $this->fakePwnedApiSinFiltraciones();

        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('tracking.motorizados.store'), [
            'nombre'   => 'Repartidor Dos',
            'sede'     => 'Lima',
            'tipo'     => 'motorizado',
            'email'    => 'motorizado2@trimaxperu.com',
            'password' => 'Trimax2026Seguro',
            'estado'   => 'activo',
        ])->assertOk();

        $this->assertDatabaseHas('motorizados', ['email' => 'motorizado2@trimaxperu.com']);
    }
}
