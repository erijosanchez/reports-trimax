<?php

namespace App\Exports;

use App\Models\RequerimientoHistorial;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RequerimientosHojaSeguimiento implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $ids) {}

    public function title(): string
    {
        return 'Seguimiento';
    }

    public function query()
    {
        return RequerimientoHistorial::with(['requerimiento', 'usuario'])
            ->whereIn('requerimiento_id', $this->ids)
            ->orderBy('requerimiento_id')
            ->orderBy('created_at');
    }

    public function headings(): array
    {
        return [
            'CÓDIGO',
            'PUESTO',
            'SEDE',
            'TIPO EVENTO',
            'TÍTULO',
            'DESCRIPCIÓN',
            'ESTADO ANTERIOR',
            'ESTADO NUEVO',
            'USUARIO',
            'FECHA Y HORA',
        ];
    }

    public function map($historial): array
    {
        $req = $historial->requerimiento;

        return [
            $req ? $req->codigo : '',
            $req ? $req->puesto : '',
            $req ? $req->sede : '',
            $historial->tipo_label,
            $historial->titulo,
            $historial->descripcion ?? '',
            $historial->estado_anterior ?? '',
            $historial->estado_nuevo ?? '',
            $historial->usuario ? $historial->usuario->name : 'Sistema',
            $historial->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '065f46'],
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
