@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-account-edit text-warning me-2"></i>
                                                Editar Usuario
                                            </h3>
                                            <p class="text-muted mt-1">Modifica la información de {{ $user->name }}</p>
                                        </div>
                                        <div>
                                            <a href="{{ route('marketing.users.show', $user->id) }}"
                                                class="btn btn-outline-secondary">
                                                <i class="mdi mdi-arrow-left me-1"></i> Volver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Errores de Validación -->
                            @if ($errors->any())
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <h6 class="alert-heading">
                                                <i class="mdi mdi-alert-circle me-2"></i>
                                                Errores de validación
                                            </h6>
                                            <ul class="mb-0 mt-2">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Formulario Principal -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-form-select text-primary me-2"></i>
                                                Información del Usuario
                                            </h4>

                                            <form method="POST" action="{{ route('marketing.users.update', $user->id) }}"
                                                id="editUserForm">
                                                @csrf
                                                @method('PUT')

                                                <div class="form-group">
                                                    <label class="form-label">
                                                        <i class="mdi mdi-account me-1"></i>
                                                        Nombre Completo
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name"
                                                        class="form-control form-control-lg"
                                                        value="{{ old('name', $user->name) }}" required
                                                        placeholder="Ej: Juan Pérez - Consultor Lima">
                                                    <small class="form-text text-muted">
                                                        <i class="mdi mdi-information"></i>
                                                        Nombre que aparecerá en las encuestas
                                                    </small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                <i class="mdi mdi-account-switch me-1"></i>
                                                                Tipo de Usuario
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <select name="role" id="role"
                                                                class="form-select form-control-lg" required>
                                                                <option value="">Seleccionar tipo...</option>
                                                                <option value="consultor"
                                                                    {{ old('role', $user->role) == 'consultor' ? 'selected' : '' }}>
                                                                    Consultor
                                                                </option>
                                                                <option value="sede"
                                                                    {{ old('role', $user->role) == 'sede' ? 'selected' : '' }}>
                                                                    Sede
                                                                </option>
                                                                <option value="trimax"
                                                                    {{ old('role', $user->role) == 'trimax' ? 'selected' : '' }}>
                                                                    Trimax
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group" id="locationField"
                                                            style="{{ old('role', $user->role) == 'sede' ? '' : 'display: none;' }}">
                                                            <label class="form-label">
                                                                <i class="mdi mdi-map-marker me-1"></i>
                                                                Ubicación de la Sede
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" name="location"
                                                                class="form-control form-control-lg"
                                                                value="{{ old('location', $user->location) }}"
                                                                placeholder="Ej: Lima Centro, Arequipa, Cusco">
                                                            <small class="form-text text-muted">
                                                                <i class="mdi mdi-information"></i>
                                                                Campo requerido solo para sedes
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="border-top pt-4 mt-4">
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" class="btn btn-warning text-white">
                                                            <i class="mdi mdi-content-save me-1"></i>
                                                            Guardar Cambios
                                                        </button>
                                                        <a href="{{ route('marketing.users.show', $user->id) }}"
                                                            class="btn btn-light">
                                                            <i class="mdi mdi-close-circle me-1"></i>
                                                            Cancelar
                                                        </a>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel de Información -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <!-- Estado Actual -->
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-information text-info me-2"></i>
                                                Estado Actual
                                            </h4>

                                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                                <img class="img-md rounded-circle me-3"
                                                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&size=128"
                                                    alt="profile">
                                                <div>
                                                    <h6 class="mb-1">{{ $user->name }}</h6>
                                                    <small class="text-muted">ID: #{{ $user->id }}</small>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Tipo</small>
                                                @if ($user->role === 'consultor')
                                                    <span class="badge badge-primary">
                                                        <i class="mdi mdi-account-tie"></i> Consultor
                                                    </span>
                                                @else
                                                    <span class="badge badge-info">
                                                        <i class="mdi mdi-office-building"></i> Sede
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($user->location)
                                                <div class="mb-3">
                                                    <small class="text-muted d-block mb-1">Ubicación</small>
                                                    <div>
                                                        <i class="mdi mdi-map-marker text-danger"></i>
                                                        <strong>{{ $user->location }}</strong>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Estado</small>
                                                @if ($user->is_active)
                                                    <span class="badge badge-success">
                                                        <i class="mdi mdi-check-circle"></i> Activo
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="mdi mdi-close-circle"></i> Inactivo
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mb-0">
                                                <small class="text-muted d-block mb-1">Creado</small>
                                                <div>
                                                    <i class="mdi mdi-calendar text-primary"></i>
                                                    <small>{{ $user->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                   
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Gap utility for older browsers */
        .gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Icon wrapper for info cards */
        .icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-wrapper i {
            font-size: 24px;
        }

        /* Image sizing */
        .img-md {
            width: 64px;
            height: 64px;
        }

        /* Form controls improvements */
        .form-control-lg,
        .form-select.form-control-lg {
            height: calc(2.875rem + 2px);
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 0.25rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }

        /* Alert improvements */
        .alert {
            border-radius: 0.375rem;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const locationField = document.getElementById('locationField');
            const locationInput = locationField.querySelector('input[name="location"]');

            // Function to toggle location field
            function toggleLocationField() {
                if (roleSelect.value === 'sede') {
                    locationField.style.display = 'block';
                    locationInput.required = true;
                } else {
                    locationField.style.display = 'none';
                    locationInput.required = false;
                    // No limpiar el valor al cambiar, mantener el original
                }
            }

            // Event listener for role change
            roleSelect.addEventListener('change', toggleLocationField);

            // Check initial state
            toggleLocationField();
        });
    </script>
@endpush
