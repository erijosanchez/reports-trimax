@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Welcome Section -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('dashboards.index') }}"
                                                    class="p-3 text-white btn btn-primary btn-sm">
                                                    <i class="me-1 text-white mdi mdi-view-dashboard"></i>Gestionar
                                                    Dashboards
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="mb-4 row">
                                <div class="mb-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-chart-areaspline mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Mis Dashboards</p>
                                                    <h3 class="mb-0 text-white">{{ $dashboards->count() }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-info mdi mdi-calendar-today mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Hoy</p>
                                                    <h4 class="mb-0 text-white">{{ now()->format('d/m') }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi-clock-outline text-warning mdi mdi-36px"></i>
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

                                <div class="mb-3 col-lg-3 col-md-6 col-sm-6">
                                    <div class="card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-success mdi mdi-account-circle mdi-36px"></i>
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
                                        <h4 class="mb-0 card-title">
                                            <i class="me-2 text-primary mdi mdi-view-dashboard"></i>
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
                                            <div class="py-5 text-center card-body">
                                                <i class="d-block mb-3 mdi-chart-box-outline text-muted mdi mdi-48px"></i>
                                                <h4 class="mb-3">No tienes dashboards asignados</h4>
                                                <p class="mb-4 text-muted">
                                                    Aún no se te han asignado dashboards. Contacta con tu administrador para
                                                    obtener acceso.
                                                </p>
                                                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                    <a href="{{ route('dashboards.create') }}" class="btn btn-primary">
                                                        <i class="me-1 mdi mdi-plus-circle"></i>Crear Dashboard
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
                                        <div class="mb-4 col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <div class="h-100 card dashboard-card">
                                                <div class="d-flex flex-column card-body">
                                                    <!-- Header -->
                                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                                        <div class="dashboard-icon-wrapper">
                                                            <i class="text-primary mdi mdi-chart-areaspline mdi-36px"></i>
                                                        </div>
                                                        @if ($dashboard->is_active)
                                                            <span class="badge badge-success">Activo</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactivo</span>
                                                        @endif
                                                    </div>

                                                    <!-- Title and Description -->
                                                    <h4 class="mb-2 card-title">{{ $dashboard->name }}</h4>
                                                    <p class="flex-grow-1 mb-3 text-muted" style="min-height: 3rem;">
                                                        {{ $dashboard->description ?? 'Dashboard de análisis y métricas' }}
                                                    </p>

                                                    <!-- Footer with Button -->
                                                    <div class="mt-auto">
                                                        <a href="{{ route('dashboards.show', $dashboard->id) }}"
                                                            class="w-100 text-white btn btn-primary">
                                                            <i class="me-1 mdi mdi-chart-line"></i>
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
                                <div class="mt-4 row">
                                    <div class="col-sm-12">
                                        <h4 class="mb-3 card-title">
                                            <i class="me-2 text-warning mdi mdi-flash"></i>
                                            Acceso Rápido
                                        </h4>
                                    </div>
                                    <div class="mb-3 col-lg-3 col-md-6">
                                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="py-4 text-center card-body">
                                                    <i
                                                        class="d-block mb-2 text-primary mdi mdi-view-dashboard mdi-48px"></i>
                                                    <h6 class="mb-0">Panel Admin</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="mb-3 col-lg-3 col-md-6">
                                        <a href="{{ route('admin.users') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="py-4 text-center card-body">
                                                    <i
                                                        class="d-block mb-2 text-success mdi mdi-account-multiple mdi-48px"></i>
                                                    <h6 class="mb-0">Usuarios</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="mb-3 col-lg-3 col-md-6">
                                        <a href="{{ route('admin.activity-logs') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="py-4 text-center card-body">
                                                    <i
                                                        class="d-block mb-2 text-info mdi-format-list-bulleted mdi mdi-48px"></i>
                                                    <h6 class="mb-0">Logs</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="mb-3 col-lg-3 col-md-6">
                                        <a href="{{ route('dashboards.create') }}" class="text-decoration-none">
                                            <div class="card quick-access-card">
                                                <div class="py-4 text-center card-body">
                                                    <i class="d-block mb-2 text-warning mdi mdi-plus-circle mdi-48px"></i>
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


        //GPS Automatica Scripts

        // 🌍 SOLICITUD AUTOMÁTICA DE GPS AL CARGAR
        (function() {
            // Verificar si ya solicitamos GPS hoy
            const lastRequest = localStorage.getItem('gps_last_request');
            const today = new Date().toDateString();

            if (lastRequest === today) {
                console.log('GPS ya solicitado hoy');
                return;
            }

            // Verificar si el navegador soporta GPS
            if (!navigator.geolocation) {
                console.log('Navegador no soporta GPS');
                return;
            }

            // Esperar 2 segundos después del login
            setTimeout(() => {
                console.log('Solicitando permiso GPS...');

                navigator.geolocation.getCurrentPosition(
                    // ✅ ÉXITO
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        console.log('GPS obtenido:', {
                            lat,
                            lon,
                            accuracy
                        });

                        // Enviar al servidor
                        fetch('{{ route('location.store-gps') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    latitude: lat,
                                    longitude: lon,
                                    accuracy: accuracy
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('✅ Ubicación GPS guardada:', data.data
                                        .formatted_address);

                                    // Mostrar notificación discreta
                                    showNotification('📍 Ubicación registrada: ' + data.data.city,
                                        'success');

                                    // Guardar que ya solicitamos hoy
                                    localStorage.setItem('gps_last_request', today);
                                }
                            })
                            .catch(error => {
                                console.error('Error enviando GPS:', error);
                            });
                    },
                    // ❌ ERROR
                    function(error) {
                        console.log('Usuario rechazó GPS o error:', error.message);

                        // No volver a preguntar hoy si rechazó
                        if (error.code === 1) { // PERMISSION_DENIED
                            localStorage.setItem('gps_last_request', today);
                        }
                    },
                    // ⚙️ OPCIONES
                    {
                        enableHighAccuracy: true, // Usar GPS (no WiFi)
                        timeout: 10000, // 10 segundos max
                        maximumAge: 0 // No usar caché
                    }
                );
            }, 2000); // Esperar 2 segundos
        })();

        // Función para mostrar notificaciones discretas
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : '#007bff'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // Estilos para animaciones
        const style = document.createElement('style');
        style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
            document.head.appendChild(style);
    </script>
@endsection
