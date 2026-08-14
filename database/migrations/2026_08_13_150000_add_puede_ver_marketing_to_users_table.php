<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permiso especial (mismo patrón que puede_enviar_avisos, etc.): los roles
 * marketing y super_admin ya tienen acceso al módulo por su rol; este
 * permiso es para otorgar acceso a otros usuarios desde el panel de
 * usuarios, sin necesidad de asignarles el rol marketing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'puede_ver_marketing')) {
                $table->boolean('puede_ver_marketing')->default(false)->after('puede_enviar_avisos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'puede_ver_marketing')) {
                $table->dropColumn('puede_ver_marketing');
            }
        });
    }
};
