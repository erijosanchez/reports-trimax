@extends('layouts.app')

@section('title', 'Dashboard Marketing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/sistemaencuestas.css') }}">
@endpush

@section('content')

    <div class="main-content">
        <!-- Filtros -->
        <div class="filters-card">
            <h5 class="mb-4"><i class="bi bi-funnel"></i> Filtros</h5>
            <form method="GET" action="{{ route('marketing.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Consultor/Sede</label>
                        <select name="user_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($users as $userMkt)
                                <option value="{{ $userMkt->id }}" {{ $userId == $userMkt->id ? 'selected' : '' }}>
                                    {{ $userMkt->name }} -
                                    {{ $userMkt->role === 'consultor' ? 'Consultor' : 'Sede ' . $userMkt->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex align-items-end col-md-2">
                        <button type="submit" class="w-100 btn btn-primary">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabs de navegación -->
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                    type="button">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="zona-tab" data-bs-toggle="tab" data-bs-target="#zona" type="button">
                    <i class="bi bi-geo-alt"></i> Detalles por Zona
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reconocimientos-tab" data-bs-toggle="tab" data-bs-target="#reconocimientos"
                    type="button">
                    <i class="bi bi-trophy"></i> Reconocimientos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tendencias-tab" data-bs-toggle="tab" data-bs-target="#tendencias"
                    type="button">
                    <i class="bi bi-graph-up"></i> Tendencias
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="alertas-tab" data-bs-toggle="tab" data-bs-target="#alertas" type="button">
                    <i class="bi bi-exclamation-triangle"></i> Alertas
                </button>
            </li>
        </ul>

        <!-- Contenido de los tabs -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- TAB 1: Dashboard General -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                <h2 class="section-title"><i class="bi bi-graph-up"></i> Estadísticas Generales</h2>
                <div class="row">
                    <div class="col-md-2">
                        <div class="stats-card total">
                            <div class="icon">📊</div>
                            <div class="number">{{ $stats['total'] }}</div>
                            <div class="label">Total Encuestas</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stats-card muy-feliz">
                            <div class="icon">😊</div>
                            <div class="number">{{ $stats['muy_feliz'] }}</div>
                            <div class="label">Muy Feliz</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stats-card feliz">
                            <div class="icon">🙂</div>
                            <div class="number">{{ $stats['feliz'] }}</div>
                            <div class="label">Feliz</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stats-card insatisfecho">
                            <div class="icon">😐</div>
                            <div class="number">{{ $stats['insatisfecho'] }}</div>
                            <div class="label">Insatisfecho</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stats-card muy-insatisfecho">
                            <div class="icon">😞</div>
                            <div class="number">{{ $stats['muy_insatisfecho'] }}</div>
                            <div class="label">Muy Insatisfecho</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stats-card total">
                            <div class="icon">⭐</div>
                            <div class="number">{{ number_format($stats['average_experience'], 2) }}</div>
                            <div class="label">Promedio</div>
                        </div>
                    </div>
                </div>

                <h2 class="section-title"><i class="bi bi-bar-chart"></i> Gráfico de Distribución</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="serviceChart"></canvas>
                        </div>
                    </div>
                </div>

                <h2 class="section-title"><i class="bi bi-people"></i> Estadísticas por Consultor/Sede</h2>
                <div class="table-card">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>😊 Muy Feliz</th>
                                <th>🙂 Feliz</th>
                                <th>😐 Insatis.</th>
                                <th>😞 Muy Insatis.</th>
                                <th>Promedio</th>
                                <th>Distribución</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userStats as $stat)
                                <tr>
                                    <td><strong>{{ $stat['name'] }}</strong></td>
                                    <td>
                                        @if ($stat['role'] === 'consultor')
                                            <span class="bg-primary badge">Consultor</span>
                                        @else
                                            <span class="bg-info badge">Sede {{ $stat['location'] }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $stat['total_surveys'] }}</strong></td>
                                    <td><span class="badge badge-muy-feliz">{{ $stat['muy_feliz'] }}</span></td>
                                    <td><span class="badge badge-feliz">{{ $stat['feliz'] }}</span></td>
                                    <td><span class="badge badge-insatisfecho">{{ $stat['insatisfecho'] }}</span></td>
                                    <td><span class="badge badge-muy-insatisfecho">{{ $stat['muy_insatisfecho'] }}</span>
                                    </td>
                                    <td><strong>{{ number_format($stat['avg_experience'], 2) }}</strong></td>
                                    <td style="width: 200px;">
                                        @if ($stat['total_surveys'] > 0)
                                            <div class="progress-bar-container">
                                                @php
                                                    $total = $stat['total_surveys'];
                                                    $muyFelizPct = ($stat['muy_feliz'] / $total) * 100;
                                                    $felizPct = ($stat['feliz'] / $total) * 100;
                                                    $insatisfechoPct = ($stat['insatisfecho'] / $total) * 100;
                                                    $muyInsatisfechoPct = ($stat['muy_insatisfecho'] / $total) * 100;
                                                @endphp
                                                @if ($muyFelizPct > 0)
                                                    <div class="progress-segment"
                                                        style="width: {{ $muyFelizPct }}%; background: #4CAF50;">
                                                        {{ round($muyFelizPct) }}%
                                                    </div>
                                                @endif
                                                @if ($felizPct > 0)
                                                    <div class="progress-segment"
                                                        style="width: {{ $felizPct }}%; background: #2196F3;">
                                                        {{ round($felizPct) }}%
                                                    </div>
                                                @endif
                                                @if ($insatisfechoPct > 0)
                                                    <div class="progress-segment"
                                                        style="width: {{ $insatisfechoPct }}%; background: #FF9800;">
                                                        {{ round($insatisfechoPct) }}%
                                                    </div>
                                                @endif
                                                @if ($muyInsatisfechoPct > 0)
                                                    <div class="progress-segment"
                                                        style="width: {{ $muyInsatisfechoPct }}%; background: #F44336;">
                                                        {{ round($muyInsatisfechoPct) }}%
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

                <h2 class="section-title"><i class="bi bi-clock-history"></i> Encuestas Recientes</h2>
                <div class="table-card">
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
                                    <td>{{ $survey->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($survey->client_name)
                                            <strong>{{ $survey->client_name }}</strong>
                                        @else
                                            <span class="text-muted">Anónimo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $survey->userMarketing->name }}</strong><br>
                                        <small class="text-muted">
                                            {{ $survey->userMarketing->role === 'consultor' ? 'Consultor' : 'Sede ' . $survey->userMarketing->location }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="emoji-rating">{{ $survey->rating_emoji }}</span>
                                        @if ($survey->experience_rating == 4)
                                            <span class="badge badge-muy-feliz">Muy Feliz</span>
                                        @elseif($survey->experience_rating == 3)
                                            <span class="badge badge-feliz">Feliz</span>
                                        @elseif($survey->experience_rating == 2)
                                            <span class="badge badge-insatisfecho">Insatisfecho</span>
                                        @else
                                            <span class="badge badge-muy-insatisfecho">Muy Insatisfecho</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($survey->service_quality_rating == 4)
                                            <span class="badge badge-muy-feliz">😊 Muy Feliz</span>
                                        @elseif($survey->service_quality_rating == 3)
                                            <span class="badge badge-feliz">🙂 Feliz</span>
                                        @elseif($survey->service_quality_rating == 2)
                                            <span class="badge badge-insatisfecho">😐 Insatisfecho</span>
                                        @else
                                            <span class="badge badge-muy-insatisfecho">😞 Muy Insatisfecho</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($survey->comments)
                                            <small>{{ Str::limit($survey->comments, 80) }}</small>
                                        @else
                                            <small class="text-muted">Sin comentarios</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Detalles por Zona -->
            <div class="tab-pane fade" id="zona" role="tabpanel">
                <h2 class="section-title"><i class="bi bi-geo-alt"></i> Análisis por Ubicación</h2>

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
                        <div class="col-md-4">
                            <div class="stats-card">
                                <h4 class="text-primary">📍 {{ $location }}</h4>
                                <div class="mt-3 row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="number" style="font-size: 32px;">{{ $totalZona }}</div>
                                            <div class="label">Encuestas</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="number" style="font-size: 32px;">
                                                {{ number_format($avgZona, 2) }}</div>
                                            <div class="label">Promedio</div>
                                        </div>
                                    </div>
                                </div>
                                @if ($totalZona > 0)
                                    <div class="mt-3 progress-bar-container">
                                        @php
                                            $mfPct = ($muyFelizZona / $totalZona) * 100;
                                            $fPct = ($felizZona / $totalZona) * 100;
                                            $iPct = ($insatisfechoZona / $totalZona) * 100;
                                            $miPct = ($muyInsatisfechoZona / $totalZona) * 100;
                                        @endphp
                                        @if ($mfPct > 0)
                                            <div class="progress-segment"
                                                style="width: {{ $mfPct }}%; background: #4CAF50;">
                                                {{ round($mfPct) }}%</div>
                                        @endif
                                        @if ($fPct > 0)
                                            <div class="progress-segment"
                                                style="width: {{ $fPct }}%; background: #2196F3;">
                                                {{ round($fPct) }}%</div>
                                        @endif
                                        @if ($iPct > 0)
                                            <div class="progress-segment"
                                                style="width: {{ $iPct }}%; background: #FF9800;">
                                                {{ round($iPct) }}%</div>
                                        @endif
                                        @if ($miPct > 0)
                                            <div class="progress-segment"
                                                style="width: {{ $miPct }}%; background: #F44336;">
                                                {{ round($miPct) }}%</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 chart-container">
                    <canvas id="zonaChart"></canvas>
                </div>
            </div>

            <!-- TAB 3: Reconocimientos -->
            <div class="tab-pane fade" id="reconocimientos" role="tabpanel">
                <h2 class="section-title"><i class="bi bi-trophy"></i> Reconocimientos y Destacados</h2>

                @php
                    $topRated = $userStats->sortByDesc('avg_experience')->take(3);
                    $mostSurveys = $userStats->sortByDesc('total_surveys')->take(3);
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">🏆 Mejor Calificados</h4>
                        @foreach ($topRated as $index => $user)
                            <div class="recognition-card">
                                <div class="trophy">
                                    @if ($index == 0)
                                        🥇
                                    @elseif($index == 1)
                                        🥈
                                    @else
                                        🥉
                                    @endif
                                </div>
                                <h4>{{ $user['name'] }}</h4>
                                <p class="mb-2">
                                    <strong>Promedio: {{ number_format($user['avg_experience'], 2) }}</strong> ⭐
                                </p>
                                <p class="mb-0">
                                    {{ $user['total_surveys'] }} encuestas |
                                    {{ $user['muy_feliz'] }} Muy Feliz 😊
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-md-6">
                        <h4 class="mb-3">📊 Más Evaluados</h4>
                        @foreach ($mostSurveys as $index => $user)
                            <div class="stats-card"
                                style="background: linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%); color: white;">
                                <h5>{{ $user['name'] }}</h5>
                                <div class="number" style="color: white;">{{ $user['total_surveys'] }}</div>
                                <p class="mb-0">
                                    Promedio: {{ number_format($user['avg_experience'], 2) }} ⭐
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- TAB 4: Tendencias -->
            <div class="tab-pane fade" id="tendencias" role="tabpanel">
                <h2 class="section-title"><i class="bi bi-graph-up"></i> Tendencias Temporales</h2>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>

                <div class="mt-4 row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3">📈 Evolución Semanal</h5>
                            <canvas id="weeklyTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3">📊 Comparativa Mensual</h5>
                            <canvas id="monthlyComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Alertas -->
            <div class="tab-pane fade" id="alertas" role="tabpanel">
                <h2 class="section-title"><i class="bi bi-exclamation-triangle"></i> Alertas y Atención Requerida</h2>

                @php
                    $lowRated = $userStats->where('avg_experience', '<', 2.5)->sortBy('avg_experience');
                    $recentBad = $recentSurveys->where('experience_rating', '<=', 2)->take(10);
                @endphp

                @if ($lowRated->count() > 0)
                    <div class="alert-item alert-danger">
                        <h5><i class="bi bi-exclamation-circle"></i> Usuarios con Calificación Baja</h5>
                        <ul class="mt-2 mb-0">
                            @foreach ($lowRated as $user)
                                <li>
                                    <strong>{{ $user['name'] }}</strong> - Promedio:
                                    {{ number_format($user['avg_experience'], 2) }}
                                    ({{ $user['muy_insatisfecho'] + $user['insatisfecho'] }} calificaciones negativas)
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="alert-item" style="border-color: #4CAF50; background: #E8F5E9;">
                        <h5 class="text-success"><i class="bi bi-check-circle"></i> Todo en orden</h5>
                        <p class="mb-0">No hay usuarios con calificaciones críticas en este periodo.</p>
                    </div>
                @endif

                @if ($recentBad->count() > 0)
                    <div class="alert-item alert-warning">
                        <h5><i class="bi bi-exclamation-triangle"></i> Encuestas Negativas Recientes</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
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
                                            <td>{{ $survey->created_at->format('d/m H:i') }}</td>
                                            <td>{{ $survey->client_name ?? 'Anónimo' }}</td>
                                            <td>{{ $survey->userMarketing->name }}</td>
                                            <td>
                                                @if ($survey->experience_rating == 2)
                                                    <span class="badge badge-insatisfecho">😐 Insatisfecho</span>
                                                @else
                                                    <span class="badge badge-muy-insatisfecho">😞 Muy
                                                        Insatisfecho</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($survey->comments ?? 'Sin comentarios', 60) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de distribución de calificaciones
        const ratingCtx = document.getElementById('ratingChart');
        if (ratingCtx) {
            new Chart(ratingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Muy Feliz 😊', 'Feliz 🙂', 'Insatisfecho 😐', 'Muy Insatisfecho 😞'],
                    datasets: [{
                        data: [{{ $stats['muy_feliz'] }}, {{ $stats['feliz'] }},
                            {{ $stats['insatisfecho'] }}, {{ $stats['muy_insatisfecho'] }}
                        ],
                        backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribución de Experiencia General'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gráfico de calidad de servicio
        const serviceCtx = document.getElementById('serviceChart');
        if (serviceCtx) {
            new Chart(serviceCtx, {
                type: 'bar',
                data: {
                    labels: @json($userStats->pluck('name')),
                    datasets: [{
                        label: 'Promedio de Atención',
                        data: @json($userStats->pluck('avg_service')),
                        backgroundColor: '#2196F3'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Calidad de Atención por Usuario'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4
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
                        backgroundColor: '#1565C0'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Comparativa de Zonas'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4
                        }
                    }
                }
            });
        }
    </script>
@endpush
