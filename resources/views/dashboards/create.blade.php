@extends('layouts.app')

@section('title', 'Crear Dashboard')

@section('content')
    <h1>Crear Nuevo Dashboard</h1>

    <form method="POST" action="{{ route('dashboards.store') }}" style="max-width:600px;margin-top:2rem;">
        @csrf

        <div style="margin-bottom:1rem;">
            <label>Nombre:</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Descripción:</label>
            <textarea name="description" rows="3" style="width:100%;padding:0.5rem;margin-top:0.25rem;">{{ old('description') }}</textarea>
        </div>

        <div style="margin-bottom:1rem;">
            <label>Link de Power BI:</label>
            <input type="url" name="powerbi_link" value="{{ old('powerbi_link') }}" required
                style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <button type="submit"
            style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;border-radius:4px;">
            Crear Dashboard
        </button>

        <a href="{{ route('dashboards.index') }}" style="margin-left:1rem;color:#007bff;">Cancelar</a>
    </form>
@endsection
