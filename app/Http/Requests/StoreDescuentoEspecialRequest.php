<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDescuentoEspecialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'tipo' => 'required|in:ANULACION,CORTESIA,DESCUENTO ADICIONAL,DESCUENTO TOTAL,OTROS',
            'marca' => 'required|string',
            'ar' => 'nullable|string',
            'disenos' => 'nullable|string',
            'material' => 'nullable|string',
            'comentarios' => 'required|string|min:1',
            'archivos.*' => 'nullable|file|max:10240',
        ];
    }
}
