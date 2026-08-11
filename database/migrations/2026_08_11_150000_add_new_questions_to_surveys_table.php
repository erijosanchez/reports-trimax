<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño de la encuesta (validado con Gerencia, 2026-08-11): agrega las
 * preguntas nuevas como columnas nullable. Las encuestas anteriores a este
 * cambio quedan con estos campos en null — no se migran ni se reconstruyen,
 * y quedan fuera de las métricas nuevas (que requieren productos_rating).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'sede_rating')) {
                $table->smallInteger('sede_rating')->nullable()->after('service_quality_rating');
            }
            if (!Schema::hasColumn('surveys', 'tiene_consultor')) {
                $table->boolean('tiene_consultor')->nullable()->after('sede_rating');
            }
            if (!Schema::hasColumn('surveys', 'consultor_id')) {
                $table->unsignedBigInteger('consultor_id')->nullable()->after('tiene_consultor');
                $table->foreign('consultor_id')->references('id')->on('users_marketing')->onDelete('set null');
                $table->index('consultor_id', 'idx_surveys_consultor_id');
            }
            if (!Schema::hasColumn('surveys', 'consultor_desconocido')) {
                $table->boolean('consultor_desconocido')->default(false)->after('consultor_id');
            }
            if (!Schema::hasColumn('surveys', 'consultor_rating')) {
                $table->smallInteger('consultor_rating')->nullable()->after('consultor_desconocido');
            }
            if (!Schema::hasColumn('surveys', 'productos_rating')) {
                $table->smallInteger('productos_rating')->nullable()->after('consultor_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'consultor_id')) {
                $table->dropForeign(['consultor_id']);
                $table->dropIndex('idx_surveys_consultor_id');
            }
            $table->dropColumn([
                'sede_rating',
                'tiene_consultor',
                'consultor_id',
                'consultor_desconocido',
                'consultor_rating',
                'productos_rating',
            ]);
        });
    }
};
