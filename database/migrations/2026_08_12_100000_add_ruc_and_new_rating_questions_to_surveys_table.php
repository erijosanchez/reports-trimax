<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Segunda vuelta del rediseño de encuesta (estructura final de Marketing,
 * 2026-08-12): "razón social" se abre en RUC + razón social, y la pregunta
 * de cierre "productos" se reemplaza por "tiempos de entrega" (obligatoria)
 * y "promociones" (opcional). productos_rating queda deprecada (se
 * conserva para no perder las encuestas ya respondidas con ese esquema),
 * igual que se hizo con service_quality_rating en el rediseño anterior.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'ruc')) {
                $table->string('ruc', 11)->nullable()->after('client_name');
            }
            if (!Schema::hasColumn('surveys', 'tiempos_entrega_rating')) {
                $table->smallInteger('tiempos_entrega_rating')->nullable()->after('productos_rating');
            }
            if (!Schema::hasColumn('surveys', 'promociones_rating')) {
                $table->smallInteger('promociones_rating')->nullable()->after('tiempos_entrega_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
                'ruc',
                'tiempos_entrega_rating',
                'promociones_rating',
            ]);
        });
    }
};
