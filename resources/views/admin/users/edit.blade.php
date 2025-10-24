@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('others')
    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / <a href="{{ route('admin.users.index') }}">Usuarios</a> / Editar
        </div>
        <h1>Editar Usuario</h1>
    </div>

    <div class="content-area">
        <div class="card">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}"
                style="display: flex; flex-direction: column; gap: 15px;">
                @csrf
                @method('PUT')
                <div>
                    <label>Nombre:</label>
                    <input type="text" name="name" value="{{ $user->name }}" required
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" value="{{ $user->email }}" required
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Nueva Contraseña (opcional):</label>
                    <input type="password" name="password" placeholder="Dejar en blanco para mantener la actual"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Rol:</label>
                    <select name="is_admin" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                        <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>Usuario</option>
                        <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Administrador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i></button>
            </form>
        </div>
    </div>
@endsection
