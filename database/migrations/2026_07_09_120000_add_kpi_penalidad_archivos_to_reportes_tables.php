<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Revisión ampliada (Conforme Observado + penalización + adjuntos) para los
 * reportes de sedes. Estas columnas existían solo en database/aditional.sql y
 * no se habían aplicado en producción, provocando:
 *   SQLSTATE[42S22] Unknown column 'revision_kpi_penalidad'
 * al revisar reportes. Se convierte a migración para mantener el esquema en sync.
 */
return new class extends Migration
{
    private array $tablas = ['reportes_cobranza', 'reportes_caja_chica', 'reportes_comentarios'];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                // % que se resta PROPORCIONALMENTE al KPI (20 o 50)
                if (!Schema::hasColumn($tabla, 'revision_kpi_penalidad')) {
                    $table->decimal('revision_kpi_penalidad', 5, 2)->nullable()->after('revision_motivo');
                }
                // Adjuntos que finanzas sube al revisar (img/pdf/excel)
                if (!Schema::hasColumn($tabla, 'revision_archivos')) {
                    $table->json('revision_archivos')->nullable()->after('revision_kpi_penalidad');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                foreach (['revision_archivos', 'revision_kpi_penalidad'] as $col) {
                    if (Schema::hasColumn($tabla, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
