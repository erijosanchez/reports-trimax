<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ya existen en producción (backup, sin migración propia — ver
 * INFRAESTRUCTURA.md, I4). Guardadas por tabla: no-op donde ya existen.
 *
 * user_locations ya tenía una migración de ALTER
 * (2025_11_18_052915_add_gps_fields_to_user_locations_table.php), pero ninguna
 * la creaba — se guardó ese ALTER con Schema::hasTable() (2026-07-25) y aquí
 * se agrega la creación completa, con los campos GPS ya incluidos, para que
 * el orden (ALTER antes que CREATE, por fecha) no deje la tabla a medias en
 * un entorno nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('failed_login_attempts')) {
            Schema::create('failed_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('reason', 50)->nullable();
                $table->timestamp('attempted_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('ip_blacklist')) {
            Schema::create('ip_blacklist', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->unique();
                $table->string('reason')->nullable();
                $table->timestamp('blocked_until')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_locations')) {
            Schema::create('user_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('session_id')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('street_name')->nullable();
                $table->string('street_number')->nullable();
                $table->string('district')->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->text('formatted_address')->nullable();
                $table->enum('location_type', ['ip', 'gps'])->default('ip');
                $table->decimal('accuracy', 10, 2)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('country_code', 2)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_vpn')->default(false);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('session_id')->nullable();
                $table->string('action', 100);
                $table->string('resource_type', 100)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('request_method', 10)->nullable();
                $table->text('request_url')->nullable();
                $table->integer('response_status')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
        Schema::dropIfExists('user_locations');
        Schema::dropIfExists('ip_blacklist');
        Schema::dropIfExists('failed_login_attempts');
    }
};
