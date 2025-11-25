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
                                        <a href="{{ route('admin.users') }}" class="btn btn-light btn-sm me-3">
                                            <i class="mdi mdi-arrow-left"></i>
                                        </a>
                                        <div>
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-account-plus text-primary me-2"></i>
                                                Crear Nuevo Usuario
                                            </h3>
                                            <p class="text-muted mt-1">Registra un nuevo usuario en el sistema</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario -->
                            <div class="row">
                                <div class="col-lg-8 mx-auto grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-account-circle text-primary me-2"></i>
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
                                                        <div class="text-danger mt-1">
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
                                                        <div class="text-danger mt-1">
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
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            id="togglePassword">
                                                            <i class="mdi mdi-eye-outline"></i>
                                                        </button>
                                                    </div>
                                                    @error('password')
                                                        <div class="text-danger mt-1">
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
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            id="togglePasswordConfirm">
                                                            <i class="mdi mdi-eye-outline"></i>
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
                                                        <select name="role"
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
                                                        <div class="text-danger mt-1">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        Define los permisos de acceso del usuario
                                                    </small>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                                    <a href="{{ route('admin.users') }}" class="btn btn-light">
                                                        <i class="mdi mdi-close me-1"></i>Cancelar
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>Crear Usuario
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información adicional -->
                            <div class="row">
                                <div class="col-lg-8 mx-auto">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="mb-3">
                                                <i class="mdi mdi-information-outline text-info me-2"></i>
                                                Información sobre roles
                                            </h5>
                                            <ul class="text-muted mb-0">
                                                <li class="mb-2">
                                                    <strong>Super Admin:</strong> Acceso total al sistema, gestión de
                                                    usuarios y configuración
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Admin:</strong> Puede gestionar usuarios y dashboards
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
        /* Fix para gap */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Input groups */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
        }

        /* Password toggle button */
        .btn-outline-secondary {
            border-color: #e3e6f0;
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
            color: #6366f1;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.className = 'mdi mdi-eye-outline';
                } else {
                    icon.className = 'mdi mdi-eye-off-outline';
                }
            });

            // Toggle password confirmation visibility
            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmation = document.getElementById('password_confirmation');

            togglePasswordConfirm.addEventListener('click', function() {
                const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordConfirmation.setAttribute('type', type);

                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.className = 'mdi mdi-eye-outline';
                } else {
                    icon.className = 'mdi mdi-eye-off-outline';
                }
            });

            // Password match validation
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
