@extends('layouts.app')

@section('title', 'Gestionar Usuarios')

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
                                                Gestión de Usuarios
                                            </h3>
                                            <p class="text-muted mt-1">Administra los usuarios del sistema</p>
                                        </div>
                                        <div>
                                            <a class="btn btn-success text-white" href="{{ route('admin.users.create') }}">
                                                <i class="mdi mdi-account-plus me-1"></i>Nuevo Usuario
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tabla de Usuarios -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-format-list-bulleted text-primary me-2"></i>
                                                    Listado de Usuarios
                                                    <span class="badge badge-primary ms-2">{{ $users->total() }}</span>
                                                </h4>
                                            </div>

                                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                                <table class="table table-hover">
                                                    <thead
                                                        style="position: sticky; top: 0; background: white; z-index: 10;">
                                                        <tr>
                                                            <th>Usuario</th>
                                                            <th>Email</th>
                                                            <th>Rol</th>
                                                            <th>Estado</th>
                                                            <th>Sesiones</th>
                                                            <th>Último Login</th>
                                                            <th style="width: 120px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($users as $user)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                                                            alt="profile">
                                                                        <strong>{{ $user->name }}</strong>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $user->email }}</small>
                                                                </td>
                                                                <td>
                                                                    @foreach ($user->roles as $role)
                                                                        <span class="badge badge-primary">
                                                                            {{ $role->name }}
                                                                        </span>
                                                                    @endforeach
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
                                                                    <span class="badge badge-info">
                                                                        {{ $user->sessions_count }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex gap-1">
                                                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                                                            class="btn btn-sm btn-warning"
                                                                            data-bs-toggle="tooltip" title="Editar">
                                                                            <i class="mdi mdi-pencil"></i>
                                                                        </a>
                                                                        @if ($user->id !== auth()->id())
                                                                            <form method="POST"
                                                                                action="{{ route('admin.users.destroy', $user->id) }}"
                                                                                class="d-inline">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                    onclick="return confirm('¿Eliminar usuario {{ $user->name }}?')"
                                                                                    class="btn btn-sm btn-danger"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Eliminar">
                                                                                    <i class="mdi mdi-delete"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            @if ($users->hasPages())
                                                <div class="d-flex justify-content-between align-items-center mt-4">
                                                    <div>
                                                        <small class="text-muted">
                                                            Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }}
                                                            de {{ $users->total() }} usuarios
                                                        </small>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Lateral - Usuarios Online -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-account-clock text-success me-2"></i>
                                                Usuarios Online
                                                <span class="badge badge-success ms-2">{{ $usersOnline->count() }}</span>
                                            </h4>
                                            <p class="text-muted mb-4">
                                                <small>Usuarios activos en los últimos 5 minutos</small>
                                            </p>

                                            <div style="max-height: 500px; overflow-y: auto;">
                                                @if ($usersOnline->isEmpty())
                                                    <div class="text-center py-5">
                                                        <i
                                                            class="mdi mdi-account-off-outline mdi-48px text-muted mb-3 d-block"></i>
                                                        <p class="text-muted">No hay usuarios online</p>
                                                    </div>
                                                @else
                                                    @foreach ($usersOnline as $user)
                                                        @foreach ($user->activeSessions as $session)
                                                            <div class="d-flex align-items-center border-bottom py-3">
                                                                <div class="position-relative me-3">
                                                                    <img class="img-xs rounded-circle"
                                                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                                                        alt="profile">
                                                                    <span class="online-indicator pulse"></span>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <p class="mb-0 fw-bold">{{ $user->name }}</p>
                                                                    <small class="text-muted">
                                                                        <i class="mdi mdi-clock-outline me-1"></i>
                                                                        {{ $session->last_activity->diffForHumans() }}
                                                                    </small>
                                                                </div>
                                                                <small class="text-muted">
                                                                    {{ $session->login_at->diffForHumans(null, true) }}
                                                                </small>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
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
    </div>

    <style>
        /* Gap utility for older browsers */
        .gap-1>*+* {
            margin-left: 0.25rem;
        }

        /* Sticky header for table */
        .table-responsive thead {
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar,
        .card-body>div::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track,
        .card-body>div::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .card-body>div::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover,
        .card-body>div::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Online indicator animation */
        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background-color: #25D366;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .online-indicator.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(37, 211, 102, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }

        /* Table improvements */
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
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
    </script>
@endpush
