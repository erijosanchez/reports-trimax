<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre la hora en que finanzas aplicó el voucher (hora_aplicado), mostrada
 * en el historial (filaHistorial) junto a 'aplicado_at' (datetime real desde
 * la migración 2026_08_10_120000; antes solo guardaba fecha).
 *
 * También cubre 'demora' (VoucherController::demoraEnMinutos), que da
 * precisión de horas/minutos en vez de días redondeados — y su fallback para
 * vouchers aplicados ANTES de esa migración, cuyo aplicado_at quedó fijo en
 * 00:00:00 (sin hora real): esos no deben mostrar una hora inventada, sino
 * seguir viéndose en días completos, como antes del fix.
 */
class VoucherHoraAplicadoTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

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

    public function test_historial_incluye_la_hora_de_aplicado_junto_a_la_fecha(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 9, 0, 0, 'America/Lima'));

        $sede = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-APL-1',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 5, 30, 16, 45, 0, 'America/Lima'));
        $finanzas = $this->userConRol('finanzas');
        $this->actingAs($finanzas)
            ->patchJson(route('vouchers.aplicar', ['id' => $voucher->id]))
            ->assertOk();

        $resp = $this->actingAs($sede)->getJson(route('vouchers.historial'));

        $resp->assertOk();
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertNotNull($fila);
        $this->assertSame('30/05/2026', $fila['aplicado_at']);
        $this->assertSame('16:45', $fila['hora_aplicado']);
        // 28/05 09:00 -> 30/05 16:45 = 2 días, 7h y 45min = 3345 minutos.
        $this->assertSame(3345, $fila['demora']);
    }

    public function test_demora_cuenta_horas_y_minutos_no_solo_dias_completos(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 1, 8, 0, 0, 'America/Lima'));

        $sede = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-APL-3',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        // Aplicado el mismo día, 5 horas después: antes se hubiera mostrado
        // "0 días", que no distinguía esto de una aplicación instantánea.
        Carbon::setTestNow(Carbon::create(2026, 6, 1, 13, 0, 0, 'America/Lima'));
        $finanzas = $this->userConRol('finanzas');
        $this->actingAs($finanzas)
            ->patchJson(route('vouchers.aplicar', ['id' => $voucher->id]))
            ->assertOk();

        $resp = $this->actingAs($sede)->getJson(route('vouchers.historial'));
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertSame(300, $fila['demora']); // 5 horas = 300 minutos
    }

    public function test_datos_de_antes_del_fix_se_ven_en_dias_sin_hora_inventada(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 9, 0, 0, 'America/Lima'));

        $sede = $this->userConRol('sede', ['sede' => 'Lima']);
        // Simula un voucher aplicado antes de la migración: aplicado_at
        // guardado directo (sin pasar por aplicar()) con hora en 00:00:00,
        // igual que quedaron los registros reales previos al fix.
        $voucher = Voucher::create([
            'codigo'        => 'V-APL-4',
            'sede'          => 'Lima',
            'status'        => 'aplicado',
            'total'         => 100,
            'solicitado_at' => '2026-05-20',
            'aplicado_at'   => '2026-05-22 00:00:00',
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->getJson(route('vouchers.historial'));
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertSame('22/05/2026', $fila['aplicado_at']);
        $this->assertNull($fila['hora_aplicado']); // no se inventa una hora que nunca se guardó
        $this->assertSame(2 * 1440, $fila['demora']); // 2 días completos, no minutos exactos falsos
    }

    public function test_hora_aplicado_es_null_mientras_el_voucher_no_se_aplique(): void
    {
        $sede = $this->userConRol('sede', ['sede' => 'Lima']);
        $voucher = Voucher::create([
            'codigo'        => 'V-APL-2',
            'sede'          => 'Lima',
            'status'        => 'pendiente',
            'total'         => 100,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $sede->id,
        ]);

        $resp = $this->actingAs($sede)->getJson(route('vouchers.historial'));

        $resp->assertOk();
        $fila = collect($resp->json('data'))->firstWhere('id', $voucher->id);

        $this->assertNull($fila['aplicado_at']);
        $this->assertNull($fila['hora_aplicado']);
    }
}
