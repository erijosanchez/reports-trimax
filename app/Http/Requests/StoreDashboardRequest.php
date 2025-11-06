<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'powerbi_link' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del dashboard es obligatorio',
            'powerbi_link.required' => 'El link de Power BI es obligatorio',
            'powerbi_link.url' => 'Debe ser una URL válida',
        ];
    }
}