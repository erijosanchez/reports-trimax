@extends('layouts.app')

@section('title', 'Desbloqueo')

@php $verTodo = $esRevisor; @endphp

@push('styles')
<style>
.dsb-table thead th {
    font-size: .71rem; text-transform: uppercase; letter-spacing: .05em;
    white-space: nowrap; vertical-align: middle;
    background: var(--bs-tertiary-bg, #f8f9fa);
}
.dsb-table tbody td { vertical-align: middle; }
.sede-badge {
    background: rgba(115,103,240,.10); color: #7367f0;
    border: 1px solid rgba(115,103,240,.25);
    font-size: .75rem; font-weight: 600; padding: 2px 9px; border-radius: 20px;
}
.ruc-badge {
    font-family: 'Courier New', monospace; font-weight: 700; font-size: .82rem;
    color: #334155; letter-spacing: .03em;
}
.drop-zone {
    border: 2px dashed #cbd5e1; border-radius: 10px; padding: 18px 16px;
    text-align: center; cursor: pointer; transition: border-color .2s, background .2s; background: #f8fafc;
}
.drop-zone.dragover { border-color: #2563eb; background: rgba(37,99,235,.05); }
.preview-thumb { position: relative; border-radius: 8px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; }
.preview-thumb img { width: 100%; height: 60px; object-fit: cover; display: block; }
.preview-thumb .preview-icon { height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; }
.preview-remove {
    position: absolute; top: 2px; right: 2px; width: 20px; height: 20px;
    background: rgba(220,53,69,.85); color: #fff; border: none; border-radius: 50%;
    font-size: 12px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0;
}
.preview-name { font-size: 10px; text-align: center; padding: 2px 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
#dsb-toast { position: fixed; top: 18px; right: 18px; z-index: 9999; min-width: 280px; }
.dsb-toast-item {
    padding: 11px 16px; border-radius: 8px; font-size: .9rem; font-weight: 500; margin-bottom: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15); display: flex; align-items: center; gap: 10px; animation: dsbFade .25s ease;
}
.dsb-toast-item.success { background:#198754; color:#fff; }
.dsb-toast-item.error   { background:#dc3545; color:#fff; }
@keyframes dsbFade { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:none} }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center gap-3 page-title">
                    <h3 class="mb-0">
                        <i class="me-2 text-primary mdi mdi-lock-open-variant-outline"></i>Desbloqueo de Clientes
                    </h3>
                    @if($sedUsuario)
                        <span class="sede-badge">{{ $sedUsuario }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="dsb-toast"></div>

    {{-- ══ CREAR SOLICITUD (sede) ══ --}}
    @if($puedeCrear)
    <div class="mb-4 row">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-plus-circle-outline text-primary mdi"></i>
                        Nueva Solicitud de Desbloqueo
                    </h6>
                </div>
                <div class="card-body">
                    <div id="msg-crear"></div>
                    <form id="formCrear" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="c-ruc">RUC <span class="text-danger">*</span></label>
                            <input type="text" inputmode="numeric" maxlength="11" class="form-control" id="c-ruc"
                                   name="ruc" placeholder="11 dígitos"
                                   style="font-family:'Courier New',monospace;font-weight:700;letter-spacing:.05em" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="c-razon">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="c-razon" name="razon_social"
                                   placeholder="Nombre / razón social del cliente" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="c-coment">Comentarios</label>
                            <textarea class="form-control" id="c-coment" name="comentarios" rows="3"
                                      placeholder="Motivo del desbloqueo, contexto, etc. (opcional)"></textarea>
                        </div>
                        <button type="submit" class="w-100 btn btn-primary" id="btn-crear-submit">
                            <i class="me-2 mdi mdi-send"></i>Enviar solicitud a finanzas
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-3 mt-lg-0 col-12 col-lg-5">
            <div class="h-100 card">
                <div class="card-header">
                    <h6 class="mb-0 card-title"><i class="me-2 mdi-information-outline text-info mdi"></i>¿Cómo funciona?</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0" style="font-size:.88rem;line-height:1.8">
                        <li>Ingresa el <strong>RUC</strong> y la <strong>razón social</strong> del cliente a desbloquear.</li>
                        <li>Agrega comentarios si es necesario.</li>
                        <li>Finanzas recibe la notificación y revisa tu solicitud.</li>
                        <li>Recibirás un correo con el resultado (conforme, observado o rechazado).</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ KPI SEMANAL (Sedes + Finanzas) ══ --}}
    <div class="mb-4 row g-3">
        @if($verTodo)
        <div class="col-12 d-flex justify-content-end">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small">Sede:</label>
                <select id="kpi-sede" class="form-select form-select-sm" style="width:auto;min-width:150px">
                    <option value="">Todas</option>
                </select>
            </div>
        </div>
        @endif

        <div class="col-12 col-lg-6">
            <div class="h-100 card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi mdi-store text-primary"></i>KPI Sedes — Conformidad
                        <small class="text-muted fw-normal ms-1" style="font-size:.72rem">(últimas 8 semanas)</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="text-center px-3 py-2 rounded" style="background:var(--bs-tertiary-bg,#f8f9fa)">
                            <div class="text-muted small">Semana actual</div>
                            <div id="kpi-sede-valor" class="fw-bold" style="font-size:1.6rem;line-height:1">—</div>
                        </div>
                        <div class="text-muted" style="font-size:.78rem" id="kpi-sede-detalle">Sin revisiones</div>
                    </div>
                    <div style="position:relative;height:200px"><canvas id="kpiSedesChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="h-100 card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi mdi-clock-fast text-warning"></i>KPI Finanzas — Tiempo de respuesta
                        <small class="text-muted fw-normal ms-1" style="font-size:.72rem">(últimas 8 semanas)</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="text-center px-3 py-2 rounded" style="background:var(--bs-tertiary-bg,#f8f9fa)">
                            <div class="text-muted small">Semana actual</div>
                            <div id="kpi-fin-valor" class="fw-bold" style="font-size:1.6rem;line-height:1">—</div>
                        </div>
                        <div class="text-muted" style="font-size:.78rem" id="kpi-fin-detalle">Sin revisiones</div>
                    </div>
                    <div style="position:relative;height:200px"><canvas id="kpiFinanzasChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ HISTORIAL ══ --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi mdi-history"></i>Historial de Solicitudes
                        @if($verTodo)<span class="bg-secondary ms-2 badge" style="font-size:.72rem">Todas las sedes</span>@endif
                    </h6>
                </div>

                {{-- Filtros --}}
                <div class="card-body border-bottom py-3">
                    <div class="row g-2 align-items-end">
                        @if($verTodo)
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Sede</label>
                            <select id="f-sede" class="form-select form-select-sm"><option value="">Todas</option></select>
                        </div>
                        @endif
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Estado</label>
                            <select id="f-conformidad" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="sin_revisar">Pendiente rev.</option>
                                <option value="conforme">Conforme</option>
                                <option value="conforme_observado">Conforme observado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Fecha solicitud</label>
                            <input type="date" id="f-fecha" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <button id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="mdi mdi-filter-off-outline me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table dsb-table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Sede</th>
                                    <th>RUC</th>
                                    <th>Razón Social</th>
                                    @if($verTodo)<th>Solicitante</th>@endif
                                    <th>Solicitado</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">KPI Sede</th>
                                    <th class="text-center">KPI Finanzas</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-historial">
                                <tr><td colspan="{{ $verTodo ? 10 : 9 }}" class="py-5 text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-top">
                        <small class="text-muted" id="hist-info">—</small>
                        <div class="d-flex align-items-center gap-2">
                            <button id="hist-prev" class="btn btn-outline-secondary btn-sm" disabled><i class="mdi mdi-chevron-left"></i></button>
                            <span class="small text-muted" id="hist-page">1 / 1</span>
                            <button id="hist-next" class="btn btn-outline-secondary btn-sm" disabled><i class="mdi mdi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DETALLE ══ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="me-2 text-primary mdi mdi-lock-open-variant-outline"></i>Detalle de Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="det-body">
                <div class="py-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL REVISIÓN (finanzas) ══ --}}
@if($esRevisor)
<div class="modal fade" id="modalRevision" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formRevision" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="me-2 text-warning mdi mdi-clipboard-check-outline"></i>Revisar Solicitud — <span id="rev-titulo"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rev-msg"></div>
                    <input type="hidden" id="rev-id" name="solicitud_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Resultado <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="conforme" class="form-check-input mt-0">
                                <span><strong class="text-success">Conforme</strong> — aprobada (KPI sede 100%)</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="conforme_observado" class="form-check-input mt-0">
                                <span><strong class="text-warning">Conforme observado</strong> — penaliza KPI de la sede</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="rechazado" class="form-check-input mt-0">
                                <span><strong class="text-danger">Rechazado</strong> — KPI sede 0%</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3" id="rev-penalidad-wrap" style="display:none">
                        <label class="form-label fw-semibold">Descuento de KPI (sede) <span class="text-danger">*</span></label>
                        <select name="penalidad" class="form-select" id="rev-penalidad">
                            <option value="">Selecciona…</option>
                            <option value="20">−20% (queda en 80%)</option>
                            <option value="50">−50% (queda en 50%)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motivo / observación <span class="text-danger" id="rev-motivo-req">*</span></label>
                        <textarea name="motivo" id="rev-motivo" class="form-control" rows="3" placeholder="Describe el motivo del rechazo u observación…"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Adjuntos <span class="text-muted fw-normal">(opcional · img, PDF, Excel · máx. 20 MB c/u)</span></label>
                        <div class="drop-zone" id="drop-zone-rev" onclick="document.getElementById('archivos-rev').click()">
                            <i class="mdi mdi-cloud-upload-outline" style="font-size:1.8rem;color:#94a3b8"></i>
                            <p class="mt-1 mb-0 text-muted" style="font-size:.85rem">Arrastra o haz clic para adjuntar</p>
                            <input type="file" id="archivos-rev" name="archivos[]" multiple class="d-none" accept=".jpg,.jpeg,.png,.webp,.pdf,.xlsx,.xls,.csv">
                        </div>
                        <div id="preview-rev" class="mt-2 row g-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold" id="btn-rev-submit"><i class="me-1 mdi mdi-check-bold"></i>Guardar revisión</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const BASE       = '{{ url("desbloqueo") }}';
    const ES_REVISOR = {{ $esRevisor ? 'true' : 'false' }};
    const VER_TODO   = {{ $verTodo ? 'true' : 'false' }};

    /* ── helpers ── */
    function toast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `dsb-toast-item ${type}`;
        el.innerHTML = `<i class="mdi ${type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'}"></i><span>${msg}</span>`;
        document.getElementById('dsb-toast').appendChild(el);
        setTimeout(() => el.remove(), 3800);
    }
    async function apiFetch(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;
        const headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
        if (!isFormData) headers['Content-Type'] = 'application/json';
        const res  = await fetch(url, { headers, ...opts });
        const data = await res.json();
        if (!res.ok) {
            const m = data.errors ? Object.values(data.errors).flat().join(' | ') : (data.message ?? `Error ${res.status}`);
            throw new Error(m);
        }
        return data;
    }
    function setBtn(btn, loading, origHtml) {
        if (loading) { btn._orig = btn.innerHTML; btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>'; btn.disabled = true; }
        else { btn.innerHTML = origHtml ?? btn._orig; btn.disabled = false; }
    }
    const estLabels = { conforme: 'Conforme', conforme_observado: 'Observado', rechazado: 'Rechazado' };
    function estadoBadge(rev) {
        if (!rev) return '<span class="badge bg-dark">Pendiente</span>';
        const color = rev === 'conforme' ? 'success' : (rev === 'conforme_observado' ? 'warning' : 'danger');
        return `<span class="badge bg-${color}">${estLabels[rev] ?? rev}</span>`;
    }

    /* ══ Drop-zone (revisión) ══ */
    const _acumulados = {};
    function setupDropZone(zoneId, inputId, previewId) {
        const zone = document.getElementById(zoneId), input = document.getElementById(inputId), preview = document.getElementById(previewId);
        if (!zone || !input) return;
        _acumulados[inputId] = new DataTransfer();
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('dragover'); addFiles(e.dataTransfer.files, inputId, preview); });
        input.addEventListener('change', () => { addFiles(input.files, inputId, preview); input.value = ''; });
    }
    function addFiles(newFiles, inputId, preview) {
        const dt = _acumulados[inputId];
        for (const f of newFiles) dt.items.add(f);
        renderPreview(dt.files, preview, inputId);
    }
    function renderPreview(files, preview, inputId) {
        preview.innerHTML = '';
        for (let i = 0; i < files.length; i++) {
            const f = files[i], col = document.createElement('div');
            col.className = 'col-4 col-md-3';
            const isImg = f.type.startsWith('image/');
            const icon = isImg ? `<img src="${URL.createObjectURL(f)}" alt="">`
                : (f.type.includes('pdf') ? `<div class="preview-icon"><i class="mdi mdi-file-pdf-box text-danger"></i></div>`
                : `<div class="preview-icon"><i class="mdi mdi-file-excel text-success"></i></div>`);
            col.innerHTML = `<div class="preview-thumb">${icon}<button type="button" class="preview-remove" onclick="__quitarArch(${i},'${inputId}','${preview.id}')">×</button><div class="preview-name" title="${f.name}">${f.name}</div></div>`;
            preview.appendChild(col);
        }
    }
    window.__quitarArch = function (idx, inputId, previewId) {
        const dt = _acumulados[inputId], dt2 = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) if (i !== idx) dt2.items.add(dt.files[i]);
        _acumulados[inputId] = dt2;
        renderPreview(dt2.files, document.getElementById(previewId), inputId);
    };

    /* ══ Crear solicitud ══ */
    const formCrear = document.getElementById('formCrear');
    if (formCrear) {
        document.getElementById('c-ruc').addEventListener('input', e => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 11);
        });
        formCrear.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-crear-submit');
            const msg = document.getElementById('msg-crear');
            msg.innerHTML = '';
            setBtn(btn, true);
            try {
                const fd = new FormData(this);
                const data = await apiFetch(BASE, { method: 'POST', body: fd });
                toast(data.message);
                this.reset();
                loadHistorial(1);
                loadKpi();
            } catch (err) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
            } finally { setBtn(btn, false); }
        });
    }

    /* ══ Ver detalle ══ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-ver-detalle');
        if (!btn) return;
        document.getElementById('det-body').innerHTML = '<div class="py-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        try {
            const d = await apiFetch(`${BASE}/${btn.dataset.id}`);
            let adjHtml = '';
            if (d.revision_archivos && d.revision_archivos.length) {
                adjHtml = '<div class="d-flex flex-wrap gap-2 mt-2">' + d.revision_archivos.map(a =>
                    `<a href="${a.preview_url}" target="_blank" class="badge bg-light text-dark border text-decoration-none"><i class="mdi mdi-paperclip me-1"></i>${a.name}</a>`).join('') + '</div>';
            }
            let revHtml;
            if (d.revision_estado) {
                revHtml = `<div class="mt-3 p-3 rounded border">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        <span class="fw-semibold" style="font-size:.85rem"><i class="mdi mdi-clipboard-check-outline me-1 text-muted"></i>Revisión:</span>
                        ${estadoBadge(d.revision_estado)}
                        <span class="badge bg-${d.kpi_sede_color}">Sede ${d.kpi_sede_label}</span>
                        <span class="badge bg-${d.kpi_finanzas_color}">Finanzas ${d.kpi_finanzas_label}</span>
                    </div>
                    ${d.revision_motivo ? `<div class="text-muted" style="font-size:.82rem"><strong>Motivo:</strong> ${d.revision_motivo}</div>` : ''}
                    <div class="text-muted mt-1" style="font-size:.75rem">Por ${d.revision_revisor ?? '—'} · ${d.revision_at ?? ''}</div>
                    ${adjHtml}
                </div>`;
            } else {
                revHtml = `<div class="mt-3 text-muted small"><i class="mdi mdi-information-outline me-1"></i>Sin revisión de finanzas aún · <span class="badge bg-dark">Pendiente</span></div>`;
            }
            let revisarBtn = '';
            if (d.puede_revisar) {
                revisarBtn = `<div class="mt-3 text-end"><button class="btn btn-warning btn-sm fw-bold" data-bs-dismiss="modal" onclick="__abrirRevision('${d.id}','${(d.razon_social||'').replace(/'/g,"\\'")}')"><i class="mdi mdi-clipboard-check-outline me-1"></i>Revisar</button></div>`;
            }
            document.getElementById('det-body').innerHTML = `
                <dl class="row mb-0" style="font-size:.9rem">
                    <dt class="col-4 text-muted">Sede</dt><dd class="col-8"><span class="sede-badge">${d.sede}</span></dd>
                    <dt class="col-4 text-muted">RUC</dt><dd class="col-8 ruc-badge">${d.ruc}</dd>
                    <dt class="col-4 text-muted">Razón Social</dt><dd class="col-8">${d.razon_social}</dd>
                    <dt class="col-4 text-muted">Solicitante</dt><dd class="col-8">${d.solicitante ?? '—'}</dd>
                    <dt class="col-4 text-muted">Solicitado</dt><dd class="col-8">${d.creado ?? '—'}</dd>
                    ${d.comentarios ? `<dt class="col-4 text-muted">Comentarios</dt><dd class="col-8">${d.comentarios}</dd>` : ''}
                </dl>
                ${revHtml}
                ${revisarBtn}`;
        } catch (err) {
            document.getElementById('det-body').innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
        }
    });

    /* ══ Eliminar ══ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;
        if (!confirm('¿Eliminar esta solicitud? Esta acción no se puede deshacer.')) return;
        btn.disabled = true;
        try {
            await apiFetch(`${BASE}/${btn.dataset.id}`, { method: 'DELETE', body: JSON.stringify({}) });
            toast('Solicitud eliminada.');
            loadHistorial(histPage);
            loadKpi();
        } catch (err) { toast(err.message, 'error'); btn.disabled = false; }
    });

    /* ══ Historial AJAX + filtros + paginación ══ */
    let histPage = 1, histLastPage = 1;
    function filtrosHist() {
        const p = new URLSearchParams();
        const sede = document.getElementById('f-sede')?.value;
        const conf = document.getElementById('f-conformidad')?.value;
        const fec  = document.getElementById('f-fecha')?.value;
        if (sede) p.set('sede', sede);
        if (conf) p.set('conformidad', conf);
        if (fec)  p.set('fecha', fec);
        return p;
    }
    function renderRow(s) {
        const solicitanteCol = VER_TODO ? `<td style="font-size:.83rem">${s.solicitante ?? '—'}</td>` : '';
        const revisarBtn = s.puede_revisar
            ? `<button class="btn-outline-warning btn btn-sm btn-revisar" title="Revisar" data-id="${s.id}" data-titulo="${(s.razon_social||'').replace(/"/g,'&quot;')}"><i class="mdi mdi-clipboard-check-outline"></i></button>` : '';
        const eliminarBtn = s.puede_eliminar
            ? `<button class="btn-outline-danger btn btn-sm btn-eliminar" title="Eliminar" data-id="${s.id}"><i class="mdi-trash-can-outline mdi"></i></button>` : '';
        return `<tr>
            <td class="ps-3 text-muted" style="font-size:.8rem">${s.id}</td>
            <td><span class="sede-badge">${s.sede}</span></td>
            <td class="ruc-badge">${s.ruc}</td>
            <td style="font-size:.85rem">${s.razon_social}</td>
            ${solicitanteCol}
            <td style="font-size:.82rem;white-space:nowrap">${s.creado ?? '—'}</td>
            <td class="text-center">${estadoBadge(s.revision_estado)}</td>
            <td class="text-center"><span class="badge bg-${s.kpi_sede_color}">${s.kpi_sede_label}</span></td>
            <td class="text-center"><span class="badge bg-${s.kpi_finanzas_color}">${s.kpi_finanzas_label}</span></td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button class="btn-outline-secondary btn btn-sm btn-ver-detalle" title="Ver detalle" data-id="${s.id}"><i class="mdi-eye-outline mdi"></i></button>
                    ${revisarBtn}${eliminarBtn}
                </div>
            </td>
        </tr>`;
    }
    async function loadHistorial(page = 1) {
        const tbody = document.getElementById('tbody-historial');
        const cols = VER_TODO ? 10 : 9;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="py-4 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const p = filtrosHist(); p.set('page', page);
        try {
            const data = await apiFetch(`${BASE}/historial?${p.toString()}`);
            histPage = data.current_page; histLastPage = data.last_page || 1;
            tbody.innerHTML = data.data.length
                ? data.data.map(renderRow).join('')
                : `<tr><td colspan="${cols}" class="py-5 text-muted text-center"><i class="mdi mdi-lock-open-outline" style="font-size:2rem;opacity:.3"></i><p class="mt-2 mb-0">No hay solicitudes.</p></td></tr>`;
            const desde = data.total ? (data.current_page - 1) * data.per_page + 1 : 0;
            const hasta = Math.min(data.current_page * data.per_page, data.total);
            document.getElementById('hist-info').textContent = `${desde}–${hasta} de ${data.total}`;
            document.getElementById('hist-page').textContent = `${histPage} / ${histLastPage}`;
            document.getElementById('hist-prev').disabled = histPage <= 1;
            document.getElementById('hist-next').disabled = histPage >= histLastPage;
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="${cols}" class="py-4 text-center text-danger">${err.message}</td></tr>`;
        }
    }
    document.getElementById('hist-prev').addEventListener('click', () => { if (histPage > 1) loadHistorial(histPage - 1); });
    document.getElementById('hist-next').addEventListener('click', () => { if (histPage < histLastPage) loadHistorial(histPage + 1); });
    ['f-sede', 'f-conformidad', 'f-fecha'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => loadHistorial(1));
    });
    document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
        ['f-sede', 'f-conformidad', 'f-fecha'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        loadHistorial(1);
    });

    /* ══ Sedes (filtros + KPI) ══ */
    async function populateSedes() {
        if (!VER_TODO) return;
        try {
            const sedes = await apiFetch(`${BASE}/sedes`);
            const opts = sedes.map(s => `<option value="${s}">${s}</option>`).join('');
            document.getElementById('f-sede')?.insertAdjacentHTML('beforeend', opts);
            document.getElementById('kpi-sede')?.insertAdjacentHTML('beforeend', opts);
        } catch (e) {}
    }

    /* ══ KPI semanal (dos gráficos) ══ */
    let chartSedes = null, chartFin = null;
    const colorSede = v => v === null ? 'rgba(148,163,184,.4)' : v >= 100 ? 'rgba(16,185,129,.85)' : v >= 75 ? 'rgba(59,130,246,.85)' : v >= 50 ? 'rgba(234,179,8,.85)' : 'rgba(239,68,68,.85)';
    function textColor(v) { return v === null ? 'text-muted' : v >= 100 ? 'text-success' : v >= 75 ? 'text-info' : v >= 50 ? 'text-warning' : 'text-danger'; }
    function mkChart(existing, canvas, labels, data, label) {
        if (existing) existing.destroy();
        return new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: data.map(colorSede), borderRadius: 6, maxBarThickness: 40 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.parsed.y === null ? 'Sin datos' : c.parsed.y + '%' } } }
            }
        });
    }
    async function loadKpi() {
        const cS = document.getElementById('kpiSedesChart'), cF = document.getElementById('kpiFinanzasChart');
        if (!cS || !cF) return;
        const sede = document.getElementById('kpi-sede')?.value || '';
        const p = new URLSearchParams(); if (sede) p.set('sede', sede);
        try {
            const d = await apiFetch(`${BASE}/kpi-semanal?${p.toString()}`);
            // Resumen sede
            const sv = document.getElementById('kpi-sede-valor'), sd = document.getElementById('kpi-sede-detalle');
            if (d.promedio_sedes === null) { sv.textContent = '—'; sv.className = 'fw-bold text-muted'; sd.textContent = 'Sin revisiones esta semana'; }
            else { sv.textContent = d.promedio_sedes + '%'; sv.className = 'fw-bold ' + textColor(d.promedio_sedes); sd.textContent = `${d.revisados_actual} revisada(s)`; }
            // Resumen finanzas
            const fv = document.getElementById('kpi-fin-valor'), fd = document.getElementById('kpi-fin-detalle');
            if (d.promedio_finanzas === null) { fv.textContent = '—'; fv.className = 'fw-bold text-muted'; fd.textContent = 'Sin revisiones esta semana'; }
            else { fv.textContent = d.promedio_finanzas + '%'; fv.className = 'fw-bold ' + textColor(d.promedio_finanzas); fd.textContent = `${d.revisados_actual} revisada(s)`; }

            chartSedes = mkChart(chartSedes, cS, d.labels, d.sedes, 'Conformidad %');
            chartFin   = mkChart(chartFin,   cF, d.labels, d.finanzas, 'Tiempo respuesta %');
        } catch (e) {}
    }
    document.getElementById('kpi-sede')?.addEventListener('change', loadKpi);

    /* ══ Revisión ══ */
    if (ES_REVISOR) {
        setupDropZone('drop-zone-rev', 'archivos-rev', 'preview-rev');
        const modalRev = new bootstrap.Modal(document.getElementById('modalRevision'));
        function abrirRevision(id, titulo) {
            const form = document.getElementById('formRevision');
            form.reset();
            document.getElementById('rev-id').value = id;
            document.getElementById('rev-titulo').textContent = titulo || ('#' + id);
            document.getElementById('rev-msg').innerHTML = '';
            document.getElementById('rev-penalidad-wrap').style.display = 'none';
            document.getElementById('preview-rev').innerHTML = '';
            _acumulados['archivos-rev'] = new DataTransfer();
            modalRev.show();
        }
        window.__abrirRevision = abrirRevision;
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-revisar');
            if (!btn) return;
            abrirRevision(btn.dataset.id, btn.dataset.titulo);
        });
        document.querySelectorAll('#formRevision input[name="estado"]').forEach(r => {
            r.addEventListener('change', () => {
                const est = document.querySelector('#formRevision input[name="estado"]:checked')?.value;
                document.getElementById('rev-penalidad-wrap').style.display = est === 'conforme_observado' ? 'block' : 'none';
                document.getElementById('rev-motivo-req').style.display = est === 'conforme' ? 'none' : 'inline';
            });
        });
        document.getElementById('formRevision').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-rev-submit'), msg = document.getElementById('rev-msg');
            msg.innerHTML = '';
            const est = document.querySelector('#formRevision input[name="estado"]:checked')?.value;
            if (!est) { msg.innerHTML = '<div class="alert alert-danger py-2">Selecciona un resultado.</div>'; return; }
            const id = document.getElementById('rev-id').value;
            setBtn(btn, true);
            try {
                const dt = _acumulados['archivos-rev'];
                const fd = new FormData(this);
                fd.delete('archivos[]');
                if (dt && dt.files.length) for (const f of dt.files) fd.append('archivos[]', f);
                const data = await apiFetch(`${BASE}/${id}/revisar`, { method: 'POST', body: fd });
                toast(data.message);
                modalRev.hide();
                loadHistorial(histPage);
                loadKpi();
            } catch (err) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
            } finally { setBtn(btn, false); }
        });
    }

    /* ══ Init ══ */
    populateSedes();
    loadHistorial(1);
    loadKpi();
})();
</script>
@endsection
