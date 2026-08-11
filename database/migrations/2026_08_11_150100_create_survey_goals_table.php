<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Meta semanal de encuestas por sede. No existía ningún concepto de meta
 * en el sistema — se modela con vigencia por fecha para poder ajustarla
 * en el tiempo sin perder el historial de lo que aplicaba cada semana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sede_id');
            $table->unsignedInteger('meta_semanal');
            $table->date('vigente_desde');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sede_id')->references('id')->on('users_marketing')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['sede_id', 'vigente_desde'], 'idx_survey_goals_sede_vigencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_goals');
    }
};
