@extends('layouts.app')

@section('title', 'Control de Rutas GPS')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-rutas { height: 420px; width: 100%; border-radius: 8px; overflow: hidden; }
    .mapa-placeholder { display:flex; align-items:center; justify-content:center; height:420px; }
    @media (max-width:767px) { #map-rutas, .mapa-placeholder { height:280px; } }
    .mot-status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .ruta-leyenda-dot { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:6px; flex-shrink:0; }
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
                                <i class="mdi mdi-map-marker-distance me-2 text-success"></i>Control de Rutas GPS
                            </h4>
                            <p class="mb-0 text-muted small">
                                Monitoreo de recorridos — los motorizados inician y finalizan desde su enlace personal
                            </p>
                        </div>
                        <a href="{{ route('tracking.mapa') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i>Mapa en vivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        {{-- ── Estado de motorizados ────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-motorbike me-1 text-primary"></i>Estado de Motorizados
                        </h6>
                        <span class="small text-muted">Los conductores usan su enlace GPS personal para iniciar/finalizar</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Motorizado</th>
                                        <th>Sede</th>
                                        <th>Estado</th>
                                        <th>Tiempo en ruta</th>
                                        <th>GPS Traccar</th>
                                        <th>Enlace personal</th>
                                        @unless(Auth::user()->isSede())
                                        <th></th>
                                        @endunless
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($motorizados as $m)
                                    @php $rutaActiva = $rutasActivas->get($m->id); @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $m->nombre }}</td>
                                        <td><span class="badge bg-secondary">{{ $m->sede }}</span></td>
                                        <td>
                                            @if($rutaActiva)
                                                <span class="badge bg-warning text-dark">
                                                    <span class="mot-status-dot bg-danger me-1"></span>En ruta
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <span class="mot-status-dot bg-white me-1"></span>Disponible
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rutaActiva)
                                                <span class="badge bg-warning text-dark font-monospace"
                                                      data-cronometro
                                                      data-started="{{ $rutaActiva->started_at->timestamp }}">
                                                    --:--:--
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m->traccar_device_id)
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-satellite-variant me-1"></i>ID {{ $m->traccar_device_id }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border">Sin GPS</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m->token_gps)
                                                <div class="d-flex gap-1 align-items-center">
                                                    <button class="btn btn-xs btn-outline-primary"
                                                            onclick="copiarEnlace('{{ url('/gps/'.$m->token_gps) }}', this)"
                                                            title="Copiar enlace">
                                                        <i class="mdi mdi-content-copy"></i>
                                                    </button>
                                                    <a href="https://wa.me/{{ preg_replace('/\D/','',$m->telefono??'') }}?text={{ urlencode('Hola '.$m->nombre.' 👋'."\nTu enlace GPS: ".url('/gps/'.$m->token_gps)) }}"
                                                       target="_blank" class="btn btn-xs btn-outline-success" title="Enviar por WhatsApp">
                                                        <i class="mdi mdi-whatsapp"></i>
                                                    </a>
                                                    <a href="{{ url('/gps/'.$m->token_gps) }}" target="_blank"
                                                       class="btn btn-xs btn-outline-secondary" title="Abrir enlace">
                                                        <i class="mdi mdi-open-in-new"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <button class="btn btn-xs btn-outline-warning"
                                                        data-generar="{{ $m->id }}"
                                                        onclick="generarEnlace({{ $m->id }}, this)">
                                                    <i class="mdi mdi-link-variant me-1"></i>Generar enlace
                                                </button>
                                            @endif
                                        </td>
                                        @unless(Auth::user()->isSede())
                                        <td>
                                            @if($rutaActiva)
                                                <button class="btn btn-xs btn-outline-danger"
                                                        onclick="forzarCierre({{ $rutaActiva->id }}, '{{ $m->nombre }}')"
                                                        title="Forzar cierre de ruta (emergencia)">
                                                    <i class="mdi mdi-stop-circle"></i>
                                                </button>
                                            @endif
                                        </td>
                                        @endunless
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Sin motorizados activos
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

        {{-- ── Mapa + Resumen ───────────────────────────────────────── --}}
        <div class="row mb-4">

            {{-- Mapa --}}
            <div class="col-lg-8 col-md-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0">
                            <i class="mdi mdi-map-marker-path me-1 text-primary"></i>Mapa de Rutas
                        </h6>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <select id="sel-mot-mapa" class="form-select form-select-sm" style="min-width:160px">
                                <option value="">Todos los motorizados</option>
                                @foreach($motorizados as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }} — {{ $m->sede }}</option>
                                @endforeach
                            </select>
                            <input type="date" id="sel-fecha-mapa" class="form-control form-control-sm"
                                   value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}"
                                   style="width:140px">
                            <button id="btn-ver-mapa" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-magnify me-1"></i>Ver rutas
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="mapa-placeholder" class="mapa-placeholder text-muted">
                            <div class="text-center">
                                <i class="mdi mdi-routes mdi-48px d-block mb-2 opacity-50 text-primary"></i>
                                <p class="small">Selecciona una fecha y presiona <strong>Ver rutas</strong></p>
                            </div>
                        </div>
                        <div id="map-rutas" class="d-none"></div>
                        <div id="leyenda-mapa" class="d-none px-3 pb-3 pt-2 border-top">
                            <div class="d-flex flex-wrap gap-3" id="leyenda-items"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resumen diario --}}
            <div class="col-lg-4 col-md-12 grid-margin">
                <div class="shadow-sm border-0 card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="mdi mdi-chart-bar me-1 text-success"></i>Resumen del día
                        </h6>
                        <span id="lbl-fecha-resumen" class="badge bg-light text-dark border small">
                            {{ today()->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="card-body p-3" id="panel-resumen">
                        <p class="text-muted small text-center py-4">
                            <i class="mdi mdi-chart-bar mdi-24px d-block mb-1 opacity-50"></i>
                            Carga el mapa para ver el resumen
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Historial ────────────────────────────────────────────── --}}
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0">
                            <i class="mdi mdi-history me-1 text-secondary"></i>Historial de Rutas GPS
                        </h6>
                        <form method="GET" action="{{ route('tracking.rutas-gps') }}" class="d-flex gap-2 flex-wrap">
                            <select name="motorizado_id" class="form-select form-select-sm" style="min-width:160px">
                                <option value="">Todos los motorizados</option>
                                @foreach($motorizados as $m)
                                <option value="{{ $m->id }}" {{ request('motorizado_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                                @endforeach
                            </select>
                            <input type="date" name="fecha" class="form-control form-control-sm"
                                   value="{{ request('fecha') }}" style="width:140px">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            @if(request('motorizado_id') || request('fecha'))
                            <a href="{{ route('tracking.rutas-gps') }}" class="btn btn-sm btn-outline-danger">
                                <i class="mdi mdi-close"></i>
                            </a>
                            @endif
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-muted small">#</th>
                                        <th>Motorizado</th>
                                        <th>Sede</th>
                                        <th>Fecha</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Duración</th>
                                        <th>Distancia</th>
                                        <th>Puntos GPS</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historial as $r)
                                    <tr>
                                        <td class="text-muted small">{{ $r->id }}</td>
                                        <td class="fw-semibold">{{ $r->motorizado->nombre }}</td>
                                        <td><span class="badge bg-secondary">{{ $r->motorizado->sede }}</span></td>
                                        <td>{{ $r->started_at->setTimezone('America/Lima')->format('d/m/Y') }}</td>
                                        <td>{{ $r->started_at->setTimezone('America/Lima')->format('H:i') }}</td>
                                        <td>{{ $r->ended_at?->setTimezone('America/Lima')->format('H:i') ?? '—' }}</td>
                                        <td>{{ $r->duracion ?? '—' }}</td>
                                        <td>
                                            @if($r->status === 'completed' && $r->distance_km !== null)
                                                <span class="fw-semibold text-primary">{{ number_format($r->distance_km, 2) }} km</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($r->polyline && count($r->polyline))
                                                <span class="badge bg-info">{{ count($r->polyline) }}</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($r->status === 'active')
                                                <span class="badge bg-warning text-dark">En curso</span>
                                            @else
                                                <span class="badge bg-success">Completada</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            <i class="mdi mdi-routes mdi-36px d-block mb-2 opacity-50"></i>
                                            Sin rutas registradas
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($historial->hasPages())
                        <div class="px-3 py-2 border-top">
                            {{ $historial->links() }}
                        </div>
                        @endif
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
const URL_API        = '{{ url('/tracking/api/rutas-gps') }}';
const URL_RESUMEN    = '{{ url('/tracking/api/rutas-gps/resumen') }}';
const URL_TOKEN_BASE = '{{ url('/tracking/rutas-gps/token') }}';
const URL_CIERRE_BASE = '{{ url('/tracking/rutas-gps') }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const COLORES = [
    '#3498db','#e74c3c','#2ecc71','#f39c12','#9b59b6',
    '#1abc9c','#e67e22','#c0392b','#16a085','#8e44ad',
];

// ── Mapa ─────────────────────────────────────────────────────────────────────
let mapInstance = null;
let capasRutas  = [];

function initMap() {
    if (mapInstance) return;
    document.getElementById('mapa-placeholder').classList.add('d-none');
    document.getElementById('map-rutas').classList.remove('d-none');
    mapInstance = L.map('map-rutas').setView([-12.046374, -77.042793], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19,
    }).addTo(mapInstance);
}

function limpiarCapas() {
    capasRutas.forEach(c => mapInstance.removeLayer(c));
    capasRutas = [];
}

function iconoCirculo(color, letra) {
    return L.divIcon({
        className: '',
        html: `<div style="width:20px;height:20px;border-radius:50%;background:${color};color:#fff;
                    font-weight:bold;display:flex;align-items:center;justify-content:center;
                    font-size:9px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.5)">${letra}</div>`,
        iconSize: [20, 20], iconAnchor: [10, 10],
    });
}

async function cargarMapa() {
    const motId = document.getElementById('sel-mot-mapa').value;
    const fecha = document.getElementById('sel-fecha-mapa').value;
    const btn   = document.getElementById('btn-ver-mapa');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cargando…';

    try {
        initMap();
        limpiarCapas();

        let urlMapa    = URL_API + '?fecha=' + fecha;
        let urlResumen = URL_RESUMEN + '?fecha=' + fecha;
        if (motId) { urlMapa += '&motorizado_id=' + motId; urlResumen += '&motorizado_id=' + motId; }

        const [resMapa, resResumen] = await Promise.all([fetch(urlMapa), fetch(urlResumen)]);
        const rutas  = await resMapa.json();
        const rData  = await resResumen.json();

        const allCoords    = [];
        const leyendaItems = [];

        rutas.forEach((ruta, idx) => {
            const poly  = ruta.polyline || [];
            if (!poly.length) return;
            const color = COLORES[idx % COLORES.length];

            const linea = L.polyline(poly, { color, weight: 5, opacity: 0.85 }).addTo(mapInstance);
            linea.bindPopup(
                `<strong>${ruta.motorizado}</strong><br>` +
                `${ruta.started_at} → ${ruta.ended_at || '--'}<br>` +
                `<strong>${ruta.distance_km} km</strong>`
            );
            capasRutas.push(linea);
            allCoords.push(...poly);

            const mA = L.marker(poly[0], { icon: iconoCirculo(color, 'A') })
                .bindPopup(`<strong>${ruta.motorizado}</strong><br>Inicio: ${ruta.started_at}`)
                .addTo(mapInstance);
            const mB = L.marker(poly[poly.length - 1], { icon: iconoCirculo(color, 'B') })
                .bindPopup(`<strong>${ruta.motorizado}</strong><br>Fin: ${ruta.ended_at || '--'}<br>${ruta.distance_km} km`)
                .addTo(mapInstance);
            capasRutas.push(mA, mB);

            leyendaItems.push({ color, nombre: ruta.motorizado, km: ruta.distance_km });
        });

        if (allCoords.length) mapInstance.fitBounds(allCoords, { padding: [40, 40] });

        const leyendaEl = document.getElementById('leyenda-mapa');
        if (leyendaItems.length) {
            document.getElementById('leyenda-items').innerHTML = leyendaItems.map(i =>
                `<span class="d-flex align-items-center small text-muted">
                    <span class="ruta-leyenda-dot" style="background:${i.color}"></span>
                    ${i.nombre} · <strong class="ms-1 text-dark">${i.km} km</strong>
                 </span>`
            ).join('');
            leyendaEl.classList.remove('d-none');
        } else {
            leyendaEl.classList.add('d-none');
        }

        renderResumen(rData.resumen || [], rData.fecha);

    } catch (e) {
        console.error(e);
        alert('Error al cargar las rutas GPS.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-magnify me-1"></i>Ver rutas';
    }
}

function renderResumen(resumen, fecha) {
    const el = document.getElementById('panel-resumen');
    if (fecha) {
        const [y, m, d] = fecha.split('-');
        document.getElementById('lbl-fecha-resumen').textContent = `${d}/${m}/${y}`;
    }
    if (!resumen.length) {
        el.innerHTML = `<p class="text-muted small text-center py-4">
            <i class="mdi mdi-chart-bar mdi-24px d-block mb-1 opacity-50"></i>
            Sin rutas completadas para esta fecha</p>`;
        return;
    }
    const totalKm    = resumen.reduce((s, r) => s + parseFloat(r.total_km), 0).toFixed(2);
    const totalRutas = resumen.reduce((s, r) => s + r.rutas, 0);
    el.innerHTML = resumen.map(r => `
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <div>
                <div class="fw-semibold small">${r.motorizado}</div>
                <div class="text-muted" style="font-size:11px">${r.sede} · ${r.rutas} ruta${r.rutas !== 1 ? 's' : ''}</div>
            </div>
            <div class="fw-bold text-primary">${r.total_km} km</div>
        </div>
    `).join('') + `
        <div class="d-flex justify-content-between align-items-center pt-3 mt-1">
            <span class="small text-muted">${totalRutas} rutas totales</span>
            <span class="fw-bold text-success fs-6">${totalKm} km</span>
        </div>`;
}

// ── Generar enlace GPS ────────────────────────────────────────────────────────
async function generarEnlace(motId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res  = await fetch(`${URL_TOKEN_BASE}/${motId}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();

        if (data.success) {
            await navigator.clipboard.writeText(data.url).catch(() => {});
            alert(`Enlace generado y copiado:\n${data.url}\n\nComparte este enlace con el motorizado (WhatsApp u otro medio).`);
            location.reload();
        } else {
            alert('Error al generar el enlace.');
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-link-variant me-1"></i>Generar enlace';
        }
    } catch (e) {
        alert('Error de conexión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-link-variant me-1"></i>Generar enlace';
    }
}

// ── Copiar enlace ─────────────────────────────────────────────────────────────
async function copiarEnlace(url, btn) {
    try {
        await navigator.clipboard.writeText(url);
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check text-success"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 1500);
    } catch (e) {
        prompt('Copia este enlace:', url);
    }
}

// ── Forzar cierre (admin emergencia) ─────────────────────────────────────────
async function forzarCierre(rutaId, nombre) {
    if (!confirm(`¿Forzar el cierre de la ruta de ${nombre}?\nSe calcularán los km hasta ahora desde Traccar.`)) return;

    try {
        const res  = await fetch(`${URL_CIERRE_BASE}/${rutaId}/forzar-cierre`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();

        if (data.success) {
            alert(`Ruta cerrada.\nDistancia: ${data.distance_km} km · Puntos GPS: ${data.puntos}`);
            location.reload();
        } else {
            alert(data.message || 'Error al forzar el cierre.');
        }
    } catch (e) {
        alert('Error de conexión.');
    }
}

// ── Cronómetros ───────────────────────────────────────────────────────────────
function tickCronometros() {
    const ahora = Math.floor(Date.now() / 1000);
    document.querySelectorAll('[data-cronometro]').forEach(el => {
        const diff = ahora - parseInt(el.dataset.started, 10);
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    });
}
if (document.querySelectorAll('[data-cronometro]').length) {
    tickCronometros();
    setInterval(tickCronometros, 1000);
}

document.getElementById('btn-ver-mapa').addEventListener('click', cargarMapa);
</script>
@endpush
