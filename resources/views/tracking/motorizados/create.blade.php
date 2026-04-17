@extends('layouts.app')

@section('title', isset($motorizado) ? 'Editar Motorizado' : 'Nuevo Motorizado')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="d-flex align-items-center gap-2 mb-3">
                <a href="{{ route('tracking.motorizados') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
                <h4 class="mb-0">{{ isset($motorizado) ? 'Editar Motorizado' : 'Nuevo Motorizado' }}</h4>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($motorizado) ? route('tracking.motorizados.update', $motorizado) : route('tracking.motorizados.store') }}">
                        @csrf
                        @if(isset($motorizado)) @method('PUT') @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $motorizado->nombre ?? '') }}" required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
                            <input type="text" name="sede" class="form-control @error('sede') is-invalid @enderror"
                                   value="{{ old('sede', $motorizado->sede ?? '') }}" required>
                            @error('sede')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                   value="{{ old('telefono', $motorizado->telefono ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Traccar Device ID</label>
                            <input type="text" name="traccar_device_id" class="form-control"
                                   value="{{ old('traccar_device_id', $motorizado->traccar_device_id ?? '') }}"
                                   placeholder="ID del dispositivo en Traccar">
                            <small class="text-muted">Dejar en blanco si no usa Traccar. El GPS se guardará vía navegador.</small>
                        </div>

                        @if(isset($motorizado))
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo"
                                       {{ old('activo', $motorizado->activo) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tracking.motorizados') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($motorizado) ? 'Guardar cambios' : 'Registrar motorizado' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
