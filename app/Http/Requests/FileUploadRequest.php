<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:pdf,xlsx,xls,csv',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debe seleccionar un archivo',
            'file.max' => 'El archivo no puede superar 10MB',
            'file.mimes' => 'Solo se permiten archivos PDF, Excel y CSV',
        ];
    }
}
