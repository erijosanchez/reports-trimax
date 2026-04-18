@extends('layouts.app')

@section('title', 'Resumen Diario — Tracking')

@push('styles')
<style>
    .progress { height: 8px; border-radius: 4px; }
    .ruta-row { transition: background .15s; }
    .ruta-row:hover { background: #f8f9fa; }
    .sede-badge { font-size: 11px; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="mdi mdi-view-dashboard me-2 text-primary"></i>Resumen Diario
                            </h4>
                            <p class="mb-0 text-muted small">Estado de entregas del día por motorizado y sede</p>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" id="sel-fecha" class="form-control form-control-sm"
                                   value="{{ $fecha }}" max="{{ today()->toDateString() }}"
                                   style="width:160px">
                            <button id="btn-fecha" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            <span class="badge bg-primary fs-6">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- KPI Cards --}}
        <div class="row mb-4">
            <div class="mb-3 col-md-3 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Rutas del día</p>
                                <h2 class="mb-0 font-weight-bold text-primary">{{ $kpi['total_rutas'] }}</h2>
                            </div>
                            <div class="bg-primary icon-stat"><i class="mdi mdi-route"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-3 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Entregas totales</p>
                                <h2 class="mb-0 font-weight-bold text-info">{{ $kpi['total_paradas'] }}</h2>
                            </div>
                            <div class="bg-info icon-stat"><i class="mdi mdi-package-variant"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-3 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Completadas</p>
                                <h2 class="mb-0 font-weight-bold text-success">{{ $kpi['completadas'] }}</h2>
                            </div>
                            <div class="bg-success icon-stat"><i class="mdi mdi-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-3 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Fallidas</p>
                                <h2 class="mb-0 font-weight-bold text-danger">{{ $kpi['fallidas'] }}</h2>
                            </div>
                            <div class="bg-danger icon-stat"><i class="mdi mdi-close-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barra de eficiencia global --}}
        @if($kpi['total_paradas'] > 0)
        @php $pctGlobal = round($kpi['completadas'] / $kpi['total_paradas'] * 100); @endphp
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="shadow-sm border-0 card">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">Eficiencia global del día</span>
                            <span class="fw-bold {{ $pctGlobal >= 80 ? 'text-success' : ($pctGlobal >= 50 ? 'text-warning' : 'text-danger') }}">
                                {{ $pctGlobal }}%
                            </span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $pctGlobal >= 80 ? 'bg-success' : ($pctGlobal >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                 style="width:{{ $pctGlobal }}%"></div>
                        </div>
                        <div class="d-flex gap-3 mt-2" style="font-size:12px">
                            <span class="text-success"><i class="mdi mdi-check"></i> {{ $kpi['completadas'] }} entregadas</span>
                            <span class="text-danger"><i class="mdi mdi-close"></i> {{ $kpi['fallidas'] }} fallidas</span>
                            <span class="text-secondary"><i class="mdi mdi-clock"></i> {{ $kpi['pendientes'] }} pendientes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">

            {{-- Tabla de rutas del día --}}
            <div class="mb-4 col-lg-8 col-md-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="mdi mdi-motorbike me-1 text-primary"></i>Motorizados del día</h6>
                        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Motorizado</th>
                                        <th>Sede</th>
                                        <th>Progreso</th>
                                        <th>Estado</th>
                                        <th class="text-center">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($rutas as $r)
                                @php
                                    $tot  = $r->paradas->count();
                                    $comp = $r->paradas->where('estado','completado')->count();
                                    $fall = $r->paradas->where('estado','fallido')->count();
                                    $pend = $r->paradas->whereIn('estado',['pendiente','en_camino'])->count();
                                    $pct  = $tot > 0 ? round($comp / $tot * 100) : 0;
                                @endphp
                                <tr class="ruta-row">
                                    <td>
                                        <div class="fw-semibold small">{{ $r->motorizado->nombre }}</div>
                                        @if($r->motorizado->telefono)
                                        <div class="text-muted" style="font-size:11px">
                                            <i class="mdi mdi-phone"></i> {{ $r->motorizado->telefono }}
                                        </div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary sede-badge">{{ $r->motorizado->sede }}</span></td>
                                    <td style="min-width:140px">
                                        <div class="d-flex justify-content-between mb-1" style="font-size:11px">
                                            <span>{{ $comp }}/{{ $tot }}</span>
                                            <span class="{{ $pct >= 80 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-muted') }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width:{{ $tot > 0 ? round($comp/$tot*100) : 0 }}%"></div>
                                            <div class="progress-bar bg-danger"  style="width:{{ $tot > 0 ? round($fall/$tot*100) : 0 }}%"></div>
                                        </div>
                                        <div class="d-flex gap-2 mt-1" style="font-size:10px">
                                            <span class="text-success">✓ {{ $comp }}</span>
                                            <span class="text-danger">✗ {{ $fall }}</span>
                                            <span class="text-muted">⏳ {{ $pend }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($r->estado === 'completado')
                                            <span class="badge bg-success">Completada</span>
                                        @elseif($r->estado === 'en_ruta')
                                            <span class="badge bg-warning text-dark">En ruta</span>
                                        @else
                                            <span class="badge bg-primary">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('tracking.ruta-detalle', $r->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="mdi mdi-route mdi-36px d-block mb-2"></i>
                                        Sin rutas para esta fecha
                                    </td>
                                </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel lateral: por sede + fallidas --}}
            <div class="mb-4 col-lg-4 col-md-12">

                {{-- Por sede --}}
                <div class="shadow-sm border-0 card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="mdi mdi-store me-1 text-info"></i>Por sede</h6>
                    </div>
                    <div class="card-body p-3">
                        @forelse($porSede as $sede => $data)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small">{{ $sede }}</span>
                                <span class="small text-muted">{{ $data['comp'] }}/{{ $data['total'] }}</span>
                            </div>
                            <div class="progress mb-1">
                                @php $ps = $data['total'] > 0 ? round($data['comp']/$data['total']*100) : 0; @endphp
                                <div class="progress-bar {{ $ps >= 80 ? 'bg-success' : ($ps >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width:{{ $ps }}%"></div>
                            </div>
                            <div class="d-flex gap-2" style="font-size:11px">
                                <span class="text-success">✓ {{ $data['comp'] }}</span>
                                <span class="text-danger">✗ {{ $data['fall'] }}</span>
                                <span class="text-muted">⏳ {{ $data['pend'] }}</span>
                                <span class="ms-auto text-primary fw-bold">{{ $ps }}%</span>
                            </div>
                        </div>
                        @empty
                            <p class="text-muted small text-center py-2">Sin datos</p>
                        @endforelse
                    </div>
                </div>

                {{-- Entregas fallidas del día --}}
                @if($fallidas->count())
                <div class="shadow-sm border-0 card">
                    <div class="card-header">
                        <h6 class="mb-0 text-danger">
                            <i class="mdi mdi-alert-circle me-1"></i>Fallidas hoy
                            <span class="badge bg-danger ms-1">{{ $fallidas->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0" style="max-height:300px;overflow-y:auto">
                        @foreach($fallidas as $p)
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-semibold small">{{ $p->orden->cliente_nombre }}</div>
                            <div class="text-muted" style="font-size:11px">
                                {{ $p->ruta->motorizado->nombre }} · {{ $p->ruta->motorizado->sede }}
                            </div>
                            @if($p->notas)
                            <div class="text-danger" style="font-size:11px">
                                <i class="mdi mdi-note-text"></i> {{ $p->notas }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-fecha').addEventListener('click', () => {
    const f = document.getElementById('sel-fecha').value;
    if (f) window.location.href = `/tracking/resumen?fecha=${f}`;
});
document.getElementById('sel-fecha').addEventListener('keydown', e => {
    if (e.key === 'Enter') document.getElementById('btn-fecha').click();
});
</script>
@endpush
