<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ya existen en producción (backup, sin migración propia — ver
 * INFRAESTRUCTURA.md, I4). Guardadas por tabla: no-op donde ya existen.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requerimientos_personal')) {
            Schema::create('requerimientos_personal', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 20)->unique();
                $table->unsignedBigInteger('solicitante_id');
                $table->string('gerencia')->default('GERENCIA COMERCIAL');
                $table->string('puesto');
                $table->string('sede');
                $table->string('jefe_directo');
                $table->string('supervisa_a')->nullable();
                $table->unsignedTinyInteger('num_vacantes')->default(1);
                $table->boolean('info_confidencial')->default(false);
                $table->enum('tipo', ['Regular', 'Urgente']);
                $table->enum('tipo_vacante', ['vacante', 'reemplazo', 'posicion_nueva'])->nullable();
                $table->enum('permanencia', ['temporal', 'permanente'])->nullable();
                $table->boolean('disponibilidad_viaje')->default(false);
                $table->enum('jornada', ['tiempo_parcial', 'tiempo_completo'])->nullable();
                $table->text('condiciones_oferta')->nullable();
                $table->text('comentarios')->nullable();
                $table->text('motivo')->nullable();
                $table->json('candidatos')->nullable();
                $table->json('herramientas')->nullable();
                $table->date('fecha_estimada_contratacion')->nullable();
                $table->string('tipo_contrato')->nullable();
                $table->string('duracion_contrato')->nullable();
                $table->decimal('remuneracion_prevista', 10, 2)->nullable();
                $table->string('horario_trabajo')->nullable();
                $table->text('beneficios')->nullable();
                $table->mediumText('firma_solicitante_data')->nullable();
                $table->dateTime('firma_solicitante_at')->nullable();
                $table->string('firma_solicitante_nombre')->nullable();
                $table->mediumText('firma_rrhh_data')->nullable();
                $table->dateTime('firma_rrhh_at')->nullable();
                $table->string('firma_rrhh_nombre')->nullable();
                $table->mediumText('firma_gerente_data')->nullable();
                $table->dateTime('firma_gerente_at')->nullable();
                $table->string('firma_gerente_nombre')->nullable();
                $table->unsignedBigInteger('responsable_rh_id')->nullable();
                $table->string('responsable_rh_externo')->nullable();
                $table->enum('estado', ['Pendiente', 'En Proceso', 'Contratado', 'Cancelado'])->default('Pendiente');
                $table->integer('sla')->default(45);
                $table->timestamp('fecha_solicitud')->useCurrent();
                $table->timestamp('fecha_cierre')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('requerimiento_historial')) {
            Schema::create('requerimiento_historial', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requerimiento_id');
                $table->unsignedBigInteger('user_id');
                $table->enum('tipo_evento', [
                    'creacion', 'cambio_estado', 'asignacion_rh', 'publicacion_oferta',
                    'revision_cvs', 'entrevista_virtual', 'entrevista_presencial',
                    'evaluacion', 'oferta_candidato', 'en_capacitacion', 'nota', 'alerta_sla',
                ]);
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->string('estado_anterior')->nullable();
                $table->string('estado_nuevo')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimiento_historial');
        Schema::dropIfExists('requerimientos_personal');
    }
};
