@extends('layouts.app')

@section('title', 'Crear Dashboard')

@section('content')
    <div style="max-width:800px;margin:0 auto;">
        <h1>Crear Nuevo Dashboard</h1>

        <form method="POST" action="{{ route('dashboards.store') }}" style="margin-top:2rem;">
            @csrf

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                    Nombre del Dashboard *
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ej: Dashboard de Ventas"
                    style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                    Descripción
                </label>
                <textarea name="description" rows="3" placeholder="Descripción del dashboard"
                    style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">{{ old('description') }}</textarea>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                    Link de Power BI *
                </label>
                <input type="url" name="powerbi_link" value="{{ old('powerbi_link') }}" required
                    placeholder="https://app.powerbi.com/view?r=..."
                    style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">
                <small style="color:#666;display:block;margin-top:0.25rem;">
                    Pega aquí el link completo del informe de Power BI
                </small>
            </div>

            <div style="display:flex;gap:1rem;margin-top:2rem;">
                <button type="submit"
                    style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;border-radius:4px;font-size:1rem;">
                    Crear Dashboard
                </button>

                <a href="{{ route('dashboards.index') }}"
                    style="padding:0.75rem 2rem;background:#6c757d;color:white;text-decoration:none;border-radius:4px;display:inline-block;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
