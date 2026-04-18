@extends('layouts.app')

@section('title', 'Historial de Recorrido')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: calc(100vh - 300px); min-height: 420px; width: 100%; border-radius: 6px; }
    .stat-mini { font-size: 12px; }
    .punto-inicio, .punto-fin { font-size: 13px; }
    @media (max-width: 767px) { #map { height: 320px; } }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="mdi mdi-map-search me-2 text-primary"></i>Historial de Recorrido
                            </h4>
                            <p class="mb-0 text-muted small">Recorrido GPS del día graficado sobre el mapa</p>
                        </div>
                        <a href="{{ route('tracking.mapa') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>Volver al mapa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- Filtros --}}
        <div class="row mb-4">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4 col-sm-12">
                                <label class="form-label small fw-bold mb-1">Motorizado</label>
                                <select id="sel-motorizado" class="form-select form-select-sm">
                                    <option value="">Seleccionar motorizado</option>
                                    @foreach($motorizados as $m)
                                        <option value="{{ $m->id }}"
                                            {{ request('motorizado_id') == $m->id ? 'selected' : '' }}>
                                            {{ $m->nombre }} — {{ $m->sede }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <label class="form-label small fw-bold mb-1">Fecha</label>
                                <input type="date" id="sel-fecha" class="form-control form-control-sm"
                                       value="{{ request('fecha', today()->toDateString()) }}"
                                       max="{{ today()->toDateString() }}">
                            </div>
                            <div class="col-md-2 col-sm-12">
                                <button id="btn-buscar" class="btn btn-sm btn-primary w-100">
                                    <i class="mdi mdi-magnify me-1"></i>Ver recorrido
                                </button>
                            </div>
                            <div class="col-md-3 col-sm-12 d-flex gap-2 align-items-center">
                                <span id="badge-puntos" class="badge bg-secondary">Sin datos</span>
                                <span id="badge-distancia" class="badge bg-info d-none"></span>
                                <span id="badge-velocidad" class="badge bg-warning text-dark d-none"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            {{-- Mapa --}}
            <div class="mb-4 col-lg-8 col-md-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" id="titulo-mapa">
                            <i class="mdi mdi-map-marker-path me-1 text-primary"></i>Recorrido del día
                        </h6>
                        <div class="d-flex gap-2">
                            <span class="small text-muted d-flex align-items-center gap-1">
                                <span style="display:inline-block;width:14px;height:4px;background:#2ecc71;border-radius:2px"></span> Inicio
                            </span>
                            <span class="small text-muted d-flex align-items-center gap-1">
                                <span style="display:inline-block;width:14px;height:4px;background:#3498db;border-radius:2px"></span> Ruta
                            </span>
                            <span class="small text-muted d-flex align-items-center gap-1">
                                <span style="display:inline-block;width:14px;height:4px;background:#e74c3c;border-radius:2px"></span> Fin
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div id="map">
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div class="text-center">
                                    <i class="mdi mdi-map-search mdi-48px d-block mb-2"></i>
                                    <p>Selecciona un motorizado y fecha para ver el recorrido</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel lateral stats --}}
            <div class="mb-4 col-lg-4 col-md-12">

                {{-- Stats del recorrido --}}
                <div class="shadow-sm border-0 card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="mdi mdi-chart-line me-1 text-success"></i>Estadísticas</h6>
                    </div>
                    <div class="card-body p-3" id="panel-stats">
                        <p class="text-muted small text-center py-2">Carga un recorrido para ver estadísticas</p>
                    </div>
                </div>

                {{-- Timeline puntos --}}
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="mb-0"><i class="mdi mdi-clock-outline me-1 text-warning"></i>Puntos GPS</h6>
                        <span id="total-puntos" class="badge bg-secondary">0</span>
                    </div>
                    <div class="card-body p-2" style="max-height:300px;overflow-y:auto" id="lista-puntos">
                        <p class="text-muted small text-center py-2">Sin datos</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let mapInstance = null;
let rutaLayer   = null;
let marcadores  = [];

function initMap() {
    if (mapInstance) return;
    mapInstance = L.map('map').setView([-12.046374, -77.042793], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19,
    }).addTo(mapInstance);
}

function limpiarMapa() {
    if (!mapInstance) return;
    if (rutaLayer) { mapInstance.removeLayer(rutaLayer); rutaLayer = null; }
    marcadores.forEach(m => mapInstance.removeLayer(m));
    marcadores = [];
}

function calcularDistanciaKm(puntos) {
    let total = 0;
    for (let i = 1; i < puntos.length; i++) {
        const a = mapInstance.distance(
            [puntos[i-1].lat, puntos[i-1].lng],
            [puntos[i].lat,   puntos[i].lng]
        );
        total += a;
    }
    return (total / 1000).toFixed(2);
}

function iconoMarcador(color, icono) {
    return L.divIcon({
        className: '',
        html: `<div style="width:24px;height:24px;border-radius:50%;background:${color};
                    color:#fff;display:flex;align-items:center;justify-content:center;
                    font-size:12px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.4)">
                    <i class="mdi ${icono}"></i>
               </div>`,
        iconSize: [24, 24], iconAnchor: [12, 12],
    });
}

function renderStats(puntos, nombre, fecha) {
    if (!puntos.length) {
        document.getElementById('panel-stats').innerHTML =
            '<p class="text-muted small text-center py-2">Sin datos para esta fecha</p>';
        return;
    }

    const distancia  = calcularDistanciaKm(puntos);
    const velocidades = puntos.map(p => p.vel).filter(v => v > 0);
    const velMax     = velocidades.length ? Math.max(...velocidades).toFixed(1) : 0;
    const velProm    = velocidades.length
        ? (velocidades.reduce((a,b) => a+b, 0) / velocidades.length).toFixed(1) : 0;

    const tsInicio = puntos[0].ts   ? new Date(puntos[0].ts).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'}) : '--';
    const tsFin    = puntos.at(-1).ts ? new Date(puntos.at(-1).ts).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'}) : '--';

    document.getElementById('panel-stats').innerHTML = `
        <div class="row g-2 text-center">
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-primary">${distancia} km</div>
                    <div class="text-muted stat-mini">Distancia</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-info">${puntos.length}</div>
                    <div class="text-muted stat-mini">Puntos GPS</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-warning">${velMax} km/h</div>
                    <div class="text-muted stat-mini">Vel. máxima</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-success">${velProm} km/h</div>
                    <div class="text-muted stat-mini">Vel. promedio</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-secondary">${tsInicio}</div>
                    <div class="text-muted stat-mini">Inicio</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 bg-light py-2">
                    <div class="fw-bold text-danger">${tsFin}</div>
                    <div class="text-muted stat-mini">Fin</div>
                </div>
            </div>
        </div>`;

    document.getElementById('badge-distancia').className  = 'badge bg-info';
    document.getElementById('badge-distancia').textContent = distancia + ' km';
    document.getElementById('badge-velocidad').className  = 'badge bg-warning text-dark';
    document.getElementById('badge-velocidad').textContent = velMax + ' km/h máx';
}

function renderListaPuntos(puntos) {
    document.getElementById('total-puntos').textContent = puntos.length;

    if (!puntos.length) {
        document.getElementById('lista-puntos').innerHTML =
            '<p class="text-muted small text-center py-2">Sin datos</p>';
        return;
    }

    // Mostrar solo cada Nth punto para no saturar (máx 50 en lista)
    const step  = Math.max(1, Math.floor(puntos.length / 50));
    const items = puntos.filter((_, i) => i % step === 0);

    document.getElementById('lista-puntos').innerHTML = items.map((p, i) => {
        const ts  = p.ts ? new Date(p.ts).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '--';
        return `
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom"
             style="font-size:11px;cursor:pointer"
             onclick="irAPunto(${p.lat},${p.lng})">
            <span class="text-muted">${ts}</span>
            <span class="badge bg-light text-dark border">${p.vel} km/h</span>
        </div>`;
    }).join('');
}

function irAPunto(lat, lng) {
    if (mapInstance) mapInstance.setView([lat, lng], 17);
}

async function cargarHistorial() {
    const motorizadoId = document.getElementById('sel-motorizado').value;
    const fecha        = document.getElementById('sel-fecha').value;

    if (!motorizadoId) {
        alert('Selecciona un motorizado');
        return;
    }

    const btn = document.getElementById('btn-buscar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando…';

    try {
        initMap();
        limpiarMapa();

        const res  = await fetch(`/tracking/api/motorizados/${motorizadoId}/historial?fecha=${fecha}`);
        const data = await res.json();
        const puntos = data.puntos || [];

        document.getElementById('badge-puntos').className  = 'badge ' + (puntos.length ? 'bg-success' : 'bg-secondary');
        document.getElementById('badge-puntos').textContent = puntos.length + ' puntos GPS';

        document.getElementById('titulo-mapa').innerHTML =
            `<i class="mdi mdi-map-marker-path me-1 text-primary"></i>
             ${data.motorizado?.nombre} — ${fecha}`;

        if (!puntos.length) {
            document.getElementById('panel-stats').innerHTML =
                '<p class="text-muted small text-center py-2">Sin datos GPS para esta fecha</p>';
            document.getElementById('lista-puntos').innerHTML =
                '<p class="text-muted small text-center py-2">Sin datos</p>';
            document.getElementById('total-puntos').textContent = '0';
            return;
        }

        const coords = puntos.map(p => [p.lat, p.lng]);

        // Colorear la polyline por velocidad (degradado verde→amarillo→rojo)
        for (let i = 1; i < coords.length; i++) {
            const vel  = puntos[i].vel || 0;
            const color = vel > 60 ? '#e74c3c'
                        : vel > 30 ? '#f39c12'
                        : '#3498db';
            L.polyline([coords[i-1], coords[i]], { color, weight: 4, opacity: 0.8 }).addTo(mapInstance);
        }

        // Marcador inicio
        const mInicio = L.marker(coords[0], { icon: iconoMarcador('#2ecc71', 'mdi-flag') })
            .bindPopup(`<strong>Inicio</strong><br>${puntos[0].ts
                ? new Date(puntos[0].ts).toLocaleTimeString('es-PE') : ''}`)
            .addTo(mapInstance);
        marcadores.push(mInicio);

        // Marcador fin
        const mFin = L.marker(coords.at(-1), { icon: iconoMarcador('#e74c3c', 'mdi-flag-checkered') })
            .bindPopup(`<strong>Fin</strong><br>${puntos.at(-1).ts
                ? new Date(puntos.at(-1).ts).toLocaleTimeString('es-PE') : ''}`)
            .addTo(mapInstance);
        marcadores.push(mFin);

        // Ajustar vista
        mapInstance.fitBounds(coords, { padding: [40, 40] });

        renderStats(puntos, data.motorizado?.nombre, fecha);
        renderListaPuntos(puntos);

    } catch (e) {
        console.error(e);
        alert('Error al cargar el historial');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-magnify me-1"></i>Ver recorrido';
    }
}

document.getElementById('btn-buscar').addEventListener('click', cargarHistorial);

// Auto-cargar si vienen parámetros en URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('motorizado_id')) {
    document.getElementById('sel-motorizado').value = urlParams.get('motorizado_id');
    cargarHistorial();
}
</script>
@endpush
