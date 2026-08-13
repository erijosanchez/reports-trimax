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
                                            <option value="{{ $s }}"
                                                {{ request('sede') === $s ? 'selected' : '' }}>
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
                                        <option value="completado"
                                            {{ request('estado') === 'completado' ? 'selected' : '' }}>
                                            Completado</option>
                                        <option value="fallido" {{ request('estado') === 'fallido' ? 'selected' : '' }}>
                                            Fallido
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
                                    <option value="">Seleccionar motorizado</option>
                                    @foreach ($motorizados as $m)
                                        <option value="{{ $m->id }}" data-sede="{{ $m->sede }}">
                                            {{ $m->nombre }} — {{ $m->sede }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ruta del día <span
                                        class="text-danger">*</span></label>
                                <select name="ruta_id" id="sel-ruta" class="form-select" required disabled>
                                    <option value="">Primero selecciona un motorizado</option>
                                </select>
                                <div id="msg-sin-ruta" class="text-warning form-text d-none">
                                    <i class="mdi-alert-outline mdi"></i>
                                    Este motorizado no tiene ruta activa hoy.
                                    Dile que presione "Iniciar" en la app primero.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_nombre" id="inp-cliente-nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono cliente</label>
                                <input type="text" name="cliente_telefono" class="form-control">
                            </div>
                            <div class="col-md-6" style="position: relative">
                                <label class="form-label fw-semibold">
                                    Referencia
                                    <span id="badge-orden" class="bg-secondary ms-2 badge">Sin verificar</span>
                                </label>
                                <input type="text" name="referencia" id="inp-referencia" class="form-control"
                                    autocomplete="off" placeholder="Buscar por N° de orden o cliente...">
                                <div id="referencia-resultados" class="list-group d-none"
                                    style="position:absolute; left:0; right:0; z-index:1050; max-height:220px; overflow-y:auto;"></div>
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
                                <label class="form-label fw-semibold">
                                    Dirección <span class="text-danger">*</span>
                                    <span id="badge-coords" class="bg-secondary ms-2 badge">Sin coords</span>
                                </label>
                                <input type="text" name="direccion" id="inp-direccion" class="form-control" required
                                    placeholder="Ej: Av. Javier Prado 123, San Isidro">
                                <div class="form-text">Las coordenadas se buscan automáticamente al escribir</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Latitud</label>
                                <input type="number" name="latitud" id="inp-latitud" class="form-control"
                                    step="0.0000001" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Longitud</label>
                                <input type="number" name="longitud" id="inp-longitud" class="form-control"
                                    step="0.0000001" readonly>
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

        // Auto-llenar sede y cargar rutas al seleccionar motorizado
        document.getElementById('sel-motorizado').addEventListener('change', async function() {
            const motId = this.value;
            const sede = this.options[this.selectedIndex]?.dataset.sede;
            const selRuta = document.getElementById('sel-ruta');
            const msgSinRuta = document.getElementById('msg-sin-ruta');

            if (sede) document.getElementById('sel-sede').value = sede;
            limpiarOrdenSeleccionada();

            if (!motId) {
                selRuta.innerHTML = '<option value="">Primero selecciona un motorizado</option>';
                selRuta.disabled = true;
                return;
            }

            selRuta.innerHTML = '<option value="">Cargando rutas...</option>';
            selRuta.disabled = true;
            msgSinRuta.classList.add('d-none');

            try {
                const res = await fetch(`/tracking/motorizados/${motId}/rutas-hoy`);
                const data = await res.json();

                if (!data.length) {
                    selRuta.innerHTML = '<option value="">Sin rutas activas hoy</option>';
                    selRuta.disabled = true;
                    msgSinRuta.classList.remove('d-none');
                    return;
                }

                selRuta.innerHTML = data.map(r =>
                    `<option value="${r.id}">${r.label}</option>`
                ).join('');
                selRuta.disabled = false;
                msgSinRuta.classList.add('d-none');

            } catch (e) {
                selRuta.innerHTML = '<option value="">Error al cargar rutas</option>';
                selRuta.disabled = true;
            }
        });

        // Buscador de órdenes reales (ordenes_historico) por sede
        function limpiarOrdenSeleccionada() {
            const badge = document.getElementById('badge-orden');
            document.getElementById('inp-referencia').value = '';
            document.getElementById('referencia-resultados').classList.add('d-none');
            badge.className = 'badge bg-secondary ms-2';
            badge.textContent = 'Sin verificar';
        }
        document.getElementById('sel-sede').addEventListener('change', limpiarOrdenSeleccionada);

        let ordenTimer = null;
        document.getElementById('inp-referencia').addEventListener('input', function() {
            clearTimeout(ordenTimer);
            const term = this.value.trim();
            const badge = document.getElementById('badge-orden');
            const resultados = document.getElementById('referencia-resultados');

            if (term.length < 2) {
                resultados.classList.add('d-none');
                badge.className = 'badge bg-secondary ms-2';
                badge.textContent = 'Sin verificar';
                return;
            }

            const sede = document.getElementById('sel-sede').value;
            if (!sede) {
                resultados.classList.add('d-none');
                badge.className = 'badge bg-warning ms-2';
                badge.textContent = 'Selecciona motorizado primero';
                return;
            }

            ordenTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `/tracking/ordenes/buscar?sede=${encodeURIComponent(sede)}&q=${encodeURIComponent(term)}`
                    );
                    const data = await res.json();

                    resultados.innerHTML = '';

                    if (!data.length) {
                        const vacio = document.createElement('div');
                        vacio.className = 'list-group-item text-muted';
                        vacio.textContent = 'Sin coincidencias';
                        resultados.appendChild(vacio);
                        resultados.classList.remove('d-none');
                        badge.className = 'badge bg-warning ms-2';
                        badge.textContent = 'Sin coincidencias';
                        return;
                    }

                    data.forEach(o => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action btn-seleccionar-orden';
                        btn.dataset.numero = o.numero_orden ?? '';
                        btn.dataset.cliente = o.cliente ?? '';

                        const numero = document.createElement('strong');
                        numero.textContent = o.numero_orden ?? '';
                        btn.appendChild(numero);
                        btn.appendChild(document.createTextNode(' — ' + (o.cliente || '—')));

                        resultados.appendChild(btn);
                    });
                    resultados.classList.remove('d-none');
                } catch (_) {
                    resultados.classList.add('d-none');
                }
            }, 350);
        });

        document.getElementById('referencia-resultados').addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-seleccionar-orden');
            if (!btn) return;

            document.getElementById('inp-referencia').value = btn.dataset.numero;
            document.getElementById('inp-cliente-nombre').value = btn.dataset.cliente;
            this.classList.add('d-none');

            const badge = document.getElementById('badge-orden');
            badge.className = 'badge bg-success ms-2';
            badge.textContent = '✓ Orden verificada';
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#inp-referencia') && !e.target.closest('#referencia-resultados')) {
                document.getElementById('referencia-resultados').classList.add('d-none');
            }
        });

        // Geocodificar dirección con Nominatim (gratis, sin API key)
        let geocodeTimer = null;
        document.getElementById('inp-direccion')?.addEventListener('input', function() {
            clearTimeout(geocodeTimer);
            const dir = this.value.trim();
            if (dir.length < 10) return;

            geocodeTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(dir + ', Peru')}&format=json&limit=1`, {
                            headers: {
                                'Accept-Language': 'es'
                            }
                        }
                    );
                    const data = await res.json();
                    if (data.length) {
                        document.getElementById('inp-latitud').value = parseFloat(data[0].lat).toFixed(
                            7);
                        document.getElementById('inp-longitud').value = parseFloat(data[0].lon).toFixed(
                            7);
                        document.getElementById('badge-coords').className = 'badge bg-success ms-2';
                        document.getElementById('badge-coords').textContent = '✓ Coords encontradas';
                    } else {
                        document.getElementById('badge-coords').className = 'badge bg-warning ms-2';
                        document.getElementById('badge-coords').textContent =
                            'Sin coords — ingresa manual';
                    }
                } catch (_) {}
            }, 800); // espera 800ms después de dejar de escribir
        });

        // Crear entrega
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
