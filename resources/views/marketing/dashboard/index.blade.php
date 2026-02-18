@extends('layouts.app')

@section('title', 'Dashboard Marketing')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                Dashboard Marketing
                                            </h3>
                                            <p class="text-muted mt-1">Análisis de satisfacción y encuestas</p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                                <i class="mdi mdi-filter-variant"></i> Filtros
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros Colapsables -->
                            <div class="collapse mb-4" id="filterCollapse">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            <i class="mdi mdi-filter-variant text-primary me-2"></i>
                                            Filtros de Búsqueda
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
                                                <div class="col-md-4">
                                                    <label class="form-label">Consultor / Sede / Trimax</label>
                                                    <select name="user_id" class="form-select">
                                                        <option value="">Todos</option>
                                                        @foreach ($users as $userMkt)
                                                            <option value="{{ $userMkt->id }}"
                                                                {{ $userId == $userMkt->id ? 'selected' : '' }}>
                                                                {{ $userMkt->name }} -
                                                                @if ($userMkt->role === 'consultor')
                                                                    Consultor
                                                                @elseif ($userMkt->role === 'trimax')
                                                                    TRIMAX General
                                                                @else
                                                                    Sede {{ $userMkt->location }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="submit" class="btn btn-primary w-100 text-white">
                                                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-file-document text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['total'] }}</h3>
                                                <p class="statistics-title mb-0">Total Encuestas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-success-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-excited text-success icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['muy_feliz'] }}</h3>
                                                <p class="statistics-title mb-0">Muy Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-info-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-happy text-info icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['feliz'] }}</h3>
                                                <p class="statistics-title mb-0">Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-warning-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-neutral text-warning icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['insatisfecho'] }}</h3>
                                                <p class="statistics-title mb-0">Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-danger-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-sad text-danger icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['muy_insatisfecho'] }}</h3>
                                                <p class="statistics-title mb-0">Muy Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-star text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">
                                                    {{ number_format($stats['average_experience'], 2) }}</h3>
                                                <p class="statistics-title mb-0">Promedio General</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs de navegación -->
                            <ul class="nav nav-tabs nav-tabs-line mb-4" id="dashboardTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab"
                                        data-bs-target="#dashboard" type="button">
                                        <i class="mdi mdi-view-dashboard me-1"></i> General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="zona-tab" data-bs-toggle="tab" data-bs-target="#zona"
                                        type="button">
                                        <i class="mdi mdi-map-marker me-1"></i> Por Zona
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="reconocimientos-tab" data-bs-toggle="tab"
                                        data-bs-target="#reconocimientos" type="button">
                                        <i class="mdi mdi-trophy me-1"></i> Reconocimientos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tendencias-tab" data-bs-toggle="tab"
                                        data-bs-target="#tendencias" type="button">
                                        <i class="mdi mdi-chart-line me-1"></i> Tendencias
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="alertas-tab" data-bs-toggle="tab"
                                        data-bs-target="#alertas" type="button">
                                        <i class="mdi mdi-alert-circle me-1"></i> Alertas
                                    </button>
                                </li>
                            </ul>

                            <!-- Contenido de los tabs -->
                            <div class="tab-content" id="dashboardTabsContent">

                                <!-- TAB 1: Dashboard General -->
                                <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                                    <!-- Gráficos -->
                                    <div class="row">
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-donut text-primary me-2"></i>
                                                        Distribución de Experiencia
                                                    </h4>
                                                    <canvas id="ratingChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-bar text-info me-2"></i>
                                                        Calidad de Atención por Usuario
                                                    </h4>
                                                    <canvas id="serviceChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla por Consultor/Sede/Trimax -->
                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-account-group text-primary me-2"></i>
                                                        Estadísticas por Consultor / Sede / Trimax
                                                    </h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nombre</th>
                                                                    <th>Tipo</th>
                                                                    <th>Total</th>
                                                                    <th class="text-center">Muy Feliz</th>
                                                                    <th class="text-center">Feliz</th>
                                                                    <th class="text-center">Insatis.</th>
                                                                    <th class="text-center">Muy Insatis.</th>
                                                                    <th>Promedio</th>
                                                                    <th style="width: 200px;">Distribución</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($userStats as $stat)
                                                                    <tr
                                                                        @if ($stat['role'] === 'trimax') style="background-color: rgba(26,26,46,0.04);" @endif>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img class="img-xs rounded-circle me-2"
                                                                                    src="https://ui-avatars.com/api/?name={{ urlencode($stat['name']) }}&background={{ $stat['role'] === 'trimax' ? '1a1a2e' : '6366f1' }}&color=fff"
                                                                                    alt="profile">
                                                                                <div>
                                                                                    <strong>{{ $stat['name'] }}</strong>
                                                                                    @if ($stat['role'] === 'consultor' && $stat['sedes_count'] > 0)
                                                                                        <br>
                                                                                        <small class="text-muted">
                                                                                            <i
                                                                                                class="mdi mdi-office-building"></i>
                                                                                            {{ $stat['sedes_count'] }}
                                                                                            {{ $stat['sedes_count'] == 1 ? 'sede' : 'sedes' }}
                                                                                        </small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            @if ($stat['role'] === 'consultor')
                                                                                <span
                                                                                    class="badge badge-primary">Consultor</span>
                                                                                @if ($stat['sedes_count'] > 0)
                                                                                    <span
                                                                                        class="badge badge-info">+Sedes</span>
                                                                                @endif
                                                                            @elseif ($stat['role'] === 'trimax')
                                                                                <span class="badge"
                                                                                    style="background-color: #1a1a2e; color: #fff;">
                                                                                    <i
                                                                                        class="mdi mdi-domain me-1"></i>TRIMAX
                                                                                </span>
                                                                            @else
                                                                                <span class="badge badge-info">
                                                                                    Sede {{ $stat['location'] }}
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <strong
                                                                                class="text-primary">{{ $stat['total_surveys'] }}</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-success">{{ $stat['muy_feliz'] }}</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-info">{{ $stat['feliz'] }}</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-warning">{{ $stat['insatisfecho'] }}</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-danger">{{ $stat['muy_insatisfecho'] }}</span>
                                                                        </td>
                                                                        <td>
                                                                            <strong>{{ number_format($stat['avg_experience'], 2) }}</strong>
                                                                            <i class="mdi mdi-star text-warning"></i>
                                                                        </td>
                                                                        <td>
                                                                            @if ($stat['total_surveys'] > 0)
                                                                                <div class="progress"
                                                                                    style="height: 25px;">
                                                                                    @php
                                                                                        $total = $stat['total_surveys'];
                                                                                        $muyFelizPct =
                                                                                            ($stat['muy_feliz'] /
                                                                                                $total) *
                                                                                            100;
                                                                                        $felizPct =
                                                                                            ($stat['feliz'] / $total) *
                                                                                            100;
                                                                                        $insatisfechoPct =
                                                                                            ($stat['insatisfecho'] /
                                                                                                $total) *
                                                                                            100;
                                                                                        $muyInsatisfechoPct =
                                                                                            ($stat['muy_insatisfecho'] /
                                                                                                $total) *
                                                                                            100;
                                                                                    @endphp
                                                                                    @if ($muyFelizPct > 0)
                                                                                        <div class="progress-bar bg-success"
                                                                                            style="width: {{ $muyFelizPct }}%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ round($muyFelizPct) }}%">
                                                                                            {{ round($muyFelizPct) > 10 ? round($muyFelizPct) . '%' : '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if ($felizPct > 0)
                                                                                        <div class="progress-bar bg-info"
                                                                                            style="width: {{ $felizPct }}%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ round($felizPct) }}%">
                                                                                            {{ round($felizPct) > 10 ? round($felizPct) . '%' : '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if ($insatisfechoPct > 0)
                                                                                        <div class="progress-bar bg-warning"
                                                                                            style="width: {{ $insatisfechoPct }}%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ round($insatisfechoPct) }}%">
                                                                                            {{ round($insatisfechoPct) > 10 ? round($insatisfechoPct) . '%' : '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                    @if ($muyInsatisfechoPct > 0)
                                                                                        <div class="progress-bar bg-danger"
                                                                                            style="width: {{ $muyInsatisfechoPct }}%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="{{ round($muyInsatisfechoPct) }}%">
                                                                                            {{ round($muyInsatisfechoPct) > 10 ? round($muyInsatisfechoPct) . '%' : '' }}
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
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

                                    <!-- Encuestas Recientes -->
                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-clock-outline text-info me-2"></i>
                                                        Encuestas Recientes
                                                    </h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Cliente</th>
                                                                    <th>Evaluado</th>
                                                                    <th>Experiencia</th>
                                                                    <th>Atención</th>
                                                                    <th>Comentarios</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($recentSurveys as $survey)
                                                                    <tr>
                                                                        <td>
                                                                            <small
                                                                                class="text-muted">{{ $survey->created_at->format('d/m/Y') }}</small><br>
                                                                            <small>{{ $survey->created_at->format('H:i') }}</small>
                                                                        </td>
                                                                        <td>
                                                                            @if ($survey->client_name)
                                                                                <div class="d-flex align-items-center">
                                                                                    <img class="img-xs rounded-circle me-2"
                                                                                        src="https://ui-avatars.com/api/?name={{ urlencode($survey->client_name) }}&background=10b981&color=fff"
                                                                                        alt="client">
                                                                                    <strong>{{ $survey->client_name }}</strong>
                                                                                </div>
                                                                            @else
                                                                                <span class="text-muted">
                                                                                    <i class="mdi mdi-account-off"></i>
                                                                                    Anónimo
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <strong>{{ $survey->userMarketing->name }}</strong><br>
                                                                            <small class="text-muted">
                                                                                @if ($survey->userMarketing->role === 'consultor')
                                                                                    Consultor
                                                                                @elseif ($survey->userMarketing->role === 'trimax')
                                                                                    TRIMAX General
                                                                                @else
                                                                                    Sede
                                                                                    {{ $survey->userMarketing->location }}
                                                                                @endif
                                                                            </small>
                                                                        </td>
                                                                        <td>
                                                                            @if ($survey->experience_rating == 4)
                                                                                <span class="badge badge-success">
                                                                                    <i
                                                                                        class="mdi mdi-emoticon-excited"></i>
                                                                                    Muy Feliz
                                                                                </span>
                                                                            @elseif($survey->experience_rating == 3)
                                                                                <span class="badge badge-info">
                                                                                    <i class="mdi mdi-emoticon-happy"></i>
                                                                                    Feliz
                                                                                </span>
                                                                            @elseif($survey->experience_rating == 2)
                                                                                <span class="badge badge-warning">
                                                                                    <i
                                                                                        class="mdi mdi-emoticon-neutral"></i>
                                                                                    Insatisfecho
                                                                                </span>
                                                                            @else
                                                                                <span class="badge badge-danger">
                                                                                    <i class="mdi mdi-emoticon-sad"></i>
                                                                                    Muy Insatisfecho
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($survey->service_quality_rating == 4)
                                                                                <span class="badge badge-success">Muy
                                                                                    Feliz</span>
                                                                            @elseif($survey->service_quality_rating == 3)
                                                                                <span class="badge badge-info">Feliz</span>
                                                                            @elseif($survey->service_quality_rating == 2)
                                                                                <span
                                                                                    class="badge badge-warning">Insatisfecho</span>
                                                                            @else
                                                                                <span class="badge badge-danger">Muy
                                                                                    Insatisfecho</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($survey->comments)
                                                                                <small>{{ Str::limit($survey->comments, 60) }}</small>
                                                                            @else
                                                                                <small class="text-muted">Sin
                                                                                    comentarios</small>
                                                                            @endif
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
                                </div>

                                <!-- TAB 2: Detalles por Zona -->
                                <div class="tab-pane fade" id="zona" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-map-marker text-primary me-2"></i>
                                                Análisis por Ubicación
                                            </h4>
                                        </div>
                                    </div>

                                    @php
                                        $sedeStats = $userStats->where('role', 'sede');
                                        $zonas = $sedeStats->groupBy('location');
                                    @endphp

                                    <div class="row">
                                        @foreach ($zonas as $location => $sedes)
                                            @php
                                                $totalZona = $sedes->sum('total_surveys');
                                                $avgZona = $sedes->avg('avg_experience');
                                                $muyFelizZona = $sedes->sum('muy_feliz');
                                                $felizZona = $sedes->sum('feliz');
                                                $insatisfechoZona = $sedes->sum('insatisfecho');
                                                $muyInsatisfechoZona = $sedes->sum('muy_insatisfecho');
                                            @endphp
                                            <div class="col-xl-4 col-lg-6 col-md-6 grid-margin stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <h4 class="card-title mb-0">
                                                                <i class="mdi mdi-map-marker text-primary me-1"></i>
                                                                {{ $location }}
                                                            </h4>
                                                            <span class="badge badge-primary">
                                                                {{ $sedes->count() }}
                                                                {{ $sedes->count() == 1 ? 'Sede' : 'Sedes' }}
                                                            </span>
                                                        </div>

                                                        <div class="row text-center mb-3">
                                                            <div class="col-6">
                                                                <div class="icon-wrapper bg-primary-subtle rounded mb-2">
                                                                    <i class="mdi mdi-chart-box text-primary icon-lg"></i>
                                                                </div>
                                                                <h3 class="rate-percentage mb-0">{{ $totalZona }}</h3>
                                                                <p class="statistics-title mb-0">Encuestas</p>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="icon-wrapper bg-warning-subtle rounded mb-2">
                                                                    <i class="mdi mdi-star text-warning icon-lg"></i>
                                                                </div>
                                                                <h3 class="rate-percentage mb-0">
                                                                    {{ number_format($avgZona, 2) }}</h3>
                                                                <p class="statistics-title mb-0">Promedio</p>
                                                            </div>
                                                        </div>

                                                        @if ($totalZona > 0)
                                                            <div class="border-top pt-3">
                                                                <small class="text-muted d-block mb-2">Distribución de
                                                                    satisfacción</small>
                                                                <div class="progress" style="height: 25px;">
                                                                    @php
                                                                        $mfPct = ($muyFelizZona / $totalZona) * 100;
                                                                        $fPct = ($felizZona / $totalZona) * 100;
                                                                        $iPct = ($insatisfechoZona / $totalZona) * 100;
                                                                        $miPct =
                                                                            ($muyInsatisfechoZona / $totalZona) * 100;
                                                                    @endphp
                                                                    @if ($mfPct > 0)
                                                                        <div class="progress-bar bg-success"
                                                                            style="width: {{ $mfPct }}%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Muy Feliz: {{ round($mfPct) }}%">
                                                                            {{ round($mfPct) > 10 ? round($mfPct) . '%' : '' }}
                                                                        </div>
                                                                    @endif
                                                                    @if ($fPct > 0)
                                                                        <div class="progress-bar bg-info"
                                                                            style="width: {{ $fPct }}%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Feliz: {{ round($fPct) }}%">
                                                                            {{ round($fPct) > 10 ? round($fPct) . '%' : '' }}
                                                                        </div>
                                                                    @endif
                                                                    @if ($iPct > 0)
                                                                        <div class="progress-bar bg-warning"
                                                                            style="width: {{ $iPct }}%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Insatisfecho: {{ round($iPct) }}%">
                                                                            {{ round($iPct) > 10 ? round($iPct) . '%' : '' }}
                                                                        </div>
                                                                    @endif
                                                                    @if ($miPct > 0)
                                                                        <div class="progress-bar bg-danger"
                                                                            style="width: {{ $miPct }}%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Muy Insatisfecho: {{ round($miPct) }}%">
                                                                            {{ round($miPct) > 10 ? round($miPct) . '%' : '' }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-bar text-info me-2"></i>
                                                        Comparativa de Zonas
                                                    </h4>
                                                    <canvas id="zonaChart" height="80"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: Reconocimientos -->
                                <div class="tab-pane fade" id="reconocimientos" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-trophy text-warning me-2"></i>
                                                Reconocimientos y Destacados
                                            </h4>
                                        </div>
                                    </div>

                                    @php
                                        // Excluir trimax de reconocimientos individuales (es empresa, no persona)
                                        $userStatsPersonal = $userStats->whereNotIn('role', ['trimax']);
                                        $topRated = $userStatsPersonal->sortByDesc('avg_experience')->take(3);
                                        $mostSurveys = $userStatsPersonal->sortByDesc('total_surveys')->take(3);
                                    @endphp

                                    <div class="row">
                                        <!-- Mejor Calificados -->
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-trophy text-warning me-2"></i>
                                                        Mejor Calificados
                                                    </h4>
                                                    @foreach ($topRated as $index => $user)
                                                        <div class="d-flex align-items-center border-bottom py-3">
                                                            <div class="me-3">
                                                                @if ($index == 0)
                                                                    <div
                                                                        class="icon-wrapper bg-warning-subtle rounded-circle">
                                                                        <span style="font-size: 2rem;">🥇</span>
                                                                    </div>
                                                                @elseif($index == 1)
                                                                    <div class="icon-wrapper bg-secondary rounded-circle">
                                                                        <span style="font-size: 2rem;">🥈</span>
                                                                    </div>
                                                                @else
                                                                    <div
                                                                        class="icon-wrapper bg-danger-subtle rounded-circle">
                                                                        <span style="font-size: 2rem;">🥉</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h5 class="mb-1">{{ $user['name'] }}</h5>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="badge badge-success me-2">
                                                                        <i class="mdi mdi-star"></i>
                                                                        {{ number_format($user['avg_experience'], 2) }}
                                                                    </span>
                                                                    <small class="text-muted">
                                                                        {{ $user['total_surveys'] }} encuestas |
                                                                        {{ $user['muy_feliz'] }} Muy Feliz
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Más Evaluados -->
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                        Más Evaluados
                                                    </h4>
                                                    @foreach ($mostSurveys as $index => $user)
                                                        <div class="card mb-3"
                                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                            <div class="card-body text-white">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h5 class="text-white mb-1">{{ $user['name'] }}
                                                                        </h5>
                                                                        <p class="mb-0 opacity-75">
                                                                            <i class="mdi mdi-star"></i>
                                                                            {{ number_format($user['avg_experience'], 2) }}
                                                                            Promedio
                                                                        </p>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <h2 class="text-white mb-0">
                                                                            {{ $user['total_surveys'] }}</h2>
                                                                        <small class="opacity-75">Encuestas</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: Tendencias -->
                                <div class="tab-pane fade" id="tendencias" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-chart-line text-success me-2"></i>
                                                Tendencias Temporales
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-timeline text-info me-2"></i>
                                                        Tendencia General
                                                    </h4>
                                                    <canvas id="trendChart" height="80"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-calendar-week text-primary me-2"></i>
                                                        Evolución Semanal
                                                    </h4>
                                                    <canvas id="weeklyTrendChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-calendar-month text-success me-2"></i>
                                                        Comparativa Mensual
                                                    </h4>
                                                    <canvas id="monthlyComparisonChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 5: Alertas -->
                                <div class="tab-pane fade" id="alertas" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-alert-circle text-danger me-2"></i>
                                                Alertas y Atención Requerida
                                            </h4>
                                        </div>
                                    </div>

                                    @php
                                        $lowRated = $userStats
                                            ->where('avg_experience', '<', 2.5)
                                            ->sortBy('avg_experience');
                                        $recentBad = $recentSurveys->where('experience_rating', '<=', 2)->take(10);
                                    @endphp

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    @if ($lowRated->count() > 0)
                                                        <div class="alert alert-danger border-0" role="alert">
                                                            <h5 class="mb-3">
                                                                <i class="mdi mdi-alert-circle me-2"></i>
                                                                Usuarios con Calificación Baja
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Usuario</th>
                                                                            <th>Tipo</th>
                                                                            <th>Promedio</th>
                                                                            <th>Calificaciones Negativas</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($lowRated as $user)
                                                                            <tr>
                                                                                <td><strong>{{ $user['name'] }}</strong>
                                                                                </td>
                                                                                <td>
                                                                                    @if ($user['role'] === 'consultor')
                                                                                        <span
                                                                                            class="badge badge-primary">Consultor</span>
                                                                                    @elseif($user['role'] === 'trimax')
                                                                                        <span class="badge"
                                                                                            style="background-color: #1a1a2e; color: #fff;">TRIMAX</span>
                                                                                    @else
                                                                                        <span
                                                                                            class="badge badge-info">Sede</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>
                                                                                    <span class="badge badge-danger">
                                                                                        {{ number_format($user['avg_experience'], 2) }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>{{ $user['muy_insatisfecho'] + $user['insatisfecho'] }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-success border-0" role="alert">
                                                            <h5 class="mb-2">
                                                                <i class="mdi mdi-check-circle me-2"></i>
                                                                Todo en Orden
                                                            </h5>
                                                            <p class="mb-0">No hay usuarios con calificaciones críticas
                                                                en este periodo.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($recentBad->count() > 0)
                                        <div class="row">
                                            <div class="col-lg-12 grid-margin stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="card-title mb-4">
                                                            <i class="mdi mdi-alert text-warning me-2"></i>
                                                            Encuestas Negativas Recientes
                                                        </h4>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Cliente</th>
                                                                        <th>Evaluado</th>
                                                                        <th>Calificación</th>
                                                                        <th>Comentario</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($recentBad as $survey)
                                                                        <tr>
                                                                            <td>
                                                                                <small>{{ $survey->created_at->format('d/m H:i') }}</small>
                                                                            </td>
                                                                            <td>{{ $survey->client_name ?? 'Anónimo' }}
                                                                            </td>
                                                                            <td>
                                                                                {{ $survey->userMarketing->name }}
                                                                                <br>
                                                                                <small class="text-muted">
                                                                                    @if ($survey->userMarketing->role === 'trimax')
                                                                                        TRIMAX General
                                                                                    @elseif($survey->userMarketing->role === 'consultor')
                                                                                        Consultor
                                                                                    @else
                                                                                        Sede
                                                                                    @endif
                                                                                </small>
                                                                            </td>
                                                                            <td>
                                                                                @if ($survey->experience_rating == 2)
                                                                                    <span class="badge badge-warning">
                                                                                        <i
                                                                                            class="mdi mdi-emoticon-neutral"></i>
                                                                                        Insatisfecho
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge badge-danger">
                                                                                        <i
                                                                                            class="mdi mdi-emoticon-sad"></i>
                                                                                        Muy Insatisfecho
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <small>{{ Str::limit($survey->comments ?? 'Sin comentarios', 50) }}</small>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-statistics .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-lg {
            font-size: 2rem;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .nav-tabs-line .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
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

        .progress {
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }

        [data-bs-toggle="tooltip"] {
            cursor: help;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });

        // Gráfico de distribución de calificaciones
        const ratingCtx = document.getElementById('ratingChart');
        if (ratingCtx) {
            new Chart(ratingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Muy Feliz', 'Feliz', 'Insatisfecho', 'Muy Insatisfecho'],
                    datasets: [{
                        data: [{{ $stats['muy_feliz'] }}, {{ $stats['feliz'] }},
                            {{ $stats['insatisfecho'] }}, {{ $stats['muy_insatisfecho'] }}
                        ],
                        backgroundColor: ['#4CAF50', '#0dcaf0', '#ffc107', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de calidad de servicio
        const serviceCtx = document.getElementById('serviceChart');
        if (serviceCtx) {
            @php
                $serviceColors = [];
                foreach ($userStats as $sItem) {
                    $serviceColors[] = $sItem['role'] === 'trimax' ? '#1a1a2e' : '#0dcaf0';
                }
            @endphp
            new Chart(serviceCtx, {
                type: 'bar',
                data: {
                    labels: @json($userStats->pluck('name')),
                    datasets: [{
                        label: 'Promedio de Atención',
                        data: @json($userStats->pluck('avg_service')),
                        backgroundColor: @json($serviceColors),
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        // Gráfico por zona
        const zonaCtx = document.getElementById('zonaChart');
        if (zonaCtx) {
            @php
                $zonaLabels = $zonas->keys();
                $zonaData = $zonas->map(function ($sedes) {
                    return $sedes->avg('avg_experience');
                });
            @endphp

            new Chart(zonaCtx, {
                type: 'bar',
                data: {
                    labels: @json($zonaLabels),
                    datasets: [{
                        label: 'Promedio por Zona',
                        data: @json($zonaData->values()),
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
