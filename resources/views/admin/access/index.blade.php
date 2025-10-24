@extends('layouts.app')

@section('title', 'Gestionar Accesos')

@section('others')
<h1>Gestionar Accesos de Usuarios</h1>

@foreach($users as $user)
<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
    <h3>{{ $user->name }} ({{ $user->email }})</h3>
    <form method="POST" action="{{ route('admin.access.update', $user->id) }}">
        @csrf
        @foreach($dashboards as $dashboard)
            <label>
                <input 
                    type="checkbox" 
                    name="dashboards[]" 
                    value="{{ $dashboard->id }}"
                    {{ $user->dashboards->contains($dashboard->id) ? 'checked' : '' }}
                >
                {{ $dashboard->title }}
            </label><br>
        @endforeach
        <button type="submit">Guardar Permisos</button>
    </form>
</div>
@endforeach
@endsection