@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

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
                                                <i class="mdi mdi-account-group text-primary me-2"></i>
                                                Gestión de Usuarios Marketing
                                            </h3>
                                            <p class="text-muted mt-1">Administra consultores y sedes, genera links de
                                                encuesta</p>
                                        </div>
                                        <div>
                                            <a href="{{ route('marketing.users.create') }}"
                                                class="btn btn-success text-white me-2">
                                                <i class="mdi mdi-plus-circle me-1"></i>Nuevo Usuario
                                            </a>
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="mdi mdi-download me-1"></i>Exportar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alertas -->
                            @if (session('success'))
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="mdi mdi-check-circle me-2"></i>
                                            {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="mdi mdi-alert-circle me-2"></i>
                                            {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-xl-3 col-lg-6 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Total Usuarios</p>
                                                    <h3 class="rate-percentage">{{ $users->total() }}</h3>
                                                    <p class="text-primary d-flex align-items-center">
                                                        <i class="mdi mdi-account-multiple"></i>
                                                        <span class="ms-1">Registrados</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-primary-subtle rounded">
                                                    <i class="mdi mdi-account-multiple text-primary icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Consultores</p>
                                                    <h3 class="rate-percentage">
                                                        {{ $users->where('role', 'consultor')->count() }}</h3>
                                                    <p class="text-info d-flex align-items-center">
                                                        <i class="mdi mdi-account-tie"></i>
                                                        <span class="ms-1">Activos</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-info-subtle rounded">
                                                    <i class="mdi mdi-account-tie text-info icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Sedes</p>
                                                    <h3 class="rate-percentage">{{ $users->where('role', 'sede')->count() }}
                                                    </h3>
                                                    <p class="text-success d-flex align-items-center">
                                                        <i class="mdi mdi-office-building"></i>
                                                        <span class="ms-1">Ubicaciones</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-success-subtle rounded">
                                                    <i class="mdi mdi-office-building text-success icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-6 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="statistics-title">Total Encuestas</p>
                                                    <h3 class="rate-percentage">{{ $users->sum('total_surveys') }}</h3>
                                                    <p class="text-warning d-flex align-items-center">
                                                        <i class="mdi mdi-file-document"></i>
                                                        <span class="ms-1">Completadas</span>
                                                    </p>
                                                </div>
                                                <div class="icon-wrapper bg-warning-subtle rounded">
                                                    <i class="mdi mdi-file-document text-warning icon-lg"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros Colapsables -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="card-title mb-0">
                                                    <i class="mdi mdi-filter-variant text-primary me-2"></i>
                                                    Búsqueda y Filtros
                                                </h5>
                                                <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                                    <i class="mdi mdi-filter"></i>
                                                    {{ $search || $role || $status !== null ? 'Ocultar' : 'Mostrar' }}
                                                </button>
                                            </div>

                                            <div class="collapse {{ $search || $role || $status !== null ? 'show' : '' }}"
                                                id="filterCollapse">
                                                <form method="GET" action="{{ route('marketing.users.index') }}">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Buscar</label>
                                                            <input type="text" name="search" class="form-control"
                                                                placeholder="Nombre, email o ubicación..."
                                                                value="{{ $search }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Tipo</label>
                                                            <select name="role" class="form-select">
                                                                <option value="">Todos</option>
                                                                <option value="consultor"
                                                                    {{ $role == 'consultor' ? 'selected' : '' }}>
                                                                    Consultores</option>
                                                                <option value="sede"
                                                                    {{ $role == 'sede' ? 'selected' : '' }}>Sedes</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Estado</label>
                                                            <select name="status" class="form-select">
                                                                <option value="">Todos</option>
                                                                <option value="1"
                                                                    {{ $status === '1' ? 'selected' : '' }}>Activos
                                                                </option>
                                                                <option value="0"
                                                                    {{ $status === '0' ? 'selected' : '' }}>Inactivos
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="submit"
                                                                class="btn btn-primary text-white w-100">
                                                                <i class="mdi mdi-magnify me-1"></i> Buscar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Usuarios -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-format-list-bulleted text-primary me-2"></i>
                                                    Listado de Usuarios
                                                    <span class="badge badge-primary ms-2">{{ $users->total() }}</span>
                                                </h4>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Usuario</th>
                                                            <th>Tipo</th>
                                                            <th>Estado</th>
                                                            <th>Estadísticas</th>
                                                            <th style="width: 300px;">Link de Encuesta</th>
                                                            <th style="width: 200px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($users as $user)
                                                            <tr>
                                                                <td>
                                                                    <strong
                                                                        class="text-primary">#{{ $user->id }}</strong>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                                                            alt="profile">
                                                                        <div>
                                                                            <strong>{{ $user->name }}</strong><br>
                                                                            <small class="text-muted">
                                                                                <i class="mdi mdi-email"></i>
                                                                                {{ $user->email }}
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @if ($user->role === 'consultor')
                                                                        <span class="badge badge-primary">
                                                                            <i class="mdi mdi-account-tie"></i> Consultor
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-info">
                                                                            <i class="mdi mdi-office-building"></i> Sede
                                                                        </span>
                                                                        @if ($user->location)
                                                                            <br><small
                                                                                class="text-muted">{{ $user->location }}</small>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($user->is_active)
                                                                        <span class="badge badge-success">
                                                                            <i class="mdi mdi-check-circle"></i> Activo
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-danger">
                                                                            <i class="mdi mdi-close-circle"></i> Inactivo
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="mb-1">
                                                                        <i class="mdi mdi-file-document text-primary"></i>
                                                                        <strong>{{ $user->total_surveys }}</strong>
                                                                        encuestas
                                                                    </div>
                                                                    @if ($user->total_surveys > 0)
                                                                        <div>
                                                                            <i class="mdi mdi-star text-warning"></i>
                                                                            <strong>{{ number_format($user->average_rating, 2) }}</strong>
                                                                            promedio
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div
                                                                        class="input-group input-group-sm border rounded overflow-hidden">
                                                                        <input type="text"
                                                                            class="form-control border-0"
                                                                            value="{{ Str::limit($user->survey_url, 35) }}"
                                                                            readonly
                                                                            style="background: transparent; font-size: 0.75rem;">
                                                                        <button class="btn btn-primary text-white"
                                                                            type="button"
                                                                            onclick="copyToClipboard('{{ $user->survey_url }}', this)">
                                                                            <i class="mdi mdi-content-copy"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        <a href="{{ route('marketing.users.show', $user->id) }}"
                                                                            class="btn btn-sm btn-info"
                                                                            data-bs-toggle="tooltip" title="Ver Detalles">
                                                                            <i class="mdi mdi-eye"></i>
                                                                        </a>
                                                                        <a href="{{ route('marketing.users.edit', $user->id) }}"
                                                                            class="btn btn-sm btn-warning"
                                                                            data-bs-toggle="tooltip" title="Editar">
                                                                            <i class="mdi mdi-pencil"></i>
                                                                        </a>
                                                                        <button
                                                                            onclick="showQR('{{ $user->id }}', '{{ $user->name }}')"
                                                                            class="btn btn-sm btn-secondary"
                                                                            data-bs-toggle="tooltip" title="Ver QR">
                                                                            <i class="mdi mdi-qrcode"></i>
                                                                        </button>
                                                                        <form method="POST"
                                                                            action="{{ route('marketing.users.toggle-status', $user->id) }}"
                                                                            class="d-inline">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}"
                                                                                data-bs-toggle="tooltip"
                                                                                title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                                                                <i class="mdi mdi-power"></i>
                                                                            </button>
                                                                        </form>
                                                                        <div class="dropdown">
                                                                            <button
                                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                                type="button" data-bs-toggle="dropdown">
                                                                                <i class="mdi mdi-dots-vertical"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu">
                                                                                <li>
                                                                                    <form method="POST"
                                                                                        action="{{ route('marketing.users.regenerate-token', $user->id) }}"
                                                                                        class="d-inline"
                                                                                        onsubmit="return confirm('¿Regenerar token? El link anterior dejará de funcionar.')">
                                                                                        @csrf
                                                                                        <button type="submit"
                                                                                            class="dropdown-item">
                                                                                            <i
                                                                                                class="mdi mdi-refresh me-2"></i>
                                                                                            Regenerar Link
                                                                                        </button>
                                                                                    </form>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="{{ route('marketing.users.preview', $user->id) }}"
                                                                                        class="dropdown-item"
                                                                                        target="_blank">
                                                                                        <i
                                                                                            class="mdi mdi-eye-outline me-2"></i>
                                                                                        Vista Previa
                                                                                    </a>
                                                                                </li>
                                                                                <li>
                                                                                    <hr class="dropdown-divider">
                                                                                </li>
                                                                                <li>
                                                                                    <form method="POST"
                                                                                        action="{{ route('marketing.users.destroy', $user->id) }}"
                                                                                        class="d-inline"
                                                                                        onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit"
                                                                                            class="dropdown-item text-danger">
                                                                                            <i
                                                                                                class="mdi mdi-delete me-2"></i>
                                                                                            Eliminar
                                                                                        </button>
                                                                                    </form>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="7" class="text-center py-5">
                                                                    <i
                                                                        class="mdi mdi-inbox mdi-48px text-muted mb-3 d-block"></i>
                                                                    <h5 class="text-muted">No se encontraron usuarios</h5>
                                                                    <p class="text-muted mb-4">Crea tu primer usuario para
                                                                        comenzar</p>
                                                                    <a href="{{ route('marketing.users.create') }}"
                                                                        class="btn btn-primary text-white">
                                                                        <i class="mdi mdi-plus-circle me-1"></i>Crear
                                                                        Primer
                                                                        Usuario
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            @if ($users->hasPages())
                                                <div class="d-flex justify-content-center mt-4">
                                                    {{ $users->links() }}
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
        </div>
    </div>

    <!-- Modal QR Code -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-qrcode me-2"></i>
                        Código QR - <span id="qrUserName"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-4">Escanea este código para acceder directamente a la encuesta</p>
                    <div class="bg-light p-4 rounded mb-3">
                        <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="qrLink" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyFromModal()">
                            <i class="mdi mdi-content-copy me-1"></i> Copiar
                        </button>
                    </div>
                    <a id="downloadQR" href="" download class="btn btn-primary text-white w-100">
                        <i class="mdi mdi-download me-1"></i> Descargar Código QR
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Gap utility for older browsers */
        .gap-1>*+* {
            margin-left: 0.25rem;
        }

        /* Dropdown menu improvements */
        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: rgba(99, 102, 241, 0.1);
        }

        /* Input group styling */
        .input-group-sm .form-control {
            font-size: 0.75rem;
        }

        /* Tooltips improvement */
        .btn[data-bs-toggle="tooltip"] {
            cursor: pointer;
        }

        /* Modal improvements */
        .modal-content {
            border: none;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });

        // Copiar link al portapapeles
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="mdi mdi-check"></i>';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar: ', err);
                alert('Error al copiar el link');
            });
        }

        // Mostrar modal QR
        function showQR(userId, userName) {
            fetch(`/users/${userId}/qr`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qrUserName').textContent = userName;
                        document.getElementById('qrImage').src = data.qr_url;
                        document.getElementById('qrLink').value = data.survey_url;
                        document.getElementById('downloadQR').href = data.qr_url;

                        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                        modal.show();
                    }
                })
                .catch(err => {
                    console.error('Error al generar QR:', err);
                    alert('Error al generar el código QR');
                });
        }

        // Copiar desde modal
        function copyFromModal() {
            const input = document.getElementById('qrLink');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="mdi mdi-check me-1"></i> ¡Copiado!';
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-success');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-primary');
                }, 2000);
            });
        }

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
@endpush
