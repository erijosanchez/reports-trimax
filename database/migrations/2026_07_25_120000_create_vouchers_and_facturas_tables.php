<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estas tablas ya existen en producción (llegaron con el backup, sin
 * migración propia — ver INFRAESTRUCTURA.md, I4). Esta migración solo las
 * crea cuando faltan (entornos nuevos, tests con sqlite en memoria); en
 * producción es un no-op porque `Schema::hasTable` ya es true.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 50);
                $table->string('sede', 100);
                $table->string('status', 20)->default('pendiente');
                $table->json('archivos')->nullable();
                $table->decimal('total', 10, 2)->default(0);
                $table->date('solicitado_at')->nullable();
                $table->date('aplicado_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('applied_by')->nullable();
                $table->string('revision_estado', 20)->nullable();
                $table->text('revision_motivo')->nullable();
                $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
                $table->json('revision_archivos')->nullable();
                $table->unsignedBigInteger('revision_user_id')->nullable();
                $table->timestamp('revision_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('voucher_facturas')) {
            Schema::create('voucher_facturas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_id');
                $table->string('factura', 50);
                $table->string('ruc', 11)->nullable();
                $table->decimal('monto', 10, 2);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_facturas');
        Schema::dropIfExists('vouchers');
    }
};
