{{-- resources/views/rrhh/requerimientos/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nuevo Requerimiento de Personal')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- HEADER --}}
                <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                    <div>
                        <a href="{{ route('rrhh.requerimientos.index') }}"
                            class="d-block mb-1 text-muted text-decoration-none">
                            <i class="mdi-arrow-left mdi"></i> Volver al listado
                        </a>
                        <h3 class="mb-3">
                            <i class="mdi mdi-clipboard-plus"></i> Nuevo Requerimiento de Personal
                        </h3>
                    </div>
                </div>

                {{-- ERRORES --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle"></i>
                        <strong>Errores de validación:</strong>
                        <ul class="mt-1 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('rrhh.requerimientos.store') }}" method="POST" id="reqForm">
                    @csrf

                    {{-- SECCIÓN 1: Info General --}}
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-information-outline mdi"></i> Información General
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">GERENCIA</label>
                                            <input type="text" value="GERENCIA COMERCIAL" class="bg-light form-control"
                                                readonly>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                PUESTO <span class="text-danger">*</span>
                                            </label>
                                            <select name="puesto"
                                                class="form-select @error('puesto') is-invalid @enderror">
                                                <option value="">Seleccionar puesto...</option>
                                                @foreach ($puestos as $puesto)
                                                    <option value="{{ $puesto }}"
                                                        {{ old('puesto') === $puesto ? 'selected' : '' }}>
                                                        {{ $puesto }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('puesto')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                TIPO DE REQUERIMIENTO <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-3 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo"
                                                        id="tipoRegular" value="Regular"
                                                        {{ old('tipo', 'Regular') === 'Regular' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tipoRegular">
                                                        <i class="mdi-clock-outline text-secondary mdi"></i> Regular
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo"
                                                        id="tipoUrgente" value="Urgente"
                                                        {{ old('tipo') === 'Urgente' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tipoUrgente">
                                                        <i class="text-danger mdi mdi-lightning-bolt"></i>
                                                        <span class="text-danger fw-semibold">Urgente</span>
                                                    </label>
                                                </div>
                                            </div>
                                            @error('tipo')
                                                <div class="mt-1 text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: Datos del Puesto --}}
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-briefcase-outline mdi"></i> Datos del Puesto
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                SEDE <span class="text-danger">*</span>
                                            </label>
                                            <select name="sede" class="form-select @error('sede') is-invalid @enderror">
                                                <option value="">Seleccionar sede...</option>
                                                @foreach ($sedes as $sede)
                                                    <option value="{{ $sede }}"
                                                        {{ old('sede') === $sede ? 'selected' : '' }}>
                                                        {{ $sede }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sede')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                JEFE DIRECTO <span class="text-danger">*</span>
                                            </label>
                                            <select name="jefe_directo"
                                                class="form-select @error('jefe_directo') is-invalid @enderror">
                                                <option value="">Seleccionar jefe directo...</option>
                                                @foreach ($jefesDirectos as $jefe)
                                                    <option value="{{ $jefe }}"
                                                        {{ old('jefe_directo') === $jefe ? 'selected' : '' }}>
                                                        {{ $jefe }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jefe_directo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: Condiciones y Comentarios --}}
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-text-box-outline mdi"></i> Condiciones y Comentarios
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">CONDICIONES DE LA OFERTA</label>
                                            <textarea name="condiciones_oferta" class="form-control" rows="4"
                                                placeholder="Ej: Sueldo base S/ 2,500 + comisiones, horario lunes a sábado..." maxlength="1000">{{ old('condiciones_oferta') }}</textarea>
                                            <small class="text-muted">Máximo 1000 caracteres</small>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">COMENTARIOS</label>
                                            <textarea name="comentarios" class="form-control" rows="4"
                                                placeholder="Información adicional relevante para el proceso..." maxlength="1000">{{ old('comentarios') }}</textarea>
                                            <small class="text-muted">Máximo 1000 caracteres</small>
                                        </div>
                                    </div>

                                    {{-- Aviso notificación --}}
                                    <div class="d-flex align-items-center gap-2 mb-4 alert alert-info">
                                        <i class="mdi-email-outline mdi mdi-24px"></i>
                                        <span>Al guardar, se enviará una notificación automática a todos los involucrados
                                            del área de RRHH.</span>
                                    </div>

                                    {{-- Botones --}}
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('rrhh.requerimientos.index') }}" class="btn btn-secondary">
                                            <i class="mdi mdi-close"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="mdi-content-save mdi"></i> Guardar Requerimiento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08) !important;
            border: none !important;
            border-radius: 10px !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('reqForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Guardando...';
        });
    </script>
@endpush
