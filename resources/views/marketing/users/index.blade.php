@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/usersencuesta.css') }}">
@endpush

@section('content')

    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-people-fill"></i> Gestión de Usuarios</h1>
                    <p class="text-muted mb-0">Administra consultores y sedes, genera links de encuesta</p>
                </div>
                <div>
                    <a href="{{ route('marketing.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Usuario
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Exportar CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if (session('success'))
            <div class="alert alert-success-custom alert-custom alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error-custom alert-custom alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filtros y Búsqueda -->
        <div class="filters-card">
            <h5 class="mb-4"><i class="bi bi-funnel"></i> Búsqueda y Filtros</h5>
            <form method="GET" action="{{ route('marketing.users.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Nombre, email o ubicación..."
                            value="{{ $search }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="role" class="form-select">
                            <option value="">Todos</option>
                            <option value="consultor" {{ $role == 'consultor' ? 'selected' : '' }}>Consultores</option>
                            <option value="sede" {{ $role == 'sede' ? 'selected' : '' }}>Sedes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ $status === '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Listado de Usuarios
                    <span class="badge bg-primary">{{ $users->total() }}</span>
                </h5>
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
                            <th>Link de Encuesta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>#{{ $user->id }}</strong></td>
                                <td>
                                    <div>
                                        <strong>{{ $user->name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> {{ $user->email }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->role === 'consultor')
                                        <span class="badge bg-primary">
                                            <i class="bi bi-person"></i> Consultor
                                        </span>
                                    @else
                                        <span class="badge bg-info">
                                            <i class="bi bi-building"></i> Sede
                                        </span>
                                        @if ($user->location)
                                            <br><small class="text-muted">{{ $user->location }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge-status badge-active">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge-status badge-inactive">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="stats-mini">
                                        <i class="bi bi-file-earmark-text"></i> {{ $user->total_surveys }} encuestas
                                    </div>
                                    @if ($user->total_surveys > 0)
                                        <div class="stats-mini">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <span
                                                class="rating-display">{{ number_format($user->average_rating, 2) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="link-container" style="max-width: 300px;">
                                        <span class="link-text" title="{{ $user->survey_url }}">
                                            {{ Str::limit($user->survey_url, 40) }}
                                        </span>
                                        <button class="copy-btn"
                                            onclick="copyToClipboard('{{ $user->survey_url }}', this)">
                                            <i class="bi bi-clipboard"></i> Copiar
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('marketing.users.show', $user->id) }}"
                                            class="btn btn-action btn-sm btn-info" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('marketing.users.edit', $user->id) }}"
                                            class="btn btn-action btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="showQR('{{ $user->id }}', '{{ $user->name }}')"
                                            class="btn btn-action btn-sm btn-secondary" title="Ver QR">
                                            <i class="bi bi-qr-code"></i>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('marketing.users.toggle-status', $user->id) }}"
                                            style="display: inline;">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-action btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}"
                                                title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                                <i class="bi bi-power"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="btn-group mt-1" role="group">
                                        <form method="POST"
                                            action="{{ route('marketing.users.regenerate-token', $user->id) }}"
                                            style="display: inline;"
                                            onsubmit="return confirm('¿Regenerar token? El link anterior dejará de funcionar.')">
                                            @csrf
                                            <button type="submit" class="btn btn-action btn-sm btn-dark"
                                                title="Regenerar Link">
                                                <i class="bi bi-arrow-clockwise"></i> Regenerar
                                            </button>
                                        </form>
                                        <a href="{{ route('marketing.users.preview', $user->id) }}"
                                            class="btn btn-action btn-sm btn-primary" target="_blank"
                                            title="Vista Previa">
                                            <i class="bi bi-eye-fill"></i> Preview
                                        </a>
                                        <form method="POST" action="{{ route('marketing.users.destroy', $user->id) }}"
                                            style="display: inline;"
                                            onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action btn-sm btn-danger"
                                                title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-3">No se encontraron usuarios</p>
                                    <a href="{{ route('marketing.users.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Crear Primer Usuario
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

    <!-- Modal QR Code -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-qr-code"></i> Código QR - <span id="qrUserName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center qr-modal">
                    <p class="text-muted">Escanea este código para acceder directamente a la encuesta</p>
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid">
                    <div class="link-container mt-3">
                        <span class="link-text" id="qrLink"></span>
                        <button class="copy-btn" onclick="copyFromModal()">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>
                    <a id="downloadQR" href="" download class="btn btn-primary mt-3">
                        <i class="bi bi-download"></i> Descargar QR
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copiar link al portapapeles
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i> ¡Copiado!';
                button.style.background = '#4CAF50';

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = '';
                }, 2000);
            }).catch(err => {
                alert('Error al copiar: ' + err);
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
                        document.getElementById('qrLink').textContent = data.survey_url;
                        document.getElementById('downloadQR').href = data.qr_url;

                        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                        modal.show();
                    }
                })
                .catch(err => {
                    alert('Error al generar QR: ' + err);
                });
        }

        // Copiar desde modal
        function copyFromModal() {
            const text = document.getElementById('qrLink').textContent;
            copyToClipboard(text, event.target);
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
