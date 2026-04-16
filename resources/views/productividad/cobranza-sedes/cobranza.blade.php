@extends('layouts.app')

@section('title', 'Depósito de Efectivo')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="me-2 text-primary mdi mdi-currency-usd"></i>Depósito de Efectivo
                            </h4>
                            <p class="mb-0 text-muted small">Reporte diario — límite: 12:00 PM</p>
                        </div>
                        <div class="text-end">
                            <span class="bg-primary badge fs-6">
                                Hoy {{ $fechaDiaLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        {{-- ══════════════════════════════════════════════
             FILA 1: Countdown + Estado semana actual
        ══════════════════════════════════════════════ --}}
        <div class="mb-4 row">
            {{-- Countdown --}}
            <div class="mb-3 col-lg-4 col-md-6">
                <div class="shadow-sm border-0 h-100 card" id="card-countdown">
                    <div class="py-4 text-center card-body">
                        <div class="mb-2">
                            <i class="mdi-timer-outline mdi" style="font-size:2.5rem;" id="countdown-icon"></i>
                        </div>
                        <h6 class="mb-1 text-muted text-uppercase fw-bold small">Tiempo restante</h6>
                        <div id="countdown-display" class="mb-1 display-6 fw-bold">--:--:--</div>
                        <div id="countdown-subtitle" class="text-muted small">Cargando...</div>
                        <hr class="my-3">
                        <div class="text-muted small">
                            Límite: <strong>Diario 12:00 PM</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estado reporte actual (solo sede) --}}
            @auth
            @if (auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <div class="mb-3 col-lg-4 col-md-6">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="py-4 card-body">
                        <h6 class="mb-3 text-muted text-uppercase fw-bold small">Estado — Hoy</h6>
                        @if ($reporteSemanaActual)
                            @php
                                $enviado = !is_null($reporteSemanaActual->fecha_envio_original);
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <i class="me-2 text-primary mdi mdi-map-marker"></i>
                                <strong>{{ $reporteSemanaActual->sede }}</strong>
                            </div>
                            @if ($enviado)
                                <div class="mb-2 py-2 alert alert-success">
                                    <i class="me-1 mdi mdi-check-circle"></i>
                                    Reporte enviado el
                                    {{ $reporteSemanaActual->fecha_envio_original?->setTimezone('America/Lima')->format('d/m H:i') }}
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="me-2 fw-bold">KPI:</span>
                                    <span class="badge bg-{{ $reporteSemanaActual->kpiColor() }} fs-6">
                                        {{ $reporteSemanaActual->kpiLabel() }}
                                    </span>
                                </div>
                                @if ($reporteSemanaActual->editado_tarde)
                                <div class="mt-2 mb-0 py-1 alert alert-warning small">
                                    <i class="me-1 mdi mdi-alert"></i>
                                    Editado con atraso — KPI ajustado
                                </div>
                                @endif
                            @else
                                <div class="mb-0 py-2 alert alert-warning">
                                    <i class="me-1 mdi mdi-clock-alert"></i>
                                    Pendiente de envío
                                </div>
                            @endif
                        @else
                            <p class="mb-0 text-muted">No tienes sede asignada.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endauth

            {{-- KPI info --}}
            <div class="mb-3 col-lg-4 col-md-12">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="py-4 card-body">
                        <h6 class="mb-3 text-muted text-uppercase fw-bold small">Escala KPI</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Antes de las 12:00 PM</span>
                                <span class="bg-success badge">100%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 1 hora de atraso</span>
                                <span class="bg-info badge">90%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 2 horas de atraso</span>
                                <span class="bg-primary badge">80%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 3 horas de atraso</span>
                                <span class="bg-warning text-dark badge">50%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Más de 3 horas o no enviado</span>
                                <span class="bg-danger badge">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════
             FILA 2: Resumen semana actual (admin/superadmin)
        ══════════════════════════════════════════════ --}}
        @if ($resumenSedes && $resumenSedes->count() > 0)
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 mdi-view-dashboard-outline text-primary mdi"></i>
                            Estado Sedes — {{ $fechaDiaLabel }}
                        </h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <span class="bg-success badge filtro-estado" data-filtro="enviado">Enviado</span>
                            <span class="bg-danger badge filtro-estado" data-filtro="pendiente">Pendiente</span>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-estado-sedes">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Responsable</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Hora de Envío</th>
                                        <th class="text-center">KPI Hoy</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resumenSedes as $fila)
                                    <tr class="{{ $fila['enviado'] ? '' : 'table-danger bg-opacity-25' }}"
                                        data-estado="{{ $fila['enviado'] ? 'enviado' : 'pendiente' }}">
                                        <td>
                                            <strong>{{ $fila['sede'] }}</strong>
                                        </td>
                                        <td class="text-muted small">{{ $fila['usuario'] }}</td>
                                        <td class="text-center">
                                            @if ($fila['enviado'])
                                                <span class="bg-success badge">
                                                    <i class="me-1 mdi mdi-check"></i>Enviado
                                                </span>
                                            @else
                                                <span class="bg-danger badge">
                                                    <i class="me-1 mdi mdi-clock-alert"></i>Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($fila['fecha_envio'])
                                                <span class="fw-semibold">{{ $fila['fecha_envio'] }} hrs</span>
                                                @if ($fila['editado_tarde'])
                                                    <br><small class="text-warning"><i class="mdi mdi-pencil"></i> Editado tarde</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (!is_null($fila['kpi']))
                                                <span class="badge bg-{{ $fila['kpi_color'] }} fs-6">
                                                    {{ $fila['kpi_label'] }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($fila['reporte_id'])
                                                <button class="px-2 py-0 btn-outline-primary btn btn-sm"
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
                    <div class="d-flex gap-4 bg-white px-4 py-2 border-top text-muted card-footer small">
                        <span>
                            <i class="text-success mdi mdi-check-circle"></i>
                            Enviados: <strong>{{ $resumenSedes->where('enviado', true)->count() }}</strong>
                        </span>
                        <span>
                            <i class="text-danger mdi mdi-clock-alert"></i>
                            Pendientes: <strong>{{ $resumenSedes->where('enviado', false)->count() }}</strong>
                        </span>
                        <span>
                            <i class="text-primary mdi mdi-chart-line"></i>
                            KPI promedio:
                            <strong>
                                @php
                                    $kpisValidos = $resumenSedes->whereNotNull('kpi')->pluck('kpi');
                                    echo $kpisValidos->count() ? number_format($kpisValidos->avg(), 1) . '%' : '—';
                                @endphp
                            </strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════
             FILA: Formulario de envío (solo sede)
        ══════════════════════════════════════════════ --}}
        @auth
        @if ((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual)
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-upload"></i>
                            @if ($reporteSemanaActual->fecha_envio_original)
                                Editar Reporte — {{ $fechaDiaLabel }}
                            @else
                                Enviar Reporte — {{ $fechaDiaLabel }}
                            @endif
                        </h5>
                        <span class="bg-light border text-dark badge">
                            Sede: <strong>{{ $reporteSemanaActual->sede }}</strong>
                        </span>
                    </div>
                    <div class="card-body">
                        @if ($reporteSemanaActual->fecha_envio_original)
                            {{-- Formulario de EDICIÓN --}}
                            <form id="form-editar-reporte" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="reporte_id" value="{{ $reporteSemanaActual->id }}">

                                {{-- Archivos existentes --}}
                                @if (count($reporteSemanaActual->archivos ?? []) > 0)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Archivos enviados</label>
                                    <div class="row g-2" id="archivos-existentes">
                                        @foreach ($reporteSemanaActual->archivos as $idx => $archivo)
                                        <div class="col-md-4 col-sm-6" id="archivo-card-{{ $idx }}">
                                            <div class="d-flex align-items-center gap-2 bg-light p-2 border rounded">
                                                <i class="mdi mdi-{{ str_contains($archivo['mime'] ?? '', 'image') ? 'image' : 'file-excel' }} text-primary fs-5"></i>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="text-truncate small fw-semibold" title="{{ $archivo['name'] }}">{{ $archivo['name'] }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ number_format(($archivo['size'] ?? 0)/1024, 1) }} KB</div>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('productividad.cobranza-sedes.cobranza.download', [$reporteSemanaActual->id, $idx]) }}"
                                                       class="px-1 py-0 btn-outline-primary btn btn-sm" title="Descargar">
                                                        <i class="mdi mdi-download"></i>
                                                    </a>
                                                    <button type="button" class="px-1 py-0 btn-outline-danger btn btn-sm"
                                                            onclick="marcarEliminar({{ $idx }}, this)" title="Eliminar">
                                                        <i class="mdi-trash-can-outline mdi"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="eliminar_indices[]" id="eliminar-{{ $idx }}" value="{{ $idx }}" disabled>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Nuevos archivos --}}
                                <div class="mb-3">
                                    <label for="archivos-edit" class="form-label fw-semibold">Agregar archivos adicionales</label>
                                    <div class="drop-zone" id="drop-zone-edit">
                                        <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                                        <p class="mt-2 mb-1 text-muted small">Arrastra fotos o archivos Excel/PDF aquí</p>
                                        <p class="text-muted" style="font-size:11px;">JPG, PNG, GIF, WEBP, XLSX, XLS, CSV, PDF — máx. 20 MB c/u</p>
                                        <input type="file" id="archivos-edit" name="archivos[]"
                                               multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf"
                                               class="d-none">
                                        <div class="d-flex justify-content-center gap-2 mt-1">
                                            <button type="button" class="btn-outline-primary btn btn-sm"
                                                    onclick="document.getElementById('archivos-edit').click()">
                                                <i class="me-1 mdi mdi-paperclip"></i>Seleccionar archivos
                                            </button>
                                            <button type="button" class="btn-outline-secondary btn btn-sm"
                                                    onclick="abrirCamara('archivos-edit', 'preview-edit')">
                                                <i class="me-1 mdi mdi-camera"></i>Tomar foto
                                            </button>
                                        </div>
                                    </div>
                                    <div id="preview-edit" class="mt-2 row g-2"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="notas-edit" class="form-label fw-semibold">Notas / Observaciones</label>
                                    <textarea id="notas-edit" name="notas" rows="2"
                                              class="form-control" placeholder="Opcional...">{{ $reporteSemanaActual->notas }}</textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning fw-bold" id="btn-editar">
                                        <i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios
                                    </button>
                                </div>
                                <div id="msg-editar" class="mt-2"></div>
                            </form>
                        @else
                            {{-- Formulario de ENVÍO INICIAL --}}
                            <form id="form-enviar-reporte" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Archivos del reporte <span class="text-danger">*</span></label>
                                    <div class="drop-zone" id="drop-zone-nuevo">
                                        <i class="mdi-cloud-upload-outline mdi" style="font-size:2.5rem;color:#94a3b8;"></i>
                                        <p class="mt-2 mb-1 text-muted">Arrastra tus fotos o archivos Excel/PDF aquí</p>
                                        <p class="text-muted small">JPG, PNG, GIF, WEBP, XLSX, XLS, CSV, PDF — máx. 20 MB c/u</p>
                                        <input type="file" id="archivos-nuevo" name="archivos[]"
                                               multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf"
                                               class="d-none">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn-outline-primary btn"
                                                    onclick="document.getElementById('archivos-nuevo').click()">
                                                <i class="me-1 mdi mdi-paperclip"></i>Seleccionar archivos
                                            </button>
                                            <button type="button" class="btn-outline-secondary btn"
                                                    onclick="abrirCamara('archivos-nuevo', 'preview-nuevo')">
                                                <i class="me-1 mdi mdi-camera"></i>Tomar foto
                                            </button>
                                        </div>
                                    </div>
                                    <div id="preview-nuevo" class="mt-2 row g-2"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="notas-nuevo" class="form-label fw-semibold">Notas / Observaciones</label>
                                    <textarea id="notas-nuevo" name="notas" rows="2"
                                              class="form-control" placeholder="Opcional..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary fw-bold" id="btn-enviar">
                                    <i class="me-1 mdi mdi-send"></i>Enviar y Registrar
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

        {{-- ══════════════════════════════════════════════
             FILA 3: Gráfico KPI diario (navegador por mes)
        ══════════════════════════════════════════════ --}}
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-chart-line"></i>KPI Diario por Sede
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="btn-mes-prev" class="px-2 py-0 btn-outline-secondary btn btn-sm">‹</button>
                            <span id="label-mes-chart" class="fw-semibold small" style="min-width:90px;text-align:center;">—</span>
                            <button id="btn-mes-next" class="px-2 py-0 btn-outline-secondary btn btn-sm">›</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:320px;">
                            <canvas id="kpi-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════
             FILA 3b: Cumplimiento mensual (solo admin)
        ══════════════════════════════════════════════ --}}
        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->puede_ver_productividad_sedes)
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-success mdi mdi-chart-bar"></i>Cumplimiento Mensual por Sede
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <label class="me-1 mb-0 text-muted small">Meses:</label>
                            <select id="filtro-meses-mensual" class="form-select-sm form-select" style="width:80px;">
                                <option value="2" selected>2</option>
                                <option value="3">3</option>
                                <option value="6">6</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:320px;">
                            <canvas id="kpi-chart-mensual"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════
             FILA 4: Historial
        ══════════════════════════════════════════════ --}}
        <div class="row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-history"></i>Historial de Reportes
                        </h5>
                        <div id="historial-loader" class="spinner-border spinner-border-sm text-primary d-none"></div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-historial">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Fecha</th>
                                        <th>Fecha Límite</th>
                                        <th>Fecha Envío</th>
                                        <th>KPI</th>
                                        <th>Estado</th>
                                        <th>Archivos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historial-body">
                                    <tr>
                                        <td colspan="8" class="py-4 text-center">
                                            <div class="me-2 spinner-border spinner-border-sm text-primary"></div>
                                            Cargando historial...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Ver Reporte ────────────────────────────────────── --}}
<div class="modal fade" id="modal-reporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 mdi-file-document-outline mdi"></i>
                    <span id="modal-titulo">Detalle del Reporte</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="py-4 text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Cámara ─────────────────────────────────────────── --}}
<div class="modal fade" id="modal-camara" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 text-primary mdi mdi-camera"></i>Tomar Foto
                </h5>
                <button type="button" class="btn-close" onclick="cerrarCamara()"></button>
            </div>
            <div class="p-3 text-center modal-body">
                <video id="camara-video" autoplay playsinline
                       style="width:100%;border-radius:10px;background:#000;max-height:380px;object-fit:cover;"></video>
                <canvas id="camara-canvas" class="d-none"></canvas>
                <div id="camara-preview-wrap" class="mt-2 d-none">
                    <img id="camara-preview-img" src="" alt="Captura"
                         style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;">
                </div>
            </div>
            <div class="justify-content-center gap-2 modal-footer">
                <button id="btn-capturar" class="btn btn-primary" onclick="capturarFoto()">
                    <i class="me-1 mdi mdi-camera"></i>Capturar
                </button>
                <button id="btn-retomar" class="btn-outline-secondary btn d-none" onclick="retomarFoto()">
                    <i class="me-1 mdi mdi-refresh"></i>Retomar
                </button>
                <button id="btn-usar-foto" class="btn btn-success d-none" onclick="usarFoto()">
                    <i class="me-1 mdi mdi-check"></i>Usar esta foto
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 32px 20px;
        text-align: center;
        background: #f8fafc;
        transition: border-color .2s, background .2s;
        cursor: pointer;
    }
    .drop-zone.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .preview-thumb {
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #f8fafc;
    }
    .preview-thumb img {
        width: 100%;
        height: 80px;
        object-fit: cover;
    }
    .preview-thumb .preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        background: #ef4444;
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .preview-thumb .preview-name {
        font-size: 10px;
        padding: 2px 4px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #countdown-display {
        font-size: 2rem;
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
    }
    .filtro-estado {
        cursor: pointer;
        user-select: none;
        transition: opacity .2s, text-decoration .2s;
    }
    .filtro-estado.inactivo {
        opacity: .35;
        text-decoration: line-through;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Config ───────────────────────────────────────────────────────
const ROUTES = {
    store:    "{{ route('productividad.cobranza-sedes.cobranza.store') }}",
    historial:"{{ route('productividad.cobranza-sedes.cobranza.historial') }}",
    kpiData:  "{{ route('productividad.cobranza-sedes.cobranza.kpi-data') }}",
    base:     "{{ url('/productividad/cobranza-sedes/deposito-efectivo') }}",
};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Helpers de URL dinámicas
const urlShow   = (id) => `${ROUTES.base}/${id}/show`;
const urlUpdate = (id) => `${ROUTES.base}/${id}`;
const urlPreview= (id, idx) => `${ROUTES.base}/${id}/preview/${idx}`;
const urlDownload=(id, idx) => `${ROUTES.base}/${id}/download/${idx}`;

// Helper fetch con manejo de errores claro
async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...(options.headers ?? {}) },
        ...options,
    });
    const contentType = res.headers.get('Content-Type') ?? '';
    if (!contentType.includes('application/json')) {
        throw new Error(`Error ${res.status}: el servidor devolvió una respuesta inesperada.`);
    }
    const data = await res.json();
    if (!res.ok) {
        const msg = data.errors
            ? Object.values(data.errors).flat().join('\n')
            : (data.error ?? data.message ?? `Error ${res.status}`);
        throw new Error(msg);
    }
    return data;
}

// ── Countdown ─────────────────────────────────────────────────────
const DEADLINE_TS = {{ $fechaLimiteTs }};

function actualizarCountdown() {
    const ahora   = Date.now();
    const diffMs  = DEADLINE_TS - ahora;
    const card    = document.getElementById('card-countdown');
    const display = document.getElementById('countdown-display');
    const sub     = document.getElementById('countdown-subtitle');
    const icon    = document.getElementById('countdown-icon');

    if (diffMs <= 0) {
        display.textContent = 'VENCIDO';
        sub.textContent     = 'El plazo ya venció';
        display.style.color = '#dc2626';
        icon.className      = 'mdi mdi-alert-circle-outline text-danger';
        card.classList.add('border-danger');
        return;
    }

    const h  = Math.floor(diffMs / 3600000);
    const m  = Math.floor((diffMs % 3600000) / 60000);
    const s  = Math.floor((diffMs % 60000) / 1000);
    const hh = String(h).padStart(2, '0');
    const mm = String(m).padStart(2, '0');
    const ss = String(s).padStart(2, '0');
    display.textContent = `${hh}:${mm}:${ss}`;

    if (diffMs <= 3600000) {          // Menos de 1 hora
        display.style.color = '#dc2626';
        icon.className      = 'mdi mdi-timer-alert-outline text-danger';
        card.classList.add('border-danger');
        sub.textContent     = 'Menos de 1 hora para el cierre';
    } else if (diffMs <= 7200000) {   // Menos de 2 horas
        display.style.color = '#d97706';
        icon.className      = 'mdi mdi-timer-outline text-warning';
        sub.textContent     = 'Menos de 2 horas para el cierre';
    } else {
        display.style.color = '#2563eb';
        icon.className      = 'mdi mdi-timer-outline text-primary';
        sub.textContent     = 'Hoy, 12:00 PM hora Lima';
    }
}

actualizarCountdown();
setInterval(actualizarCountdown, 1000);

// ── Drop zones ────────────────────────────────────────────────────
// Acumulador independiente por inputId para evitar duplicados
const _acumulados = {};

function setupDropZone(zoneId, inputId, previewId) {
    const zone    = document.getElementById(zoneId);
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!zone || !input) return;

    _acumulados[inputId] = new DataTransfer();

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        agregarArchivos(e.dataTransfer.files, inputId, preview);
    });
    input.addEventListener('change', () => {
        // input.files ya tiene SOLO la nueva selección del diálogo;
        // usamos el acumulador para preservar los anteriores sin duplicar.
        agregarArchivos(input.files, inputId, preview);
        input.value = ''; // permite re-seleccionar el mismo archivo
    });
}

function agregarArchivos(newFiles, inputId, preview) {
    const dt = _acumulados[inputId];
    for (const f of newFiles) dt.items.add(f);
    renderPreview(dt.files, preview, inputId);
}

function renderPreview(files, preview, inputId) {
    preview.innerHTML = '';
    for (let i = 0; i < files.length; i++) {
        const f   = files[i];
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3 col-lg-2';
        const isImg = f.type.startsWith('image/');
        col.innerHTML = `
            <div class="p-1 preview-thumb">
                ${isImg
                    ? `<img src="${URL.createObjectURL(f)}" alt="${f.name}">`
                    : `<div class="d-flex align-items-center justify-content-center" style="height:80px;font-size:2rem;">
                            <i class="text-success mdi mdi-file-excel"></i>
                       </div>`
                }
                <button type="button" class="preview-remove" onclick="quitarArchivo(${i}, '${inputId}')">×</button>
                <div class="text-muted preview-name">${f.name}</div>
            </div>`;
        preview.appendChild(col);
    }
}

function quitarArchivo(idx, inputId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(inputId.replace('archivos', 'preview'));
    const dt      = new DataTransfer();
    const current = _acumulados[inputId].files;
    for (let i = 0; i < current.length; i++) {
        if (i !== idx) dt.items.add(current[i]);
    }
    _acumulados[inputId] = dt;
    input.files = dt.files;
    renderPreview(dt.files, preview, inputId);
}

setupDropZone('drop-zone-nuevo', 'archivos-nuevo', 'preview-nuevo');
setupDropZone('drop-zone-edit',  'archivos-edit',  'preview-edit');

// ── Marcar archivos existentes para eliminar ──────────────────────
function marcarEliminar(idx, btn) {
    const hidden = document.getElementById('eliminar-' + idx);
    const card   = document.getElementById('archivo-card-' + idx);
    if (hidden.disabled) {
        hidden.disabled = false;
        card.style.opacity = '0.4';
        btn.innerHTML = '<i class="mdi mdi-undo"></i>';
        btn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
    } else {
        hidden.disabled = true;
        card.style.opacity = '1';
        btn.innerHTML = '<i class="mdi-trash-can-outline mdi"></i>';
        btn.classList.replace('btn-outline-secondary', 'btn-outline-danger');
    }
}

// ── Envío inicial ─────────────────────────────────────────────────
const formEnviar = document.getElementById('form-enviar-reporte');
if (formEnviar) {
    formEnviar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-enviar');
        const msg = document.getElementById('msg-enviar');
        // Sync accumulator → input before FormData reads it
        document.getElementById('archivos-nuevo').files = _acumulados['archivos-nuevo'].files;
        const fd  = new FormData(this);

        btn.disabled  = true;
        btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Enviando...';
        msg.innerHTML = '';

        try {
            const data = await apiFetch(ROUTES.store, { method: 'POST', body: fd });
            msg.innerHTML = `<div class="py-2 alert alert-success">${data.message}</div>`;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`;
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="me-1 mdi mdi-send"></i>Enviar y Registrar';
        }
    });
}

// ── Edición ───────────────────────────────────────────────────────
const formEditar = document.getElementById('form-editar-reporte');
if (formEditar) {
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn       = document.getElementById('btn-editar');
        const msg       = document.getElementById('msg-editar');
        const reporteId = this.querySelector('[name="reporte_id"]').value;

        // Sync accumulator → input before FormData reads it
        document.getElementById('archivos-edit').files = _acumulados['archivos-edit'].files;
        // FormData ya trae _method=PUT del @method('PUT') en el form
        const fd = new FormData(this);

        btn.disabled  = true;
        btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Guardando...';
        msg.innerHTML = '';

        try {
            const data = await apiFetch(urlUpdate(reporteId), { method: 'POST', body: fd });
            const tipo = data.tardio ? 'warning' : 'success';
            msg.innerHTML = `<div class="alert alert-${tipo} py-2">${data.message}</div>`;
            setTimeout(() => location.reload(), 1800);
        } catch (err) {
            msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`;
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios';
        }
    });
}

// ── Historial ─────────────────────────────────────────────────────
async function cargarHistorial() {
    const tbody = document.getElementById('historial-body');
    const loader = document.getElementById('historial-loader');
    loader.classList.remove('d-none');

    try {
        const data = await apiFetch(ROUTES.historial);
        renderHistorial(data.data ?? []);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-3 text-danger text-center">${err.message}</td></tr>`;
    } finally {
        loader.classList.add('d-none');
    }
}

function badgeEstado(estado) {
    const map = { en_tiempo:'success', con_atraso:'warning', pendiente:'warning', no_enviado:'danger' };
    const labels = { en_tiempo:'En tiempo', con_atraso:'Con atraso', pendiente:'Pendiente', no_enviado:'No enviado' };
    return `<span class="badge bg-${map[estado] ?? 'secondary'}">${labels[estado] ?? estado}</span>`;
}

function renderHistorial(rows) {
    const tbody = document.getElementById('historial-body');

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-4 text-muted text-center">Sin registros.</td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><strong>${r.sede}</strong></td>
            <td><span class="bg-light border text-dark badge">${r.semana}</span></td>
            <td class="small">${r.fecha_limite ?? '—'}</td>
            <td class="small">
                ${r.fecha_envio ?? '<span class="text-muted">—</span>'}
                ${r.fecha_edicion ? `<br><span class="text-warning small"><i class="mdi mdi-pencil"></i> ${r.fecha_edicion}</span>` : ''}
            </td>
            <td>
                ${r.kpi !== null
                    ? `<span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>`
                    : '<span class="text-muted">—</span>'
                }
                ${r.editado_tarde ? '<br><small class="text-warning"><i class="mdi mdi-alert"></i> Editado tarde</small>' : ''}
            </td>
            <td>${badgeEstado(r.estado)}</td>
            <td class="text-center">
                ${r.num_archivos > 0 ? `<span class="bg-info badge">${r.num_archivos}</span>` : '<span class="text-muted">0</span>'}
            </td>
            <td>
                <button class="px-2 py-0 btn-outline-primary btn btn-sm" onclick="verReporte(${r.id})" title="Ver detalles">
                    <i class="mdi mdi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

cargarHistorial();

// ── Modal Ver Reporte ─────────────────────────────────────────────
async function verReporte(id) {
    const modal  = new bootstrap.Modal(document.getElementById('modal-reporte'));
    const body   = document.getElementById('modal-body');
    const titulo = document.getElementById('modal-titulo');

    body.innerHTML     = '<div class="py-4 text-center"><div class="spinner-border text-primary"></div></div>';
    titulo.textContent = 'Detalle del Reporte';
    modal.show();

    try {
        const r = await apiFetch(urlShow(id));
        titulo.textContent = `${r.sede} — ${r.semana}`;

        // Archivos: imágenes con preview inline, otros con ícono
        const archivosHTML = (r.archivos ?? []).length
            ? buildArchivosHTML(r.archivos)
            : '<p class="text-muted">Sin archivos.</p>';

        body.innerHTML = `
            <div class="mb-3 row g-3">
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Sede</div>
                    <div class="fw-bold">${r.sede}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Fecha</div>
                    <div class="fw-bold">${r.semana}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Fecha Límite</div>
                    <div>${r.fecha_limite ?? '—'}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Fecha Envío</div>
                    <div>${r.fecha_envio ?? '<span class="text-muted">No enviado</span>'}</div>
                </div>
                ${r.fecha_edicion ? `
                <div class="col-12">
                    <div class="mb-0 py-1 alert alert-warning small">
                        <i class="me-1 mdi mdi-pencil"></i>Editado tardíamente el ${r.fecha_edicion}
                    </div>
                </div>` : ''}
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">KPI</div>
                    <span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Estado</div>
                    <div>${r.estado ?? '—'}</div>
                </div>
                ${r.notas ? `
                <div class="col-12">
                    <div class="text-muted text-uppercase small fw-semibold">Notas</div>
                    <div class="bg-light p-2 border rounded small">${r.notas}</div>
                </div>` : ''}
            </div>
            <hr>
            <h6 class="mb-2 fw-semibold">Archivos adjuntos</h6>
            ${archivosHTML}
        `;
    } catch (err) {
        body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    }
}

function buildArchivosHTML(archivos) {
    // Separar imágenes de otros archivos
    const imagenes = archivos.filter(a => a.es_imagen);
    const otros    = archivos.filter(a => !a.es_imagen);

    let html = '';

    // Grid de imágenes con preview
    if (imagenes.length) {
        html += `<div class="mb-3 row g-2">`;
        imagenes.forEach(a => {
            html += `
                <div class="col-6 col-md-4">
                    <div class="position-relative border rounded overflow-hidden" style="aspect-ratio:4/3;">
                        <img src="${a.preview_url}" alt="${a.name}"
                             style="width:100%;height:100%;object-fit:cover;cursor:pointer;"
                             onclick="window.open('${a.preview_url}','_blank')"
                             title="Click para ampliar">
                        <div class="bottom-0 position-absolute d-flex align-items-center justify-content-between px-2 py-1 start-0 end-0"
                             style="background:rgba(0,0,0,0.45);">
                            <span class="text-white text-truncate small" style="max-width:70%;">${a.name}</span>
                            <a href="${a.download_url}" class="px-1 py-0 btn btn-sm btn-light" title="Descargar" download>
                                <i class="mdi mdi-download small"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
        });
        html += `</div>`;
    }

    // Lista de otros archivos
    otros.forEach(a => {
        const ext  = a.name.split('.').pop().toUpperCase();
        const icon = ['XLSX','XLS','CSV'].includes(ext) ? 'mdi-file-excel text-success'
                   : ext === 'PDF' ? 'mdi-file-pdf text-danger'
                   : 'mdi-file-document text-primary';
        html += `
            <div class="d-flex align-items-center gap-2 bg-light mb-1 p-2 border rounded">
                <i class="mdi ${icon} fs-5"></i>
                <span class="flex-grow-1 text-truncate small fw-semibold">${a.name}</span>
                <a href="${a.download_url}" class="px-2 py-0 btn-outline-primary btn btn-sm" title="Descargar" download>
                    <i class="mdi mdi-download"></i> Descargar
                </a>
            </div>`;
    });

    return html;
}

// ── Cámara ────────────────────────────────────────────────────────
let _streamActivo    = null;
let _camaraInputId   = null;
let _camaraPreviewId = null;
let _fotoBlob        = null;
let _modalCamaraInst = null;

async function abrirCamara(inputId, previewId) {
    _camaraInputId   = inputId;
    _camaraPreviewId = previewId;
    _fotoBlob        = null;

    const video   = document.getElementById('camara-video');
    const preview = document.getElementById('camara-preview-wrap');

    preview.classList.add('d-none');
    video.classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');

    try {
        _streamActivo = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
        video.srcObject = _streamActivo;
    } catch (err) {
        alert('No se pudo acceder a la cámara. Verifica los permisos del navegador.');
        return;
    }

    _modalCamaraInst = new bootstrap.Modal(document.getElementById('modal-camara'));
    _modalCamaraInst.show();
}

function capturarFoto() {
    const video  = document.getElementById('camara-video');
    const canvas = document.getElementById('camara-canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(blob => {
        _fotoBlob = blob;
        const imgSrc = URL.createObjectURL(blob);
        document.getElementById('camara-preview-img').src = imgSrc;
        document.getElementById('camara-preview-wrap').classList.remove('d-none');
        video.classList.add('d-none');
        document.getElementById('btn-capturar').classList.add('d-none');
        document.getElementById('btn-retomar').classList.remove('d-none');
        document.getElementById('btn-usar-foto').classList.remove('d-none');

        // Pausar stream mientras se revisa la foto
        _streamActivo?.getTracks().forEach(t => t.enabled = false);
    }, 'image/jpeg', 0.92);
}

function retomarFoto() {
    _fotoBlob = null;
    _streamActivo?.getTracks().forEach(t => t.enabled = true);
    document.getElementById('camara-preview-wrap').classList.add('d-none');
    document.getElementById('camara-video').classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');
}

function usarFoto() {
    if (!_fotoBlob) return;
    const ts   = new Date().toISOString().replace(/[:.]/g,'-');
    const file = new File([_fotoBlob], `foto-${ts}.jpg`, { type: 'image/jpeg' });

    const preview = document.getElementById(_camaraPreviewId);
    agregarArchivos([file], _camaraInputId, preview);

    cerrarCamara();
}

function cerrarCamara() {
    _streamActivo?.getTracks().forEach(t => t.stop());
    _streamActivo = null;
    _modalCamaraInst?.hide();
}

// ── Gráfico KPI diario (navegador por mes) ────────────────────────
const MESES_ES = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
let kpiChart   = null;
let mesChart   = new Date().getMonth() + 1;
let anioChart  = new Date().getFullYear();

function opcionesChart(tooltipExtra = '') {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: {
                min: 0, max: 100,
                ticks: { callback: v => v + '%', stepSize: 10 },
                grid: { color: '#f1f5f9' },
            },
            x: { grid: { display: false } },
        },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: c => `${c.dataset.label}: ${c.parsed.y !== null ? c.parsed.y + '%' : 'Sin dato'}` + tooltipExtra,
                }
            },
            annotation: {
                annotations: {
                    linea100: {
                        type: 'line', yMin: 100, yMax: 100,
                        borderColor: 'rgba(16,185,129,0.3)',
                        borderWidth: 1, borderDash: [6, 4],
                    }
                }
            }
        },
    };
}

async function cargarKpiChart(mes = mesChart, anio = anioChart) {
    mesChart  = mes;
    anioChart = anio;

    // Actualizar label y estado del botón siguiente
    document.getElementById('label-mes-chart').textContent = `${MESES_ES[mes]} ${anio}`;
    const hoy = new Date();
    document.getElementById('btn-mes-next').disabled =
        (anio > hoy.getFullYear()) ||
        (anio === hoy.getFullYear() && mes >= hoy.getMonth() + 1);

    try {
        const res  = await fetch(
            `${ROUTES.kpiData}?tipo=diario&mes=${mes}&anio=${anio}`,
            { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } }
        );
        const data = await res.json();
        const ctx  = document.getElementById('kpi-chart').getContext('2d');
        if (kpiChart) kpiChart.destroy();
        kpiChart = new Chart(ctx, {
            type: 'line',
            data: { labels: data.labels ?? [], datasets: data.datasets ?? [] },
            options: opcionesChart(),
        });
    } catch (e) {
        console.error('Error cargando KPI diario:', e);
    }
}

document.getElementById('btn-mes-prev').addEventListener('click', () => {
    let m = mesChart - 1, a = anioChart;
    if (m < 1) { m = 12; a--; }
    cargarKpiChart(m, a);
});

document.getElementById('btn-mes-next').addEventListener('click', () => {
    const hoy = new Date();
    const sigMes  = mesChart === 12 ? 1 : mesChart + 1;
    const sigAnio = mesChart === 12 ? anioChart + 1 : anioChart;
    if (sigAnio < hoy.getFullYear() || (sigAnio === hoy.getFullYear() && sigMes <= hoy.getMonth() + 1)) {
        cargarKpiChart(sigMes, sigAnio);
    }
});

cargarKpiChart();

// ── Gráfico cumplimiento mensual (admin y con permiso productividad sedes) ─────
@if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->puede_ver_productividad_sedes)
let kpiChartMensual = null;

async function cargarKpiChartMensual(meses = 2) {
    try {
        const res  = await fetch(
            `${ROUTES.kpiData}?tipo=mensual&meses=${meses}`,
            { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } }
        );
        const data = await res.json();
        const ctx  = document.getElementById('kpi-chart-mensual').getContext('2d');
        if (kpiChartMensual) kpiChartMensual.destroy();
        kpiChartMensual = new Chart(ctx, {
            type: 'line',
            data: { labels: data.labels ?? [], datasets: data.datasets ?? [] },
            options: opcionesChart(' (prom.)'),
        });
    } catch (e) {
        console.error('Error cargando KPI mensual:', e);
    }
}

cargarKpiChartMensual(2);

document.getElementById('filtro-meses-mensual')?.addEventListener('change', function() {
    cargarKpiChartMensual(parseInt(this.value));
});
@endif

// ── Filtros tabla Estado Sedes ────────────────────────────────────
(function () {
    const activos = new Set(['enviado', 'pendiente']);

    function aplicar() {
        document.querySelectorAll('#tabla-estado-sedes tbody tr[data-estado]').forEach(tr => {
            tr.style.display = activos.has(tr.dataset.estado) ? '' : 'none';
        });
    }

    document.querySelectorAll('.filtro-estado').forEach(badge => {
        badge.addEventListener('click', function () {
            const key = this.dataset.filtro;
            if (activos.has(key)) {
                activos.delete(key);
                this.classList.add('inactivo');
            } else {
                activos.add(key);
                this.classList.remove('inactivo');
            }
            aplicar();
        });
    });
})();
</script>
@endpush
