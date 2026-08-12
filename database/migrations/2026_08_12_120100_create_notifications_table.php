<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla estándar de Laravel para el canal "database" de notificaciones
 * (bandeja in-app / campanita). Ninguna notificación del CRM usaba ese canal
 * hasta ahora, solo "mail".
 *
 * Ya existía una tabla `notifications` — scaffolding sin usar de
 * 2026_07_27_090800_create_admin_dashboard_tables.php (columnas user_id/
 * title/message/is_read, sin `notifiable_type`/`notifiable_id`, ningún
 * código del CRM la lee ni la escribe). No es compatible con el esquema
 * polimórfico que espera el canal "database" de Laravel, así que se
 * reemplaza por la tabla estándar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notifications') && !Schema::hasColumn('notifications', 'notifiable_type')) {
            Schema::drop('notifications');
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
