@extends('layouts.app')

@section('title', 'Panel Admin')

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
                                                <i class="mdi mdi-view-dashboard text-primary me-2"></i>
                                                Panel de Administración
                                            </h3>
                                            <p class="text-muted mt-1">Vista general del sistema</p>
                                        </div>
                                        <div>
                                            <button id="refreshStats" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-refresh"></i> Actualizar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Usuarios Totales</p>
                                                    <h3 class="rate-percentage">{{ $stats['total_users'] }}</h3>
                                                    <p class="text-success d-flex align-items-center">
                                                        <i class="mdi mdi-menu-up"></i>
                                                        <span class="ms-1">Registrados</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-primary-subtle rounded">
                                                    <i class="mdi mdi-account-multiple text-primary icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Usuarios Online</p>
                                                    <h3 class="rate-percentage">{{ $stats['users_online'] }}</h3>
                                                    <p class="text-success d-flex align-items-center">
                                                        <i class="mdi mdi-circle text-success pulse-dot me-1"></i>
                                                        <span>Activos ahora</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-success-subtle rounded">
                                                    <i class="mdi mdi-account-check text-success icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Sesiones Hoy</p>
                                                    <h3 class="rate-percentage">{{ $stats['total_sessions_today'] }}</h3>
                                                    <p class="text-warning d-flex align-items-center">
                                                        <i class="mdi mdi-calendar-today"></i>
                                                        <span class="ms-1">{{ date('d/m/Y') }}</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-warning-subtle rounded">
                                                    <i class="mdi mdi-login-variant text-warning icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">IPs Bloqueadas</p>
                                                    <h3 class="rate-percentage">{{ $stats['blocked_ips'] }}</h3>
                                                    <p class="text-danger d-flex align-items-center">
                                                        <i class="mdi mdi-shield-alert"></i>
                                                        <span class="ms-1">Seguridad</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-danger-subtle rounded">
                                                    <i class="mdi mdi-block-helper text-danger icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts Row -->
                            <div class="row">
                                <!-- User Activity Chart -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                    Actividad de Usuarios (Últimos 7 días)
                                                </h4>
                                            </div>
                                            <canvas id="userActivityChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Users Online Card -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-account-clock text-success me-2"></i>
                                                Usuarios Online Ahora
                                            </h4>
                                            <div style="max-height: 350px; overflow-y: auto;">
                                                @if ($usersOnline->isEmpty())
                                                    <div class="text-center py-5">
                                                        <i
                                                            class="mdi mdi-account-off-outline mdi-48px text-muted mb-3 d-block"></i>
                                                        <p class="text-muted">No hay usuarios online</p>
                                                    </div>
                                                @else
                                                    @foreach ($usersOnline as $user)
                                                        @foreach ($user->activeSessions as $session)
                                                            <div class="d-flex align-items-center border-bottom py-3">
                                                                <div class="position-relative me-3">
                                                                    <img class="img-xs rounded-circle"
                                                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                                                        alt="profile">
                                                                    <span class="online-indicator pulse"></span>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <p class="mb-0 fw-bold">{{ $user->name }}</p>
                                                                    <small class="text-muted">
                                                                        <i
                                                                            class="mdi mdi-web me-1"></i>{{ $session->ip_address }}
                                                                    </small>
                                                                </div>
                                                                <small class="text-muted">
                                                                    {{ $session->last_activity->diffForHumans(null, true) }}
                                                                </small>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Logs and Quick Actions -->
                            <div class="row">
                                <!-- Recent Activity -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-history text-info me-2"></i>
                                                Actividad Reciente
                                            </h4>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Usuario</th>
                                                            <th>Acción</th>
                                                            <th>Descripción</th>
                                                            <th>Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($recentActivity as $log)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=6366f1&color=fff"
                                                                            alt="profile">
                                                                        <span>{{ $log->user->name }}</span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span
                                                                        class="badge 
                                                                    @if (str_contains(strtolower($log->action), 'login')) badge-success
                                                                    @elseif(str_contains(strtolower($log->action), 'logout')) badge-secondary
                                                                    @elseif(str_contains(strtolower($log->action), 'create')) badge-primary
                                                                    @elseif(str_contains(strtolower($log->action), 'delete')) badge-danger
                                                                    @elseif(str_contains(strtolower($log->action), 'update')) badge-info
                                                                    @else badge-dark @endif">
                                                                        {{ $log->action }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <small
                                                                        class="text-muted">{{ Str::limit($log->description, 50) }}</small>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        <i class="mdi mdi-clock-outline me-1"></i>
                                                                        {{ $log->created_at->diffForHumans() }}
                                                                    </small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3 text-center">
                                                <a href="{{ route('admin.activity-logs') }}"
                                                    class="btn btn-sm btn-outline-info">
                                                    <i class="mdi mdi-format-list-bulleted me-1"></i>Ver Todos los Logs
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-flash text-warning me-2"></i>
                                                Acciones Rápidas
                                            </h4>

                                            <a href="{{ route('admin.users') }}"
                                                class="btn btn-outline-primary w-100 mb-3 d-flex align-items-center justify-content-between">
                                                <span>
                                                    <i class="mdi mdi-account-cog me-2"></i>Gestionar Usuarios
                                                </span>
                                                <i class="mdi mdi-arrow-right"></i>
                                            </a>

                                            <a href="{{ route('admin.activity-logs') }}"
                                                class="btn btn-outline-info w-100 mb-3 d-flex align-items-center justify-content-between">
                                                <span>
                                                    <i class="mdi mdi-format-list-bulleted me-2"></i>Logs de Actividad
                                                </span>
                                                <i class="mdi mdi-arrow-right"></i>
                                            </a>

                                            <a href="{{ route('admin.security') }}"
                                                class="btn btn-outline-danger w-100 mb-3 d-flex align-items-center justify-content-between">
                                                <span>
                                                    <i class="mdi mdi-shield-alert me-2"></i>Seguridad
                                                </span>
                                                @if ($stats['blocked_ips'] > 0)
                                                    <span class="badge badge-danger">{{ $stats['blocked_ips'] }}</span>
                                                @endif
                                            </a>

                                            <div class="border-top pt-3 mt-3">
                                                <h6 class="mb-3">Estado del Sistema</h6>
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted">Capacidad de Usuarios</small>
                                                        <small
                                                            class="text-muted">{{ round(($stats['users_online'] / max($stats['total_users'], 1)) * 100) }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: {{ round(($stats['users_online'] / max($stats['total_users'], 1)) * 100) }}%">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted">Actividad del Día</small>
                                                        <small class="text-muted">{{ $stats['total_sessions_today'] }}
                                                            sesiones</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-warning" role="progressbar"
                                                            style="width: {{ min(100, $stats['total_sessions_today'] * 10) }}%">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-light border d-flex align-items-center mb-0"
                                                    role="alert">
                                                    <i class="mdi mdi-information-outline text-primary me-2"></i>
                                                    <small>Última actualización: {{ now()->format('H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Stats Cards */
        .card-statistics .statistics-title {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-lg {
            font-size: 2rem;
        }

        .bg-primary-subtle {
            background-color: rgba(99, 102, 241, 0.1) !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Online Indicator */
        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background-color: #25D366;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .pulse-dot {
            font-size: 8px;
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(37, 211, 102, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }

        @keyframes pulse-animation {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .online-indicator.pulse {
            animation: pulse 2s infinite;
        }

        /* Scrollbar */
        .card-body>div::-webkit-scrollbar {
            width: 6px;
        }

        .card-body>div::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .card-body>div::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .card-body>div::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // User Activity Chart
            const ctx = document.getElementById('userActivityChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Hace 6 días', 'Hace 5 días', 'Hace 4 días', 'Hace 3 días', 'Hace 2 días',
                            'Ayer', 'Hoy'
                        ],
                        datasets: [{
                            label: 'Sesiones',
                            data: [45, 52, 38, 65, 59, 80, {{ $stats['total_sessions_today'] }}],
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Usuarios Únicos',
                            data: [28, 35, 25, 42, 38, 55, {{ $stats['users_online'] }}],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Refresh button
            document.getElementById('refreshStats')?.addEventListener('click', function() {
                this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Actualizando...';
                this.disabled = true;
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
        });
    </script>
@endsection
