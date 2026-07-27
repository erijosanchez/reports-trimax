<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ya existe en producción (backup, sin migración propia — ver
 * INFRAESTRUCTURA.md, I4). `TrackUserActivityMiddleware` la consulta en
 * *cualquier* ruta autenticada (web.php:76), así que sin esta tabla ningún
 * test de feature que autentique un usuario puede correr contra un esquema
 * nuevo. No-op en producción.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_sessions')) {
            return;
        }

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_id')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_online')->default(true);
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->unsignedInteger('session_duration')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
