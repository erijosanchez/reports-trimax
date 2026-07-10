@extends('layouts.app')

@section('title', 'Vouchers')

@push('styles')
<style>
/* ── Vouchers ──────────────────────────────────────── */
.voucher-table thead th {
    font-size: .71rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    vertical-align: middle;
    background: var(--bs-tertiary-bg, #f8f9fa);
}
.voucher-table tbody td { vertical-align: middle; }

.badge-pendiente { background: rgba(255,193,7,.15);  color: #856404; border: 1px solid rgba(255,193,7,.4);  font-weight:600; }
.badge-aplicado  { background: rgba(25,135,84,.12);  color: #198754; border: 1px solid rgba(25,135,84,.3);  font-weight:600; }

.codigo-badge {
    background: linear-gradient(135deg,rgba(255,152,0,.15),rgba(255,87,34,.10));
    border: 1px solid rgba(255,152,0,.35);
    color: #e65100;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: .82rem;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: .04em;
}

.sede-badge {
    background: rgba(115,103,240,.10);
    color: #7367f0;
    border: 1px solid rgba(115,103,240,.25);
    font-size: .75rem;
    font-weight: 600;
    padding: 2px 9px;
    border-radius: 20px;
}

.kpi-ok   { color: #198754; font-weight:700; }
.kpi-warn { color: #856404; font-weight:700; }
.kpi-bad  { color: #dc3545; font-weight:700; }

/* Factura rows */
.factura-row td { padding: 6px 10px; }
.factura-row input { font-size:.85rem; }

.total-row td {
    font-weight: 700;
    font-size: .95rem;
    border-top: 2px solid #dee2e6 !important;
    background: var(--bs-tertiary-bg,#f8f9fa);
}

/* Pending card (Silvia) */
.pending-card {
    border-left: 4px solid #fd7e14;
    transition: box-shadow .15s;
}
.pending-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }

/* Drop-zone */
.drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 22px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #f8fafc;
}
.drop-zone.dragover {
    border-color: #2563eb;
    background: rgba(37,99,235,.05);
}
.drop-zone p { margin: 0; }
.preview-thumb {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}
.preview-thumb img {
    width: 100%;
    height: 70px;
    object-fit: cover;
    display: block;
}
.preview-thumb .preview-icon {
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}
.preview-remove {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    background: rgba(220,53,69,.85);
    color: #fff;
    border: none;
    border-radius: 50%;
    font-size: 12px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.preview-name {
    font-size: 10px;
    text-align: center;
    padding: 2px 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Toast */
#vchr-toast {
    position: fixed;
    top: 18px;
    right: 18px;
    z-index: 9999;
    min-width: 280px;
}
.vchr-toast-item {
    padding: 11px 16px;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 500;
    margin-bottom: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    display: flex;
    align-items: center;
    gap: 10px;
    animation: vchrFade .25s ease;
}
.vchr-toast-item.success { background:#198754; color:#fff; }
.vchr-toast-item.error   { background:#dc3545; color:#fff; }
@keyframes vchrFade { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:none} }
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
                        <i class="me-2 text-primary mdi mdi-receipt"></i>Vouchers
                    </h3>
                    @if($sedUsuario)
                        <span class="sede-badge">{{ $sedUsuario }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="vchr-toast"></div>

    {{-- ══ PANEL SILVIA: Vouchers Pendientes ══ --}}
    @if($esSilvia)
    <div class="mb-4 row">
        <div class="col-12">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-clock-alert-outline text-warning mdi"></i>
                        Vouchers Pendientes de Aplicar
                    </h6>
                    <span class="bg-warning text-dark badge">
                        {{ $pendientes->count() }} pendiente(s)
                    </span>
                </div>
                <div class="p-0 card-body">
                    @if($pendientes->isEmpty())
                    <div class="py-5 text-muted text-center">
                        <i class="mdi mdi-check-all" style="font-size:2rem;opacity:.3"></i>
                        <p class="mt-2 mb-0">No hay vouchers pendientes. ¡Todo al día!</p>
                    </div>
                    @else
                    <div class="d-flex flex-column gap-3 p-3">
                        @foreach($pendientes as $v)
                        @php
                            $kpi = $v->solicitado_at ? $v->solicitado_at->diffInDays(now()) : null;
                        @endphp
                        <div class="mb-0 card pending-card">
                            <div class="py-3 card-body">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="codigo-badge">{{ $v->codigo }}</span>
                                            <span class="sede-badge">{{ $v->sede }}</span>
                                            <span class="badge badge-pendiente">Pendiente</span>
                                            @if($kpi !== null)
                                                <span class="badge {{ $kpi <= 3 ? 'bg-success' : ($kpi <= 7 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                    {{ $kpi }}d
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            Solicitado por <strong>{{ $v->creator?->name ?? '—' }}</strong>
                                            el {{ $v->solicitado_at?->format('d/m/Y') ?? '—' }}
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-dark fw-bold">S/ {{ number_format($v->total, 2) }}</span>
                                        <button class="btn-outline-secondary btn btn-sm btn-ver-detalle"
                                                title="Ver facturas"
                                                data-id="{{ $v->id }}"
                                                data-codigo="{{ $v->codigo }}">
                                            <i class="mdi-eye-outline mdi"></i>
                                        </button>
                                        <button class="text-dark btn btn-sm btn-warning fw-bold btn-aplicar"
                                                data-id="{{ $v->id }}"
                                                data-codigo="{{ $v->codigo }}">
                                            <i class="me-1 mdi mdi-check-bold"></i>APLICAR
                                        </button>
                                    </div>
                                </div>
                                {{-- Facturas inline --}}
                                <div class="facturas-inline mt-2" id="facturas-inline-{{ $v->id }}" style="display:none">
                                    <table class="table table-bordered table-sm mt-2 mb-0" style="font-size:.82rem">
                                        <thead class="table-light">
                                            <tr><th>Factura</th><th>RUC</th><th class="text-end">Monto</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($v->facturas as $f)
                                            <tr>
                                                <td>{{ $f->factura }}</td>
                                                <td style="font-family:'Courier New',monospace">{{ $f->ruc ?? '—' }}</td>
                                                <td class="text-end">S/ {{ number_format($f->monto,2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light fw-bold">
                                                <td colspan="2">Total</td>
                                                <td class="text-end">S/ {{ number_format($v->total,2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PANEL SEDE: Crear Voucher ══ --}}
    @elseif($puedeCrear)
    <div class="mb-4 row">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-plus-circle-outline text-primary mdi"></i>
                        Nuevo Voucher
                    </h6>
                </div>
                <div class="card-body">
                    <div id="msg-crear"></div>
                    <form id="formCrear" enctype="multipart/form-data" novalidate>
                        @csrf

                        {{-- Número de voucher (manual) --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="c-codigo">
                                N° de Voucher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="c-codigo"
                                   name="codigo" placeholder="Ej: ER1452D225"
                                   style="font-family:'Courier New',monospace;font-weight:700;letter-spacing:.05em"
                                   required>
                            <small class="text-muted">Ingresa el número real del voucher físico.</small>
                        </div>

                        {{-- Facturas dinámicas --}}
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="mb-0 form-label fw-semibold">Facturas</label>
                                <button type="button" class="btn-outline-primary btn btn-sm" id="btn-add-factura">
                                    <i class="me-1 mdi mdi-plus"></i>Agregar fila
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-1" id="tabla-facturas">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:35%">Factura</th>
                                            <th style="width:30%">RUC</th>
                                            <th style="width:27%">Monto (S/)</th>
                                            <th style="width:8%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-facturas">
                                        {{-- filas dinámicas --}}
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td class="text-end" colspan="2">TOTAL DOCUMENTO:</td>
                                            <td colspan="2">
                                                S/ <span id="total-display">0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <small class="text-muted">Agrega al menos una factura antes de enviar.</small>
                        </div>

                        {{-- Drop-zone: múltiples archivos --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Documentos adjuntos
                                <span class="text-muted fw-normal">(opcional · JPG, PNG, PDF, Excel · máx. 20 MB c/u)</span>
                            </label>
                            <div class="drop-zone" id="drop-zone-crear"
                                 onclick="document.getElementById('archivos-crear').click()">
                                <i class="mdi mdi-cloud-upload-outline" style="font-size:2.2rem;color:#94a3b8"></i>
                                <p class="mt-1 mb-1 text-muted" style="font-size:.88rem">
                                    Arrastra archivos aquí o haz clic para seleccionar
                                </p>
                                <p class="text-muted small mb-2">JPG, PNG, GIF, WEBP, PDF, Excel/CSV</p>
                                <div class="d-flex justify-content-center gap-2" onclick="event.stopPropagation()">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="document.getElementById('archivos-crear').click()">
                                        <i class="mdi mdi-paperclip me-1"></i>Seleccionar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="abrirCamara('archivos-crear','preview-crear')">
                                        <i class="mdi mdi-camera me-1"></i>Tomar foto
                                    </button>
                                </div>
                                <input type="file" id="archivos-crear" name="archivos[]"
                                       multiple class="d-none"
                                       accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xlsx,.xls,.csv">
                            </div>
                            <div id="preview-crear" class="mt-2 row g-2"></div>
                            <div id="peso-crear" class="mt-2 small text-muted" style="display:none"></div>
                        </div>

                        <button type="submit" class="w-100 btn btn-primary" id="btn-crear-submit">
                            <i class="me-2 mdi mdi-send"></i>Crear y enviar a Silvia para aplicar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-3 mt-lg-0 col-12 col-lg-5">
            <div class="h-100 card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-information-outline text-info mdi"></i>
                        ¿Cómo funciona?
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0" style="font-size:.88rem;line-height:1.8">
                        <li>Ingresa el <strong>número real del voucher</strong> físico.</li>
                        <li>Agrega las facturas con sus montos.</li>
                        <li>Arrastra o adjunta los documentos/fotos del comprobante.</li>
                        <li>Presiona <strong>"Crear y enviar"</strong> — Silvia recibe notificación por correo.</li>
                        <li>Cuando sea aplicado, recibirás un correo de confirmación.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @endif

    @php $verTodo = $esSilvia || $esRevisor; @endphp

    {{-- ══ KPI SEMANAL DE CONFORMIDAD ══ --}}
    <div class="mb-4 row">
        <div class="col-12">
            <div class="card">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi mdi-chart-line text-primary"></i>
                        KPI Semanal de Conformidad
                        <small class="text-muted fw-normal ms-1" style="font-size:.72rem">
                            (promedio de vouchers revisados · últimas 8 semanas)
                        </small>
                    </h6>
                    @if($verTodo)
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 text-muted small">Sede:</label>
                        <select id="kpi-sede" class="form-select form-select-sm" style="width:auto;min-width:150px">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-3">
                            <div class="text-center p-3 rounded" style="background:var(--bs-tertiary-bg,#f8f9fa)">
                                <div class="text-muted small mb-1">Semana actual</div>
                                <div id="kpi-actual-valor" class="fw-bold" style="font-size:2rem;line-height:1">—</div>
                                <div id="kpi-actual-detalle" class="text-muted mt-1" style="font-size:.75rem">Sin revisiones</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-9">
                            <div style="position:relative;height:220px">
                                <canvas id="kpiSemanalChart"></canvas>
                            </div>
                        </div>
                    </div>
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
                        <i class="me-2 mdi mdi-history"></i>
                        Historial de Vouchers
                        @if($verTodo)
                            <span class="bg-secondary ms-2 badge" style="font-size:.72rem">Todas las sedes</span>
                        @endif
                    </h6>
                </div>

                {{-- Filtros --}}
                <div class="card-body border-bottom py-3">
                    <div class="row g-2 align-items-end">
                        @if($verTodo)
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Sede</label>
                            <select id="f-sede" class="form-select form-select-sm">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Estado</label>
                            <select id="f-estado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aplicado">Aplicado</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted">Conformidad</label>
                            <select id="f-conformidad" class="form-select form-select-sm">
                                <option value="">Todas</option>
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
                        <div class="col-12 col-lg-2">
                            <button id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="mdi mdi-filter-off-outline me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table voucher-table table-hover mb-0" id="tablaHistorial">
                            <thead>
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Voucher</th>
                                    <th>Estado</th>
                                    <th>Sede</th>
                                    @if($verTodo)
                                    <th>Solicitante</th>
                                    @endif
                                    <th class="text-end">Monto</th>
                                    <th>Solicitado</th>
                                    <th>Aplicado</th>
                                    <th class="text-center">Demora</th>
                                    <th class="text-center">KPI</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-historial">
                                <tr><td colspan="{{ $verTodo ? 11 : 10 }}" class="py-5 text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- Paginación --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2 border-top">
                        <small class="text-muted" id="hist-info">—</small>
                        <div class="d-flex align-items-center gap-2">
                            <button id="hist-prev" class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="mdi mdi-chevron-left"></i>
                            </button>
                            <span class="small text-muted" id="hist-page">1 / 1</span>
                            <button id="hist-next" class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="mdi mdi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL DETALLE VOUCHER ══ --}}
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="me-2 text-primary mdi mdi-receipt"></i>
                    Detalle Voucher — <span id="det-codigo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="det-body">
                <div class="py-3 text-center">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
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
                    <h5 class="modal-title">
                        <i class="me-2 text-warning mdi mdi-clipboard-check-outline"></i>
                        Revisar Voucher — <span id="rev-codigo"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rev-msg"></div>
                    <input type="hidden" id="rev-id" name="voucher_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Resultado de la revisión <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="conforme" class="form-check-input mt-0">
                                <span><strong class="text-success">Conforme</strong> — mantiene 100% en el KPI</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="conforme_observado" class="form-check-input mt-0">
                                <span><strong class="text-warning">Conforme observado</strong> — penaliza el KPI</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer">
                                <input type="radio" name="estado" value="rechazado" class="form-check-input mt-0">
                                <span><strong class="text-danger">Rechazado</strong> — KPI a 0%</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="rev-penalidad-wrap" style="display:none">
                        <label class="form-label fw-semibold">Descuento de KPI <span class="text-danger">*</span></label>
                        <select name="penalidad" class="form-select" id="rev-penalidad">
                            <option value="">Selecciona…</option>
                            <option value="20">−20% (queda en 80%)</option>
                            <option value="50">−50% (queda en 50%)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Motivo / observación <span class="text-danger" id="rev-motivo-req">*</span>
                        </label>
                        <textarea name="motivo" id="rev-motivo" class="form-control" rows="3"
                                  placeholder="Describe el motivo del rechazo u observación…"></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">
                            Adjuntos <span class="text-muted fw-normal">(opcional · img, PDF, Excel · máx. 20 MB c/u)</span>
                        </label>
                        <div class="drop-zone" id="drop-zone-rev"
                             onclick="document.getElementById('archivos-rev').click()">
                            <i class="mdi mdi-cloud-upload-outline" style="font-size:1.8rem;color:#94a3b8"></i>
                            <p class="mt-1 mb-0 text-muted" style="font-size:.85rem">Arrastra o haz clic para adjuntar</p>
                            <input type="file" id="archivos-rev" name="archivos[]" multiple class="d-none"
                                   accept=".jpg,.jpeg,.png,.webp,.pdf,.xlsx,.xls,.csv">
                        </div>
                        <div id="preview-rev" class="mt-2 row g-2"></div>
                        <div id="peso-rev" class="mt-2 small text-muted" style="display:none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold" id="btn-rev-submit">
                        <i class="me-1 mdi mdi-check-bold"></i>Guardar revisión
                    </button>
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
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    const BASE   = '{{ url("vouchers") }}';
    const ES_SILVIA  = {{ $esSilvia ? 'true' : 'false' }};
    const ES_REVISOR = {{ $esRevisor ? 'true' : 'false' }};
    const VER_TODO   = {{ ($esSilvia || $esRevisor) ? 'true' : 'false' }};

    // Límites reales del servidor (php.ini) para validar antes de enviar.
    const MAX_UPLOAD_FILES = {{ $maxUploadFiles ?? 20 }};
    const MAX_UPLOAD_TOTAL = {{ $maxUploadTotal ?? (38 * 1024 * 1024) }};

    /* ── helpers ──────────────────────────────────────────── */
    function toast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `vchr-toast-item ${type}`;
        el.innerHTML = `<i class="mdi ${type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'}"></i><span>${msg}</span>`;
        document.getElementById('vchr-toast').appendChild(el);
        setTimeout(() => el.remove(), 3800);
    }

    async function apiFetch(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;
        const headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
        if (!isFormData) headers['Content-Type'] = 'application/json';

        const res  = await fetch(url, { headers, ...opts });
        const data = await res.json();
        if (!res.ok) {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' | ')
                : (data.message ?? `Error ${res.status}`);
            throw new Error(msg);
        }
        return data;
    }

    function setBtn(btn, loading, origHtml) {
        if (loading) {
            btn._orig = btn.innerHTML;
            btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>';
            btn.disabled  = true;
        } else {
            btn.innerHTML = origHtml ?? btn._orig;
            btn.disabled  = false;
        }
    }

    function statusBadge(status) {
        return status === 'pendiente'
            ? '<span class="badge badge-pendiente">Pendiente</span>'
            : '<span class="badge badge-aplicado">Aplicado</span>';
    }

    /* ══ DROP-ZONE: múltiples archivos ═════════════════════ */
    const _acumulados = {};

    function setupDropZone(zoneId, inputId, previewId) {
        const zone    = document.getElementById(zoneId);
        const input   = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!zone || !input) return;

        _acumulados[inputId] = new DataTransfer();

        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', async e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            await agregarArchivos(e.dataTransfer.files, inputId, preview);
        });
        input.addEventListener('change', async () => {
            await agregarArchivos(input.files, inputId, preview);
            input.value = '';
        });
    }

    /* ── Compresión de imágenes en el navegador ──
       Foto de celular (1.5-4 MB) → JPEG ~400 KB, legible para un recibo.
       PDFs/Excel/GIF y las imágenes ya livianas pasan tal cual. Si algo falla
       (formato raro tipo HEIC), se devuelve el archivo original sin romper. */
    async function compressImage(file, { maxDim = 1800, quality = 0.72, skipUnderKB = 500 } = {}) {
        if (!file.type.startsWith('image/')) return file;
        if (file.type === 'image/gif')       return file;
        if (file.size <= skipUnderKB * 1024)  return file;
        try {
            const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
            const scale  = Math.min(1, maxDim / Math.max(bitmap.width, bitmap.height));
            const w = Math.max(1, Math.round(bitmap.width  * scale));
            const h = Math.max(1, Math.round(bitmap.height * scale));
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(bitmap, 0, 0, w, h);
            if (bitmap.close) bitmap.close();
            const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', quality));
            if (!blob || blob.size >= file.size) return file; // si no mejora, original
            const nombre = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], nombre, { type: 'image/jpeg', lastModified: Date.now() });
        } catch (e) {
            return file;
        }
    }

    const pesoElId = inputId => 'peso-' + inputId.replace('archivos-', '');
    const fmtMB    = bytes => (bytes / 1048576).toFixed(1) + ' MB';

    function pesoTotal(inputId) {
        const files = _acumulados[inputId] ? _acumulados[inputId].files : [];
        let total = 0;
        for (const f of files) total += f.size;
        return { n: files.length, total };
    }

    function updatePeso(inputId) {
        const el = document.getElementById(pesoElId(inputId));
        if (!el) return;
        const { n, total } = pesoTotal(inputId);
        if (n === 0) { el.style.display = 'none'; return; }
        el.style.display = '';
        const over  = n > MAX_UPLOAD_FILES || total > MAX_UPLOAD_TOTAL;
        const cerca = n > MAX_UPLOAD_FILES * 0.8 || total > MAX_UPLOAD_TOTAL * 0.8;
        el.className = 'mt-2 small ' + (over ? 'text-danger fw-semibold' : cerca ? 'text-warning fw-semibold' : 'text-muted');
        let msg = `<i class="mdi mdi-paperclip me-1"></i>Archivos: ${n}/${MAX_UPLOAD_FILES} · Peso total: ${fmtMB(total)} / ${fmtMB(MAX_UPLOAD_TOTAL)}`;
        if (n > MAX_UPLOAD_FILES)      msg += ` — demasiados archivos (máx ${MAX_UPLOAD_FILES})`;
        else if (total > MAX_UPLOAD_TOTAL) msg += ` — supera el peso permitido`;
        el.innerHTML = msg;
    }

    /** Devuelve un mensaje de error si la subida excede los límites, o null si está ok. */
    function validarSubida(inputId) {
        const { n, total } = pesoTotal(inputId);
        if (n > MAX_UPLOAD_FILES) {
            return `Tienes ${n} archivos y el servidor acepta un máximo de ${MAX_UPLOAD_FILES}. Quita algunos o pide a soporte subir el límite (max_file_uploads).`;
        }
        if (total > MAX_UPLOAD_TOTAL) {
            return `El peso total (${fmtMB(total)}) supera el límite del servidor (${fmtMB(MAX_UPLOAD_TOTAL)}). Quita o reduce archivos antes de enviar.`;
        }
        return null;
    }

    async function agregarArchivos(newFiles, inputId, preview) {
        const arr = Array.from(newFiles);
        if (!arr.length) return;
        const dt = _acumulados[inputId];
        const el = document.getElementById(pesoElId(inputId));
        if (el) {
            el.style.display = '';
            el.className = 'mt-2 small text-muted';
            el.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando archivos…';
        }
        for (const f of arr) {
            const procesado = await compressImage(f);
            dt.items.add(procesado);
        }
        renderPreview(dt.files, preview, inputId);
    }

    function renderPreview(files, preview, inputId) {
        preview.innerHTML = '';
        for (let i = 0; i < files.length; i++) {
            const f   = files[i];
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3 col-lg-2';
            const isImg = f.type.startsWith('image/');
            let iconHtml;
            if (isImg) {
                iconHtml = `<img src="${URL.createObjectURL(f)}" alt="${f.name}">`;
            } else if (f.type.includes('pdf')) {
                iconHtml = `<div class="preview-icon"><i class="mdi mdi-file-pdf-box text-danger"></i></div>`;
            } else {
                iconHtml = `<div class="preview-icon"><i class="mdi mdi-file-excel text-success"></i></div>`;
            }
            col.innerHTML = `
                <div class="preview-thumb">
                    ${iconHtml}
                    <button type="button" class="preview-remove"
                            onclick="quitarArchivo(${i},'${inputId}','${preview.id}')">×</button>
                    <div class="preview-name" title="${f.name}">${f.name}</div>
                </div>`;
            preview.appendChild(col);
        }
        // Los archivos se conservan en _acumulados[inputId] y se leen desde ahí
        // al enviar (FormData). NO se sincroniza a input.files: hacerlo con
        // Object.defineProperty sobrescribe el accessor nativo y congela el input,
        // impidiendo volver a seleccionar archivos hasta refrescar la página.
        updatePeso(inputId);
    }

    function quitarArchivo(idx, inputId, previewId) {
        const dt  = _acumulados[inputId];
        const dt2 = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== idx) dt2.items.add(dt.files[i]);
        }
        _acumulados[inputId] = dt2;
        renderPreview(dt2.files, document.getElementById(previewId), inputId);
    }

    function abrirCamara(inputId, previewId) {
        const input = document.createElement('input');
        input.type  = 'file';
        input.accept= 'image/*';
        input.capture = 'environment';
        input.onchange = async () => {
            await agregarArchivos(input.files, inputId, document.getElementById(previewId));
        };
        input.click();
    }

    /* ══ PANEL SEDE: Crear voucher ══════════════════════════ */
    const formCrear = document.getElementById('formCrear');
    if (formCrear) {
        setupDropZone('drop-zone-crear', 'archivos-crear', 'preview-crear');

        let facturaIdx = 0;

        function addFacturaRow() {
            const i     = facturaIdx++;
            const tbody = document.getElementById('tbody-facturas');
            const tr    = document.createElement('tr');
            tr.className = 'factura-row';
            tr.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm"
                           name="facturas[${i}][factura]"
                           placeholder="Ej: F0090" required>
                </td>
                <td>
                    <input type="text" inputmode="numeric" maxlength="11"
                           class="form-control form-control-sm ruc-input"
                           name="facturas[${i}][ruc]"
                           placeholder="11 dígitos" required
                           style="font-family:'Courier New',monospace">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm monto-input"
                           name="facturas[${i}][monto]"
                           placeholder="0.00" step="0.01" min="0.01" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">
                        <i class="mdi mdi-minus"></i>
                    </button>
                </td>`;
            tbody.appendChild(tr);
            tr.querySelector('input').focus();
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.monto-input').forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            document.getElementById('total-display').textContent = total.toFixed(2);
        }

        addFacturaRow();

        document.getElementById('btn-add-factura').addEventListener('click', addFacturaRow);

        document.getElementById('tbody-facturas').addEventListener('input', e => {
            if (e.target.classList.contains('monto-input')) recalcTotal();
            if (e.target.classList.contains('ruc-input')) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 11);
            }
        });

        document.getElementById('tbody-facturas').addEventListener('click', e => {
            const btn = e.target.closest('.btn-remove-row');
            if (!btn) return;
            if (document.querySelectorAll('#tbody-facturas .factura-row').length <= 1) {
                toast('Debes tener al menos una factura.', 'error');
                return;
            }
            btn.closest('tr').remove();
            recalcTotal();
        });

        formCrear.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-crear-submit');
            const msg = document.getElementById('msg-crear');
            msg.innerHTML = '';

            if (!document.querySelectorAll('#tbody-facturas .factura-row').length) {
                msg.innerHTML = '<div class="alert alert-danger py-2">Agrega al menos una factura.</div>';
                return;
            }

            const errSubida = validarSubida('archivos-crear');
            if (errSubida) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${errSubida}</div>`;
                return;
            }

            setBtn(btn, true);
            try {
                const dt = _acumulados['archivos-crear'];

                const fd = new FormData(this);
                fd.delete('archivos[]');
                if (dt && dt.files.length) {
                    for (const f of dt.files) fd.append('archivos[]', f);
                }

                const data = await apiFetch(BASE, { method: 'POST', body: fd });

                toast(data.message);
                this.reset();
                document.getElementById('c-codigo').value = '';
                document.getElementById('tbody-facturas').innerHTML = '';
                document.getElementById('preview-crear').innerHTML = '';
                _acumulados['archivos-crear'] = new DataTransfer();
                updatePeso('archivos-crear');
                facturaIdx = 0;
                addFacturaRow();
                recalcTotal();

                loadHistorial(1);
                loadKpiSemanal();
            } catch (err) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
            } finally {
                setBtn(btn, false);
            }
        });
    }

    /* ══ SILVIA: Aplicar voucher ════════════════════════════ */
    if (ES_SILVIA) {
        document.addEventListener('click', async e => {
            const btn = e.target.closest('.btn-aplicar');
            if (!btn) return;

            const id     = btn.dataset.id;
            const codigo = btn.dataset.codigo;
            if (!confirm(`¿Aplicar el voucher ${codigo}?`)) return;

            setBtn(btn, true);
            try {
                const data = await apiFetch(`${BASE}/${id}/aplicar`, { method: 'PATCH', body: JSON.stringify({}) });
                toast(data.message);

                btn.closest('.card.pending-card')?.remove();

                const badgeCount = document.querySelector('.card-header .badge.bg-warning');
                if (badgeCount) {
                    const n = parseInt(badgeCount.textContent) - 1;
                    badgeCount.textContent = `${n} pendiente(s)`;
                }

                loadHistorial(histPage);
                loadKpiSemanal();
            } catch (err) {
                toast(err.message, 'error');
                setBtn(btn, false);
            }
        });
    }

    /* ══ Ver detalle ════════════════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-ver-detalle');
        if (!btn) return;

        const id     = btn.dataset.id;
        const codigo = btn.dataset.codigo;
        document.getElementById('det-codigo').textContent = codigo;
        document.getElementById('det-body').innerHTML =
            '<div class="py-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        new bootstrap.Modal(document.getElementById('modalDetalle')).show();

        try {
            const data  = await apiFetch(`${BASE}/${id}/facturas`);
            const rows  = data.facturas.map(f =>
                `<tr><td>${f.factura}</td><td style="font-family:'Courier New',monospace">${f.ruc ?? '—'}</td><td class="text-end">S/ ${f.monto}</td></tr>`
            ).join('');
            const total = data.facturas.reduce((s, f) => s + parseFloat(f.monto), 0);

            let archivosHtml = '';
            if (data.archivos && data.archivos.length) {
                const items = data.archivos.map(a => {
                    const isImg = a.mime && a.mime.startsWith('image/');
                    const icon  = a.mime && a.mime.includes('pdf')
                        ? '<i class="mdi mdi-file-pdf-box text-danger fs-4"></i>'
                        : '<i class="mdi mdi-file-excel text-success fs-4"></i>';
                    return `
                        <div class="col-6 col-md-4">
                            <a href="${a.url}" target="_blank" class="text-decoration-none">
                                <div class="border rounded p-2 text-center bg-light">
                                    ${isImg
                                        ? `<img src="${a.url}" class="img-fluid rounded mb-1" style="max-height:80px;object-fit:cover">`
                                        : `<div class="py-2">${icon}</div>`}
                                    <div class="text-truncate text-muted" style="font-size:10px" title="${a.name}">${a.name}</div>
                                    <div class="text-muted" style="font-size:10px">${a.size}</div>
                                </div>
                            </a>
                        </div>`;
                }).join('');
                archivosHtml = `
                    <div class="mt-3">
                        <p class="fw-semibold mb-2" style="font-size:.85rem">
                            <i class="mdi mdi-paperclip me-1 text-muted"></i>Adjuntos (${data.archivos.length})
                        </p>
                        <div class="row g-2">${items}</div>
                    </div>`;
            }

            // Bloque de revisión
            const estLabels = { conforme: 'Conforme', conforme_observado: 'Conforme observado', rechazado: 'Rechazado' };
            let revHtml;
            if (data.revision_estado) {
                let adjHtml = '';
                if (data.revision_archivos && data.revision_archivos.length) {
                    adjHtml = '<div class="d-flex flex-wrap gap-2 mt-2">' + data.revision_archivos.map(a =>
                        `<a href="${a.preview_url}" target="_blank" class="badge bg-light text-dark border text-decoration-none"><i class="mdi mdi-paperclip me-1"></i>${a.name}</a>`
                    ).join('') + '</div>';
                }
                revHtml = `
                    <div class="mt-3 p-3 rounded border">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <span class="fw-semibold" style="font-size:.85rem"><i class="mdi mdi-clipboard-check-outline me-1 text-muted"></i>Revisión de finanzas:</span>
                            <span class="badge bg-${data.conformidad_color}">${estLabels[data.revision_estado] ?? data.revision_estado} · ${data.conformidad_label}</span>
                        </div>
                        ${data.revision_motivo ? `<div class="text-muted" style="font-size:.82rem"><strong>Motivo:</strong> ${data.revision_motivo}</div>` : ''}
                        <div class="text-muted mt-1" style="font-size:.75rem">Por ${data.revision_revisor ?? '—'} · ${data.revision_at ?? ''}</div>
                        ${adjHtml}
                    </div>`;
            } else {
                revHtml = `<div class="mt-3 text-muted small"><i class="mdi mdi-information-outline me-1"></i>Sin revisión de finanzas aún · <span class="badge bg-dark">Pendiente rev.</span></div>`;
            }

            let revisarBtnHtml = '';
            if (data.puede_revisar) {
                revisarBtnHtml = `
                    <div class="mt-3 text-end">
                        <button class="btn btn-warning btn-sm fw-bold" data-bs-dismiss="modal"
                                onclick="__abrirRevision('${data.id}','${data.codigo}')">
                            <i class="mdi mdi-clipboard-check-outline me-1"></i>Revisar voucher
                        </button>
                    </div>`;
            }

            document.getElementById('det-body').innerHTML = `
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Factura</th><th>RUC</th><th class="text-end">Monto</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="2">Total</td>
                            <td class="text-end">S/ ${total.toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
                ${archivosHtml}
                ${revHtml}
                ${revisarBtnHtml}`;
        } catch (err) {
            document.getElementById('det-body').innerHTML =
                `<div class="alert alert-danger">${err.message}</div>`;
        }
    });

    /* ══ Reenviar notificación ══════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-reenviar');
        if (!btn) return;
        setBtn(btn, true);
        try {
            const data = await apiFetch(`${BASE}/${btn.dataset.id}/enviar`, {
                method: 'PATCH', body: JSON.stringify({})
            });
            toast(data.message);
        } catch (err) {
            toast(err.message, 'error');
        } finally {
            setBtn(btn, false);
        }
    });

    /* ══ Eliminar ═══════════════════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;
        if (!confirm('¿Eliminar este voucher? Esta acción no se puede deshacer.')) return;

        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            await apiFetch(`${BASE}/${id}`, { method: 'DELETE', body: JSON.stringify({}) });
            toast('Voucher eliminado.');
            const pendCard = document.querySelector(`.btn-aplicar[data-id="${id}"]`)?.closest('.card.pending-card');
            if (pendCard) pendCard.remove();
            loadHistorial(histPage);
            loadKpiSemanal();
        } catch (err) {
            toast(err.message, 'error');
            btn.disabled = false;
        }
    });

    /* ══ Historial AJAX + filtros + paginación ══════════════ */
    let histPage = 1, histLastPage = 1;

    function filtrosHist() {
        const p = new URLSearchParams();
        const sede = document.getElementById('f-sede')?.value;
        const est  = document.getElementById('f-estado')?.value;
        const conf = document.getElementById('f-conformidad')?.value;
        const fec  = document.getElementById('f-fecha')?.value;
        if (sede) p.set('sede', sede);
        if (est)  p.set('estado', est);
        if (conf) p.set('conformidad', conf);
        if (fec)  p.set('fecha', fec);
        return p;
    }

    function demoraHtml(v) {
        if (v.demora === null || v.demora === undefined) return '<span class="text-muted">—</span>';
        const cls = v.demora <= 3 ? 'kpi-ok' : (v.demora <= 7 ? 'kpi-warn' : 'kpi-bad');
        return `<span class="${cls}">${v.demora}d</span>`;
    }

    function renderHistRow(v) {
        const aplicadoHtml = v.aplicado_at
            ? `<span class="text-success fw-semibold">${v.aplicado_at}</span>`
            : `<span class="text-muted">—</span>`;
        const solicitanteCol = VER_TODO ? `<td style="font-size:.83rem">${v.creator_name ?? '—'}</td>` : '';
        const revisarBtn = v.puede_revisar
            ? `<button class="btn-outline-warning btn btn-sm btn-revisar" title="Revisar" data-id="${v.id}" data-codigo="${v.codigo}"><i class="mdi mdi-clipboard-check-outline"></i></button>` : '';
        const reenviarBtn = v.puede_reenviar
            ? `<button class="btn-outline-info btn btn-sm btn-reenviar" title="Reenviar" data-id="${v.id}"><i class="mdi-email-send-outline mdi"></i></button>` : '';
        const eliminarBtn = v.puede_eliminar
            ? `<button class="btn-outline-danger btn btn-sm btn-eliminar" title="Eliminar" data-id="${v.id}"><i class="mdi-trash-can-outline mdi"></i></button>` : '';

        return `<tr>
            <td class="ps-3 text-muted" style="font-size:.8rem">${v.id}</td>
            <td><span class="codigo-badge">${v.codigo}</span></td>
            <td>${statusBadge(v.status)}</td>
            <td><span class="sede-badge">${v.sede}</span></td>
            ${solicitanteCol}
            <td class="text-end fw-semibold">S/ ${v.total}</td>
            <td style="font-size:.82rem;white-space:nowrap">${v.solicitado_at ?? '—'}</td>
            <td style="font-size:.82rem;white-space:nowrap">${aplicadoHtml}</td>
            <td class="text-center">${demoraHtml(v)}</td>
            <td class="text-center"><span class="badge bg-${v.conformidad_color}">${v.conformidad_label}</span></td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button class="btn-outline-secondary btn btn-sm btn-ver-detalle" title="Ver detalle" data-id="${v.id}" data-codigo="${v.codigo}">
                        <i class="mdi-eye-outline mdi"></i>
                    </button>
                    ${revisarBtn}${reenviarBtn}${eliminarBtn}
                </div>
            </td>
        </tr>`;
    }

    async function loadHistorial(page = 1) {
        const tbody = document.getElementById('tbody-historial');
        const cols  = VER_TODO ? 11 : 10;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="py-4 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const p = filtrosHist();
        p.set('page', page);
        try {
            const data = await apiFetch(`${BASE}/historial?${p.toString()}`);
            histPage     = data.current_page;
            histLastPage = data.last_page || 1;
            if (!data.data.length) {
                tbody.innerHTML = `<tr><td colspan="${cols}" class="py-5 text-muted text-center">
                    <i class="mdi-receipt-outline mdi" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-0">No hay vouchers registrados.</p></td></tr>`;
            } else {
                tbody.innerHTML = data.data.map(renderHistRow).join('');
            }
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
    window.__loadHistorial = () => loadHistorial(histPage);

    document.getElementById('hist-prev').addEventListener('click', () => { if (histPage > 1) loadHistorial(histPage - 1); });
    document.getElementById('hist-next').addEventListener('click', () => { if (histPage < histLastPage) loadHistorial(histPage + 1); });
    ['f-sede', 'f-estado', 'f-conformidad', 'f-fecha'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => loadHistorial(1));
    });
    document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
        ['f-sede', 'f-estado', 'f-conformidad', 'f-fecha'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        loadHistorial(1);
    });

    /* ══ Poblado de sedes (filtro + KPI) ════════════════════ */
    async function populateSedes() {
        if (!VER_TODO) return;
        try {
            const sedes = await apiFetch(`${BASE}/sedes`);
            const opts  = sedes.map(s => `<option value="${s}">${s}</option>`).join('');
            document.getElementById('f-sede')?.insertAdjacentHTML('beforeend', opts);
            document.getElementById('kpi-sede')?.insertAdjacentHTML('beforeend', opts);
        } catch (e) { /* noop */ }
    }

    /* ══ KPI semanal de conformidad ═════════════════════════ */
    let kpiChart = null;
    async function loadKpiSemanal() {
        const canvas = document.getElementById('kpiSemanalChart');
        if (!canvas) return;
        const sede = document.getElementById('kpi-sede')?.value || '';
        const p = new URLSearchParams();
        if (sede) p.set('sede', sede);
        try {
            const data = await apiFetch(`${BASE}/kpi-semanal?${p.toString()}`);

            const valEl = document.getElementById('kpi-actual-valor');
            const detEl = document.getElementById('kpi-actual-detalle');
            if (data.promedio_actual === null) {
                valEl.textContent = '—';
                valEl.className   = 'fw-bold text-muted';
                detEl.textContent = 'Sin revisiones esta semana';
            } else {
                const c = data.promedio_actual >= 100 ? 'text-success'
                        : data.promedio_actual >= 80  ? 'text-info'
                        : data.promedio_actual >= 50  ? 'text-warning' : 'text-danger';
                valEl.textContent = data.promedio_actual + '%';
                valEl.className   = 'fw-bold ' + c;
                detEl.textContent = `${data.revisados_actual} voucher(s) revisado(s)`;
            }

            const color = v => v === null ? 'rgba(148,163,184,.4)'
                             : v >= 100 ? 'rgba(16,185,129,.85)'
                             : v >= 80  ? 'rgba(59,130,246,.85)'
                             : v >= 50  ? 'rgba(234,179,8,.85)' : 'rgba(239,68,68,.85)';

            if (kpiChart) kpiChart.destroy();
            kpiChart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Conformidad %',
                        data: data.data,
                        backgroundColor: data.data.map(color),
                        borderRadius: 6,
                        maxBarThickness: 46,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: c => c.parsed.y === null ? 'Sin datos' : c.parsed.y + '%' } }
                    }
                }
            });
        } catch (e) { /* noop */ }
    }
    document.getElementById('kpi-sede')?.addEventListener('change', loadKpiSemanal);

    /* ══ Revisión de finanzas ═══════════════════════════════ */
    if (ES_REVISOR) {
        setupDropZone('drop-zone-rev', 'archivos-rev', 'preview-rev');
        const modalRev = new bootstrap.Modal(document.getElementById('modalRevision'));

        function abrirRevision(id, codigo) {
            const form = document.getElementById('formRevision');
            form.reset();
            document.getElementById('rev-id').value = id;
            document.getElementById('rev-codigo').textContent = codigo;
            document.getElementById('rev-msg').innerHTML = '';
            document.getElementById('rev-penalidad-wrap').style.display = 'none';
            document.getElementById('preview-rev').innerHTML = '';
            _acumulados['archivos-rev'] = new DataTransfer();
            updatePeso('archivos-rev');
            modalRev.show();
        }
        window.__abrirRevision = abrirRevision;

        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-revisar');
            if (!btn) return;
            abrirRevision(btn.dataset.id, btn.dataset.codigo);
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
            const btn = document.getElementById('btn-rev-submit');
            const msg = document.getElementById('rev-msg');
            msg.innerHTML = '';

            const est = document.querySelector('#formRevision input[name="estado"]:checked')?.value;
            if (!est) { msg.innerHTML = '<div class="alert alert-danger py-2">Selecciona un resultado.</div>'; return; }

            const errSubidaRev = validarSubida('archivos-rev');
            if (errSubidaRev) { msg.innerHTML = `<div class="alert alert-danger py-2">${errSubidaRev}</div>`; return; }

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
                loadKpiSemanal();
            } catch (err) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
            } finally {
                setBtn(btn, false);
            }
        });
    }

    /* ══ Init ═══════════════════════════════════════════════ */
    populateSedes();
    loadHistorial(1);
    loadKpiSemanal();

})();
</script>
@endsection
