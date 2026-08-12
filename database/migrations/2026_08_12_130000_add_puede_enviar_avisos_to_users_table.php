<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permiso especial (mismo patrón que puede_ver_desbloqueo, etc.):
 * super_admin lo tiene siempre (Gate::before), y además se puede otorgar a
 * cualquier otro usuario desde el panel de usuarios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'puede_enviar_avisos')) {
                $table->boolean('puede_enviar_avisos')->default(false)->after('puede_ver_desbloqueo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'puede_enviar_avisos')) {
                $table->dropColumn('puede_enviar_avisos');
            }
        });
    }
};
