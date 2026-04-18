<?php $__env->startSection('title', 'Mapa en Vivo — Motorizados'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ── Layout ── */
    #map { height: calc(100vh - 220px); min-height: 460px; width: 100%; border-radius: 12px; }
    #panel-lateral { max-height: calc(100vh - 280px); min-height: 200px; overflow-y: auto; }

    /* ── Moto pulsante ── */
    .moto-pulse-wrapper {
        position: relative;
        width: 48px; height: 48px;
    }
    .moto-pulse-ring {
        position: absolute; inset: -8px;
        border-radius: 50%;
        animation: moto-pulse 2s ease-out infinite;
        pointer-events: none;
    }
    @keyframes moto-pulse {
        0%   { transform: scale(0.7); opacity: 0.8; }
        70%  { transform: scale(1.6); opacity: 0;   }
        100% { transform: scale(1.6); opacity: 0;   }
    }

    /* ── Moto icon ── */
    .moto-icon {
        width: 48px; height: 48px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,.45);
        position: relative; z-index: 1;
        font-size: 20px;
    }

    /* ── Parada pin (estilo app de delivery) ── */
    .parada-pin {
        display: flex; flex-direction: column; align-items: center;
    }
    .parada-pin-bubble {
        width: 36px; height: 36px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 3px 10px rgba(0,0,0,.4);
        border: 3px solid #fff;
    }
    .parada-pin-bubble span {
        transform: rotate(45deg);
        color: #fff;
        font-weight: 700;
        font-size: 12px;
        line-height: 1;
    }

    /* ── Panel lateral cards ── */
    .moto-card {
        border-left: 4px solid #dee2e6;
        cursor: pointer;
        transition: all .18s;
        border-radius: 8px;
    }
    .moto-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,.18) !important;
        transform: translateX(2px);
    }
    .moto-card.activa { box-shadow: 0 0 0 2px currentColor; }

    /* ── Badge estado ── */
    #badge-estado { font-size: 11px; padding: 4px 10px; border-radius: 20px; }

    /* ── Leyenda ── */
    .legend-dot    { display:inline-block; width:11px; height:11px; border-radius:50%;   margin-right:5px; }
    .legend-parada { display:inline-block; width:11px; height:11px; border-radius:2px;   margin-right:5px; }

    /* ── Popup ── */
    .leaflet-popup-content-wrapper { border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,.25); }
    .popup-moto h6 { margin:0 0 4px; font-size:13px; font-weight:700; }
    .popup-moto small { color:#666; font-size:11px; }
    .popup-moto hr { margin:6px 0; }

    @media (max-width: 991px) {
        #map { height: 360px; }
        #panel-lateral { max-height: 280px; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="me-2 text-danger mdi mdi-map-marker-radius"></i>Mapa en Vivo
                                <span class="text-muted fw-normal" style="font-size:15px">· Motorizados</span>
                            </h4>
                            <p class="mb-0 text-muted small">Posiciones en tiempo real · actualiza cada 30 s</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select id="filtro-sede" class="form-select-sm form-select" style="min-width:150px">
                                <option value="">Todas las sedes</option>
                                <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button id="btn-refresh" class="btn-outline-primary btn btn-sm">
                                <i class="me-1 mdi mdi-refresh"></i>Actualizar
                            </button>
                            <span id="badge-estado" class="bg-secondary badge">Cargando…</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="row">

            
            <div class="mb-4 col-lg-9 col-md-12">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="p-2 card-body">
                        <div id="map"></div>
                    </div>
                </div>
            </div>

            
            <div class="mb-4 col-lg-3 col-md-12">
                <div class="shadow-sm mb-3 border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-dark py-2 text-white card-header">
                        <span><i class="me-1 mdi mdi-motorbike"></i>Activos</span>
                        <span id="contador" class="bg-warning text-dark badge">0</span>
                    </div>
                    <div class="p-2 card-body" id="panel-lateral">
                        <div id="lista-motorizados">
                            <div class="py-4 text-muted text-center small">
                                <div class="me-1 spinner-border spinner-border-sm"></div> Cargando…
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="shadow-sm border-0 card">
                    <div class="px-3 py-2 card-body">
                        <p class="mb-2 fw-semibold small">Leyenda</p>
                        <div id="leyenda-sedes" class="mb-3 small"></div>
                        <p class="mb-1 fw-semibold small" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#999">Paradas</p>
                        <div class="small">
                            <div class="mb-1"><span class="legend-parada" style="background:#f39c12"></span>Pendiente</div>
                            <div class="mb-1"><span class="legend-parada" style="background:#27ae60"></span>Entregado</div>
                            <div class="mb-1"><span class="legend-parada" style="background:#e74c3c"></span>Fallido</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ─── Paleta ───────────────────────────────────────────── */
const SEDE_COLORS = {
    'ICA':      '#e74c3c',
    'LIMA':     '#3498db',
    'AREQUIPA': '#2ecc71',
    'TRUJILLO': '#f39c12',
    'CHICLAYO': '#9b59b6',
    'PIURA':    '#1abc9c',
};
const PARADA_COLORS = {
    pendiente:  '#f39c12',
    en_camino:  '#e67e22',
    completado: '#27ae60',
    fallido:    '#e74c3c',
};
const DEFAULT_COLOR = '#7f8c8d';
const RUTA_COLOR    = '#e74c3c';   // línea roja como en la imagen

/* ─── Mapa ─────────────────────────────────────────────── */
const map = L.map('map', { zoomControl: true }).setView([-12.046374, -77.042793], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19,
}).addTo(map);

const markers       = {};   // motorizado_id → L.Marker
const paradaMarkers = {};   // motorizado_id → [L.Marker]
const rutaLines     = {};   // motorizado_id → [L.Polyline]
let allData = [], filtroSede = '';

/* ─── Helpers color ────────────────────────────────────── */
function colorSede(sede) {
    return SEDE_COLORS[(sede || '').toUpperCase()] || DEFAULT_COLOR;
}

/* ─── Ícono motorizado con pulso ───────────────────────── */
function crearIconoMoto(sede) {
    const c = colorSede(sede);
    return L.divIcon({
        className: '',
        html: `
        <div class="moto-pulse-wrapper">
            <div class="moto-pulse-ring" style="background:${c}"></div>
            <div class="moto-icon" style="background:${c}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
                    <path d="M5 11l1.5-4.5h7L15 9h3a3 3 0 0 1 3 3v1h-2a3 3 0 0 1-6 0H9a3 3 0 0 1-6 0H1v-2h4zm2.5 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm9 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>
                </svg>
            </div>
        </div>`,
        iconSize:    [48, 48],
        iconAnchor:  [24, 24],
        popupAnchor: [0, -28],
    });
}

/* ─── Pin de parada estilo app delivery ─────────────────── */
function crearPinParada(parada) {
    const c     = PARADA_COLORS[parada.estado] || PARADA_COLORS.pendiente;
    const label = parada.estado === 'completado' ? '✓'
                : parada.estado === 'fallido'    ? '✗'
                : parada.secuencia;

    // Pin verde grande para el destino principal (último pendiente)
    const esDestino = parada._esDestino;
    if (esDestino) {
        return L.divIcon({
            className: '',
            html: `
            <div style="display:flex;flex-direction:column;align-items:center">
                <div style="
                    width:46px;height:46px;
                    background:#27ae60;
                    border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);
                    border:3px solid #fff;
                    box-shadow:0 4px 14px rgba(0,0,0,.4);
                    display:flex;align-items:center;justify-content:center;">
                    <span style="transform:rotate(45deg);color:#fff;font-size:18px">📦</span>
                </div>
            </div>`,
            iconSize:    [46, 46],
            iconAnchor:  [23, 46],
            popupAnchor: [0, -50],
        });
    }

    return L.divIcon({
        className: '',
        html: `
        <div style="display:flex;flex-direction:column;align-items:center">
            <div style="
                width:32px;height:32px;
                background:${c};
                border-radius:50% 50% 50% 0;
                transform:rotate(-45deg);
                border:2.5px solid #fff;
                box-shadow:0 3px 10px rgba(0,0,0,.35);
                display:flex;align-items:center;justify-content:center;">
                <span style="transform:rotate(45deg);color:#fff;font-weight:700;font-size:12px">${label}</span>
            </div>
        </div>`,
        iconSize:    [32, 32],
        iconAnchor:  [16, 32],
        popupAnchor: [0, -36],
    });
}

/* ─── Leyenda de sedes ─────────────────────────────────── */
function renderLeyenda(data) {
    const sedes = [...new Set(data.map(d => d.sede))].sort();
    document.getElementById('leyenda-sedes').innerHTML = sedes.length
        ? sedes.map(s =>
            `<div class="mb-1">
                <span class="legend-dot" style="background:${colorSede(s)}"></span>
                <span>${s}</span>
            </div>`).join('')
        : '<span class="text-muted">Sin datos</span>';
}

/* ─── Panel lateral ────────────────────────────────────── */
function renderPanel(data) {
    const lista = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
    document.getElementById('contador').textContent = lista.length;

    if (!lista.length) {
        document.getElementById('lista-motorizados').innerHTML =
            '<p class="py-3 text-muted text-center small">Sin motorizados activos</p>';
        return;
    }

    document.getElementById('lista-motorizados').innerHTML = lista.map(m => {
        const c   = colorSede(m.sede);
        const vel = m.velocidad ?? 0;
        const ts  = m.ultima_actualizacion
            ? new Date(m.ultima_actualizacion).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
            : '--:--';
        const velColor = vel > 60 ? '#e74c3c' : vel > 30 ? '#f39c12' : '#27ae60';
        return `
        <div class="shadow-sm mb-2 p-2 border-0 card moto-card"
             style="border-left-color:${c}!important"
             onclick="irAMotorizado(${m.id})">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:10px;height:10px;border-radius:50%;background:${c};flex-shrink:0"></div>
                    <strong class="small">${m.nombre}</strong>
                </div>
                <span class="badge" style="background:${velColor};font-size:10px">${vel} km/h</span>
            </div>
            <div class="mt-1 text-muted" style="font-size:10px;padding-left:18px">
                ${m.sede} · ${ts}
            </div>
        </div>`;
    }).join('');
}

/* ─── Marcadores de motorizados ────────────────────────── */
function actualizarMarcadores(data) {
    const lista  = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
    const idsAct = new Set(lista.map(m => m.id));

    Object.keys(markers).forEach(id => {
        if (!idsAct.has(Number(id))) { map.removeLayer(markers[id]); delete markers[id]; }
    });

    lista.forEach(m => {
        if (!m.latitud || !m.longitud) return;

        const popup = `
        <div class="popup-moto" style="min-width:180px">
            <h6>${m.nombre}</h6>
            <small>${m.sede}</small>
            <hr>
            <div style="font-size:12px">
                🏎 <b>${m.velocidad ?? 0} km/h</b><br>
                <span style="color:#999;font-size:11px">${m.ultima_actualizacion
                    ? new Date(m.ultima_actualizacion).toLocaleTimeString('es-PE') : '--'}</span>
            </div>
            <div style="margin-top:8px;display:flex;flex-direction:column;gap:3px">
                <a href="/tracking/historial?motorizado_id=${m.id}" style="font-size:11px;color:#3498db">📍 Historial recorrido</a>
                <a href="/tracking/rutas" style="font-size:11px;color:#3498db">🗺 Ver rutas</a>
            </div>
        </div>`;

        if (markers[m.id]) {
            markers[m.id].setLatLng([m.latitud, m.longitud]).setPopupContent(popup);
        } else {
            markers[m.id] = L.marker([m.latitud, m.longitud], { icon: crearIconoMoto(m.sede) })
                .bindPopup(popup, { maxWidth: 220 })
                .addTo(map);
        }
    });
}

/* ─── Rutas activas ────────────────────────────────────── */
function limpiarRuta(motoId) {
    (paradaMarkers[motoId] || []).forEach(mk => map.removeLayer(mk));
    paradaMarkers[motoId] = [];
    (rutaLines[motoId] || []).forEach(l => map.removeLayer(l));
    rutaLines[motoId] = [];
}

function dibujarRutas(rutasActivas, motoData) {
    const motoIdsConRuta = new Set(rutasActivas.map(r => r.motorizado_id));
    Object.keys(paradaMarkers).forEach(id => {
        if (!motoIdsConRuta.has(Number(id))) limpiarRuta(Number(id));
    });

    rutasActivas.forEach(ruta => {
        const motoId = ruta.motorizado_id;
        const moto   = motoData.find(m => m.id === motoId);
        limpiarRuta(motoId);
        paradaMarkers[motoId] = [];
        rutaLines[motoId]     = [];

        // Identificar el próximo destino (primer pendiente)
        const pendientes = ruta.paradas.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
        const destinoIdx = pendientes.length > 0 ? pendientes[0].secuencia : null;

        // Dibujar marcadores de paradas
        ruta.paradas.forEach(p => {
            const esDestino = p.secuencia === destinoIdx;
            const mk = L.marker([p.lat, p.lng], { icon: crearPinParada({ ...p, _esDestino: esDestino }) })
                .bindPopup(`
                <div style="min-width:160px;font-size:12px">
                    <strong>${p.secuencia}. ${p.cliente}</strong><br>
                    <span style="color:#666;font-size:11px">${p.direccion}</span><br>
                    <span style="color:${PARADA_COLORS[p.estado]};font-weight:600;font-size:11px;text-transform:capitalize">${p.estado}</span>
                </div>`)
                .addTo(map);
            paradaMarkers[motoId].push(mk);
        });

        // Línea roja gruesa: moto → paradas pendientes
        if (pendientes.length && moto?.latitud && moto?.longitud) {
            const puntos = [
                [moto.latitud, moto.longitud],
                ...pendientes.map(p => [p.lat, p.lng]),
            ];

            // Línea principal roja gruesa (igual a la imagen)
            const linea = L.polyline(puntos, {
                color:   RUTA_COLOR,
                weight:  5,
                opacity: 0.9,
                lineJoin: 'round',
                lineCap:  'round',
            }).addTo(map);
            rutaLines[motoId].push(linea);

            // Borde sutil más grueso atrás para efecto de profundidad
            const lineaSombra = L.polyline(puntos, {
                color:   'rgba(0,0,0,0.15)',
                weight:  8,
                opacity: 1,
                lineJoin: 'round',
                lineCap:  'round',
            });
            lineaSombra.addTo(map);
            lineaSombra.bringToBack();
            rutaLines[motoId].push(lineaSombra);
        }
    });
}

/* ─── Ir a motorizado ──────────────────────────────────── */
function irAMotorizado(id) {
    const m = allData.find(x => x.id === id);
    if (m?.latitud) {
        map.setView([m.latitud, m.longitud], 15);
        markers[id]?.openPopup();
    }
}

/* ─── Carga principal ──────────────────────────────────── */
async function cargarTodo() {
    try {
        const [resUbi, resRutas] = await Promise.all([
            fetch('/tracking/api/ubicaciones'),
            fetch('/tracking/api/rutas-activas'),
        ]);
        if (!resUbi.ok) throw new Error('ubicaciones error');

        allData = await resUbi.json();
        const rutasActivas = resRutas.ok ? await resRutas.json() : [];

        actualizarMarcadores(allData);
        dibujarRutas(rutasActivas, allData);
        renderPanel(allData);
        renderLeyenda(allData);

        const badge = document.getElementById('badge-estado');
        badge.className = 'badge bg-success';
        badge.textContent = `En vivo · ${new Date().toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'})}`;
    } catch (e) {
        const badge = document.getElementById('badge-estado');
        badge.className = 'badge bg-danger';
        badge.textContent = 'Error de conexión';
    }
}

/* ─── Eventos ──────────────────────────────────────────── */
document.getElementById('filtro-sede').addEventListener('change', function () {
    filtroSede = this.value;
    actualizarMarcadores(allData);
    renderPanel(allData);
});

document.getElementById('btn-refresh').addEventListener('click', () => {
    const btn = document.getElementById('btn-refresh');
    btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>';
    cargarTodo().finally(() => {
        btn.innerHTML = '<i class="me-1 mdi mdi-refresh"></i>Actualizar';
    });
});

cargarTodo();
setInterval(cargarTodo, 30000);
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/mapa.blade.php ENDPATH**/ ?>