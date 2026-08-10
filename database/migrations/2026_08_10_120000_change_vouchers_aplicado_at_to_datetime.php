<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `aplicado_at` era `date` (sin hora): la columna "Demora" del historial y
 * el KPI de aplicación no podían medir con precisión de horas/minutos, y se
 * recurría a `updated_at` como proxy — frágil, porque cualquier UPDATE
 * futuro sobre un voucher ya aplicado (p. ej. removeFactura sin guard de
 * status) lo corrompía en silencio. Con `aplicado_at` como datetime real,
 * `aplicar()` guarda el instante exacto y deja de depender de `updated_at`.
 *
 * Usa Blueprint::change() (portable MySQL/SQLite desde Laravel 11, sin
 * doctrine/dbal), igual que 2026_07_30_120100_add_feriado_estado_to_reportes_tables.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dateTime('aplicado_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        Schema::table('vouchers', function (Blueprint $table) {
            $table->date('aplicado_at')->nullable()->change();
        });
    }
};
