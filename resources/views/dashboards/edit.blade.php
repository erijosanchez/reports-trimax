@extends('layouts.app')

@section('title', 'Editar Dashboard')

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
                                            <a href="{{ route('dashboards.index') }}" class="btn btn-light btn-sm me-3">
                                                <i class="mdi mdi-arrow-left"></i>
                                            </a>
                                            <div>
                                                <h3 class="rate-percentage mb-0">
                                                    <i class="mdi mdi-pencil-box text-primary me-2"></i>
                                                    {{ $dashboard->name }}
                                                </h3>
                                                <p class="text-muted mt-1">Editar configuración del dashboard</p>
                                            </div>
                                        </div>
                                        <div>
                                            @if ($dashboard->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs Navigation -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <ul class="nav nav-tabs nav-tabs-custom mb-2" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#info"
                                                role="tab" aria-controls="info" aria-selected="true">
                                                <i class="mdi mdi-information-outline me-2"></i>
                                                Información
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users"
                                                role="tab" aria-controls="users" aria-selected="false">
                                                <i class="mdi mdi-account-multiple me-2"></i>
                                                Asignar Usuarios
                                                <span
                                                    class="badge badge-primary ms-2">{{ $dashboard->users->count() }}</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Tabs Content -->
                            <div class="tab-content">

                                <!-- TAB: Información del Dashboard -->
                                <div class="tab-pane fade show active" id="info" role="tabpanel"
                                    aria-labelledby="info-tab">
                                    <div class="row">
                                        <div class="col-lg-12 mx-auto grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-chart-box-plus-outline text-primary me-2"></i>
                                                        Configuración del Dashboard
                                                    </h4>

                                                    <form method="POST"
                                                        action="{{ route('dashboards.update', $dashboard->id) }}">
                                                        @csrf
                                                        @method('PUT')

                                                        <!-- Nombre -->
                                                        <div class="mb-4">
                                                            <label class="form-label">
                                                                Nombre del Dashboard <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-chart-line"></i>
                                                                </span>
                                                                <input type="text" name="name"
                                                                    class="form-control @error('name') is-invalid @enderror"
                                                                    value="{{ old('name', $dashboard->name) }}" required
                                                                    placeholder="Ej: Dashboard de Ventas Mensual">
                                                            </div>
                                                            @error('name')
                                                                <div class="text-danger mt-1">
                                                                    <small>{{ $message }}</small>
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <!-- Descripción -->
                                                        <div class="mb-4">
                                                            <label class="form-label">
                                                                Descripción
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-text"></i>
                                                                </span>
                                                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                                                                    placeholder="Breve descripción del dashboard...">{{ old('description', $dashboard->description) }}</textarea>
                                                            </div>
                                                            @error('description')
                                                                <div class="text-danger mt-1">
                                                                    <small>{{ $message }}</small>
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <!-- Link de Power BI -->
                                                        <div class="mb-4">
                                                            <label class="form-label">
                                                                Link de Power BI <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="mdi mdi-link-variant"></i>
                                                                </span>
                                                                <input type="url" name="powerbi_link"
                                                                    class="form-control @error('powerbi_link') is-invalid @enderror"
                                                                    value="{{ old('powerbi_link', $dashboard->decrypted_link) }}"
                                                                    required
                                                                    placeholder="https://app.powerbi.com/view?r=...">
                                                            </div>
                                                            @error('powerbi_link')
                                                                <div class="text-danger mt-1">
                                                                    <small>{{ $message }}</small>
                                                                </div>
                                                            @enderror
                                                        </div>

                                                        <!-- Estado -->
                                                        <div class="mb-4">
                                                            <div class="form-check form-check-success">
                                                                <label class="form-check-label">
                                                                    <input type="checkbox" name="is_active" value="1"
                                                                        class="form-check-input"
                                                                        {{ old('is_active', $dashboard->is_active) ? 'checked' : '' }}>
                                                                    Dashboard activo
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                            <small class="text-muted ms-4">
                                                                Los dashboards activos estarán visibles para los usuarios
                                                                asignados
                                                            </small>
                                                        </div>

                                                        <!-- Botones -->
                                                        <div class="d-flex gap-2 justify-content-between pt-3 border-top">
                                                            <a href="{{ route('dashboards.index') }}"
                                                                class="btn btn-light">
                                                                <i class="mdi mdi-close me-1"></i>Cancelar
                                                            </a>
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ route('dashboards.show', $dashboard->id) }}"
                                                                    class="btn btn-info">
                                                                    <i class="mdi mdi-eye me-1"></i>Ver Dashboard
                                                                </a>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="mdi mdi-content-save me-1"></i>Actualizar
                                                                    Dashboard
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    <!-- Zona de Peligro -->
                                                    @if (auth()->user()->isSuperAdmin())
                                                        <div class="mt-5 pt-4 border-top">
                                                            <div class="alert alert-danger d-flex align-items-start"
                                                                role="alert">
                                                                <i class="mdi mdi-alert-circle-outline me-3 mdi-24px"></i>
                                                                <div class="flex-grow-1">
                                                                    <h5 class="alert-heading mb-2">Zona de Peligro</h5>
                                                                    <p class="mb-3">
                                                                        Eliminar este dashboard es una acción permanente y
                                                                        no se puede deshacer.
                                                                        Todos los usuarios perderán el acceso.
                                                                    </p>
                                                                    <form method="POST"
                                                                        action="{{ route('dashboards.destroy', $dashboard->id) }}"
                                                                        onsubmit="return confirm('¿Estás seguro de eliminar este dashboard?\n\nEsta acción no se puede deshacer.');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger">
                                                                            <i
                                                                                class="mdi mdi-delete-forever me-1"></i>Eliminar
                                                                            Dashboard
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB: Asignar Usuarios -->
                                <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                                    <div class="row">
                                        <div class="col-lg-10 mx-auto grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-account-multiple text-primary me-2"></i>
                                                        Gestión de Usuarios
                                                    </h4>

                                                    <form method="POST"
                                                        action="{{ route('dashboards.assign-users', $dashboard->id) }}">
                                                        @csrf

                                                        <!-- Select All -->
                                                        <div class="alert alert-light border d-flex align-items-center mb-3"
                                                            role="alert">
                                                            <div class="form-check form-check-primary mb-0 flex-grow-1">
                                                                <label class="form-check-label">
                                                                    <input type="checkbox" id="select-all"
                                                                        class="form-check-input">
                                                                    <strong>Seleccionar Todos los Usuarios</strong>
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                            <div class="ms-3">
                                                                <span class="badge badge-primary"
                                                                    id="selected-count">{{ count($assignedUserIds) }}</span>
                                                                <span class="text-muted ms-1">seleccionados</span>
                                                            </div>
                                                        </div>

                                                        <!-- Lista de Usuarios -->
                                                        <div class="user-list-container border rounded p-3"
                                                            style="max-height: 500px; overflow-y: auto;">
                                                            @foreach ($allUsers as $user)
                                                                <div class="user-item border-bottom py-3">
                                                                    <div class="form-check form-check-primary">
                                                                        <label class="form-check-label w-100">
                                                                            <div class="d-flex align-items-center">
                                                                                <input type="checkbox" name="users[]"
                                                                                    value="{{ $user->id }}"
                                                                                    class="form-check-input user-checkbox"
                                                                                    {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }}>
                                                                                <i class="input-helper"></i>

                                                                                <img class="img-xs rounded-circle ms-2 me-3"
                                                                                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                                                                    alt="profile">

                                                                                <div class="flex-grow-1">
                                                                                    <p class="mb-0 fw-bold">
                                                                                        {{ $user->name }}</p>
                                                                                    <small
                                                                                        class="text-muted">{{ $user->email }}</small>
                                                                                </div>

                                                                                <div>
                                                                                    @foreach ($user->roles as $role)
                                                                                        <span
                                                                                            class="badge badge-info">{{ $role->name }}</span>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Resumen y Botones -->
                                                        <div class="alert alert-info mt-4" role="alert">
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-information-outline me-2 mdi-24px"></i>
                                                                <div>
                                                                    <strong>Total de usuarios seleccionados: <span
                                                                            id="selected-count-bottom">{{ count($assignedUserIds) }}</span></strong>
                                                                    <p class="mb-0 mt-1">
                                                                        Los usuarios seleccionados tendrán acceso a este
                                                                        dashboard en su panel.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                                            <a href="{{ route('dashboards.index') }}"
                                                                class="btn btn-light">
                                                                <i class="mdi mdi-close me-1"></i>Cancelar
                                                            </a>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="mdi mdi-check-circle me-1"></i>Guardar
                                                                Asignaciones
                                                            </button>
                                                        </div>
                                                    </form>
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
        /* Fix para gap */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Tabs personalizados */
        .nav-tabs-custom {
            border-bottom: 2px solid #e3e6f0;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #6366f1;
            background-color: #f8f9fa;
        }

        .nav-tabs-custom .nav-link.active {
            color: #6366f1;
            background-color: transparent;
            border-bottom: 3px solid #6366f1;
            margin-bottom: -2px;
        }

        /* Input groups */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
        }

        /* Textarea */
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* User list */
        .user-list-container {
            background-color: #f8f9fa;
        }

        .user-item {
            transition: background-color 0.2s;
            margin-left: -0.75rem;
            margin-right: -0.75rem;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .user-item:hover {
            background-color: #fff;
        }

        .user-item:last-child {
            border-bottom: none !important;
        }

        /* Scrollbar personalizado */
        .user-list-container::-webkit-scrollbar {
            width: 8px;
        }

        .user-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .user-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .user-list-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const selectedCountTop = document.getElementById('selected-count');
            const selectedCountBottom = document.getElementById('selected-count-bottom');

            // Toggle all checkboxes
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateCount();
            });

            // Update count when individual checkbox changes
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllState();
                    updateCount();
                });
            });

            // Update "Select All" checkbox state
            function updateSelectAllState() {
                const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
                const totalCount = userCheckboxes.length;
                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            }

            // Update selected count
            function updateCount() {
                const count = document.querySelectorAll('.user-checkbox:checked').length;
                selectedCountTop.textContent = count;
                selectedCountBottom.textContent = count;
            }

            // Initialize
            updateSelectAllState();
            updateCount();
        });
    </script>
@endsection
