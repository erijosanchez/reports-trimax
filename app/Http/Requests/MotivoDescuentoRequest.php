<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Compartido por DescuentosEspecialesController::deshabilitarDescuento() y
 * ::rehabilitarDescuento() — mismo campo 'motivo', mismas reglas (ARQUITECTURA.md, A5).
 */
class MotivoDescuentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => 'required|string|min:10',
        ];
    }
}
