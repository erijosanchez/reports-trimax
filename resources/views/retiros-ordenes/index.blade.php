@extends('layouts.app')

@section('title', 'Retiros de Órdenes')

@push('styles')
<style>
/* ── Retiros de Órdenes ───────────────────────────────── */
.retiros-table thead th {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    vertical-align: middle;
    background: var(--bs-tertiary-bg, #f8f9fa);
}
.retiros-table tbody td { vertical-align: middle; }
.retiros-table tbody tr { transition: background .15s; }

.badge-espera   { background: rgba(255,193,7,.15);  color: #856404;  border: 1px solid rgba(255,193,7,.4);  font-weight: 600; }
.badge-atendido { background: rgba(25,135,84,.12);  color: #198754;  border: 1px solid rgba(25,135,84,.3);  font-weight: 600; }
.badge-rechazado{ background: rgba(220,53,69,.12);  color: #dc3545;  border: 1px solid rgba(220,53,69,.3);  font-weight: 600; }

/* Toast notification */
#ro-toast-container {
    position: fixed;
    top: 18px;
    right: 18px;
    z-index: 9999;
    min-width: 280px;
}
.ro-toast {
    padding: 12px 18px;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 500;
    margin-bottom: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    animation: roFadeIn .25s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ro-toast.success { background: #198754; color: #fff; }
.ro-toast.error   { background: #dc3545; color: #fff; }
@keyframes roFadeIn { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:none; } }

.btn-icon-sm {
    width: 28px; height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: .85rem;
}

.sede-badge {
    background: rgba(115,103,240,.1);
    color: #7367f0;
    border: 1px solid rgba(115,103,240,.25);
    font-size: .78rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-title d-flex align-items-center gap-3">
                    <h3 class="mb-0">
                        <i class="mdi mdi-clipboard-list-outline text-primary me-2"></i>
                        Retiros de Órdenes
                    </h3>
                    <span class="sede-badge">{{ $sedUsuario ?? 'TODAS LAS SEDES' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="ro-toast-container"></div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="card-title mb-0">Listado de Retiros</h6>
                    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                            data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="mdi mdi-plus"></i> AGREGAR ORDEN DE RETIRO
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover retiros-table mb-0" id="tablaRetiros">
                            <thead>
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Sede</th>
                                    <th># Orden</th>
                                    <th>Motivo</th>
                                    <th>Responsable</th>
                                    <th>Observación</th>
                                    <th>Status</th>
                                    <th>Creado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyRetiros">
                                @forelse($registros as $r)
                                <tr id="row-{{ $r->id }}" data-id="{{ $r->id }}">
                                    <td class="ps-3 text-muted" style="font-size:.8rem">{{ $r->id }}</td>
                                    <td>
                                        <span class="sede-badge">{{ $r->sede ?? '—' }}</span>
                                    </td>
                                    <td class="td-orden" style="min-width:110px">
                                        {{ $r->numero_orden ?? '—' }}
                                    </td>
                                    <td class="td-motivo" style="min-width:120px">
                                        {{ $r->motivo ?? '—' }}
                                    </td>
                                    <td style="min-width:130px">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="td-responsable">{{ $r->nombre_responsable ?? '—' }}</span>
                                            <button class="btn btn-outline-secondary btn-icon-sm ms-1 btn-reasignar"
                                                    title="Reasignar responsable"
                                                    data-id="{{ $r->id }}"
                                                    data-actual="{{ $r->nombre_responsable }}">
                                                <i class="mdi mdi-swap-vertical"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="td-observacion" style="min-width:140px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">
                                        {{ $r->observacion ?? '—' }}
                                    </td>
                                    <td>
                                        @if($r->status === 'espera')
                                            <span class="badge badge-espera">Espera</span>
                                        @elseif($r->status === 'atendido')
                                            <span class="badge badge-atendido">Atendido</span>
                                        @else
                                            <span class="badge badge-rechazado">Rechazado</span>
                                        @endif
                                    </td>
                                    <td style="font-size:.78rem; white-space:nowrap">
                                        {{ $r->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center" style="min-width:150px">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button class="btn btn-sm btn-outline-secondary btn-editar"
                                                    title="Editar"
                                                    data-id="{{ $r->id }}"
                                                    data-orden="{{ $r->numero_orden }}"
                                                    data-motivo="{{ $r->motivo }}"
                                                    data-responsable="{{ $r->nombre_responsable }}"
                                                    data-observacion="{{ $r->observacion }}">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            @if($r->status === 'espera')
                                            <button class="btn btn-sm btn-success btn-atender"
                                                    title="Marcar Atendido"
                                                    data-id="{{ $r->id }}">
                                                <i class="mdi mdi-check"></i> Atendido
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-rechazar"
                                                    title="Marcar Rechazado"
                                                    data-id="{{ $r->id }}">
                                                <i class="mdi mdi-close"></i> Rechazado
                                            </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar"
                                                    title="Eliminar"
                                                    data-id="{{ $r->id }}">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr id="row-empty">
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-clipboard-list-outline" style="font-size:2rem; opacity:.3"></i>
                                        <p class="mt-2 mb-0">No hay órdenes de retiro registradas.</p>
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
</div>

{{-- ══ MODAL CREAR ══════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearLabel">
                    <i class="mdi mdi-clipboard-plus-outline me-2 text-primary"></i>
                    Nueva Orden de Retiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrear" novalidate>
                @csrf
                <div class="modal-body">
                    <div id="msg-crear"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sede</label>
                        <input type="text" class="form-control" name="sede" id="c-sede"
                               value="{{ $sedUsuario ?? '' }}" placeholder="Sede">
                        <div class="form-text">Se autocompleta con tu sede; puedes modificarla si es necesario.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold"># Orden</label>
                        <input type="text" class="form-control" name="numero_orden" id="c-numero_orden"
                               placeholder="Número de orden">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo</label>
                        <input type="text" class="form-control" name="motivo" id="c-motivo"
                               placeholder="Motivo del retiro">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre Responsable</label>
                        <select class="form-select" name="nombre_responsable" id="c-nombre_responsable">
                            <option value="">— Seleccionar —</option>
                            @foreach($responsables as $resp)
                            <option value="{{ $resp }}">{{ $resp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observación</label>
                        <textarea class="form-control" name="observacion" id="c-observacion"
                                  rows="3" placeholder="Observación adicional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-crear-submit">
                        <i class="mdi mdi-content-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ MODAL EDITAR ══════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">
                    <i class="mdi mdi-pencil me-2 text-warning"></i>
                    Editar Orden de Retiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditar" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" id="e-id">
                <div class="modal-body">
                    <div id="msg-editar"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sede</label>
                        <input type="text" class="form-control" name="sede" id="e-sede"
                               placeholder="Sede">
                        <div class="form-text">Puedes modificar la sede si es necesario.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold"># Orden</label>
                        <input type="text" class="form-control" name="numero_orden" id="e-numero_orden"
                               placeholder="Número de orden">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo</label>
                        <input type="text" class="form-control" name="motivo" id="e-motivo"
                               placeholder="Motivo del retiro">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre Responsable</label>
                        <select class="form-select" name="nombre_responsable" id="e-nombre_responsable">
                            <option value="">— Seleccionar —</option>
                            @foreach($responsables as $resp)
                            <option value="{{ $resp }}">{{ $resp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observación</label>
                        <textarea class="form-control" name="observacion" id="e-observacion"
                                  rows="3" placeholder="Observación adicional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-white" id="btn-editar-submit">
                        <i class="mdi mdi-content-save me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ MODAL REASIGNAR ══════════════════════════════════════════ --}}
<div class="modal fade" id="modalReasignar" tabindex="-1" aria-labelledby="modalReasignarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReasignarLabel">
                    <i class="mdi mdi-swap-vertical me-2 text-info"></i>
                    Reasignar Responsable
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReasignar">
                <input type="hidden" id="r-id">
                <div class="modal-body">
                    <div id="msg-reasignar"></div>
                    <label class="form-label fw-semibold">Nuevo Responsable</label>
                    <select class="form-select" id="r-nombre_responsable" name="nombre_responsable">
                        @foreach($responsables as $resp)
                        <option value="{{ $resp }}">{{ $resp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="mdi mdi-check me-1"></i> Reasignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const ROUTES  = {
        store:    '{{ route("retiros-ordenes.store") }}',
        base:     '{{ url("retiros-ordenes") }}',
    };

    /* ── helpers ──────────────────────────────────────────── */
    function toast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `ro-toast ${type}`;
        el.innerHTML = `<i class="mdi ${type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'}"></i><span>${msg}</span>`;
        document.getElementById('ro-toast-container').appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(options.headers ?? {}),
            },
            ...options,
        });
        const data = await res.json();
        if (!res.ok) {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' | ')
                : (data.error ?? data.message ?? `Error ${res.status}`);
            throw new Error(msg);
        }
        return data;
    }

    function setLoading(btn, loading) {
        if (loading) {
            btn.dataset.orig = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
            btn.disabled = true;
        } else {
            btn.innerHTML = btn.dataset.orig ?? btn.innerHTML;
            btn.disabled = false;
        }
    }

    function statusBadge(status) {
        const map = {
            espera:    '<span class="badge badge-espera">Espera</span>',
            atendido:  '<span class="badge badge-atendido">Atendido</span>',
            rechazado: '<span class="badge badge-rechazado">Rechazado</span>',
        };
        return map[status] ?? status;
    }

    function accionesBtns(id, status) {
        let btns = `
            <button class="btn btn-sm btn-outline-secondary btn-editar" title="Editar" data-id="${id}">
                <i class="mdi mdi-pencil"></i>
            </button>`;
        if (status === 'espera') {
            btns += `
            <button class="btn btn-sm btn-success btn-atender" title="Marcar Atendido" data-id="${id}">
                <i class="mdi mdi-check"></i> Atendido
            </button>
            <button class="btn btn-sm btn-danger btn-rechazar" title="Marcar Rechazado" data-id="${id}">
                <i class="mdi mdi-close"></i> Rechazado
            </button>`;
        }
        btns += `
            <button class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar" data-id="${id}">
                <i class="mdi mdi-trash-can-outline"></i>
            </button>`;
        return `<div class="d-flex align-items-center justify-content-center gap-1">${btns}</div>`;
    }

    function removeEmptyRow() {
        const empty = document.getElementById('row-empty');
        if (empty) empty.remove();
    }

    /* ── CREAR ─────────────────────────────────────────────── */
    document.getElementById('formCrear').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btn-crear-submit');
        const msg = document.getElementById('msg-crear');
        msg.innerHTML = '';
        setLoading(btn, true);

        try {
            const fd = Object.fromEntries(new FormData(this));
            const data = await apiFetch(ROUTES.store, {
                method: 'POST',
                body: JSON.stringify(fd),
            });

            toast(data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalCrear')).hide();
            this.reset();
            removeEmptyRow();

            const r = data.registro;
            const tbody = document.getElementById('tbodyRetiros');
            const tr = document.createElement('tr');
            tr.id = `row-${r.id}`;
            tr.dataset.id = r.id;
            tr.innerHTML = `
                <td class="ps-3 text-muted" style="font-size:.8rem">${r.id}</td>
                <td><span class="sede-badge">${r.sede ?? '—'}</span></td>
                <td class="td-orden">${r.numero_orden ?? '—'}</td>
                <td class="td-motivo">${r.motivo ?? '—'}</td>
                <td style="min-width:130px">
                    <div class="d-flex align-items-center gap-1">
                        <span class="td-responsable">${r.nombre_responsable ?? '—'}</span>
                        <button class="btn btn-outline-secondary btn-icon-sm ms-1 btn-reasignar"
                                title="Reasignar responsable"
                                data-id="${r.id}"
                                data-actual="${r.nombre_responsable ?? ''}">
                            <i class="mdi mdi-swap-vertical"></i>
                        </button>
                    </div>
                </td>
                <td class="td-observacion" style="min-width:140px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">${r.observacion ?? '—'}</td>
                <td>${statusBadge(r.status)}</td>
                <td style="font-size:.78rem; white-space:nowrap">${r.created_at ?? ''}</td>
                <td class="text-center accion-cell">${accionesBtns(r.id, r.status)}</td>`;
            tbody.insertAdjacentElement('afterbegin', tr);

        } catch (err) {
            msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
        } finally {
            setLoading(btn, false);
        }
    });

    /* ── EDITAR (abrir modal) ─────────────────────────────── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-editar');
        if (!btn) return;
        const id  = btn.dataset.id;
        const row = document.getElementById(`row-${id}`);

        document.getElementById('e-id').value              = id;
        document.getElementById('e-sede').value            = row.querySelector('.sede-badge')?.textContent.trim().replace('—','') ?? '';
        document.getElementById('e-numero_orden').value    = row.querySelector('.td-orden')?.textContent.trim().replace('—','') ?? '';
        document.getElementById('e-motivo').value          = row.querySelector('.td-motivo')?.textContent.trim().replace('—','') ?? '';
        document.getElementById('e-observacion').value     = row.querySelector('.td-observacion')?.textContent.trim().replace('—','') ?? '';
        const resp = row.querySelector('.td-responsable')?.textContent.trim().replace('—','') ?? '';
        document.getElementById('e-nombre_responsable').value = resp;
        document.getElementById('msg-editar').innerHTML = '';

        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    });

    /* ── EDITAR (guardar) ─────────────────────────────────── */
    document.getElementById('formEditar').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('btn-editar-submit');
        const msg = document.getElementById('msg-editar');
        const id  = document.getElementById('e-id').value;
        msg.innerHTML = '';
        setLoading(btn, true);

        try {
            const fd = Object.fromEntries(new FormData(this));
            await apiFetch(`${ROUTES.base}/${id}`, {
                method: 'PUT',
                body: JSON.stringify(fd),
            });

            toast('Registro actualizado correctamente.');
            bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();

            const row = document.getElementById(`row-${id}`);
            if (row) {
                if (fd.sede) row.querySelector('.sede-badge').textContent = fd.sede;
                row.querySelector('.td-orden').textContent       = fd.numero_orden || '—';
                row.querySelector('.td-motivo').textContent      = fd.motivo || '—';
                row.querySelector('.td-observacion').textContent = fd.observacion || '—';
                row.querySelector('.td-responsable').textContent = fd.nombre_responsable || '—';
                const rBtn = row.querySelector('.btn-reasignar');
                if (rBtn) rBtn.dataset.actual = fd.nombre_responsable ?? '';
            }

        } catch (err) {
            msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
        } finally {
            setLoading(btn, false);
        }
    });

    /* ── REASIGNAR (abrir modal) ──────────────────────────── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-reasignar');
        if (!btn) return;

        document.getElementById('r-id').value = btn.dataset.id;
        document.getElementById('r-nombre_responsable').value = btn.dataset.actual ?? '';
        document.getElementById('msg-reasignar').innerHTML = '';

        new bootstrap.Modal(document.getElementById('modalReasignar')).show();
    });

    /* ── REASIGNAR (guardar) ──────────────────────────────── */
    document.getElementById('formReasignar').addEventListener('submit', async function (e) {
        e.preventDefault();
        const id    = document.getElementById('r-id').value;
        const nuevo = document.getElementById('r-nombre_responsable').value;
        const msg   = document.getElementById('msg-reasignar');
        msg.innerHTML = '';

        try {
            await apiFetch(`${ROUTES.base}/${id}/reasignar`, {
                method: 'PATCH',
                body: JSON.stringify({ nombre_responsable: nuevo }),
            });

            toast('Responsable reasignado correctamente.');
            bootstrap.Modal.getInstance(document.getElementById('modalReasignar')).hide();

            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.querySelector('.td-responsable').textContent = nuevo || '—';
                const rBtn = row.querySelector('.btn-reasignar');
                if (rBtn) rBtn.dataset.actual = nuevo;
            }

        } catch (err) {
            msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
        }
    });

    /* ── ATENDER ──────────────────────────────────────────── */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-atender');
        if (!btn) return;

        const id  = btn.dataset.id;
        const row = document.getElementById(`row-${id}`);
        const orig = btn.innerHTML;
        btn.disabled = true;

        try {
            await apiFetch(`${ROUTES.base}/${id}/atender`, { method: 'PATCH' });
            toast('Orden marcada como atendida.');

            if (row) {
                row.querySelector('td:nth-child(7)').innerHTML = statusBadge('atendido');
                row.querySelector('.accion-cell').innerHTML = accionesBtns(id, 'atendido');
            }
        } catch (err) {
            toast(err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

    /* ── RECHAZAR ─────────────────────────────────────────── */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-rechazar');
        if (!btn) return;

        const id  = btn.dataset.id;
        const row = document.getElementById(`row-${id}`);
        const orig = btn.innerHTML;
        btn.disabled = true;

        try {
            await apiFetch(`${ROUTES.base}/${id}/rechazar`, { method: 'PATCH' });
            toast('Orden marcada como rechazada.');

            if (row) {
                row.querySelector('td:nth-child(7)').innerHTML = statusBadge('rechazado');
                row.querySelector('.accion-cell').innerHTML = accionesBtns(id, 'rechazado');
            }
        } catch (err) {
            toast(err.message, 'error');
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

    /* ── ELIMINAR ─────────────────────────────────────────── */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;

        if (!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) return;

        const id = btn.dataset.id;
        btn.disabled = true;

        try {
            await apiFetch(`${ROUTES.base}/${id}`, { method: 'DELETE' });
            toast('Registro eliminado correctamente.');

            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.style.transition = 'opacity .3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    if (!document.querySelector('#tbodyRetiros tr')) {
                        document.getElementById('tbodyRetiros').innerHTML = `
                            <tr id="row-empty">
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-clipboard-list-outline" style="font-size:2rem; opacity:.3"></i>
                                    <p class="mt-2 mb-0">No hay órdenes de retiro registradas.</p>
                                </td>
                            </tr>`;
                    }
                }, 300);
            }
        } catch (err) {
            toast(err.message, 'error');
            btn.disabled = false;
        }
    });

    /* ── Reset modal crear al cerrar ─────────────────────── */
    document.getElementById('modalCrear').addEventListener('hidden.bs.modal', function () {
        document.getElementById('formCrear').reset();
        document.getElementById('msg-crear').innerHTML = '';
    });
    document.getElementById('modalEditar').addEventListener('hidden.bs.modal', function () {
        document.getElementById('msg-editar').innerHTML = '';
    });

})();
</script>
@endsection
