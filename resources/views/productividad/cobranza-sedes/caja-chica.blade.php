@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="mdi mdi-cash-multiple me-2 text-success"></i>Caja Chica Sedes
                            </h4>
                            <p class="text-muted mb-0 small">Reporte semanal — límite: sábado 2:00 PM</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6">Semana {{ $semanaNumero }}/{{ $anio }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- ── FILA 1: Countdown + Estado + KPI Info ─────────────── --}}
        <div class="row mb-4">
            {{-- Countdown --}}
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm" id="card-countdown">
                    <div class="card-body text-center py-4">
                        <div class="mb-2">
                            <i class="mdi mdi-timer-outline" style="font-size:2.5rem;" id="countdown-icon"></i>
                        </div>
                        <h6 class="text-muted text-uppercase fw-bold small mb-1">Tiempo restante</h6>
                        <div id="countdown-display" class="fw-bold mb-1" style="font-size:2rem;font-family:'Courier New',monospace;letter-spacing:2px;">--:--:--</div>
                        <div id="countdown-subtitle" class="small text-muted">Cargando...</div>
                        <hr class="my-3">
                        <div class="small text-muted">Límite: <strong>Sábado 2:00 PM</strong></div>
                    </div>
                </div>
            </div>

            {{-- Estado semana actual (sede) --}}
            @auth
            @if ((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h6 class="text-muted text-uppercase fw-bold small mb-3">Estado — Semana Actual</h6>
                        @php $enviado = !is_null($reporteSemanaActual->fecha_envio_original); @endphp
                        <div class="d-flex align-items-center mb-2">
                            <i class="mdi mdi-map-marker me-2 text-success"></i>
                            <strong>{{ $reporteSemanaActual->sede }}</strong>
                        </div>
                        @if ($enviado)
                            <div class="alert alert-success py-2 mb-2">
                                <i class="mdi mdi-check-circle me-1"></i>
                                Enviado el {{ $reporteSemanaActual->fecha_envio_original?->setTimezone('America/Lima')->format('d/m H:i') }}
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">KPI:</span>
                                <span class="badge bg-{{ $reporteSemanaActual->kpiColor() }} fs-6">
                                    {{ $reporteSemanaActual->kpiLabel() }}
                                </span>
                            </div>
                            @if ($reporteSemanaActual->editado_tarde)
                            <div class="alert alert-warning py-1 mt-2 mb-0 small">
                                <i class="mdi mdi-alert me-1"></i>Editado con atraso — KPI ajustado
                            </div>
                            @endif
                        @else
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="mdi mdi-clock-alert me-1"></i>Pendiente de envío
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endauth

            {{-- KPI Info --}}
            <div class="col-lg-4 col-md-12 mb-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-4">
                        <h6 class="text-muted text-uppercase fw-bold small mb-3">Escala KPI</h6>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center p-3 rounded"
                                 style="background:#d1fae5;border-left:4px solid #10b981;">
                                <div>
                                    <div class="fw-bold text-success">Enviado el sábado</div>
                                    <div class="small text-muted">Cualquier hora del sábado</div>
                                </div>
                                <span class="badge bg-success fs-5">100%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 rounded"
                                 style="background:#fee2e2;border-left:4px solid #ef4444;">
                                <div>
                                    <div class="fw-bold text-danger">Otro día</div>
                                    <div class="small text-muted">Antes o después del sábado</div>
                                </div>
                                <span class="badge bg-danger fs-5">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FILA 2: Resumen por sede (admin/superadmin) ────────── --}}
        @if ($resumenSedes && $resumenSedes->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold">
                            <i class="mdi mdi-view-dashboard-outline me-2 text-success"></i>
                            Estado Sedes — Semana {{ $semanaNumero }}/{{ $anio }}
                        </h5>
                        <div class="d-flex gap-2 small text-muted align-items-center">
                            <span class="badge bg-success">Enviado</span>
                            <span class="badge bg-danger">Pendiente</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Responsable</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Hora Envío</th>
                                        <th class="text-center">KPI</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resumenSedes as $fila)
                                    <tr class="{{ $fila['enviado'] ? '' : 'table-danger bg-opacity-25' }}">
                                        <td><strong>{{ $fila['sede'] }}</strong></td>
                                        <td class="text-muted small">{{ $fila['usuario'] }}</td>
                                        <td class="text-center">
                                            @if ($fila['enviado'])
                                                <span class="badge bg-success"><i class="mdi mdi-check me-1"></i>Enviado</span>
                                            @else
                                                <span class="badge bg-danger"><i class="mdi mdi-clock-alert me-1"></i>Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $fila['fecha_envio'] ? $fila['fecha_envio'].' hrs' : '—' }}
                                            @if ($fila['editado_tarde'])
                                                <br><small class="text-warning"><i class="mdi mdi-pencil"></i> Editado tarde</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (!is_null($fila['kpi']))
                                                <span class="badge bg-{{ $fila['kpi_color'] }} fs-6">{{ $fila['kpi_label'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($fila['reporte_id'])
                                                <button class="btn btn-sm btn-outline-success py-0 px-2"
                                                        onclick="verReporte({{ $fila['reporte_id'] }})" title="Ver detalle">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            @else
                                                <span class="text-muted small">Sin reporte</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex gap-4 small text-muted py-2 px-4">
                        <span><i class="mdi mdi-check-circle text-success"></i> Enviados: <strong>{{ $resumenSedes->where('enviado', true)->count() }}</strong></span>
                        <span><i class="mdi mdi-clock-alert text-danger"></i> Pendientes: <strong>{{ $resumenSedes->where('enviado', false)->count() }}</strong></span>
                        <span><i class="mdi mdi-chart-line text-success"></i> KPI promedio:
                            <strong>@php $kpisValidos = $resumenSedes->whereNotNull('kpi')->pluck('kpi'); echo $kpisValidos->count() ? number_format($kpisValidos->avg(), 1).'%' : '—'; @endphp</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── FILA 3: Formulario envío/edición (sede) ─────────────── --}}
        @auth
        @if ((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold">
                            <i class="mdi mdi-upload me-2 text-success"></i>
                            @if ($reporteSemanaActual->fecha_envio_original) Editar Reporte @else Enviar Reporte @endif
                            — Semana {{ $semanaNumero }}/{{ $anio }}
                        </h5>
                        <span class="badge bg-light text-dark border">Sede: <strong>{{ $reporteSemanaActual->sede }}</strong></span>
                    </div>
                    <div class="card-body">
                        @if ($reporteSemanaActual->fecha_envio_original)
                        {{-- EDICIÓN --}}
                        <form id="form-editar-reporte" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="reporte_id" value="{{ $reporteSemanaActual->id }}">

                            @if (count($reporteSemanaActual->archivos ?? []) > 0)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Archivos enviados</label>
                                <div class="row g-2" id="archivos-existentes">
                                    @foreach ($reporteSemanaActual->archivos as $idx => $archivo)
                                    <div class="col-md-4 col-sm-6" id="archivo-card-{{ $idx }}">
                                        <div class="border rounded p-2 d-flex align-items-center gap-2 bg-light">
                                            <i class="mdi mdi-{{ str_contains($archivo['mime'] ?? '', 'image') ? 'image' : 'file-excel' }} text-success fs-5"></i>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="small fw-semibold text-truncate" title="{{ $archivo['name'] }}">{{ $archivo['name'] }}</div>
                                                <div class="text-muted" style="font-size:11px;">{{ number_format(($archivo['size'] ?? 0)/1024, 1) }} KB</div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('productividad.cobranza-sedes.caja-chica.download', [$reporteSemanaActual->id, $idx]) }}"
                                                   class="btn btn-sm btn-outline-success py-0 px-1">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                                                        onclick="marcarEliminar({{ $idx }}, this)">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="eliminar_indices[]" id="eliminar-{{ $idx }}" value="{{ $idx }}" disabled>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Agregar archivos adicionales</label>
                                <div class="drop-zone" id="drop-zone-edit">
                                    <i class="mdi mdi-cloud-upload-outline" style="font-size:2rem;color:#94a3b8;"></i>
                                    <p class="mb-1 mt-2 text-muted small">Arrastra Excel, PDF o fotos aquí</p>
                                    <p class="text-muted" style="font-size:11px;">XLSX, XLS, CSV, PDF, imágenes — máx. 20 MB c/u</p>
                                    <input type="file" id="archivos-edit" name="archivos[]"
                                           multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                                    <div class="d-flex gap-2 justify-content-center mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="document.getElementById('archivos-edit').click()">
                                            <i class="mdi mdi-paperclip me-1"></i>Seleccionar archivos
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="abrirCamara('archivos-edit','preview-edit')">
                                            <i class="mdi mdi-camera me-1"></i>Tomar foto
                                        </button>
                                    </div>
                                </div>
                                <div id="preview-edit" class="row g-2 mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" rows="2" class="form-control" placeholder="Opcional...">{{ $reporteSemanaActual->notas }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-warning fw-bold" id="btn-editar">
                                <i class="mdi mdi-content-save-edit me-1"></i>Guardar Cambios
                            </button>
                            <div id="msg-editar" class="mt-2"></div>
                        </form>
                        @else
                        {{-- ENVÍO INICIAL --}}
                        <form id="form-enviar-reporte" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Archivos del reporte <span class="text-danger">*</span></label>
                                <div class="drop-zone" id="drop-zone-nuevo">
                                    <i class="mdi mdi-cloud-upload-outline" style="font-size:2.5rem;color:#94a3b8;"></i>
                                    <p class="mb-1 mt-2 text-muted">Arrastra tu Excel, PDF o fotos aquí</p>
                                    <p class="text-muted small">XLSX, XLS, CSV, PDF, imágenes — máx. 20 MB c/u</p>
                                    <input type="file" id="archivos-nuevo" name="archivos[]"
                                           multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="document.getElementById('archivos-nuevo').click()">
                                            <i class="mdi mdi-paperclip me-1"></i>Seleccionar archivos
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="abrirCamara('archivos-nuevo','preview-nuevo')">
                                            <i class="mdi mdi-camera me-1"></i>Tomar foto
                                        </button>
                                    </div>
                                </div>
                                <div id="preview-nuevo" class="row g-2 mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" rows="2" class="form-control" placeholder="Opcional..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success fw-bold" id="btn-enviar">
                                <i class="mdi mdi-send me-1"></i>Enviar y Registrar
                            </button>
                            <div id="msg-enviar" class="mt-2"></div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endauth

        {{-- ── FILA 4: Gráfico KPI ─────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-chart-line me-2 text-success"></i>KPI por Sede</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <label class="mb-0 small text-muted me-1">Semanas:</label>
                            <select id="filtro-semanas" class="form-select form-select-sm" style="width:80px;">
                                <option value="4">4</option>
                                <option value="8" selected>8</option>
                                <option value="12">12</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:300px;">
                            <canvas id="kpi-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FILA 5: Historial ───────────────────────────────────── --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-history me-2 text-success"></i>Historial de Reportes</h5>
                        <div id="historial-loader" class="spinner-border spinner-border-sm text-success d-none"></div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th><th>Semana</th><th>Período</th>
                                        <th>Fecha Límite</th><th>Fecha Envío</th>
                                        <th>KPI</th><th>Estado</th><th>Archivos</th><th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historial-body">
                                    <tr><td colspan="9" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-success me-2"></div>Cargando...
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver Reporte --}}
<div class="modal fade" id="modal-reporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="mdi mdi-cash-multiple me-2 text-success"></i>
                    <span id="modal-titulo">Detalle del Reporte</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="text-center py-4"><div class="spinner-border text-success"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cámara --}}
<div class="modal fade" id="modal-camara" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="mdi mdi-camera me-2 text-success"></i>Tomar Foto</h5>
                <button type="button" class="btn-close" onclick="cerrarCamara()"></button>
            </div>
            <div class="modal-body text-center p-3">
                <video id="camara-video" autoplay playsinline
                       style="width:100%;border-radius:10px;background:#000;max-height:380px;object-fit:cover;"></video>
                <canvas id="camara-canvas" class="d-none"></canvas>
                <div id="camara-preview-wrap" class="d-none mt-2">
                    <img id="camara-preview-img" src="" alt="Captura"
                         style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;">
                </div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button id="btn-capturar" class="btn btn-success" onclick="capturarFoto()">
                    <i class="mdi mdi-camera me-1"></i>Capturar
                </button>
                <button id="btn-retomar" class="btn btn-outline-secondary d-none" onclick="retomarFoto()">
                    <i class="mdi mdi-refresh me-1"></i>Retomar
                </button>
                <button id="btn-usar-foto" class="btn btn-success d-none" onclick="usarFoto()">
                    <i class="mdi mdi-check me-1"></i>Usar esta foto
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.drop-zone { border:2px dashed #cbd5e1;border-radius:12px;padding:32px 20px;text-align:center;background:#f8fafc;transition:border-color .2s,background .2s;cursor:pointer; }
.drop-zone.dragover { border-color:#10b981;background:#ecfdf5; }
.preview-thumb { position:relative;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#f8fafc; }
.preview-thumb img { width:100%;height:80px;object-fit:cover; }
.preview-thumb .preview-remove { position:absolute;top:4px;right:4px;width:22px;height:22px;background:#ef4444;border:none;border-radius:50%;color:#fff;font-size:12px;display:flex;align-items:center;justify-content:center;cursor:pointer; }
.preview-thumb .preview-name { font-size:10px;padding:2px 4px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ROUTES = {
    store:    "{{ route('productividad.cobranza-sedes.caja-chica.store') }}",
    historial:"{{ route('productividad.cobranza-sedes.caja-chica.historial') }}",
    kpiData:  "{{ route('productividad.cobranza-sedes.caja-chica.kpi-data') }}",
    base:     "{{ url('/productividad/cobranza-sedes/caja-chica') }}",
};
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const urlShow    = (id)      => `${ROUTES.base}/${id}/show`;
const urlUpdate  = (id)      => `${ROUTES.base}/${id}`;
const urlPreview = (id, idx) => `${ROUTES.base}/${id}/preview/${idx}`;
const urlDownload= (id, idx) => `${ROUTES.base}/${id}/download/${idx}`;

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...(options.headers ?? {}) },
        ...options,
    });
    const ct = res.headers.get('Content-Type') ?? '';
    if (!ct.includes('application/json')) throw new Error(`Error ${res.status}: respuesta inesperada del servidor.`);
    const data = await res.json();
    if (!res.ok) {
        const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.error ?? data.message ?? `Error ${res.status}`);
        throw new Error(msg);
    }
    return data;
}

// ── Countdown ──────────────────────────────────────────────────────
@php
    $limiteTs = \Carbon\Carbon::parse(\App\Models\ReporteCajaChica::datosSemanActual()[4])->setTimezone('America/Lima')->timestamp * 1000;
@endphp
const DEADLINE_TS = {{ $limiteTs }};

function actualizarCountdown() {
    const diffMs  = DEADLINE_TS - Date.now();
    const display = document.getElementById('countdown-display');
    const sub     = document.getElementById('countdown-subtitle');
    const icon    = document.getElementById('countdown-icon');
    const card    = document.getElementById('card-countdown');
    if (diffMs <= 0) {
        display.textContent = 'VENCIDO'; display.style.color = '#dc2626';
        sub.textContent = 'El plazo ya venció';
        icon.className  = 'mdi mdi-alert-circle-outline text-danger';
        card.classList.add('border-danger'); return;
    }
    const h = String(Math.floor(diffMs/3600000)).padStart(2,'0');
    const m = String(Math.floor((diffMs%3600000)/60000)).padStart(2,'0');
    const s = String(Math.floor((diffMs%60000)/1000)).padStart(2,'0');
    display.textContent = `${h}:${m}:${s}`;
    if (diffMs <= 3600000) { display.style.color='#dc2626'; icon.className='mdi mdi-timer-alert-outline text-danger'; card.classList.add('border-danger'); sub.textContent='Menos de 1 hora — ¡envía ahora!'; }
    else if (diffMs <= 7200000) { display.style.color='#d97706'; icon.className='mdi mdi-timer-outline text-warning'; sub.textContent='Menos de 2 horas'; }
    else { display.style.color='#10b981'; icon.className='mdi mdi-timer-outline text-success'; sub.textContent='Sábado, 2:00 PM hora Lima'; }
}
actualizarCountdown();
setInterval(actualizarCountdown, 1000);

// ── Drop zones ─────────────────────────────────────────────────────
function setupDropZone(zoneId, inputId, previewId) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    if (!zone || !input) return;
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('dragover'); agregarArchivos(e.dataTransfer.files, input, document.getElementById(previewId)); });
    input.addEventListener('change', () => agregarArchivos(input.files, input, document.getElementById(previewId)));
}
function agregarArchivos(files, input, preview) {
    const dt = new DataTransfer();
    if (input.files) for (const f of input.files) dt.items.add(f);
    for (const f of files) dt.items.add(f);
    input.files = dt.files;
    renderPreview(input.files, preview, input);
}
function renderPreview(files, preview, input) {
    preview.innerHTML = '';
    for (let i = 0; i < files.length; i++) {
        const f = files[i]; const col = document.createElement('div'); col.className = 'col-6 col-md-3 col-lg-2';
        const isImg = f.type.startsWith('image/');
        col.innerHTML = `<div class="preview-thumb p-1">${isImg ? `<img src="${URL.createObjectURL(f)}" alt="">` : `<div class="d-flex align-items-center justify-content-center" style="height:80px;font-size:2rem;"><i class="mdi mdi-file-excel text-success"></i></div>`}<button type="button" class="preview-remove" onclick="quitarArchivo(${i},this)">×</button><div class="preview-name text-muted">${f.name}</div></div>`;
        preview.appendChild(col);
    }
}
function quitarArchivo(idx, btn) {
    const preview = btn.closest('[id^="preview"]');
    const input   = document.getElementById(preview.id.replace('preview','archivos'));
    const dt = new DataTransfer();
    for (let i = 0; i < input.files.length; i++) if (i !== idx) dt.items.add(input.files[i]);
    input.files = dt.files; renderPreview(input.files, preview, input);
}
setupDropZone('drop-zone-nuevo','archivos-nuevo','preview-nuevo');
setupDropZone('drop-zone-edit','archivos-edit','preview-edit');

function marcarEliminar(idx, btn) {
    const hidden = document.getElementById('eliminar-' + idx);
    const card   = document.getElementById('archivo-card-' + idx);
    if (hidden.disabled) { hidden.disabled=false; card.style.opacity='0.4'; btn.innerHTML='<i class="mdi mdi-undo"></i>'; btn.classList.replace('btn-outline-danger','btn-outline-secondary'); }
    else { hidden.disabled=true; card.style.opacity='1'; btn.innerHTML='<i class="mdi mdi-trash-can-outline"></i>'; btn.classList.replace('btn-outline-secondary','btn-outline-danger'); }
}

// ── Envío ──────────────────────────────────────────────────────────
const formEnviar = document.getElementById('form-enviar-reporte');
if (formEnviar) {
    formEnviar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-enviar');
        const msg = document.getElementById('msg-enviar');
        btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Enviando...'; msg.innerHTML='';
        try {
            const data = await apiFetch(ROUTES.store, { method:'POST', body: new FormData(this) });
            msg.innerHTML = `<div class="alert alert-success py-2">${data.message}</div>`;
            setTimeout(() => location.reload(), 1500);
        } catch(err) { msg.innerHTML=`<div class="alert alert-danger py-2">${err.message}</div>`; }
        finally { btn.disabled=false; btn.innerHTML='<i class="mdi mdi-send me-1"></i>Enviar y Registrar'; }
    });
}

// ── Edición ────────────────────────────────────────────────────────
const formEditar = document.getElementById('form-editar-reporte');
if (formEditar) {
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-editar');
        const msg = document.getElementById('msg-editar');
        const reporteId = this.querySelector('[name="reporte_id"]').value;
        btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'; msg.innerHTML='';
        try {
            const data = await apiFetch(urlUpdate(reporteId), { method:'POST', body: new FormData(this) });
            msg.innerHTML=`<div class="alert alert-${data.tardio?'warning':'success'} py-2">${data.message}</div>`;
            setTimeout(()=>location.reload(),1800);
        } catch(err) { msg.innerHTML=`<div class="alert alert-danger py-2">${err.message}</div>`; }
        finally { btn.disabled=false; btn.innerHTML='<i class="mdi mdi-content-save-edit me-1"></i>Guardar Cambios'; }
    });
}

// ── Historial ──────────────────────────────────────────────────────
async function cargarHistorial() {
    const loader = document.getElementById('historial-loader');
    loader.classList.remove('d-none');
    try {
        const data = await apiFetch(ROUTES.historial);
        const rows = data.data ?? [];
        if (!rows.length) { document.getElementById('historial-body').innerHTML='<tr><td colspan="9" class="text-center text-muted py-4">Sin registros.</td></tr>'; return; }
        document.getElementById('historial-body').innerHTML = rows.map(r => `
            <tr>
                <td><strong>${r.sede}</strong></td>
                <td><span class="badge bg-light text-dark border">${r.semana}</span></td>
                <td class="small text-muted">${r.semana_inicio} — ${r.semana_fin}</td>
                <td class="small">${r.fecha_limite ?? '—'}</td>
                <td class="small">${r.fecha_envio ?? '<span class="text-muted">—</span>'}${r.fecha_edicion ? `<br><span class="text-warning small"><i class="mdi mdi-pencil"></i> ${r.fecha_edicion}</span>` : ''}</td>
                <td>${r.kpi !== null ? `<span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>` : '<span class="text-muted">—</span>'}</td>
                <td><span class="badge bg-${{ 'en_tiempo':'success','con_atraso':'danger','pendiente':'secondary','no_enviado':'danger' }[r.estado] ?? 'secondary'}">${r.estado}</span></td>
                <td class="text-center">${r.num_archivos > 0 ? `<span class="badge bg-info">${r.num_archivos}</span>` : '0'}</td>
                <td><button class="btn btn-sm btn-outline-success py-0 px-2" onclick="verReporte(${r.id})"><i class="mdi mdi-eye"></i></button></td>
            </tr>`).join('');
    } catch(err) { document.getElementById('historial-body').innerHTML=`<tr><td colspan="9" class="text-center text-danger py-3">${err.message}</td></tr>`; }
    finally { loader.classList.add('d-none'); }
}
cargarHistorial();

// ── Modal Ver ──────────────────────────────────────────────────────
async function verReporte(id) {
    const modal  = new bootstrap.Modal(document.getElementById('modal-reporte'));
    const body   = document.getElementById('modal-body');
    const titulo = document.getElementById('modal-titulo');
    body.innerHTML='<div class="text-center py-4"><div class="spinner-border text-success"></div></div>';
    titulo.textContent='Detalle'; modal.show();
    try {
        const r = await apiFetch(urlShow(id));
        titulo.textContent = `${r.sede} — ${r.semana}`;
        const imagenes = (r.archivos??[]).filter(a=>a.es_imagen);
        const otros    = (r.archivos??[]).filter(a=>!a.es_imagen);
        let archivosHTML = '';
        if (imagenes.length) {
            archivosHTML += `<div class="row g-2 mb-3">${imagenes.map(a=>`<div class="col-6 col-md-4"><div class="border rounded overflow-hidden position-relative" style="aspect-ratio:4/3;"><img src="${a.preview_url}" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onclick="window.open('${a.preview_url}','_blank')"><div class="position-absolute bottom-0 start-0 end-0 d-flex justify-content-between px-2 py-1" style="background:rgba(0,0,0,0.45);"><span class="text-white small text-truncate" style="max-width:70%;">${a.name}</span><a href="${a.download_url}" class="btn btn-sm btn-light py-0 px-1" download><i class="mdi mdi-download small"></i></a></div></div></div>`).join('')}</div>`;
        }
        otros.forEach(a => {
            const ext = a.name.split('.').pop().toUpperCase();
            const icon = ['XLSX','XLS','CSV'].includes(ext) ? 'mdi-file-excel text-success' : ext==='PDF' ? 'mdi-file-pdf text-danger' : 'mdi-file-document text-primary';
            archivosHTML += `<div class="d-flex align-items-center gap-2 bg-light mb-1 p-2 border rounded"><i class="mdi ${icon} fs-5"></i><span class="flex-grow-1 text-truncate small fw-semibold">${a.name}</span><a href="${a.download_url}" class="btn btn-sm btn-outline-success px-2 py-0" download><i class="mdi mdi-download"></i> Descargar</a></div>`;
        });
        if (!archivosHTML) archivosHTML = '<p class="text-muted">Sin archivos.</p>';
        body.innerHTML = `
            <div class="row g-3 mb-3">
                <div class="col-6"><div class="small text-muted fw-semibold text-uppercase">Sede</div><div class="fw-bold">${r.sede}</div></div>
                <div class="col-6"><div class="small text-muted fw-semibold text-uppercase">Semana</div><div class="fw-bold">${r.semana}</div></div>
                <div class="col-6"><div class="small text-muted fw-semibold text-uppercase">Fecha Límite</div><div>${r.fecha_limite??'—'}</div></div>
                <div class="col-6"><div class="small text-muted fw-semibold text-uppercase">Fecha Envío</div><div>${r.fecha_envio??'<span class="text-muted">No enviado</span>'}</div></div>
                ${r.fecha_edicion?`<div class="col-12"><div class="alert alert-warning py-1 mb-0 small"><i class="mdi mdi-pencil me-1"></i>Editado tardíamente el ${r.fecha_edicion}</div></div>`:''}
                <div class="col-6"><div class="small text-muted fw-semibold text-uppercase">KPI</div><span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span></div>
                ${r.notas?`<div class="col-12"><div class="small text-muted fw-semibold text-uppercase">Notas</div><div class="border rounded p-2 bg-light small">${r.notas}</div></div>`:''}
            </div><hr>
            <h6 class="fw-semibold mb-2">Archivos adjuntos</h6>${archivosHTML}`;
    } catch(err) { body.innerHTML=`<div class="alert alert-danger">${err.message}</div>`; }
}

// ── Gráfico KPI ────────────────────────────────────────────────────
let kpiChart = null;
async function cargarKpiChart(semanas=8) {
    try {
        const data = await apiFetch(`${ROUTES.kpiData}?semanas=${semanas}`);
        const ctx  = document.getElementById('kpi-chart').getContext('2d');
        if (kpiChart) kpiChart.destroy();
        kpiChart = new Chart(ctx, { type:'line', data, options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, scales:{ y:{min:0,max:100,ticks:{callback:v=>v+'%',stepSize:25},grid:{color:'#f1f5f9'}}, x:{grid:{display:false}} }, plugins:{ legend:{position:'bottom',labels:{boxWidth:12,font:{size:11}}}, tooltip:{callbacks:{label:c=>`${c.dataset.label}: ${c.parsed.y!==null?c.parsed.y+'%':'Sin dato'}`}} } } });
    } catch(e) { console.error(e); }
}
cargarKpiChart(8);
document.getElementById('filtro-semanas')?.addEventListener('change', function(){ cargarKpiChart(parseInt(this.value)); });

// ── Cámara ─────────────────────────────────────────────────────────
let _streamActivo=null,_camaraInputId=null,_camaraPreviewId=null,_fotoBlob=null,_modalCamaraInst=null;
async function abrirCamara(inputId, previewId) {
    _camaraInputId=inputId; _camaraPreviewId=previewId; _fotoBlob=null;
    document.getElementById('camara-preview-wrap').classList.add('d-none');
    document.getElementById('camara-video').classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');
    try { _streamActivo=await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'},audio:false}); document.getElementById('camara-video').srcObject=_streamActivo; }
    catch { alert('No se pudo acceder a la cámara. Verifica los permisos.'); return; }
    _modalCamaraInst=new bootstrap.Modal(document.getElementById('modal-camara')); _modalCamaraInst.show();
}
function capturarFoto() {
    const video=document.getElementById('camara-video'), canvas=document.getElementById('camara-canvas');
    canvas.width=video.videoWidth; canvas.height=video.videoHeight;
    canvas.getContext('2d').drawImage(video,0,0);
    canvas.toBlob(blob=>{ _fotoBlob=blob; document.getElementById('camara-preview-img').src=URL.createObjectURL(blob); document.getElementById('camara-preview-wrap').classList.remove('d-none'); video.classList.add('d-none'); document.getElementById('btn-capturar').classList.add('d-none'); document.getElementById('btn-retomar').classList.remove('d-none'); document.getElementById('btn-usar-foto').classList.remove('d-none'); _streamActivo?.getTracks().forEach(t=>t.enabled=false); },'image/jpeg',0.92);
}
function retomarFoto() { _fotoBlob=null; _streamActivo?.getTracks().forEach(t=>t.enabled=true); document.getElementById('camara-preview-wrap').classList.add('d-none'); document.getElementById('camara-video').classList.remove('d-none'); document.getElementById('btn-capturar').classList.remove('d-none'); document.getElementById('btn-retomar').classList.add('d-none'); document.getElementById('btn-usar-foto').classList.add('d-none'); }
function usarFoto() {
    if (!_fotoBlob) return;
    const file=new File([_fotoBlob],`foto-${new Date().toISOString().replace(/[:.]/g,'-')}.jpg`,{type:'image/jpeg'});
    const input=document.getElementById(_camaraInputId), preview=document.getElementById(_camaraPreviewId);
    const dt=new DataTransfer(); if(input.files) for(const f of input.files) dt.items.add(f); dt.items.add(file); input.files=dt.files; renderPreview(input.files,preview,input); cerrarCamara();
}
function cerrarCamara() { _streamActivo?.getTracks().forEach(t=>t.stop()); _streamActivo=null; _modalCamaraInst?.hide(); }
</script>
@endpush
