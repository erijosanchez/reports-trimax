@extends('layouts.app')

@section('title', 'Editar Dashboard')

@section('content')
<h1>Editar Dashboard</h1>
<a href="{{ route('admin.dashboards.index') }}">← Volver</a>

<form method="POST" action="{{ route('admin.dashboards.update', $dashboard->id) }}">
    @csrf
    @method('PUT')
    <div>
        <label>Título:</label>
        <input type="text" name="title" value="{{ $dashboard->title }}" required>
    </div>
    <div>
        <label>Descripción:</label>
        <textarea name="description">{{ $dashboard->description }}</textarea>
    </div>
    <div>
        <label>URL Embed de Power BI:</label>
        <input type="url" name="embed_url" value="{{ $dashboard->embed_url }}" required style="width:100%">
    </div>
    <div>
        <label>Orden:</label>
        <input type="number" name="order_position" value="{{ $dashboard->order_position }}" required>
    </div>
    <div>
        <label>
            <input type="checkbox" name="is_active" value="1" {{ $dashboard->is_active ? 'checked' : '' }}>
            Activo
        </label>
    </div>
    <button type="submit">Actualizar Dashboard</button>
</form>
@endsection