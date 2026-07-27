<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ya existen en producción (backup, sin migración propia — ver
 * INFRAESTRUCTURA.md, I4). Guardadas por tabla: no-op donde ya existen.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users_marketing')) {
            Schema::create('users_marketing', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role', 50);
                $table->string('location')->nullable();
                $table->string('unique_token', 32)->nullable()->unique();
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('consultor_sede')) {
            Schema::create('consultor_sede', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('consultor_id');
                $table->unsignedBigInteger('sede_id');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['consultor_id', 'sede_id'], 'consultor_sede_unique');
            });
        }

        if (!Schema::hasTable('surveys')) {
            Schema::create('surveys', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('client_name')->nullable();
                $table->smallInteger('experience_rating');
                $table->smallInteger('service_quality_rating');
                $table->text('comments')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('consultor_sede');
        Schema::dropIfExists('users_marketing');
    }
};
