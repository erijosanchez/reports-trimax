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
        let mapInstance = null;
        let capasRuta = [];
        let marcadores = [];

        function initMap() {

            if (mapInstance) return;

            mapInstance = L.map('map', {
                preferCanvas: true,
                zoomControl: true,
            }).setView([-12.046374, -77.042793], 12);

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19
                }
            ).addTo(mapInstance);
        }

        function limpiarMapa() {

            capasRuta.forEach(c => {
                mapInstance.removeLayer(c);
            });

            marcadores.forEach(m => {
                mapInstance.removeLayer(m);
            });

            capasRuta = [];
            marcadores = [];
        }

        function colorVelocidad(vel) {

            if (vel > 60) return '#ef4444';
            if (vel > 30) return '#f59e0b';

            return '#3b82f6';
        }

        function iconoMarcador(color, emoji) {

            return L.divIcon({
                className: '',
                html: `
                    <div style="
                        width:34px;
                        height:34px;
                        border-radius:50%;
                        background:${color};
                        border:4px solid #fff;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        box-shadow:0 5px 15px rgba(0,0,0,.25);
                        font-size:16px;
                    ">
                        ${emoji}
                    </div>
                `,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });
        }

        async function snapToRoads(puntos) {

            if (puntos.length < 2) {
                return puntos.map(p => [p.lat, p.lng]);
            }

            // filtrar ruido GPS
            const filtrados = puntos.filter(p =>
                p.lat &&
                p.lng &&
                p.vel < 140
            );

            // reducir puntos
            const reducidos = filtrados.filter((_, i) => i % 3 === 0);

            const coordenadas = reducidos
                .map(p => `${p.lng},${p.lat}`)
                .join(';');

            try {

                const response = await fetch(
                    `https://router.project-osrm.org/match/v1/driving/${coordenadas}?overview=full&geometries=geojson`
                );

                const data = await response.json();

                if (
                    data.code === 'Ok' &&
                    data.matchings &&
                    data.matchings.length
                ) {

                    return data.matchings[0]
                        .geometry
                        .coordinates
                        .map(c => [c[1], c[0]]);
                }

            } catch (e) {

                console.error('OSRM ERROR', e);
            }

            return filtrados.map(p => [p.lat, p.lng]);
        }

        function calcularKm(puntos) {

            let total = 0;

            for (let i = 1; i < puntos.length; i++) {

                const R = 6371;

                const dLat = (puntos[i].lat - puntos[i - 1].lat) * Math.PI / 180;

                const dLng = (puntos[i].lng - puntos[i - 1].lng) * Math.PI / 180;

                const a =
                    Math.sin(dLat / 2) ** 2 +
                    Math.cos(puntos[i - 1].lat * Math.PI / 180) *
                    Math.cos(puntos[i].lat * Math.PI / 180) *
                    Math.sin(dLng / 2) ** 2;

                total += R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            return total;
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

            btn.innerHTML = `
                <span class="me-1 spinner-border spinner-border-sm"></span>
                Cargando...
            `;

            try {

                initMap();

                limpiarMapa();

                const response = await fetch(
                    `/api/admin/recorrido?motorizado_id=${motId}&fecha=${fecha}`
                );

                const data = await response.json();

                const puntos = data.puntos || [];

                if (!puntos.length) {

                    alert('No hay puntos GPS');

                    return;
                }

                document.getElementById('titulo-mapa').innerHTML = `
                    <i class="me-1 text-primary mdi mdi-map-marker-path"></i>
                    ${data.motorizado?.nombre} — ${fecha}
                `;

                // badges
                document.getElementById('badge-puntos').textContent =
                    `${puntos.length} puntos GPS`;

                // snap real
                btn.innerHTML = `
                    <span class="me-1 spinner-border spinner-border-sm"></span>
                    Ajustando ruta...
                `;

                const coordsSnapped = await snapToRoads(puntos);

                // sombra
                const sombra = L.polyline(coordsSnapped, {
                    color: 'rgba(0,0,0,0.25)',
                    weight: 12,
                    opacity: .35,
                    smoothFactor: 2,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(mapInstance);

                // borde
                const borde = L.polyline(coordsSnapped, {
                    color: '#ffffff',
                    weight: 8,
                    opacity: 1,
                    smoothFactor: 2,
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(mapInstance);

                capasRuta.push(sombra, borde);

                // segmentos por velocidad
                for (let i = 1; i < coordsSnapped.length; i++) {

                    const idx = Math.min(
                        Math.floor(i * puntos.length / coordsSnapped.length),
                        puntos.length - 1
                    );

                    const vel = puntos[idx]?.vel || 0;

                    const color = colorVelocidad(vel);

                    const linea = L.polyline(
                        [coordsSnapped[i - 1], coordsSnapped[i]], {
                            color,
                            weight: 5,
                            opacity: .95,
                            smoothFactor: 2,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }
                    ).addTo(mapInstance);

                    capasRuta.push(linea);
                }

                // inicio
                const markerInicio = L.marker(
                    coordsSnapped[0], {
                        icon: iconoMarcador('#22c55e', '▶')
                    }
                ).addTo(mapInstance);

                // fin
                const markerFin = L.marker(
                    coordsSnapped.at(-1), {
                        icon: iconoMarcador('#ef4444', '🏁')
                    }
                ).addTo(mapInstance);

                marcadores.push(markerInicio, markerFin);

                mapInstance.fitBounds(coordsSnapped, {
                    padding: [50, 50]
                });

                renderStats(puntos);

                renderListaPuntos(puntos);

            } catch (e) {

                console.error(e);

                alert('Error cargando recorrido');

            } finally {

                btn.disabled = false;

                btn.innerHTML = `
                    <i class="me-1 mdi mdi-magnify"></i>
                    Ver recorrido
                `;
            }
        }

        function renderStats(puntos) {

            const km = calcularKm(puntos).toFixed(2);

            const velocidades = puntos
                .map(p => p.vel)
                .filter(v => v > 0);

            const velMax = velocidades.length ?
                Math.max(...velocidades).toFixed(1) :
                0;

            const velProm = velocidades.length ?
                (
                    velocidades.reduce((a, b) => a + b, 0) /
                    velocidades.length
                ).toFixed(1) :
                0;

            const inicio = puntos[0]?.ts ?
                new Date(puntos[0].ts).toLocaleTimeString('es-PE', {
                    timeZone: 'America/Lima',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) : '--';

            const fin = puntos.at(-1)?.ts ?
                new Date(puntos.at(-1).ts).toLocaleTimeString('es-PE', {
                    timeZone: 'America/Lima',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) : '--';

            document.getElementById('panel-stats').innerHTML = `

                <div class="text-center row g-3">

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h3 class="text-primary fw-bold">${km}</h3>
                            <small class="text-muted">Kilómetros</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h3 class="text-info fw-bold">${puntos.length}</h3>
                            <small class="text-muted">Puntos GPS</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h3 class="text-warning fw-bold">${velMax}</h3>
                            <small class="text-muted">Vel máx</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h3 class="text-success fw-bold">${velProm}</h3>
                            <small class="text-muted">Vel promedio</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold">${inicio}</h6>
                            <small class="text-muted">Inicio</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold">${fin}</h6>
                            <small class="text-muted">Fin</small>
                        </div>
                    </div>

                </div>
            `;

            document.getElementById('badge-km').classList.remove('d-none');
            document.getElementById('badge-vel').classList.remove('d-none');

            document.getElementById('badge-km').textContent =
                `${km} km`;

            document.getElementById('badge-vel').textContent =
                `${velMax} km/h máx`;
        }

        function renderListaPuntos(puntos) {

            document.getElementById('total-puntos').textContent =
                puntos.length;

            const step = Math.max(
                1,
                Math.floor(puntos.length / 60)
            );

            const visibles = puntos.filter((_, i) => i % step === 0);

            document.getElementById('lista-puntos').innerHTML =
                visibles.map(p => {

                    const color = colorVelocidad(p.vel || 0);

                    const hora = p.ts ?
                        new Date(p.ts).toLocaleTimeString('es-PE', {
                            timeZone: 'America/Lima',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        }) :
                        '--';

                    return `
                        <div
                            class="d-flex align-items-center justify-content-between p-2 border-bottom gps-item"
                            style="cursor:pointer"
                            onclick="irAPunto(${p.lat}, ${p.lng})"
                        >

                            <div>
                                <div class="small fw-bold">
                                    ${hora}
                                </div>

                                <div class="text-muted small">
                                    ${p.lat}, ${p.lng}
                                </div>
                            </div>

                            <span class="badge"
                                style="background:${color}">
                                ${p.vel} km/h
                            </span>

                        </div>
                    `;
                }).join('');
        }

        function irAPunto(lat, lng) {

            mapInstance.flyTo([lat, lng], 17, {
                duration: 1.5
            });
        }

        document.getElementById('btn-buscar')
            .addEventListener('click', cargarRecorrido);
    </script>
@endpush
