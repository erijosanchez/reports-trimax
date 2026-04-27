
<?php $__env->startSection('title', 'Historial de Recorrido'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: calc(100vh - 320px);
            min-height: 450px;
            border-radius: 10px;
        }

        .speed-badge {
            display: inline-block;
            width: 12px;
            height: 4px;
            border-radius: 2px;
            margin-right: 6px;
            vertical-align: middle;
        }

        @media(max-width:767px) {
            #map {
                height: 320px;
            }
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
                                    <i class="me-2 text-primary mdi mdi-map-search"></i>Historial de Recorrido
                                </h4>
                                <p class="mb-0 text-muted small">Ruta GPS dibujada con velocidad por tramo</p>
                            </div>
                            <a href="<?php echo route('tracking.historial'); ?>" class="btn-outline-secondary btn btn-sm">
                                <i class="mdi-arrow-left me-1 mdi"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="py-3 card-body">
                            <div class="align-items-end row g-2">
                                <div class="col-md-4">
                                    <label class="mb-1 form-label small fw-bold">Motorizado</label>
                                    <select id="sel-motorizado" class="form-select-sm form-select">
                                        <option value="">Seleccionar motorizado</option>
                                        <?php $__currentLoopData = $motorizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo $m->id; ?>"
                                                <?php echo request('motorizado_id') == $m->id ? 'selected' : ''; ?>>
                                                <?php echo $m->nombre; ?> — <?php echo $m->sede; ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="mb-1 form-label small fw-bold">Fecha</label>
                                    <input type="date" id="sel-fecha" class="form-control form-control-sm"
                                        value="<?php echo request('fecha', today()->toDateString()); ?>"
                                        max="<?php echo today()->toDateString(); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button id="btn-buscar" class="w-100 btn btn-sm btn-primary">
                                        <i class="me-1 mdi mdi-magnify"></i>Ver recorrido
                                    </button>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 col-md-3">
                                    <span id="badge-puntos" class="bg-secondary badge">Sin datos</span>
                                    <span id="badge-km" class="bg-primary badge d-none"></span>
                                    <span id="badge-vel" class="bg-warning text-dark badge d-none"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                
                <div class="mb-4 col-lg-8">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-header">
                            <h6 class="mb-0 fw-bold" id="titulo-mapa">
                                <i class="me-1 text-primary mdi mdi-map-marker-path"></i>
                                Selecciona un motorizado y fecha
                            </h6>
                            <div class="d-flex gap-3 text-muted small">
                                <span><span class="speed-badge" style="background:#3498db"></span>
                                    < 30 km/h</span>
                                        <span><span class="speed-badge" style="background:#f39c12"></span>30–60</span>
                                        <span><span class="speed-badge" style="background:#e74c3c"></span>> 60</span>
                            </div>
                        </div>
                        <div class="p-2 card-body">
                            <div id="map">
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted"
                                    style="min-height:450px">
                                    <div class="text-center">
                                        <i class="d-block opacity-50 mb-2 mdi mdi-map-search mdi-48px"></i>
                                        <p>Selecciona un motorizado y fecha para ver el recorrido</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 col-lg-4">

                    
                    <div class="shadow-sm mb-3 border-0 card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="me-1 text-success mdi mdi-chart-line"></i>Estadísticas
                            </h6>
                        </div>
                        <div class="p-3 card-body" id="panel-stats">
                            <p class="py-3 text-muted text-center small">Carga un recorrido para ver estadísticas</p>
                        </div>
                    </div>

                    
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="me-1 mdi-clock-outline text-warning mdi"></i>Puntos GPS
                            </h6>
                            <span id="total-puntos" class="bg-primary badge">0</span>
                        </div>
                        <div class="p-2 card-body" style="max-height:280px;overflow-y:auto" id="lista-puntos">
                            <p class="py-3 text-muted text-center small">Sin datos</p>
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
        let mapInstance = null;
        let capasRuta = [];
        let marcadores = [];

        function initMap() {
            if (mapInstance) return;
            document.getElementById('map').innerHTML = '';
            document.getElementById('map').style.minHeight = '450px';
            mapInstance = L.map('map').setView([-12.046374, -77.042793], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19,
            }).addTo(mapInstance);
        }

        function limpiar() {
            capasRuta.forEach(c => mapInstance.removeLayer(c));
            marcadores.forEach(m => mapInstance.removeLayer(m));
            capasRuta = [];
            marcadores = [];
        }

        function colorVelocidad(vel) {
            if (vel > 60) return '#e74c3c';
            if (vel > 30) return '#f39c12';
            return '#3498db';
        }

        function iconoMarcador(color, icono) {
            return L.divIcon({
                className: '',
                html: `<div style="width:28px;height:28px;border-radius:50%;background:${color};
                    color:#fff;display:flex;align-items:center;justify-content:center;
                    font-size:14px;border:3px solid #fff;
                    box-shadow:0 3px 10px rgba(0,0,0,.4)">${icono}</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
        }

        async function cargarRecorrido() {
            const motId = document.getElementById('sel-motorizado').value;
            const fecha = document.getElementById('sel-fecha').value;

            if (!motId) {
                alert('Selecciona un motorizado');
                return;
            }

            const btn = document.getElementById('btn-buscar');
            btn.disabled = true;
            btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Cargando…';

            try {
                initMap();
                limpiar();

                const res = await fetch(`/api/admin/recorrido?motorizado_id=${motId}&fecha=${fecha}`);
                const data = await res.json();
                const puntos = data.puntos || [];

                // Badges
                document.getElementById('badge-puntos').className = 'badge ' + (puntos.length ? 'bg-success' :
                    'bg-warning text-dark');
                document.getElementById('badge-puntos').textContent = puntos.length + ' puntos GPS';

                document.getElementById('titulo-mapa').innerHTML =
                    `<i class="me-1 text-primary mdi mdi-map-marker-path"></i>
             ${data.motorizado?.nombre} — ${fecha}`;

                if (!puntos.length) {
                    renderStats([], null, null);
                    renderListaPuntos([]);
                    return;
                }

                const coords = puntos.map(p => [p.lat, p.lng]);

                // Polyline coloreada por velocidad — 3 capas estilo app
                for (let i = 1; i < coords.length; i++) {
                    const vel = puntos[i].vel || 0;
                    const color = colorVelocidad(vel);

                    // Sombra
                    const sombra = L.polyline([coords[i - 1], coords[i]], {
                        color: 'rgba(0,0,0,0.15)',
                        weight: 9,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(mapInstance);

                    // Borde blanco
                    const borde = L.polyline([coords[i - 1], coords[i]], {
                        color: '#fff',
                        weight: 6,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(mapInstance);

                    // Línea principal
                    const linea = L.polyline([coords[i - 1], coords[i]], {
                        color,
                        weight: 4,
                        opacity: 0.9,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(mapInstance);

                    linea.bindTooltip(`${vel} km/h`, {
                        sticky: true
                    });
                    capasRuta.push(sombra, borde, linea);
                }

                // Marcador inicio
                const mInicio = L.marker(coords[0], {
                        icon: iconoMarcador('#2ecc71', '🚩')
                    })
                    .bindPopup(`<strong>Inicio</strong><br>${puntos[0].ts
                ? new Date(puntos[0].ts).toLocaleTimeString('es-PE') : ''}`)
                    .addTo(mapInstance);

                // Marcador fin
                const mFin = L.marker(coords.at(-1), {
                        icon: iconoMarcador('#e74c3c', '🏁')
                    })
                    .bindPopup(`<strong>Fin</strong><br>${puntos.at(-1).ts
                ? new Date(puntos.at(-1).ts).toLocaleTimeString('es-PE') : ''}`)
                    .addTo(mapInstance);

                marcadores.push(mInicio, mFin);
                mapInstance.fitBounds(coords, {
                    padding: [50, 50]
                });

                renderStats(puntos, data.motorizado?.nombre, fecha);
                renderListaPuntos(puntos);

                // Badges
                const km = (puntos.length > 1 ? calcularKm(puntos) : 0).toFixed(2);
                const vels = puntos.map(p => p.vel).filter(v => v > 0);
                const vMax = vels.length ? Math.max(...vels).toFixed(1) : 0;

                document.getElementById('badge-km').className = 'badge bg-primary';
                document.getElementById('badge-km').textContent = km + ' km';
                document.getElementById('badge-vel').className = 'badge bg-warning text-dark';
                document.getElementById('badge-vel').textContent = vMax + ' km/h máx';
                document.getElementById('badge-km').classList.remove('d-none');
                document.getElementById('badge-vel').classList.remove('d-none');

            } catch (e) {
                console.error(e);
                alert('Error al cargar el recorrido');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="me-1 mdi mdi-magnify"></i>Ver recorrido';
            }
        }

        function calcularKm(puntos) {
            let total = 0;
            for (let i = 1; i < puntos.length; i++) {
                const R = 6371;
                const dLat = (puntos[i].lat - puntos[i - 1].lat) * Math.PI / 180;
                const dLng = (puntos[i].lng - puntos[i - 1].lng) * Math.PI / 180;
                const a = Math.sin(dLat / 2) ** 2 +
                    Math.cos(puntos[i - 1].lat * Math.PI / 180) *
                    Math.cos(puntos[i].lat * Math.PI / 180) *
                    Math.sin(dLng / 2) ** 2;
                total += R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }
            return total;
        }

        function renderStats(puntos, nombre, fecha) {
            if (!puntos.length) {
                document.getElementById('panel-stats').innerHTML =
                    '<p class="py-3 text-muted text-center small">Sin datos GPS para esta fecha</p>';
                return;
            }

            const km = calcularKm(puntos).toFixed(2);
            const vels = puntos.map(p => p.vel).filter(v => v > 0);
            const vMax = vels.length ? Math.max(...vels).toFixed(1) : 0;
            const vProm = vels.length ?
                (vels.reduce((a, b) => a + b, 0) / vels.length).toFixed(1) : 0;
            const tIni = puntos[0].ts ?
                new Date(puntos[0].ts).toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '--';
            const tFin = puntos.at(-1).ts ?
                new Date(puntos.at(-1).ts).toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit'
                }) : '--';

            document.getElementById('panel-stats').innerHTML = `
    <div class="text-center row g-2">
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-primary fw-bold">${km} km</div>
                <div class="text-muted" style="font-size:11px">Distancia</div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-info fw-bold">${puntos.length}</div>
                <div class="text-muted" style="font-size:11px">Puntos GPS</div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-warning fw-bold">${vMax} km/h</div>
                <div class="text-muted" style="font-size:11px">Vel. máxima</div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-success fw-bold">${vProm} km/h</div>
                <div class="text-muted" style="font-size:11px">Vel. promedio</div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-secondary fw-bold">${tIni}</div>
                <div class="text-muted" style="font-size:11px">Inicio</div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light py-2 rounded">
                <div class="text-danger fw-bold">${tFin}</div>
                <div class="text-muted" style="font-size:11px">Fin</div>
            </div>
        </div>
    </div>`;
        }

        function renderListaPuntos(puntos) {
            document.getElementById('total-puntos').textContent = puntos.length;
            if (!puntos.length) {
                document.getElementById('lista-puntos').innerHTML =
                    '<p class="py-3 text-muted text-center small">Sin datos</p>';
                return;
            }

            const step = Math.max(1, Math.floor(puntos.length / 50));
            const items = puntos.filter((_, i) => i % step === 0);

            document.getElementById('lista-puntos').innerHTML = items.map(p => {
                const ts = p.ts ? new Date(p.ts).toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) : '--';
                const color = colorVelocidad(p.vel || 0);
                return `
        <div class="d-flex align-items-center justify-content-between py-1 border-bottom"
            style="font-size:11px;cursor:pointer"
            onclick="irAPunto(${p.lat},${p.lng})">
            <span class="text-muted">${ts}</span>
            <span class="badge" style="background:${color}">${p.vel} km/h</span>
        </div>`;
            }).join('');
        }

        function irAPunto(lat, lng) {
            if (mapInstance) mapInstance.setView([lat, lng], 17);
        }

        document.getElementById('btn-buscar').addEventListener('click', cargarRecorrido);

        // Autocargar si vienen params
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('motorizado_id')) {
            document.getElementById('sel-motorizado').value = urlParams.get('motorizado_id');
            if (urlParams.get('fecha')) document.getElementById('sel-fecha').value = urlParams.get('fecha');
            cargarRecorrido();
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/historial-recorrido.blade.php ENDPATH**/ ?>