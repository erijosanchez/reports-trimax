@extends('layouts.app')

@section('title', 'Dashboards')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
        <h1>Dashboards</h1>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('dashboards.create') }}"
                style="padding:0.5rem 1rem;background:#28a745;color:white;text-decoration:none;border-radius:4px;">
                Crear Dashboard
            </a>
        @endif
    </div>

    @if ($dashboards->isEmpty())
        <p>No hay dashboards disponibles.</p>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
            @foreach ($dashboards as $dashboard)
                <div style="border:1px solid #ddd;padding:1.5rem;border-radius:4px;">
                    <h3>{{ $dashboard->name }}</h3>
                    <p style="color:#666;margin:0.5rem 0;">{{ $dashboard->description }}</p>
                    <a href="{{ route('dashboards.show', $dashboard->id) }}"
                        style="display:inline-block;margin-top:1rem;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;">
                        Ver Dashboard
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
