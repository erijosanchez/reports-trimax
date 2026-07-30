<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estas columnas ya existen en producción (llegaron con el backup, sin
 * migración propia — ver INFRAESTRUCTURA.md, I4). `create_users_table` solo
 * trae el esquema estándar de Breeze, así que un esquema nuevo (tests con
 * sqlite en memoria, entorno recién clonado) se queda corto en 25 columnas
 * reales: sede/permisos por módulo, 2FA (S4) y tracking. Guardada por
 * columna: no-op en producción, donde ya existen.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cargo')) {
                $table->string('cargo')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'firma_imagen')) {
                $table->mediumText('firma_imagen')->nullable()->after('cargo');
            }
            if (!Schema::hasColumn('users', 'es_gerente_general')) {
                $table->boolean('es_gerente_general')->default(false)->after('firma_imagen');
            }
            if (!Schema::hasColumn('users', 'sede')) {
                $table->string('sede', 50)->nullable()->after('email');
            }

            foreach ([
                'puede_ver_ventas_consolidadas',
                'puede_ver_descuentos_especiales',
                'puede_ver_consultar_orden',
                'puede_ver_acuerdos_comerciales',
                'puede_ver_lead_time',
                'puede_ver_pendiente_entrega_montura',
                'puede_ver_venta_clientes',
                'puede_ver_ordenes_x_sede',
                'puede_ver_asignacion_bases',
                'puede_crear_requerimientos',
                'puede_gestionar_requerimientos',
                'puede_ver_todos_requerimientos',
                'puede_ver_productividad_sedes',
                'puede_ver_productivy_total',
                'puede_ver_retiros_ordenes',
                'puede_ver_vouchers',
                'puede_ver_desbloqueo',
                'puede_ver_tracking',
                'puede_gestionar_tracking',
                'puede_ver_motorizados',
            ] as $col) {
                if (!Schema::hasColumn('users', $col)) {
                    $table->boolean($col)->default(false);
                }
            }

            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'cargo', 'firma_imagen', 'es_gerente_general', 'sede',
                'puede_ver_ventas_consolidadas', 'puede_ver_descuentos_especiales',
                'puede_ver_consultar_orden', 'puede_ver_acuerdos_comerciales',
                'puede_ver_lead_time', 'puede_ver_pendiente_entrega_montura',
                'puede_ver_venta_clientes', 'puede_ver_ordenes_x_sede',
                'puede_ver_asignacion_bases', 'puede_crear_requerimientos',
                'puede_gestionar_requerimientos', 'puede_ver_todos_requerimientos',
                'puede_ver_productividad_sedes', 'puede_ver_productivy_total',
                'puede_ver_retiros_ordenes', 'puede_ver_vouchers', 'puede_ver_desbloqueo',
                'puede_ver_tracking', 'puede_gestionar_tracking', 'puede_ver_motorizados',
                'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
                'is_active', 'last_login_at',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
