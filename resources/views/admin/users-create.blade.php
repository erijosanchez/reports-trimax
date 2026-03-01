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
                                    <div class="d-flex align-items-center mb-4">
                                        <a href="{{ route('admin.users') }}" class="me-3 btn btn-light btn-sm">
                                            <i class="mdi-arrow-left mdi"></i>
                                        </a>
                                        <div>
                                            <h3 class="mb-0 rate-percentage">
                                                <i class="me-2 text-primary mdi mdi-account-plus"></i>
                                                Crear Nuevo Usuario
                                            </h3>
                                            <p class="mt-1 text-muted">Registra un nuevo usuario en el sistema</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario -->
                            <div class="row">
                                <div class="grid-margin mx-auto col-lg-8 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-primary mdi mdi-account-circle"></i>
                                                Información del Usuario
                                            </h4>

                                            <form method="POST" action="{{ route('admin.users.store') }}">
                                                @csrf

                                                <!-- Nombre -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Nombre Completo <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-account"></i>
                                                        </span>
                                                        <input type="text" name="name"
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            value="{{ old('name') }}" required
                                                            placeholder="Ej: Juan Pérez">
                                                    </div>
                                                    @error('name')
                                                        <div class="mt-1 text-danger">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                </div>

                                                <!-- Email -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Correo Electrónico <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-email"></i>
                                                        </span>
                                                        <input type="email" name="email"
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            value="{{ old('email') }}" required
                                                            placeholder="usuario@ejemplo.com">
                                                    </div>
                                                    @error('email')
                                                        <div class="mt-1 text-danger">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        El usuario utilizará este correo para iniciar sesión
                                                    </small>
                                                </div>

                                                <!-- Contraseña -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Contraseña <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-lock"></i>
                                                        </span>
                                                        <input type="password" id="password" name="password"
                                                            class="form-control @error('password') is-invalid @enderror"
                                                            required placeholder="Mínimo 8 caracteres">
                                                        <button type="button" class="btn-outline-secondary btn"
                                                            id="togglePassword">
                                                            <i class="mdi-eye-outline mdi"></i>
                                                        </button>
                                                    </div>
                                                    @error('password')
                                                        <div class="mt-1 text-danger">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        Debe contener al menos 8 caracteres
                                                    </small>
                                                </div>

                                                <!-- Confirmar Contraseña -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Confirmar Contraseña <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-lock-check"></i>
                                                        </span>
                                                        <input type="password" id="password_confirmation"
                                                            name="password_confirmation" class="form-control" required
                                                            placeholder="Repite la contraseña">
                                                        <button type="button" class="btn-outline-secondary btn"
                                                            id="togglePasswordConfirm">
                                                            <i class="mdi-eye-outline mdi"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Rol -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Rol <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-shield-account"></i>
                                                        </span>
                                                        <select name="role" id="role"
                                                            class="form-select @error('role') is-invalid @enderror"
                                                            required>
                                                            <option value="">Seleccionar rol...</option>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->name }}"
                                                                    {{ old('role') === $role->name ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('role')
                                                        <div class="mt-1 text-danger">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        Define los permisos de acceso del usuario
                                                    </small>
                                                </div>

                                                <!-- Campo Sede (solo visible cuando rol = sede) -->
                                                <div class="mb-4" id="sedeField" style="display: none;">
                                                    <label class="form-label">
                                                        Sede <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-office-building"></i>
                                                        </span>
                                                        <select name="sede" id="sede"
                                                            class="form-select @error('sede') is-invalid @enderror">
                                                            <option value="">Seleccionar sede...</option>
                                                            @foreach ($sedes as $key => $nombre)
                                                                <option value="{{ $key }}"
                                                                    {{ old('sede') === $key ? 'selected' : '' }}>
                                                                    {{ $nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('sede')
                                                        <div class="mt-1 text-danger">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        Asigna la sede a la que pertenece este usuario
                                                    </small>
                                                </div>

                                                @if (auth()->user()->isSuperAdmin())
                                                    <div class="mb-4">
                                                        <div class="pt-4 border-top">
                                                            <h5 class="mb-1">
                                                                <i class="me-2 text-success mdi mdi-shield-check"></i>
                                                                Permisos Especiales
                                                            </h5>
                                                            <p class="mb-3 text-muted small">Solo el Super Admin puede
                                                                otorgar estos permisos</p>

                                                            {{-- SECCIÓN COMERCIAL --}}
                                                            <div class="mb-4">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi-briefcase-outline mdi"></i> Módulo
                                                                    Comercial
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_consultar_orden"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_consultar_orden') ? 'checked' : '' }}>
                                                                                Consultar Orden
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Automático para
                                                                                    rol Sede</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_acuerdos_comerciales"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_acuerdos_comerciales') ? 'checked' : '' }}>
                                                                                Acuerdos Comerciales
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_ventas_consolidadas"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_ventas_consolidadas') ? 'checked' : '' }}>
                                                                                Ventas Consolidadas
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_descuentos_especiales"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_descuentos_especiales') ? 'checked' : '' }}>
                                                                                Descuentos Especiales
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_lead_time"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_lead_time') ? 'checked' : '' }}>
                                                                                Lead Time
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- SECCIÓN RRHH --}}
                                                            <div class="mb-3">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi mdi-account-search"></i> Módulo RRHH — Requerimientos de Personal
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_crear_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_crear_requerimientos') ? 'checked' : '' }}>
                                                                                Crear Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Puede solicitar nuevas contrataciones</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_gestionar_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_gestionar_requerimientos') ? 'checked' : '' }}>
                                                                                Gestionar Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Asignar RH, cambiar estado, registrar avances</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_todos_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    {{ old('puede_ver_todos_requerimientos') ? 'checked' : '' }}>
                                                                                Ver Todos los Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Sin esto, solo ve los suyos</small></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3 mb-0 px-3 py-2 border alert alert-light">
                                                                    <small class="text-muted">
                                                                        <i class="me-1 mdi-information-outline mdi"></i>
                                                                        El rol <strong>RRHH</strong> tiene los 3 permisos automáticamente. Usa estos checkboxes para otros roles que necesiten acceso parcial.
                                                                    </small>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Botones -->
                                                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                                    <a href="{{ route('admin.users') }}" class="btn btn-light">
                                                        <i class="me-1 mdi mdi-close"></i>Cancelar
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="me-1 mdi mdi-check-circle"></i>Crear Usuario
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información adicional -->
                            <div class="row">
                                <div class="mx-auto col-lg-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="mb-3">
                                                <i class="me-2 mdi-information-outline text-info mdi"></i>
                                                Información sobre roles
                                            </h5>
                                            <ul class="mb-0 text-muted">
                                                <li class="mb-2">
                                                    <strong>Super Admin:</strong> Acceso total al sistema, gestión de usuarios y configuración
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Admin:</strong> Puede gestionar usuarios y dashboards
                                                </li>
                                                <li class="mb-2">
                                                    <strong>RRHH:</strong> Acceso completo al módulo de requerimientos de personal
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Sede:</strong> Acceso al dashboard de ventas de su sede asignada
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Consultor:</strong> Acceso a módulos comerciales
                                                </li>
                                                <li class="mb-0">
                                                    <strong>Usuario:</strong> Acceso limitado solo a dashboards asignados
                                                </li>
                                            </ul>
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
        .d-flex.gap-2>*+* { margin-left: 0.5rem; }
        .input-group-text { background-color: #f8f9fa; border-color: #e3e6f0; }
        .btn-outline-secondary { border-color: #e3e6f0; }
        .btn-outline-secondary:hover { background-color: #f8f9fa; border-color: #e3e6f0; color: #6366f1; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const sedeField = document.getElementById('sedeField');
            const sedeSelect = document.getElementById('sede');

            function toggleSedeField() {
                if (roleSelect.value === 'sede') {
                    sedeField.style.display = 'block';
                    sedeSelect.setAttribute('required', 'required');
                } else {
                    sedeField.style.display = 'none';
                    sedeSelect.removeAttribute('required');
                    sedeSelect.value = '';
                }
            }
            toggleSedeField();
            roleSelect.addEventListener('change', toggleSedeField);

            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
            });

            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmation = document.getElementById('password_confirmation');
            togglePasswordConfirm.addEventListener('click', function() {
                const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmation.setAttribute('type', type);
                this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
            });

            passwordConfirmation.addEventListener('input', function() {
                if (this.value && password.value !== this.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
@endsection