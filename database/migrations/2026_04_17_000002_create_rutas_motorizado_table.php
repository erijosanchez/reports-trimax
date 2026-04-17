<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutas_motorizado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorizado_id')->constrained('motorizados')->cascadeOnDelete();
            $table->foreignId('creado_por')->constrained('users');
            $table->string('nombre')->nullable();
            $table->string('sede');
            $table->enum('estado', ['programada', 'en_ruta', 'completada', 'cancelada'])->default('programada');
            $table->timestamp('inicio_at')->nullable();
            $table->timestamp('fin_at')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutas_motorizado');
    }
};
