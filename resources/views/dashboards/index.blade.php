@extends('layouts.app')

@section('title', 'Mis Dashboards')

@section('sidebar-menu')
    @foreach ($dashboards as $dashboard)
        <a href="{{ route('dashboard.show', $dashboard->id) }}"
            class="menu-item {{ request()->route('id') == $dashboard->id ? 'active' : '' }}">
            <span class="menu-icon">📊</span>
            <span>{{ $dashboard->title }}</span>
        </a>
    @endforeach
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <span>Dashboard</span> / Mis Dashboards
        </div>
        <h1>Mis Dashboards</h1>
        <p>Accede a todos tus dashboards asignados</p>
    </div>

    @if ($dashboards->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No tienes dashboards disponibles</h3>
                <p>Aún no tienes acceso a ningún dashboard. Contacta con tu administrador para obtener acceso.</p>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
            @foreach ($dashboards as $dashboard)
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="border-bottom: none; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div
                                style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #2196F3, #1976D2); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                                <i data-lucide="layout-dashboard"></i>
                            </div>
                            <div>
                                <h3 class="card-title" style="margin: 0; font-size: 18px;">{{ $dashboard->title }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p style="margin-bottom: 20px; color: #666; font-size: 14px; line-height: 1.6;">
                            {{ $dashboard->description ?: 'Sin descripción disponible' }}
                        </p>
                        <a href="{{ route('dashboard.show', $dashboard->id) }}" class="btn btn-primary"
                            style="width: 100%; text-align: center;">
                            Ver Dashboard
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sección de estadísticas rápidas -->
        <div style="margin-top: 40px;">
            <h2 style="color: #0a2540; font-size: 24px; margin-bottom: 20px;">Resumen Rápido</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="card" style="text-align: center; margin-bottom: 0;">
                    <div style="font-size: 40px; color: #2196F3; margin-bottom: 10px;">{{ $dashboards->count() }}</div>
                    <div style="color: #666; font-size: 14px;">Dashboards Activos</div>
                </div>
                <div class="card" style="text-align: center; margin-bottom: 0;">
                    <div style="font-size: 40px; color: #4CAF50; margin-bottom: 10px;">✓</div>
                    <div style="color: #666; font-size: 14px;">Acceso Completo</div>
                </div>
                <div class="card" style="text-align: center; margin-bottom: 0;">
                    <div style="font-size: 40px; color: #FF9800; margin-bottom: 10px;">🔔</div>
                    <div style="color: #666; font-size: 14px;"> Notificaciones</div>
                </div>
            </div>
        </div>
    @endif
@endsection
