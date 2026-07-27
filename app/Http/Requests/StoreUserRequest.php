<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()->uncompromised()],
            'role' => 'required|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 10 caracteres, con letras y números',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.uncompromised' => 'Esta contraseña apareció en una filtración de datos conocida. Elige otra.',
            'role.required' => 'Debe seleccionar un rol',
        ];
    }
}