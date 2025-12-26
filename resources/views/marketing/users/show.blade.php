<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - TRIMAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #1565C0;
            --success-color: #4CAF50;
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
        }

        .main-content {
            padding: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 700;
        }

        .user-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .user-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .badge-large {
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card .label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }

        .link-box {
            background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .link-display {
            background: rgba(255,255,255,0.2);
            padding: 15px 20px;
            border-radius: 10px;
            font-family: monospace;
            word-break: break-all;
            margin: 15px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .qr-container {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .qr-container img {
            max-width: 300px;
            border: 5px solid white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .table-responsive {
            margin-top: 20px;
        }

        .badge-rating {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }

        .badge-muy-feliz {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-feliz {
            background: #E3F2FD;
            color: #1565C0;
        }

        .badge-insatisfecho {
            background: #FFF3E0;
            color: #E65100;
        }

        .badge-muy-insatisfecho {
            background: #FFEBEE;
            color: #C62828;
        }

        .alert-success-custom {
            background: #E8F5E9;
            border-left: 4px solid var(--success-color);
            color: #2E7D32;
            padding: 15px 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
            <span class="navbar-brand">TRIMAX Admin</span>
        </div>
    </nav>

    <div class="main-content">
        @if(session('success'))
        <div class="alert alert-success-custom alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Header de Usuario -->
        <div class="user-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1><i class="bi bi-person-circle"></i> {{ $user->name }}</h1>
                    <p class="mb-3">
                        <i class="bi bi-envelope"></i> {{ $user->email }}
                    </p>
                    <div class="d-flex gap-2">
                        @if($user->role === 'consultor')
                            <span class="badge-large bg-primary">
                                <i class="bi bi-person"></i> Consultor
                            </span>
                        @else
                            <span class="badge-large bg-info">
                                <i class="bi bi-building"></i> Sede - {{ $user->location }}
                            </span>
                        @endif

                        @if($user->is_active)
                            <span class="badge-large bg-success">
                                <i class="bi bi-check-circle"></i> Activo
                            </span>
                        @else
                            <span class="badge-large bg-danger">
                                <i class="bi bi-x-circle"></i> Inactivo
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📊</div>
                <div class="number">{{ $stats['total_surveys'] }}</div>
                <div class="label">Total Encuestas</div>
            </div>
            <div class="stat-card">
                <div class="icon">⭐</div>
                <div class="number">{{ number_format($stats['average_rating'], 2) }}</div>
                <div class="label">Promedio</div>
            </div>
            <div class="stat-card">
                <div class="icon">😊</div>
                <div class="number">{{ $stats['muy_feliz'] }}</div>
                <div class="label">Muy Feliz</div>
            </div>
            <div class="stat-card">
                <div class="icon">🙂</div>
                <div class="number">{{ $stats['feliz'] }}</div>
                <div class="label">Feliz</div>
            </div>
            <div class="stat-card">
                <div class="icon">😐</div>
                <div class="number">{{ $stats['insatisfecho'] }}</div>
                <div class="label">Insatisfecho</div>
            </div>
            <div class="stat-card">
                <div class="icon">😞</div>
                <div class="number">{{ $stats['muy_insatisfecho'] }}</div>
                <div class="label">Muy Insatisfecho</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Link de Encuesta -->
                <div class="link-box">
                    <h3 style="color: white;"><i class="bi bi-link-45deg"></i> Link de Encuesta Única</h3>
                    <p style="color: rgba(255,255,255,0.9);">Comparte este link con tus clientes para recibir feedback</p>
                    
                    <div class="link-display">
                        {{ $user->survey_url }}
                    </div>

                    <div class="action-buttons">
                        <button onclick="copyLink('{{ $user->survey_url }}')" class="btn-action btn-light">
                            <i class="bi bi-clipboard"></i> Copiar Link
                        </button>
                        <a href="{{ route('users.preview', $user->id) }}" target="_blank" class="btn-action btn-light">
                            <i class="bi bi-eye"></i> Vista Previa
                        </a>
                        <button onclick="shareWhatsApp('{{ $user->survey_url }}')" class="btn-action btn-success">
                            <i class="bi bi-whatsapp"></i> Compartir WhatsApp
                        </button>
                        <button onclick="shareEmail('{{ $user->survey_url }}', '{{ $user->name }}')" class="btn-action btn-info">
                            <i class="bi bi-envelope"></i> Enviar Email
                        </button>
                    </div>

                    <div class="mt-3">
                        <small style="color: rgba(255,255,255,0.7);">
                            <i class="bi bi-shield-check"></i> Este link es único y permanente. 
                            Si necesitas regenerarlo, contacta al administrador.
                        </small>
                    </div>
                </div>

                <!-- Gráfico -->
                @if($stats['total_surveys'] > 0)
                <div class="card">
                    <h3><i class="bi bi-bar-chart"></i> Distribución de Calificaciones</h3>
                    <canvas id="ratingsChart"></canvas>
                </div>
                @endif

                <!-- Encuestas Recientes -->
                <div class="card">
                    <h3><i class="bi bi-clock-history"></i> Encuestas Recientes</h3>
                    @if($recentSurveys->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Experiencia</th>
                                    <th>Atención</th>
                                    <th>Comentario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSurveys as $survey)
                                <tr>
                                    <td>{{ $survey->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $survey->client_name ?? 'Anónimo' }}</td>
                                    <td>
                                        @if($survey->experience_rating == 4)
                                            <span class="badge-rating badge-muy-feliz">😊 Muy Feliz</span>
                                        @elseif($survey->experience_rating == 3)
                                            <span class="badge-rating badge-feliz">🙂 Feliz</span>
                                        @elseif($survey->experience_rating == 2)
                                            <span class="badge-rating badge-insatisfecho">😐 Insatisfecho</span>
                                        @else
                                            <span class="badge-rating badge-muy-insatisfecho">😞 Muy Insatisfecho</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($survey->service_quality_rating == 4)
                                            <span class="badge-rating badge-muy-feliz">😊</span>
                                        @elseif($survey->service_quality_rating == 3)
                                            <span class="badge-rating badge-feliz">🙂</span>
                                        @elseif($survey->service_quality_rating == 2)
                                            <span class="badge-rating badge-insatisfecho">😐</span>
                                        @else
                                            <span class="badge-rating badge-muy-insatisfecho">😞</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($survey->comments)
                                            <small>{{ Str::limit($survey->comments, 50) }}</small>
                                        @else
                                            <small class="text-muted">Sin comentarios</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">No hay encuestas aún</p>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <!-- Código QR -->
                <div class="card">
                    <h3><i class="bi bi-qr-code"></i> Código QR</h3>
                    <div class="qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($user->survey_url) }}" alt="QR Code">
                        <div class="mt-3">
                            <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data={{ urlencode($user->survey_url) }}" download="qr_{{ $user->id }}.png" class="btn btn-primary w-100">
                                <i class="bi bi-download"></i> Descargar QR
                            </a>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Imprime este código para recibir encuestas presenciales
                        </small>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="card">
                    <h3><i class="bi bi-tools"></i> Acciones</h3>
                    <div class="d-grid gap-2">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar Usuario
                        </a>
                        
                        <form method="POST" action="{{ route('users.toggle-status', $user->id) }}">
                            @csrf
                            <button type="submit" class="btn {{ $user->is_active ? 'btn-danger' : 'btn-success' }} w-100">
                                <i class="bi bi-power"></i> {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('users.regenerate-token', $user->id) }}" onsubmit="return confirm('¿Regenerar token? El link anterior dejará de funcionar.')">
                            @csrf
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-arrow-clockwise"></i> Regenerar Token
                            </button>
                        </form>

                        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> Eliminar Usuario
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copiar link
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('¡Link copiado al portapapeles!');
            });
        }

        // Compartir en WhatsApp
        function shareWhatsApp(url) {
            const text = `Hola, por favor completa esta breve encuesta de satisfacción sobre tu experiencia en TRIMAX: ${url}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        // Compartir por Email
        function shareEmail(url, name) {
            const subject = `Encuesta de Satisfacción - ${name}`;
            const body = `Hola,\n\nNos gustaría conocer tu opinión sobre la atención recibida.\n\nPor favor completa esta breve encuesta:\n${url}\n\nGracias,\nEquipo TRIMAX`;
            window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }

        // Gráfico de calificaciones
        @if($stats['total_surveys'] > 0)
        const ctx = document.getElementById('ratingsChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Muy Feliz 😊', 'Feliz 🙂', 'Insatisfecho 😐', 'Muy Insatisfecho 😞'],
                datasets: [{
                    data: [{{ $stats['muy_feliz'] }}, {{ $stats['feliz'] }}, {{ $stats['insatisfecho'] }}, {{ $stats['muy_insatisfecho'] }}],
                    backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        @endif
    </script>
</body>
</html>
