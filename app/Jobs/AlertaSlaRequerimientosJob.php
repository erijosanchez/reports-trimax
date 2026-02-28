<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\RequerimientoPersonal;
use App\Models\User;
use App\Models\RequerimientoHistorial;
use App\Notifications\RequerimientoAlertaSla;
use Illuminate\Support\Facades\Notification;

class AlertaSlaRequerimientosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Emails que siempre reciben las alertas
    const EMAILS_NOTIFICACION = [
        'sergio@trimax.pe',
        'christian@trimax.pe',
        'luz@trimax.pe',
        'estefani@trimax.pe',
    ];

    public function handle(): void
    {
        // Requerimientos En Proceso con >= 45 días
        $requerimientos = RequerimientoPersonal::slaVencidos()
            ->with('solicitante')
            ->get();

        foreach ($requerimientos as $req) {
            $diasTranscurridos = $req->kpi;

            // Destinatarios: solicitante + emails fijos
            $destinatarios = User::where(function ($q) use ($req) {
                $q->whereIn('email', self::EMAILS_NOTIFICACION)
                    ->orWhere('id', $req->solicitante_id);
            })->get();

            Notification::send($destinatarios, new RequerimientoAlertaSla($req, $diasTranscurridos));

            // Registrar en historial (usuario_id = 1 = sistema, ajusta según tu seed)
            RequerimientoHistorial::create([
                'requerimiento_id' => $req->id,
                'user_id'          => 1,
                'tipo_evento'      => 'alerta_sla',
                'titulo'           => "Alerta SLA: {$diasTranscurridos} días transcurridos",
                'descripcion'      => "Alerta automática enviada. El requerimiento lleva {$diasTranscurridos} días sin cerrarse (SLA: 45 días).",
            ]);
        }
    }
}
