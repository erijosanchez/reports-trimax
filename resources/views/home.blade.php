@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Welcome Section -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('dashboards.index') }}" class="btn btn-primary p-3 btn-sm text-white">
                                                    <i class="mdi mdi-view-dashboard me-1 text-white"></i>Gestionar Dashboards
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                    <div class="card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-chart-areaspline text-primary mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Mis Dashboards</p>
                                                    <h3 class="mb-0 text-white">{{ $dashboards->count() }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                    <div class="card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-calendar-today text-info mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Hoy</p>
                                                    <h4 class="mb-0 text-white">{{ now()->format('d/m') }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                    <div class="card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-clock-outline text-warning mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Hora</p>
                                                    <h4 class="mb-0 text-white" id="current-time">{{ now()->format('H:i') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                    <div class="card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-account-circle text-success mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Mi Rol</p>
                                                    <h6 class="mb-0 text-white">
                                                        @if (auth()->user()->isSuperAdmin())
                                                            Super Admin
                                                        @elseif(auth()->user()->isAdmin())
                                                            Admin
                                                        @else
                                                            Usuario
                                                        @endif
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dashboards Section -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="card-title mb-0">
                                            <i class="mdi mdi-view-dashboard text-primary me-2"></i>
                                            Tus Dashboards de Power BI
                                        </h4>
                                        @if (!$dashboards->isEmpty())
                                            <span class="badge badge-primary">{{ $dashboards->count() }} disponibles</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($dashboards->isEmpty())
                                <!-- Empty State -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body text-center py-5">
                                                <i class="mdi mdi-chart-box-outline mdi-48px text-muted mb-3 d-block"></i>
                                                <h4 class="mb-3">No tienes dashboards asignados</h4>
                                                <p class="text-muted mb-4">
                                                    Aún no se te han asignado dashboards. Contacta con tu administrador para
                                                    obtener acceso.
                                                </p>
                                                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                    <a href="{{ route('dashboards.create') }}" class="btn btn-primary">
                                                        <i class="mdi mdi-plus-circle me-1"></i>Crear Dashboard
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Dashboard Cards Grid -->
                                <div class="row">
                                    @foreach ($dashboards as $dashboard)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <div class="card dashboard-card h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="dashboard-icon-wrapper">
                                                            <i class="mdi mdi-chart-areaspline mdi-36px text-primary"></i>
                                                        </div>
                                                        @if ($dashboard->is_active)
                                                            <span class="badge badge-success">Activo</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactivo</span>
                                                        @endif
                                                    </div>

                                                    <!-- Title and Description -->
                                                    <h4 class="card-title mb-2">{{ $dashboard->name }}</h4>
                                                    <p class="text-muted mb-3 flex-grow-1" style="min-height: 3rem;">
                                                        {{ $dashboard->description ?? 'Dashboard de análisis y métricas' }}
                                                    </p>

                                                    <!-- Footer with Button -->
                                                    <div class="mt-auto">
                                                        <a href="{{ route('dashboards.show', $dashboard->id) }}"
                                                            class="btn btn-primary w-100 text-white">
                                                            <i class="mdi mdi-chart-line me-1"></i>
                                                            Ver Dashboard
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Quick Access Section (Optional) -->
                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <div class="row mt-4">
                                    <div class="col-sm-12">
                                        <h4 class="card-title mb-3">
                                            <i class="mdi mdi-flash text-warning me-2"></i>
                                            Acceso Rápido
                                        </h4>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="card-body text-center py-4">
                                                    <i
                                                        class="mdi mdi-view-dashboard mdi-48px text-primary mb-2 d-block"></i>
                                                    <h6 class="mb-0">Panel Admin</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <a href="{{ route('admin.users') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="card-body text-center py-4">
                                                    <i
                                                        class="mdi mdi-account-multiple mdi-48px text-success mb-2 d-block"></i>
                                                    <h6 class="mb-0">Usuarios</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <a href="{{ route('admin.activity-logs') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="card-body text-center py-4">
                                                    <i
                                                        class="mdi mdi-format-list-bulleted mdi-48px text-info mb-2 d-block"></i>
                                                    <h6 class="mb-0">Logs</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <a href="{{ route('dashboards.create') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="card-body text-center py-4">
                                                    <i class="mdi mdi-plus-circle mdi-48px text-warning mb-2 d-block"></i>
                                                    <h6 class="mb-0">Nuevo Dashboard</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Dashboard Cards */
        .dashboard-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e3e6f0;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .dashboard-icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Quick Access Cards */
        .quick-access-card {
            transition: all 0.3s;
            border: 1px solid #e3e6f0;
            cursor: pointer;
        }

        .quick-access-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }

        /* Icon wrappers for stats */
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Update time every second */
        #current-time {
            font-family: 'Courier New', monospace;
        }
    </style>

    <script>
        // Update current time every second
        setInterval(function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = hours + ':' + minutes;
        }, 1000);
    </script>
@endsection
