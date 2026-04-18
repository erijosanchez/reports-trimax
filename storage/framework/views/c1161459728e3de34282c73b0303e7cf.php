<?php $__env->startSection('title', 'Mapa en Vivo — Motorizados'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: calc(100vh - 240px); min-height: 420px; width: 100%; border-radius: 6px; }
    #panel-lateral { max-height: calc(100vh - 300px); min-height: 200px; overflow-y: auto; }
    .moto-card { border-left: 4px solid #dee2e6; cursor: pointer; transition: box-shadow .15s; }
    .moto-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.15) !important; }
    .legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 5px; }
    @media (max-width: 991px) {
        #map { height: 350px; }
        #panel-lateral { max-height: 300px; }
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
                                <i class="mdi mdi-map-marker-radius me-2 text-danger"></i>Mapa en Vivo — Motorizados
                            </h4>
                            <p class="mb-0 text-muted small">Posiciones en tiempo real · actualiza cada 30 segundos</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <select id="filtro-sede" class="form-select form-select-sm" style="min-width:140px">
                                <option value="">Todas las sedes</option>
                                <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button id="btn-refresh" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-refresh me-1"></i>Actualizar
                            </button>
                            <span id="badge-estado" class="badge bg-secondary">Cargando…</span>
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
                <div class="shadow-sm border-0 card mb-3">
                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                        <span><i class="mdi mdi-motorbike me-1"></i>Activos</span>
                        <span id="contador" class="badge bg-warning text-dark">0</span>
                    </div>
                    <div class="card-body p-2" id="panel-lateral">
                        <div id="lista-motorizados">
                            <div class="text-center text-muted small py-3">
                                <span class="spinner-border spinner-border-sm me-1"></span>Cargando…
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="shadow-sm border-0 card">
                    <div class="card-body py-2 px-3">
                        <p class="fw-semibold small mb-2">Leyenda por sede</p>
                        <div id="leyenda-sedes" class="small"></div>
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
const SEDE_COLORS = {
    'ICA':      '#e74c3c',
    'LIMA':     '#3498db',
    'AREQUIPA': '#2ecc71',
    'TRUJILLO': '#f39c12',
    'CHICLAYO': '#9b59b6',
    'PIURA':    '#1abc9c',
};
const DEFAULT_COLOR = '#95a5a6';

const map = L.map('map').setView([-12.046374, -77.042793], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(map);

const markers = {};
let allData = [], filtroSede = '';

function colorSede(sede) {
    return SEDE_COLORS[(sede || '').toUpperCase()] || DEFAULT_COLOR;
}

function crearIcono(sede) {
    const c = colorSede(sede);
    return L.divIcon({
        className: '',
        html: `<div style="width:28px;height:28px;border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);background:${c};border:3px solid #fff;
                    box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>`,
        iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -30],
    });
}

function renderLeyenda(data) {
    const sedes = [...new Set(data.map(d => d.sede))].sort();
    document.getElementById('leyenda-sedes').innerHTML = sedes.length
        ? sedes.map(s => `<div class="mb-1"><span class="legend-dot" style="background:${colorSede(s)}"></span>${s}</div>`).join('')
        : '<span class="text-muted">Sin datos</span>';
}

function renderPanel(data) {
    const lista = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
    document.getElementById('contador').textContent = lista.length;

    if (!lista.length) {
        document.getElementById('lista-motorizados').innerHTML =
            '<p class="text-muted small text-center py-3">Sin motorizados para mostrar</p>';
        return;
    }

    document.getElementById('lista-motorizados').innerHTML = lista.map(m => {
        const c   = colorSede(m.sede);
        const vel = m.velocidad ?? 0;
        const ts  = m.ultima_actualizacion
            ? new Date(m.ultima_actualizacion).toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'})
            : '--:--';
        return `
        <div class="card moto-card shadow-sm border-0 mb-2 p-2"
             style="border-left-color:${c}!important"
             onclick="irAMotorizado(${m.id})">
            <div class="d-flex justify-content-between align-items-start">
                <strong class="small">${m.nombre}</strong>
                <span class="badge small" style="background:${c}">${vel} km/h</span>
            </div>
            <div class="text-muted" style="font-size:11px">${m.sede} · ${ts}</div>
        </div>`;
    }).join('');
}

function actualizarMarcadores(data) {
    const lista    = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
    const idsAct   = new Set(lista.map(m => m.id));

    Object.keys(markers).forEach(id => {
        if (!idsAct.has(Number(id))) { map.removeLayer(markers[id]); delete markers[id]; }
    });

    lista.forEach(m => {
        if (!m.latitud || !m.longitud) return;
        const popup = `<div style="min-width:160px">
            <strong>${m.nombre}</strong><br>
            <span class="text-muted small">${m.sede}</span>
            <hr class="my-1">
            <span><i class="mdi mdi-speedometer"></i> <b>${m.velocidad ?? 0} km/h</b></span><br>
            <span style="font-size:11px">${m.ultima_actualizacion
                ? new Date(m.ultima_actualizacion).toLocaleTimeString('es-PE') : '--'}</span><br>
            <a href="/tracking/historial?motorizado_id=${m.id}" class="small d-block mt-1">
                <i class="mdi mdi-map-search"></i> Historial recorrido
            </a>
            <a href="/tracking/rutas" class="small d-block">
                <i class="mdi mdi-route"></i> Ver rutas
            </a>
        </div>`;

        if (markers[m.id]) {
            markers[m.id].setLatLng([m.latitud, m.longitud]).setPopupContent(popup);
        } else {
            markers[m.id] = L.marker([m.latitud, m.longitud], { icon: crearIcono(m.sede) })
                .bindPopup(popup).addTo(map);
        }
    });
}

function irAMotorizado(id) {
    const m = allData.find(x => x.id === id);
    if (m?.latitud) { map.setView([m.latitud, m.longitud], 15); markers[id]?.openPopup(); }
}

async function cargarUbicaciones() {
    try {
        const res = await fetch('/tracking/api/ubicaciones');
        if (!res.ok) throw new Error(res.status);
        allData = await res.json();
        actualizarMarcadores(allData);
        renderPanel(allData);
        renderLeyenda(allData);
        const badge = document.getElementById('badge-estado');
        badge.className = 'badge bg-success';
        badge.textContent = 'En vivo';
    } catch {
        const badge = document.getElementById('badge-estado');
        badge.className = 'badge bg-danger';
        badge.textContent = 'Error de conexión';
    }
}

document.getElementById('filtro-sede').addEventListener('change', function() {
    filtroSede = this.value;
    actualizarMarcadores(allData);
    renderPanel(allData);
});
document.getElementById('btn-refresh').addEventListener('click', cargarUbicaciones);

cargarUbicaciones();
setInterval(cargarUbicaciones, 30000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/mapa.blade.php ENDPATH**/ ?>