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
                            <p class="mb-0 text-muted small">Reporte diario — límite: {{ $fechaLimiteLabel }}</p>
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
                            Límite: <strong>Diario {{ $fechaLimiteLabel }}</strong>
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
                        @if ($reporteHoyEstado)
                            @php
                                $enviado = !is_null($reporteHoyEstado->fecha_envio_original);
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <i class="me-2 text-primary mdi mdi-map-marker"></i>
                                <strong>{{ $reporteHoyEstado->sede }}</strong>
                            </div>
                            @if ($enviado)
                                <div class="mb-2 py-2 alert alert-success">
                                    <i class="me-1 mdi mdi-check-circle"></i>
                                    Reporte enviado el
                                    {{ $reporteHoyEstado->fecha_envio_original?->setTimezone('America/Lima')->format('d/m H:i') }}
                                </div>
                                @if ($reporteHoyEstado->sin_deposito)
                                <div class="mb-2 py-1 alert alert-info small">
                                    <i class="me-1 mdi mdi-credit-card-off"></i>
                                    Registrado como <strong>Sin depósito</strong> (sin facturación en efectivo)
                                </div>
                                @endif
                                <div class="d-flex align-items-center">
                                    <span class="me-2 fw-bold">KPI:</span>
                                    <span class="badge bg-{{ $reporteHoyEstado->kpiColor() }} fs-6">
                                        {{ $reporteHoyEstado->kpiLabel() }}
                                    </span>
                                </div>
                                @if ($reporteHoyEstado->editado_tarde)
                                <div class="mt-2 mb-0 py-1 alert alert-warning small">
                                    <i class="me-1 mdi mdi-alert"></i>
                                    Editado con atraso — KPI ajustado
                                </div>
                                @endif
                            @elseif ($plazoPasadoHoy)
                                <div class="mb-0 py-2 alert alert-danger">
                                    <i class="me-1 mdi mdi-clock-remove"></i>
                                    <strong>Plazo vencido</strong> — no enviado.<br>
                                    <small>Busca el botón <strong>Enviar Atrasado</strong> en Historial.</small>
                                </div>
                            @else
                                <div class="mb-0 py-2 alert alert-warning">
                                    <i class="me-1 mdi mdi-clock-alert"></i>
                                    Pendiente — tienes hasta las <strong>{{ $fechaLimiteLabel }}</strong>
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
                                <span class="small">Antes de las {{ $fechaLimiteLabel }}</span>
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
                        @if ($mostrarExcepcionNota)
                        <div class="mt-2 text-muted" style="font-size:11px;">
                            <i class="me-1 mdi-information-outline mdi"></i>Huanuco, Ica y Ate: límite 11:00 AM
                        </div>
                        @endif
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
                                                @if ($fila['sin_deposito'])
                                                    <br><span class="bg-success mt-1 badge" title="No hubo facturación en efectivo">
                                                        <i class="me-1 mdi mdi-credit-card-off"></i>Sin depósito
                                                    </span>
                                                @endif
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

        {{-- Banner: plazo vencido hoy y aún no enviado --}}
        @auth
        @if (auth()->user()->isSede() && $plazoPasadoHoy && $reporteHoyEstado && is_null($reporteHoyEstado->fecha_envio_original))
        <div class="mb-4 row">
            <div class="col-12">
                <div class="mb-0 py-2 alert alert-danger">
                    <i class="me-2 mdi mdi-alert-circle"></i>
                    <strong>El plazo de hoy ({{ $fechaLimiteLabel }}) ya venció.</strong>
                    Busca en Historial el botón <strong>Enviar Atrasado</strong> para registrar tu depósito.
                    El formulario de abajo corresponde al día <strong>{{ $fechaReporteLabel }}</strong>.
                </div>
            </div>
        </div>
        @endif
        @endauth

        {{-- ══════════════════════════════════════════════
             FILA: Formulario de envío (solo sede)
        ══════════════════════════════════════════════ --}}
        @auth
        @if ((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual)
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="d-flex flex-wrap align-items-center gap-2 mb-0 fw-bold">
                            <span>
                                <i class="me-2 text-primary mdi mdi-upload"></i>
                                @if ($reporteSemanaActual->fecha_envio_original)
                                    Editar Reporte — {{ $fechaReporteLabel }}
                                @else
                                    Enviar Reporte — {{ $fechaReporteLabel }}
                                @endif
                            </span>
                            @if ($plazoPasadoHoy)
                                <span class="bg-warning text-dark badge fw-normal" style="font-size:0.72rem;">
                                    <i class="mdi-calendar-arrow-right me-1 mdi"></i>Siguiente día hábil
                                </span>
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

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary fw-bold" id="btn-enviar">
                                        <i class="me-1 mdi mdi-send"></i>Enviar y Registrar
                                    </button>
                                    <button type="button" class="btn-outline-success btn fw-bold" id="btn-sin-deposito"
                                            onclick="abrirSinDeposito({{ $reporteSemanaActual->id }}, '{{ addslashes($reporteSemanaActual->sede) }}')">
                                        <i class="me-1 mdi mdi-credit-card-off"></i>Sin depósito
                                    </button>
                                </div>
                                <p class="mt-2 mb-0 text-muted small">
                                    <i class="me-1 mdi-information-outline mdi"></i>
                                    Usa <strong>Sin depósito</strong> si ese día no facturaste en efectivo (solo Yape/transferencias).
                                </p>
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
                            <i class="me-2 text-primary mdi mdi-chart-line"></i>KPI Diario
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
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-history"></i>Historial de Reportes
                            <span id="historial-total-badge" class="ms-2 text-muted fw-normal small d-none"></span>
                        </h5>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select id="filtro-sede" class="form-select-sm form-select" style="width:auto" onchange="aplicarFiltros()">
                                <option value="">Todas las sedes</option>
                            </select>
                            <input type="date" id="filtro-fecha" class="form-control form-control-sm" style="width:auto" onchange="cargarHistorial(this.value)" title="Filtrar por fecha exacta">
                            <button class="btn-outline-secondary btn btn-sm" onclick="document.getElementById('filtro-fecha').value='';cargarHistorial()" title="Limpiar fecha">
                                <i class="mdi mdi-close"></i>
                            </button>
                            <select id="sort-fecha" class="form-select-sm form-select" style="width:auto" onchange="aplicarFiltros()">
                                <option value="desc">Más reciente primero</option>
                                <option value="asc">Más antiguo primero</option>
                            </select>
                            <div id="historial-loader" class="spinner-border spinner-border-sm text-primary d-none"></div>
                        </div>
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
                        <div id="historial-pagination-bar" class="d-flex align-items-center justify-content-between px-3 py-2 border-top d-none">
                            <div id="historial-info" class="text-muted small"></div>
                            <nav>
                                <ul id="historial-pager" class="mb-0 pagination pagination-sm"></ul>
                            </nav>
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

{{-- ── Modal Editar desde Historial ───────────────────────── --}}
<div class="modal fade" id="modal-editar-historial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 text-primary mdi mdi-pencil"></i>
                    Editar Reporte — <span id="editar-historial-titulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-editar-historial" enctype="multipart/form-data"
                  style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
                @csrf
                @method('PUT')
                <input type="hidden" id="editar-historial-id">
                <div class="modal-body">
                    <div id="editar-historial-archivos-existentes" class="mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agregar archivos</label>
                        <div class="drop-zone" id="drop-zone-editar-historial">
                            <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                            <p class="mt-2 mb-1 text-muted small">Arrastra archivos aquí o selecciona</p>
                            <input type="file" id="archivos-editar-historial" name="archivos[]"
                                   multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                            <button type="button" class="mt-1 btn-outline-primary btn btn-sm"
                                    onclick="document.getElementById('archivos-editar-historial').click()">
                                <i class="me-1 mdi mdi-paperclip"></i>Seleccionar
                            </button>
                        </div>
                        <div id="preview-editar-historial" class="mt-2 row g-2"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea id="editar-historial-notas" name="notas" rows="2" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
                    </div>
                    <div id="msg-editar-historial"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary btn btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold btn-sm" id="btn-guardar-editar-historial">
                        <i class="me-1 mdi-content-save mdi"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Enviar Reporte Atrasado ───────────────────────── --}}
<div class="modal fade" id="modal-enviar-atrasado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 mdi-clock-alert-outline text-warning mdi"></i>Enviar Reporte Atrasado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-atrasado" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="atrasado-reporte-id">
                <div class="modal-body">
                    <div class="mb-3 py-2 alert alert-warning small">
                        <i class="me-1 mdi mdi-alert"></i>
                        El plazo ya venció. Este envío se registrará como <strong>atrasado</strong> y el KPI será penalizado.
                    </div>
                    <p class="mb-3 small"><strong>Sede:</strong> <span id="atrasado-sede-label" class="text-primary fw-bold"></span></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Archivos <span class="text-danger">*</span></label>
                        <div class="drop-zone" id="drop-zone-atrasado">
                            <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                            <p class="mt-2 mb-1 text-muted small">Arrastra archivos aquí o selecciona</p>
                            <input type="file" id="archivos-atrasado" name="archivos[]"
                                   multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                            <button type="button" class="mt-1 btn-outline-primary btn btn-sm"
                                    onclick="document.getElementById('archivos-atrasado').click()">
                                <i class="me-1 mdi mdi-paperclip"></i>Seleccionar
                            </button>
                        </div>
                        <div id="preview-atrasado" class="mt-2 row g-2"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notas" rows="2" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
                    </div>
                    <div id="msg-atrasado"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary btn btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold btn-sm" id="btn-enviar-atrasado">
                        <i class="me-1 mdi-clock-alert-outline mdi"></i>Enviar Atrasado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Sin Depósito ──────────────────────────────────── --}}
<div class="modal fade" id="modal-sin-deposito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 text-secondary mdi mdi-credit-card-off"></i>Registrar "Sin depósito"
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-sin-deposito">
                @csrf
                @method('PUT')
                <input type="hidden" id="sin-deposito-reporte-id">
                <div class="modal-body">
                    <div class="mb-3 py-2 alert alert-info small">
                        <i class="me-1 mdi-information-outline mdi"></i>
                        Registra el día como <strong>sin depósito</strong> cuando no hubo facturación en efectivo
                        (solo Yape/transferencias). No se adjuntan archivos, pero cuenta como reporte cumplido.
                    </div>
                    <p class="mb-3 small"><strong>Sede:</strong> <span id="sin-deposito-sede-label" class="text-primary fw-bold"></span></p>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                        <textarea id="sin-deposito-motivo" name="motivo" rows="3" maxlength="2000"
                                  class="form-control" placeholder="Ej: Todas las ventas del día fueron por Yape/transferencia."></textarea>
                    </div>
                    <div id="msg-sin-deposito"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary btn btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold btn-sm" id="btn-confirmar-sin-deposito">
                        <i class="me-1 mdi mdi-check"></i>Confirmar Sin Depósito
                    </button>
                </div>
            </form>
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
    sedes:    "{{ route('productividad.cobranza-sedes.cobranza.sedes') }}",
    kpiData:  "{{ route('productividad.cobranza-sedes.cobranza.kpi-data') }}",
    base:     "{{ url('/productividad/cobranza-sedes/deposito-efectivo') }}",
};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Helpers de URL dinámicas
const urlShow   = (id) => `${ROUTES.base}/${id}/show`;
const urlUpdate = (id) => `${ROUTES.base}/${id}`;
const urlSinDeposito = (id) => `${ROUTES.base}/${id}/sin-deposito`;
const urlRevisar= (id) => `${ROUTES.base}/${id}/revisar`;
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
        sub.textContent     = '{{ $countdownContextLabel }}, {{ $fechaLimiteLabel }} hora Lima';
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

setupDropZone('drop-zone-nuevo',           'archivos-nuevo',           'preview-nuevo');
setupDropZone('drop-zone-edit',            'archivos-edit',            'preview-edit');
setupDropZone('drop-zone-atrasado',        'archivos-atrasado',        'preview-atrasado');
setupDropZone('drop-zone-editar-historial','archivos-editar-historial','preview-editar-historial');

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
let _historialMeta = { current_page: 1, last_page: 1, total: 0 };

async function cargarHistorial(page = 1) {
    const tbody  = document.getElementById('historial-body');
    const loader = document.getElementById('historial-loader');
    loader.classList.remove('d-none');
    try {
        const sede  = document.getElementById('filtro-sede')?.value ?? '';
        const fecha = document.getElementById('filtro-fecha')?.value ?? '';
        const sort  = document.getElementById('sort-fecha')?.value ?? 'desc';
        let url = ROUTES.historial + `?page=${page}&sort=${sort}`;
        if (sede)  url += `&sede=${encodeURIComponent(sede)}`;
        if (fecha) url += `&fecha=${encodeURIComponent(fecha)}`;
        const data = await apiFetch(url);
        _historialMeta = { current_page: data.current_page, last_page: data.last_page, total: data.total };
        renderHistorial(data.data ?? []);
        renderPaginacion();
        const badge = document.getElementById('historial-total-badge');
        if (badge) { badge.textContent = `(${data.total} registros)`; badge.classList.remove('d-none'); }
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" class="py-3 text-danger text-center">${err.message}</td></tr>`;
    } finally {
        loader.classList.add('d-none');
    }
}

function aplicarFiltros() {
    cargarHistorial(1);
}

function renderPaginacion() {
    const bar   = document.getElementById('historial-pagination-bar');
    const info  = document.getElementById('historial-info');
    const pager = document.getElementById('historial-pager');
    if (!bar || !info || !pager) return;
    const { current_page: cur, last_page: last, total } = _historialMeta;
    if (last <= 1) { bar.classList.add('d-none'); return; }
    bar.classList.remove('d-none');
    info.textContent = `Página ${cur} de ${last} — ${total} registros`;
    let pages = [];
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || Math.abs(i - cur) <= 1) pages.push(i);
        else if (pages[pages.length - 1] !== '...') pages.push('...');
    }
    pager.innerHTML = `
        <li class="page-item ${cur <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault();cargarHistorial(${cur - 1})">&laquo;</a>
        </li>
        ${pages.map(p => p === '...'
            ? `<li class="page-item disabled"><span class="page-link">…</span></li>`
            : `<li class="page-item ${p === cur ? 'active' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault();cargarHistorial(${p})">${p}</a>
               </li>`
        ).join('')}
        <li class="page-item ${cur >= last ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault();cargarHistorial(${cur + 1})">&raquo;</a>
        </li>`;
}

function badgeEstado(estado) {
    const map    = { en_tiempo:'success', con_atraso:'danger', pendiente:'warning', no_enviado:'danger' };
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
                ${r.kpi !== null ? `<span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>` : '<span class="text-muted">—</span>'}
                ${r.editado_tarde ? '<br><small class="text-warning"><i class="mdi mdi-alert"></i> Editado tarde</small>' : ''}
            </td>
            <td>${badgeEstado(r.estado)}${r.sin_deposito ? ' <span class="bg-secondary badge" title="No hubo facturación en efectivo">Sin depósito</span>' : ''}${badgeRevision(r.revision_estado)}</td>
            <td class="text-center">
                ${r.sin_deposito ? '<span class="text-muted small"><i class="mdi mdi-credit-card-off"></i> N/A</span>' : (r.num_archivos > 0 ? `<span class="bg-info badge">${r.num_archivos}</span>` : '<span class="text-muted">0</span>')}
            </td>
            <td class="text-nowrap">
                <button class="px-2 py-0 btn-outline-primary btn btn-sm" onclick="verReporte(${r.id})" title="Ver detalles">
                    <i class="mdi mdi-eye"></i>
                </button>
                ${r.puede_editar ? `
                <button class="ms-1 px-2 py-0 btn-outline-warning btn btn-sm" onclick="abrirEditarHistorial(${r.id},'${r.sede}','${r.semana_inicio}')" title="Editar reporte">
                    <i class="mdi mdi-pencil"></i>
                </button>` : ''}
                ${r.puede_enviar_atrasado ? `
                <button class="ms-1 px-2 py-0 btn-outline-danger btn btn-sm" onclick="abrirEnviarAtrasado(${r.id},'${r.sede}')" title="Enviar reporte atrasado">
                    <i class="mdi-clock-alert-outline mdi"></i>
                </button>
                <button class="ms-1 px-2 py-0 btn-outline-success btn btn-sm" onclick="abrirSinDeposito(${r.id}, '${r.sede}')" title="Registrar sin depósito">
                    <i class="mdi mdi-credit-card-off"></i>
                </button>` : ''}
            </td>
        </tr>
    `).join('');
}

cargarHistorial(1);

// Poblar filtro sede desde API
(async function poblarFiltroSedeApi() {
    try {
        const sedes = await apiFetch(ROUTES.sedes);
        const sel   = document.getElementById('filtro-sede');
        if (!sel || !Array.isArray(sedes)) return;
        sedes.forEach(s => {
            const o = document.createElement('option');
            o.value = s; o.textContent = s;
            sel.appendChild(o);
        });
    } catch (_) {}
})();

// ── Modal Editar desde Historial ─────────────────────────────────
async function abrirEditarHistorial(id, sede, fecha) {
    document.getElementById('editar-historial-id').value = id;
    document.getElementById('editar-historial-titulo').textContent = `${sede} — ${fecha}`;
    document.getElementById('editar-historial-notas').value = '';
    document.getElementById('msg-editar-historial').innerHTML = '';
    document.getElementById('editar-historial-archivos-existentes').innerHTML =
        '<div class="py-2 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    _acumulados['archivos-editar-historial'] = new DataTransfer();
    document.getElementById('preview-editar-historial').innerHTML = '';

    new bootstrap.Modal(document.getElementById('modal-editar-historial')).show();

    try {
        const r = await apiFetch(urlShow(id));
        document.getElementById('editar-historial-notas').value = r.notas ?? '';
        const archivos   = r.archivos ?? [];
        const container  = document.getElementById('editar-historial-archivos-existentes');
        if (!archivos.length) {
            container.innerHTML = '<p class="text-muted small">Sin archivos previos.</p>';
        } else {
            container.innerHTML = `
                <label class="form-label fw-semibold">Archivos actuales</label>
                <div class="row g-2">
                    ${archivos.map((a, idx) => `
                    <div class="col-md-4 col-sm-6" id="eh-archivo-${idx}">
                        <div class="bg-light border rounded overflow-hidden" style="position:relative;">
                            ${a.es_imagen ? `
                            <div style="height:110px;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                                <img src="${a.preview_url}" alt="${a.name}"
                                     style="max-height:110px;max-width:100%;object-fit:contain;display:block;"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div style="display:none;height:110px;align-items:center;justify-content:center;">
                                    <i class="text-muted mdi mdi-image-broken" style="font-size:2rem;"></i>
                                </div>
                            </div>` : `
                            <div style="height:70px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;">
                                <i class="text-primary mdi mdi-file-document" style="font-size:2.5rem;"></i>
                            </div>`}
                            <div class="d-flex align-items-center gap-1 px-2 py-1">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="text-truncate" style="font-size:11px;font-weight:600;" title="${a.name}">${a.name}</div>
                                </div>
                                <button type="button" class="flex-shrink-0 px-1 py-0 btn-outline-danger btn btn-sm"
                                        id="eh-btn-del-${idx}" onclick="toggleEliminarHistorial(${idx}, this)" title="Eliminar">
                                    <i class="mdi-trash-can-outline mdi"></i>
                                </button>
                            </div>
                            <input type="hidden" name="eliminar_indices[]" id="eh-eliminar-${idx}" value="${idx}" disabled>
                        </div>
                    </div>`).join('')}
                </div>`;
        }
    } catch (err) {
        document.getElementById('editar-historial-archivos-existentes').innerHTML =
            `<div class="alert alert-danger small">${err.message}</div>`;
    }
}

function toggleEliminarHistorial(idx, btn) {
    const input = document.getElementById(`eh-eliminar-${idx}`);
    const card  = document.getElementById(`eh-archivo-${idx}`);
    if (!input) return;
    const marcado = input.disabled;
    input.disabled = !marcado;
    btn.classList.toggle('btn-outline-danger', marcado);
    btn.classList.toggle('btn-danger',        !marcado);
    if (card) card.style.opacity = marcado ? '0.45' : '';
}

document.getElementById('form-editar-historial')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar-editar-historial');
    const msg = document.getElementById('msg-editar-historial');
    const id  = document.getElementById('editar-historial-id').value;
    btn.disabled = true;
    btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Guardando...';
    msg.innerHTML = '';
    try {
        document.getElementById('archivos-editar-historial').files = _acumulados['archivos-editar-historial'].files;
        const data = await apiFetch(urlUpdate(id), { method: 'POST', body: new FormData(this) });
        msg.innerHTML = `<div class="py-2 alert alert-success">${data.message}</div>`;
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('modal-editar-historial'))?.hide();
            cargarHistorial(_historialMeta.current_page);
        }, 1500);
    } catch(err) {
        msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="me-1 mdi-content-save mdi"></i>Guardar Cambios';
    }
});

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
                ${r.sin_deposito ? `
                <div class="col-12">
                    <div class="mb-0 py-2 alert alert-info small">
                        <i class="me-1 mdi mdi-credit-card-off"></i>
                        Registrado como <strong>Sin depósito</strong> — no hubo facturación en efectivo ese día.
                    </div>
                </div>` : ''}
                ${r.notas ? `
                <div class="col-12">
                    <div class="text-muted text-uppercase small fw-semibold">${r.sin_deposito ? 'Motivo' : 'Notas'}</div>
                    <div class="bg-light p-2 border rounded small">${r.notas}</div>
                </div>` : ''}
            </div>
            <hr>
            <h6 class="mb-2 fw-semibold">Archivos adjuntos</h6>
            ${r.sin_deposito && !(r.archivos ?? []).length ? '<p class="text-muted small"><i class="me-1 mdi mdi-credit-card-off"></i>Sin depósito — no aplica adjuntar archivos.</p>' : archivosHTML}
            ${buildRevisionHTML(r, id)}
        `;
    } catch (err) {
        body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    }
}

// ── Revisión (conforme / rechazado) ───────────────────────────────
function badgeRevision(estado) {
    if (estado === 'conforme')  return ' <span class="bg-success badge" title="Revisión conforme"><i class="mdi mdi-check"></i></span>';
    if (estado === 'rechazado') return ' <span class="bg-danger badge" title="Reporte rechazado"><i class="mdi mdi-close"></i></span>';
    return '';
}

function buildRevisionHTML(r, id) {
    const estado = r.revision_estado;
    let statusBadge;
    if (estado === 'conforme')       statusBadge = '<span class="bg-success badge">Conforme</span>';
    else if (estado === 'rechazado') statusBadge = '<span class="bg-danger badge">Rechazado</span>';
    else                             statusBadge = '<span class="bg-primary badge">Pendiente de revisión</span>';

    let html = `
        <hr>
        <h6 class="mb-2 fw-semibold"><i class="me-1 mdi-clipboard-check-outline mdi"></i>Revisión</h6>
        <div class="d-flex align-items-center gap-2 mb-2">
            ${statusBadge}
            ${r.revision_revisor ? `<span class="text-muted small">por ${r.revision_revisor}${r.revision_at ? ' · ' + r.revision_at : ''}</span>` : ''}
        </div>
        ${estado === 'rechazado' && r.revision_motivo ? `<div class="mb-2 py-2 alert alert-danger small"><strong>Motivo:</strong> ${r.revision_motivo}</div>` : ''}
    `;

    if (!r.puede_revisar) return html;

    html += `
        <div class="bg-light mt-2 p-2 border rounded">
            <div class="mb-2 text-muted small">Marca el reporte según los documentos enviados:</div>
            <div class="mb-2" id="revision-motivo-wrap" style="display:none;">
                <textarea id="revision-motivo" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="Motivo del rechazo (obligatorio)..."></textarea>
            </div>
            <div id="revision-msg" class="mb-2"></div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm fw-bold" onclick="enviarRevision(${id}, 'conforme')">
                    <i class="me-1 mdi mdi-check-circle"></i>Conforme
                </button>
                <button class="btn btn-danger btn-sm fw-bold" onclick="solicitarRechazo(${id})">
                    <i class="me-1 mdi mdi-close-circle"></i>Rechazar
                </button>
            </div>
        </div>
    `;
    return html;
}

function solicitarRechazo(id) {
    const wrap = document.getElementById('revision-motivo-wrap');
    const ta   = document.getElementById('revision-motivo');
    const msg  = document.getElementById('revision-msg');
    if (wrap.style.display === 'none') {
        wrap.style.display = '';
        ta.focus();
        msg.innerHTML = '<div class="text-muted small">Escribe el motivo y vuelve a pulsar "Rechazar".</div>';
        return;
    }
    if (!ta.value.trim()) {
        ta.focus();
        msg.innerHTML = '<div class="text-danger small">Indica el motivo del rechazo.</div>';
        return;
    }
    enviarRevision(id, 'rechazado', ta.value.trim());
}

async function enviarRevision(id, estado, motivo = null) {
    const msg = document.getElementById('revision-msg');
    if (msg) msg.innerHTML = '<div class="text-muted small">Guardando…</div>';
    try {
        const fd = new FormData();
        fd.append('estado', estado);
        if (motivo) fd.append('motivo', motivo);
        const data = await apiFetch(urlRevisar(id), { method: 'POST', body: fd });
        if (msg) msg.innerHTML = `<div class="text-success small">${data.message}</div>`;
        setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('modal-reporte'))?.hide();
            cargarHistorial(_historialMeta?.current_page ?? 1);
        }, 700);
    } catch (err) {
        if (msg) msg.innerHTML = `<div class="text-danger small">${err.message}</div>`;
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

function opcionesChart() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f1f5f9' } },
        },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: c => `${c.dataset.label}: ${c.parsed.y} sedes`,
                }
            },
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
            type: 'bar',
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
            type: 'bar',
            data: { labels: data.labels ?? [], datasets: data.datasets ?? [] },
            options: opcionesChart(),
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

// ── Envío atrasado ────────────────────────────────────────────────
function abrirEnviarAtrasado(id, sede) {
    document.getElementById('atrasado-reporte-id').value = id;
    document.getElementById('atrasado-sede-label').textContent = sede;
    document.getElementById('msg-atrasado').innerHTML = '';
    _acumulados['archivos-atrasado'] = new DataTransfer();
    document.getElementById('preview-atrasado').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modal-enviar-atrasado')).show();
}

document.getElementById('form-atrasado')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-enviar-atrasado');
    const msg = document.getElementById('msg-atrasado');
    const id  = document.getElementById('atrasado-reporte-id').value;
    btn.disabled = true; btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Enviando...'; msg.innerHTML = '';
    try {
        document.getElementById('archivos-atrasado').files = _acumulados['archivos-atrasado'].files;
        const data = await apiFetch(urlUpdate(id), { method: 'POST', body: new FormData(this) });
        msg.innerHTML = `<div class="py-2 alert alert-warning">${data.message}</div>`;
        setTimeout(() => location.reload(), 1800);
    } catch(err) { msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="me-1 mdi-clock-alert-outline mdi"></i>Enviar Atrasado'; }
});

// ── Sin depósito ──────────────────────────────────────────────────
function abrirSinDeposito(id, sede) {
    document.getElementById('sin-deposito-reporte-id').value = id;
    document.getElementById('sin-deposito-sede-label').textContent = sede;
    document.getElementById('sin-deposito-motivo').value = '';
    document.getElementById('msg-sin-deposito').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modal-sin-deposito')).show();
}

document.getElementById('form-sin-deposito')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-confirmar-sin-deposito');
    const msg = document.getElementById('msg-sin-deposito');
    const id  = document.getElementById('sin-deposito-reporte-id').value;
    const motivo = document.getElementById('sin-deposito-motivo').value.trim();
    if (!motivo) {
        msg.innerHTML = '<div class="py-2 alert alert-danger">Debes indicar por qué no hubo depósito.</div>';
        return;
    }
    btn.disabled = true; btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Guardando...'; msg.innerHTML = '';
    try {
        const data = await apiFetch(urlSinDeposito(id), { method: 'POST', body: new FormData(this) });
        msg.innerHTML = `<div class="py-2 alert alert-success">${data.message}</div>`;
        setTimeout(() => location.reload(), 1500);
    } catch(err) { msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="me-1 mdi mdi-check"></i>Confirmar Sin Depósito'; }
});

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
