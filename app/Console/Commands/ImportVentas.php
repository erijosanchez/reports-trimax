<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportVentas extends Command
{
    protected $signature = 'import:ventas {file}';
    protected $description = 'Importar ventas desde Excel';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error('Archivo no encontrado');
            return;
        }

        $this->info('Importando ventas...');

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Saltar la primera fila (encabezados)
        array_shift($rows);

        $imported = 0;
        foreach ($rows as $row) {
            DB::table('ventas')->insert([
                'fecha' => $this->parseDate($row[1]),
                'tipo_documento' => $row[2],
                'nro_documento' => $row[3],
                'nro_orden_fabricacion' => $row[4],
                'ruc_dni' => $row[5],
                'razon_social' => $row[6],
                'tipo_cliente' => $row[7],
                'motorizado' => $row[8],
                'sede' => $row[9],
                'zona' => $row[10],
                'cod_producto' => $row[11],
                'descripcion' => $row[12],
                'importe' => $row[13],
                'igv' => $row[14],
                'importe_global' => $row[15],
                'cantidad' => $row[16],
                'anio' => $row[17],
                'mes' => $row[18],
                'tallado' => $row[19],
                'marca' => $row[20],
                'diseno' => $row[21],
                'material' => $row[22],
                'tipo_fotocromatico' => $row[23],
                'color' => $row[24],
                'tipo_articulo' => $row[25],
                'tipo_articulo2' => $row[26],
                'tipo_tributo' => $row[27],
                'doc_referencia_nc' => $row[28],
                'motivo_nc' => $row[29],
                'observacion_nc' => $row[30]
            ]);

            $imported++;
        }

        $this->info("✅ Se importaron {$imported} registros");
    }

    private function parseDate($date)
    {
        // Ajustar según el formato de tu Excel
        return date('Y-m-d', strtotime($date));
    }
}
