@extends('layouts.app')

@section('title', 'Detalle de Ruta #' . $ruta->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapa-ruta { height: 380px; width: 100%; border-radius: 6px; }
    .parada-card { border-left: 4px solid #dee2e6; transition: border-color .2s; }
    .parada-card.completado { border-left-color: #28a745 !important; }
    .parada-card.en_camino  { border-left-color: #ffc107 !important; }
    .parada-card.fallido    { border-left-color: #dc3545 !important; }
    .parada-card.pendiente  { border-left-color: #6c757d !important; }
    .num-parada {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px; flex-shrink: 0; color: #fff;
    }
    @media (max-width: 767px) { #mapa-ruta { height: 260px; } }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('tracking.rutas') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-arrow-left"></i>
                            </a>
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="mdi mdi-route me-2 text-primary"></i>
                                    Ruta #{{ $ruta->id }} — {{ $ruta->motorizado->nombre }}
                                </h4>
                                <p class="mb-0 text-muted small">
                                    {{ $ruta->fecha->format('d/m/Y') }} ·
                                    Sede {{ $ruta->motorizado->sede }} ·
                                    {{ $ruta->paradas->count() }} paradas
                                </p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge fs-6
                                {{ $ruta->estado === 'completado' ? 'bg-success'
                                   : ($ruta->estado === 'en_ruta' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $ruta->estado)) }}
                            </span>
                            <button class="btn btn-sm btn-success" id="btn-generar-link"
                                    data-id="{{ $ruta->id }}" onclick="generarLink({{ $ruta->id }})">
                                <i class="mdi mdi-whatsapp me-1"></i>
                                {{ $ruta->token_acceso ? 'Reenviar link' : 'Generar link motorizado' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="row">

            {{-- Mapa --}}
            <div class="mb-4 col-lg-7 col-md-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="mdi mdi-map me-1 text-primary"></i>Mapa de la ruta</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="mapa-ruta"></div>
                    </div>
                </div>
            </div>

            {{-- Timeline paradas --}}
            <div class="mb-4 col-lg-5 col-md-12">
                <div class="shadow-sm border-0 card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="mdi mdi-map-marker-multiple me-1 text-danger"></i>Paradas
                        </h6>
                        @php
                            $completadas = $ruta->paradas->where('estado','completado')->count();
                            $total       = $ruta->paradas->count();
                        @endphp
                        <span class="badge bg-primary">{{ $completadas }}/{{ $total }}</span>
                    </div>
                    <div class="card-body p-3">

                        @forelse($ruta->paradas as $parada)
                        @php
                            $colores = [
                                'completado' => '#28a745',
                                'en_camino'  => '#ffc107',
                                'fallido'    => '#dc3545',
                                'pendiente'  => '#6c757d',
                            ];
                            $color = $colores[$parada->estado] ?? '#6c757d';
                        @endphp
                        <div class="card parada-card {{ $parada->estado }} border-0 shadow-sm mb-3">
                            <div class="card-body py-2 px-3 d-flex gap-3 align-items-start">

                                <div class="num-parada" style="background:{{ $color }}">
                                    {{ $parada->orden_secuencia }}
                                </div>

                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ $parada->orden->cliente_nombre }}</div>
                                    <div class="text-muted" style="font-size:12px">
                                        {{ $parada->orden->direccion }}
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-1" style="font-size:11px">
                                        @if($parada->hora_llegada)
                                            <span class="text-secondary">
                                                <i class="mdi mdi-clock-in"></i> {{ $parada->hora_llegada->format('H:i') }}
                                            </span>
                                        @endif
                                        @if($parada->hora_salida)
                                            <span class="text-secondary">
                                                <i class="mdi mdi-clock-out"></i> {{ $parada->hora_salida->format('H:i') }}
                                            </span>
                                        @endif
                                        @if($parada->tiempo_en_parada !== null)
                                            <span class="text-primary">
                                                <i class="mdi mdi-timer"></i> {{ $parada->tiempo_en_parada }} min
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <select class="form-select form-select-sm sel-estado"
                                        data-id="{{ $parada->id }}" style="width:110px">
                                    <option value="pendiente"  {{ $parada->estado==='pendiente'  ?'selected':'' }}>Pendiente</option>
                                    <option value="en_camino"  {{ $parada->estado==='en_camino'  ?'selected':'' }}>En camino</option>
                                    <option value="completado" {{ $parada->estado==='completado' ?'selected':'' }}>Entregado</option>
                                    <option value="fallido"    {{ $parada->estado==='fallido'    ?'selected':'' }}>Fallido</option>
                                </select>

                            </div>
                        </div>
                        @empty
                            <p class="text-muted text-center py-3">Sin paradas registradas</p>
                        @endforelse

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
const paradas = @json($ruta->paradas->map(fn($p) => [
    'secuencia' => $p->orden_secuencia,
    'estado'    => $p->estado,
    'lat'       => $p->orden->latitud,
    'lng'       => $p->orden->longitud,
    'cliente'   => $p->orden->cliente_nombre,
    'direccion' => $p->orden->direccion,
]));

const COLORES = { completado:'#28a745', en_camino:'#ffc107', fallido:'#dc3545', pendiente:'#6c757d' };
const map     = L.map('mapa-ruta');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { attribution: '© OpenStreetMap' }).addTo(map);

const bounds = [];
paradas.forEach(p => {
    if (!p.lat || !p.lng) return;
    const c    = COLORES[p.estado] || '#6c757d';
    const icon = L.divIcon({
        className: '',
        html: `<div style="width:30px;height:30px;border-radius:50%;background:${c};
                    color:#fff;display:flex;align-items:center;justify-content:center;
                    font-weight:700;font-size:13px;border:2px solid #fff;
                    box-shadow:0 2px 6px rgba(0,0,0,.4)">${p.secuencia}</div>`,
        iconSize: [30, 30], iconAnchor: [15, 15],
    });
    L.marker([p.lat, p.lng], { icon })
        .bindPopup(`<strong>${p.secuencia}. ${p.cliente}</strong><br><small>${p.direccion}</small>`)
        .addTo(map);
    bounds.push([p.lat, p.lng]);
});

if (bounds.length > 1) {
    L.polyline(bounds, { color: '#3498db', weight: 3, dashArray: '6 4' }).addTo(map);
    map.fitBounds(bounds, { padding: [40, 40] });
} else if (bounds.length === 1) {
    map.setView(bounds[0], 14);
} else {
    map.setView([-12.046374, -77.042793], 6);
}

// Generar link para motorizado
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function generarLink(rutaId) {
    const btn = document.getElementById('btn-generar-link');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generando…';

    const res = await fetch(`/tracking/rutas/${rutaId}/token`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });

    if (res.ok) {
        const data = await res.json();

        // Modal con opciones
        const modal = document.createElement('div');
        modal.innerHTML = `
        <div class="modal fade" id="modalLink" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="mdi mdi-link-variant me-1"></i>Link del Motorizado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">El motorizado abre este link en su celular para marcar entregas:</p>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-sm" id="input-url" value="${data.url}" readonly>
                            <button class="btn btn-outline-secondary" onclick="copiarUrl()">
                                <i class="mdi mdi-content-copy"></i>
                            </button>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="${data.whatsapp}" target="_blank" class="btn btn-success btn-lg">
                                <i class="mdi mdi-whatsapp me-2"></i>Enviar por WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);
        new bootstrap.Modal(document.getElementById('modalLink')).show();

        btn.innerHTML = '<i class="mdi mdi-whatsapp me-1"></i>Reenviar link';
    } else {
        alert('Error al generar el link');
        btn.innerHTML = '<i class="mdi mdi-whatsapp me-1"></i>Generar link motorizado';
    }
    btn.disabled = false;
}

function copiarUrl() {
    const input = document.getElementById('input-url');
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = input.nextElementSibling;
        btn.innerHTML = '<i class="mdi mdi-check text-success"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="mdi mdi-content-copy"></i>'; }, 1500);
    });
}

// Cambio de estado de parada
document.querySelectorAll('.sel-estado').forEach(sel => {
    sel.addEventListener('change', async () => {
        const id     = sel.dataset.id;
        const estado = sel.value;
        const hoy    = new Date().toTimeString().slice(0, 5);
        const body   = { estado };
        if (estado === 'en_camino')  body.hora_llegada = hoy;
        if (estado === 'completado') body.hora_salida  = hoy;

        const res = await fetch(`/tracking/paradas/${id}`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });

        if (res.ok) {
            const card = sel.closest('.parada-card');
            card.className = `card parada-card ${estado} border-0 shadow-sm mb-3`;
        } else {
            alert('Error al actualizar parada');
            location.reload();
        }
    });
});
</script>
@endpush
