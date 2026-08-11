<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El select de Tipo en el formulario de Descuentos Especiales agregó la
 * opción "APOYO COMERCIAL", pero la columna `tipo` es un ENUM de MySQL que
 * no la contemplaba, causando "Data truncated for column 'tipo'" al guardar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('descuentos_especiales')) {
            return;
        }

        Schema::table('descuentos_especiales', function (Blueprint $table) {
            $table->enum('tipo', ['ANULACION', 'CORTESIA', 'DESCUENTO ADICIONAL', 'DESCUENTO TOTAL', 'APOYO COMERCIAL', 'OTROS'])->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('descuentos_especiales')) {
            return;
        }

        Schema::table('descuentos_especiales', function (Blueprint $table) {
            $table->enum('tipo', ['ANULACION', 'CORTESIA', 'DESCUENTO ADICIONAL', 'DESCUENTO TOTAL', 'OTROS'])->change();
        });
    }
};
