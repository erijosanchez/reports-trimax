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
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('admin.users') }}" class="btn btn-light btn-sm me-3">
                                            <i class="mdi mdi-arrow-left"></i>
                                        </a>
                                        <div>
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-account-edit text-primary me-2"></i>
                                                Editar Usuario
                                            </h3>
                                            <p class="text-muted mt-1">Modifica la información del usuario</p>
                                        </div>
                                    </div>
                                    <div>
                                        @if($user->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Formulario principal -->
                            <div class="col-lg-8 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">
                                            <i class="mdi mdi-account-circle text-primary me-2"></i>
                                            Información del Usuario
                                        </h4>

                                        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <!-- Nombre -->
                                            <div class="mb-4">
                                                <label class="form-label">
                                                    Nombre Completo <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="mdi mdi-account"></i>
                                                    </span>
                                                    <input type="text" 
                                                           name="name" 
                                                           class="form-control @error('name') is-invalid @enderror" 
                                                           value="{{ old('name', $user->name) }}" 
                                                           required>
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
                                                    <input type="email" 
                                                           name="email" 
                                                           class="form-control @error('email') is-invalid @enderror" 
                                                           value="{{ old('email', $user->email) }}" 
                                                           required>
                                                </div>
                                                @error('email')
                                                    <div class="text-danger mt-1">
                                                        <small>{{ $message }}</small>
                                                    </div>
                                                @enderror
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
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->name }}" 
                                                                {{ $user->hasRole($role->name) ? 'selected' : '' }}>
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
                                            </div>

                                            <!-- Estado -->
                                            <div class="mb-4">
                                                <div class="form-check form-check-success">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" 
                                                               name="is_active" 
                                                               value="1"
                                                               class="form-check-input"
                                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                                        Usuario activo
                                                        <i class="input-helper"></i>
                                                    </label>
                                                </div>
                                                <small class="text-muted ms-4">
                                                    Los usuarios inactivos no podrán acceder al sistema
                                                </small>
                                            </div>

                                            <!-- Cambiar contraseña (opcional) -->
                                            <div class="border-top pt-4 mb-4">
                                                <h5 class="mb-3">
                                                    <i class="mdi mdi-lock-reset text-warning me-2"></i>
                                                    Cambiar Contraseña (Opcional)
                                                </h5>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Nueva Contraseña</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-lock"></i>
                                                        </span>
                                                        <input type="password" 
                                                               id="password"
                                                               name="password" 
                                                               class="form-control @error('password') is-invalid @enderror" 
                                                               placeholder="Dejar en blanco para mantener la actual">
                                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                                            <i class="mdi mdi-eye-outline"></i>
                                                        </button>
                                                    </div>
                                                    @error('password')
                                                        <div class="text-danger mt-1">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                    <small class="text-muted">
                                                        Mínimo 8 caracteres. Dejar en blanco para no cambiar.
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Confirmar Nueva Contraseña</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-lock-check"></i>
                                                        </span>
                                                        <input type="password" 
                                                               id="password_confirmation"
                                                               name="password_confirmation" 
                                                               class="form-control" 
                                                               placeholder="Repite la nueva contraseña">
                                                        <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm">
                                                            <i class="mdi mdi-eye-outline"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Botones -->
                                            <div class="d-flex gap-2 justify-content-between pt-3 border-top">
                                                <a href="{{ route('admin.users') }}" class="btn btn-light">
                                                    <i class="mdi mdi-close me-1"></i>Cancelar
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="mdi mdi-content-save me-1"></i>Actualizar Usuario
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Información adicional -->
                            <div class="col-lg-4 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">
                                            <i class="mdi mdi-information text-info me-2"></i>
                                            Información del Usuario
                                        </h4>

                                        <!-- Avatar -->
                                        <div class="text-center mb-4 pb-3 border-bottom">
                                            <img class="rounded-circle mb-3" 
                                                 style="width: 100px; height: 100px;"
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=200&background=6366f1&color=fff" 
                                                 alt="profile">
                                            <h5 class="mb-1">{{ $user->name }}</h5>
                                            <p class="text-muted mb-0">{{ $user->email }}</p>
                                        </div>

                                        <!-- Estadísticas -->
                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Rol actual</span>
                                                <span class="badge badge-primary">
                                                    {{ $user->roles->first()->name ?? 'Sin rol' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Estado</span>
                                                @if($user->is_active)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Registrado</span>
                                                <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted">Última actualización</span>
                                                <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                            </div>
                                        </div>

                                        <!-- Dashboards asignados -->
                                        @if(isset($user->dashboards))
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Dashboards asignados</span>
                                                    <span class="badge badge-info">{{ $user->dashboards->count() }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Zona de peligro -->
                                @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id())
                                    <div class="card border-danger">
                                        <div class="card-body">
                                            <h5 class="text-danger mb-3">
                                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                                Zona de Peligro
                                            </h5>
                                            <p class="text-muted small mb-3">
                                                Eliminar este usuario es permanente y no se puede deshacer.
                                            </p>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger w-100"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?\n\nEsta acción no se puede deshacer.')">
                                                    <i class="mdi mdi-delete-forever me-1"></i>Eliminar Usuario
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
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
        
        if (togglePassword && password) {
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
        }

        // Toggle password confirmation visibility
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordConfirmation = document.getElementById('password_confirmation');
        
        if (togglePasswordConfirm && passwordConfirmation) {
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
        }

        // Password match validation
        if (password && passwordConfirmation) {
            passwordConfirmation.addEventListener('input', function() {
                if (this.value && password.value && password.value !== this.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    });
</script>
@endsection