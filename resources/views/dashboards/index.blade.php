@extends('layouts.app')

@section('title', 'Dashboards')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <h1>Dashboards</h1>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('dashboards.create') }}" style="padding:0.5rem 1rem;background:#28a745;color:white;text-decoration:none;border-radius:4px;">
            + Crear Dashboard
        </a>
    @endif
</div>

@if($dashboards->isEmpty())
    <div style="padding:2rem;text-align:center;background:#f8f9fa;border-radius:4px;">
        <p style="color:#666;">No hay dashboards disponibles.</p>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('dashboards.create') }}" style="color:#007bff;text-decoration:underline;">
                Crear el primer dashboard
            </a>
        @endif
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
        @foreach($dashboards as $dashboard)
            <div style="border:1px solid #ddd;padding:1.5rem;border-radius:4px;background:white;">
                <div style="display:flex;justify-content:space-between;align-items:start;">
                    <h3 style="margin:0 0 0.5rem 0;">{{ $dashboard->name }}</h3>
                    @if(!$dashboard->is_active)
                        <span style="padding:0.25rem 0.5rem;background:#dc3545;color:white;border-radius:3px;font-size:0.75rem;">
                            Inactivo
                        </span>
                    @endif
                </div>
                
                <p style="color:#666;margin:0.5rem 0 1rem 0;min-height:3rem;">
                    {{ $dashboard->description ?? 'Sin descripción' }}
                </p>
                
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <a href="{{ route('dashboards.show', $dashboard->id) }}" 
                       style="display:inline-block;padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;flex:1;text-align:center;">
                        Ver Dashboard
                    </a>
                    
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboards.edit', $dashboard->id) }}" 
                           style="display:inline-block;padding:0.5rem 1rem;background:#ffc107;color:#000;text-decoration:none;border-radius:4px;">
                            Editar
                        </a>
                    @endif
                </div>
                
                @if(auth()->user()->isAdmin())
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #eee;font-size:0.85rem;color:#666;">
                        <strong>Usuarios asignados:</strong> {{ $dashboard->users->count() }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection