<?php

namespace Tests\Feature\VentaCliente;

use App\Models\Feriado;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre VentaClienteController::getTopClientesData — top 25 por sede según
 * promedio de los 3 meses anteriores, proyección del mes actual por días
 * hábiles (Feriado::esDiaLaborable) y semáforo de variación %.
 *
 * "Hoy" se fija al día 15 del mes real (no una fecha hardcodeada) para que
 * los tests no dependan del calendario ni de en qué fecha corra la suite,
 * y para evitar el caso límite día-1-es-domingo (0 días hábiles transcurridos).
 * Los períodos (mes-3/-2/-1/actual) y los conteos de días hábiles esperados
 * se derivan del mismo "hoy" con la misma lógica que usa el controlador,
 * pero calculados de forma independiente en el test (sin llamar a métodos
 * privados del controlador).
 */
class TopClientesTest extends TestCase
{
    use RefreshDatabase;

    // Mismo mapeo que VentaClienteController::MESES, para armar el label esperado en las aserciones.
    private const MESES_NOMBRES = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
        7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SETIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now()->startOfMonth()->addDays(14)->setTime(10, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * super_admin/admin/finanzas requieren 2FA (S4, ver TwoFactorVerifiedMiddleware).
     * Se deja la sesión ya verificada aquí para no volver a probar S4 en cada
     * test — mismo patrón que CobranzaSedesFronteraDeSedeTest / TwoFactorEnforcementTest.
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

    /** Igual que el offset que usa el controlador: subMonthsNoOverflow desde "hoy". */
    private function periodo(int $offsetMeses): array
    {
        $f = Carbon::now()->subMonthsNoOverflow($offsetMeses);
        return ['anio' => $f->year, 'mes' => $f->month];
    }

    /** Cuenta días no-domingo en el rango — válido como oráculo porque el test no siembra feriados salvo que se indique. */
    private function diasNoDomingoEnRango(Carbon $inicio, Carbon $fin): int
    {
        $count = 0;
        $cursor = $inicio->copy();
        while ($cursor->lte($fin)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY) $count++;
            $cursor->addDay();
        }
        return $count;
    }

    private function diasHabilesTranscurridosEsperado(): int
    {
        return $this->diasNoDomingoEnRango(Carbon::now()->copy()->startOfMonth(), Carbon::now()->copy()->startOfDay());
    }

    private function diasHabilesTotalesEsperado(): int
    {
        return $this->diasNoDomingoEnRango(Carbon::now()->copy()->startOfMonth(), Carbon::now()->copy()->endOfMonth());
    }

    private function venta(string $sede, string $ruc, string $razon, int $anio, int $mes, float $importe): void
    {
        DB::table('venta_clientes_historico')->insert([
            'sede'         => $sede,
            'ruc'          => $ruc,
            'razon_social' => $razon,
            'anio'         => $anio,
            'mes'          => $mes,
            'importe'      => $importe,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function sedeEnRespuesta(array $sedes, string $nombre): ?array
    {
        foreach ($sedes as $s) {
            if ($s['sede'] === $nombre) return $s;
        }
        return null;
    }

    public function test_calcula_promedio_proyeccion_y_semaforo_correctamente(): void
    {
        $m3 = $this->periodo(3);
        $m2 = $this->periodo(2);
        $m1 = $this->periodo(1);
        $actual = $this->periodo(0);

        $T = $this->diasHabilesTranscurridosEsperado();
        $D = $this->diasHabilesTotalesEsperado();

        // Los 3 clientes tienen el mismo prom (300) para poder fijar cada
        // caso de semáforo ajustando solo la venta del mes actual.
        foreach ([
            ['RUC-ROJO', 'Cliente Rojo', -20],       // decrecimiento -> rojo
            ['RUC-AMARILLO', 'Cliente Amarillo', 5], // crecimiento leve -> amarillo
            ['RUC-VERDE', 'Cliente Verde', 25],      // crecimiento fuerte -> verde
        ] as [$ruc, $razon, $pctObjetivo]) {
            $this->venta('AREQUIPA', $ruc, $razon, $m3['anio'], $m3['mes'], 300);
            $this->venta('AREQUIPA', $ruc, $razon, $m2['anio'], $m2['mes'], 300);
            $this->venta('AREQUIPA', $ruc, $razon, $m1['anio'], $m1['mes'], 300);

            $proyeccionObjetivo = 300 * (1 + $pctObjetivo / 100);
            $ventaActualReal = $proyeccionObjetivo * $T / $D;
            $this->venta('AREQUIPA', $ruc, $razon, $actual['anio'], $actual['mes'], $ventaActualReal);
        }

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data'));

        $resp->assertOk();
        $data = $resp->json();

        $this->assertSame($T, $data['dias_habiles_transcurridos']);
        $this->assertSame($D, $data['dias_habiles_totales']);

        $sede = $this->sedeEnRespuesta($data['sedes'], 'AREQUIPA');
        $this->assertNotNull($sede);

        $porRuc = collect($sede['clientes'])->keyBy('ruc');

        $this->assertEquals(300.0, $porRuc['RUC-ROJO']['prom']);
        $this->assertSame('rojo', $porRuc['RUC-ROJO']['semaforo']);
        $this->assertLessThan(0, $porRuc['RUC-ROJO']['variacion_pct']);

        $this->assertSame('amarillo', $porRuc['RUC-AMARILLO']['semaforo']);
        $this->assertGreaterThanOrEqual(0, $porRuc['RUC-AMARILLO']['variacion_pct']);
        $this->assertLessThan(10, $porRuc['RUC-AMARILLO']['variacion_pct']);

        $this->assertSame('verde', $porRuc['RUC-VERDE']['semaforo']);
        $this->assertGreaterThanOrEqual(10, $porRuc['RUC-VERDE']['variacion_pct']);
    }

    public function test_ordena_por_promedio_descendente_y_limita_a_25(): void
    {
        $m3 = $this->periodo(3);
        $m2 = $this->periodo(2);
        $m1 = $this->periodo(1);

        for ($i = 1; $i <= 30; $i++) {
            $ruc = "RUC-{$i}";
            $importe = $i * 100; // prom = i*100, todos distintos
            $this->venta('LINCE', $ruc, "Cliente {$i}", $m3['anio'], $m3['mes'], $importe);
            $this->venta('LINCE', $ruc, "Cliente {$i}", $m2['anio'], $m2['mes'], $importe);
            $this->venta('LINCE', $ruc, "Cliente {$i}", $m1['anio'], $m1['mes'], $importe);
        }

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data'));

        $sede = $this->sedeEnRespuesta($resp->json('sedes'), 'LINCE');
        $this->assertCount(25, $sede['clientes']);

        // El de mayor promedio (cliente 30, prom=3000) debe ser el primero.
        $this->assertSame('RUC-30', $sede['clientes'][0]['ruc']);
        $this->assertEquals(3000.0, $sede['clientes'][0]['prom']);

        $proms = array_column($sede['clientes'], 'prom');
        $ordenado = $proms;
        rsort($ordenado);
        $this->assertSame($ordenado, $proms);
    }

    public function test_excluye_clientes_sin_actividad_en_los_3_meses_previos(): void
    {
        $actual = $this->periodo(0);

        // Solo tiene venta este mes, nada en m3/m2/m1 -> prom=0 -> no debe salir.
        $this->venta('TRUJILLO', 'RUC-NUEVO', 'Cliente Nuevo', $actual['anio'], $actual['mes'], 500);

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data'));

        $sede = $this->sedeEnRespuesta($resp->json('sedes'), 'TRUJILLO');
        $this->assertNull($sede, 'La sede no debería aparecer si ningún cliente tiene actividad en los 3 meses previos.');
    }

    public function test_excluye_sedes_especiales_y_sin_asignar(): void
    {
        $m1 = $this->periodo(1);

        foreach (['CONSULTOR DE MONTURAS 1', 'CONSULTOR DE MONTURAS 2', 'MONTURAS GENERAL', '(EN BLANCO)'] as $sedeEspecial) {
            $this->venta($sedeEspecial, 'RUC-X', 'Cliente X', $m1['anio'], $m1['mes'], 1000);
        }
        $this->venta('PIURA', 'RUC-Y', 'Cliente Y', $m1['anio'], $m1['mes'], 1000);

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data'));

        $nombresSedes = array_column($resp->json('sedes'), 'sede');

        foreach (['CONSULTOR DE MONTURAS 1', 'CONSULTOR DE MONTURAS 2', 'MONTURAS GENERAL', '(EN BLANCO)'] as $sedeEspecial) {
            $this->assertNotContains($sedeEspecial, $nombresSedes);
        }
        $this->assertContains('PIURA', $nombresSedes);
    }

    public function test_usuario_de_sede_solo_ve_su_propia_sede(): void
    {
        $m1 = $this->periodo(1);
        $this->venta('AREQUIPA', 'RUC-AQP', 'Cliente AQP', $m1['anio'], $m1['mes'], 1000);
        $this->venta('HUANUCO', 'RUC-HCO', 'Cliente HCO', $m1['anio'], $m1['mes'], 1000);

        $sedeUser = $this->userConRol('sede', ['sede' => 'AREQUIPA', 'puede_ver_venta_clientes' => true]);
        $resp = $this->actingAs($sedeUser)->getJson(route('comercial.venta-cliente.top.data'));

        $resp->assertOk();
        $nombresSedes = array_column($resp->json('sedes'), 'sede');
        $this->assertSame(['AREQUIPA'], $nombresSedes);
    }

    public function test_usuario_sin_permiso_no_accede(): void
    {
        $user = $this->userConRol('consultor');

        $this->actingAs($user)
            ->get(route('comercial.venta-cliente.top'))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('comercial.venta-cliente.top.data'))
            ->assertStatus(403);
    }

    public function test_feriado_reduce_los_dias_habiles_totales_y_transcurridos(): void
    {
        // Primer día no-domingo del mes (siempre <= día 7, por lo tanto <= "hoy" que está fijo en el día 15).
        $diaFeriado = Carbon::now()->copy()->startOfMonth();
        while ($diaFeriado->dayOfWeek === Carbon::SUNDAY) {
            $diaFeriado->addDay();
        }
        Feriado::create([
            'fecha'   => $diaFeriado->toDateString(),
            'motivo'  => 'Feriado de prueba',
            'tipo'    => 'nacional',
            'fuente'  => 'manual',
        ]);

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data'));

        $resp->assertOk();
        $this->assertSame($this->diasHabilesTranscurridosEsperado() - 1, $resp->json('dias_habiles_transcurridos'));
        $this->assertSame($this->diasHabilesTotalesEsperado() - 1, $resp->json('dias_habiles_totales'));
    }

    public function test_mes_de_referencia_pasado_muestra_venta_real_sin_proyeccion(): void
    {
        // Mes de referencia: 2 meses antes del "hoy" congelado en setUp.
        $ref = Carbon::now()->subMonthsNoOverflow(2);
        $refM1 = $ref->copy()->subMonthsNoOverflow(1);
        $refM2 = $ref->copy()->subMonthsNoOverflow(2);
        $refM3 = $ref->copy()->subMonthsNoOverflow(3);

        $this->venta('CUSCO', 'RUC-CERRADO', 'Cliente Cerrado', $refM3->year, $refM3->month, 300);
        $this->venta('CUSCO', 'RUC-CERRADO', 'Cliente Cerrado', $refM2->year, $refM2->month, 300);
        $this->venta('CUSCO', 'RUC-CERRADO', 'Cliente Cerrado', $refM1->year, $refM1->month, 300);
        $this->venta('CUSCO', 'RUC-CERRADO', 'Cliente Cerrado', $ref->year, $ref->month, 360); // +20% real

        $admin = $this->userConRol('admin');
        $resp = $this->actingAs($admin)->getJson(route('comercial.venta-cliente.top.data', [
            'anio' => $ref->year,
            'mes'  => $ref->month,
        ]));

        $resp->assertOk();
        $data = $resp->json();

        $this->assertTrue($data['mes_cerrado']);
        $this->assertSame(self::MESES_NOMBRES[$ref->month] . ' ' . $ref->year, $data['periodos']['actual']);

        $cliente = collect($data['sedes'])
            ->firstWhere('sede', 'CUSCO')['clientes'][0];

        // Sin días hábiles de por medio: la venta del mes actual = la venta real, sin escalar.
        $this->assertEquals(360.0, $cliente['venta_actual']);
        $this->assertEquals(360.0, $cliente['venta_actual_real']);
        $this->assertEquals(300.0, $cliente['prom']);
        $this->assertEquals(20.0, $cliente['variacion_pct']);
        $this->assertSame('verde', $cliente['semaforo']);
    }

    public function test_mes_de_referencia_igual_al_mes_actual_se_trata_como_en_curso(): void
    {
        $hoy = Carbon::now();

        $admin = $this->userConRol('admin');
        $sinParametros = $this->actingAs($admin)
            ->getJson(route('comercial.venta-cliente.top.data'))
            ->json();

        $admin2 = $this->userConRol('admin');
        $conMesActualExplicito = $this->actingAs($admin2)
            ->getJson(route('comercial.venta-cliente.top.data', ['anio' => $hoy->year, 'mes' => $hoy->month]))
            ->json();

        $this->assertFalse($sinParametros['mes_cerrado']);
        $this->assertFalse($conMesActualExplicito['mes_cerrado']);
        $this->assertSame($sinParametros['dias_habiles_transcurridos'], $conMesActualExplicito['dias_habiles_transcurridos']);
        $this->assertSame($sinParametros['dias_habiles_totales'], $conMesActualExplicito['dias_habiles_totales']);
    }
}
