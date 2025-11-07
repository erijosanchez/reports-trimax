@extends('layouts.app')

@section('title', $dashboard->name)

@section('content')
    <div style="margin-bottom:1rem;">
        <a href="{{ route('dashboards.index') }}" style="color:#007bff;text-decoration:none;">
            ← Volver a Dashboards
        </a>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1.5rem;">
        <div>
            <h1 style="margin:0 0 0.5rem 0;">{{ $dashboard->name }}</h1>
            @if ($dashboard->description)
                <p style="color:#666;margin:0;">{{ $dashboard->description }}</p>
            @endif
        </div>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('dashboards.edit', $dashboard->id) }}"
                style="padding:0.5rem 1rem;background:#ffc107;color:#000;text-decoration:none;border-radius:4px;">
                Editar Dashboard
            </a>
        @endif
    </div>

    <div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;background:#f8f9fa;">
        <iframe src="{{ $powerbiLink }}" width="100%" height="600" frameborder="0" allowfullscreen
            style="display:block;">
        </iframe>
    </div>

    <div style="margin-top:1rem;font-size:0.85rem;color:#666;">
        <strong>Tip:</strong> Usa el modo pantalla completa para una mejor visualización
    </div>
@endsection
