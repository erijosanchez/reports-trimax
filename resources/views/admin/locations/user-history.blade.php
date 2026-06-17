@extends('layouts.app')

@section('title', 'Historial GPS de ' . $user->name)

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                
                <!-- Navegación -->
                <div style="margin-bottom:2rem;">
                    <a href="{{ route('admin.locations.map') }}" class="btn btn-secondary">
                        ← Volver al Mapa
                    </a>
                </div>

                <!-- Header -->
                <div class="card">
                    <div class="card-body">
                        <h2><i class="mdi mdi-history"></i> Historial GPS: {{ $user->name }}</h2>
                        <p style="color:#666;">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h3>{{ $stats['total_locations'] }}</h3>
                                <p class="mb-0"><i class="mdi mdi-map-marker"></i> Ubicaciones GPS</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h3>{{ $stats['cities_visited'] }}</h3>
                                <p class="mb-0"><i class="mdi mdi-city"></i> Ciudades Visitadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h3>{{ $stats['countries_visited'] }}</h3>
                                <p class="mb-0"><i class="mdi mdi-earth"></i> Países Visitados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mini-mapa del recorrido -->
                @if($mapPoints->count() > 0)
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="mb-3"><i class="mdi mdi-map-marker-path"></i> Recorrido (últimos {{ $mapPoints->count() }} puntos)</h4>
                        <div id="historyMap" style="height:420px;width:100%;border-radius:8px;"></div>
                    </div>
                </div>
                @endif

                <!-- Ciudades Únicas -->
                @if($uniqueCities->count() > 0)
                <div class="card mt-4">
                    <div class="card-body">
                        <h4><i class="mdi mdi-city"></i> Ciudades Visitadas:</h4>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:1rem;">
                            @foreach($uniqueCities as $city)
                                <span class="badge bg-primary" style="padding:0.5rem 1rem;font-size:0.9rem;">
                                    <i class="mdi mdi-map-marker"></i> {{ $city }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Tabla de Historial -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h4><i class="mdi mdi-history"></i> Historial Completo</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>📅 Fecha/Hora</th>
                                        <th>📍 Ubicación GPS</th>
                                        <th>🎯 Precisión</th>
                                        <th>📐 Coordenadas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($locations as $location)
                                        <tr>
                                            <td>
                                                {{ $location->created_at->format('d/m/Y H:i:s') }}<br>
                                                <small class="text-muted">{{ $location->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($location->formatted_address)
                                                    {{ $location->formatted_address }}
                                                @elseif($location->city)
                                                    {{ $location->city }}
                                                    @if($location->region), {{ $location->region }}@endif
                                                    @if($location->country), {{ $location->country }}@endif
                                                @else
                                                    <span class="text-muted">Ubicación no disponible</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($location->accuracy)
                                                    <span class="badge bg-
                                                        @if($location->accuracy < 50) success
                                                        @elseif($location->accuracy < 100) primary
                                                        @elseif($location->accuracy < 500) warning
                                                        @else danger
                                                        @endif
                                                    ">
                                                        {{ number_format($location->accuracy, 0) }}m
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($location->latitude && $location->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                                                        target="_blank" rel="noopener" class="text-decoration-none">
                                                        <small>
                                                            {{ number_format($location->latitude, 4) }},
                                                            {{ number_format($location->longitude, 4) }}
                                                            <i class="mdi mdi-open-in-new"></i>
                                                        </small>
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No hay ubicaciones GPS registradas
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-3">
                            {{ $locations->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if($mapPoints->count() > 0)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (function () {
                const points = @json($mapPoints);
                if (!points.length) return;

                const histMap = L.map('historyMap');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(histMap);

                const latlngs = points.map(p => [p.lat, p.lng]);

                // Línea del recorrido (orden cronológico).
                L.polyline(latlngs, { color: '#6366f1', weight: 3, opacity: 0.7 }).addTo(histMap);

                points.forEach((p, i) => {
                    const isFirst = i === 0;
                    const isLast = i === points.length - 1;
                    const color = isLast ? '#28a745' : (isFirst ? '#dc3545' : '#6366f1');
                    const label = isLast ? '📍 Más reciente' : (isFirst ? '🏁 Más antiguo' : '');

                    L.circleMarker([p.lat, p.lng], {
                        radius: isLast ? 9 : 6,
                        fillColor: color,
                        color: 'white',
                        weight: 2,
                        fillOpacity: 0.9
                    }).addTo(histMap).bindPopup(
                        `<strong>${label}</strong><br>${p.address ?? ''}<br><small>${p.time ?? ''}</small>`
                    );
                });

                histMap.fitBounds(latlngs, { padding: [40, 40] });
            })();
        </script>
    @endif
@endsection
