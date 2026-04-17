<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_ver_tracking')->default(false)->after('puede_ver_productividad_sedes');
            $table->boolean('puede_gestionar_tracking')->default(false)->after('puede_ver_tracking');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['puede_ver_tracking', 'puede_gestionar_tracking']);
        });
    }
};
