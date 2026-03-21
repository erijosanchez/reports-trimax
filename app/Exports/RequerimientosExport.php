<?php

namespace App\Exports;

use App\Models\RequerimientoPersonal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RequerimientosExport implements WithMultipleSheets
{
    public function __construct(private Request $request) {}

    public function sheets(): array
    {
        $ids = $this->getFilteredIds();

        return [
            new RequerimientosHojaPrincipal($ids),
            new RequerimientosHojaSeguimiento($ids),
        ];
    }

    private function getFilteredIds(): array
    {
        $query = RequerimientoPersonal::query();

        if ($this->request->filled('estado')) {
            $query->where('estado', $this->request->estado);
        }
        if ($this->request->filled('tipo')) {
            $query->where('tipo', $this->request->tipo);
        }
        if ($this->request->filled('sede')) {
            $query->where('sede', $this->request->sede);
        }
        if ($this->request->filled('search')) {
            $s = $this->request->search;
            $query->where(fn($q) => $q->where('codigo', 'like', "%$s%")->orWhere('puesto', 'like', "%$s%"));
        }

        return $query->orderBy('fecha_solicitud', 'desc')->pluck('id')->toArray();
    }
}
