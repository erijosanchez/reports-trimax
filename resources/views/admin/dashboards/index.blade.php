@extends('layouts.app')

@section('title', 'Gestionar Dashboards')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <span>Admin</span> / Gestionar Dashboards
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div>
            <h1>Gestionar Dashboards</h1>
            <p>Administra todos los dashboards del sistema</p>
        </div>
        <a href="{{ route('admin.dashboards.create') }}" class="btn btn-primary">
            ➕ Crear Nuevo Dashboard
        </a>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 30px;">
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 32px; font-weight: 700; color: #0a2540; margin-bottom: 5px;">
                    {{ $dashboards->count() }}
                </div>
                <div style="color: #666; font-size: 14px;">Total Dashboards</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #2196F3, #1976D2); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                📊
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 32px; font-weight: 700; color: #0a2540; margin-bottom: 5px;">
                    {{ $dashboards->where('is_active', true)->count() }}
                </div>
                <div style="color: #666; font-size: 14px;">Dashboards Activos</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #4CAF50, #388E3C); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                ✓
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 32px; font-weight: 700; color: #0a2540; margin-bottom: 5px;">
                    {{ $dashboards->where('is_active', false)->count() }}
                </div>
                <div style="color: #666; font-size: 14px;">Dashboards Inactivos</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #FF9800, #F57C00); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                ⏸
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <div style="padding: 25px; border-bottom: 2px solid #f0f0f0;">
        <h2 style="color: #0a2540; font-size: 20px; font-weight: 600;">Lista de Dashboards</h2>
    </div>
    
    @if($dashboards->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>No hay dashboards creados</h3>
            <p>Comienza creando tu primer dashboard</p>
            <a href="{{ route('admin.dashboards.create') }}" class="btn btn-primary" style="margin-top: 20px;">
                ➕ Crear Primer Dashboard
            </a>
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Título</th>
                    <th style="width: 150px;">Orden</th>
                    <th style="width: 120px;">Estado</th>
                    <th style="width: 200px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dashboards as $index => $dashboard)
                <tr>
                    <td style="font-weight: 600; color: #0a2540;">{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: 600; color: #0a2540; margin-bottom: 3px;">
                            {{ $dashboard->title }}
                        </div>
                        <div style="font-size: 12px; color: #999;">
                            {{ Str::limit($dashboard->description, 50) }}
                        </div>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 5px; background: #E3F2FD; color: #2196F3; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            📍 Posición {{ $dashboard->order_position }}
                        </span>
                    </td>
                    <td>
                        @if($dashboard->is_active)
                            <span class="badge badge-success">✓ Activo</span>
                        @else
                            <span class="badge badge-danger">✗ Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="{{ route('admin.dashboards.edit', $dashboard->id) }}" class="btn btn-sm btn-secondary">
                                ✏️ Editar
                            </a>
                            <form method="POST" action="{{ route('admin.dashboards.destroy', $dashboard->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este dashboard?')">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection