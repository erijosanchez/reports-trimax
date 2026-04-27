@extends('layouts.app')
@section('title', 'Entregas')

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-primary mdi mdi-package-variant"></i>Entregas
                                </h4>
                                <p class="mb-0 text-muted small">Registro de órdenes asignadas a motorizados</p>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                <i class="me-1 mdi mdi-plus"></i>Nueva Entrega
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            {{-- Filtros --}}
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="py-3 card-body">
                            <form method="GET" action="{{ route('tracking.entregas') }}" class="align-items-end row g-2">
                                <div class="col-md-3">
                                    <label class="mb-1 form-label small fw-bold">Motorizado</label>
                                    <select name="motorizado_id" class="form-select-sm form-select">
                                        <option value="">Todos</option>
                                        @foreach ($motorizados as $m)
                                            <option value="{{ $m->id }}"
                                                {{ request('motorizado_id') == $m->id ? 'selected' : '' }}>
                                                {{ $m->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Sede</label>
                                    <select name="sede" class="form-select-sm form-select">
                                        <option value="">Todas</option>
                                        @foreach ($sedes as $s)
                                            <option value="{{ $s }}" {{ request('sede') === $s ? 'selected' : '' }}>
                                                {{ $s }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Estado</label>
                                    <select name="estado" class="form-select-sm form-select">
                                        <option value="">Todos</option>
                                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>
                                            Pendiente</option>
                                        <option value="completado" {{ request('estado') === 'completado' ? 'selected' : '' }}>
                                            Completado</option>
                                        <option value="fallido" {{ request('estado') === 'fallido' ? 'selected' : '' }}>Fallido
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="w-100 btn btn-sm btn-primary">
                                        <i class="me-1 mdi mdi-magnify"></i>Filtrar
                                    </button>
                                </div>
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
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Dirección</th>
                                            <th>Motorizado</th>
                                            <th>Sede</th>
                                            <th>Estado</th>
                                            <th>Entregado</th>
                                            <th>Coords entrega</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($entregas as $e)
                                            @php
                                                $badges = [
                                                    'pendiente' => 'bg-secondary',
                                                    'completado' => 'bg-success',
                                                    'fallido' => 'bg-danger',
                                                ];
                                            @endphp
                                            <tr>
                                                <td class="text-muted small">{{ $e->id }}</td>
                                                <td>
                                                    <div class="fw-semibold small">{{ $e->cliente_nombre }}</div>
                                                    @if ($e->cliente_telefono)
                                                        <div class="text-muted" style="font-size:11px">
                                                            <i class="mdi mdi-phone"></i> {{ $e->cliente_telefono }}
                                                        </div>
                                                    @endif
                                                    @if ($e->referencia)
                                                        <div class="text-muted" style="font-size:11px">
                                                            Ref: {{ $e->referencia }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="small" style="max-width:200px">{{ $e->direccion }}</td>
                                                <td class="small fw-semibold">{{ $e->motorizado->nombre }}</td>
                                                <td><span class="bg-primary badge">{{ $e->sede }}</span></td>
                                                <td>
                                                    <span class="badge {{ $badges[$e->estado] ?? 'bg-secondary' }}">
                                                        {{ ucfirst($e->estado) }}
                                                    </span>
                                                </td>
                                                <td class="small">
                                                    {{ $e->entregado_en?->setTimezone('America/Lima')->format('d/m H:i') ?? '—' }}
                                                </td>
                                                <td>
                                                    @if ($e->entrega_latitud && $e->entrega_longitud)
                                                        <a href="https://maps.google.com/?q={{ $e->entrega_latitud }},{{ $e->entrega_longitud }}"
                                                            target="_blank" class="btn-outline-success btn btn-xs">
                                                            <i class="mdi mdi-map-marker"></i> Ver
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="py-5 text-muted text-center">
                                                    <i class="d-block opacity-50 mb-2 mdi mdi-package-variant mdi-36px"></i>
                                                    Sin entregas registradas
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if ($entregas->hasPages())
                                <div class="px-3 py-2 border-top">
                                    {{ $entregas->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Crear --}}
    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="form-crear">
                @csrf
                <div class="modal-content">
                    <div class="bg-primary text-white modal-header">
                        <h5 class="modal-title"><i class="me-1 mdi mdi-package-variant"></i>Nueva Entrega</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Motorizado <span class="text-danger">*</span></label>
                                <select name="motorizado_id" id="sel-motorizado" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    @foreach ($motorizados as $m)
                                        <option value="{{ $m->id }}" data-sede="{{ $m->sede }}">
                                            {{ $m->nombre }} — {{ $m->sede }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ruta (ID) <span class="text-danger">*</span></label>
                                <input type="number" name="ruta_id" id="ruta-id" class="form-control"
                                    placeholder="ID de la ruta GPS" required>
                                <div class="form-text">La ruta debe estar creada primero desde la app.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono cliente</label>
                                <input type="text" name="cliente_telefono" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Referencia</label>
                                <input type="text" name="referencia" class="form-control" placeholder="Ej: ORD-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
                                <select name="sede" id="sel-sede" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    @foreach ($sedes as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Latitud</label>
                                <input type="number" name="latitud" class="form-control" step="0.0000001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Longitud</label>
                                <input type="number" name="longitud" class="form-control" step="0.0000001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Orden secuencia <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="orden_secuencia" class="form-control" value="1"
                                    min="1" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="me-1 mdi-content-save mdi"></i>Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        function toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
            el.style.zIndex = 9999;
            el.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        // Auto-llenar sede al seleccionar motorizado
        document.getElementById('sel-motorizado').addEventListener('change', function() {
            const sede = this.options[this.selectedIndex]?.dataset.sede;
            if (sede) document.getElementById('sel-sede').value = sede;
        });

        // Crear
        document.getElementById('form-crear').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const btn = e.target.querySelector('[type=submit]');
            btn.disabled = true;
            const res = await fetch('/tracking/entregas', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(fd)),
            });
            btn.disabled = false;
            if (res.ok) {
                toast('Entrega creada ✓');
                setTimeout(() => location.reload(), 800);
            } else {
                const d = await res.json();
                toast(Object.values(d.errors ?? {}).flat()[0] || d.message || 'Error', 'danger');
            }
        });
    </script>
@endpush
