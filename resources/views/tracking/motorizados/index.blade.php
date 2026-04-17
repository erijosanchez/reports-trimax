@extends('layouts.app')

@section('title', 'Motorizados')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-motorbike me-2 text-primary"></i>Motorizados</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('tracking.mapa') }}" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-map me-1"></i>Ver Mapa
            </a>
            <a href="{{ route('tracking.motorizados.create') }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-plus me-1"></i>Nuevo Motorizado
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Sede</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>GPS (GPSLogger)</th>
                        <th>Rutas</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($motorizados as $m)
                    <tr>
                        <td class="text-muted small">{{ $m->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-motorbike text-secondary"></i>
                                <strong>{{ $m->nombre }}</strong>
                            </div>
                        </td>
                        <td>{{ $m->sede }}</td>
                        <td>{{ $m->telefono ?: '—' }}</td>
                        <td>
                            @if($m->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            @if($m->api_token)
                                <button class="btn btn-xs btn-outline-info"
                                        onclick="verConfigGPS({{ $m->id }}, '{{ $m->nombre }}', '{{ $m->api_token }}')">
                                    <i class="mdi mdi-qrcode me-1"></i>Ver config GPSLogger
                                </button>
                            @else
                                <button class="btn btn-xs btn-outline-secondary"
                                        onclick="generarToken({{ $m->id }}, this)">
                                    <i class="mdi mdi-key me-1"></i>Generar token
                                </button>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tracking.rutas', ['motorizado_id' => $m->id]) }}" class="text-primary small">
                                Ver rutas
                            </a>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('tracking.motorizados.edit', $m) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('tracking.motorizados.destroy', $m) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar motorizado?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay motorizados registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal config GPSLogger --}}
<div class="modal fade" id="modalGPS" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-cellphone-marker me-2"></i>Configurar GPSLogger</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">El motorizado debe instalar <strong>GPSLogger</strong> (Play Store) y configurar así:</p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">General Settings → Log to custom URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm font-monospace" id="gps-url" readonly>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copiar('gps-url')">
                            <i class="mdi mdi-content-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="alert alert-info py-2 small">
                    <strong>Pasos en GPSLogger:</strong><br>
                    1. Abrir app → menú ☰ → <strong>General Options</strong><br>
                    2. Activar <strong>Log to custom URL</strong><br>
                    3. Pegar la URL de arriba<br>
                    4. <strong>HTTP Method:</strong> POST<br>
                    5. <strong>Log Interval:</strong> 30 segundos<br>
                    6. Volver al inicio → presionar <strong>Start Logging</strong> ▶
                </div>

                <div class="alert alert-warning py-2 small mb-0">
                    <i class="mdi mdi-shield-lock me-1"></i>
                    Este token es secreto. Si lo compartes, solo úsalo en el dispositivo del motorizado.
                    Puedes regenerarlo en cualquier momento.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-danger" id="btn-regenerar">
                    <i class="mdi mdi-refresh me-1"></i>Regenerar token
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const BASE_URL = 'http://app.trimaxcrm.com/api/tracking/gps';
let motoIdActual = null;

function verConfigGPS(id, nombre, token) {
    motoIdActual = id;
    const url = `${BASE_URL}?token=${token}&lat=%LAT&lng=%LON&speed=%SPD&accuracy=%ACC`;
    document.getElementById('gps-url').value = url;
    new bootstrap.Modal(document.getElementById('modalGPS')).show();
}

function generarToken(id, btn) {
    if (!confirm('¿Generar token GPS para este motorizado?')) return;

    fetch(`/tracking/motorizados/${id}/generar-token`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.token) {
            btn.outerHTML = `<button class="btn btn-xs btn-outline-info" onclick="verConfigGPS(${id}, '', '${data.token}')">
                <i class="mdi mdi-qrcode me-1"></i>Ver config GPSLogger
            </button>`;
        }
    });
}

document.getElementById('btn-regenerar')?.addEventListener('click', function() {
    if (!motoIdActual || !confirm('¿Regenerar token? El anterior dejará de funcionar.')) return;

    fetch(`/tracking/motorizados/${motoIdActual}/generar-token`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.token) {
            const url = `${BASE_URL}?token=${data.token}&lat=%LAT&lng=%LON&speed=%SPD&accuracy=%ACC`;
            document.getElementById('gps-url').value = url;
            alert('Token regenerado. Actualiza la configuración en GPSLogger.');
        }
    });
});

function copiar(inputId) {
    const el = document.getElementById(inputId);
    el.select();
    navigator.clipboard.writeText(el.value).then(() => {
        const btn = el.nextElementSibling;
        btn.innerHTML = '<i class="mdi mdi-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="mdi mdi-content-copy"></i>', 1500);
    });
}
</script>
@endpush
