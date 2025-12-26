<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - TRIMAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1565C0;
            --success-color: #4CAF50;
            --danger-color: #F44336;
        }

        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: var(--primary-color);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            margin: 0 10px;
        }

        .nav-link:hover {
            color: white !important;
        }

        .main-content {
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        .table thead {
            background: #f8f9fa;
        }

        .table thead th {
            border: none;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background: #0D47A1;
        }

        .btn-action {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin: 2px;
            border: none;
            transition: all 0.3s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .badge-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-active {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-inactive {
            background: #FFEBEE;
            color: #C62828;
        }

        .link-container {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border: 2px dashed #ddd;
            font-family: monospace;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .link-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .copy-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .copy-btn:hover {
            background: #0D47A1;
        }

        .alert-custom {
            border-left: 4px solid;
            border-radius: 8px;
            padding: 15px 20px;
        }

        .alert-success-custom {
            background: #E8F5E9;
            border-color: var(--success-color);
            color: #2E7D32;
        }

        .alert-error-custom {
            background: #FFEBEE;
            border-color: var(--danger-color);
            color: #C62828;
        }

        .stats-mini {
            display: inline-block;
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            margin-right: 8px;
        }

        .rating-display {
            font-weight: 700;
            color: var(--primary-color);
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .qr-modal img {
            max-width: 300px;
            margin: 20px auto;
            display: block;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .btn-action {
                padding: 6px 10px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <span class="navbar-brand">TRIMAX Admin</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard.index') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('users.index') }}">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-people-fill"></i> Gestión de Usuarios</h1>
                    <p class="text-muted mb-0">Administra consultores y sedes, genera links de encuesta</p>
                </div>
                <div>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Usuario
                    </a>
                    <a href="{{ route('users.export') }}" class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Exportar CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if(session('success'))
        <div class="alert alert-success-custom alert-custom alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error-custom alert-custom alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Filtros y Búsqueda -->
        <div class="filters-card">
            <h5 class="mb-4"><i class="bi bi-funnel"></i> Búsqueda y Filtros</h5>
            <form method="GET" action="{{ route('users.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" placeholder="Nombre, email o ubicación..." value="{{ $search }}">
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
                                @if($user->role === 'consultor')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-person"></i> Consultor
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="bi bi-building"></i> Sede
                                    </span>
                                    @if($user->location)
                                    <br><small class="text-muted">{{ $user->location }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
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
                                @if($user->total_surveys > 0)
                                <div class="stats-mini">
                                    <i class="bi bi-star-fill text-warning"></i> 
                                    <span class="rating-display">{{ number_format($user->average_rating, 2) }}</span>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="link-container" style="max-width: 300px;">
                                    <span class="link-text" title="{{ $user->survey_url }}">
                                        {{ Str::limit($user->survey_url, 40) }}
                                    </span>
                                    <button class="copy-btn" onclick="copyToClipboard('{{ $user->survey_url }}', this)">
                                        <i class="bi bi-clipboard"></i> Copiar
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-action btn-sm btn-info" title="Ver Detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-action btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="showQR('{{ $user->id }}', '{{ $user->name }}')" class="btn btn-action btn-sm btn-secondary" title="Ver QR">
                                        <i class="bi bi-qr-code"></i>
                                    </button>
                                    <form method="POST" action="{{ route('users.toggle-status', $user->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-action btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}" title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="btn-group mt-1" role="group">
                                    <form method="POST" action="{{ route('users.regenerate-token', $user->id) }}" style="display: inline;" onsubmit="return confirm('¿Regenerar token? El link anterior dejará de funcionar.')">
                                        @csrf
                                        <button type="submit" class="btn btn-action btn-sm btn-dark" title="Regenerar Link">
                                            <i class="bi bi-arrow-clockwise"></i> Regenerar
                                        </button>
                                    </form>
                                    <a href="{{ route('users.preview', $user->id) }}" class="btn btn-action btn-sm btn-primary" target="_blank" title="Vista Previa">
                                        <i class="bi bi-eye-fill"></i> Preview
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', $user->id) }}" style="display: inline;" onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-action btn-sm btn-danger" title="Eliminar">
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
                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Crear Primer Usuario
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($users->hasPages())
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
</body>
</html>
