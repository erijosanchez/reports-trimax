<?php

namespace Tests\Feature;

use App\Models\SolicitudDesbloqueo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre S1 (SEGURIDAD.md): los adjuntos de desbloqueo deben guardarse en el
 * disco 'local' (fuera de la raíz web), no en 'public' (servido por nginx sin
 * autenticación).
 */
class DesbloqueoAttachmentDiskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User::role(...) lanza RoleDoesNotExist si el rol consultado no existe
     * en la tabla `roles`, aunque no haya ningún usuario asignado. Como
     * store()/revisar() notifican a roles fijos (finanzas, super_admin, ...),
     * se crean todos los roles que el modelo User conoce para que esas
     * consultas no revienten en un entorno de test vacío.
     */
    private function sedeUser(string $sede): User
    {
        foreach (['super_admin', 'admin', 'sede', 'finanzas', 'rrhh', 'marketing', 'consultor'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $user = User::factory()->create(['sede' => $sede]);
        $user->assignRole('sede');

        return $user;
    }

    public function test_uploaded_attachment_is_stored_on_local_disk_not_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Notification::fake();

        $user = $this->sedeUser('Lima');

        $response = $this->actingAs($user)->postJson(route('desbloqueo.store'), [
            'ruc'          => '20123456789',
            'razon_social' => 'Cliente SAC',
            'comentarios'  => 'Sustento adjunto',
            'archivos'     => [UploadedFile::fake()->create('sustento.pdf', 100, 'application/pdf')],
        ]);

        $response->assertOk();

        $solicitud = SolicitudDesbloqueo::firstOrFail();
        $path      = $solicitud->archivos[0]['path'];

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_owner_sede_can_download_its_own_attachment(): void
    {
        Storage::fake('local');
        Notification::fake();

        $user = $this->sedeUser('Lima');

        $this->actingAs($user)->postJson(route('desbloqueo.store'), [
            'ruc'          => '20123456789',
            'razon_social' => 'Cliente SAC',
            'archivos'     => [UploadedFile::fake()->create('sustento.pdf', 100, 'application/pdf')],
        ])->assertOk();

        $solicitud = SolicitudDesbloqueo::firstOrFail();

        $this->actingAs($user)
            ->get(route('desbloqueo.file', ['id' => $solicitud->id, 'index' => 0]))
            ->assertOk();
    }

    public function test_a_different_sede_cannot_download_the_attachment(): void
    {
        Storage::fake('local');
        Notification::fake();

        $owner = $this->sedeUser('Lima');

        $this->actingAs($owner)->postJson(route('desbloqueo.store'), [
            'ruc'          => '20123456789',
            'razon_social' => 'Cliente SAC',
            'archivos'     => [UploadedFile::fake()->create('sustento.pdf', 100, 'application/pdf')],
        ])->assertOk();

        $solicitud = SolicitudDesbloqueo::firstOrFail();
        $otraSede  = $this->sedeUser('Huánuco');

        $this->actingAs($otraSede)
            ->get(route('desbloqueo.file', ['id' => $solicitud->id, 'index' => 0]))
            ->assertForbidden();
    }
}
