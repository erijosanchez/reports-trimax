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
        if (!Schema::hasTable('descuentos_especiales')) {
            Schema::create('descuentos_especiales', function (Blueprint $table) {
                $table->id();
                $table->string('numero_descuento')->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('numero_factura')->nullable();
                $table->string('numero_orden')->nullable();
                $table->string('sede');
                $table->string('ruc');
                $table->string('razon_social');
                $table->string('consultor');
                $table->string('ciudad');
                $table->text('descuento_especial');
                $table->enum('tipo', ['ANULACION', 'CORTESIA', 'DESCUENTO ADICIONAL', 'DESCUENTO TOTAL', 'OTROS']);
                $table->string('marca');
                $table->string('ar')->nullable();
                $table->string('disenos')->nullable();
                $table->string('material')->nullable();
                $table->text('comentarios')->nullable();
                $table->enum('aplicado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');
                $table->unsignedBigInteger('aplicado_por')->nullable();
                $table->timestamp('aplicado_at')->nullable();
                $table->enum('aprobado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');
                $table->unsignedBigInteger('aprobado_por')->nullable();
                $table->timestamp('aprobado_at')->nullable();
                $table->json('archivos_adjuntos')->nullable();
                $table->boolean('habilitado')->default(true);
                $table->text('motivo_deshabilitacion')->nullable();
                $table->timestamp('deshabilitado_at')->nullable();
                $table->unsignedBigInteger('deshabilitado_por')->nullable();
                $table->text('motivo_rehabilitacion')->nullable();
                $table->timestamp('rehabilitado_at')->nullable();
                $table->unsignedBigInteger('rehabilitado_por')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('retiros_ordenes')) {
            Schema::create('retiros_ordenes', function (Blueprint $table) {
                $table->id();
                $table->string('sede');
                $table->string('numero_orden')->nullable();
                $table->string('motivo')->nullable();
                $table->string('nombre_responsable')->nullable();
                $table->text('observacion')->nullable();
                $table->enum('status', ['espera', 'atendido', 'rechazado'])->default('espera');
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ordenes_historico')) {
            Schema::create('ordenes_historico', function (Blueprint $table) {
                $table->id();
                $table->string('descripcion_sede', 150)->nullable();
                $table->string('numero_orden', 50)->nullable()->unique('uq_numero_orden');
                $table->string('ruc', 20)->nullable();
                $table->string('cliente', 200)->nullable();
                $table->string('diseno', 200)->nullable();
                $table->string('descripcion_producto', 300)->nullable();
                $table->decimal('importe', 12, 2)->nullable();
                $table->string('orden_compra', 100)->nullable();
                $table->date('fecha_orden')->nullable();
                $table->string('hora_orden', 20)->nullable();
                $table->string('tipo_orden', 100)->nullable();
                $table->string('nombre_usuario', 150)->nullable();
                $table->string('estado_orden', 50)->nullable();
                $table->string('ubicacion_orden', 150)->nullable();
                $table->string('descripcion_tallado', 300)->nullable();
                $table->string('tratamiento', 200)->nullable();
                $table->unsignedSmallInteger('lead_time')->nullable();
                $table->unsignedSmallInteger('mes')->nullable();
                $table->unsignedSmallInteger('anio')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ordenes_sede_stats')) {
            Schema::create('ordenes_sede_stats', function (Blueprint $table) {
                $table->id();
                $table->string('sede', 100);
                $table->date('fecha');
                $table->unsignedSmallInteger('mes');
                $table->unsignedSmallInteger('anio');
                $table->unsignedInteger('cant')->default(0);
                $table->unsignedInteger('facturadas')->default(0);
                $table->unsignedInteger('prod')->default(0);
                $table->timestamps();
                $table->unique(['sede', 'fecha'], 'uq_sede_fecha');
            });
        }

        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id();
                $table->date('fecha');
                $table->string('tipo_documento', 50)->nullable();
                $table->string('nro_documento', 100)->nullable();
                $table->string('nro_orden_fabricacion', 100)->nullable();
                $table->string('ruc_dni', 20)->nullable();
                $table->string('razon_social')->nullable();
                $table->string('tipo_cliente', 100)->nullable();
                $table->string('motorizado', 100)->nullable();
                $table->string('sede', 100)->nullable();
                $table->string('zona', 100)->nullable();
                $table->string('cod_producto', 50)->nullable();
                $table->text('descripcion')->nullable();
                $table->decimal('importe', 12, 2)->nullable();
                $table->decimal('igv', 12, 2)->nullable();
                $table->decimal('importe_global', 12, 2)->nullable();
                $table->integer('cantidad')->nullable();
                $table->integer('anio')->nullable();
                $table->integer('mes')->nullable();
                $table->string('tallado', 100)->nullable();
                $table->string('marca', 100)->nullable();
                $table->string('diseno', 100)->nullable();
                $table->string('material', 100)->nullable();
                $table->string('tipo_fotocromatico', 100)->nullable();
                $table->string('color', 100)->nullable();
                $table->string('tipo_articulo', 100)->nullable();
                $table->string('tipo_articulo2', 100)->nullable();
                $table->string('tipo_tributo', 100)->nullable();
                $table->string('doc_referencia_nc', 100)->nullable();
                $table->text('motivo_nc')->nullable();
                $table->text('observacion_nc')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asignacion_bases')) {
            Schema::create('asignacion_bases', function (Blueprint $table) {
                $table->id();
                $table->date('fecha_asignacion');
                $table->string('numero_orden', 20);
                $table->string('codigo_pt', 50)->nullable();
                $table->string('producto_pt')->nullable();
                $table->string('id_catalogo_base', 20)->nullable();
                $table->string('descripcion_base')->nullable();
                $table->unsignedTinyInteger('cantidad')->default(1);
                $table->string('ojo', 1)->nullable();
                $table->string('estado_asignacion', 1);
                $table->string('descripcion_art')->nullable();
                $table->decimal('precio', 10, 4)->nullable();
                $table->unsignedTinyInteger('mes');
                $table->unsignedSmallInteger('anio');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_bases');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('ordenes_sede_stats');
        Schema::dropIfExists('ordenes_historico');
        Schema::dropIfExists('retiros_ordenes');
        Schema::dropIfExists('descuentos_especiales');
    }
};
