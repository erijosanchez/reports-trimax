@extends('layouts.app')

@section('title', 'Rutas de Entrega')

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
                                <i class="mdi mdi-route me-2 text-primary"></i>Rutas de Entrega
                            </h4>
                            <p class="mb-0 text-muted small">Gestión de rutas diarias y paradas por motorizado</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRuta">
                            <i class="mdi mdi-plus me-1"></i>Nueva Ruta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- Tabla rutas --}}
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Motorizado</th>
                                        <th>Sede</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($rutas as $r)
                                    <tr>
                                        <td class="text-muted small">{{ $r->id }}</td>
                                        <td class="fw-semibold">{{ $r->motorizado->nombre }}</td>
                                        <td><span class="badge bg-secondary">{{ $r->motorizado->sede }}</span></td>
                                        <td>{{ $r->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            @if($r->estado === 'completado')
                                                <span class="badge bg-success">Completado</span>
                                            @elseif($r->estado === 'en_ruta')
                                                <span class="badge bg-warning text-dark">En ruta</span>
                                            @else
                                                <span class="badge bg-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('tracking.ruta-detalle', $r->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-eye me-1"></i>Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="mdi mdi-route mdi-36px d-block mb-2"></i>
                                            No hay rutas registradas
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($rutas->hasPages())
                        <div class="mt-3">
                            {{ $rutas->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal crear ruta --}}
<div class="modal fade" id="modalCrearRuta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-ruta">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-route me-1"></i>Nueva Ruta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Motorizado <span class="text-danger">*</span></label>
                            <select name="motorizado_id" class="form-select" required>
                                <option value="">Seleccionar motorizado</option>
                                @foreach($motorizados as $m)
                                    <option value="{{ $m->id }}">{{ $m->nombre }} ({{ $m->sede }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                   value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas</label>
                            <textarea name="notas" class="form-control" rows="2"
                                      placeholder="Observaciones de la ruta…"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">
                            <i class="mdi mdi-map-marker-multiple me-1 text-primary"></i>Paradas
                        </h6>
                        <button type="button" id="btn-agregar-parada" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-plus me-1"></i>Agregar parada
                        </button>
                    </div>
                    <div id="contenedor-paradas"></div>

                    <div class="alert alert-info small py-2 mt-2 mb-0">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Ingresa el <strong>ID de la orden</strong> de la tabla <code>ordenes_tracking</code>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Crear Ruta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
let paradaIdx = 0;

document.getElementById('btn-agregar-parada').addEventListener('click', () => {
    paradaIdx++;
    const div = document.createElement('div');
    div.className = 'card border-0 bg-light mb-2';
    div.innerHTML = `
        <div class="card-body py-2 px-3 d-flex gap-2 align-items-center">
            <span class="badge bg-primary">${paradaIdx}</span>
            <input type="number" name="paradas[${paradaIdx}][orden_id]"
                   class="form-control form-control-sm"
                   placeholder="ID de la orden" required>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="this.closest('.card').remove()">
                <i class="mdi mdi-close"></i>
            </button>
        </div>`;
    document.getElementById('contenedor-paradas').appendChild(div);
});

document.getElementById('form-ruta').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);

    const paradasMap = {};
    for (const [k, v] of fd.entries()) {
        const m = k.match(/^paradas\[(\d+)\]\[(\w+)\]$/);
        if (m) { if (!paradasMap[m[1]]) paradasMap[m[1]] = {}; paradasMap[m[1]][m[2]] = v; }
    }

    const body = {
        motorizado_id: fd.get('motorizado_id'),
        fecha:         fd.get('fecha'),
        notas:         fd.get('notas'),
        paradas:       Object.values(paradasMap),
    };

    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';

    const res = await fetch('/tracking/rutas', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });

    if (res.ok) {
        const data = await res.json();
        window.location.href = `/tracking/rutas/${data.ruta_id}`;
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>Crear Ruta';
        alert('Error al crear la ruta. Verifica los datos.');
    }
});
</script>
@endpush
