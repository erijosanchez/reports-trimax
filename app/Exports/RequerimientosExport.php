<?php

namespace App\Exports;

use App\Models\RequerimientoPersonal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RequerimientosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Request $request) {}

    public function query()
    {
        $query = RequerimientoPersonal::with(['solicitante', 'responsableRh'])
            ->orderBy('fecha_solicitud', 'desc');

        if ($this->request->filled('estado')) {
            $query->where('estado', $this->request->estado);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'CÓDIGO', 'GERENCIA', 'PUESTO', 'SEDE', 'JEFE DIRECTO',
            'TIPO', 'CONDICIONES DE LA OFERTA', 'SOLICITUD',
            'RESPONSABLE RH', 'ESTADO', 'SLA', 'KPI', 'TIEMPO TOTAL',
        ];
    }

    public function map($req): array
    {
        $responsable = $req->responsableRh
            ? $req->responsableRh->name
            : ($req->responsable_rh_externo ?? '');

        return [
            $req->codigo,
            $req->gerencia,
            $req->puesto,
            $req->sede,
            $req->jefe_directo,
            $req->tipo,
            $req->condiciones_oferta ?? '',
            $req->fecha_solicitud->format('d/m/Y'),
            $responsable,
            $req->estado,
            $req->sla,
            $req->kpi,
            $req->tiempo_total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1a56db'],
                ],
            ],
        ];
    }
}