@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('others')
    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / <a href="{{ route('admin.users.index') }}">Usuarios</a> / Crear
        </div>
        <h1>Crear Nuevo Usuario</h1>
    </div>

    <div class="content-area">
        <div class="card">
            <form method="POST" action="{{ route('admin.users.store') }}" style="display: flex; flex-direction: column; gap: 15px;">
                @csrf
                <div>
                    <label>Nombre:</label>
                    <input type="text" name="name" required
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="email" required
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Contraseña:</label>
                    <input type="password" name="password" required
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                </div>
                <div>
                    <label>Rol:</label>
                    <select name="is_admin" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                        <option value="0">Usuario</option>
                        <option value="1">Administrador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">💾 Crear Usuario</button>
            </form>
        </div>
    </div>
@endsection
