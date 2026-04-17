<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motorizados', function (Blueprint $table) {
            $table->string('api_token', 64)->unique()->nullable()->after('traccar_device_id');
        });
    }

    public function down(): void
    {
        Schema::table('motorizados', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
