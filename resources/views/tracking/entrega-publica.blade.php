<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mis entregas — {{ $ruta->fecha->format('d/m/Y') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        /* ── Mapa ── */
        #mapa-entrega {
            height: 220px;
            width: 100%;
        }
        .mapa-wrapper {
            position: relative;
            overflow: hidden;
        }
        .mapa-toggle {
            position: absolute;
            bottom: 10px; right: 10px;
            z-index: 999;
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #1a73e8;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
            cursor: pointer;
        }

        /* ── Header ── */
        .header-top {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff; padding: 16px 20px;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }

        .progress-bar-wrap { height: 6px; background: rgba(255,255,255,.3); border-radius: 3px; }
        .progress-bar-fill { height: 6px; background: #4cff91; border-radius: 3px; transition: width .4s; }

        .parada-card {
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            transition: transform .1s;
        }
        .parada-card:active { transform: scale(.98); }

        .parada-numero {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; flex-shrink: 0; color: #fff;
        }

        .btn-entregar {
            border-radius: 50px; font-weight: 700; font-size: 15px;
            padding: 12px 0; width: 100%;
            box-shadow: 0 3px 10px rgba(40,167,69,.35);
        }
        .btn-fallido {
            border-radius: 50px; font-weight: 600; font-size: 14px;
            padding: 10px 0; width: 100%;
        }

        .estado-badge {
            font-size: 13px; padding: 5px 12px; border-radius: 20px; font-weight: 600;
        }

        .card-completado { border-left: 5px solid #28a745 !important; opacity: .75; }
        .card-fallido    { border-left: 5px solid #dc3545 !important; opacity: .75; }
        .card-pendiente  { border-left: 5px solid #dee2e6; }
        .card-en_camino  { border-left: 5px solid #ffc107; }

        .direccion-link { color: #1a73e8; text-decoration: none; font-size: 13px; }
        .direccion-link:hover { text-decoration: underline; }

        .spinner-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .spinner-overlay.active { display: flex; }

        .resumen-final {
            display: none; background: #fff; border-radius: 16px;
            padding: 28px 20px; text-align: center; margin: 20px 0;
        }
        .resumen-final.show { display: block; }

        .btn-navegar-ruta {
            background: #fff; color: #1a73e8; border: none;
            border-radius: 50px; font-weight: 700; font-size: 13px;
            padding: 8px 16px; display: flex; align-items: center; gap: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .btn-navegar-parada {
            background: #e8f0fe; color: #1a73e8; border: none;
            border-radius: 50px; font-size: 12px; font-weight: 600;
            padding: 6px 12px; white-space: nowrap;
        }
        .btn-navegar-parada:active { background: #d2e3fc; }

        /* ── Popup Leaflet ── */
        .leaflet-popup-content-wrapper { border-radius: 10px; font-family: inherit; }
    </style>
</head>
<body>

{{-- ── MAPA ─────────────────────────────────────────────── --}}
<div class="mapa-wrapper" id="mapa-wrapper">
    <div id="mapa-entrega"></div>
    <button class="mapa-toggle" onclick="toggleMapa()">
        <i class="me-1 mdi mdi-chevron-up" id="mapa-toggle-icon"></i>Ocultar mapa
    </button>
</div>

{{-- Header sticky --}}
<div class="header-top">
    <div class="d-flex align-items-start justify-content-between mb-2">
        <div>
            <div style="font-size:13px;opacity:.8">Trimax — Ruta de entregas</div>
            <div class="fw-bold" style="font-size:17px">{{ $ruta->motorizado->nombre }}</div>
            <div style="font-size:12px;opacity:.8">
                {{ $ruta->fecha->format('d/m/Y') }} · {{ $ruta->motorizado->sede }}
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 text-end">
            <div id="badge-ruta" class="bg-warning text-dark badge" style="font-size:12px">
                {{ ucfirst(str_replace('_',' ', $ruta->estado)) }}
            </div>
            <div style="font-size:12px;opacity:.8">
                <span id="cont-completadas">{{ $ruta->paradas->where('estado','completado')->count() }}</span>
                /{{ $ruta->paradas->count() }} entregas
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-between mt-2 mb-1">
        <div class="flex-grow-1 me-3 progress-bar-wrap">
            @php
                $pct = $ruta->paradas->count()
                    ? round($ruta->paradas->where('estado','completado')->count() / $ruta->paradas->count() * 100)
                    : 0;
            @endphp
            <div class="progress-bar-fill" id="barra-progreso" style="width:{{ $pct }}%"></div>
        </div>
        <button class="btn-navegar-ruta" onclick="navegarRutaCompleta()" id="btn-nav-ruta">
            <i class="mdi mdi-google-maps" style="font-size:16px"></i>
            Navegar ruta
        </button>
    </div>
</div>

<div class="px-3 py-3 container-fluid" style="max-width:600px;margin:0 auto">

    {{-- Resumen cuando se completa todo --}}
    <div class="resumen-final {{ $pct === 100 ? 'show' : '' }}" id="resumen-final">
        <i class="text-success mdi mdi-check-circle" style="font-size:56px"></i>
        <h4 class="mt-2 mb-1 fw-bold">¡Ruta completada!</h4>
        <p class="text-muted">Todas las entregas han sido registradas.</p>
    </div>

    @forelse($ruta->paradas as $parada)
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
        <div class="p-3 card-body">

            {{-- Número + info --}}
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="parada-numero" style="background:{{ $color }}" id="num-{{ $parada->id }}">
                    {{ $parada->orden_secuencia }}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size:16px">{{ $orden->cliente_nombre }}</div>
                    @if($orden->cliente_telefono)
                    <a href="tel:{{ $orden->cliente_telefono }}" class="d-block text-muted small">
                        <i class="me-1 mdi mdi-phone"></i>{{ $orden->cliente_telefono }}
                    </a>
                    @endif
                    @if($orden->referencia)
                    <div class="text-muted small"><i class="me-1 mdi mdi-tag"></i>{{ $orden->referencia }}</div>
                    @endif
                </div>
                <div id="badge-estado-{{ $parada->id }}">
                    @if($parada->estado === 'completado')
                        <span class="bg-success text-white estado-badge">
                            <i class="mdi mdi-check"></i> Entregado
                        </span>
                    @elseif($parada->estado === 'fallido')
                        <span class="bg-danger text-white estado-badge">
                            <i class="mdi mdi-close"></i> Fallido
                        </span>
                    @else
                        <span class="bg-light border text-secondary estado-badge">Pendiente</span>
                    @endif
                </div>
            </div>

            {{-- Dirección --}}
            <div class="d-flex align-items-start gap-2 mb-3">
                <a href="{{ $mapsUrl }}" target="_blank" class="d-flex flex-grow-1 align-items-start gap-1 direccion-link">
                    <i class="mt-1 mdi mdi-map-marker" style="flex-shrink:0"></i>
                    <span>{{ $orden->direccion }}</span>
                </a>
                <button class="btn-navegar-parada"
                        onclick="navegarParada({{ $orden->latitud ?? 'null' }}, {{ $orden->longitud ?? 'null' }}, '{{ addslashes($orden->direccion) }}')">
                    <i class="mdi mdi-navigation"></i> Navegar
                </button>
            </div>

            {{-- Hora entrega --}}
            @if($parada->hora_salida)
            <div class="mb-2 text-muted small">
                <i class="me-1 mdi mdi-clock-check"></i>
                Entregado a las {{ $parada->hora_salida->format('H:i') }}
            </div>
            @endif

            {{-- Acciones --}}
            <div id="acciones-{{ $parada->id }}"
                 class="{{ in_array($parada->estado, ['completado','fallido']) ? 'd-none' : '' }}">

                <div class="mb-2" id="notas-wrap-{{ $parada->id }}" style="display:none">
                    <textarea id="notas-{{ $parada->id }}" class="form-control form-control-sm" rows="2"
                              placeholder="Motivo / observación…"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-entregar"
                            onclick="marcar({{ $parada->id }}, 'completado', '{{ $token }}')">
                        <i class="me-1 mdi mdi-check-bold"></i>Entregado
                    </button>
                    <button class="btn-outline-danger btn btn-fallido"
                            style="width:auto;padding:12px 14px"
                            onclick="toggleNotas({{ $parada->id }})">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                </div>

                <div id="btn-fallido-wrap-{{ $parada->id }}" style="display:none" class="mt-2">
                    <button class="btn-outline-danger btn btn-fallido"
                            onclick="marcar({{ $parada->id }}, 'fallido', '{{ $token }}')">
                        <i class="me-1 mdi mdi-close-circle"></i>No entregado / Nadie en casa
                    </button>
                </div>
            </div>

        </div>
    </div>
    @empty
        <div class="py-5 text-muted text-center">
            <i class="d-block mb-2 mdi mdi-package-variant mdi-48px"></i>
            Sin paradas para esta ruta
        </div>
    @endforelse

</div>

{{-- Spinner --}}
<div class="spinner-overlay" id="spinner">
    <div class="text-white text-center">
        <div class="mb-2 spinner-border" style="width:2.5rem;height:2.5rem"></div>
        <div class="fw-semibold">Guardando…</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── Datos del controller (tu variable existente) ───────── */
const PARADAS_NAV = @json($paradasNav);
const COLORES     = { completado:'#28a745', fallido:'#dc3545', en_camino:'#ffc107', pendiente:'#1a73e8' };
const RUTA_COLOR  = '#e74c3c';

/* ── Mapa Leaflet ───────────────────────────────────────── */
const map = L.map('mapa-entrega', { zoomControl: false, attributionControl: false })
    .setView([-12.046374, -77.042793], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

const bounds = [];
const pendientes = PARADAS_NAV.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
const destinoId  = pendientes.length > 0 ? pendientes[0].id : null;

PARADAS_NAV.forEach((p, idx) => {
    if (!p.lat || !p.lng) return;

    const c        = COLORES[p.estado] || '#1a73e8';
    const esDestino = p.id === destinoId;
    const label    = p.estado === 'completado' ? '✓' : p.estado === 'fallido' ? '✗' : (idx + 1);

    let icon;
    if (esDestino) {
        // Pin verde grande — próxima entrega
        icon = L.divIcon({
            className: '',
            html: `<div style="width:44px;height:44px;background:#28a745;
                        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                        border:3px solid #fff;box-shadow:0 5px 18px rgba(40,167,69,.55);
                        display:flex;align-items:center;justify-content:center;">
                       <span style="transform:rotate(45deg);color:#fff;font-size:18px">📦</span>
                   </div>`,
            iconSize: [44,44], iconAnchor: [22,44], popupAnchor: [0,-48],
        });
    } else {
        icon = L.divIcon({
            className: '',
            html: `<div style="width:32px;height:32px;background:${c};
                        border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                        border:2.5px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.3);
                        display:flex;align-items:center;justify-content:center;">
                       <span style="transform:rotate(45deg);color:#fff;font-weight:700;font-size:12px">${label}</span>
                   </div>`,
            iconSize: [32,32], iconAnchor: [16,32], popupAnchor: [0,-36],
        });
    }

    L.marker([p.lat, p.lng], { icon })
        .bindPopup(`<strong>${idx+1}. ${p.cliente}</strong><br><small>${p.direccion}</small>`)
        .addTo(map);

    bounds.push([p.lat, p.lng]);
});

// Línea roja gruesa entre paradas
if (bounds.length > 1) {
    // Sombra
    L.polyline(bounds, { color:'rgba(0,0,0,0.15)', weight:9, lineJoin:'round', lineCap:'round' })
        .addTo(map).bringToBack();
    // Línea roja principal
    L.polyline(bounds, { color: RUTA_COLOR, weight: 5, opacity: 0.9, lineJoin:'round', lineCap:'round' })
        .addTo(map);
    map.fitBounds(bounds, { padding: [30, 30] });
} else if (bounds.length === 1) {
    map.setView(bounds[0], 14);
}

/* ── Toggle mapa (ocultar/mostrar) ─────────────────────── */
let mapaVisible = true;
function toggleMapa() {
    const wrapper = document.getElementById('mapa-wrapper');
    const icon    = document.getElementById('mapa-toggle-icon');
    const btn     = icon.closest('button');

    mapaVisible = !mapaVisible;
    document.getElementById('mapa-entrega').style.height = mapaVisible ? '220px' : '0';
    icon.className = mapaVisible ? 'mdi mdi-chevron-up me-1' : 'mdi mdi-chevron-down me-1';
    btn.innerHTML  = (mapaVisible ? '<i class="me-1 mdi mdi-chevron-up"></i>Ocultar mapa'
                                  : '<i class="me-1 mdi mdi-chevron-down"></i>Ver mapa');

    // Leaflet necesita invalidateSize cuando el contenedor cambia
    if (mapaVisible) setTimeout(() => map.invalidateSize(), 10);
}

/* ── Lógica original (sin cambios) ─────────────────────── */
const totalParadas = {{ $ruta->paradas->count() }};
let completadas    = {{ $ruta->paradas->where('estado','completado')->count() }};

function toggleNotas(paradaId) {
    const wrap    = document.getElementById(`notas-wrap-${paradaId}`);
    const btnFall = document.getElementById(`btn-fallido-wrap-${paradaId}`);
    const show    = wrap.style.display === 'none';
    wrap.style.display    = show ? 'block' : 'none';
    btnFall.style.display = show ? 'block' : 'none';
}

async function marcar(paradaId, estado, token) {
    const spinner = document.getElementById('spinner');
    spinner.classList.add('active');

    const notas = document.getElementById(`notas-${paradaId}`)?.value || '';
    const csrf  = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res = await fetch(`/entrega/${token}/parada/${paradaId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
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
            badge.innerHTML = `<span class="bg-success text-white estado-badge"><i class="mdi mdi-check"></i> Entregado</span>`;
        } else {
            badge.innerHTML = `<span class="bg-danger text-white estado-badge"><i class="mdi mdi-close"></i> Fallido</span>`;
        }

        const pct = Math.round(completadas / totalParadas * 100);
        document.getElementById('barra-progreso').style.width = pct + '%';
        document.getElementById('cont-completadas').textContent = completadas;

        if (data.ruta_estado === 'completado') {
            document.getElementById('badge-ruta').className   = 'badge bg-success';
            document.getElementById('badge-ruta').textContent = 'Completada';
            document.getElementById('resumen-final').classList.add('show');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (data.ruta_estado === 'en_ruta') {
            document.getElementById('badge-ruta').className   = 'badge bg-warning text-dark';
            document.getElementById('badge-ruta').textContent = 'En ruta';
        }

        if (navigator.vibrate) navigator.vibrate(estado === 'completado' ? [50,30,50] : [100]);

    } catch {
        alert('Error al guardar. Verifica tu conexión.');
    } finally {
        spinner.classList.remove('active');
    }
}

function navegarParada(lat, lng, direccion) {
    let url;
    if (lat && lng) {
        url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&travelmode=driving`;
    } else {
        url = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(direccion)}&travelmode=driving`;
    }
    window.open(url, '_blank');
}

function navegarRutaCompleta() {
    const pendientesNav = PARADAS_NAV.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
    if (!pendientesNav.length) { alert('No hay paradas pendientes.'); return; }

    const destino = pendientesNav[pendientesNav.length - 1];
    const destStr = (destino.lat && destino.lng)
        ? `${destino.lat},${destino.lng}`
        : encodeURIComponent(destino.direccion);

    const waypoints = pendientesNav.slice(0, -1)
        .filter(p => p.lat && p.lng)
        .map(p => `${p.lat},${p.lng}`)
        .join('|');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                const origin = `${pos.coords.latitude},${pos.coords.longitude}`;
                let url = `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destStr}&travelmode=driving`;
                if (waypoints) url += `&waypoints=${waypoints}`;
                window.open(url, '_blank');
            },
            () => {
                let url = `https://www.google.com/maps/dir/?api=1&destination=${destStr}&travelmode=driving`;
                if (waypoints) url += `&waypoints=${waypoints}`;
                window.open(url, '_blank');
            },
            { timeout: 5000 }
        );
    } else {
        let url = `https://www.google.com/maps/dir/?api=1&destination=${destStr}&travelmode=driving`;
        if (waypoints) url += `&waypoints=${waypoints}`;
        window.open(url, '_blank');
    }
}
</script>
</body>
</html>