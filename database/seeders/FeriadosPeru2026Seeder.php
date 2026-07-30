<?php

namespace Database\Seeders;

use App\Models\Feriado;
use Illuminate\Database\Seeder;

class FeriadosPeru2026Seeder extends Seeder
{
    public function run(): void
    {
        $feriados = [
            ['2026-01-01', 'Año Nuevo'],
            ['2026-04-02', 'Jueves Santo'],
            ['2026-04-03', 'Viernes Santo'],
            ['2026-05-01', 'Día del Trabajo'],
            ['2026-06-07', 'Batalla de Arica y Día de la Bandera'],
            ['2026-06-29', 'San Pedro y San Pablo'],
            ['2026-07-23', 'Día de la Fuerza Aérea del Perú'],
            ['2026-07-28', 'Fiestas Patrias'],
            ['2026-07-29', 'Fiestas Patrias'],
            ['2026-08-06', 'Batalla de Junín'],
            ['2026-08-30', 'Santa Rosa de Lima'],
            ['2026-10-08', 'Combate de Angamos'],
            ['2026-11-01', 'Día de Todos los Santos'],
            ['2026-12-08', 'Inmaculada Concepción'],
            ['2026-12-09', 'Batalla de Ayacucho'],
            ['2026-12-25', 'Navidad'],
        ];

        foreach ($feriados as [$fecha, $motivo]) {
            Feriado::updateOrCreate(
                ['fecha' => $fecha],
                ['motivo' => $motivo, 'tipo' => 'nacional', 'fuente' => 'manual']
            );
        }
    }
}
