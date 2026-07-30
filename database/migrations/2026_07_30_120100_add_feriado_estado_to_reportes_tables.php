<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega 'feriado' al enum nativo de `estado` en reportes_cobranza (reporte
 * diario: cada día puede caer en feriado). reportes_caja_chica y
 * reportes_comentarios son semanales — ahí el feriado se resuelve corriendo
 * `fecha_limite` al siguiente día hábil (ver datosSemanActual()), sin
 * necesitar un estado propio.
 *
 * Usa Blueprint::change() (portable MySQL/SQLite desde Laravel 11, sin
 * doctrine/dbal) en vez de ALTER TABLE crudo, para que la migración corra
 * igual en producción (MySQL) y en el sqlite de los tests.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reportes_cobranza')) {
            return;
        }

        Schema::table('reportes_cobranza', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'en_tiempo', 'con_atraso', 'no_enviado', 'feriado'])
                ->default('pendiente')
                ->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('reportes_cobranza')) {
            return;
        }

        DB::table('reportes_cobranza')->where('estado', 'feriado')->update(['estado' => 'no_enviado']);

        Schema::table('reportes_cobranza', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'en_tiempo', 'con_atraso', 'no_enviado'])
                ->default('pendiente')
                ->change();
        });
    }
};
