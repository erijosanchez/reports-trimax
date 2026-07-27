<?php

namespace Tests\Feature;

use App\Models\ReporteCobranza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre A8 (ARQUITECTURA.md): "el test que convierte A1 en algo verificable"
 * — un usuario de sede A no debe ver datos de sede B. A diferencia del
 * piloto de A1 (SedeScope en Voucher), aquí no hay scope global: el filtro
 * en CobranzaSedesController::historial() sigue siendo manual
 * ($query->where('sede', $user->sede)). Este test protege ese filtro de
 * romperse sin que nadie se dé cuenta.
 */
class CobranzaSedesFronteraDeSedeTest extends TestCase
{
    use RefreshDatabase;

    private function userDeSede(string $sede): User
    {
        Role::findOrCreate('sede', 'web');
        $user = User::factory()->create(['sede' => $sede]);
        $user->assignRole('sede');

        return $user;
    }

    private function reporteDeSede(string $sede): ReporteCobranza
    {
        $autor = User::factory()->create(['sede' => $sede]);

        return ReporteCobranza::create([
            'user_id'       => $autor->id,
            'sede'          => $sede,
            'semana_numero' => 30,
            'anio'          => 2026,
            'fecha_limite'  => now(),
        ]);
    }

    public function test_usuario_de_sede_solo_ve_reportes_de_su_propia_sede_en_el_historial(): void
    {
        $this->reporteDeSede('Lima');
        $this->reporteDeSede('Huánuco');

        $user = $this->userDeSede('Lima');

        $response = $this->actingAs($user)
            ->getJson(route('productividad.cobranza-sedes.cobranza.historial'))
            ->assertOk();

        $sedes = collect($response->json('data'))->pluck('sede')->unique()->values()->all();

        $this->assertSame(['Lima'], $sedes);
    }

    public function test_finanzas_ve_reportes_de_todas_las_sedes_en_el_historial(): void
    {
        $this->reporteDeSede('Lima');
        $this->reporteDeSede('Huánuco');

        // finanzas requiere 2FA (S4) — se deja la sesión ya verificada para
        // no probar S4 de nuevo aquí.
        Role::findOrCreate('finanzas', 'web');
        $user = User::factory()->create([
            'two_factor_secret'       => encrypt('SECRETDEPRUEBA'),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->assignRole('finanzas');
        $this->withSession(['2fa_verified' => true]);

        $response = $this->actingAs($user)
            ->getJson(route('productividad.cobranza-sedes.cobranza.historial'))
            ->assertOk();

        $sedes = collect($response->json('data'))->pluck('sede')->unique()->sort()->values()->all();

        $this->assertSame(['Huánuco', 'Lima'], $sedes);
    }
}
