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
        if (!Schema::hasTable('ai_interactions')) {
            Schema::create('ai_interactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id');
                $table->string('user_role', 50)->nullable();
                $table->string('module', 100)->default('general');
                $table->text('question');
                $table->json('context')->nullable();
                $table->text('ai_response');
                $table->string('response_type', 50)->default('direct_answer');
                $table->boolean('was_helpful')->nullable();
                $table->text('feedback_comment')->nullable();
                $table->string('action_taken')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_knowledge_base')) {
            Schema::create('ai_knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->string('category', 100);
                $table->text('question_pattern');
                $table->text('answer_template');
                $table->decimal('confidence_score', 3, 2)->default(0.75);
                $table->integer('usage_count')->default(0);
                $table->decimal('success_rate', 3, 2)->default(1.00);
                $table->timestamp('last_used_at')->nullable();
                $table->integer('created_from_interactions')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('knowledge_base')) {
            Schema::create('knowledge_base', function (Blueprint $table) {
                $table->increments('id');
                $table->string('categoria', 100)->nullable();
                $table->text('pregunta')->nullable();
                $table->text('respuesta')->nullable();
                $table->text('keywords')->nullable();
                $table->text('ejemplos')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
        Schema::dropIfExists('ai_knowledge_base');
        Schema::dropIfExists('ai_interactions');
    }
};
