<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paradas_ruta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas_motorizado')->cascadeOnDelete();
            $table->integer('orden');
            $table->string('cliente')->nullable();
            $table->string('direccion');
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('referencia')->nullable();
            $table->enum('estado', ['pendiente', 'entregado', 'fallido'])->default('pendiente');
            $table->string('motivo_fallo')->nullable();
            $table->timestamp('llegada_at')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paradas_ruta');
    }
};
