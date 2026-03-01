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
                                            <a href="{{ route('admin.users') }}" class="me-3 btn btn-light btn-sm">
                                                <i class="mdi-arrow-left mdi"></i>
                                            </a>
                                            <div>
                                                <h3 class="mb-0 rate-percentage">
                                                    <i class="me-2 text-primary mdi mdi-account-edit"></i>
                                                    Editar Usuario
                                                </h3>
                                                <p class="mt-1 text-muted">Modifica la información del usuario</p>
                                            </div>
                                        </div>
                                        <div>
                                            @if ($user->is_active)
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
                                <div class="grid-margin col-lg-8 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-primary mdi mdi-account-circle"></i>
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
                                                        <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                                                        <input type="text" name="name"
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            value="{{ old('name', $user->name) }}" required>
                                                    </div>
                                                    @error('name')<div class="mt-1 text-danger"><small>{{ $message }}</small></div>@enderror
                                                </div>

                                                <!-- Email -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Correo Electrónico <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                        <input type="email" name="email"
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            value="{{ old('email', $user->email) }}" required>
                                                    </div>
                                                    @error('email')<div class="mt-1 text-danger"><small>{{ $message }}</small></div>@enderror
                                                </div>

                                                <!-- Rol -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Rol <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="mdi mdi-shield-account"></i></span>
                                                        <select name="role" id="role"
                                                            class="form-select @error('role') is-invalid @enderror" required>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->name }}"
                                                                    {{ old('role', $user->hasRole($role->name) ? $role->name : '') === $role->name ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('role')<div class="mt-1 text-danger"><small>{{ $message }}</small></div>@enderror
                                                </div>

                                                <!-- Campo Sede -->
                                                <div class="mb-4" id="sedeField"
                                                    style="{{ old('role', $user->hasRole('sede') ? 'sede' : '') === 'sede' ? 'display: block;' : 'display: none;' }}">
                                                    <label class="form-label">
                                                        Sede <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="mdi mdi-office-building"></i></span>
                                                        <select name="sede" id="sede"
                                                            class="form-select @error('sede') is-invalid @enderror">
                                                            <option value="">Seleccionar sede...</option>
                                                            @foreach ($sedes as $key => $nombre)
                                                                <option value="{{ $key }}"
                                                                    {{ old('sede', $user->sede) === $key ? 'selected' : '' }}>
                                                                    {{ $nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @error('sede')<div class="mt-1 text-danger"><small>{{ $message }}</small></div>@enderror
                                                    <small class="text-muted">Asigna la sede a la que pertenece este usuario</small>
                                                </div>

                                                <!-- Estado -->
                                                <div class="mb-4">
                                                    <div class="form-check form-check-success">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" name="is_active" value="1"
                                                                class="form-check-input"
                                                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                                            Usuario activo
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                                    <small class="ms-4 text-muted">Los usuarios inactivos no podrán acceder al sistema</small>
                                                </div>

                                                @if (auth()->user()->isSuperAdmin())
                                                    <div class="mb-4">
                                                        <div class="pt-4 border-top">
                                                            <h5 class="mb-1">
                                                                <i class="me-2 text-success mdi mdi-shield-check"></i>
                                                                Permisos Especiales
                                                            </h5>
                                                            <p class="mb-3 text-muted small">Solo el Super Admin puede otorgar estos permisos</p>

                                                            {{-- MÓDULO COMERCIAL --}}
                                                            <div class="mb-4">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small" style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi-briefcase-outline mdi"></i> Módulo Comercial
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_consultar_orden" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_consultar_orden', $user->puede_ver_consultar_orden ?? false) ? 'checked' : '' }}>
                                                                                Consultar Orden <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Automático para rol Sede</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_acuerdos_comerciales" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_acuerdos_comerciales', $user->puede_ver_acuerdos_comerciales ?? false) ? 'checked' : '' }}>
                                                                                Acuerdos Comerciales <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_ventas_consolidadas" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_ventas_consolidadas', $user->puede_ver_ventas_consolidadas ?? false) ? 'checked' : '' }}>
                                                                                Ventas Consolidadas <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_descuentos_especiales" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_descuentos_especiales', $user->puede_ver_descuentos_especiales ?? false) ? 'checked' : '' }}>
                                                                                Descuentos Especiales <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_lead_time" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_lead_time', $user->puede_ver_lead_time ?? false) ? 'checked' : '' }}>
                                                                                Lead Time <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- MÓDULO RRHH --}}
                                                            <div class="mb-3">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small" style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi mdi-account-search"></i> Módulo RRHH — Requerimientos de Personal
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_crear_requerimientos" value="1" class="form-check-input"
                                                                                    {{ old('puede_crear_requerimientos', $user->puede_crear_requerimientos ?? false) ? 'checked' : '' }}>
                                                                                Crear Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Puede solicitar nuevas contrataciones</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_gestionar_requerimientos" value="1" class="form-check-input"
                                                                                    {{ old('puede_gestionar_requerimientos', $user->puede_gestionar_requerimientos ?? false) ? 'checked' : '' }}>
                                                                                Gestionar Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Asignar RH, cambiar estado, registrar avances</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox" name="puede_ver_todos_requerimientos" value="1" class="form-check-input"
                                                                                    {{ old('puede_ver_todos_requerimientos', $user->puede_ver_todos_requerimientos ?? false) ? 'checked' : '' }}>
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

                                                <!-- Cambiar contraseña -->
                                                <div class="mb-4 pt-4 border-top">
                                                    <h5 class="mb-3">
                                                        <i class="me-2 text-warning mdi mdi-lock-reset"></i>
                                                        Cambiar Contraseña (Opcional)
                                                    </h5>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nueva Contraseña</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                                                            <input type="password" id="password" name="password"
                                                                class="form-control @error('password') is-invalid @enderror"
                                                                placeholder="Dejar en blanco para mantener la actual">
                                                            <button type="button" class="btn-outline-secondary btn" id="togglePassword">
                                                                <i class="mdi-eye-outline mdi"></i>
                                                            </button>
                                                        </div>
                                                        @error('password')<div class="mt-1 text-danger"><small>{{ $message }}</small></div>@enderror
                                                        <small class="text-muted">Mínimo 8 caracteres. Dejar en blanco para no cambiar.</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Confirmar Nueva Contraseña</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="mdi mdi-lock-check"></i></span>
                                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                                class="form-control" placeholder="Repite la nueva contraseña">
                                                            <button type="button" class="btn-outline-secondary btn" id="togglePasswordConfirm">
                                                                <i class="mdi-eye-outline mdi"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex justify-content-between gap-2 pt-3 border-top">
                                                    <a href="{{ route('admin.users') }}" class="btn btn-light">
                                                        <i class="me-1 mdi mdi-close"></i>Cancelar
                                                    </a>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="me-1 mdi-content-save mdi"></i>Actualizar Usuario
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel lateral info -->
                                <div class="grid-margin col-lg-4 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-info mdi mdi-information"></i>
                                                Información del Usuario
                                            </h4>

                                            <div class="mb-4 pb-3 border-bottom text-center">
                                                <img class="mb-3 rounded-circle" style="width: 100px; height: 100px;"
                                                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=200&background=6366f1&color=fff"
                                                    alt="profile">
                                                <h5 class="mb-1">{{ $user->name }}</h5>
                                                <p class="mb-0 text-muted">{{ $user->email }}</p>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="text-muted">Rol actual</span>
                                                    <span class="badge badge-primary">
                                                        {{ $user->roles->first()->name ?? 'Sin rol' }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($user->hasRole('sede') && $user->sede)
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Sede asignada</span>
                                                        <span class="badge badge-info">{{ $user->getSedeName() }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Permisos activos agrupados --}}
                                            @php
                                                $permisosComerciales = collect([
                                                    'puede_ver_consultar_orden'       => 'Consultar Orden',
                                                    'puede_ver_acuerdos_comerciales'  => 'Acuerdos Comerciales',
                                                    'puede_ver_ventas_consolidadas'   => 'Ventas Consolidadas',
                                                    'puede_ver_descuentos_especiales' => 'Descuentos Especiales',
                                                    'puede_ver_lead_time'             => 'Lead Time',
                                                ])->filter(fn($label, $campo) => $user->$campo);

                                                $permisosRrhh = collect([
                                                    'puede_crear_requerimientos'      => 'Crear Reqs.',
                                                    'puede_gestionar_requerimientos'  => 'Gestionar Reqs.',
                                                    'puede_ver_todos_requerimientos'  => 'Ver Todos',
                                                ])->filter(fn($label, $campo) => $user->$campo);
                                            @endphp

                                            @if ($permisosComerciales->isNotEmpty())
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <p class="mb-2 text-muted"><small><i class="me-1 mdi-briefcase-outline mdi"></i>Permisos Comerciales</small></p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($permisosComerciales as $label)
                                                            <span class="badge badge-success">
                                                                <i class="me-1 mdi mdi-check"></i>{{ $label }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($permisosRrhh->isNotEmpty())
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <p class="mb-2 text-muted"><small><i class="me-1 mdi mdi-account-search"></i>Permisos RRHH</small></p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($permisosRrhh as $label)
                                                            <span class="badge badge-primary">
                                                                <i class="me-1 mdi mdi-check"></i>{{ $label }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Estado</span>
                                                    @if ($user->is_active)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Registrado</span>
                                                    <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                                </div>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Última actualización</span>
                                                    <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                                </div>
                                            </div>

                                            @if (isset($user->dashboards))
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Dashboards asignados</span>
                                                        <span class="badge badge-info">{{ $user->dashboards->count() }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid-margin col-lg-12 stretch-card">
                                    @if (auth()->user()->isSuperAdmin() &&
                                            $user->id !== auth()->id() &&
                                            !in_array($user->roles->first()->name ?? '', ['super_admin']))
                                        <div class="border-danger card">
                                            <div class="card-body">
                                                <h5 class="mb-3 text-danger">
                                                    <i class="me-2 mdi-alert-circle-outline mdi"></i>
                                                    Zona de Peligro
                                                </h5>
                                                <p class="mb-3 text-muted small">
                                                    Eliminar este usuario es permanente y no se puede deshacer.
                                                </p>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-100 btn btn-danger"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?\n\nEsta acción no se puede deshacer.')">
                                                        <i class="me-1 mdi mdi-delete-forever"></i>Eliminar Usuario
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
        .d-flex.gap-2>*+* { margin-left: 0.5rem; }
        .input-group-text { background-color: #f8f9fa; border-color: #e3e6f0; }
        .btn-outline-secondary { border-color: #e3e6f0; }
        .btn-outline-secondary:hover { background-color: #f8f9fa; border-color: #e3e6f0; color: #6366f1; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const sedeField  = document.getElementById('sedeField');
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
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
                });
            }

            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmation  = document.getElementById('password_confirmation');
            if (togglePasswordConfirm && passwordConfirmation) {
                togglePasswordConfirm.addEventListener('click', function() {
                    const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordConfirmation.setAttribute('type', type);
                    this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
                });
            }

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