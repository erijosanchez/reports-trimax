<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avisos manuales dentro del sistema (distinto de las notificaciones
 * automáticas por evento de negocio): un usuario con permiso redacta un
 * aviso y elige a qué roles llega. `roles` null = a todos los usuarios
 * activos. Se entrega solo por la bandeja in-app (canal "database"), nunca
 * por correo — es explícitamente "dentro del sistema".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('mensaje');
            $table->json('roles')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
