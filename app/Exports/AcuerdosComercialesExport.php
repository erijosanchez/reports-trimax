<?php

namespace App\Exports;

use App\Models\AcuerdoComercial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AcuerdosComercialesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private Request $request) {}

    public function title(): string
    {
        return 'Acuerdos Comerciales';
    }

    public function query()
    {
        $user  = Auth::user();
        $query = AcuerdoComercial::with(['creador', 'validador', 'aprobador', 'deshabilitador', 'extensor']);

        if ($user->isSede()) {
            $query->where('sede', $user->sede);
        }

        if ($this->request->filled('usuario') && !$user->isSede()) {
            $query->where('user_id', $this->request->usuario);
        }

        if ($this->request->filled('sede') && !$user->isSede()) {
            $query->where('sede', $this->request->sede);
        }

        if ($this->request->filled('estado')) {
            $query->where('estado', $this->request->estado);
        }

        if ($this->request->filled('buscar')) {
            $buscar = $this->request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_acuerdo', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('ruc', 'like', "%{$buscar}%");
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'N° ACUERDO',
            'SEDE',
            'RUC',
            'RAZÓN SOCIAL',
            'CONSULTOR',
            'CIUDAD',
            'ACUERDO COMERCIAL',
            'TIPO PROMOCIÓN',
            'MARCA',
            'AR',
            'DISEÑOS',
            'MATERIAL',
            'COMENTARIOS',
            'FECHA INICIO',
            'FECHA FIN',
            'ESTADO',
            'VALIDADO',
            'VALIDADO POR',
            'FECHA VALIDACIÓN',
            'APROBADO',
            'APROBADO POR',
            'FECHA APROBACIÓN',
            'HABILITADO',
            'MOTIVO DESHABILITACIÓN',
            'DESHABILITADO POR',
            'FECHA DESHABILITACIÓN',
            'MOTIVO REHABILITACIÓN',
            'REHABILITADO POR',
            'FECHA REHABILITACIÓN',
            'MOTIVO EXTENSIÓN',
            'EXTENDIDO POR',
            'FECHA EXTENSIÓN',
            'CREADO POR',
            'FECHA CREACIÓN',
        ];
    }

    public function map($acuerdo): array
    {
        return [
            $acuerdo->numero_acuerdo,
            $acuerdo->sede,
            $acuerdo->ruc,
            $acuerdo->razon_social,
            $acuerdo->consultor,
            $acuerdo->ciudad ?? '',
            $acuerdo->acuerdo_comercial ?? '',
            $acuerdo->tipo_promocion ?? '',
            $acuerdo->marca ?? '',
            $acuerdo->ar ?? '',
            $acuerdo->disenos ?? '',
            $acuerdo->material ?? '',
            $acuerdo->comentarios ?? '',
            $acuerdo->fecha_inicio ? $acuerdo->fecha_inicio->format('d/m/Y') : '',
            $acuerdo->fecha_fin    ? $acuerdo->fecha_fin->format('d/m/Y')    : '',
            $acuerdo->estado_calculado,
            $acuerdo->validado ?? '',
            $acuerdo->validador ? $acuerdo->validador->name : '',
            $acuerdo->validado_at ? $acuerdo->validado_at->format('d/m/Y H:i') : '',
            $acuerdo->aprobado ?? '',
            $acuerdo->aprobador ? $acuerdo->aprobador->name : '',
            $acuerdo->aprobado_at ? $acuerdo->aprobado_at->format('d/m/Y H:i') : '',
            $acuerdo->habilitado ? 'Sí' : 'No',
            $acuerdo->motivo_deshabilitacion ?? '',
            $acuerdo->deshabilitador ? $acuerdo->deshabilitador->name : '',
            $acuerdo->deshabilitado_at ? $acuerdo->deshabilitado_at->format('d/m/Y H:i') : '',
            $acuerdo->motivo_rehabilitacion ?? '',
            $acuerdo->rehabilitador ? $acuerdo->rehabilitador->name : '',
            $acuerdo->rehabilitado_at ? $acuerdo->rehabilitado_at->format('d/m/Y H:i') : '',
            $acuerdo->motivo_extension ?? '',
            $acuerdo->extensor ? $acuerdo->extensor->name : '',
            $acuerdo->extendido_at ? $acuerdo->extendido_at->format('d/m/Y H:i') : '',
            $acuerdo->creador ? $acuerdo->creador->name : '',
            $acuerdo->created_at ? $acuerdo->created_at->format('d/m/Y H:i') : '',
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
