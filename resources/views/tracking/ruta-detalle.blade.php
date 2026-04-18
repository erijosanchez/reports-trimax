@extends('layouts.app')

@section('title', 'Detalle de Ruta #' . $ruta->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ── Mapa ── */
    #mapa-ruta { height: 420px; width: 100%; border-radius: 10px; }

    /* ── Timeline de paradas ── */
    .parada-timeline {
        position: relative;
        padding-left: 48px;
        margin-bottom: 0;
    }
    .parada-timeline::before {
        content: '';
        position: absolute;
        left: 19px; top: 16px; bottom: 16px;
        width: 2px;
        background: linear-gradient(to bottom, #dee2e6, #dee2e6);
    }

    .parada-item {
        position: relative;
        margin-bottom: 12px;
    }
    .parada-item:last-child { margin-bottom: 0; }

    .parada-num {
        position: absolute;
        left: -48px;
        width: 36px; height: 36px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex; align-items: center; justify-content: center;
        border: 2.5px solid #fff;
        box-shadow: 0 3px 10px rgba(0,0,0,.3);
        top: 0;
    }
    .parada-num span { transform: rotate(45deg); color:#fff; font-weight:700; font-size:13px; }

    .parada-body {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 14px;
        border-left: 3px solid #dee2e6;
        transition: border-color .2s, box-shadow .2s;
    }
    .parada-item.completado .parada-body { border-left-color: #27ae60; }
    .parada-item.en_camino  .parada-body { border-left-color: #f39c12; }
    .parada-item.fallido    .parada-body { border-left-color: #e74c3c; }
    .parada-item.pendiente  .parada-body { border-left-color: #95a5a6; }

    /* ── Pin verde destino (estilo app) ── */
    .destino-pin-wrapper {
        display: flex; flex-direction: column; align-items: center;
    }
    .destino-pin-head {
        width: 50px; height: 50px;
        background: #27ae60;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 3px solid #fff;
        box-shadow: 0 5px 16px rgba(39,174,96,.55);
        display: flex; align-items: center; justify-content: center;
    }
    .destino-pin-head svg { transform: rotate(45deg); }

    /* ── Parada pin ruta ── */
    .parada-pin-ruta {
        width: 32px; height: 32px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 2px solid #fff;
        box-shadow: 0 3px 10px rgba(0,0,0,.35);
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Popup ── */
    .leaflet-popup-content-wrapper { border-radius: 10px; }

    /* ── Badge estado ruta ── */
    .badge-ruta { font-size:13px; padding: 6px 14px; border-radius: 20px; }

    @media (max-width: 767px) {
        #mapa-ruta { height: 280px; }
        .parada-timeline { padding-left: 40px; }
    }
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
                            <a href="{{ route('tracking.rutas') }}" class="btn-outline-secondary btn btn-sm">
                                <i class="mdi-arrow-left mdi"></i>
                            </a>
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-primary mdi mdi-route"></i>
                                    Ruta #{{ $ruta->id }}
                                    <span class="text-muted fw-normal" style="font-size:15px">· {{ $ruta->motorizado->nombre }}</span>
                                </h4>
                                <p class="mb-0 text-muted small">
                                    {{ $ruta->fecha->format('d/m/Y') }} ·
                                    Sede {{ $ruta->motorizado->sede }} ·
                                    {{ $ruta->paradas->count() }} paradas
                                </p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-ruta
                                {{ $ruta->estado === 'completado' ? 'bg-success'
                                   : ($ruta->estado === 'en_ruta' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $ruta->estado)) }}
                            </span>
                            <button class="btn btn-sm btn-success" id="btn-generar-link"
                                    onclick="generarLink({{ $ruta->id }})">
                                <i class="me-1 mdi mdi-whatsapp"></i>
                                {{ $ruta->token_acceso ? 'Reenviar link' : 'Enviar a motorizado' }}
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
            <div class="grid-margin mb-4 col-lg-7 col-md-12 stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between card-header">
                        <h6 class="mb-0">
                            <i class="me-1 text-primary mdi mdi-map"></i>Mapa de la ruta
                        </h6>
                        {{-- Progreso --}}
                        @php
                            $completadas = $ruta->paradas->where('estado','completado')->count();
                            $total       = $ruta->paradas->count();
                            $pct         = $total > 0 ? round($completadas / $total * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress" style="width:80px;height:6px;border-radius:3px">
                                <div class="bg-success progress-bar" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-muted small">{{ $completadas }}/{{ $total }}</span>
                        </div>
                    </div>
                    <div class="p-2 card-body">
                        <div id="mapa-ruta"></div>
                    </div>
                </div>
            </div>

            {{-- Timeline paradas --}}
            <div class="mb-4 col-lg-5 col-md-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between card-header">
                        <h6 class="mb-0">
                            <i class="me-1 text-danger mdi mdi-map-marker-multiple"></i>Paradas
                        </h6>
                        <span class="bg-primary badge">{{ $completadas }}/{{ $total }}</span>
                    </div>
                    <div class="p-3 card-body" style="max-height:520px;overflow-y:auto">

                        <div class="parada-timeline">
                        @forelse($ruta->paradas as $parada)
                        @php
                            $colores = [
                                'completado' => '#27ae60',
                                'en_camino'  => '#f39c12',
                                'fallido'    => '#e74c3c',
                                'pendiente'  => '#95a5a6',
                            ];
                            $color = $colores[$parada->estado] ?? '#95a5a6';
                            $icono = match($parada->estado) {
                                'completado' => '✓',
                                'fallido'    => '✗',
                                default      => $parada->orden_secuencia,
                            };
                        @endphp
                        <div class="parada-item {{ $parada->estado }}">
                            <div class="parada-num" style="background:{{ $color }}">
                                <span>{{ $icono }}</span>
                            </div>
                            <div class="parada-body">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $parada->orden->cliente_nombre }}</div>
                                        <div class="text-muted" style="font-size:11px">
                                            <i class="mdi mdi-map-marker" style="color:{{ $color }}"></i>
                                            {{ $parada->orden->direccion }}
                                        </div>
                                        @if($parada->hora_llegada || $parada->hora_salida)
                                        <div class="d-flex gap-2 mt-1" style="font-size:10px;color:#888">
                                            @if($parada->hora_llegada)
                                                <span>↓ {{ $parada->hora_llegada->format('H:i') }}</span>
                                            @endif
                                            @if($parada->hora_salida)
                                                <span>↑ {{ $parada->hora_salida->format('H:i') }}</span>
                                            @endif
                                            @if($parada->tiempo_en_parada !== null)
                                                <span style="color:#3498db">⏱ {{ $parada->tiempo_en_parada }}min</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    <select class="form-select-sm form-select sel-estado"
                                            data-id="{{ $parada->id }}" style="width:100px;font-size:11px">
                                        <option value="pendiente"  {{ $parada->estado==='pendiente'  ?'selected':'' }}>Pendiente</option>
                                        <option value="en_camino"  {{ $parada->estado==='en_camino'  ?'selected':'' }}>En camino</option>
                                        <option value="completado" {{ $parada->estado==='completado' ?'selected':'' }}>Entregado</option>
                                        <option value="fallido"    {{ $parada->estado==='fallido'    ?'selected':'' }}>Fallido</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @empty
                            <p class="py-3 text-muted text-center">Sin paradas registradas</p>
                        @endforelse
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
const paradas = @json($paradasJson);
const COLORES  = { completado:'#27ae60', en_camino:'#f39c12', fallido:'#e74c3c', pendiente:'#95a5a6' };
const RUTA_COLOR = '#e74c3c';

const map = L.map('mapa-ruta', { zoomControl: true });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);

const bounds = [];

// Primer pendiente = destino principal (pin verde grande)
const pendientes = paradas.filter(p => p.estado !== 'completado' && p.estado !== 'fallido');
const destinoSec = pendientes.length > 0 ? pendientes[0].secuencia : null;

paradas.forEach(p => {
    if (!p.lat || !p.lng) return;

    const c   = COLORES[p.estado] || '#95a5a6';
    const esDestino = p.secuencia === destinoSec;

    let icon;
    if (esDestino) {
        // Pin verde grande estilo app
        icon = L.divIcon({
            className: '',
            html: `
            <div style="display:flex;flex-direction:column;align-items:center">
                <div style="
                    width:50px;height:50px;
                    background:#27ae60;
                    border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);
                    border:3px solid #fff;
                    box-shadow:0 5px 18px rgba(39,174,96,.55);
                    display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white" style="transform:rotate(45deg)">
                        <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM4 19V9h16v10H4zm8-5.5 4-2.5v5l-4-2.5zm-4 2.5V11l4 2.5L8 16z"/>
                    </svg>
                </div>
            </div>`,
            iconSize:    [50, 50],
            iconAnchor:  [25, 50],
            popupAnchor: [0, -54],
        });
    } else {
        const label = p.estado === 'completado' ? '✓' : p.estado === 'fallido' ? '✗' : p.secuencia;
        icon = L.divIcon({
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

    L.marker([p.lat, p.lng], { icon })
        .bindPopup(`
        <div style="min-width:160px;font-size:12px">
            <strong>${p.secuencia}. ${p.cliente}</strong><br>
            <small style="color:#666">${p.direccion}</small><br>
            <span style="color:${c};font-weight:600;font-size:11px;text-transform:capitalize">${p.estado}</span>
        </div>`)
        .addTo(map);
    bounds.push([p.lat, p.lng]);
});

// Línea roja gruesa de ruta
if (bounds.length > 1) {
    // Sombra de la línea
    L.polyline(bounds, { color: 'rgba(0,0,0,0.15)', weight: 9, lineJoin:'round', lineCap:'round' })
        .addTo(map).bringToBack();
    // Línea principal roja
    L.polyline(bounds, { color: RUTA_COLOR, weight: 5, opacity: 0.92, lineJoin:'round', lineCap:'round' })
        .addTo(map);
    map.fitBounds(bounds, { padding: [50, 50] });
} else if (bounds.length === 1) {
    map.setView(bounds[0], 14);
} else {
    map.setView([-12.046374, -77.042793], 6);
}

/* ─── Generar link WhatsApp ─────────────────────────── */
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

async function generarLink(rutaId) {
    const btn = document.getElementById('btn-generar-link');
    btn.disabled = true;
    btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Generando…';

    const res = await fetch(`/tracking/rutas/${rutaId}/token`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });

    if (res.ok) {
        const data = await res.json();
        const modal = document.createElement('div');
        modal.innerHTML = `
        <div class="modal fade" id="modalLink" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius:12px;overflow:hidden">
                    <div class="bg-success border-0 text-white modal-header">
                        <h5 class="modal-title"><i class="me-1 mdi mdi-link-variant"></i>Link del Motorizado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="p-4 modal-body">
                        <p class="mb-3 text-muted small">El motorizado abre este link en su celular para marcar entregas:</p>
                        <div class="input-group mb-4">
                            <input type="text" class="form-control" id="input-url" value="${data.url}" readonly
                                   style="font-size:12px;font-family:monospace;background:#f8f9fa">
                            <button class="btn-outline-secondary btn" onclick="copiarUrl()">
                                <i class="mdi-content-copy mdi"></i>
                            </button>
                        </div>
                        <a href="${data.whatsapp}" target="_blank"
                           class="d-flex align-items-center justify-content-center gap-2 w-100 btn btn-success btn-lg">
                            <i class="mdi mdi-whatsapp" style="font-size:20px"></i>
                            <span>Enviar por WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);
        new bootstrap.Modal(document.getElementById('modalLink')).show();
        btn.innerHTML = '<i class="me-1 mdi mdi-whatsapp"></i>Reenviar link';
    } else {
        alert('Error al generar el link');
        btn.innerHTML = '<i class="me-1 mdi mdi-whatsapp"></i>Enviar a motorizado';
    }
    btn.disabled = false;
}

function copiarUrl() {
    const input = document.getElementById('input-url');
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = input.nextElementSibling;
        btn.innerHTML = '<i class="text-success mdi mdi-check"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="mdi-content-copy mdi"></i>'; }, 1500);
    });
}

/* ─── Cambio estado parada ──────────────────────────── */
document.querySelectorAll('.sel-estado').forEach(sel => {
    sel.addEventListener('change', async () => {
        const id     = sel.dataset.id;
        const estado = sel.value;
        const hoy    = new Date().toTimeString().slice(0, 5);
        const body   = { estado };
        if (estado === 'en_camino')  body.hora_llegada = hoy;
        if (estado === 'completado') body.hora_salida  = hoy;

        sel.disabled = true;
        const res = await fetch(`/tracking/paradas/${id}`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });

        if (res.ok) {
            const item = sel.closest('.parada-item');
            item.className = `parada-item ${estado}`;
            // Actualizar el número del pin
            const colors = { completado:'#27ae60', en_camino:'#f39c12', fallido:'#e74c3c', pendiente:'#95a5a6' };
            const numEl  = item.querySelector('.parada-num');
            numEl.style.background = colors[estado];
            const label = estado === 'completado' ? '✓' : estado === 'fallido' ? '✗' : numEl.querySelector('span').textContent;
            numEl.querySelector('span').textContent = label;
        } else {
            alert('Error al actualizar parada');
            location.reload();
        }
        sel.disabled = false;
    });
});
</script>
@endpush