<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'puede_ver_top_clientes')) {
                $table->boolean('puede_ver_top_clientes')->default(false)->after('puede_ver_venta_clientes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'puede_ver_top_clientes')) {
                $table->dropColumn('puede_ver_top_clientes');
            }
        });
    }
};
