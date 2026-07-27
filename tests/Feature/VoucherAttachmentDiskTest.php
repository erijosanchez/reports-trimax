<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre S1 (SEGURIDAD.md): los adjuntos de vouchers deben guardarse en el
 * disco 'local' (fuera de la raíz web), no en 'public' (servido por nginx sin
 * autenticación). Y S10: servirArchivo()/getFacturas() deben exigir
 * puedeVerVouchers(), igual que el resto del controlador.
 */
class VoucherAttachmentDiskTest extends TestCase
{
    use RefreshDatabase;

    private function sedeUser(string $sede): User
    {
        Role::findOrCreate('sede', 'web');

        $user = User::factory()->create(['sede' => $sede]);
        $user->assignRole('sede');

        return $user;
    }

    /** Usuario autenticado sin ningún rol/flag que otorgue puedeVerVouchers(). */
    private function usuarioSinPermiso(): User
    {
        Role::findOrCreate('consultor', 'web');

        $user = User::factory()->create();
        $user->assignRole('consultor');

        return $user;
    }

    public function test_uploaded_attachment_is_stored_on_local_disk_not_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = $this->sedeUser('Lima');

        $response = $this->actingAs($user)->postJson(route('vouchers.store'), [
            'codigo'   => 'V-001',
            'facturas' => [
                ['factura' => 'F001', 'ruc' => '20123456789', 'monto' => 100.50],
            ],
            'archivos' => [UploadedFile::fake()->create('comprobante.pdf', 100, 'application/pdf')],
        ]);

        $response->assertOk();

        $voucher = Voucher::firstOrFail();
        $path    = $voucher->archivos[0]['path'];

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_authorized_user_can_download_the_attachment(): void
    {
        Storage::fake('local');

        $user = $this->sedeUser('Lima');

        $this->actingAs($user)->postJson(route('vouchers.store'), [
            'codigo'   => 'V-002',
            'facturas' => [
                ['factura' => 'F001', 'ruc' => '20123456789', 'monto' => 100.50],
            ],
            'archivos' => [UploadedFile::fake()->create('comprobante.pdf', 100, 'application/pdf')],
        ])->assertOk();

        $voucher = Voucher::firstOrFail();

        $this->actingAs($user)
            ->get(route('vouchers.archivo', ['id' => $voucher->id, 'index' => 0]))
            ->assertOk();
    }

    public function test_user_without_permission_cannot_download_the_attachment(): void
    {
        Storage::fake('local');

        $owner = $this->sedeUser('Lima');

        $this->actingAs($owner)->postJson(route('vouchers.store'), [
            'codigo'   => 'V-003',
            'facturas' => [
                ['factura' => 'F001', 'ruc' => '20123456789', 'monto' => 100.50],
            ],
            'archivos' => [UploadedFile::fake()->create('comprobante.pdf', 100, 'application/pdf')],
        ])->assertOk();

        $voucher = Voucher::firstOrFail();
        $intruso = $this->usuarioSinPermiso();

        $this->actingAs($intruso)
            ->get(route('vouchers.archivo', ['id' => $voucher->id, 'index' => 0]))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_view_facturas(): void
    {
        $owner = $this->sedeUser('Lima');

        $this->actingAs($owner)->postJson(route('vouchers.store'), [
            'codigo'   => 'V-004',
            'facturas' => [
                ['factura' => 'F001', 'ruc' => '20123456789', 'monto' => 100.50],
            ],
        ])->assertOk();

        $voucher = Voucher::firstOrFail();
        $intruso = $this->usuarioSinPermiso();

        $this->actingAs($intruso)
            ->getJson(route('vouchers.facturas', ['id' => $voucher->id]))
            ->assertForbidden();
    }
}
