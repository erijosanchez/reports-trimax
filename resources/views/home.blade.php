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
        // 🌍 SOLICITUD AUTOMÁTICA DE GPS AL LOGIN
        (function() {
            console.log('Iniciando solicitud GPS...');

            // Verificar soporte GPS
            if (!navigator.geolocation) {
                console.log('❌ Navegador no soporta GPS');
                return;
            }

            // Solicitar GPS después de 2 segundos
            setTimeout(function() {
                console.log('📍 Solicitando permiso GPS...');

                navigator.geolocation.getCurrentPosition(
                    // ✅ ÉXITO - Usuario aceptó GPS
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        console.log('✅ GPS obtenido:', {
                            lat: lat,
                            lon: lon,
                            accuracy: accuracy
                        });

                        // Enviar al servidor vía AJAX
                        fetch('{{ route('admin.locations.store-gps') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
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
                                    console.log('✅ Ubicación GPS guardada:', data.data);

                                    // Mostrar notificación discreta
                                    mostrarNotificacion(
                                        '📱 Ubicación GPS registrada: ' + (data.data.city ||
                                            'Ubicación obtenida'),
                                        'success'
                                    );
                                } else {
                                    console.error('❌ Error del servidor:', data.message);
                                }
                            })
                            .catch(error => {
                                console.error('❌ Error enviando GPS:', error);
                            });
                    },

                    // ❌ ERROR - Usuario rechazó o falló
                    function(error) {
                        let mensaje = '';
                        switch (error.code) {
                            case 1: // PERMISSION_DENIED
                                mensaje = 'Permiso GPS denegado';
                                console.log('❌ Usuario rechazó GPS');
                                break;
                            case 2: // POSITION_UNAVAILABLE
                                mensaje = 'GPS no disponible';
                                console.log('❌ GPS no disponible');
                                break;
                            case 3: // TIMEOUT
                                mensaje = 'GPS timeout';
                                console.log('❌ GPS timeout');
                                break;
                        }

                        // Mostrar notificación informativa (no error)
                        mostrarNotificacion(
                            '🌐 Usando ubicación por IP (GPS: ' + mensaje + ')',
                            'info'
                        );
                    },

                    // ⚙️ OPCIONES GPS
                    {
                        enableHighAccuracy: true, // Usar GPS real (no WiFi)
                        timeout: 15000, // 15 segundos máximo
                        maximumAge: 0 // No usar caché
                    }
                );
            }, 2000); // Esperar 2 segundos después del login

        })();

        // Función para mostrar notificaciones
        function mostrarNotificacion(mensaje, tipo = 'info') {
            // Colores según tipo
            const colores = {
                'success': '#28a745',
                'info': '#17a2b8',
                'warning': '#ffc107',
                'error': '#dc3545'
            };

            // Crear elemento de notificación
            const notif = document.createElement('div');
            notif.textContent = mensaje;
            notif.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${colores[tipo] || colores['info']};
                color: white;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 99999;
                font-size: 14px;
                max-width: 350px;
                animation: slideIn 0.4s ease-out;
            `;

            document.body.appendChild(notif);

            // Auto-remover después de 5 segundos
            setTimeout(() => {
                notif.style.animation = 'slideOut 0.4s ease-out';
                setTimeout(() => notif.remove(), 400);
            }, 5000);
        }

        // CSS para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
@endsection
