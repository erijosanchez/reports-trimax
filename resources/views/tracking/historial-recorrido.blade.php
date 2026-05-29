@extends('layouts.app')
@section('title', 'Historial de Recorrido')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        #map {
            height: calc(100vh - 320px);
            min-height: 500px;
            border-radius: 14px;
            overflow: hidden;
        }

        @media(max-width:767px) {
            #map {
                height: 380px;
            }
        }

        .speed-badge {
            display: inline-block;
            width: 12px;
            height: 4px;
            border-radius: 10px;
            margin-right: 5px;
        }

        .gps-item {
            transition: .2s ease;
        }

        .gps-item:hover {
            background: #f7f9fc;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 14px;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">

        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h3 class="mb-1 fw-bold">
                                    👋 Bienvenido,
                                    {{ auth()->user()->name }}
                                </h3>
                                <p class="mb-0 text-muted">
                                    Usa el sistema responsablemente
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <input type="date" class="form-control" id="sel-fecha-top"
                                    value="{{ request('fecha', today()->toDateString()) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            {{-- filtros --}}
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="card-body">

                            <div class="align-items-end row g-2">

                                <div class="col-md-4">
                                    <select id="sel-motorizado" class="form-select">
                                        <option value="">Seleccionar motorizado</option>

                                        @foreach ($motorizados as $m)
                                            <option value="{{ $m->id }}">
                                                {{ $m->nombre }} — {{ $m->sede }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <input type="date" id="sel-fecha" class="form-control"
                                        value="{{ request('fecha', today()->toDateString()) }}">
                                </div>

                                <div class="col-md-2">
                                    <button id="btn-buscar" class="w-100 btn btn-primary">

                                        <i class="me-1 mdi mdi-magnify"></i>
                                        Ver recorrido
                                    </button>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 col-md-3">

                                    <span id="badge-puntos" class="bg-info badge">
                                        0 puntos
                                    </span>

                                    <span id="badge-km" class="bg-primary badge d-none">
                                    </span>

                                    <span id="badge-vel" class="bg-warning text-dark badge d-none">
                                    </span>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                {{-- mapa --}}
                <div class="mb-4 col-lg-8">

                    <div class="shadow-sm border-0 card">

                        <div class="d-flex align-items-center justify-content-between card-header">

                            <h5 class="mb-0 fw-bold" id="titulo-mapa">
                                <i class="me-1 text-primary mdi mdi-map-marker-path"></i>
                                Selecciona un motorizado
                            </h5>

                            <div class="d-flex gap-3 text-muted small">

                                <span>
                                    <span class="bg-primary speed-badge"></span>
                                    < 30 km/h </span>

                                        <span>
                                            <span class="bg-warning speed-badge"></span>
                                            30 - 60
                                        </span>

                                        <span>
                                            <span class="bg-danger speed-badge"></span>
                                            > 60
                                        </span>

                            </div>

                        </div>

                        <div class="p-2 card-body">
                            <div id="map"></div>
                        </div>

                    </div>

                </div>

                {{-- stats --}}
                <div class="mb-4 col-lg-4">

                    <div class="shadow-sm mb-3 border-0 card">

                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">
                                📈 Estadísticas
                            </h5>
                        </div>

                        <div class="card-body" id="panel-stats">

                            <div class="py-5 text-muted text-center">
                                Sin datos
                            </div>

                        </div>

                    </div>

                    {{-- puntos --}}
                    <div class="shadow-sm border-0 card">

                        <div class="d-flex align-items-center justify-content-between card-header">
                            <h5 class="mb-0 fw-bold">
                                📍 Puntos GPS
                            </h5>

                            <span id="total-puntos" class="bg-primary badge">
                                0
                            </span>
                        </div>

                        <div class="p-2 card-body" style="max-height:420px;overflow-y:auto" id="lista-puntos">

                            <div class="py-5 text-muted text-center">
                                Sin datos
                            </div>

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
        let mapInstance  = null;
        let capasRuta    = [];
        let marcadores   = [];

        // Color distinto por número de ruta
        const COLORES_RUTA = ['#3b82f6','#a855f7','#f97316','#22c55e','#ec4899','#06b6d4','#eab308'];

        function colorRuta(num) {
            return COLORES_RUTA[(num - 1) % COLORES_RUTA.length];
        }

        function initMap() {
            if (mapInstance) return;
            mapInstance = L.map('map', { preferCanvas: true, zoomControl: true })
                          .setView([-12.046374, -77.042793], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap', maxZoom: 19
            }).addTo(mapInstance);
        }

        function limpiarMapa() {
            [...capasRuta, ...marcadores].forEach(c => mapInstance.removeLayer(c));
            capasRuta  = [];
            marcadores = [];
        }

        function colorVelocidad(vel) {
            if (vel > 60) return '#ef4444';
            if (vel > 30) return '#f59e0b';
            return '#3b82f6';
        }

        function iconoMarcador(colorFondo, texto, colorBorde) {
            return L.divIcon({
                className: '',
                html: `<div style="
                    min-width:36px; height:36px; border-radius:18px;
                    background:${colorFondo}; border:3px solid ${colorBorde || '#fff'};
                    display:flex; align-items:center; justify-content:center;
                    box-shadow:0 4px 12px rgba(0,0,0,.30);
                    font-size:13px; font-weight:700; color:#fff;
                    padding:0 6px; white-space:nowrap;
                ">${texto}</div>`,
                iconSize:   [36, 36],
                iconAnchor: [18, 18],
            });
        }

        async function snapToRoads(puntos) {
            if (puntos.length < 2) return puntos.map(p => [p.lat, p.lng]);
            const filtrados = puntos.filter(p => p.lat && p.lng && p.vel < 140);
            const reducidos = filtrados.filter((_, i) => i % 3 === 0);
            const coords    = reducidos.map(p => `${p.lng},${p.lat}`).join(';');
            try {
                const res  = await fetch(`https://router.project-osrm.org/match/v1/driving/${coords}?overview=full&geometries=geojson`);
                const data = await res.json();
                if (data.code === 'Ok' && data.matchings?.length) {
                    return data.matchings[0].geometry.coordinates.map(c => [c[1], c[0]]);
                }
            } catch (e) { console.error('OSRM', e); }
            return filtrados.map(p => [p.lat, p.lng]);
        }

        function calcularKm(puntos) {
            let total = 0;
            for (let i = 1; i < puntos.length; i++) {
                const R    = 6371;
                const dLat = (puntos[i].lat - puntos[i-1].lat) * Math.PI / 180;
                const dLng = (puntos[i].lng - puntos[i-1].lng) * Math.PI / 180;
                const a    = Math.sin(dLat/2)**2 +
                             Math.cos(puntos[i-1].lat*Math.PI/180) *
                             Math.cos(puntos[i].lat*Math.PI/180) *
                             Math.sin(dLng/2)**2;
                total += R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            }
            return total;
        }

        function hora(ts) {
            if (!ts) return '--';
            return new Date(ts).toLocaleTimeString('es-PE', {
                timeZone: 'America/Lima', hour: '2-digit', minute: '2-digit'
            });
        }

        async function dibujarRuta(ruta) {
            const { numero, puntos } = ruta;
            const color = colorRuta(numero);

            const coords = await snapToRoads(puntos);

            // sombra
            capasRuta.push(L.polyline(coords, {
                color: 'rgba(0,0,0,0.22)', weight: 12, opacity: .3,
                smoothFactor: 2, lineCap: 'round', lineJoin: 'round'
            }).addTo(mapInstance));

            // borde con color de la ruta
            capasRuta.push(L.polyline(coords, {
                color, weight: 8, opacity: 1,
                smoothFactor: 2, lineCap: 'round', lineJoin: 'round'
            }).addTo(mapInstance));

            // segmentos por velocidad encima
            for (let i = 1; i < coords.length; i++) {
                const idx = Math.min(
                    Math.floor(i * puntos.length / coords.length),
                    puntos.length - 1
                );
                capasRuta.push(L.polyline([coords[i-1], coords[i]], {
                    color:  colorVelocidad(puntos[idx]?.vel || 0),
                    weight: 5, opacity: .92,
                    smoothFactor: 2, lineCap: 'round', lineJoin: 'round'
                }).addTo(mapInstance));
            }

            // marcador inicio
            const mInicio = L.marker(coords[0], {
                icon: iconoMarcador(color, `R${numero} ▶`, '#fff')
            }).bindPopup(`<b>Ruta ${numero}</b><br>Inicio: ${hora(puntos[0]?.ts)}`)
              .addTo(mapInstance);

            // marcador fin
            const mFin = L.marker(coords.at(-1), {
                icon: iconoMarcador('#1e293b', `R${numero} 🏁`, color)
            }).bindPopup(`<b>Ruta ${numero}</b><br>Fin: ${hora(puntos.at(-1)?.ts)}`)
              .addTo(mapInstance);

            marcadores.push(mInicio, mFin);

            return coords;
        }

        async function cargarRecorrido() {
            const motId = document.getElementById('sel-motorizado').value;
            const fecha = document.getElementById('sel-fecha').value;
            if (!motId) { alert('Selecciona un motorizado'); return; }

            const btn = document.getElementById('btn-buscar');
            btn.disabled = true;
            btn.innerHTML = `<span class="me-1 spinner-border spinner-border-sm"></span> Cargando...`;

            try {
                initMap();
                limpiarMapa();

                const res  = await fetch(`/api/admin/recorrido?motorizado_id=${motId}&fecha=${fecha}`);
                const data = await res.json();
                const rutas = data.rutas || [];

                if (!rutas.length) { alert('No hay puntos GPS para esta fecha'); return; }

                document.getElementById('titulo-mapa').innerHTML =
                    `<i class="me-1 text-primary mdi mdi-map-marker-path"></i>
                     ${data.motorizado?.nombre} — ${fecha}
                     <span class="ms-2 badge bg-primary">${rutas.length} ruta${rutas.length > 1 ? 's' : ''}</span>`;

                document.getElementById('badge-puntos').textContent =
                    `${data.total_puntos} puntos GPS`;

                btn.innerHTML = `<span class="me-1 spinner-border spinner-border-sm"></span> Ajustando rutas...`;

                const todosCoords = [];
                for (const ruta of rutas) {
                    const coords = await dibujarRuta(ruta);
                    todosCoords.push(...coords);
                }

                if (todosCoords.length) {
                    mapInstance.fitBounds(todosCoords, { padding: [50, 50] });
                }

                document.getElementById('badge-km').classList.remove('d-none');
                document.getElementById('badge-vel').classList.remove('d-none');

                renderStats(rutas);
                renderListaPuntos(rutas);

            } catch (e) {
                console.error(e);
                alert('Error cargando recorrido');
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<i class="me-1 mdi mdi-magnify"></i> Ver recorrido`;
            }
        }

        function renderStats(rutas) {
            const todosPuntos = rutas.flatMap(r => r.puntos);
            const kmTotal     = rutas.reduce((s, r) => s + calcularKm(r.puntos), 0).toFixed(2);
            const vels        = todosPuntos.map(p => p.vel).filter(v => v > 0);
            const velMax      = vels.length ? Math.max(...vels).toFixed(1) : 0;

            document.getElementById('badge-km').textContent  = `${kmTotal} km`;
            document.getElementById('badge-vel').textContent = `${velMax} km/h máx`;

            const filasRutas = rutas.map(r => {
                const color = colorRuta(r.numero);
                const km    = calcularKm(r.puntos).toFixed(2);
                const vr    = r.puntos.map(p => p.vel).filter(v => v > 0);
                const vmax  = vr.length ? Math.max(...vr).toFixed(1) : 0;
                return `
                    <div class="d-flex align-items-center gap-2 p-2 rounded mb-2"
                         style="background:rgba(0,0,0,.04);border-left:4px solid ${color}">
                        <div style="width:10px;height:10px;border-radius:50%;background:${color};flex-shrink:0"></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">Ruta ${r.numero}</div>
                            <div class="text-muted small">${hora(r.puntos[0]?.ts)} → ${hora(r.puntos.at(-1)?.ts)}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small">${km} km</div>
                            <div class="text-muted small">${r.puntos.length} pts</div>
                        </div>
                    </div>`;
            }).join('');

            document.getElementById('panel-stats').innerHTML = `
                <div class="row g-2 text-center mb-3">
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <div class="fw-bold text-primary">${kmTotal}</div>
                            <small class="text-muted">km totales</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-2 rounded">
                            <div class="fw-bold text-warning">${velMax}</div>
                            <small class="text-muted">vel máx</small>
                        </div>
                    </div>
                </div>
                <div class="small fw-semibold text-muted mb-2 text-uppercase" style="letter-spacing:.06em">
                    Detalle por ruta
                </div>
                ${filasRutas}`;
        }

        function renderListaPuntos(rutas) {
            const total = rutas.reduce((s, r) => s + r.puntos.length, 0);
            document.getElementById('total-puntos').textContent = total;

            const html = rutas.map(ruta => {
                const color = colorRuta(ruta.numero);
                const step  = Math.max(1, Math.floor(ruta.puntos.length / 40));
                const items = ruta.puntos
                    .filter((_, i) => i % step === 0)
                    .map(p => `
                        <div class="d-flex align-items-center justify-content-between p-2 border-bottom gps-item"
                             style="cursor:pointer" onclick="irAPunto(${p.lat},${p.lng})">
                            <div>
                                <div class="small fw-bold">${hora(p.ts)}</div>
                                <div class="text-muted" style="font-size:11px">${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}</div>
                            </div>
                            <span class="badge" style="background:${colorVelocidad(p.vel||0)}">${p.vel} km/h</span>
                        </div>`).join('');

                return `
                    <div class="px-2 py-1 fw-bold small"
                         style="background:${color}18;border-left:3px solid ${color};color:${color}">
                        Ruta ${ruta.numero} &nbsp;·&nbsp;
                        ${hora(ruta.puntos[0]?.ts)} → ${hora(ruta.puntos.at(-1)?.ts)}
                    </div>
                    ${items}`;
            }).join('');

            document.getElementById('lista-puntos').innerHTML = html;
        }

        function irAPunto(lat, lng) {
            mapInstance.flyTo([lat, lng], 17, { duration: 1.5 });
        }

        document.getElementById('btn-buscar').addEventListener('click', cargarRecorrido);

        // sincronizar los dos date pickers
        document.getElementById('sel-fecha-top').addEventListener('change', function() {
            document.getElementById('sel-fecha').value = this.value;
        });
        document.getElementById('sel-fecha').addEventListener('change', function() {
            document.getElementById('sel-fecha-top').value = this.value;
        });
    </script>
@endpush
