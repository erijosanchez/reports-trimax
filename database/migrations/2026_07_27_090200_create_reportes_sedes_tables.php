<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ya existen en producción (backup, sin migración propia — ver
 * INFRAESTRUCTURA.md, I4). Incluyen ya las columnas de revisión y KPI que
 * agregan add_revision_to_reportes_tables.php y
 * add_kpi_penalidad_archivos_to_reportes_tables.php, porque esas dos
 * migraciones corren *antes* que esta (por fecha) y si la tabla no existe
 * todavía simplemente no hacen nada (`continue`) — el esquema completo tiene
 * que quedar aquí para un entorno nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reportes_cobranza')) {
            Schema::create('reportes_cobranza', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('sede', 100);
                $table->unsignedSmallInteger('semana_numero');
                $table->unsignedSmallInteger('anio');
                $table->date('semana_inicio')->nullable();
                $table->date('semana_fin')->nullable();
                $table->dateTime('fecha_limite')->nullable();
                $table->dateTime('fecha_envio_original')->nullable();
                $table->dateTime('fecha_ultimo_envio')->nullable();
                $table->json('archivos')->nullable();
                $table->text('notas')->nullable();
                $table->decimal('kpi_porcentaje', 5, 2)->nullable();
                $table->boolean('editado_tarde')->default(false);
                $table->boolean('sin_deposito')->default(false);
                $table->enum('estado', ['pendiente', 'en_tiempo', 'con_atraso', 'no_enviado'])->default('pendiente');
                $table->timestamps();
                $table->string('revision_estado', 20)->nullable();
                $table->text('revision_motivo')->nullable();
                $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
                $table->json('revision_archivos')->nullable();
                $table->unsignedBigInteger('revision_user_id')->nullable();
                $table->timestamp('revision_at')->nullable();
                $table->unique(['sede', 'semana_inicio']);
            });
        }

        if (!Schema::hasTable('reportes_caja_chica')) {
            Schema::create('reportes_caja_chica', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('sede', 100);
                $table->unsignedSmallInteger('semana_numero');
                $table->unsignedSmallInteger('anio');
                $table->date('semana_inicio')->nullable();
                $table->date('semana_fin')->nullable();
                $table->dateTime('fecha_limite')->nullable();
                $table->dateTime('fecha_envio_original')->nullable();
                $table->dateTime('fecha_ultimo_envio')->nullable();
                $table->json('archivos')->nullable();
                $table->text('notas')->nullable();
                $table->decimal('kpi_porcentaje', 5, 2)->nullable();
                $table->boolean('editado_tarde')->default(false);
                $table->enum('estado', ['pendiente', 'en_tiempo', 'con_atraso', 'no_enviado'])->default('pendiente');
                $table->timestamps();
                $table->string('revision_estado', 20)->nullable();
                $table->text('revision_motivo')->nullable();
                $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
                $table->json('revision_archivos')->nullable();
                $table->unsignedBigInteger('revision_user_id')->nullable();
                $table->timestamp('revision_at')->nullable();
                $table->unique(['sede', 'semana_numero', 'anio'], 'unique_cajachica_sede_semana');
            });
        }

        if (!Schema::hasTable('reportes_comentarios')) {
            Schema::create('reportes_comentarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('sede');
                $table->unsignedSmallInteger('semana_numero');
                $table->unsignedSmallInteger('anio');
                $table->date('semana_inicio')->nullable();
                $table->date('semana_fin')->nullable();
                $table->timestamp('fecha_limite')->nullable();
                $table->timestamp('fecha_envio_original')->nullable();
                $table->timestamp('fecha_ultimo_envio')->nullable();
                $table->json('archivos')->nullable();
                $table->text('notas')->nullable();
                $table->decimal('kpi_porcentaje', 5, 2)->nullable();
                $table->boolean('editado_tarde')->default(false);
                $table->string('estado')->default('pendiente');
                $table->timestamps();
                $table->string('revision_estado', 20)->nullable();
                $table->text('revision_motivo')->nullable();
                $table->decimal('revision_kpi_penalidad', 5, 2)->nullable();
                $table->json('revision_archivos')->nullable();
                $table->unsignedBigInteger('revision_user_id')->nullable();
                $table->timestamp('revision_at')->nullable();
                $table->unique(['sede', 'semana_numero', 'anio'], 'reportes_comentarios_sede_semana_anio');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_comentarios');
        Schema::dropIfExists('reportes_caja_chica');
        Schema::dropIfExists('reportes_cobranza');
    }
};
