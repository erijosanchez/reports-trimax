

<?php $__env->startSection('title', 'Mapa en Vivo — Motorizados'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: calc(100vh - 220px);
            min-height: 500px;
            border-radius: 12px;
        }

        #panel-lateral {
            max-height: calc(100vh - 280px);
            overflow-y: auto;
        }

        /* Moto pulsante */
        .moto-wrapper {
            position: relative;
            width: 48px;
            height: 48px;
        }

        .moto-ring {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            animation: pulse 2s ease-out infinite;
            pointer-events: none;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.7);
                opacity: 0.8;
            }

            70% {
                transform: scale(1.6);
                opacity: 0;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        .moto-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .45);
            position: relative;
            z-index: 1;
        }

        /* Cards panel */
        .moto-card {
            border-left: 4px solid #dee2e6;
            cursor: pointer;
            transition: all .18s;
            border-radius: 8px;
        }

        .moto-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .18) !important;
            transform: translateX(2px);
        }

        /* Polyline colores velocidad */
        .leyenda-line {
            display: inline-block;
            width: 24px;
            height: 4px;
            border-radius: 2px;
            margin-right: 6px;
            vertical-align: middle;
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
                                </h4>
                                <p class="mb-0 text-muted small">Actualiza cada 10s · posiciones GPS reales</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select id="filtro-sede" class="form-select-sm form-select" style="min-width:150px">
                                    <option value="">Todas las sedes</option>
                                    <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button id="btn-refresh" class="btn-outline-primary btn btn-sm">
                                    <i class="me-1 mdi mdi-refresh"></i>Actualizar
                                </button>
                                <span id="badge-estado" class="bg-info badge">Cargando…</span>
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
                        <div class="d-flex align-items-center justify-content-between bg-dark text-white card-header">
                            <span><i class="me-1 mdi mdi-motorbike"></i>En ruta</span>
                            <span id="contador" class="bg-warning text-dark badge">0</span>
                        </div>
                        <div class="p-2 card-body" id="panel-lateral">
                            <div id="lista-motorizados" class="py-4 text-muted text-center small">
                                <div class="me-1 spinner-border spinner-border-sm"></div>Cargando…
                            </div>
                        </div>
                    </div>

                    
                    <div class="shadow-sm border-0 card">
                        <div class="px-3 py-2 card-body">
                            <p class="mb-2 fw-semibold small">Velocidad</p>
                            <div class="small">
                                <div class="mb-1"><span class="leyenda-line" style="background:#3498db"></span>
                                    < 30 km/h</div>
                                        <div class="mb-1"><span class="leyenda-line"
                                                style="background:#f39c12"></span>30–60 km/h</div>
                                        <div><span class="leyenda-line" style="background:#e74c3c"></span>> 60 km/h</div>
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
            const SEDE_COLORS = {
                'LIMA': '#3498db',
                'ICA': '#e74c3c',
                'AREQUIPA': '#2ecc71',
                'TRUJILLO': '#f39c12',
                'CHICLAYO': '#9b59b6',
                'PIURA': '#1abc9c',
            };
            const DEFAULT_COLOR = '#7f8c8d';

            // ── Mapa ──────────────────────────────────────────────────
            const map = L.map('map').setView([-12.046374, -77.042793], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const markers = {}; // motorizado_id → marker
            const rutaLayers = {}; // motorizado_id → polyline layers
            let allData = [];
            let filtroSede = '';

            // ── Ícono moto pulsante ───────────────────────────────────
            function iconoMoto(sede) {
                const c = SEDE_COLORS[sede] || DEFAULT_COLOR;
                return L.divIcon({
                    className: '',
                    html: `<div class="moto-wrapper">
                                <div class="moto-ring" style="background:${c}"></div>
                                <div class="moto-icon" style="background:${c}">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                        <path d="M19 7c0-1.1-.9-2-2-2h-3l2 4H4l-2 3h1.3c.4-1.2 1.5-2 2.7-2s2.3.8 2.7 2h6.6c.4-1.2 1.5-2 2.7-2s2.3.8 2.7 2H22l-1-3h-2V7zM6 16.5c0 .8-.7 1.5-1.5 1.5S3 17.3 3 16.5 3.7 15 4.5 15s1.5.7 1.5 1.5zm13 0c0 .8-.7 1.5-1.5 1.5s-1.5-.7-1.5-1.5.7-1.5 1.5-1.5 1.5.7 1.5 1.5z"/>
                                    </svg>
                                </div>
                            </div>`,
                    iconSize: [48, 48],
                    iconAnchor: [24, 24],
                    popupAnchor: [0, -28],
                });
            }

            // ── Popup motorizado ──────────────────────────────────────
            function popupMoto(m) {
                return `<div style="min-width:180px;font-size:12px">
        <strong style="font-size:14px">${m.nombre}</strong><br>
        <span class="text-muted">${m.sede}</span>
        <hr style="margin:6px 0">
        <div>🏎 <b>${m.velocidad} km/h</b></div>
        <div>📍 ${m.distance_km} km hoy</div>
        <div style="color:#999;font-size:11px">⏱ ${m.actualizado || '--'}</div>
    </div>`;
            }

            // ── Actualizar marcadores ─────────────────────────────────
            function actualizarMarcadores(data) {
                const lista = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
                const idsAct = new Set(lista.map(m => m.id));

                // Quitar los que ya no están
                Object.keys(markers).forEach(id => {
                    if (!idsAct.has(Number(id))) {
                        map.removeLayer(markers[id]);
                        delete markers[id];
                    }
                });

                lista.forEach(m => {
                    if (!m.latitud || !m.longitud) return;
                    const popup = popupMoto(m);

                    if (markers[m.id]) {
                        markers[m.id].setLatLng([m.latitud, m.longitud]).setPopupContent(popup);
                    } else {
                        markers[m.id] = L.marker([m.latitud, m.longitud], {
                                icon: iconoMoto(m.sede)
                            })
                            .bindPopup(popup, {
                                maxWidth: 220
                            })
                            .addTo(map);
                    }
                });
            }

            // ── Panel lateral ─────────────────────────────────────────
            function renderPanel(data) {
                const lista = filtroSede ? data.filter(d => d.sede === filtroSede) : data;
                document.getElementById('contador').textContent = lista.length;

                if (!lista.length) {
                    document.getElementById('lista-motorizados').innerHTML =
                        '<p class="py-3 text-muted text-center small">Sin motorizados activos</p>';
                    return;
                }

                document.getElementById('lista-motorizados').innerHTML = lista.map(m => {
                    const c = SEDE_COLORS[m.sede] || DEFAULT_COLOR;
                    const vel = m.velocidad || 0;
                    const vc = vel > 60 ? '#e74c3c' : vel > 30 ? '#f39c12' : '#27ae60';
                    return `
        <div class="shadow-sm mb-2 p-2 border-0 card moto-card"
             style="border-left-color:${c}!important"
             onclick="irA(${m.id})">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:10px;height:10px;border-radius:50%;background:${c}"></div>
                    <strong class="small">${m.nombre}</strong>
                </div>
                <span class="badge" style="background:${vc};font-size:10px">${vel} km/h</span>
            </div>
            <div class="mt-1 text-muted" style="font-size:10px;padding-left:18px">
                ${m.sede} · ${m.distance_km} km hoy · ${m.actualizado || '--'}
            </div>
        </div>`;
                }).join('');
            }

            function irA(id) {
                const m = allData.find(x => x.id === id);
                if (m?.latitud) {
                    map.setView([m.latitud, m.longitud], 16);
                    markers[id]?.openPopup();
                }
            }

            // ── Cargar todo ───────────────────────────────────────────
            async function cargarTodo() {
                try {
                    const res = await fetch('/api/admin/mapa-vivo', {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    allData = await res.json();

                    actualizarMarcadores(allData);
                    renderPanel(allData);

                    const badge = document.getElementById('badge-estado');
                    badge.className = 'badge bg-success';
                    badge.textContent =
                        `En vivo · ${new Date().toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'})}`;
                } catch {
                    const badge = document.getElementById('badge-estado');
                    badge.className = 'badge bg-danger';
                    badge.textContent = 'Error de conexión';
                }
            }

            // ── Eventos ───────────────────────────────────────────────
            document.getElementById('filtro-sede').addEventListener('change', function() {
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
            setInterval(cargarTodo, 10000);
        </script>
    <?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/mapa-vivo.blade.php ENDPATH**/ ?>