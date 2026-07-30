<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre el arreglo de 2FA pedido tras la Ronda 3: el QR dependía de
 * chart.googleapis.com (API de Google dada de baja, devuelve 404 — el
 * código nunca se veía) y no existían códigos de recuperación
 * (two_factor_recovery_codes se guardaba en la tabla pero nunca se
 * generaba ni se usaba).
 */
class TwoFactorSetupAndRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function userConRol(string $rol): User
    {
        Role::findOrCreate($rol, 'web');
        $user = User::factory()->create();
        $user->assignRole($rol);

        return $user;
    }

    /** Actor con 2FA ya habilitado — para roles (admin/finanzas) donde el middleware lo exige. */
    private function actorConRolY2faListo(string $rol): User
    {
        $user = $this->userConRol($rol);
        $user->update([
            'two_factor_secret' => encrypt((new Google2FA())->generateSecretKey()),
            'two_factor_confirmed_at' => now(),
        ]);

        return $user;
    }

    public function test_la_pagina_de_setup_renderiza_un_svg_local_no_la_api_muerta_de_google(): void
    {
        $user = $this->userConRol('finanzas');

        $html = $this->actingAs($user)->get(route('2fa.setup'))->getContent();

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringNotContainsString('chart.googleapis.com', $html);
    }

    public function test_habilitar_2fa_genera_8_codigos_de_recuperacion_hasheados_y_los_muestra_una_vez(): void
    {
        $user = $this->userConRol('finanzas');
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $response = $this->actingAs($user)->post(route('2fa.enable'), [
            'secret' => $secret,
            'code'   => $google2fa->getCurrentOtp($secret),
        ]);

        $response->assertRedirect(route('2fa.recovery-codes'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);

        $hashes = json_decode($user->two_factor_recovery_codes, true);
        $this->assertCount(8, $hashes);
    }

    public function test_la_pagina_de_codigos_de_recuperacion_redirige_a_home_sin_sesion_flash(): void
    {
        // Si alguien entra directo a la URL sin acabar de pasar por
        // enable() (o refresca la página después), no hay códigos en claro
        // que mostrar — nunca se guardan sin hashear.
        $user = $this->userConRol('finanzas');

        $this->actingAs($user)
            ->get(route('2fa.recovery-codes'))
            ->assertRedirect(route('home'));
    }

    public function test_un_codigo_de_recuperacion_valido_deja_pasar_y_se_consume_una_sola_vez(): void
    {
        $user = $this->userConRol('finanzas');

        // Secreto TOTP real y válido — el usuario simplemente no tiene su
        // teléfono a mano, por eso usa un código de recuperación en vez de
        // uno generado por la app.
        $secretValido = (new Google2FA())->generateSecretKey();

        $codigoEnClaro = 'ABCD-1234';
        $user->update([
            'two_factor_secret'         => encrypt($secretValido),
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => json_encode([Hash::make($codigoEnClaro)]),
        ]);

        // Primer uso: entra.
        $this->actingAs($user)
            ->post(route('2fa.verify'), ['code' => $codigoEnClaro])
            ->assertRedirect(route('home'));

        $user->refresh();
        $this->assertSame([], json_decode($user->two_factor_recovery_codes, true));

        // Segundo intento con el mismo código: ya se consumió, falla.
        session()->flush();
        $this->actingAs($user)
            ->post(route('2fa.verify'), ['code' => $codigoEnClaro])
            ->assertSessionHasErrors('code');
    }

    /**
     * Encontrado al escribir el test anterior: Google2FA::verifyKey() lanza
     * una excepción (no devuelve false) si el secreto no tiene formato
     * base32 válido — sin el try/catch, esto tumbaba la petición con un 500
     * en vez de mostrar "código inválido".
     */
    public function test_secreto_corrupto_no_tumba_la_peticion_con_un_500(): void
    {
        $user = $this->userConRol('finanzas');
        $user->update([
            'two_factor_secret'       => encrypt('esto-no-es-un-secreto-base32-valido'),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['code' => '123456'])
            ->assertSessionHasErrors('code');
    }

    public function test_deshabilitar_2fa_borra_tambien_los_codigos_de_recuperacion(): void
    {
        $user = $this->userConRol('finanzas');
        $user->update([
            'two_factor_secret'         => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => json_encode([Hash::make('ABCD-1234')]),
        ]);

        $this->actingAs($user)->post(route('2fa.disable'));

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    /**
     * Sin esto, un usuario que pierde el teléfono Y los códigos de
     * recuperación queda bloqueado sin ninguna vía de acceso salvo un
     * UPDATE directo en la base de datos.
     */
    public function test_un_admin_puede_resetear_el_2fa_de_otro_usuario_bloqueado(): void
    {
        $admin = $this->actorConRolY2faListo('admin');
        $bloqueado = $this->userConRol('finanzas');
        $bloqueado->update([
            'two_factor_secret'         => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => json_encode([Hash::make('ABCD-1234')]),
        ]);

        $this->actingAs($admin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.users.reset-2fa', $bloqueado->id))
            ->assertRedirect();

        $bloqueado->refresh();
        $this->assertNull($bloqueado->two_factor_secret);
        $this->assertNull($bloqueado->two_factor_confirmed_at);
        $this->assertNull($bloqueado->two_factor_recovery_codes);
    }

    public function test_un_usuario_sin_rol_admin_no_puede_resetear_2fa_ajeno(): void
    {
        $noAdmin = $this->actorConRolY2faListo('finanzas');
        $otro = $this->userConRol('finanzas');

        $this->actingAs($noAdmin)
            ->withSession(['2fa_verified' => true])
            ->post(route('admin.users.reset-2fa', $otro->id))
            ->assertForbidden();
    }
}
