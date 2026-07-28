<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Compartido por DescuentosEspecialesController::cambiarAplicacion() y
 * ::cambiarAprobacion() — mismo campo 'nuevo_estado', mismas reglas
 * (ARQUITECTURA.md, A5).
 */
class NuevoEstadoDescuentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nuevo_estado' => 'required|in:Aprobado,Rechazado,Pendiente',
        ];
    }
}
