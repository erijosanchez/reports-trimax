@extends('layouts.app')

@section('title', 'Mapa de Ubicaciones')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom"></div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            
                            <!-- TÍTULO -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage"><i class="mdi mdi-map"></i> Mapa de Ubicaciones GPS</h3>
                                            <p style="color:#666;">
                                                Seguimiento en tiempo real de ubicaciones precisas
                                                <small class="text-muted ms-2">
                                                    <i class="mdi mdi-sync"></i> Actualizado: <span id="lastUpdate">—</span>
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ESTADÍSTICAS -->
                            <div class="row">
                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-warning card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 text-white"><i class="mdi mdi-map-marker"></i> Usuarios con GPS</p>
                                                            <h2 class="text-white" id="statTotal">{{ $usersWithLocations->count() }}</h2>
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
                                                            <p class="mb-1 text-white">🟢 En línea ahora</p>
                                                            <h2 class="text-white" id="statOnline">
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
                                                            <p class="mb-1 text-white"><i class="mdi mdi-city"></i> Ciudades</p>
                                                            <h2 class="text-white" id="statCities">
                                                                {{ $usersWithLocations->pluck('city')->unique()->filter()->count() }}
                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MAPA INTERACTIVO -->
                            <div class="row">
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:2rem;">
                                                    <div id="map" style="height:600px;width:100%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TABLA DE UBICACIONES -->
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="d-sm-flex align-items-start justify-content-between">
                                                        <div>
                                                            <h3 class="card-title"><i class="mdi mdi-map-marker"></i> Ubicaciones GPS Actuales</h3>
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive mt-3">
                                                        <table class="table">
                                                            <thead>
                                                                <tr style="background:#f0f0f0;">
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Estado</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Usuario</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">📍 Ubicación GPS</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">🎯 Precisión</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">⏰ Última Actualización</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($usersWithLocations as $location)
                                                                    <tr>
                                                                        <!-- Estado Online/Offline -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;text-align:center;">
                                                                            @if($location['is_online'])
                                                                                <span style="display:inline-block;width:12px;height:12px;background:#28a745;border-radius:50%;" title="Online"></span>
                                                                            @else
                                                                                <span style="display:inline-block;width:12px;height:12px;background:#6c757d;border-radius:50%;" title="Offline"></span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Usuario -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <strong>{{ $location['name'] }}</strong><br>
                                                                            <small style="color:#666;">{{ $location['email'] }}</small>
                                                                        </td>

                                                                        <!-- Ubicación GPS -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <div style="display:flex;align-items:start;gap:0.5rem;">
                                                                                <span style="font-size:1.5rem;">📱</span>
                                                                                <div>
                                                                                    @if($location['formatted_address'])
                                                                                        <div style="font-size:0.95rem;margin-bottom:0.25rem;">
                                                                                            {{ $location['formatted_address'] }}
                                                                                        </div>
                                                                                    @elseif($location['city'])
                                                                                        <div style="font-size:0.95rem;">
                                                                                            @if($location['street_name'])
                                                                                                {{ $location['street_name'] }}
                                                                                                @if($location['street_number'])
                                                                                                    #{{ $location['street_number'] }}
                                                                                                @endif
                                                                                                <br>
                                                                                            @endif
                                                                                            @if($location['district'])
                                                                                                {{ $location['district'] }},
                                                                                            @endif
                                                                                            {{ $location['city'] }}
                                                                                            @if($location['region'])
                                                                                                , {{ $location['region'] }}
                                                                                            @endif
                                                                                        </div>
                                                                                    @else
                                                                                        <small style="color:#999;">Ubicación no disponible</small>
                                                                                    @endif
                                                                                    
                                                                                    @if($location['latitude'] && $location['longitude'])
                                                                                        <small style="color:#999;font-size:0.8rem;">
                                                                                            {{ number_format($location['latitude'], 4) }}, {{ number_format($location['longitude'], 4) }}
                                                                                        </small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </td>

                                                                        <!-- Precisión -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;text-align:center;">
                                                                            @if($location['accuracy'])
                                                                                <span style="padding:0.3rem 0.8rem;background:
                                                                                    @if($location['accuracy'] < 50) #28a745
                                                                                    @elseif($location['accuracy'] < 100) #5cb85c
                                                                                    @elseif($location['accuracy'] < 500) #ffc107
                                                                                    @else #dc3545
                                                                                    @endif
                                                                                    ;color:white;border-radius:12px;font-size:0.85rem;font-weight:bold;">
                                                                                    {{ number_format($location['accuracy'], 0) }}m
                                                                                </span>
                                                                                <br>
                                                                                <small style="color:#666;">
                                                                                    @if($location['accuracy'] < 50)
                                                                                        ⭐ Excelente
                                                                                    @elseif($location['accuracy'] < 100)
                                                                                        ✓ Buena
                                                                                    @elseif($location['accuracy'] < 500)
                                                                                        △ Regular
                                                                                    @else
                                                                                        ○ Baja
                                                                                    @endif
                                                                                </small>
                                                                            @else
                                                                                <span style="color:#999;">N/A</span>
                                                                            @endif
                                                                        </td>

                                                                        <!-- Última Actualización -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <small>{{ $location['last_seen'] }}</small>
                                                                        </td>

                                                                        <!-- Acciones -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <a href="{{ route('admin.locations.user-history', $location['user_id']) }}" 
                                                                               class="btn btn-sm btn-primary">
                                                                                📊 Ver Historial
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" style="padding:2rem;text-align:center;color:#999;">
                                                                            📍 No hay ubicaciones GPS registradas aún
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Inicializar mapa centrado en Perú
        const map = L.map('map').setView([-12.0464, -77.0428], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const LIVE_URL = "{{ route('admin.locations.live') }}";
        const markersLayer = L.layerGroup().addTo(map);
        let firstRender = true;

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            } [s]));
        }

        function popupHtml(loc) {
            const gmaps = `https://www.google.com/maps?q=${loc.latitude},${loc.longitude}`;
            return `
                <div style="min-width:230px;">
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                        <span style="font-size:1.3rem;">📱</span>
                        <strong>${escapeHtml(loc.name)}</strong>
                        <span style="margin-left:auto;font-size:0.75rem;color:${loc.is_online ? '#28a745' : '#6c757d'};">
                            ${loc.is_online ? '🟢 En línea' : '⚪ Offline'}
                        </span>
                    </div>
                    <small style="color:#666;">${escapeHtml(loc.email)}</small>
                    <hr style="margin:0.5rem 0;">
                    ${escapeHtml(loc.formatted_address || loc.city || 'Ubicación no disponible')}
                    ${loc.accuracy ? `<br><small style="color:#28a745;">🎯 Precisión: ${Math.round(loc.accuracy)}m</small>` : ''}
                    <br><small style="color:#999;">⏰ ${escapeHtml(loc.last_seen || '')}</small>
                    <hr style="margin:0.5rem 0;">
                    <div style="display:flex;gap:0.5rem;">
                        <a href="${loc.history_url}" class="btn btn-sm btn-primary" style="flex:1;">📊 Historial</a>
                        <a href="${gmaps}" target="_blank" rel="noopener" class="btn btn-sm btn-light" style="flex:1;">🗺️ Maps</a>
                    </div>
                </div>`;
        }

        function render(locations) {
            markersLayer.clearLayers();
            const bounds = [];

            locations.forEach(loc => {
                if (loc.latitude == null || loc.longitude == null) return;
                bounds.push([loc.latitude, loc.longitude]);

                const color = loc.is_online ? '#28a745' : '#6c757d';

                // Círculo de precisión (radio real en metros).
                if (loc.accuracy) {
                    L.circle([loc.latitude, loc.longitude], {
                        radius: loc.accuracy,
                        color: color,
                        weight: 1,
                        fillColor: color,
                        fillOpacity: 0.08
                    }).addTo(markersLayer);
                }

                L.circleMarker([loc.latitude, loc.longitude], {
                    radius: 10,
                    fillColor: color,
                    color: 'white',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.85
                }).addTo(markersLayer).bindPopup(popupHtml(loc));
            });

            // Solo encuadrar la primera vez, para no "saltar" mientras el admin explora.
            if (firstRender && bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
                firstRender = false;
            }
        }

        function refresh() {
            fetch(LIVE_URL, { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(data => {
                    render(data.locations || []);
                    const total = (data.locations || []).length;
                    const cities = new Set((data.locations || []).map(l => l.city).filter(Boolean));
                    document.getElementById('statTotal').textContent = total;
                    document.getElementById('statOnline').textContent = data.online ?? 0;
                    document.getElementById('statCities').textContent = cities.size;
                    document.getElementById('lastUpdate').textContent = data.updated_at || '';
                })
                .catch(() => { /* silencioso */ });
        }

        // Render inicial con datos del servidor + refresco vía AJAX cada 60s.
        render(@json($usersWithLocations));
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('es-PE', { hour12: false });
        setInterval(() => {
            if (document.visibilityState === 'visible') refresh();
        }, 60000);
    </script>
@endsection
