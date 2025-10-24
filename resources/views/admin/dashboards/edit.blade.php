@extends('layouts.app')

@section('title', 'Editar Dashboard')

@section('others')

    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / <a href="{{ route('admin.dashboards.index') }}">Dashboards</a> / Editar
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <h1>Editar Dashboard</h1>
                <p>Modifica los datos del reporte existente de Power BI</p>
            </div>
            <a href="{{ route('admin.dashboards.index') }}" class="btn btn-outline">
                ← Volver
            </a>
        </div>
    </div>

    <div class="content-area">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Información del Dashboard</h2>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.dashboards.update', $dashboard->id) }}">
                    @csrf
                    @method('PUT')

                    <div style="display: flex; flex-direction: column; gap: 15px;">

                        <div>
                            <label for="title" style="font-weight: 600; color: #0a2540;">Título:</label>
                            <input type="text" id="title" name="title" value="{{ $dashboard->title }}" required
                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; margin-top: 5px;">
                        </div>

                        <div>
                            <label for="description" style="font-weight: 600; color: #0a2540;">Descripción:</label>
                            <textarea id="description" name="description" rows="3"
                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; margin-top: 5px; resize: vertical;">{{ $dashboard->description }}</textarea>
                        </div>

                        <div>
                            <label for="embed_url" style="font-weight: 600; color: #0a2540;">URL Embed de Power BI:</label>
                            <input type="url" id="embed_url" name="embed_url" value="{{ $dashboard->embed_url }}"
                                required placeholder="https://app.powerbi.com/view?r=..."
                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; margin-top: 5px;">
                        </div>

                        <div style="display: flex; gap: 20px; align-items: center;">
                            <div style="flex: 1;">
                                <label for="order_position" style="font-weight: 600; color: #0a2540;">Orden:</label>
                                <input type="number" id="order_position" name="order_position"
                                    value="{{ $dashboard->order_position }}" required
                                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; margin-top: 5px;">
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 25px;">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ $dashboard->is_active ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; accent-color: #2196F3;">
                                <label for="is_active" style="margin: 0;">Activo</label>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                            <a href="{{ route('admin.dashboards.index') }}" class="btn btn-outline">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Actualizar Dashboard
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
