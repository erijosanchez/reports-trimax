<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;

class TestGoogleSheets extends Command
{
    protected $signature = 'test:google-sheets';
    protected $description = 'Probar conexión con Google Sheets';

    public function handle(GoogleSheetsService $googleSheets)
    {
        $this->info('🔍 Probando conexión con Google Sheets...');
        $this->newLine();

        try {
            // Probar obtener datos parseados
            $ordenes = $googleSheets->getSheetDataParsed('Orden_x_Usuario');

            if (empty($ordenes)) {
                $this->error('❌ No se obtuvieron datos');
                return 1;
            }

            $this->info('✅ Conexión exitosa!');
            $this->info('📊 Total de órdenes: ' . count($ordenes));
            $this->newLine();

            // Mostrar primera orden
            if (isset($ordenes[0])) {
                $this->info('📋 Primera orden:');
                $primera = $ordenes[0];
                foreach (array_slice($primera, 0, 5) as $key => $value) {
                    $this->line("   {$key}: {$value}");
                }
            }

            $this->newLine();

            // Estadísticas
            $stats = $googleSheets->getStats($ordenes);
            $this->info('📈 Estadísticas:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total', $stats['total']],
                    ['En Tránsito', $stats['en_transito']],
                    ['En Sede', $stats['en_sede']],
                    ['Facturados', $stats['facturados']],
                    ['Disponibles para facturar', $stats['disponibles_facturar']],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
