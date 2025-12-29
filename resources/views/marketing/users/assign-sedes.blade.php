@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-office-building"></i> 
                            Asignar Sedes a {{ $consultor->name }}
                        </h4>
                        <a href="{{ route('marketing.users.show', $consultor->id) }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> Volver
                        </a>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('marketing.users.update-sedes', $consultor->id) }}">
                        @csrf

                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            <strong>Información:</strong> Las encuestas de las sedes seleccionadas se incluirán en las estadísticas de este consultor.
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="mdi mdi-checkbox-multiple-marked"></i> 
                                Seleccionar Sedes ({{ count($sedesAsignadas) }} asignadas):
                            </label>
                            
                            @if($sedes->count() > 0)
                            <div class="row">
                                @foreach($sedes as $sede)
                                <div class="col-md-6 mb-3">
                                    <div class="card {{ in_array($sede->id, $sedesAsignadas) ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input" 
                                                    type="checkbox" 
                                                    name="sedes[]" 
                                                    value="{{ $sede->id }}"
                                                    id="sede_{{ $sede->id }}"
                                                    {{ in_array($sede->id, $sedesAsignadas) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label w-100" for="sede_{{ $sede->id }}">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong>{{ $sede->name }}</strong><br>
                                                            <span class="badge bg-secondary">
                                                                <i class="mdi mdi-map-marker"></i> {{ $sede->location }}
                                                            </span>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted">
                                                                {{ $sede->total_surveys }} encuestas
                                                            </small><br>
                                                            <span class="badge bg-warning">
                                                                ⭐ {{ number_format($sede->average_rating, 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                    <i class="mdi mdi-checkbox-multiple-marked"></i> Seleccionar Todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                    <i class="mdi mdi-checkbox-multiple-blank-outline"></i> Deseleccionar Todas
                                </button>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> No hay sedes activas disponibles para asignar.
                            </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Guardar Asignación
                            </button>
                            <a href="{{ route('marketing.users.show', $consultor->id) }}" class="btn btn-secondary">
                                <i class="mdi mdi-close"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('input[type="checkbox"][name="sedes[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('input[type="checkbox"][name="sedes[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection