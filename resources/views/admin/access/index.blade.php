@extends('layouts.app')

@section('title', 'Gestionar Accesos')

@section('others')

    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / Gestionar Permisos
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <h1>Gestionar Permisos</h1>
                <p>Administra todos los permisos sobre los reportes y accesos de usuarios</p>
            </div>
            <a href="{{ route('admin.dashboards.create') }}" class="btn btn-primary" style="margin-top: 20px;">
                <i data-lucide="user-round-plus"></i> Crear Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="content-area">

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Gestionar Accesos de Usuarios</h2>
            </div>
            <div class="card-body">

                @foreach ($users as $user)
                    <div class="card" style="margin-bottom: 20px;">
                        <div class="card-header">
                            <div>
                                <h3 style="margin: 0; font-size: 18px; color:#0a2540;">{{ $user->name }}</h3>
                                <p style="margin: 0; color: #888; font-size: 14px;">{{ $user->email }}</p>
                            </div>
                            <span class="badge badge-info">ID: {{ $user->id }}</span>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.access.update', $user->id) }}">
                                @csrf

                                <div
                                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px;">
                                    @foreach ($dashboards as $dashboard)
                                        <label
                                            style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #333; background: #f9fafc; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;">
                                            <input type="checkbox" name="dashboards[]" value="{{ $dashboard->id }}"
                                                {{ $user->dashboards->contains($dashboard->id) ? 'checked' : '' }}
                                                style="width: 16px; height: 16px; accent-color: #2196F3;">
                                            {{ $dashboard->title }}
                                        </label>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.25rem 6.5rem">
                                    <i data-lucide="save"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @if ($users->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">👤</div>
                        <h3>No hay usuarios registrados</h3>
                        <p>Agrega nuevos usuarios para asignarles permisos de acceso.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
