<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDescuentoEspecialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mismo ruleset que StoreDescuentoEspecialRequest excepto 'comentarios':
     * al crear es obligatorio, al editar no. Divergencia ya existente antes
     * de esta extracción (ARQUITECTURA.md, A5) — se preserva el
     * comportamiento actual, no se decide aquí si es correcta.
     */
    public function rules(): array
    {
        return [
            'numero_factura' => 'nullable|string',
            'numero_orden' => 'nullable|string',
            'sede' => 'required|string',
            'ruc' => 'required|string',
            'razon_social' => 'required|string',
            'consultor' => 'required|string',
            'ciudad' => 'required|string',
            'descuento_especial' => 'required|string',
            'tipo' => 'required|in:ANULACION,CORTESIA,DESCUENTO ADICIONAL,DESCUENTO TOTAL,APOYO COMERCIAL,OTROS',
            'marca' => 'required|string',
            'ar' => 'nullable|string',
            'disenos' => 'nullable|string',
            'material' => 'nullable|string',
            'comentarios' => 'nullable|string',
            'archivos.*' => 'nullable|file|max:10240',
        ];
    }
}
