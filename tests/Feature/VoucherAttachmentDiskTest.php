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
 * autenticación).
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
}
