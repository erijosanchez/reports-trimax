@extends('layouts.app')

@section('title', 'Gestionar Dashboards')

@section('content')
<h1>Gestionar Dashboards</h1>
<a href="{{ route('admin.dashboards.create') }}">+ Crear Nuevo Dashboard</a>

<table border="1" style="margin-top:20px; width:100%">
    <thead>
        <tr>
            <th>Título</th>
            <th>Orden</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dashboards as $dashboard)
        <tr>
            <td>{{ $dashboard->title }}</td>
            <td>{{ $dashboard->order_position }}</td>
            <td>{{ $dashboard->is_active ? 'Sí' : 'No' }}</td>
            <td>
                <a href="{{ route('admin.dashboards.edit', $dashboard->id) }}">Editar</a>
                <form method="POST" action="{{ route('admin.dashboards.destroy', $dashboard->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection