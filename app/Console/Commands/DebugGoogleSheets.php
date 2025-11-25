<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DebugGoogleSheets extends Command
{
    protected $signature = 'debug:google-sheets';
    protected $description = 'Debug Google Sheets connection';

    public function handle()
    {
        $this->info('🔍 Debuggeando Google Sheets...');
        $this->newLine();

        // 1. Verificar variables de entorno
        $this->info('1️⃣ Verificando variables de entorno:');
        $spreadsheetId = config('google.spreadsheet_id');
        $apiKey = config('google.api_key');
        $sheetName = config('google.sheet_name');

        $this->line("   Spreadsheet ID: " . ($spreadsheetId ?: '❌ NO CONFIGURADO'));
        $this->line("   API Key: " . ($apiKey ? '✅ Configurada (' . substr($apiKey, 0, 10) . '...)' : '❌ NO CONFIGURADA'));
        $this->line("   Sheet Name: " . ($sheetName ?: '❌ NO CONFIGURADO'));
        $this->newLine();

        if (!$spreadsheetId || !$apiKey) {
            $this->error('❌ Faltan configuraciones. Verifica tu .env');
            return 1;
        }

        // 2. Construir URL
        $sheetName = $sheetName ?: 'Orden_x_Usuario';
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$sheetName}";
        
        $this->info('2️⃣ URL de la API:');
        $this->line("   {$url}");
        $this->newLine();

        // 3. Hacer request
        $this->info('3️⃣ Haciendo request...');
        
        try {
            $response = Http::timeout(30)->get($url, [
                'key' => $apiKey
            ]);

            $this->info("   Status Code: " . $response->status());
            $this->newLine();

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Respuesta exitosa!');
                $this->line('   Keys en respuesta: ' . implode(', ', array_keys($data)));
                
                if (isset($data['values'])) {
                    $this->info('   Total de filas: ' . count($data['values']));
                    
                    if (count($data['values']) > 0) {
                        $this->newLine();
                        $this->info('📋 Primera fila (headers):');
                        $this->line('   ' . implode(' | ', $data['values'][0]));
                        
                        if (count($data['values']) > 1) {
                            $this->newLine();
                            $this->info('📋 Segunda fila (datos):');
                            $this->line('   ' . implode(' | ', array_slice($data['values'][1], 0, 5)));
                        }
                    }
                } else {
                    $this->warn('⚠️  No se encontró el key "values" en la respuesta');
                }
                
                return 0;
                
            } else {
                $this->error('❌ Error en la respuesta:');
                $this->line('   Status: ' . $response->status());
                $this->line('   Body: ' . $response->body());
                
                $json = $response->json();
                if (isset($json['error'])) {
                    $this->newLine();
                    $this->error('📋 Detalle del error:');
                    $this->line('   Código: ' . ($json['error']['code'] ?? 'N/A'));
                    $this->line('   Mensaje: ' . ($json['error']['message'] ?? 'N/A'));
                    $this->line('   Status: ' . ($json['error']['status'] ?? 'N/A'));
                    
                    // Sugerencias según el error
                    $this->newLine();
                    $this->warn('💡 Posibles soluciones:');
                    
                    if ($response->status() === 403) {
                        $this->line('   - La API Key no tiene permisos');
                        $this->line('   - Verifica que Google Sheets API esté habilitada');
                        $this->line('   - El Sheet no es público o no está compartido');
                    } elseif ($response->status() === 404) {
                        $this->line('   - El SPREADSHEET_ID es incorrecto');
                        $this->line('   - El nombre del Sheet es incorrecto');
                    } elseif ($response->status() === 400) {
                        $this->line('   - La API Key es inválida');
                        $this->line('   - Revisa que no tenga espacios o caracteres extra');
                    }
                }
                
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Excepción: ' . $e->getMessage());
            $this->line('   Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}