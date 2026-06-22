<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
                if (!Schema::hasColumn($tabla, 'revision_estado')) {
                    // null = sin revisar | 'conforme' | 'rechazado'
                    $table->string('revision_estado', 20)->nullable();
                }
                if (!Schema::hasColumn($tabla, 'revision_motivo')) {
                    $table->text('revision_motivo')->nullable();
                }
                if (!Schema::hasColumn($tabla, 'revision_user_id')) {
                    $table->unsignedBigInteger('revision_user_id')->nullable();
                }
                if (!Schema::hasColumn($tabla, 'revision_at')) {
                    $table->timestamp('revision_at')->nullable();
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
                foreach (['revision_estado', 'revision_motivo', 'revision_user_id', 'revision_at'] as $col) {
                    if (Schema::hasColumn($tabla, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
