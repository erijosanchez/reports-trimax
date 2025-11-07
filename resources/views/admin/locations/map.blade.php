@extends('layouts.app')

@section('title', 'Mapa de Ubicaciones')

@section('content')
    <div style="margin-bottom:2rem;">
        <h1>Mapa de Ubicaciones en Tiempo Real</h1>
        <p style="color:#666;">Seguimiento de ubicaciones de trabajadores en visitas a provincia</p>
    </div>

    <!-- Estadísticas Rápidas -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem;">
        <div style="padding:1.5rem;background:#007bff;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $usersWithLocations->count() }}</h3>
            <p style="margin:0.5rem 0 0 0;">Usuarios Rastreados</p>
        </div>
        <div style="padding:1.5rem;background:#28a745;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $usersWithLocations->where('is_online', true)->count() }}</h3>
            <p style="margin:0.5rem 0 0 0;">Online Ahora</p>
        </div>
        <div style="padding:1.5rem;background:#ffc107;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $usersWithLocations->pluck('city')->unique()->count() }}</h3>
            <p style="margin:0.5rem 0 0 0;">Ciudades Diferentes</p>
        </div>
    </div>

    <!-- Mapa Interactivo -->
    <div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:2rem;">
        <div id="map" style="height:600px;width:100%;"></div>
    </div>

    <!-- Lista de Usuarios con Ubicación -->
    <div style="background:white;border:1px solid #ddd;border-radius:4px;padding:1.5rem;">
        <h2>Ubicaciones Actuales</h2>

        <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Estado</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Ubicación</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Última Actualización</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Acciones</th>
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
                            <small style="color:#666;">{{ $location['email'] }}</small>
                        </td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">
                            📍 {{ $location['city'] }}, {{ $location['region'] }}, {{ $location['country'] }}
                            @if ($location['is_vpn'])
                                <span
                                    style="padding:0.25rem 0.5rem;background:#dc3545;color:white;border-radius:3px;font-size:0.75rem;margin-left:0.5rem;">VPN</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;font-size:0.85rem;">
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
