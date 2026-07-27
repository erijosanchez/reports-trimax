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
        if (!Schema::hasTable('dashboards')) {
            Schema::create('dashboards', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->text('powerbi_link_encrypted');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dashboard_user')) {
            Schema::create('dashboard_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('dashboard_id');
                $table->boolean('can_view')->default(true);
                $table->timestamps();
                $table->unique(['user_id', 'dashboard_id'], 'unique_user_dashboard');
            });
        }

        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key_name')->unique();
                $table->text('key_value')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('uploaded_files')) {
            Schema::create('uploaded_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('filename');
                $table->string('original_name');
                $table->string('file_type', 50);
                $table->string('mime_type', 100)->nullable();
                $table->text('file_path');
                $table->unsignedBigInteger('file_size');
                $table->boolean('is_public')->default(false);
                $table->text('description')->nullable();
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('downloads_count')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('file_permissions')) {
            Schema::create('file_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('file_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->boolean('can_view')->default(true);
                $table->boolean('can_download')->default(true);
                $table->boolean('can_delete')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type');
                $table->string('title');
                $table->text('message')->nullable();
                $table->json('data')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('file_permissions');
        Schema::dropIfExists('uploaded_files');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('dashboard_user');
        Schema::dropIfExists('dashboards');
    }
};
