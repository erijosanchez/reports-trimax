<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * ordenes_historico ya existe en producción (backup, sin migración propia).
 * Índice guardado por existencia: no-op si ya está creado.
 */
return new class extends Migration
{
    private string $index = 'ordenes_historico_sede_numero_idx';

    public function up(): void
    {
        if (!Schema::hasTable('ordenes_historico')) {
            return;
        }

        if (!Schema::hasIndex('ordenes_historico', $this->index)) {
            Schema::table('ordenes_historico', function ($table) {
                $table->index(['descripcion_sede', 'numero_orden'], $this->index);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ordenes_historico') && Schema::hasIndex('ordenes_historico', $this->index)) {
            Schema::table('ordenes_historico', function ($table) {
                $table->dropIndex($this->index);
            });
        }
    }
};
