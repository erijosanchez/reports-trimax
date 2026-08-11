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

class TopClientesExport implements FromArray, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private array $sedes,
        private array $periodos,
        private bool $mesCerrado
    ) {}

    public function title(): string
    {
        return 'Top Clientes';
    }

    public function array(): array
    {
        $filas = [];
        foreach ($this->sedes as $sede) {
            foreach ($sede['clientes'] as $cliente) {
                $filas[] = array_merge(['sede' => $sede['sede']], $cliente);
            }
        }
        return $filas;
    }

    public function headings(): array
    {
        return [
            'SEDE',
            'RUC',
            'RAZÓN SOCIAL',
            $this->periodos['m3'],
            $this->periodos['m2'],
            $this->periodos['m1'],
            'PROMEDIO',
            $this->periodos['actual'] . ($this->mesCerrado ? '' : ' (PROYECCIÓN)'),
            'VARIACIÓN %',
            'SEMÁFORO',
        ];
    }

    public function map($fila): array
    {
        return [
            $fila['sede'],
            $fila['ruc'],
            $fila['razon'],
            $fila['venta_m3'],
            $fila['venta_m2'],
            $fila['venta_m1'],
            $fila['prom'],
            $fila['venta_actual'],
            $fila['variacion_pct'] . '%',
            strtoupper($fila['semaforo']),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e3a8a'],
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
