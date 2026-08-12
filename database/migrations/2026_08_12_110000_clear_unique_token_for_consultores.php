<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los consultores dejan de tener link/QR propio (2026-08-12): a partir de
 * ahora solo se les asigna a sedes y su calificación sale de la pregunta de
 * consultor en la encuesta maestra (Survey.consultor_id/consultor_rating).
 * A pedido explícito, se revocan también los tokens ya emitidos — sus links
 * antiguos dejan de responder.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users_marketing')
            ->where('role', 'consultor')
            ->update(['unique_token' => null]);
    }

    public function down(): void
    {
        // No reversible de forma significativa: los tokens originales no se
        // conservan. Si hiciera falta, se regeneran uno a uno desde el panel
        // de Marketing (ya no aplica para consultores, ver UsersMarketing::boot()).
    }
};
