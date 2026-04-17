@extends('layouts.app')

@section('title', 'Ruta: ' . $ruta->nombre)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapa-ruta { height: 450px; border-radius: 8px; }
    .parada-pendiente  { border-left: 3px solid #6c757d; }
    .parada-entregado  { border-left: 3px solid #1bcfb4; }
    .parada-fallido    { border-left: 3px solid #fc424a; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('tracking.rutas') }}" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h4 class="mb-0">{{ $ruta->nombre }}</h4>
            <small class="text-muted">
                {{ $ruta->motorizado->nombre }} — {{ $ruta->sede }} —
                Creada {{ $ruta->created_at->setTimezone('America/Lima')->format('d/m/Y H:i') }}
            </small>
        </div>

        @if(auth()->user()->puedeGestionarTracking())
        <div class="d-flex gap-2">
            @if($ruta->estado === 'programada')
            <button class="btn btn-sm btn-success" onclick="cambiarEstado('iniciar')">
                <i class="mdi mdi-play me-1"></i>Iniciar Ruta
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="cambiarEstado('cancelar')">Cancelar</button>
            @elseif($ruta->estado === 'en_ruta')
            <button class="btn btn-sm btn-primary" onclick="cambiarEstado('completar')">
                <i class="mdi mdi-check me-1"></i>Completar Ruta
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-3">
        @php
            $badges = [
                'programada' => ['bg-secondary', 'Programada'],
                'en_ruta'    => ['bg-info', 'En ruta'],
                'completada' => ['bg-success', 'Completada'],
                'cancelada'  => ['bg-danger', 'Cancelada'],
            ];
            [$cls, $label] = $badges[$ruta->estado] ?? ['bg-secondary', $ruta->estado];
        @endphp
        <div class="col-md-3">
            <div class="card text-center py-3">
                <div class="fs-4 fw-bold"><span class="badge {{ $cls }}">{{ $label }}</span></div>
                <small class="text-muted">Estado</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-3">
                <div class="fs-4 fw-bold text-success">{{ $ruta->paradasCompletadas() }}</div>
                <small class="text-muted">Entregadas</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-3">
                <div class="fs-4 fw-bold text-danger">{{ $ruta->paradasFallidas() }}</div>
                <small class="text-muted">Fallidas</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-3">
                <div class="fs-4 fw-bold">
                    @if($ruta->duracionMinutos() !== null)
                        {{ $ruta->duracionMinutos() }} min
                    @elseif($ruta->estado === 'en_ruta' && $ruta->inicio_at)
                        <span class="text-info" id="duracion-live">{{ $ruta->inicio_at->diffInMinutes(now()) }} min</span>
                    @else
                        —
                    @endif
                </div>
                <small class="text-muted">Duración</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Mapa --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header py-2 fw-semibold d-flex align-items-center gap-2">
                    <i class="mdi mdi-map-search text-primary"></i> Recorrido
                    @if($ruta->estado === 'en_ruta')
                    <span class="badge bg-info ms-auto">En vivo</span>
                    @endif
                </div>
                <div class="card-body p-2">
                    <div id="mapa-ruta"></div>
                </div>
            </div>
        </div>

        {{-- Paradas --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2 fw-semibold">
                    <i class="mdi mdi-map-marker-multiple text-primary me-1"></i>Paradas
                    ({{ $ruta->paradas->count() }})
                </div>
                <div class="card-body p-2 overflow-auto" style="max-height: 450px">
                    @foreach($ruta->paradas as $p)
                    <div class="card mb-2 p-2 parada-{{ $p->estado }}">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge bg-secondary">{{ $p->orden }}</span>
                            <div class="flex-grow-1">
                                @if($p->cliente)
                                <div class="fw-semibold lh-1">{{ $p->cliente }}</div>
                                @endif
                                <small>{{ $p->direccion }}</small>
                                @if($p->referencia)
                                <br><small class="text-muted">{{ $p->referencia }}</small>
                                @endif
                            </div>
                            @php
                                $estadoIco = ['pendiente' => '⏳', 'entregado' => '✅', 'fallido' => '❌'];
                            @endphp
                            <span title="{{ $p->estado }}">{{ $estadoIco[$p->estado] ?? '' }}</span>
                        </div>
                        @if($p->completado_at)
                        <small class="text-muted mt-1 d-block">
                            {{ $p->completado_at->setTimezone('America/Lima')->format('H:i') }}
                            @if($p->motivo_fallo) — {{ $p->motivo_fallo }} @endif
                        </small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if($ruta->notas)
    <div class="card mt-3">
        <div class="card-body py-2">
            <strong>Notas:</strong> {{ $ruta->notas }}
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const polylineData = @json($polyline);
const paradas      = @json($ruta->paradas->map(fn($p) => [
    'orden'     => $p->orden,
    'cliente'   => $p->cliente,
    'direccion' => $p->direccion,
    'estado'    => $p->estado,
    'lat'       => $p->latitud ? (float)$p->latitud : null,
    'lng'       => $p->longitud ? (float)$p->longitud : null,
]));

const mapa = L.map('mapa-ruta');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapa);

const bounds = [];

// Dibujar polyline del recorrido
if (polylineData.length > 0) {
    const coords = polylineData.map(p => [p.lat, p.lng]);
    L.polyline(coords, { color: '#4B49AC', weight: 4, opacity: 0.8 }).addTo(mapa);
    coords.forEach(c => bounds.push(c));

    // Marcador inicio
    L.circleMarker(coords[0], { radius: 8, color: '#4B49AC', fillColor: '#fff', fillOpacity: 1, weight: 3 })
        .addTo(mapa).bindPopup('<strong>Inicio</strong>');

    // Marcador posición actual (último punto)
    const ultimo = coords[coords.length - 1];
    const iconoMoto = L.divIcon({
        html: '<i class="mdi mdi-motorbike" style="font-size:22px;color:#4B49AC"></i>',
        iconSize: [24, 24], iconAnchor: [12, 12], className: ''
    });
    L.marker(ultimo, { icon: iconoMoto }).addTo(mapa).bindPopup('<strong>Posición actual</strong>');
}

// Marcadores de paradas
const colores = { pendiente: '#6c757d', entregado: '#1bcfb4', fallido: '#fc424a' };
paradas.forEach(p => {
    if (!p.lat || !p.lng) return;
    bounds.push([p.lat, p.lng]);
    L.circleMarker([p.lat, p.lng], {
        radius: 9,
        color: colores[p.estado] || '#6c757d',
        fillColor: colores[p.estado] || '#6c757d',
        fillOpacity: 0.9,
        weight: 2
    }).addTo(mapa).bindPopup(`<strong>${p.orden}. ${p.cliente || ''}</strong><br>${p.direccion}<br><em>${p.estado}</em>`);
});

if (bounds.length > 0) {
    mapa.fitBounds(bounds, { padding: [20, 20] });
} else {
    mapa.setView([-12.046374, -77.042793], 12);
}

// Actualizar en vivo si la ruta está en curso
@if($ruta->estado === 'en_ruta')
let markerVivo = null;
function actualizarVivo() {
    fetch('{{ route("tracking.ubicaciones-vivo") }}')
        .then(r => r.json())
        .then(data => {
            const m = data.find(x => x.id == {{ $ruta->motorizado_id }});
            if (m?.posicion) {
                const { lat, lng } = m.posicion;
                const iconoMoto = L.divIcon({
                    html: '<i class="mdi mdi-motorbike" style="font-size:22px;color:#1bcfb4"></i>',
                    iconSize: [24,24], iconAnchor: [12,12], className: ''
                });
                if (markerVivo) {
                    markerVivo.setLatLng([lat, lng]);
                } else {
                    markerVivo = L.marker([lat, lng], { icon: iconoMoto }).addTo(mapa);
                }
            }
        });
}
actualizarVivo();
setInterval(actualizarVivo, 10000);

// Contador de duración
setInterval(() => {
    const el = document.getElementById('duracion-live');
    if (el) {
        const min = parseInt(el.textContent) + 1;
        el.textContent = min + ' min';
    }
}, 60000);
@endif

// Acciones de estado
window.cambiarEstado = function(accion) {
    const urls = {
        iniciar:   '{{ route("tracking.rutas.iniciar",   $ruta) }}',
        completar: '{{ route("tracking.rutas.completar", $ruta) }}',
        cancelar:  '{{ route("tracking.rutas.cancelar",  $ruta) }}',
    };
    const msgs = {
        iniciar:   '¿Iniciar la ruta?',
        completar: '¿Marcar ruta como completada?',
        cancelar:  '¿Cancelar la ruta?',
    };
    if (!confirm(msgs[accion])) return;

    fetch(urls[accion], {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.ok ? location.reload() : alert('Error al cambiar estado.'));
};
</script>
@endpush
