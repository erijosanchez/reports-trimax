@extends('layouts.app')

@section('title', 'Rutas de Motorizados')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-routes me-2 text-primary"></i>Rutas</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('tracking.mapa') }}" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-map me-1"></i>Mapa en Vivo
            </a>
            @if(auth()->user()->puedeGestionarTracking())
            <a href="{{ route('tracking.rutas.create') }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-plus me-1"></i>Nueva Ruta
            </a>
            @endif
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="card card-body mb-3 py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Motorizado</label>
                <select name="motorizado_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($motorizados as $m)
                    <option value="{{ $m->id }}" {{ request('motorizado_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->nombre }} — {{ $m->sede }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="programada" {{ request('estado') == 'programada' ? 'selected' : '' }}>Programada</option>
                    <option value="en_ruta"    {{ request('estado') == 'en_ruta'    ? 'selected' : '' }}>En ruta</option>
                    <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="cancelada"  {{ request('estado') == 'cancelada'  ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
            </div>
            @if(request()->hasAny(['motorizado_id','estado','sede']))
            <div class="col-md-1">
                <a href="{{ route('tracking.rutas') }}" class="btn btn-sm btn-outline-secondary w-100">Limpiar</a>
            </div>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ruta</th>
                        <th>Motorizado</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Paradas</th>
                        <th>Inicio</th>
                        <th>Duración</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rutas as $ruta)
                    <tr>
                        <td>
                            <a href="{{ route('tracking.rutas.show', $ruta) }}" class="fw-semibold text-decoration-none">
                                {{ $ruta->nombre }}
                            </a>
                            <br><small class="text-muted">{{ $ruta->created_at->setTimezone('America/Lima')->format('d/m/Y') }}</small>
                        </td>
                        <td>{{ $ruta->motorizado->nombre }}</td>
                        <td>{{ $ruta->sede }}</td>
                        <td>
                            @php
                                $badges = [
                                    'programada' => ['bg-secondary', 'Programada'],
                                    'en_ruta'    => ['bg-info text-white', 'En ruta'],
                                    'completada' => ['bg-success', 'Completada'],
                                    'cancelada'  => ['bg-danger', 'Cancelada'],
                                ];
                                [$cls, $label] = $badges[$ruta->estado] ?? ['bg-secondary', $ruta->estado];
                            @endphp
                            <span class="badge {{ $cls }}">{{ $label }}</span>
                        </td>
                        <td>
                            @php $total = $ruta->paradas->count(); $comp = $ruta->paradasCompletadas(); @endphp
                            <span class="text-success fw-semibold">{{ $comp }}</span> / {{ $total }}
                            @if($ruta->paradasFallidas() > 0)
                                <small class="text-danger ms-1">({{ $ruta->paradasFallidas() }} fallidas)</small>
                            @endif
                        </td>
                        <td>{{ $ruta->inicio_at?->setTimezone('America/Lima')->format('d/m H:i') ?? '—' }}</td>
                        <td>
                            @if($ruta->duracionMinutos() !== null)
                                {{ $ruta->duracionMinutos() }} min
                            @elseif($ruta->estado === 'en_ruta' && $ruta->inicio_at)
                                <span class="text-info">{{ $ruta->inicio_at->diffInMinutes(now()) }} min (en curso)</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('tracking.rutas.show', $ruta) }}" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-map-search"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay rutas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rutas->hasPages())
        <div class="card-footer">
            {{ $rutas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
