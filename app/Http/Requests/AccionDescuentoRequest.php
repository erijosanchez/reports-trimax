<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Compartido por DescuentosEspecialesController::aplicarDescuento() y
 * ::aprobarDescuento() — mismo campo 'accion', mismas reglas (ARQUITECTURA.md, A5).
 */
class AccionDescuentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accion' => 'required|in:Aprobado,Rechazado',
        ];
    }
}
