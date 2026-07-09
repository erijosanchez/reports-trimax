<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicitudes_desbloqueo')) {
            return;
        }

        Schema::create('solicitudes_desbloqueo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');          // solicitante
            $table->string('sede')->index();
            $table->string('ruc', 11);
            $table->string('razon_social');
            $table->text('comentarios')->nullable();

            // Revisión de finanzas
            $table->string('revision_estado', 20)->nullable(); // conforme | conforme_observado | rechazado
            $table->text('revision_motivo')->nullable();
            $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
            $table->json('revision_archivos')->nullable();
            $table->unsignedBigInteger('revision_user_id')->nullable();
            $table->timestamp('revision_at')->nullable();

            $table->timestamps();

            $table->index('revision_estado');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_desbloqueo');
    }
};
