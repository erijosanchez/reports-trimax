@extends('layouts.app')

@section('title', 'Gestión de Motorizados')

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
                                <i class="mdi mdi-motorbike me-2 text-primary"></i>Gestión de Motorizados
                            </h4>
                            <p class="mb-0 text-muted small">Alta, baja y vinculación con dispositivos Traccar</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                            <i class="mdi mdi-plus me-1"></i>Nuevo Motorizado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- Cards stats --}}
        <div class="row mb-4">
            @php
                $total   = $motorizados->whereNull('deleted_at')->count();
                $activos = $motorizados->where('estado','activo')->whereNull('deleted_at')->count();
                $sinGps  = $motorizados->whereNull('traccar_device_id')->whereNull('deleted_at')->count();
            @endphp
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Total</p>
                                <h2 class="mb-0 font-weight-bold text-primary">{{ $total }}</h2>
                            </div>
                            <div class="bg-primary icon-stat"><i class="mdi mdi-motorbike"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Activos</p>
                                <h2 class="mb-0 font-weight-bold text-success">{{ $activos }}</h2>
                            </div>
                            <div class="bg-success icon-stat"><i class="mdi mdi-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Sin GPS</p>
                                <h2 class="mb-0 font-weight-bold text-warning">{{ $sinGps }}</h2>
                            </div>
                            <div class="bg-warning icon-stat"><i class="mdi mdi-alert"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla motorizados --}}
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Sede</th>
                                        <th>Teléfono</th>
                                        <th>Device Traccar</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($motorizados as $m)
                                    <tr class="{{ $m->trashed() ? 'table-secondary' : '' }}">
                                        <td class="text-muted small">{{ $m->id }}</td>
                                        <td class="fw-semibold">{{ $m->nombre }}</td>
                                        <td><span class="badge bg-primary">{{ $m->sede }}</span></td>
                                        <td class="small">{{ $m->telefono ?? '—' }}</td>
                                        <td>
                                            @if($m->traccar_device_id)
                                                <code class="small bg-light px-1 rounded">ID: {{ $m->traccar_device_id }}</code>
                                            @else
                                                <span class="text-warning small">
                                                    <i class="mdi mdi-alert-outline"></i> Sin asignar
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m->trashed())
                                                <span class="badge bg-dark">Eliminado</span>
                                            @elseif($m->estado === 'activo')
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @unless($m->trashed())
                                            <button class="btn btn-sm btn-outline-warning btn-editar"
                                                data-id="{{ $m->id }}"
                                                data-nombre="{{ $m->nombre }}"
                                                data-sede="{{ $m->sede }}"
                                                data-telefono="{{ $m->telefono ?? '' }}"
                                                data-device="{{ $m->traccar_device_id ?? '' }}"
                                                data-estado="{{ $m->estado }}"
                                                title="Editar">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar"
                                                data-id="{{ $m->id }}" title="Eliminar">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="mdi mdi-motorbike mdi-36px d-block mb-2 text-muted"></i>
                                            No hay motorizados registrados
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dispositivos Traccar --}}
        @if(count($dispositivosTraccar))
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-satellite-variant me-1 text-info"></i>
                            Dispositivos registrados en Traccar
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Identificador único</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($dispositivosTraccar as $d)
                                    <tr>
                                        <td><code>{{ $d['id'] }}</code></td>
                                        <td>{{ $d['name'] }}</td>
                                        <td class="text-muted small">{{ $d['uniqueId'] ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ ($d['status'] ?? '') === 'online' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $d['status'] ?? 'offline' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-crear">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-motorbike me-1"></i>Nuevo Motorizado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('tracking._form-motorizado', ['sedes' => $sedes])
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
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-editar">
            @csrf
            <input type="hidden" id="edit-id">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="mdi mdi-pencil me-1"></i>Editar Motorizado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('tracking._form-motorizado', ['sedes' => $sedes, 'prefix' => 'edit-'])
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
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit-id').value                    = btn.dataset.id;
        document.getElementById('edit-nombre').value                = btn.dataset.nombre;
        document.getElementById('edit-sede').value                  = btn.dataset.sede;
        document.getElementById('edit-telefono').value              = btn.dataset.telefono;
        document.getElementById('edit-traccar_device_id').value     = btn.dataset.device;
        document.getElementById('edit-estado').value                = btn.dataset.estado;
        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    });
});

// Crear
document.getElementById('form-crear').addEventListener('submit', async e => {
    e.preventDefault();
    const fd  = new FormData(e.target);
    const res = await fetch('/tracking/motorizados', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    if (res.ok) { toast('Motorizado creado'); setTimeout(() => location.reload(), 800); }
    else        { const d = await res.json(); toast(d.message || 'Error al crear', 'danger'); }
});

// Actualizar
document.getElementById('form-editar').addEventListener('submit', async e => {
    e.preventDefault();
    const id  = document.getElementById('edit-id').value;
    const fd  = new FormData(e.target);
    const res = await fetch(`/tracking/motorizados/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json',
                   'Content-Type': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    if (res.ok) { toast('Motorizado actualizado'); setTimeout(() => location.reload(), 800); }
    else        { const d = await res.json(); toast(d.message || 'Error al actualizar', 'danger'); }
});

// Eliminar
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('¿Eliminar este motorizado?')) return;
        const res = await fetch(`/tracking/motorizados/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) { toast('Eliminado'); btn.closest('tr').remove(); }
        else        toast('Error al eliminar', 'danger');
    });
});
</script>
@endpush
