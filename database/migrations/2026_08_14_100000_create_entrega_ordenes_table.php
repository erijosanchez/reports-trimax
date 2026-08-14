<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Antes, una Entrega solo podía llevar 1 orden (entregas.referencia, texto libre
 * sin FK). En la práctica una entrega puede llevar varias órdenes, así que se
 * modela como tabla hija con FK real hacia entregas. entregas.referencia se
 * conserva (ahora como concatenación) porque Api/EntregaController::hoy() la
 * expone como contrato hacia la app móvil del motorizado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_ordenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entrega_id');
            $table->string('numero_orden');
            $table->string('cliente')->nullable();
            $table->string('ruc', 20)->nullable();
            $table->date('fecha_orden')->nullable();
            $table->timestamps();

            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
            $table->unique(['entrega_id', 'numero_orden']);
            $table->index('numero_orden');
        });

        DB::table('entregas')
            ->whereNotNull('referencia')
            ->where('referencia', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($entregas) {
                foreach ($entregas as $entrega) {
                    $orden = DB::table('ordenes_historico')
                        ->where('numero_orden', $entrega->referencia)
                        ->first(['cliente', 'ruc', 'fecha_orden']);

                    DB::table('entrega_ordenes')->insert([
                        'entrega_id'   => $entrega->id,
                        'numero_orden' => $entrega->referencia,
                        'cliente'      => $orden->cliente ?? null,
                        'ruc'          => $orden->ruc ?? null,
                        'fecha_orden'  => $orden->fecha_orden ?? null,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_ordenes');
    }
};
