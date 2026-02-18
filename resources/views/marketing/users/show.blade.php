@extends('layouts.app')

@section('title', $user->name . ' - Detalle')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

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

                            <!-- Header de Usuario -->
                            <div class="row pb-3">
                                <div class="col-sm-12">
                                    <div class="card"
                                        style="background: linear-gradient(135deg,
                                            {{ $user->role === 'trimax' ? '#1a1a2e 0%, #16213e 100%' : '#667eea 0%, #764ba2 100%' }});">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="text-white">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <img class="img-lg rounded-circle me-3"
                                                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=fff&color={{ $user->role === 'trimax' ? '1a1a2e' : '667eea' }}&size=128"
                                                            alt="profile">
                                                        <div>
                                                            <h2 class="text-white mb-2">{{ $user->name }}</h2>
                                                            @if ($user->role === 'trimax')
                                                                <p class="mb-0 opacity-75">
                                                                    <i class="mdi mdi-domain me-1"></i>Empresa General -
                                                                    TRIMAX
                                                                </p>
                                                            @else
                                                                <p class="mb-0 opacity-75">
                                                                    <i class="mdi mdi-email me-1"></i>{{ $user->email }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        @if ($user->role === 'consultor')
                                                            <span class="badge badge-light badge-pill px-3 py-2">
                                                                <i class="mdi mdi-account-tie"></i> Consultor
                                                            </span>
                                                        @elseif ($user->role === 'sede')
                                                            <span class="badge badge-info badge-pill px-3 py-2">
                                                                <i class="mdi mdi-office-building"></i> Sede -
                                                                {{ $user->location }}
                                                            </span>
                                                        @elseif ($user->role === 'trimax')
                                                            <span class="badge badge-dark badge-pill px-3 py-2"
                                                                style="background-color: rgba(255, 255, 255, 0.863) !important;">
                                                                <i class="mdi mdi-domain"></i> TRIMAX - General
                                                            </span>
                                                        @endif

                                                        @if ($user->is_active)
                                                            <span class="badge badge-success badge-pill px-3 py-2">
                                                                <i class="mdi mdi-check-circle"></i> Activo
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger badge-pill px-3 py-2">
                                                                <i class="mdi mdi-close-circle"></i> Inactivo
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="{{ route('marketing.users.index') }}" class="btn btn-light">
                                                        <i class="mdi mdi-arrow-left me-1"></i> Volver
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-file-document text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['total_surveys'] }}</h3>
                                                <p class="statistics-title mb-0">Total Encuestas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-warning-subtle rounded mb-3">
                                                    <i class="mdi mdi-star text-warning icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">
                                                    {{ number_format($stats['average_rating'], 2) }}</h3>
                                                <p class="statistics-title mb-0">Promedio</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-success-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-excited text-success icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['muy_feliz'] }}</h3>
                                                <p class="statistics-title mb-0">Muy Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-info-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-happy text-info icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['feliz'] }}</h3>
                                                <p class="statistics-title mb-0">Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-warning-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-neutral text-warning icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['insatisfecho'] }}</h3>
                                                <p class="statistics-title mb-0">Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-danger-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-sad text-danger icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">{{ $stats['muy_insatisfecho'] }}</h3>
                                                <p class="statistics-title mb-0">Muy Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sedes Asignadas (solo para consultores) -->
                            @if ($user->isConsultor())
                                <div class="row">
                                    <div class="col-lg-12 grid-margin stretch-card">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    <h4 class="card-title mb-0">
                                                        <i class="mdi mdi-office-building text-primary me-2"></i>
                                                        Sedes Asignadas
                                                    </h4>
                                                    <a href="{{ route('marketing.users.assign-sedes', $user->id) }}"
                                                        class="btn btn-primary text-white btn-sm">
                                                        <i class="mdi mdi-cog me-1"></i> Gestionar Sedes
                                                    </a>
                                                </div>

                                                @if ($user->sedes->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sede</th>
                                                                    <th>Ubicación</th>
                                                                    <th>Encuestas</th>
                                                                    <th>Promedio</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($user->sedes as $sede)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img class="img-xs rounded-circle me-2"
                                                                                    src="https://ui-avatars.com/api/?name={{ urlencode($sede->name) }}&background=0dcaf0&color=fff"
                                                                                    alt="sede">
                                                                                <a
                                                                                    href="{{ route('marketing.users.show', $sede->id) }}">
                                                                                    <strong>{{ $sede->name }}</strong>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-secondary">
                                                                                <i class="mdi mdi-map-marker"></i>
                                                                                {{ $sede->location }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <strong
                                                                                class="text-primary">{{ $sede->total_surveys }}</strong>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-warning">
                                                                                <i class="mdi mdi-star"></i>
                                                                                {{ number_format($sede->average_rating, 2) }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="alert alert-info border-0 mt-3" role="alert">
                                                        <h6 class="alert-heading">
                                                            <i class="mdi mdi-chart-line me-2"></i>
                                                            Estadísticas Consolidadas
                                                        </h6>
                                                        <div class="row mt-3">
                                                            <div class="col-md-6">
                                                                <p class="mb-1">
                                                                    <strong>Encuestas Propias:</strong>
                                                                    {{ $stats['total_surveys'] }}
                                                                </p>
                                                                <p class="mb-0">
                                                                    <strong>Promedio Propio:</strong>
                                                                    <i class="mdi mdi-star text-warning"></i>
                                                                    {{ number_format($stats['average_rating'], 2) }}
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1">
                                                                    <strong>Total con Sedes:</strong>
                                                                    {{ $stats['total_surveys_with_sedes'] }}
                                                                </p>
                                                                <p class="mb-0">
                                                                    <strong>Promedio General:</strong>
                                                                    <i class="mdi mdi-star text-warning"></i>
                                                                    {{ number_format($stats['average_rating_with_sedes'], 2) }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning border-0" role="alert">
                                                        <i class="mdi mdi-alert me-2"></i>
                                                        No hay sedes asignadas a este consultor.
                                                        <a href="{{ route('marketing.users.assign-sedes', $user->id) }}"
                                                            class="alert-link">Asignar ahora</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Banner informativo para Trimax -->
                            @if ($user->role === 'trimax')
                                <div class="row">
                                    <div class="col-lg-12 grid-margin">
                                        <div class="alert border-0 mb-0"
                                            style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white;">
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-domain me-3" style="font-size: 2rem;"></i>
                                                <div>
                                                    <h5 class="mb-1 text-white">Usuario General - TRIMAX</h5>
                                                    <p class="mb-0 opacity-75">
                                                        Este usuario representa a la empresa en general. Las encuestas
                                                        recibidas reflejan la satisfacción global con TRIMAX sin estar
                                                        asociadas a un consultor o sede específica.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Columna Principal -->
                                <div class="col-lg-8">
                                    <!-- Link de Encuesta -->
                                    <div class="card grid-margin stretch-card">
                                        <div class="card-body"
                                            style="background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%); color: white; border-radius: 10px;">
                                            <h4 class="card-title text-white mb-3">
                                                <i class="mdi mdi-link-variant me-2"></i>
                                                Link de Encuesta Única
                                            </h4>
                                            <p class="opacity-75 mb-3">Comparte este link con tus clientes para recibir
                                                feedback</p>

                                            <div class="input-group mb-3 bg-white rounded overflow-hidden">
                                                <input type="text" class="form-control border-0"
                                                    value="{{ $user->survey_url }}" readonly id="surveyLink">
                                                <button class="btn btn-primary text-white" type="button"
                                                    onclick="copyLink()">
                                                    <i class="mdi mdi-content-copy"></i> Copiar
                                                </button>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('marketing.users.preview', $user->id) }}"
                                                    target="_blank" class="btn btn-light btn-sm">
                                                    <i class="mdi mdi-eye me-1"></i> Vista Previa
                                                </a>
                                                <button onclick="shareWhatsApp()" class="btn btn-success btn-sm">
                                                    <i class="mdi mdi-whatsapp me-1"></i> Compartir WhatsApp
                                                </button>
                                                <button onclick="shareEmail()" class="btn btn-info btn-sm">
                                                    <i class="mdi mdi-email me-1"></i> Enviar Email
                                                </button>
                                            </div>

                                            <div class="mt-3">
                                                <small class="opacity-75">
                                                    <i class="mdi mdi-shield-check me-1"></i>
                                                    Este link es único y permanente. Si necesitas regenerarlo, usa el botón
                                                    en la sección de acciones.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Gráfico -->
                                    @if ($stats['total_surveys'] > 0)
                                        <div class="card grid-margin stretch-card">
                                            <div class="card-body">
                                                <h4 class="card-title mb-3">
                                                    <i class="mdi mdi-chart-donut text-primary me-2"></i>
                                                    Distribución de Calificaciones
                                                </h4>
                                                <canvas id="ratingsChart" height="250"></canvas>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Encuestas Recientes -->
                                    <div class="card grid-margin stretch-card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-clock-outline text-info me-2"></i>
                                                Encuestas Recientes
                                            </h4>

                                            @if ($recentSurveys->count() > 0)
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
                                                            @foreach ($recentSurveys as $survey)
                                                                <tr>
                                                                    <td>
                                                                        <small
                                                                            class="text-muted">{{ $survey->created_at->format('d/m/Y') }}</small><br>
                                                                        <small>{{ $survey->created_at->format('H:i') }}</small>
                                                                    </td>
                                                                    <td>
                                                                        @if ($survey->client_name)
                                                                            <div class="d-flex align-items-center">
                                                                                <img class="img-xs rounded-circle me-2"
                                                                                    src="https://ui-avatars.com/api/?name={{ urlencode($survey->client_name) }}&background=10b981&color=fff"
                                                                                    alt="client">
                                                                                {{ $survey->client_name }}
                                                                            </div>
                                                                        @else
                                                                            <span class="text-muted">
                                                                                <i class="mdi mdi-account-off"></i>
                                                                                Anónimo
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($survey->experience_rating == 4)
                                                                            <span class="badge badge-success">
                                                                                <i class="mdi mdi-emoticon-excited"></i>
                                                                                Muy Feliz
                                                                            </span>
                                                                        @elseif($survey->experience_rating == 3)
                                                                            <span class="badge badge-info">
                                                                                <i class="mdi mdi-emoticon-happy"></i>
                                                                                Feliz
                                                                            </span>
                                                                        @elseif($survey->experience_rating == 2)
                                                                            <span class="badge badge-warning">
                                                                                <i class="mdi mdi-emoticon-neutral"></i>
                                                                                Insatisfecho
                                                                            </span>
                                                                        @else
                                                                            <span class="badge badge-danger">
                                                                                <i class="mdi mdi-emoticon-sad"></i>
                                                                                Muy Insatisfecho
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($survey->service_quality_rating == 4)
                                                                            <span class="badge badge-success">
                                                                                <i class="mdi mdi-emoticon-excited"></i>
                                                                            </span>
                                                                        @elseif($survey->service_quality_rating == 3)
                                                                            <span class="badge badge-info">
                                                                                <i class="mdi mdi-emoticon-happy"></i>
                                                                            </span>
                                                                        @elseif($survey->service_quality_rating == 2)
                                                                            <span class="badge badge-warning">
                                                                                <i class="mdi mdi-emoticon-neutral"></i>
                                                                            </span>
                                                                        @else
                                                                            <span class="badge badge-danger">
                                                                                <i class="mdi mdi-emoticon-sad"></i>
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($survey->comments)
                                                                            <small>{{ Str::limit($survey->comments, 40) }}</small>
                                                                        @else
                                                                            <small class="text-muted">Sin
                                                                                comentarios</small>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-5">
                                                    <i class="mdi mdi-inbox mdi-48px text-muted mb-3 d-block"></i>
                                                    <p class="text-muted">No hay encuestas registradas aún</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna Lateral -->
                                <div class="col-lg-4">
                                    <!-- Código QR -->
                                    <div class="card grid-margin stretch-card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-qrcode text-primary me-2"></i>
                                                Código QR
                                            </h4>
                                            <div class="text-center">
                                                <div class="bg-light p-4 rounded mb-3">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($user->survey_url) }}"
                                                        alt="QR Code" class="img-fluid"
                                                        style="max-width: 250px; border-radius: 10px;">
                                                </div>
                                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data={{ urlencode($user->survey_url) }}"
                                                    download="qr_{{ $user->id }}.png"
                                                    class="btn btn-primary text-white w-100 mb-2">
                                                    <i class="mdi mdi-download me-1"></i> Descargar QR en Alta Calidad
                                                </a>
                                                <small class="text-muted d-block">
                                                    Imprime este código para recibir encuestas presenciales
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="card grid-margin stretch-card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-wrench text-warning me-2"></i>
                                                Acciones
                                            </h4>
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('marketing.users.edit', $user->id) }}"
                                                    class="btn btn-warning">
                                                    <i class="mdi mdi-pencil me-1"></i> Editar Usuario
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('marketing.users.toggle-status', $user->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn {{ $user->is_active ? 'btn-danger' : 'btn-success' }} w-100">
                                                        <i class="mdi mdi-power me-1"></i>
                                                        {{ $user->is_active ? 'Desactivar Usuario' : 'Activar Usuario' }}
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                    action="{{ route('marketing.users.regenerate-token', $user->id) }}"
                                                    onsubmit="return confirm('¿Regenerar token? El link anterior dejará de funcionar.')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-dark w-100">
                                                        <i class="mdi mdi-refresh me-1"></i> Regenerar Token
                                                    </button>
                                                </form>

                                                <hr>

                                                <form method="POST"
                                                    action="{{ route('marketing.users.destroy', $user->id) }}"
                                                    onsubmit="return confirm('¿Eliminar usuario? Esta acción no se puede deshacer.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger w-100">
                                                        <i class="mdi mdi-delete me-1"></i> Eliminar Usuario
                                                    </button>
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

    <style>
        /* Gap utility for older browsers */
        .gap-2>*+* {
            margin-left: 0.5rem;
            margin-top: 0.5rem;
        }

        .d-grid.gap-2>* {
            margin-bottom: 0.5rem;
        }

        /* Badge pills */
        .badge-pill {
            border-radius: 50rem;
        }

        /* Image sizing */
        .img-lg {
            width: 80px;
            height: 80px;
        }

        /* Gradient card improvements */
        .card-body .opacity-75 {
            opacity: 0.75;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Copiar link
        function copyLink() {
            const input = document.getElementById('surveyLink');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="mdi mdi-check"></i> ¡Copiado!';

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar:', err);
                alert('Error al copiar el link');
            });
        }

        // Compartir en WhatsApp
        function shareWhatsApp() {
            const url = document.getElementById('surveyLink').value;
            const text =
                `Hola, por favor completa esta breve encuesta de satisfacción sobre tu experiencia en TRIMAX: ${url}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        // Compartir por Email
        function shareEmail() {
            const url = document.getElementById('surveyLink').value;
            const subject = `Encuesta de Satisfacción - {{ $user->name }}`;
            const body =
                `Hola,\n\nNos gustaría conocer tu opinión sobre la atención recibida.\n\nPor favor completa esta breve encuesta:\n${url}\n\nGracias,\nEquipo TRIMAX`;
            window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        }

        // Gráfico de calificaciones
        @if ($stats['total_surveys'] > 0)
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('ratingsChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Muy Feliz', 'Feliz', 'Insatisfecho', 'Muy Insatisfecho'],
                            datasets: [{
                                data: [{{ $stats['muy_feliz'] }}, {{ $stats['feliz'] }},
                                    {{ $stats['insatisfecho'] }},
                                    {{ $stats['muy_insatisfecho'] }}
                                ],
                                backgroundColor: ['#4CAF50', '#0dcaf0', '#ffc107', '#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.parsed || 0;
                                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            let percentage = ((value / total) * 100).toFixed(1);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        @endif
    </script>
@endpush
