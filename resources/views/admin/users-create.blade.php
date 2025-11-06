@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
    <h1>Crear Nuevo Usuario</h1>

    <form method="POST" action="{{ route('admin.users.store') }}" style="max-width:600px;margin-top:2rem;">
        @csrf

        <div style="margin-bottom:1rem;">
            <label>Nombre:</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Contraseña:</label>
            <input type="password" name="password" required style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Confirmar Contraseña:</label>
            <input type="password" name="password_confirmation" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Rol:</label>
            <select name="role" required style="width:100%;padding:0.5rem;margin-top:0.25rem;">
                <option value="">Seleccionar...</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
            style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;border-radius:4px;">
            Crear Usuario
        </button>

        <a href="{{ route('admin.users') }}" style="margin-left:1rem;color:#007bff;">Cancelar</a>
    </form>
@endsection
