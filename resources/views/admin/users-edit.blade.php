@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
    <h1>Editar Usuario: {{ $user->name }}</h1>

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}" style="max-width:600px;margin-top:2rem;">
        @csrf
        @method('PUT')

        <div style="margin-bottom:1rem;">
            <label>Nombre:</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Rol:</label>
            <select name="role" required style="width:100%;padding:0.5rem;margin-top:0.25rem;">
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1rem;">
            <label>
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                Usuario Activo
            </label>
        </div>

        <button type="submit"
            style="padding:0.75rem 2rem;background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px;">
            Actualizar Usuario
        </button>

        <a href="{{ route('admin.users') }}" style="margin-left:1rem;color:#007bff;">Cancelar</a>
    </form>
@endsection
