<?php

namespace App\Exports;

use App\Models\RequerimientoPersonal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RequerimientosHojaPrincipal implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $ids) {}

    public function title(): string
    {
        return 'Requerimientos';
    }

    public function query()
    {
        return RequerimientoPersonal::with(['solicitante', 'responsableRh'])
            ->whereIn('id', $this->ids)
            ->orderBy('fecha_solicitud', 'desc');
    }

    public function headings(): array
    {
        return [
            'CÓDIGO',
            'GERENCIA',
            'PUESTO',
            'SEDE',
            'JEFE DIRECTO',
            'TIPO',
            'CONDICIONES DE LA OFERTA',
            'COMENTARIOS',
            'SOLICITANTE',
            'FECHA SOLICITUD',
            'RESPONSABLE RH',
            'ESTADO',
            'SLA (días)',
            'KPI (días reales)',
            'SEMÁFORO',
            'FECHA CIERRE',
            'TIEMPO TOTAL',
        ];
    }

    public function map($req): array
    {
        $responsable = $req->responsableRh
            ? $req->responsableRh->name
            : ($req->responsable_rh_externo ?? '');

        $semaforo = match ($req->semaforo) {
            'optimo'    => 'Óptimo (≤45 días)',
            'riesgo'    => 'Riesgo (46-60 días)',
            'critico'   => 'Crítico (>60 días)',
            'pendiente' => 'Pendiente',
            default     => $req->semaforo,
        };

        return [
            $req->codigo,
            $req->gerencia,
            $req->puesto,
            $req->sede,
            $req->jefe_directo,
            $req->tipo,
            $req->condiciones_oferta ?? '',
            $req->comentarios ?? '',
            $req->solicitante ? $req->solicitante->name : '',
            $req->fecha_solicitud->format('d/m/Y'),
            $responsable,
            $req->estado,
            $req->sla,
            $req->estado !== 'Pendiente' ? $req->kpi : '',
            $semaforo,
            $req->fecha_cierre ? $req->fecha_cierre->format('d/m/Y') : '',
            $req->estado !== 'Pendiente' ? $req->total : '',
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
