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
        if (!Schema::hasTable('motorizados')) {
            Schema::create('motorizados', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('sede');
                $table->enum('tipo', ['motorizado', 'delivery'])->default('motorizado');
                $table->string('telefono')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('gps_rutas')) {
            Schema::create('gps_rutas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('motorizado_id');
                $table->date('fecha');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->decimal('distance_km', 10, 3)->default(0);
                $table->json('polyline')->nullable();
                $table->enum('status', ['pendiente', 'activa', 'completada'])->default('pendiente');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gps_posiciones')) {
            Schema::create('gps_posiciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('motorizado_id');
                $table->unsignedBigInteger('ruta_id');
                $table->decimal('latitud', 10, 7);
                $table->decimal('longitud', 10, 7);
                $table->float('velocidad')->default(0);
                $table->float('precicion')->nullable();
                $table->timestamp('capturado_en');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('entregas')) {
            Schema::create('entregas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('motorizado_id');
                $table->unsignedBigInteger('ruta_id');
                $table->string('cliente_nombre');
                $table->string('cliente_telefono')->nullable();
                $table->string('referencia')->nullable();
                $table->text('direccion');
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                $table->integer('orden_secuencia');
                $table->enum('estado', ['pendiente', 'completado', 'fallido'])->default('pendiente');
                $table->decimal('entrega_latitud', 10, 7)->nullable();
                $table->decimal('entrega_longitud', 10, 7)->nullable();
                $table->timestamp('entregado_en')->nullable();
                $table->text('notas')->nullable();
                $table->string('sede');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
        Schema::dropIfExists('gps_posiciones');
        Schema::dropIfExists('gps_rutas');
        Schema::dropIfExists('motorizados');
    }
};
