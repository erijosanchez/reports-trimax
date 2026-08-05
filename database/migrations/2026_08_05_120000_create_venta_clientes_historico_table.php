<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_clientes_historico', function (Blueprint $table) {
            $table->id();
            $table->string('sede', 100);
            $table->string('ruc', 20);
            $table->string('razon_social', 200)->nullable();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->decimal('importe', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['sede', 'ruc', 'anio', 'mes'], 'uq_sede_ruc_anio_mes');
            $table->index(['anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_clientes_historico');
    }
};
