@extends('layouts.app')

@section('title', $dashboard->name)

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
                                        <div class="d-flex align-items-center flex-grow-1">
                                            <a href="{{ route('dashboards.index') }}" class="btn btn-light btn-sm me-3">
                                                <i class="mdi mdi-arrow-left"></i>
                                            </a>
                                            <div class="flex-grow-1">
                                                <h3 class="rate-percentage mb-0">
                                                    <i class="mdi mdi-chart-areaspline text-primary me-2"></i>
                                                    {{ $dashboard->name }}
                                                </h3>
                                                @if ($dashboard->description)
                                                    <p class="text-muted mt-1 mb-0">{{ $dashboard->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            @if ($dashboard->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                            @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('dashboards.edit', $dashboard->id) }}"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="mdi mdi-pencil me-1"></i>Editar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Controles del Dashboard -->
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body py-2">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2">
                                                    <button id="toggleFullscreen" class="btn btn-sm btn-outline-primary">
                                                        <i class="mdi mdi-fullscreen"></i>
                                                        <span class="d-none d-md-inline ms-1">Pantalla Completa</span>
                                                    </button>
                                                    <button id="refreshDashboard" class="btn btn-sm btn-outline-secondary">
                                                        <i class="mdi mdi-refresh"></i>
                                                        <span class="d-none d-md-inline ms-1">Actualizar</span>
                                                    </button>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="mdi mdi-information-outline me-1"></i>
                                                    Presiona <kbd>F11</kbd> o haz clic en pantalla completa para mejor
                                                    visualización
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Power BI Dashboard -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body p-0">
                                            <div id="dashboard-container" class="powerbi-container">
                                                <div id="loading-overlay" class="loading-overlay">
                                                    <div class="text-center">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Cargando...</span>
                                                        </div>
                                                        <p class="mt-3 text-muted">Cargando dashboard de Power BI...</p>
                                                    </div>
                                                </div>
                                                <iframe id="powerbi-iframe" src="{{ $powerbiLink }}" frameborder="0"
                                                    allowfullscreen>
                                                </iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="alert alert-info d-flex align-items-start" role="alert">
                                        <i class="mdi mdi-lightbulb-on-outline me-3 mdi-24px"></i>
                                        <div>
                                            <strong class="d-block mb-2">Consejos para mejor experiencia:</strong>
                                            <ul class="mb-0 ps-3">
                                                <li>Utiliza el modo de pantalla completa para visualizar mejor los datos
                                                </li>
                                                <li>Si el dashboard no carga, haz clic en "Actualizar" para intentar de
                                                    nuevo</li>
                                                <li>Algunos gráficos son interactivos - haz clic en ellos para filtrar datos
                                                </li>
                                                <li>Para una mejor experiencia en móvil, gira tu dispositivo horizontalmente
                                                </li>
                                            </ul>
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

        /* Power BI Container */
        .powerbi-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            /* 16:9 Aspect Ratio */
            background: #f8f9fa;
            overflow: hidden;
            border-radius: 8px;
        }

        #powerbi-iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .loading-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }

        /* Icon wrappers */
        .icon-wrapper {
            min-width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary-subtle {
            background-color: rgba(99, 102, 241, 0.1) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        /* Kbd styling */
        kbd {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 0.875rem;
            color: #495057;
        }

        /* Fullscreen mode */
        #dashboard-container.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding-bottom: 0;
            z-index: 9999;
            background: white;
        }

        #dashboard-container.fullscreen #powerbi-iframe {
            border-radius: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .powerbi-container {
                padding-bottom: 75%;
                /* Taller on mobile */
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('powerbi-iframe');
            const loadingOverlay = document.getElementById('loading-overlay');
            const toggleFullscreenBtn = document.getElementById('toggleFullscreen');
            const refreshBtn = document.getElementById('refreshDashboard');
            const dashboardContainer = document.getElementById('dashboard-container');
            let isFullscreen = false;

            // Hide loading overlay when iframe loads
            iframe.addEventListener('load', function() {
                setTimeout(function() {
                    loadingOverlay.classList.add('hidden');
                }, 500);
            });

            // Toggle fullscreen
            toggleFullscreenBtn.addEventListener('click', function() {
                if (!isFullscreen) {
                    // Enter fullscreen
                    dashboardContainer.classList.add('fullscreen');
                    toggleFullscreenBtn.innerHTML =
                        '<i class="mdi mdi-fullscreen-exit"></i><span class="d-none d-md-inline ms-1">Salir Pantalla Completa</span>';
                    isFullscreen = true;
                } else {
                    // Exit fullscreen
                    dashboardContainer.classList.remove('fullscreen');
                    toggleFullscreenBtn.innerHTML =
                        '<i class="mdi mdi-fullscreen"></i><span class="d-none d-md-inline ms-1">Pantalla Completa</span>';
                    isFullscreen = false;
                }
            });

            // Refresh dashboard
            refreshBtn.addEventListener('click', function() {
                loadingOverlay.classList.remove('hidden');
                iframe.src = iframe.src;

                // Add spinner to button
                const originalHTML = refreshBtn.innerHTML;
                refreshBtn.innerHTML =
                    '<i class="mdi mdi-loading mdi-spin"></i><span class="d-none d-md-inline ms-1">Actualizando...</span>';
                refreshBtn.disabled = true;

                // Reset button after load
                iframe.addEventListener('load', function resetButton() {
                    setTimeout(function() {
                        refreshBtn.innerHTML = originalHTML;
                        refreshBtn.disabled = false;
                    }, 500);
                    iframe.removeEventListener('load', resetButton);
                });
            });

            // Exit fullscreen with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && isFullscreen) {
                    dashboardContainer.classList.remove('fullscreen');
                    toggleFullscreenBtn.innerHTML =
                        '<i class="mdi mdi-fullscreen"></i><span class="d-none d-md-inline ms-1">Pantalla Completa</span>';
                    isFullscreen = false;
                }
            });
        });
    </script>
@endsection
