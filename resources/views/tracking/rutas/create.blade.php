@extends('layouts.app')

@section('title', 'Nueva Ruta')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapa-parada { height: 300px; border-radius: 8px; }
    .parada-item { background: #f8f9fa; border-radius: 8px; }
    .parada-drag { cursor: grab; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-9">

            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="{{ route('tracking.rutas') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
                <h4 class="mb-0">Nueva Ruta</h4>
            </div>

            @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('tracking.rutas.store') }}" id="form-ruta">
                @csrf

                <div class="card mb-3">
                    <div class="card-header py-2 fw-semibold">Datos de la ruta</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Motorizado <span class="text-danger">*</span></label>
                                <select name="motorizado_id" class="form-select @error('motorizado_id') is-invalid @enderror" required id="sel-motorizado">
                                    <option value="">Seleccionar...</option>
                                    @foreach($motorizados as $m)
                                    <option value="{{ $m->id }}" data-sede="{{ $m->sede }}" {{ old('motorizado_id') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nombre }} — {{ $m->sede }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('motorizado_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre de la ruta</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="{{ old('nombre') }}" placeholder="Ej: Ruta Mañana 17/04">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" class="form-control" rows="2"
                                          placeholder="Instrucciones especiales...">{{ old('notas') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <span class="fw-semibold">Paradas</span>
                        <button type="button" class="btn btn-sm btn-success" onclick="agregarParada()">
                            <i class="mdi mdi-plus me-1"></i>Agregar parada
                        </button>
                    </div>
                    <div class="card-body">

                        {{-- Mini mapa para ubicar parada (opcional) --}}
                        <div class="mb-3">
                            <div id="mapa-parada"></div>
                            <small class="text-muted">Haz clic en el mapa para capturar coordenadas de la parada activa (opcional).</small>
                        </div>

                        <div id="lista-paradas">
                            {{-- Las paradas se agregan dinámicamente --}}
                        </div>
                        <p id="msg-sin-paradas" class="text-muted text-center py-3">
                            Agrega al menos una parada.
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tracking.rutas') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Crear Ruta
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let paradaIndex = 0;
let paradaSeleccionada = null;

const mapa = L.map('mapa-parada').setView([-12.046374, -77.042793], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapa);

let markerTmp = null;
mapa.on('click', function(e) {
    const { lat, lng } = e.latlng;
    if (markerTmp) mapa.removeLayer(markerTmp);
    markerTmp = L.marker([lat, lng]).addTo(mapa);

    if (paradaSeleccionada !== null) {
        const el = document.querySelector(`[data-parada-idx="${paradaSeleccionada}"]`);
        if (el) {
            el.querySelector('.lat-input').value = lat.toFixed(7);
            el.querySelector('.lng-input').value = lng.toFixed(7);
        }
    }
});

function agregarParada() {
    const idx = paradaIndex++;
    document.getElementById('msg-sin-paradas').classList.add('d-none');

    const div = document.createElement('div');
    div.className = 'parada-item p-3 mb-2';
    div.dataset.paradaIdx = idx;
    div.innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary">Parada ${idx + 1}</span>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="eliminarParada(this)">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="paradas[${idx}][cliente]" class="form-control form-control-sm"
                       placeholder="Nombre del cliente">
            </div>
            <div class="col-md-6">
                <input type="text" name="paradas[${idx}][direccion]" class="form-control form-control-sm"
                       placeholder="Dirección *" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="paradas[${idx}][referencia]" class="form-control form-control-sm"
                       placeholder="Referencia">
            </div>
            <div class="col-md-3">
                <input type="number" name="paradas[${idx}][latitud]"  class="form-control form-control-sm lat-input"
                       placeholder="Latitud" step="any">
            </div>
            <div class="col-md-3">
                <input type="number" name="paradas[${idx}][longitud]" class="form-control form-control-sm lng-input"
                       placeholder="Longitud" step="any">
            </div>
        </div>
        <small class="text-muted">
            <a href="#" onclick="seleccionarParada(${idx}); return false;">
                <i class="mdi mdi-crosshairs-gps me-1"></i>Capturar del mapa
            </a>
        </small>
    `;

    document.getElementById('lista-paradas').appendChild(div);
    seleccionarParada(idx);
}

function seleccionarParada(idx) {
    paradaSeleccionada = idx;
    document.querySelectorAll('.parada-item').forEach(el => el.classList.remove('border', 'border-primary'));
    const el = document.querySelector(`[data-parada-idx="${idx}"]`);
    if (el) el.classList.add('border', 'border-primary');
}

function eliminarParada(btn) {
    btn.closest('.parada-item').remove();
    const quedan = document.querySelectorAll('.parada-item').length;
    if (quedan === 0) document.getElementById('msg-sin-paradas').classList.remove('d-none');
}

// Agregar primera parada automáticamente
agregarParada();
</script>
@endpush
