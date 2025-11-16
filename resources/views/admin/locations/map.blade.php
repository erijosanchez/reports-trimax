@extends('layouts.app')

@section('title', 'Mapa de Ubicaciones')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="rate-percentage">Mapa de Ubicaciones en Tiempo Real</h3>
                                            <p style="color:#666;">Seguimiento de ubicaciones de trabajadores en campo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Estadísticas Rápidas -->
                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-warning card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 status-summary-ight-white">Usuarios Rastreados
                                                            </p>
                                                            <h2 class="text-white">{{ $usersWithLocations->count() }}</h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-success card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 status-summary-ight-white">En linea</p>
                                                            <h2 class="text-white">
                                                                {{ $usersWithLocations->where('is_online', true)->count() }}
                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 status-summary-ight-white">Ciudades Diferentes
                                                            </p>
                                                            <h2 class="text-white">
                                                                {{ $usersWithLocations->pluck('city')->unique()->count() }}
                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Mapa Interactivo -->
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div
                                                    style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:2rem;">
                                                    <div id="map" style="height:600px;width:100%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LISTA DE UBICACIONES ACTUALES-->
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="d-sm-flex align-items-start justify-content-between">
                                                        <div>
                                                            <h3 class="card-title card-title-dash">Ubicaciones Actuales</h3>
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive mt-1">
                                                        <table class="table">
                                                            <thead>
                                                                <tr style="background:#f0f0f0;">
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        Estado</th>
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        Usuario</th>
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        Ubicación</th>
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        IP</th>
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        Última Actualización</th>
                                                                    <th
                                                                        style="padding:0.75rem;text-align:left;border:1px solid #ddd;">
                                                                        Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($usersWithLocations as $location)
                                                                    <tr>
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            @if ($location['is_online'])
                                                                                <span
                                                                                    style="display:inline-block;width:10px;height:10px;background:#28a745;border-radius:50%;"
                                                                                    title="Online"></span>
                                                                            @else
                                                                                <span
                                                                                    style="display:inline-block;width:10px;height:10px;background:#6c757d;border-radius:50%;"
                                                                                    title="Offline"></span>
                                                                            @endif
                                                                        </td>
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <strong>{{ $location['name'] }}</strong><br>
                                                                            <small
                                                                                style="color:#666;">{{ $location['email'] }}</small>
                                                                        </td>
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            📍 {{ $location['city'] }},
                                                                            {{ $location['region'] }},
                                                                            {{ $location['country'] }}
                                                                            @if ($location['is_vpn'])
                                                                                <span
                                                                                    style="padding:0.25rem 0.5rem;background:#dc3545;color:white;border-radius:3px;font-size:0.75rem;margin-left:0.5rem;">VPN</span>
                                                                            @endif
                                                                        </td>
                                                                        <td
                                                                            style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;font-size:0.85rem;">
                                                                            {{ $location['ip'] }}
                                                                        </td>
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            {{ $location['last_seen'] }}
                                                                        </td>
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <a href="{{ route('admin.locations.user-history', $location['user_id']) }}"
                                                                                style="color:#007bff;text-decoration:none;">
                                                                                Ver Historial
                                                                            </a>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Inicializar mapa centrado en Perú
        const map = L.map('map').setView([-12.0464, -77.0428], 6);

        // Agregar capa de mapa (OpenStreetMap - GRATIS)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Datos de ubicaciones
        const locations = @json($usersWithLocations);

        // Agregar marcadores al mapa
        locations.forEach(location => {
            const color = location.is_online ? 'green' : 'gray';

            const marker = L.circleMarker([location.latitude, location.longitude], {
                radius: 10,
                fillColor: color,
                color: 'white',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map);

            // Popup con información
            const popupContent = `
        <div style="min-width:200px;">
            <strong>${location.name}</strong><br>
            ${location.email}<br>
            <hr style="margin:0.5rem 0;">
            📍 ${location.city}, ${location.region}<br>
            ${location.country}<br>
            <small>IP: ${location.ip}</small><br>
            <small>${location.last_seen}</small>
            ${location.is_vpn ? '<br><span style="color:red;">⚠️ VPN Detectado</span>' : ''}
        </div>
    `;

            marker.bindPopup(popupContent);
        });

        // Auto-refresh cada 30 segundos
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
@endsection
