@extends('layouts.app')

@section('title', 'Tracking Motorizados — Mapa en Vivo')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapa-vivo {
        height: calc(100vh - 200px);
        min-height: 500px;
        border-radius: 8px;
    }
    .motorizado-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .motorizado-card:hover {
        transform: translateX(4px);
        border-left: 3px solid #4B49AC;
    }
    .badge-en-ruta { background-color: #1bcfb4; }
    .badge-idle    { background-color: #6c757d; }
    .leaflet-popup-content { font-size: 13px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-map-marker-radius me-2 text-primary"></i>Tracking Motorizados — En Vivo</h4>
        <div class="d-flex gap-2">
            @if(auth()->user()->puedeGestionarTracking())
            <a href="{{ route('tracking.rutas.create') }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-plus me-1"></i>Nueva Ruta
            </a>
            <a href="{{ route('tracking.motorizados') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-account-multiple me-1"></i>Motorizados
            </a>
            @endif
            <a href="{{ route('tracking.rutas') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-format-list-bulleted me-1"></i>Historial Rutas
            </a>
        </div>
    </div>

    <div class="row g-3">
        {{-- Panel izquierdo: lista de motorizados --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header py-2">
                    <span class="fw-semibold">Motorizados</span>
                    <span id="conteo-activos" class="badge bg-success ms-2">0</span>
                    <small class="text-muted ms-2">en ruta</small>
                </div>
                <div class="card-body p-2 overflow-auto" style="max-height: calc(100vh - 260px)">
                    <div id="lista-motorizados">
                        @foreach($motorizados as $m)
                        <div class="motorizado-card card mb-2 p-2" data-id="{{ $m->id }}" onclick="centrarEnMotorizado({{ $m->id }})">
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-motorbike fs-4 text-secondary"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold lh-1">{{ $m->nombre }}</div>
                                    <small class="text-muted">{{ $m->sede }}</small>
                                </div>
                                <span class="badge badge-idle" id="estado-{{ $m->id }}">—</span>
                            </div>
                            <div id="info-{{ $m->id }}" class="mt-1 d-none">
                                <small class="text-muted" id="ruta-nombre-{{ $m->id }}"></small><br>
                                <small id="paradas-info-{{ $m->id }}"></small>
                            </div>
                        </div>
                        @endforeach

                        @if($motorizados->isEmpty())
                        <p class="text-muted text-center small py-3">No hay motorizados registrados.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Mapa principal --}}
        <div class="col-md-9">
            <div class="card">
                <div class="card-body p-2">
                    <div id="mapa-vivo"></div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 mt-2 px-1">
                <small class="text-muted"><i class="mdi mdi-refresh me-1"></i>Actualización automática cada <strong>10 seg</strong></small>
                <small class="text-muted" id="ultima-actualizacion"></small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const mapa = L.map('mapa-vivo').setView([-12.046374, -77.042793], 12); // Lima

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(mapa);

const iconoMotorizado = L.divIcon({
    html: '<i class="mdi mdi-motorbike" style="font-size:22px;color:#4B49AC"></i>',
    iconSize: [24, 24],
    iconAnchor: [12, 12],
    className: ''
});

let marcadores = {};

function actualizarMapa() {
    fetch('{{ route("tracking.ubicaciones-vivo") }}')
        .then(r => r.json())
        .then(data => {
            let activos = 0;

            data.forEach(m => {
                const $badge    = document.getElementById('estado-' + m.id);
                const $info     = document.getElementById('info-' + m.id);
                const $rutaNom  = document.getElementById('ruta-nombre-' + m.id);
                const $paradas  = document.getElementById('paradas-info-' + m.id);

                if (m.en_ruta) activos++;

                if ($badge) {
                    $badge.textContent  = m.en_ruta ? 'En ruta' : 'Inactivo';
                    $badge.className    = 'badge ' + (m.en_ruta ? 'badge-en-ruta' : 'badge-idle');
                }
                if ($info) {
                    if (m.en_ruta) {
                        $info.classList.remove('d-none');
                        if ($rutaNom) $rutaNom.textContent = m.ruta_nombre || '';
                        if ($paradas) $paradas.innerHTML =
                            `<span class="text-success">${m.paradas_completadas}</span> / ${m.paradas_total} paradas`;
                    } else {
                        $info.classList.add('d-none');
                    }
                }

                if (m.posicion) {
                    const lat = m.posicion.lat;
                    const lng = m.posicion.lng;

                    if (marcadores[m.id]) {
                        marcadores[m.id].setLatLng([lat, lng]);
                        marcadores[m.id].getPopup().setContent(popupContent(m));
                    } else {
                        marcadores[m.id] = L.marker([lat, lng], { icon: iconoMotorizado })
                            .addTo(mapa)
                            .bindPopup(popupContent(m));
                    }
                }
            });

            const $conteo = document.getElementById('conteo-activos');
            if ($conteo) $conteo.textContent = activos;

            const $ts = document.getElementById('ultima-actualizacion');
            if ($ts) {
                const ahora = new Date();
                $ts.textContent = 'Últ. actualización: ' + ahora.toLocaleTimeString('es-PE');
            }
        })
        .catch(console.error);
}

function popupContent(m) {
    let html = `<strong>${m.nombre}</strong><br><small class="text-muted">${m.sede}</small>`;
    if (m.en_ruta) {
        html += `<br><span class="badge" style="background:#1bcfb4">En ruta</span>`;
        if (m.ruta_nombre) html += `<br><small>${m.ruta_nombre}</small>`;
        html += `<br><small>${m.paradas_completadas}/${m.paradas_total} paradas</small>`;
        html += `<br><a href="/tracking/rutas/${m.ruta_id}" class="btn btn-xs btn-outline-primary mt-1" style="font-size:11px">Ver ruta</a>`;
    } else {
        html += `<br><small class="text-muted">Sin ruta activa</small>`;
    }
    return html;
}

window.centrarEnMotorizado = function(id) {
    if (marcadores[id]) {
        mapa.setView(marcadores[id].getLatLng(), 16);
        marcadores[id].openPopup();
    }
};

actualizarMapa();
setInterval(actualizarMapa, 10000);
</script>
@endpush
