@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <h1>Bienvenido, {{ auth()->user()->name }}</h1>

    <div style="margin-top:2rem;">
        <h2>Tus Dashboards</h2>

        @if ($dashboards->isEmpty())
            <p>No tienes dashboards asignados.</p>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem;margin-top:1rem;">
                @foreach ($dashboards as $dashboard)
                    <div style="border:1px solid #ddd;padding:1rem;border-radius:4px;">
                        <h3>{{ $dashboard->name }}</h3>
                        <p>{{ $dashboard->description }}</p>
                        <a href="{{ route('dashboards.show', $dashboard->id) }}"
                            style="display:inline-block;margin-top:0.5rem;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;">
                            Ver Dashboard
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
