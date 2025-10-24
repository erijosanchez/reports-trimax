@extends('layouts.app')

@section('title', 'Crear Dashboard')

@section('content')
<h1>Crear Nuevo Dashboard</h1>
<a href="{{ route('admin.dashboards.index') }}">← Volver</a>

<form method="POST" action="{{ route('admin.dashboards.store') }}">
    @csrf
    <div>
        <label>Título:</label>
        <input type="text" name="title" required>
    </div>
    <div>
        <label>Descripción:</label>
        <textarea name="description"></textarea>
    </div>
    <div>
        <label>URL Embed de Power BI:</label>
        <input type="url" name="embed_url" required style="width:100%">
    </div>
    <div>
        <label>Orden:</label>
        <input type="number" name="order_position" value="0" required>
    </div>
    <div>
        <label>
            <input type="checkbox" name="is_active" value="1" checked>
            Activo
        </label>
    </div>
    <button type="submit">Crear Dashboard</button>
</form>

@endsection