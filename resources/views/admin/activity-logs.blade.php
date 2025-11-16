@extends('layouts.app')

@section('title', 'Logs de Actividad')

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
                                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm me-3">
                                                <i class="mdi mdi-arrow-left"></i>
                                            </a>
                                            <div>
                                                <h3 class="rate-percentage mb-0">
                                                    <i class="mdi mdi-format-list-bulleted text-primary me-2"></i>
                                                    Logs de Actividad
                                                </h3>
                                                <p class="text-muted mt-1">Historial completo de acciones del sistema</p>
                                            </div>
                                        </div>
                                        <div>
                                            <button id="exportLogs" class="btn btn-success btn-sm">
                                                <i class="mdi mdi-download me-1"></i>Exportar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros -->
                            <div class="row mb-4">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-filter-variant text-primary me-2"></i>
                                                Filtros de Búsqueda
                                            </h4>
                                            <form method="GET" id="filterForm">
                                                <div class="row">
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Usuario</label>
                                                        <select name="user_id" class="form-select">
                                                            <option value="">Todos los usuarios</option>
                                                            @if (isset($users))
                                                                @foreach ($users as $user)
                                                                    <option value="{{ $user->id }}"
                                                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                                        {{ $user->name }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Acción</label>
                                                        <select name="action" class="form-select">
                                                            <option value="">Todas las acciones</option>
                                                            <option value="login"
                                                                {{ request('action') == 'login' ? 'selected' : '' }}>Login
                                                            </option>
                                                            <option value="logout"
                                                                {{ request('action') == 'logout' ? 'selected' : '' }}>Logout
                                                            </option>
                                                            <option value="create"
                                                                {{ request('action') == 'create' ? 'selected' : '' }}>Crear
                                                            </option>
                                                            <option value="update"
                                                                {{ request('action') == 'update' ? 'selected' : '' }}>
                                                                Actualizar</option>
                                                            <option value="delete"
                                                                {{ request('action') == 'delete' ? 'selected' : '' }}>
                                                                Eliminar</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Desde</label>
                                                        <input type="date" name="date_from"
                                                            value="{{ request('date_from') }}" class="form-control">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Hasta</label>
                                                        <input type="date" name="date_to"
                                                            value="{{ request('date_to') }}" class="form-control">
                                                    </div>

                                                    <div class="col-md-9 mb-3">
                                                        <label class="form-label">Buscar en descripción o IP</label>
                                                        <input type="text" name="search"
                                                            value="{{ request('search') }}" class="form-control"
                                                            placeholder="Buscar por descripción, IP...">
                                                    </div>

                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label d-block">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="mdi mdi-magnify me-1"></i>Buscar
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.activity-logs') }}"
                                                        class="btn btn-light btn-sm">
                                                        <i class="mdi mdi-refresh me-1"></i>Limpiar Filtros
                                                    </a>
                                                    <span class="text-muted ms-3 align-self-center">
                                                        <i class="mdi mdi-information-outline me-1"></i>
                                                        Mostrando {{ $logs->count() }} de {{ $logs->total() }} registros
                                                    </span>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas Rápidas -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded me-3">
                                                    <i class="mdi mdi-file-document text-primary mdi-24px"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Total de Logs</small>
                                                    <strong>{{ $logs->total() }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-success-subtle rounded me-3">
                                                    <i class="mdi mdi-login text-success mdi-24px"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Logins Hoy</small>
                                                    <strong id="loginsToday">-</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-danger-subtle rounded me-3">
                                                    <i class="mdi mdi-delete text-danger mdi-24px"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Eliminaciones</small>
                                                    <strong id="deletesToday">-</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-info-subtle rounded me-3">
                                                    <i class="mdi mdi-account-multiple text-info mdi-24px"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Usuarios Activos</small>
                                                    <strong id="activeUsers">-</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Logs -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">Registros de Actividad</h4>

                                            @if ($logs->isEmpty())
                                                <div class="text-center py-5">
                                                    <i
                                                        class="mdi mdi-file-document-box-outline mdi-48px text-muted mb-3 d-block"></i>
                                                    <h4 class="mb-3">No se encontraron registros</h4>
                                                    <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                                                </div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    <i class="mdi mdi-calendar-clock me-1"></i>Fecha/Hora
                                                                </th>
                                                                <th>
                                                                    <i class="mdi mdi-account me-1"></i>Usuario
                                                                </th>
                                                                <th>
                                                                    <i class="mdi mdi-tag me-1"></i>Acción
                                                                </th>
                                                                <th>
                                                                    <i class="mdi mdi-information me-1"></i>Descripción
                                                                </th>
                                                                <th>
                                                                    <i class="mdi mdi-web me-1"></i>IP
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($logs as $log)
                                                                <tr>
                                                                    <td>
                                                                        <div>
                                                                            <span
                                                                                class="text-muted">{{ $log->created_at->format('d/m/Y') }}</span><br>
                                                                            <small
                                                                                class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <img class="img-xs rounded-circle me-2"
                                                                                src="https://ui-avatars.com/api/?name={{ urlencode($log->user->name) }}&background=6366f1&color=fff"
                                                                                alt="profile">
                                                                            <div>
                                                                                <span
                                                                                    class="fw-bold">{{ $log->user->name }}</span><br>
                                                                                <small
                                                                                    class="text-muted">{{ $log->user->email }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge 
                                                                        @if (str_contains(strtolower($log->action), 'login')) badge-success
                                                                        @elseif(str_contains(strtolower($log->action), 'logout')) badge-secondary
                                                                        @elseif(str_contains(strtolower($log->action), 'create')) badge-primary
                                                                        @elseif(str_contains(strtolower($log->action), 'delete')) badge-danger
                                                                        @elseif(str_contains(strtolower($log->action), 'update')) badge-info
                                                                        @else badge-dark @endif">
                                                                            {{ $log->action }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="text-muted description-cell">
                                                                            <span
                                                                                class="description-text">{{ Str::limit($log->description, 60) }}</span>
                                                                            @if (strlen($log->description) > 60)
                                                                                <a href="#"
                                                                                    class="view-full d-block mt-1"
                                                                                    data-description="{{ $log->description }}">
                                                                                    <small>Ver más</small>
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <code
                                                                            class="text-dark">{{ $log->ip_address }}</code>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Paginación -->
                                                <div class="mt-4">
                                                    {{ $logs->links() }}
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

    <!-- Modal para descripción completa -->
    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-information-outline me-2"></i>Descripción Completa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="fullDescription" class="mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Fix para gap */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Icon wrappers */
        .icon-wrapper {
            min-width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary-subtle {
            background-color: rgba(99, 102, 241, 0.1) !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        /* Table columns widths */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 130px;
            white-space: nowrap;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 220px;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 120px;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: auto;
            min-width: 250px;
            max-width: 400px;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 140px;
            white-space: nowrap;
        }

        /* Description cell */
        .description-cell {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }

        .description-text {
            display: block;
            line-height: 1.5;
        }

        /* View more link */
        .view-full {
            color: #6366f1;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .view-full:hover {
            text-decoration: underline;
        }

        /* Pagination styling */
        nav[role="navigation"] {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        nav[role="navigation"] p {
            font-size: 0.875rem;
            color: #6c757d;
            margin: 0;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 0.25rem;
        }

        .pagination .page-item {
            display: inline-block;
        }

        .pagination .page-link {
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #6366f1;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination .page-link:hover {
            color: #4f46e5;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .pagination .page-item.active .page-link {
            color: #fff;
            background-color: #6366f1;
            border-color: #6366f1;
            font-weight: 600;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Responsive pagination */
        @media (max-width: 576px) {
            nav[role="navigation"] {
                flex-direction: column;
                gap: 1rem;
            }

            .pagination .page-link {
                padding: 0.375rem 0.625rem;
                font-size: 0.8125rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View full description
            document.querySelectorAll('.view-full').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const description = this.getAttribute('data-description');
                    document.getElementById('fullDescription').textContent = description;
                    new bootstrap.Modal(document.getElementById('descriptionModal')).show();
                });
            });

            // Export logs
            document.getElementById('exportLogs')?.addEventListener('click', function() {
                alert('Funcionalidad de exportación - Implementar en backend');
            });

            // Calculate quick stats from current page
            const rows = document.querySelectorAll('tbody tr');
            let loginsCount = 0;
            let deletesCount = 0;
            const uniqueUsers = new Set();

            rows.forEach(row => {
                const badge = row.querySelector('.badge');
                const userName = row.querySelector('.fw-bold')?.textContent;

                if (badge) {
                    const action = badge.textContent.toLowerCase();
                    if (action.includes('login')) loginsCount++;
                    if (action.includes('delete')) deletesCount++;
                }

                if (userName) uniqueUsers.add(userName);
            });

            document.getElementById('loginsToday').textContent = loginsCount;
            document.getElementById('deletesToday').textContent = deletesCount;
            document.getElementById('activeUsers').textContent = uniqueUsers.size;
        });
    </script>
@endsection
