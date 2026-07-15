<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarcarAcuerdosVencidos extends Command
{
    protected $signature = 'acuerdos:marcar-vencidos';

    protected $description = 'Marca como "Vencido" los acuerdos vigentes cuya fecha_fin ya pasó (reemplaza la escritura por request del listado).';

    public function handle(): int
    {
        // Única transición dependiente del tiempo: Vigente -> Vencido.
        // Las demás transiciones (aprobación, deshabilitación, extensión) ya
        // persisten el estado en sus propios endpoints vía actualizarEstado().
        $afectados = DB::table('acuerdos_comerciales')
            ->whereNull('deleted_at')
            ->where('habilitado', 1)
            ->where('validado', 'Aprobado')
            ->where('aprobado', 'Aprobado')
            ->where('estado', 'Vigente')
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->update([
                'estado'     => 'Vencido',
                'updated_at' => now(),
            ]);

        Log::info("acuerdos:marcar-vencidos — {$afectados} acuerdo(s) marcados como Vencido.");
        $this->info("Acuerdos marcados como Vencido: {$afectados}");

        return self::SUCCESS;
    }
}
