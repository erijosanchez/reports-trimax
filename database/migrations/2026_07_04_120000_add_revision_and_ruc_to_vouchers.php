<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RUC por factura
        if (Schema::hasTable('voucher_facturas') && !Schema::hasColumn('voucher_facturas', 'ruc')) {
            Schema::table('voucher_facturas', function (Blueprint $table) {
                $table->string('ruc', 11)->nullable()->after('factura');
            });
        }

        // Revisión de finanzas en vouchers (mismo patrón que reportes_*)
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('vouchers', 'revision_estado')) {
                    // null = sin revisar | 'conforme' | 'conforme_observado' | 'rechazado'
                    $table->string('revision_estado', 20)->nullable();
                }
                if (!Schema::hasColumn('vouchers', 'revision_motivo')) {
                    $table->text('revision_motivo')->nullable();
                }
                if (!Schema::hasColumn('vouchers', 'revision_kpi_penalidad')) {
                    $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('vouchers', 'revision_archivos')) {
                    $table->json('revision_archivos')->nullable();
                }
                if (!Schema::hasColumn('vouchers', 'revision_user_id')) {
                    $table->unsignedBigInteger('revision_user_id')->nullable();
                }
                if (!Schema::hasColumn('vouchers', 'revision_at')) {
                    $table->timestamp('revision_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('voucher_facturas') && Schema::hasColumn('voucher_facturas', 'ruc')) {
            Schema::table('voucher_facturas', function (Blueprint $table) {
                $table->dropColumn('ruc');
            });
        }

        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                foreach (['revision_estado', 'revision_motivo', 'revision_kpi_penalidad', 'revision_archivos', 'revision_user_id', 'revision_at'] as $col) {
                    if (Schema::hasColumn('vouchers', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
