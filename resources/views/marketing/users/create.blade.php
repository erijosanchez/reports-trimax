@extends('layouts.app')

@section('title', 'Crear Usuario')

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
                                                <i class="mdi mdi-account-plus text-success me-2"></i>
                                                Crear Nuevo Usuario
                                            </h3>
                                            <p class="text-muted mt-1">Registra un nuevo consultor o sede para el sistema de
                                                encuestas</p>
                                        </div>
                                        <div>
                                            <a href="{{ route('marketing.users.index') }}"
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

                                            <form method="POST" action="{{ route('marketing.users.store') }}"
                                                id="createUserForm">
                                                @csrf

                                                <div class="form-group">
                                                    <label class="form-label">
                                                        <i class="mdi mdi-account me-1"></i>
                                                        Nombre Completo
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name"
                                                        class="form-control form-control-lg" value="{{ old('name') }}"
                                                        required placeholder="Ej: Juan Pérez - Consultor Lima">
                                                    <small class="form-text text-muted">
                                                        <i class="mdi mdi-information"></i>
                                                        Nombre que aparecerá en las encuestas
                                                    </small>
                                                </div>

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
                                                            {{ old('role') == 'consultor' ? 'selected' : '' }}>
                                                            Consultor
                                                        </option>
                                                        <option value="sede"
                                                            {{ old('role') == 'sede' ? 'selected' : '' }}>
                                                            Sede
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="form-group" id="locationField" style="display: none;">
                                                    <label class="form-label">
                                                        <i class="mdi mdi-map-marker me-1"></i>
                                                        Ubicación de la Sede
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="location"
                                                        class="form-control form-control-lg" value="{{ old('location') }}"
                                                        placeholder="Ej: Lima Centro, Arequipa, Cusco">
                                                    <small class="form-text text-muted">
                                                        <i class="mdi mdi-information"></i>
                                                        Campo requerido solo para sedes
                                                    </small>
                                                </div>

                                                <div class="border-top pt-4 mt-4">
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" class="btn btn-success text-white">
                                                            <i class="mdi mdi-check-circle me-1"></i>
                                                            Crear Usuario
                                                        </button>
                                                        <a href="{{ route('marketing.users.index') }}"
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

                                <!-- Panel Lateral -->
                                <div class="col-lg-4">
                                    <!-- Información del Sistema -->
                                    <div class="card mb-3 grid-margin stretch-card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-information text-info me-2"></i>
                                                Información Importante
                                            </h4>
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="icon-wrapper bg-primary-subtle rounded me-3">
                                                    <i class="mdi mdi-account-check text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Usuario Referencial</h6>
                                                    <small class="text-muted">Solo para gestión de encuestas, no requiere
                                                        login</small>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start mb-3">
                                                <div class="icon-wrapper bg-success-subtle rounded me-3">
                                                    <i class="mdi mdi-link-variant text-success"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Link Único</h6>
                                                    <small class="text-muted">Se generará automáticamente un enlace único de
                                                        encuesta</small>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start mb-3">
                                                <div class="icon-wrapper bg-warning-subtle rounded me-3">
                                                    <i class="mdi mdi-check-circle text-warning"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Estado Activo</h6>
                                                    <small class="text-muted">El usuario estará activo por defecto</small>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start">
                                                <div class="icon-wrapper bg-info-subtle rounded me-3">
                                                    <i class="mdi mdi-qrcode text-info"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Código QR</h6>
                                                    <small class="text-muted">Se generará un código QR para
                                                        compartir</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tipos de Usuario -->
                                    <div class="card grid-margin stretch-card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-help-circle text-primary me-2"></i>
                                                Tipos de Usuario
                                            </h4>

                                            <div class="alert alert-light border mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="mdi mdi-account-tie text-primary me-2"
                                                        style="font-size: 24px;"></i>
                                                    <div>
                                                        <h6 class="mb-1 text-primary">Consultor</h6>
                                                        <small class="text-muted">Personal que atiende directamente a
                                                            clientes. Puede tener
                                                            sedes asignadas.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-light border mb-0">
                                                <div class="d-flex align-items-start">
                                                    <i class="mdi mdi-office-building text-info me-2"
                                                        style="font-size: 24px;"></i>
                                                    <div>
                                                        <h6 class="mb-1 text-info">Sede</h6>
                                                        <small class="text-muted">Ubicación física donde se brinda
                                                            atención. Requiere
                                                            especificar la ubicación.</small>
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
    </div>

    <style>
        /* Gap utility for older browsers */
        .gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Icon wrapper for info cards */
        .icon-wrapper {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-wrapper i {
            font-size: 20px;
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
        .alert-light {
            background-color: #f8f9fa;
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
                console.log('Role selected:', roleSelect.value); // Debug

                if (roleSelect.value === 'sede') {
                    locationField.style.display = 'block';
                    locationInput.required = true;
                    console.log('Showing location field'); // Debug
                } else {
                    locationField.style.display = 'none';
                    locationInput.required = false;
                    locationInput.value = '';
                    console.log('Hiding location field'); // Debug
                }
            }

            // Event listener for role change
            roleSelect.addEventListener('change', function() {
                console.log('Change event triggered'); // Debug
                toggleLocationField();
            });

            // Check initial state (for old() values)
            toggleLocationField();
        });
    </script>
@endpush
