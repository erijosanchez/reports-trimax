@extends('layouts.app')

@section('title', 'Órdenes de Entrega')

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
                                <i class="mdi mdi-package-variant me-2 text-primary"></i>Órdenes de Entrega
                            </h4>
                            <p class="mb-0 text-muted small">Registro manual de pedidos para asignar a rutas</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearOrden">
                            <i class="mdi mdi-plus me-1"></i>Nueva Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- Filtros --}}
        <div class="row mb-3">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small fw-bold mb-1">Sede</label>
                                <select id="filtro-sede" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    @foreach($sedes as $s)
                                        <option value="{{ $s }}" {{ request('sede') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small fw-bold mb-1">Estado</label>
                                <select id="filtro-estado" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_ruta">En ruta</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="fallido">Fallido</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small fw-bold mb-1">Buscar</label>
                                <input type="text" id="filtro-buscar" class="form-control form-control-sm"
                                       placeholder="Cliente o referencia…">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <button id="btn-filtrar" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="mdi mdi-magnify me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row mb-4">
            @php
                $stats = [
                    ['label'=>'Total',      'val'=>$ordenes->total(),                                           'color'=>'primary',  'icon'=>'mdi-package-variant'],
                    ['label'=>'Pendientes', 'val'=>$ordenes->getCollection()->where('estado','pendiente')->count(),'color'=>'secondary','icon'=>'mdi-clock-outline'],
                    ['label'=>'En ruta',    'val'=>$ordenes->getCollection()->where('estado','en_ruta')->count(),  'color'=>'warning',  'icon'=>'mdi-motorbike'],
                    ['label'=>'Entregados', 'val'=>$ordenes->getCollection()->where('estado','entregado')->count(),'color'=>'success',  'icon'=>'mdi-check-circle'],
                ];
            @endphp
            @foreach($stats as $s)
            <div class="mb-3 col-md-3 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">{{ $s['label'] }}</p>
                                <h2 class="mb-0 font-weight-bold text-{{ $s['color'] }}">{{ $s['val'] }}</h2>
                            </div>
                            <div class="bg-{{ $s['color'] }} icon-stat">
                                <i class="mdi {{ $s['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Tabla --}}
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Referencia</th>
                                        <th>Dirección</th>
                                        <th>Sede</th>
                                        <th>Estado</th>
                                        <th>Coords</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-ordenes">
                                @forelse($ordenes as $o)
                                    <tr>
                                        <td class="text-muted small">{{ $o->id }}</td>
                                        <td>
                                            <div class="fw-semibold small">{{ $o->cliente_nombre }}</div>
                                            @if($o->cliente_telefono)
                                                <div class="text-muted" style="font-size:11px">
                                                    <i class="mdi mdi-phone"></i> {{ $o->cliente_telefono }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $o->referencia ?? '—' }}</td>
                                        <td class="small" style="max-width:200px">{{ $o->direccion }}</td>
                                        <td><span class="badge bg-primary">{{ $o->sede }}</span></td>
                                        <td>
                                            @php
                                                $badgeMap = ['pendiente'=>'bg-primary','en_ruta'=>'bg-warning text-dark','entregado'=>'bg-success','fallido'=>'bg-danger'];
                                            @endphp
                                            <span class="badge {{ $badgeMap[$o->estado] ?? 'bg-primary' }}">
                                                {{ ucfirst(str_replace('_',' ',$o->estado)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($o->latitud && $o->longitud)
                                                <a href="https://maps.google.com/?q={{ $o->latitud }},{{ $o->longitud }}"
                                                   target="_blank" class="text-primary small">
                                                    <i class="mdi mdi-map-marker"></i> Ver
                                                </a>
                                            @else
                                                <span class="text-warning small">
                                                    <i class="mdi mdi-alert-outline"></i> Sin coords
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-warning btn-editar-orden"
                                                data-orden="{{ json_encode($o) }}" title="Editar">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar-orden"
                                                data-id="{{ $o->id }}" title="Eliminar">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="mdi mdi-package-variant mdi-36px d-block mb-2"></i>
                                            No hay órdenes registradas
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($ordenes->hasPages())
                            <div class="mt-3">{{ $ordenes->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="modalCrearOrden" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-crear-orden">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-package-variant me-1"></i>Nueva Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('tracking._form-orden', ['sedes' => $sedes])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="modalEditarOrden" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-editar-orden">
            @csrf
            <input type="hidden" id="edit-orden-id">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="mdi mdi-pencil me-1"></i>Editar Orden</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('tracking._form-orden', ['sedes' => $sedes, 'prefix' => 'edit-'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="mdi mdi-content-save me-1"></i>Actualizar
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

function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
    el.style.zIndex = 9999;
    el.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// Abrir modal editar
document.querySelectorAll('.btn-editar-orden').forEach(btn => {
    btn.addEventListener('click', () => {
        const o = JSON.parse(btn.dataset.orden);
        document.getElementById('edit-orden-id').value               = o.id;
        document.getElementById('edit-cliente_nombre').value         = o.cliente_nombre;
        document.getElementById('edit-cliente_telefono').value       = o.cliente_telefono ?? '';
        document.getElementById('edit-referencia').value             = o.referencia ?? '';
        document.getElementById('edit-direccion').value              = o.direccion;
        document.getElementById('edit-latitud').value                = o.latitud ?? '';
        document.getElementById('edit-longitud').value               = o.longitud ?? '';
        document.getElementById('edit-sede').value                   = o.sede;
        document.getElementById('edit-estado').value                 = o.estado;
        document.getElementById('edit-notas').value                  = o.notas ?? '';
        new bootstrap.Modal(document.getElementById('modalEditarOrden')).show();
    });
});

// Crear
document.getElementById('form-crear-orden').addEventListener('submit', async e => {
    e.preventDefault();
    const fd  = new FormData(e.target);
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const res = await fetch('/tracking/ordenes', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    btn.disabled = false;
    if (res.ok) { toast('Orden creada'); setTimeout(() => location.reload(), 800); }
    else { const d = await res.json(); toast(Object.values(d.errors ?? {}).flat()[0] || 'Error', 'danger'); }
});

// Actualizar
document.getElementById('form-editar-orden').addEventListener('submit', async e => {
    e.preventDefault();
    const id  = document.getElementById('edit-orden-id').value;
    const fd  = new FormData(e.target);
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;
    const res = await fetch(`/tracking/ordenes/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json',
                   'Content-Type': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    btn.disabled = false;
    if (res.ok) { toast('Orden actualizada'); setTimeout(() => location.reload(), 800); }
    else { const d = await res.json(); toast(Object.values(d.errors ?? {}).flat()[0] || 'Error', 'danger'); }
});

// Eliminar
document.querySelectorAll('.btn-eliminar-orden').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('¿Eliminar esta orden?')) return;
        const res = await fetch(`/tracking/ordenes/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) { toast('Eliminada'); btn.closest('tr').remove(); }
        else toast('Error al eliminar', 'danger');
    });
});

// Filtrar (recarga con query params)
document.getElementById('btn-filtrar').addEventListener('click', () => {
    const sede   = document.getElementById('filtro-sede').value;
    const estado = document.getElementById('filtro-estado').value;
    const buscar = document.getElementById('filtro-buscar').value;
    const params = new URLSearchParams();
    if (sede)   params.set('sede',   sede);
    if (estado) params.set('estado', estado);
    if (buscar) params.set('buscar', buscar);
    window.location.search = params.toString();
});
</script>
@endpush
