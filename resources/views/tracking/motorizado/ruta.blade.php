<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Mis Entregas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <style>
        body { background: #f4f6fb; font-size: 15px; }
        .header { background: #4B49AC; color: white; padding: 12px 16px; }
        .parada-card { border-radius: 12px; margin-bottom: 12px; }
        .parada-pendiente  { border-left: 4px solid #6c757d; }
        .parada-entregado  { border-left: 4px solid #1bcfb4; opacity: 0.7; }
        .parada-fallido    { border-left: 4px solid #fc424a; opacity: 0.7; }
        .btn-entregar { background: #1bcfb4; border: none; color: white; }
        .btn-fallar   { background: #fc424a; border: none; color: white; }
        .gps-bar { background: #fff3cd; padding: 8px 16px; font-size: 13px; }
        .gps-ok  { background: #d1f7ef; }
    </style>
</head>
<body>

<div class="header d-flex align-items-center gap-2">
    <i class="mdi mdi-motorbike fs-4"></i>
    <div>
        <div class="fw-bold">{{ $motorizado->nombre }}</div>
        <small>{{ $motorizado->sede }}</small>
    </div>
</div>

<div id="gps-bar" class="gps-bar">
    <i class="mdi mdi-crosshairs-gps me-1"></i>
    <span id="gps-status">Iniciando GPS...</span>
</div>

@if(!$ruta)
<div class="container mt-4 text-center">
    <i class="mdi mdi-sleep fs-1 text-muted"></i>
    <h5 class="mt-2 text-muted">Sin ruta activa</h5>
    <p class="text-muted">Tu administrador aún no ha iniciado una ruta para hoy.</p>
</div>
@else
<div class="container py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="fw-semibold">{{ $ruta->nombre }}</div>
            <small class="text-muted">{{ $ruta->paradas->where('estado','entregado')->count() }} / {{ $ruta->paradas->count() }} paradas completadas</small>
        </div>
        <div class="progress" style="width: 80px; height: 8px;">
            @php $pct = $ruta->paradas->count() > 0 ? round(($ruta->paradas->where('estado','entregado')->count() / $ruta->paradas->count()) * 100) : 0; @endphp
            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    @foreach($ruta->paradas as $parada)
    <div class="card parada-card parada-{{ $parada->estado }}" id="parada-{{ $parada->id }}">
        <div class="card-body py-3 px-3">
            <div class="d-flex align-items-start gap-2 mb-2">
                <span class="badge bg-secondary mt-1">{{ $parada->orden }}</span>
                <div class="flex-grow-1">
                    @if($parada->cliente)
                    <div class="fw-bold">{{ $parada->cliente }}</div>
                    @endif
                    <div>{{ $parada->direccion }}</div>
                    @if($parada->referencia)
                    <small class="text-muted">{{ $parada->referencia }}</small>
                    @endif
                </div>
                <div>
                    @if($parada->estado === 'entregado')
                        <span class="fs-4">✅</span>
                    @elseif($parada->estado === 'fallido')
                        <span class="fs-4">❌</span>
                    @endif
                </div>
            </div>

            @if($parada->estado === 'pendiente')
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-entregar flex-grow-1 rounded-3 py-2 fw-semibold"
                        onclick="marcarParada({{ $parada->id }}, 'entregado')">
                    <i class="mdi mdi-check me-1"></i>Entregado
                </button>
                <button class="btn btn-fallar flex-grow-1 rounded-3 py-2 fw-semibold"
                        onclick="mostrarFallo({{ $parada->id }})">
                    <i class="mdi mdi-close me-1"></i>No entregado
                </button>
            </div>

            {{-- Formulario de fallo (oculto) --}}
            <div id="fallo-{{ $parada->id }}" class="mt-2 d-none">
                <input type="text" class="form-control form-control-sm mb-2"
                       id="motivo-{{ $parada->id }}" placeholder="Motivo (ej: no había nadie)">
                <button class="btn btn-sm btn-danger w-100"
                        onclick="marcarParada({{ $parada->id }}, 'fallido')">Confirmar no entregado</button>
                <button class="btn btn-sm btn-outline-secondary w-100 mt-1"
                        onclick="document.getElementById('fallo-{{ $parada->id }}').classList.add('d-none')">Cancelar</button>
            </div>
            @elseif($parada->completado_at)
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($parada->completado_at)->setTimezone('America/Lima')->format('H:i') }}
                @if($parada->motivo_fallo) — {{ $parada->motivo_fallo }} @endif
            </small>
            @endif

            @if($parada->lat && $parada->lng)
            <a href="https://maps.google.com/?q={{ $parada->latitud }},{{ $parada->longitud }}"
               target="_blank" class="btn btn-sm btn-outline-secondary mt-2 w-100">
                <i class="mdi mdi-navigation me-1"></i>Cómo llegar
            </a>
            @endif
        </div>
    </div>
    @endforeach

</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const MOTORIZADO_ID = {{ $motorizado->id }};
const RUTA_ID       = {{ $ruta?->id ?? 'null' }};
let ultimaLat = null, ultimaLng = null;

// GPS
function iniciarGPS() {
    if (!navigator.geolocation) {
        document.getElementById('gps-status').textContent = 'GPS no disponible en este navegador.';
        return;
    }

    navigator.geolocation.watchPosition(
        pos => {
            ultimaLat = pos.coords.latitude;
            ultimaLng = pos.coords.longitude;

            const bar = document.getElementById('gps-bar');
            bar.className = 'gps-bar gps-ok';
            document.getElementById('gps-status').textContent =
                `GPS activo — Precisión: ${Math.round(pos.coords.accuracy)}m`;

            enviarUbicacion(pos);
        },
        err => {
            document.getElementById('gps-status').textContent = 'Error GPS: ' + err.message;
        },
        { enableHighAccuracy: true, maximumAge: 5000 }
    );
}

function enviarUbicacion(pos) {
    if (!RUTA_ID) return;
    fetch(`/tracking/motorizado/${MOTORIZADO_ID}/ubicacion`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            lat:      pos.coords.latitude,
            lng:      pos.coords.longitude,
            speed:    pos.coords.speed ? pos.coords.speed * 3.6 : null, // m/s → km/h
            accuracy: pos.coords.accuracy,
            ruta_id:  RUTA_ID,
        })
    }).catch(() => {});
}

// Enviar GPS cada 15 segundos aunque no haya movimiento
setInterval(() => {
    if (ultimaLat && RUTA_ID) {
        fetch(`/tracking/motorizado/${MOTORIZADO_ID}/ubicacion`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ lat: ultimaLat, lng: ultimaLng, ruta_id: RUTA_ID })
        }).catch(() => {});
    }
}, 15000);

function mostrarFallo(id) {
    document.getElementById('fallo-' + id).classList.remove('d-none');
}

function marcarParada(id, estado) {
    const motivo = estado === 'fallido'
        ? document.getElementById('motivo-' + id)?.value
        : null;

    fetch(`/tracking/paradas/${id}/marcar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ estado, motivo_fallo: motivo })
    })
    .then(r => r.json())
    .then(() => location.reload())
    .catch(() => alert('Error al guardar. Intenta de nuevo.'));
}

iniciarGPS();
</script>
</body>
</html>
