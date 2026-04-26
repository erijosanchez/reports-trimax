<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $motorizado->nombre }} — Ruta GPS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body { background:#f0f2f5; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }

        /* ── Mapa ── */
        #mapa-entrega { height:240px; width:100%; }
        .mapa-wrapper  { position:relative; overflow:hidden; }
        .mapa-toggle {
            position:absolute; bottom:10px; right:10px; z-index:999;
            background:#fff; border:none; border-radius:20px;
            padding:5px 12px; font-size:12px; font-weight:600; color:#1a73e8;
            box-shadow:0 2px 8px rgba(0,0,0,.2); cursor:pointer;
        }

        /* ── Header sticky ── */
        .header-top {
            background:linear-gradient(135deg,#1a2035,#0d47a1);
            color:#fff; padding:14px 16px;
            position:sticky; top:0; z-index:100;
            box-shadow:0 3px 10px rgba(0,0,0,.35);
        }
        .progress-bar-wrap { height:5px; background:rgba(255,255,255,.25); border-radius:3px; }
        .progress-bar-fill { height:5px; background:#4cff91; border-radius:3px; transition:width .4s; }

        /* ── Barra GPS ── */
        #gps-bar {
            background:#1a2035; color:#fff;
            padding:12px 16px; border-bottom:1px solid rgba(255,255,255,.08);
            position:sticky; top:0; z-index:99;
        }
        .gps-dot {
            width:9px; height:9px; border-radius:50%; display:inline-block;
            animation:dotpulse 1.6s ease infinite;
        }
        @keyframes dotpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
        .cronometro-gps {
            font-size:20px; font-weight:700; letter-spacing:2px;
            font-variant-numeric:tabular-nums; color:#f1c40f;
        }
        .btn-gps {
            border:none; border-radius:12px; font-weight:700;
            font-size:14px; padding:10px 18px; cursor:pointer;
            transition:opacity .15s, transform .1s;
        }
        .btn-gps:active { transform:scale(.96); }
        .btn-gps:disabled { opacity:.55; cursor:not-allowed; }
        .btn-gps.iniciar  { background:#2ecc71; color:#fff; }
        .btn-gps.finalizar { background:#e74c3c; color:#fff; }

        /* ── Paradas ── */
        .parada-card {
            border-radius:14px; overflow:hidden;
            box-shadow:0 2px 10px rgba(0,0,0,.08);
            transition:transform .1s;
        }
        .parada-card:active { transform:scale(.98); }
        .parada-numero {
            width:44px; height:44px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:18px; font-weight:800; flex-shrink:0; color:#fff;
        }
        .btn-entregar {
            border-radius:50px; font-weight:700; font-size:15px;
            padding:12px 0; width:100%;
            box-shadow:0 3px 10px rgba(40,167,69,.35);
        }
        .btn-fallido {
            border-radius:50px; font-weight:600; font-size:14px;
            padding:10px 0; width:100%;
        }
        .estado-badge { font-size:13px; padding:5px 12px; border-radius:20px; font-weight:600; }
        .card-completado { border-left:5px solid #28a745 !important; opacity:.75; }
        .card-fallido    { border-left:5px solid #dc3545 !important; opacity:.75; }
        .card-pendiente  { border-left:5px solid #dee2e6; }
        .card-en_camino  { border-left:5px solid #ffc107; }
        .direccion-link  { color:#1a73e8; text-decoration:none; font-size:13px; }
        .btn-navegar-ruta {
            background:#fff; color:#1a73e8; border:none; border-radius:50px;
            font-weight:700; font-size:12px; padding:7px 14px;
            display:flex; align-items:center; gap:5px;
            box-shadow:0 2px 8px rgba(0,0,0,.2);
        }
        .btn-navegar-parada {
            background:#e8f0fe; color:#1a73e8; border:none;
            border-radius:50px; font-size:12px; font-weight:600;
            padding:6px 12px; white-space:nowrap;
        }
        .resumen-final {
            display:none; background:#fff; border-radius:16px;
            padding:28px 20px; text-align:center; margin:20px 0;
        }
        .resumen-final.show { display:block; }
        .spinner-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.5); z-index:9999;
            align-items:center; justify-content:center;
        }
        .spinner-overlay.active { display:flex; }
        .leaflet-popup-content-wrapper { border-radius:10px; font-family:inherit; }

        /* ── Polyline GPS en mapa ── */
        .gps-status-dot { width:8px;height:8px;border-radius:50%;display:inline-block;vertical-align:middle; }
    </style>
</head>
<body>

{{-- ── MAPA ──────────────────────────────────────────────── --}}
<div class="mapa-wrapper" id="mapa-wrapper">
    <div id="mapa-entrega"></div>
    <button class="mapa-toggle" onclick="toggleMapa()">
        <i class="mdi mdi-chevron-up me-1" id="mapa-toggle-icon"></i>Ocultar mapa
    </button>
</div>

{{-- ── HEADER sticky ────────────────────────────────────── --}}
<div class="header-top">
    <div class="d-flex align-items-start justify-content-between mb-2">
        <div>
            <div style="font-size:12px;opacity:.7">Trimax — Ruta GPS</div>
            <div class="fw-bold" style="font-size:17px">{{ $motorizado->nombre }}</div>
            <div style="font-size:12px;opacity:.7">
                {{ today()->format('d/m/Y') }} · {{ $motorizado->sede }}
            </div>
        </div>
        <div class="text-end d-flex flex-column align-items-end gap-1">
            @if($rutaEntrega)
                <span id="badge-ruta" class="badge {{ $rutaEntrega->estado === 'completado' ? 'bg-success' : 'bg-warning text-dark' }}"
                      style="font-size:12px">
                    {{ ucfirst(str_replace('_',' ', $rutaEntrega->estado)) }}
                </span>
                <div style="font-size:12px;opacity:.75">
                    <span id="cont-completadas">{{ $rutaEntrega->paradas->where('estado','completado')->count() }}</span>
                    /{{ $rutaEntrega->paradas->count() }} entregas
                </div>
            @else
                <span class="badge bg-secondary" style="font-size:12px">Sin ruta hoy</span>
            @endif
        </div>
    </div>

    @if($rutaEntrega)
    @php $pct = $rutaEntrega->paradas->count()
        ? round($rutaEntrega->paradas->where('estado','completado')->count() / $rutaEntrega->paradas->count() * 100)
        : 0;
    @endphp
    <div class="d-flex align-items-center gap-3 mt-1">
        <div class="flex-grow-1 progress-bar-wrap">
            <div class="progress-bar-fill" id="barra-progreso" style="width:{{ $pct }}%"></div>
        </div>
        <button class="btn-navegar-ruta" onclick="navegarRutaCompleta()">
            <i class="mdi mdi-google-maps" style="font-size:15px"></i>Navegar ruta
        </button>
    </div>
    @endif
</div>

{{-- ── BARRA GPS ─────────────────────────────────────────── --}}
<div id="gps-bar">
    @if($rutaGps)
    {{-- GPS activo --}}
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div style="font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.8px">GPS activo</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="gps-dot" style="background:#f1c40f"></span>
                    <span class="cronometro-gps" id="cronometro"
                          data-started="{{ $rutaGps->started_at->timestamp }}">00:00:00</span>
                </div>
            </div>
            @if($motorizado->traccar_device_id)
            <div style="font-size:11px;color:rgba(255,255,255,.4)">
                <i class="mdi mdi-satellite-variant me-1"></i>Traccar ID {{ $motorizado->traccar_device_id }}
            </div>
            @endif
        </div>
        <button id="btn-finalizar" class="btn-gps finalizar"
                onclick="finalizarGps({{ $rutaGps->id }})">
            <i class="mdi mdi-stop-circle me-1"></i>Finalizar GPS
        </button>
    </div>
    @else
    {{-- GPS inactivo --}}
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div>
            <div style="font-size:11px;color:rgba(255,255,255,.4)">GPS no iniciado</div>
            <div style="font-size:13px;color:rgba(255,255,255,.65)">Inicia para registrar km</div>
        </div>
        <button id="btn-iniciar" class="btn-gps iniciar" onclick="iniciarGps()">
            <i class="mdi mdi-play-circle me-1"></i>Iniciar GPS
        </button>
    </div>
    @endif
</div>

{{-- ── CONTENIDO ────────────────────────────────────────── --}}
<div class="px-3 py-3 container-fluid" style="max-width:600px;margin:0 auto">

    {{-- Sin ruta de entregas --}}
    @if(!$rutaEntrega)
    <div class="py-5 text-center text-muted">
        <i class="mdi mdi-package-variant-closed mdi-48px d-block mb-2 opacity-50"></i>
        <div class="fw-semibold">Sin ruta de entregas asignada hoy</div>
        <div class="small mt-1">El GPS sí funciona — puedes iniciarlo desde arriba.</div>
    </div>
    @else

    {{-- Resumen cuando se completa todo --}}
    <div class="resumen-final {{ $pct === 100 ? 'show' : '' }}" id="resumen-final">
        <i class="mdi mdi-check-circle text-success" style="font-size:56px"></i>
        <h4 class="fw-bold mt-2 mb-1">¡Entregas completadas!</h4>
        <p class="text-muted small">Todas las paradas han sido registradas.</p>
        @if(!$rutaGps)
        <p class="text-muted small">El GPS fue finalizado.</p>
        @else
        <div class="alert alert-warning small py-2 mt-2">
            <i class="mdi mdi-alert-outline me-1"></i>Recuerda finalizar el GPS arriba.
        </div>
        @endif
    </div>

    {{-- Cards de paradas --}}
    @forelse($rutaEntrega->paradas as $parada)
    @php
        $orden   = $parada->orden;
        $colores = ['completado'=>'#28a745','fallido'=>'#dc3545','en_camino'=>'#ffc107','pendiente'=>'#1a73e8'];
        $color   = $colores[$parada->estado] ?? '#1a73e8';
        $mapsUrl = $orden->latitud && $orden->longitud
            ? "https://maps.google.com/?q={$orden->latitud},{$orden->longitud}"
            : "https://maps.google.com/?q=" . urlencode($orden->direccion);
    @endphp

    <div class="card parada-card border-0 mb-3 card-{{ $parada->estado }}"
         id="card-{{ $parada->id }}" data-estado="{{ $parada->estado }}">
        <div class="card-body p-3">

            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="parada-numero" style="background:{{ $color }}" id="num-{{ $parada->id }}">
                    {{ $parada->orden_secuencia }}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size:16px">{{ $orden->cliente_nombre }}</div>
                    @if($orden->cliente_telefono)
                    <a href="tel:{{ $orden->cliente_telefono }}" class="d-block text-muted small">
                        <i class="mdi mdi-phone me-1"></i>{{ $orden->cliente_telefono }}
                    </a>
                    @endif
                    @if($orden->referencia)
                    <div class="text-muted small">
                        <i class="mdi mdi-tag me-1"></i>{{ $orden->referencia }}
                    </div>
                    @endif
                </div>
                <div id="badge-estado-{{ $parada->id }}">
                    @if($parada->estado === 'completado')
                        <span class="badge bg-success estado-badge">
                            <i class="mdi mdi-check"></i> Entregado
                        </span>
                    @elseif($parada->estado === 'fallido')
                        <span class="badge bg-danger text-white estado-badge">
                            <i class="mdi mdi-close"></i> Fallido
                        </span>
                    @else
                        <span class="badge bg-light border text-secondary estado-badge">Pendiente</span>
                    @endif
                </div>
            </div>

            {{-- Dirección + Navegar --}}
            <div class="d-flex align-items-start gap-2 mb-3">
                <a href="{{ $mapsUrl }}" target="_blank"
                   class="d-flex flex-grow-1 align-items-start gap-1 direccion-link">
                    <i class="mdi mdi-map-marker mt-1" style="flex-shrink:0"></i>
                    <span>{{ $orden->direccion }}</span>
                </a>
                <button class="btn-navegar-parada"
                        onclick="navegarParada({{ $orden->latitud ?? 'null' }}, {{ $orden->longitud ?? 'null' }}, '{{ addslashes($orden->direccion) }}')">
                    <i class="mdi mdi-navigation"></i> Navegar
                </button>
            </div>

            @if($parada->hora_salida)
            <div class="text-muted small mb-2">
                <i class="mdi mdi-clock-check me-1"></i>
                Registrado a las {{ $parada->hora_salida->setTimezone('America/Lima')->format('H:i') }}
            </div>
            @endif

            {{-- Acciones --}}
            <div id="acciones-{{ $parada->id }}"
                 class="{{ in_array($parada->estado, ['completado','fallido']) ? 'd-none' : '' }}">

                <div id="notas-wrap-{{ $parada->id }}" style="display:none" class="mb-2">
                    <textarea id="notas-{{ $parada->id }}" class="form-control form-control-sm" rows="2"
                              placeholder="Motivo / observación…"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-entregar"
                            onclick="marcar({{ $parada->id }}, 'completado')">
                        <i class="mdi mdi-check-bold me-1"></i>Entregado
                    </button>
                    <button class="btn btn-outline-danger btn-fallido"
                            style="width:auto;padding:12px 14px"
                            onclick="toggleNotas({{ $parada->id }})">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                </div>

                <div id="btn-fallido-wrap-{{ $parada->id }}" style="display:none" class="mt-2">
                    <button class="btn btn-outline-danger btn-fallido"
                            onclick="marcar({{ $parada->id }}, 'fallido')">
                        <i class="mdi mdi-close-circle me-1"></i>No entregado / Nadie en casa
                    </button>
                </div>

            </div>
        </div>
    </div>
    @empty
    <div class="py-4 text-muted text-center">
        <i class="mdi mdi-package-variant mdi-36px d-block mb-2 opacity-50"></i>
        Sin paradas para esta ruta
    </div>
    @endforelse

    @endif {{-- $rutaEntrega --}}

</div>

{{-- Spinner --}}
<div class="spinner-overlay" id="spinner">
    <div class="text-white text-center">
        <div class="spinner-border mb-2" style="width:2.5rem;height:2.5rem"></div>
        <div class="fw-semibold">Guardando…</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Constantes ────────────────────────────────────────────────────────────────
const TOKEN = '{{ $token }}';
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;

const PARADAS_NAV = @json($paradasNav);
const COLORES     = { completado:'#28a745', fallido:'#dc3545', en_camino:'#ffc107', pendiente:'#1a73e8' };

// ── Mapa Leaflet ──────────────────────────────────────────────────────────────
const map = L.map('mapa-entrega', { zoomControl: false, attributionControl: false })
    .setView([-12.046374, -77.042793], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

const bounds     = [];
const pendientes = PARADAS_NAV.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
const destinoId  = pendientes.length > 0 ? pendientes[0].id : null;
const marcadoresParada = {};

PARADAS_NAV.forEach((p, idx) => {
    if (!p.lat || !p.lng) return;

    const c        = COLORES[p.estado] || '#1a73e8';
    const esDestino = p.id === destinoId;
    const label    = p.estado === 'completado' ? '✓' : p.estado === 'fallido' ? '✗' : (idx + 1);

    const icon = esDestino
        ? L.divIcon({
            className: '',
            html: `<div style="width:44px;height:44px;background:#28a745;
                        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                        border:3px solid #fff;box-shadow:0 5px 18px rgba(40,167,69,.55);
                        display:flex;align-items:center;justify-content:center;">
                       <span style="transform:rotate(45deg);color:#fff;font-size:18px">📦</span>
                   </div>`,
            iconSize: [44,44], iconAnchor: [22,44], popupAnchor: [0,-48],
        })
        : L.divIcon({
            className: '',
            html: `<div style="width:32px;height:32px;background:${c};
                        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                        border:2.5px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.3);
                        display:flex;align-items:center;justify-content:center;">
                       <span style="transform:rotate(45deg);color:#fff;font-weight:700;font-size:12px">${label}</span>
                   </div>`,
            iconSize: [32,32], iconAnchor: [16,32], popupAnchor: [0,-36],
        });

    const marker = L.marker([p.lat, p.lng], { icon })
        .bindPopup(`<strong>${p.secuencia ?? idx+1}. ${p.cliente}</strong><br><small>${p.direccion}</small>`)
        .addTo(map);

    marcadoresParada[p.id] = { marker, lat: p.lat, lng: p.lng };
    bounds.push([p.lat, p.lng]);
});

// Línea roja entre paradas (ruta planificada)
if (bounds.length > 1) {
    L.polyline(bounds, { color:'rgba(0,0,0,.12)', weight: 9, lineJoin:'round', lineCap:'round' })
        .addTo(map).bringToBack();
    L.polyline(bounds, { color:'#e74c3c', weight: 5, opacity: 0.85, lineJoin:'round', lineCap:'round',
        dashArray: null })
        .addTo(map);
    map.fitBounds(bounds, { padding: [40, 40] });
} else if (bounds.length === 1) {
    map.setView(bounds[0], 14);
}

// Posición actual (geolocalización del navegador)
let posActualMarker = null;
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(
        pos => {
            const latlng = [pos.coords.latitude, pos.coords.longitude];
            if (!posActualMarker) {
                const iconPos = L.divIcon({
                    className: '',
                    html: `<div style="width:16px;height:16px;border-radius:50%;background:#1a73e8;
                                border:3px solid #fff;box-shadow:0 2px 8px rgba(26,115,232,.5)"></div>`,
                    iconSize: [16,16], iconAnchor: [8,8],
                });
                posActualMarker = L.marker(latlng, { icon: iconPos, zIndexOffset: 1000 })
                    .bindPopup('Tu posición actual')
                    .addTo(map);
            } else {
                posActualMarker.setLatLng(latlng);
            }
        },
        null,
        { enableHighAccuracy: true, maximumAge: 15000 }
    );
}

// ── Toggle mapa ───────────────────────────────────────────────────────────────
let mapaVisible = true;
function toggleMapa() {
    const mapa = document.getElementById('mapa-entrega');
    const icon = document.getElementById('mapa-toggle-icon');
    const btn  = icon.closest('button');
    mapaVisible = !mapaVisible;
    mapa.style.height = mapaVisible ? '240px' : '0';
    icon.className = mapaVisible ? 'mdi mdi-chevron-up me-1' : 'mdi mdi-chevron-down me-1';
    btn.innerHTML  = mapaVisible ? '<i class="mdi mdi-chevron-up me-1"></i>Ocultar mapa'
                                 : '<i class="mdi mdi-chevron-down me-1"></i>Ver mapa';
    if (mapaVisible) setTimeout(() => map.invalidateSize(), 10);
}

// ── Cronómetro GPS ────────────────────────────────────────────────────────────
const cronEl = document.getElementById('cronometro');
if (cronEl) {
    function tick() {
        const diff = Math.floor(Date.now() / 1000) - parseInt(cronEl.dataset.started, 10);
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        cronEl.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }
    tick();
    setInterval(tick, 1000);
}

// ── Iniciar GPS ───────────────────────────────────────────────────────────────
async function iniciarGps() {
    const btn = document.getElementById('btn-iniciar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Iniciando…';
    try {
        const res  = await fetch(`/gps/${TOKEN}/iniciar`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al iniciar el GPS.');
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-play-circle me-1"></i>Iniciar GPS';
        }
    } catch (e) {
        alert('Error de conexión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-play-circle me-1"></i>Iniciar GPS';
    }
}

// ── Finalizar GPS ─────────────────────────────────────────────────────────────
async function finalizarGps(rutaId) {
    if (!confirm('¿Finalizar el GPS?\nSe calcularán los km recorridos desde Traccar.')) return;
    const btn = document.getElementById('btn-finalizar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Calculando…';
    try {
        const res  = await fetch(`/gps/${TOKEN}/finalizar/${rutaId}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.success) {
            alert(`✓ GPS finalizado\nDistancia registrada: ${data.distance_km} km`);
            location.reload();
        } else {
            alert(data.message || 'Error al finalizar.');
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-stop-circle me-1"></i>Finalizar GPS';
        }
    } catch (e) {
        alert('Error de conexión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-stop-circle me-1"></i>Finalizar GPS';
    }
}

// ── Marcar parada ─────────────────────────────────────────────────────────────
const totalParadas = {{ $rutaEntrega ? $rutaEntrega->paradas->count() : 0 }};
let completadas    = {{ $rutaEntrega ? $rutaEntrega->paradas->where('estado','completado')->count() : 0 }};

function toggleNotas(paradaId) {
    const wrap    = document.getElementById(`notas-wrap-${paradaId}`);
    const btnFall = document.getElementById(`btn-fallido-wrap-${paradaId}`);
    const show    = wrap.style.display === 'none';
    wrap.style.display    = show ? 'block' : 'none';
    btnFall.style.display = show ? 'block' : 'none';
}

async function marcar(paradaId, estado) {
    document.getElementById('spinner').classList.add('active');
    const notas = document.getElementById(`notas-${paradaId}`)?.value || '';

    try {
        const res = await fetch(`/gps/${TOKEN}/parada/${paradaId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ estado, notas }),
        });

        if (!res.ok) throw new Error();
        const data = await res.json();

        const card     = document.getElementById(`card-${paradaId}`);
        const num      = document.getElementById(`num-${paradaId}`);
        const badge    = document.getElementById(`badge-estado-${paradaId}`);
        const acciones = document.getElementById(`acciones-${paradaId}`);

        card.className       = `card parada-card border-0 mb-3 card-${estado}`;
        num.style.background = COLORES[estado];
        acciones.classList.add('d-none');

        if (estado === 'completado') {
            completadas++;
            badge.innerHTML = `<span class="badge bg-success estado-badge"><i class="mdi mdi-check"></i> Entregado</span>`;
            // Actualizar marcador en mapa
            const info = marcadoresParada[paradaId];
            if (info) {
                const iconActual = L.divIcon({
                    className: '',
                    html: `<div style="width:32px;height:32px;background:#28a745;
                                border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                                border:2.5px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.3);
                                display:flex;align-items:center;justify-content:center;">
                               <span style="transform:rotate(45deg);color:#fff;font-weight:700;font-size:14px">✓</span>
                           </div>`,
                    iconSize: [32,32], iconAnchor: [16,32], popupAnchor: [0,-36],
                });
                info.marker.setIcon(iconActual);
            }
        } else {
            badge.innerHTML = `<span class="badge bg-danger text-white estado-badge"><i class="mdi mdi-close"></i> Fallido</span>`;
            const info = marcadoresParada[paradaId];
            if (info) {
                const iconFall = L.divIcon({
                    className: '',
                    html: `<div style="width:32px;height:32px;background:#dc3545;
                                border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                                border:2.5px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.3);
                                display:flex;align-items:center;justify-content:center;">
                               <span style="transform:rotate(45deg);color:#fff;font-weight:700;font-size:14px">✗</span>
                           </div>`,
                    iconSize: [32,32], iconAnchor: [16,32], popupAnchor: [0,-36],
                });
                info.marker.setIcon(iconFall);
            }
        }

        if (totalParadas) {
            const pct = Math.round(completadas / totalParadas * 100);
            const barra = document.getElementById('barra-progreso');
            const contEl = document.getElementById('cont-completadas');
            if (barra) barra.style.width = pct + '%';
            if (contEl) contEl.textContent = completadas;
        }

        if (data.ruta_estado === 'completado') {
            const badgeRuta = document.getElementById('badge-ruta');
            if (badgeRuta) { badgeRuta.className = 'badge bg-success'; badgeRuta.textContent = 'Completada'; }
            document.getElementById('resumen-final')?.classList.add('show');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (data.ruta_estado === 'en_ruta') {
            const badgeRuta = document.getElementById('badge-ruta');
            if (badgeRuta) { badgeRuta.className = 'badge bg-warning text-dark'; badgeRuta.textContent = 'En ruta'; }
        }

        if (navigator.vibrate) navigator.vibrate(estado === 'completado' ? [50,30,50] : [100]);

    } catch {
        alert('Error al guardar. Verifica tu conexión.');
    } finally {
        document.getElementById('spinner').classList.remove('active');
    }
}

// ── Navegar ───────────────────────────────────────────────────────────────────
function navegarParada(lat, lng, direccion) {
    const url = (lat && lng)
        ? `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving`
        : `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(direccion)}&travelmode=driving`;
    window.open(url, '_blank');
}

function navegarRutaCompleta() {
    const pend = PARADAS_NAV.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
    if (!pend.length) { alert('No hay paradas pendientes.'); return; }
    const destino  = pend[pend.length - 1];
    const destStr  = (destino.lat && destino.lng)
        ? `${destino.lat},${destino.lng}`
        : encodeURIComponent(destino.direccion);
    const waypts = pend.slice(0, -1).filter(p => p.lat && p.lng)
        .map(p => `${p.lat},${p.lng}`).join('|');
    const open = origin => {
        let url = `https://www.google.com/maps/dir/?api=1${origin}&destination=${destStr}&travelmode=driving`;
        if (waypts) url += `&waypoints=${waypts}`;
        window.open(url, '_blank');
    };
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => open(`&origin=${pos.coords.latitude},${pos.coords.longitude}`),
            ()  => open(''),
            { timeout: 5000 }
        );
    } else {
        open('');
    }
}
</script>
</body>
</html>
