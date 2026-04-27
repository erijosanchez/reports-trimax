@extends('layouts.app')
@section('title', 'Historial de Km')

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-success mdi mdi-map-marker-distance"></i>Historial de Km
                                </h4>
                                <p class="mb-0 text-muted small">Control de kilometraje para justificar gastos de gasolina
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            {{-- Filtros --}}
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="py-3 card-body">
                            <form method="GET" action="{{ route('tracking.historial') }}" class="align-items-end row g-2">
                                <div class="col-md-3">
                                    <label class="mb-1 form-label small fw-bold">Motorizado</label>
                                    <select name="motorizado_id" class="form-select-sm form-select">
                                        <option value="">Todos</option>
                                        @foreach ($motorizados as $m)
                                            <option value="{{ $m->id }}"
                                                {{ $motorizadoId == $m->id ? 'selected' : '' }}>
                                                {{ $m->nombre }} — {{ $m->sede }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Desde</label>
                                    <input type="date" name="desde" class="form-control form-control-sm"
                                        value="{{ $desde }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Hasta</label>
                                    <input type="date" name="hasta" class="form-control form-control-sm"
                                        value="{{ $hasta }}">
                                </div>
                                <div class="col-md-2">
                                    <button class="w-100 btn btn-sm btn-primary">
                                        <i class="me-1 mdi mdi-magnify"></i>Filtrar
                                    </button>
                                </div>
                                @if ($motorizadoId || $desde || $hasta)
                                    <div class="col-md-1">
                                        <a href="{{ route('tracking.historial') }}"
                                            class="btn-outline-danger w-100 btn btn-sm">
                                            <i class="mdi mdi-close"></i>
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="p-0 card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Motorizado</th>
                                            <th>Sede</th>
                                            <th>Fecha</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Duración</th>
                                            <th>Km</th>
                                            <th>Entregas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rutas as $r)
                                            @php
                                                $comp = $r->entregas->where('estado', 'completado')->count();
                                                $tot = $r->entregas->count();
                                            @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $r->motorizado->nombre }}</td>
                                                <td><span class="bg-secondary badge">{{ $r->motorizado->sede }}</span></td>
                                                <td>{{ $r->fecha->format('d/m/Y') }}</td>
                                                <td class="small">
                                                    {{ $r->started_at?->setTimezone('America/Lima')->format('H:i') ?? '—' }}
                                                </td>
                                                <td class="small">
                                                    {{ $r->ended_at?->setTimezone('America/Lima')->format('H:i') ?? '—' }}
                                                </td>
                                                <td class="small">{{ $r->duracion }}</td>
                                                <td>
                                                    <span class="text-primary fw-bold fs-6">
                                                        {{ number_format($r->distance_km, 2) }} km
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-semibold">{{ $comp }}</span>
                                                    <span class="text-muted">/{{ $tot }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="py-5 text-muted text-center">
                                                    <i
                                                        class="d-block opacity-50 mb-2 mdi mdi-map-marker-distance mdi-36px"></i>
                                                    Sin registros para este período
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if ($rutas->hasPages())
                                <div class="px-3 py-2 border-top">
                                    {{ $rutas->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
