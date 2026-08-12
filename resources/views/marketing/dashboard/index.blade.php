@extends('layouts.app')

@section('title', 'Dashboard Marketing')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            {{-- ════════════ HEADER ════════════ --}}
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h3 class="mb-0 rate-percentage">
                                        <i class="me-2 text-primary mdi mdi-chart-line"></i>Dashboard Marketing
                                    </h3>
                                    <p class="mt-1 mb-0 text-muted">Encuestas de satisfacción por sede</p>
                                </div>
                                <button class="btn-outline-primary btn btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#filterCollapse">
                                    <i class="mdi-filter-variant mdi"></i> Rango de fechas
                                </button>
                            </div>

                            {{-- ════════════ FILTRO DE FECHAS (cabecera) ════════════ --}}
                            <div class="collapse mb-4" id="filterCollapse">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-3 card-title"><i
                                                class="mdi-filter-variant me-2 text-primary mdi"></i>Rango de fechas
                                        </h5>
                                        <form method="GET" action="{{ route('marketing.index') }}">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Fecha Inicio</label>
                                                    <input type="date" name="start_date" class="form-control"
                                                        value="{{ $startDate }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Fecha Fin</label>
                                                    <input type="date" name="end_date" class="form-control"
                                                        value="{{ $endDate }}">
                                                </div>
                                                <div class="d-flex align-items-end col-md-2">
                                                    <button type="submit" class="w-100 text-white btn btn-primary">
                                                        <i class="me-1 mdi mdi-magnify"></i> Filtrar
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="mb-0 mt-2 text-muted small">Afecta el total y el promedio de la
                                                cabecera. La meta y el cumplimiento semanal por sede son siempre de
                                                la semana en curso.</p>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- ════════════ STATS CARDS ════════════ --}}
                            <div class="mb-2 row">
                                <div class="grid-margin col-xl col-lg-3 col-md-6 col-sm-6 stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-file-document text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="mb-1 rate-percentage">{{ $stats['total'] }}</h3>
                                                <p class="mb-0 statistics-title">Total Encuestas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid-margin col-xl col-lg-3 col-md-6 col-sm-6 stretch-card">
                                    <div class="card card-statistics" style="border-top: 3px solid #6366f1;">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-primary-subtle mb-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-star icon-lg"></i>
                                                </div>
                                                <h3 class="mb-1 rate-percentage">
                                                    {{ number_format($stats['promedio'], 2) }}</h3>
                                                <p class="mb-0 statistics-title">Promedio Consolidado</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid-margin col-xl col-lg-3 col-md-6 col-sm-6 stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-success-subtle mb-3 rounded icon-wrapper">
                                                    <i class="text-success mdi mdi-target icon-lg"></i>
                                                </div>
                                                <h3 class="mb-1 rate-percentage">{{ $sedesCumplen }}/{{ $sedesConMeta->count() }}
                                                </h3>
                                                <p class="mb-0 statistics-title">Sedes cumpliendo meta (semana)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid-margin col-xl col-lg-3 col-md-6 col-sm-6 stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-danger-subtle mb-3 rounded icon-wrapper">
                                                    <i class="text-danger mdi mdi-alert-circle icon-lg"></i>
                                                </div>
                                                <h3 class="mb-1 rate-percentage">{{ $recentLowRated->count() }}</h3>
                                                <p class="mb-0 statistics-title">Alertas recientes</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ════════════ TABS ════════════ --}}
                            <ul class="mb-4 nav nav-tabs nav-tabs-line" id="dashboardTabs" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#tab-zona" type="button"><i
                                            class="me-1 mdi mdi-map-marker"></i>Por Sede</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-encuestas" type="button" id="btn-tab-encuestas">
                                        <i class="me-1 mdi mdi-clipboard-list"></i>Todas las Encuestas
                                    </button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-alertas" type="button">
                                        <i class="me-1 mdi mdi-alert-circle"></i>Alertas
                                        @if ($recentLowRated->count() > 0)
                                            <span class="bg-danger ms-1 badge">{{ $recentLowRated->count() }}</span>
                                        @endif
                                    </button></li>
                            </ul>

                            <div class="tab-content" id="dashboardTabsContent">

                                {{-- ══ TAB: POR SEDE ══ --}}
                                <div class="tab-pane fade show active" id="tab-zona" role="tabpanel">

                                    <div class="grid-margin card">
                                        <div class="card-body d-flex align-items-center flex-wrap gap-2">
                                            <i class="me-1 text-muted mdi mdi-information-outline"></i>
                                            <span class="text-muted small">
                                                Meta y cumplimiento son semanales (lunes–domingo). Solo cuentan
                                                encuestas con el formulario nuevo — las anteriores al rediseño no
                                                tienen las preguntas de sede/consultor/tiempos de entrega.
                                            </span>
                                            <button type="button" class="ms-auto btn-outline-primary btn btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalMeta">
                                                <i class="mdi mdi-target me-1"></i>Definir meta semanal
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @forelse ($sedeStats as $s)
                                            @php
                                                $semaforo = is_null($s['cumplimiento_pct'])
                                                    ? 'sin-meta'
                                                    : ($s['cumplimiento_pct'] < 70
                                                        ? 'rojo'
                                                        : ($s['cumplimiento_pct'] < 100
                                                            ? 'amarillo'
                                                            : 'verde'));
                                                $maxDia = collect($s['avance_diario'])->max('total') ?: 1;
                                            @endphp
                                            <div class="grid-margin col-xl-4 col-lg-6 col-md-6 stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                                            <h5 class="mb-0 card-title"><i
                                                                    class="me-1 text-primary mdi mdi-map-marker"></i>{{ $s['name'] }}
                                                            </h5>
                                                            <span
                                                                class="badge-cumplimiento {{ $semaforo }}">{{ is_null($s['cumplimiento_pct']) ? 'Sin meta' : $s['cumplimiento_pct'] . '%' }}</span>
                                                        </div>

                                                        <div class="mb-3 text-center row">
                                                            <div class="col-4">
                                                                <h4 class="mb-0 rate-percentage">
                                                                    {{ $s['obtenidas_semana'] }}</h4>
                                                                <p class="mb-0 statistics-title">Esta semana</p>
                                                            </div>
                                                            <div class="col-4">
                                                                <h4 class="mb-0 rate-percentage">
                                                                    {{ $s['meta_semanal'] ?? '—' }}</h4>
                                                                <p class="mb-0 statistics-title">Meta / semana</p>
                                                            </div>
                                                            <div class="col-4">
                                                                <h4 class="mb-0 rate-percentage">
                                                                    {{ $s['total_historico'] }}</h4>
                                                                <p class="mb-0 statistics-title">Total histórico</p>
                                                            </div>
                                                        </div>

                                                        <p class="mb-1 text-muted small">Avance por día</p>
                                                        <div class="mb-3 sede-week-bars">
                                                            @foreach ($s['avance_diario'] as $dia)
                                                                <div class="day" title="{{ $dia['total'] }} el {{ $dia['label'] }}">
                                                                    <div class="bar"
                                                                        style="height:{{ $dia['total'] > 0 ? max(10, round(($dia['total'] / $maxDia) * 100)) : 2 }}%;">
                                                                    </div>
                                                                    <div class="day-label">{{ $dia['label'] }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        @if ($s['total_historico'] > 0)
                                                            <div
                                                                class="d-flex align-items-center justify-content-between p-2 mb-2 rounded promedio-consolidado-box">
                                                                <span class="text-muted small">Promedio
                                                                    consolidado</span>
                                                                <span
                                                                    class="badge {{ $s['promedio'] >= 3.5 ? 'badge-success' : ($s['promedio'] >= 2.5 ? 'badge-warning' : 'badge-danger') }} px-3 py-2"
                                                                    style="font-size:.9rem;">
                                                                    ⭐ {{ number_format($s['promedio'], 2) }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        @if ($s['consultores']->count() > 0)
                                                            <p class="mb-1 text-muted small">Consultores asignados</p>
                                                            <ul class="p-0 mb-0 list-unstyled small">
                                                                @foreach ($s['consultores'] as $c)
                                                                    <li
                                                                        class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                                        <span><i
                                                                                class="me-1 text-muted mdi mdi-account-tie"></i>{{ $c['name'] }}</span>
                                                                        <span class="text-muted">
                                                                            {{ $c['total_surveys'] > 0 ? '⭐ ' . number_format($c['average_rating'], 2) . ' · ' . $c['total_surveys'] . ' enc.' : 'Sin encuestas' }}
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-warning">No hay sedes activas registradas en Marketing.</div>
                                            </div>
                                        @endforelse
                                    </div>

                                    @if ($sedesConMeta->count() > 1)
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <h5 class="mb-3 card-title"><i
                                                        class="me-2 text-info mdi mdi-chart-bar"></i>Cumplimiento semanal por sede</h5>
                                                <canvas id="chartZona" height="80"></canvas>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- ══ MODAL: Definir meta semanal por sede ══ --}}
                                <div class="modal fade" id="modalMeta" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form id="formMeta">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Definir meta semanal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sede</label>
                                                        <select class="form-select" name="sede_id" required>
                                                            <option value="">Selecciona una sede...</option>
                                                            @foreach ($sedesParaMeta as $sede)
                                                                <option value="{{ $sede->id }}">{{ $sede->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Meta de encuestas por semana</label>
                                                        <input type="number" class="form-control" name="meta_semanal" min="1" max="1000" required>
                                                    </div>
                                                    <p class="mb-0 text-muted small">Aplica desde hoy en adelante — no cambia el histórico de semanas anteriores.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar meta</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══ TAB: TODAS LAS ENCUESTAS ══ --}}
                                <div class="tab-pane fade" id="tab-encuestas" role="tabpanel">

                                    {{-- Filtros inline del tab --}}
                                    <div class="grid-margin card">
                                        <div class="py-3 card-body">
                                            <div class="align-items-end row g-2">
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Fecha Inicio</label>
                                                    <input type="date" id="enc-start"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Fecha Fin</label>
                                                    <input type="date" id="enc-end"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Sede</label>
                                                    <select id="enc-sede" class="form-select-sm form-select">
                                                        <option value="">Todas</option>
                                                        @foreach ($sedesParaMeta as $sede)
                                                            <option value="{{ $sede->id }}">{{ $sede->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="mb-1 form-label small">Experiencia General</label>
                                                    <select id="enc-rating" class="form-select-sm form-select">
                                                        <option value="">Todas</option>
                                                        <option value="4">😊 Muy Satisfecho</option>
                                                        <option value="3">🙂 Satisfecho</option>
                                                        <option value="2">😐 Insatisfecho</option>
                                                        <option value="1">😞 Muy Insatisfecho</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex gap-1 col-md-1">
                                                    <button class="w-100 text-white btn btn-primary btn-sm"
                                                        onclick="loadEncuestas(1)">
                                                        <i class="mdi mdi-magnify"></i>
                                                    </button>
                                                    <button class="btn-outline-secondary btn btn-sm"
                                                        onclick="resetEncuestas()" title="Limpiar filtros">
                                                        <i class="mdi mdi-refresh"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tabla --}}
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h4 class="mb-0 card-title">
                                                    <i class="me-2 text-primary mdi mdi-clipboard-list"></i>
                                                    Listado de Encuestas
                                                    <small class="ms-2 text-muted fw-normal" id="enc-count-label"></small>
                                                </h4>
                                                <small class="text-muted"><i class="me-1 mdi-cursor-pointer mdi"></i>Clic
                                                    en la fila para ver detalle</small>
                                            </div>

                                            {{-- Spinner --}}
                                            <div id="enc-loading" class="py-5 text-center" style="display:none;">
                                                <div class="spinner-border text-primary" role="status"></div>
                                                <p class="mt-2 text-muted small">Cargando encuestas...</p>
                                            </div>

                                            {{-- Tabla resultado --}}
                                            <div class="table-responsive" id="enc-table-wrapper">
                                                <table class="table table-hover" id="enc-table">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Fecha</th>
                                                            <th>Cliente</th>
                                                            <th>Sede</th>
                                                            <th class="text-center">Experiencia General</th>
                                                            <th class="text-center">Promedio</th>
                                                            <th>Comentario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="enc-tbody">
                                                        <tr>
                                                            <td colspan="7" class="py-4 text-muted text-center">
                                                                <i
                                                                    class="d-block mdi-filter-variant mb-2 mdi mdi-36px"></i>
                                                                Usa los filtros y presiona <strong>buscar</strong> para
                                                                cargar encuestas
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- Paginación --}}
                                            <div id="enc-pagination"
                                                class="d-flex align-items-center justify-content-between mt-3"
                                                style="display:none!important;">
                                                <small class="text-muted" id="enc-pagination-info"></small>
                                                <div id="enc-pagination-links" class="d-flex gap-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══ TAB: ALERTAS ══ --}}
                                <div class="tab-pane fade" id="tab-alertas" role="tabpanel">
                                    @php
                                        $sedesBajas = $sedeStats
                                            ->filter(fn($s) => $s['total_historico'] > 0 && $s['promedio'] < 2.5)
                                            ->sortBy('promedio');
                                    @endphp

                                    @if ($sedesBajas->count() > 0)
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <div class="mb-0 border-0 alert alert-danger">
                                                    <h5 class="mb-3"><i class="me-2 mdi mdi-alert-circle"></i>Sedes
                                                        con Promedio Consolidado Bajo (&lt; 2.5)</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sede</th>
                                                                    <th class="text-center">Promedio consolidado</th>
                                                                    <th class="text-center">Total histórico</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($sedesBajas as $s)
                                                                    <tr>
                                                                        <td><strong>{{ $s['name'] }}</strong></td>
                                                                        <td class="text-center"><span
                                                                                class="badge badge-danger fw-bold">{{ number_format($s['promedio'], 2) }}</span>
                                                                        </td>
                                                                        <td class="text-center">{{ $s['total_historico'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <div class="mb-0 border-0 alert alert-success">
                                                    <h5><i class="me-2 mdi mdi-check-circle"></i>Todo en Orden</h5>
                                                    <p class="mb-0">Ninguna sede tiene promedio consolidado crítico.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($recentLowRated->count() > 0)
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <h4 class="mb-3 card-title"><i
                                                        class="me-2 text-warning mdi mdi-alert"></i>Encuestas Negativas
                                                    Recientes <small class="text-muted fw-normal">(clic para ver
                                                        detalle)</small></h4>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Fecha</th>
                                                                <th>Cliente</th>
                                                                <th>Sede</th>
                                                                <th class="text-center">Peor calificación</th>
                                                                <th>Comentario</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($recentLowRated as $sv)
                                                                @php
                                                                    $peor = collect([
                                                                        $sv->experience_rating,
                                                                        $sv->sede_rating,
                                                                        $sv->consultor_rating,
                                                                        $sv->tiempos_entrega_rating,
                                                                        $sv->promociones_rating,
                                                                    ])->filter()->min();
                                                                @endphp
                                                                <tr class="survey-row"
                                                                    data-survey-id="{{ $sv->id }}"
                                                                    style="cursor:pointer;">
                                                                    <td><small
                                                                            class="text-muted">#{{ $sv->id }}</small>
                                                                    </td>
                                                                    <td><small>{{ $sv->created_at->format('d/m H:i') }}</small>
                                                                    </td>
                                                                    <td>{{ $sv->client_name ?: 'Anónimo' }}</td>
                                                                    <td>{{ $sv->display_entity->name }}</td>
                                                                    <td class="text-center">@include(
                                                                        'marketing.dashboard._rating_badge',
                                                                        ['rating' => $peor]
                                                                    )
                                                                    </td>
                                                                    <td><small>{{ Str::limit($sv->comments ?? '—', 50) }}</small>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>{{-- /tab-content --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════ MODAL DETALLE ENCUESTA ════════════ --}}
    <div class="modal fade" id="surveyDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="shadow border-0 modal-content">
                <div class="text-white modal-header" id="modalHeader"
                    style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <div>
                        <h5 class="mb-0 modal-title"><i class="me-2 mdi mdi-clipboard-text"></i>Detalle de Encuesta <span
                                id="modalSurveyId"></span></h5>
                        <small class="opacity-75" id="modalDate"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-0 modal-body" id="modalBody">
                    <div class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"><span
                                class="visually-hidden">Cargando...</span></div>
                        <p class="mt-2 text-muted">Cargando detalle...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-lg {
            font-size: 2rem;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-cumplimiento {
            display: inline-block;
            min-width: 60px;
            padding: .35em .6em;
            font-weight: 700;
            border-radius: .375rem;
        }

        .badge-cumplimiento.rojo {
            background: #f8d7da;
            color: #842029;
        }

        .badge-cumplimiento.amarillo {
            background: #fff3cd;
            color: #664d03;
        }

        .badge-cumplimiento.verde {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-cumplimiento.sin-meta {
            background: #e9ecef;
            color: #6c757d;
        }

        .sede-week-bars {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 40px;
        }

        .sede-week-bars .day {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            gap: 3px;
            height: 100%;
        }

        .sede-week-bars .bar {
            width: 100%;
            max-width: 18px;
            background: #6366f1;
            border-radius: 3px 3px 0 0;
            min-height: 2px;
        }

        .sede-week-bars .day-label {
            font-size: 9px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .promedio-consolidado-box {
            background: rgba(99, 102, 241, .06);
        }

        .nav-tabs-line .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            padding: .75rem 1.25rem;
        }

        .nav-tabs-line .nav-link.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
            background: transparent;
        }

        .nav-tabs-line .nav-link:hover {
            border-bottom-color: #6366f1;
            color: #6366f1;
        }

        .survey-row:hover {
            background: rgba(99, 102, 241, .06) !important;
        }

        .bg-primary-subtle {
            background: rgba(99, 102, 241, .1) !important;
        }

        .bg-success-subtle {
            background: rgba(76, 175, 80, .1) !important;
        }

        .bg-info-subtle {
            background: rgba(13, 202, 240, .1) !important;
        }

        .bg-warning-subtle {
            background: rgba(255, 193, 7, .1) !important;
        }

        .bg-danger-subtle {
            background: rgba(220, 53, 69, .1) !important;
        }

        /* modal rating pills */
        .modal-rating-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            border-radius: 12px;
        }

        .modal-rating-pill .emoji {
            font-size: 2rem;
        }

        .modal-rating-pill .label {
            font-size: .68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-align: center;
        }

        .modal-rating-pill.muy-feliz {
            background: rgba(76, 175, 80, .1);
            color: #2e7d32;
        }

        .modal-rating-pill.feliz {
            background: rgba(13, 202, 240, .1);
            color: #0277bd;
        }

        .modal-rating-pill.insatisfecho {
            background: rgba(255, 193, 7, .1);
            color: #e65100;
        }

        .modal-rating-pill.muy-insatisfecho {
            background: rgba(220, 53, 69, .1);
            color: #c62828;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Avatar de iniciales generado en cliente (FRONTEND.md, F2) — reemplaza al
        // fetch dinámico a ui-avatars.com dentro de innerHTML construido por JS.
        function avatarInicialesHtml(nombre, background = '6366f1', size = 32, extraClass = '') {
            const iniciales = String(nombre || '').trim().split(/\s+/).filter(Boolean)
                .slice(0, 2).map(p => p[0].toUpperCase()).join('') || '?';
            const fontSize = Math.max(10, Math.round(size / 2.5));
            return `<span class="avatar-iniciales ${extraClass}" style="display:inline-flex;align-items:center;justify-content:center;width:${size}px;height:${size}px;background-color:#${background};color:#fff;font-weight:600;font-size:${fontSize}px;line-height:1;flex-shrink:0;" title="${nombre ?? ''}">${iniciales}</span>`;
        }

        const CHART_DEFAULTS = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        };

        const EMOJIS = [null, '😞', '😐', '🙂', '😊'];
        const LABELS = [null, 'Muy Insatisfecho', 'Insatisfecho', 'Satisfecho', 'Muy Satisfecho'];

        document.addEventListener('DOMContentLoaded', () => {

            // Cumplimiento semanal por sede
            @php
                $sedesConMetaChart = $sedesConMeta->values();
            @endphp
            const zonaLabels = @json($sedesConMetaChart->pluck('name'));
            const zonaAvgs = @json($sedesConMetaChart->pluck('cumplimiento_pct'));

            const zonaEl = document.getElementById('chartZona');
            if (zonaEl && zonaLabels.length > 0) {
                new Chart(zonaEl, {
                    type: 'bar',
                    data: {
                        labels: zonaLabels,
                        datasets: [{
                            label: 'Cumplimiento (%)',
                            data: zonaAvgs,
                            backgroundColor: zonaAvgs.map(v => v >= 100 ? '#198754' : (v >= 70 ? '#ffc107' :
                                '#dc3545')),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...CHART_DEFAULTS,
                        plugins: {
                            ...CHART_DEFAULTS.plugins,
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (v) => v + '%'
                                }
                            }
                        }
                    }
                });
            }

            // Guardar meta semanal por sede
            const formMeta = document.getElementById('formMeta');
            if (formMeta) {
                formMeta.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const fd = new FormData(formMeta);
                    try {
                        const res = await fetch('{{ route('marketing.metas.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: fd
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('No se pudo guardar la meta.');
                        }
                    } catch (err) {
                        alert('Error de conexión al guardar la meta.');
                    }
                });
            }

            // Tooltips Bootstrap
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

            // Filas de encuestas ya renderizadas en el servidor (tab Alertas)
            document.querySelectorAll('.survey-row').forEach(row => {
                row.addEventListener('click', () => openSurveyModal(row.dataset.surveyId));
            });
        });

        // ─── MODAL DETALLE ENCUESTA ─────────────────────────────────────────────
        function openSurveyModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('surveyDetailModal'));
            const body = document.getElementById('modalBody');
            const header = document.getElementById('modalHeader');

            document.getElementById('modalSurveyId').textContent = '#' + id;
            document.getElementById('modalDate').textContent = '';
            body.innerHTML =
                `<div class="py-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Cargando...</p></div>`;
            header.style.background = 'linear-gradient(135deg,#667eea,#764ba2)';
            modal.show();

            fetch(`/marketing/surveys/${id}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error('Error');
                    const sv = data.survey;

                    const peor = Math.min(...sv.preguntas.map(p => p.valor));
                    const headerBg = peor === 1 ? 'linear-gradient(135deg,#c62828,#e53935)' :
                        peor === 2 ? 'linear-gradient(135deg,#e65100,#f57c00)' :
                        peor === 3 ? 'linear-gradient(135deg,#0277bd,#039be5)' :
                        'linear-gradient(135deg,#2e7d32,#43a047)';
                    header.style.background = headerBg;
                    document.getElementById('modalDate').textContent = sv.created_at;

                    function ratingPill(pregunta) {
                        const cls = {
                            4: 'muy-feliz',
                            3: 'feliz',
                            2: 'insatisfecho',
                            1: 'muy-insatisfecho'
                        } [pregunta.valor] ?? '';
                        const emoji = EMOJIS[pregunta.valor] ?? '?';
                        return `<div class="text-center col">
                            <small class="d-block mb-2 text-muted fw-bold">${pregunta.label}</small>
                            <div class="modal-rating-pill ${cls}">
                                <span class="emoji">${emoji}</span>
                                <span class="label">${pregunta.texto}</span>
                                <small>(${pregunta.valor}/4)</small>
                            </div>
                        </div>`;
                    }

                    const comb = sv.promedio;
                    const combCls = comb >= 3.5 ? 'success' : comb >= 2.5 ? 'warning' : 'danger';

                    body.innerHTML = `
            <div class="p-4">
                {{-- Evaluado --}}
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    ${avatarInicialesHtml(sv.evaluado_name, '6366f1', 56, 'me-3 rounded-circle')}
                    <div>
                        <h5 class="mb-1">${sv.evaluado_name}</h5>
                        <span class="badge badge-primary">${sv.evaluado_role === 'trimax' ? 'TRIMAX General' : 'Sede — '+(sv.evaluado_location ?? '')}</span>
                    </div>
                    <div class="ms-auto text-end">
                        <small class="d-block text-muted">Cliente</small>
                        <strong>${sv.client_name}</strong>
                        ${sv.ruc ? `<small class="d-block text-muted">RUC ${sv.ruc}</small>` : ''}
                    </div>
                </div>

                {{-- Calificaciones --}}
                <div class="mb-4 row g-3">
                    ${sv.preguntas.map(ratingPill).join('')}
                </div>

                {{-- Promedio consolidado --}}
                <div class="mb-4 text-center">
                    <small class="d-block mb-2 text-muted fw-bold">Promedio Consolidado</small>
                    <span class="badge bg-${combCls} px-4 py-3" style="font-size:1.4rem;">⭐ ${comb}</span>
                    <small class="d-block text-muted mt-1">/ 4.00</small>
                    <div class="progress mt-2" style="height:12px;border-radius:6px;max-width:320px;margin:0 auto;">
                        <div class="progress-bar bg-${combCls}" style="width:${(comb/4)*100}%"></div>
                    </div>
                </div>

                {{-- Comentarios --}}
                <div>
                    <p class="mb-2 fw-bold"><i class="me-1 text-primary mdi mdi-comment-text"></i>Comentarios del cliente</p>
                    ${sv.comments
                        ? `<blockquote class="p-3 rounded blockquote-style" style="background:#fff8e1;border-left:4px solid #ffc107;font-style:italic;color:#555;">"${sv.comments}"</blockquote>`
                        : `<p class="text-muted">El cliente no dejó comentarios.</p>`
                    }
                </div>
            </div>`;
                })
                .catch(() => {
                    body.innerHTML =
                        `<div class="py-5 text-danger text-center"><i class="d-block mb-2 mdi mdi-alert-circle mdi-48px"></i>Error al cargar el detalle</div>`;
                });
        }

        // ─── TAB: TODAS LAS ENCUESTAS (AJAX + paginación) ───────────────────────────
        const RATING_BADGE = {
            4: '<span class="badge badge-success"><i class="mdi mdi-emoticon-excited"></i> Muy Satisfecho</span>',
            3: '<span class="badge badge-info"><i class="mdi mdi-emoticon-happy"></i> Satisfecho</span>',
            2: '<span class="badge badge-warning"><i class="mdi mdi-emoticon-neutral"></i> Insatisfecho</span>',
            1: '<span class="badge badge-danger"><i class="mdi mdi-emoticon-sad"></i> Muy Insatisfecho</span>',
        };

        let encCurrentPage = 1;

        function loadEncuestas(page = 1) {
            encCurrentPage = page;
            const start = document.getElementById('enc-start').value;
            const end = document.getElementById('enc-end').value;
            const sedeId = document.getElementById('enc-sede').value;
            const rating = document.getElementById('enc-rating').value;

            const params = new URLSearchParams({
                page
            });
            if (start) params.append('start_date', start);
            if (end) params.append('end_date', end);
            if (sedeId) params.append('sede_id', sedeId);
            if (rating) params.append('rating', rating);

            document.getElementById('enc-loading').style.display = 'block';
            document.getElementById('enc-table-wrapper').style.opacity = '0.4';

            fetch(`/marketing/encuestas/ajax?${params}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('enc-loading').style.display = 'none';
                    document.getElementById('enc-table-wrapper').style.opacity = '1';

                    const tbody = document.getElementById('enc-tbody');

                    if (!data.data || data.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="py-5 text-muted text-center">
                    <i class="d-block mb-2 mdi mdi-inbox mdi-36px"></i>No se encontraron encuestas con los filtros aplicados
                </td></tr>`;
                        document.getElementById('enc-count-label').textContent = '';
                        document.getElementById('enc-pagination').style.display = 'none';
                        return;
                    }

                    tbody.innerHTML = data.data.map(sv => {
                        const combCls = sv.promedio >= 3.5 ? 'badge-success' : sv.promedio >= 2.5 ? 'badge-warning' :
                            'badge-danger';
                        const sedeLabel = sv.evaluado_role === 'trimax' ? 'TRIMAX General' : sv.evaluado_name;
                        return `<tr class="survey-row" data-survey-id="${sv.id}" style="cursor:pointer;" title="Clic para ver detalle">
                    <td><small class="text-muted">#${sv.id}</small></td>
                    <td>
                        <small class="d-block text-muted">${sv.date}</small>
                        <small>${sv.time}</small>
                    </td>
                    <td>
                        ${sv.client_name
                            ? `<div class="d-flex align-items-center">
                                    ${avatarInicialesHtml(sv.client_name, '10b981', 32, 'me-2 rounded-circle')}
                                    <strong>${sv.client_name}</strong>
                                   </div>`
                            : '<span class="text-muted"><i class="mdi mdi-account-off"></i> Anónimo</span>'
                        }
                    </td>
                    <td><strong>${sedeLabel}</strong></td>
                    <td class="text-center">${RATING_BADGE[sv.experience_rating] ?? sv.experience_rating}</td>
                    <td class="text-center"><span class="badge ${combCls}">${sv.promedio}</span></td>
                    <td><small class="text-muted">${sv.comments ? sv.comments.substring(0,45) + (sv.comments.length > 45 ? '…' : '') : '—'}</small></td>
                </tr>`;
                    }).join('');

                    // Registrar event listeners para el modal
                    document.querySelectorAll('#enc-tbody .survey-row').forEach(row => {
                        row.addEventListener('click', () => openSurveyModal(row.dataset.surveyId));
                    });

                    // Info paginación
                    document.getElementById('enc-count-label').textContent =
                        `(${data.from}–${data.to} de ${data.total})`;
                    document.getElementById('enc-pagination').style.display = 'flex';
                    document.getElementById('enc-pagination-info').textContent =
                        `Mostrando ${data.from}–${data.to} de ${data.total} encuestas`;

                    // Links de paginación
                    const linksEl = document.getElementById('enc-pagination-links');
                    linksEl.innerHTML = '';
                    if (data.prev_page_url) {
                        linksEl.innerHTML += `<button class="btn-outline-primary btn btn-sm" onclick="loadEncuestas(${data.current_page - 1})" aria-label="Página anterior">
                    <i class="mdi-chevron-left mdi"></i>
                </button>`;
                    }
                    linksEl.innerHTML +=
                        `<span class="text-white btn btn-sm btn-primary disabled">${data.current_page} / ${data.last_page}</span>`;
                    if (data.next_page_url) {
                        linksEl.innerHTML += `<button class="btn-outline-primary btn btn-sm" onclick="loadEncuestas(${data.current_page + 1})" aria-label="Página siguiente">
                    <i class="mdi-chevron-right mdi"></i>
                </button>`;
                    }
                })
                .catch(err => {
                    document.getElementById('enc-loading').style.display = 'none';
                    document.getElementById('enc-table-wrapper').style.opacity = '1';
                    console.error('Error cargando encuestas:', err);
                });
        }

        function resetEncuestas() {
            document.getElementById('enc-start').value = '';
            document.getElementById('enc-end').value = '';
            document.getElementById('enc-sede').value = '';
            document.getElementById('enc-rating').value = '';
            loadEncuestas(1);
        }

        // Cargar automáticamente al activar el tab — sin filtros iniciales
        document.getElementById('btn-tab-encuestas').addEventListener('shown.bs.tab', () => {
            loadEncuestas(1);
        });
    </script>
@endpush
