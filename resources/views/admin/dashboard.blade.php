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
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-account-clock text-success me-2"></i>
                                                    Estado de Usuarios
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge badge-success" id="onlineCount">
                                                        {{ $stats['users_online'] }} en línea
                                                    </span>
                                                    <small class="text-muted" id="statusUpdatedAt" title="Última actualización"></small>
                                                </div>
                                            </div>

                                            <div id="usersStatusList" style="max-height: 350px; overflow-y: auto;">
                                                @forelse ($allUsersStatus as $u)
                                                    <div class="presence-item d-flex align-items-center border-bottom py-2"
                                                         data-user-id="{{ $u['id'] }}">
                                                        <div class="position-relative me-3 flex-shrink-0">
                                                            <x-avatar-iniciales class="rounded-circle" :nombre="$u['name']"
                                                                :background="$u['is_online'] ? '25D366' : 'adb5bd'" :size="36" />
                                                            <span class="presence-dot {{ $u['is_online'] ? 'dot-online pulse' : 'dot-offline' }}"></span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <p class="mb-0 fw-semibold text-truncate" style="font-size:0.875rem;">
                                                                {{ $u['name'] }}
                                                            </p>
                                                            <small class="presence-label {{ $u['is_online'] ? 'text-success' : 'text-muted' }}">
                                                                {{ $u['last_seen'] }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-4">
                                                        <i class="mdi mdi-account-off-outline mdi-48px text-muted d-block mb-2"></i>
                                                        <p class="text-muted mb-0">Sin usuarios registrados</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div class="mt-2 text-end">
                                                <small class="text-muted" style="font-size:0.7rem;">
                                                    <i class="mdi mdi-refresh me-1"></i>Se actualiza cada 30s
                                                </small>
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
                                                                        <x-avatar-iniciales class="rounded-circle me-2" :nombre="$log->user->name" :size="32" />
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

        /* Online / Presence Indicators */
        .online-indicator,
        .presence-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 11px;
            height: 11px;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .dot-online {
            background-color: #25D366;
            box-shadow: 0 0 0 1px rgba(0,0,0,.08);
        }

        .dot-offline {
            background-color: #adb5bd;
        }

        .pulse-dot {
            font-size: 8px;
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0   rgba(37,211,102,.7); }
            70%  { box-shadow: 0 0 0 6px rgba(37,211,102,0);  }
            100% { box-shadow: 0 0 0 0   rgba(37,211,102,0);  }
        }

        @keyframes pulse-animation {
            0%,100% { opacity: 1; }
            50%     { opacity: .5; }
        }

        .online-indicator.pulse,
        .dot-online.pulse {
            animation: pulse 2s infinite;
        }

        .presence-item {
            transition: background .15s;
        }
        .presence-item:hover {
            background-color: rgba(0,0,0,.03);
            border-radius: 6px;
        }
        .presence-label {
            font-size: 0.75rem;
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // User Activity Chart
            const ctx = document.getElementById('userActivityChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($stats['activity_labels']) !!},
                        datasets: [{
                            label: 'Sesiones',
                            data: {!! json_encode($stats['activity_sessions']) !!},
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Usuarios Únicos',
                            data: {!! json_encode($stats['activity_unique_users']) !!},
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
                setTimeout(() => location.reload(), 500);
            });

            // ── Live presence polling ────────────────────────────────────────
            const onlineStatusUrl = '{{ route("admin.api.online-status") }}';

            function getInitials(name) {
                return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            }

            function refreshPresence() {
                fetch(onlineStatusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        // Update counter badge
                        const countEl = document.getElementById('onlineCount');
                        if (countEl) countEl.textContent = data.online_count + ' en línea';

                        const updEl = document.getElementById('statusUpdatedAt');
                        if (updEl) updEl.textContent = data.updated_at;

                        const list = document.getElementById('usersStatusList');
                        if (!list) return;

                        data.users.forEach(user => {
                            let item = list.querySelector(`[data-user-id="${user.id}"]`);

                            if (!item) {
                                // New user appeared — create element
                                item = document.createElement('div');
                                item.className = 'presence-item d-flex align-items-center border-bottom py-2';
                                item.dataset.userId = user.id;
                                item.innerHTML = `
                                    <div class="position-relative me-3 flex-shrink-0">
                                        <span class="avatar-iniciales rounded-circle" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;color:#fff;font-weight:600;font-size:14px;line-height:1;"></span>
                                        <span class="presence-dot"></span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="mb-0 fw-semibold text-truncate" style="font-size:.875rem;"></p>
                                        <small class="presence-label"></small>
                                    </div>`;
                                list.appendChild(item);
                            }

                            // Update avatar
                            const avatar = item.querySelector('.avatar-iniciales');
                            avatar.textContent = getInitials(user.name);
                            avatar.style.backgroundColor = user.is_online ? '#25D366' : '#adb5bd';
                            avatar.title = user.name;

                            // Update dot
                            const dot = item.querySelector('.presence-dot');
                            dot.className = 'presence-dot ' + (user.is_online ? 'dot-online pulse' : 'dot-offline');

                            // Update name & last seen
                            item.querySelector('p').textContent = user.name;
                            const label = item.querySelector('small');
                            label.textContent = user.last_seen;
                            label.className = 'presence-label ' + (user.is_online ? 'text-success' : 'text-muted');
                        });

                        // Re-sort: online first
                        const items = [...list.querySelectorAll('.presence-item')];
                        items.sort((a, b) => {
                            const aOnline = a.querySelector('.dot-online') ? 1 : 0;
                            const bOnline = b.querySelector('.dot-online') ? 1 : 0;
                            return bOnline - aOnline;
                        });
                        items.forEach(el => list.appendChild(el));
                    })
                    .catch(() => {}); // Silently ignore fetch errors
            }

            // Refresh every 30 seconds
            setInterval(refreshPresence, 30000);
            // Initial call after 5s to update "updated_at" label
            setTimeout(refreshPresence, 5000);
        });
    </script>
@endsection
