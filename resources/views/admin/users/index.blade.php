@extends('layouts.app')

@section('title', 'Usuarios')

@section('others')
    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / Usuarios
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1>Gestión de Usuarios</h1>
                <p>Administra los usuarios del sistema</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">➕ Nuevo Usuario</a>
        </div>
    </div>

    <div class="content-area">
        @if ($users->count())
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @foreach ($users as $user)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $user->name }}</h3>
                            <span class="badge {{ $user->is_admin ? 'badge-success' : 'badge-warning' }}">
                                {{ $user->is_admin ? 'Administrador' : 'Usuario' }}
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Creado:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                            <div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">✏️ Editar</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No hay usuarios registrados</h3>
            </div>
        @endif
    </div>
@endsection
