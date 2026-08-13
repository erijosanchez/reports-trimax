<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MarketingResumenSedesExport implements FromArray, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $sedeStats, private array $totales) {}

    public function title(): string
    {
        return 'Resumen por sede';
    }

    public function array(): array
    {
        return array_merge($this->sedeStats, [array_merge($this->totales, ['name' => 'TOTAL'])]);
    }

    public function headings(): array
    {
        return [
            'SEDE',
            'ENCUESTAS ESTA SEMANA',
            'META SEMANAL',
            'CUMPLIMIENTO SEMANAL',
            'ENCUESTAS ESTE MES',
            'META MENSUAL (ESTIMADA)',
            'CUMPLIMIENTO MENSUAL (ESTIMADO)',
            'PROMEDIO CONSOLIDADO',
            'CSAT (TOP-BOX)',
        ];
    }

    public function map($fila): array
    {
        $esTotal = $fila['name'] === 'TOTAL';

        return [
            $fila['name'],
            // (string): PhpSpreadsheet compara el valor contra null con `!=` (no
            // estricto) al volcar el array a celdas — 0 == null es true en PHP,
            // así que un entero 0 literal se queda sin escribir en la celda.
            (string) $fila['obtenidas_semana'],
            $fila['meta_semanal'] ?? 'Sin meta',
            $fila['cumplimiento_pct'] !== null ? $fila['cumplimiento_pct'] . '%' : '—',
            (string) $fila['obtenidas_mes'],
            $fila['meta_mensual_estimada'] ?? 'Sin meta',
            $fila['cumplimiento_mensual_pct'] !== null ? $fila['cumplimiento_mensual_pct'] . '%' : '—',
            $esTotal ? '—' : number_format($fila['promedio'], 2),
            $esTotal ? '—' : number_format($fila['csat'], 1) . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $totalRow = count($this->sedeStats) + 2; // +1 encabezado, +1 porque las filas de datos empiezan en la 2

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366f1'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
            $totalRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF'],
                ],
            ],
        ];
    }
}
