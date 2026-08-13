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

class MarketingConsultoresExport implements FromArray, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $consultores) {}

    public function title(): string
    {
        return 'Calificación consultores';
    }

    public function array(): array
    {
        return $this->consultores;
    }

    public function headings(): array
    {
        return [
            'CONSULTOR',
            'SEDE(S)',
            'TOTAL ENCUESTAS',
            'PROMEDIO',
            'CSAT (TOP-BOX)',
        ];
    }

    public function map($fila): array
    {
        return [
            $fila['name'],
            $fila['sedes'],
            // (string): evita que PhpSpreadsheet deje la celda vacía cuando el
            // valor es 0 (compara con `!=` contra null, y 0 == null es true en PHP).
            (string) $fila['total_surveys'],
            $fila['total_surveys'] > 0 ? number_format($fila['promedio'], 2) : 'Sin encuestas',
            $fila['total_surveys'] > 0 ? number_format($fila['csat'], 1) . '%' : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
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
        ];
    }
}
