<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones_motorizado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorizado_id')->constrained('motorizados')->cascadeOnDelete();
            $table->foreignId('ruta_id')->nullable()->constrained('rutas_motorizado')->nullOnDelete();
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->decimal('velocidad', 8, 2)->nullable();
            $table->decimal('precision_metros', 8, 2)->nullable();
            $table->string('fuente')->default('browser'); // browser | traccar
            $table->timestamp('registrado_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_motorizado');
    }
};
