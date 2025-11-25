@extends('layouts.app')

@section('title', 'Dashboards')

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
                                            <h3 class="rate-percentage mb-0">Dashboards</h3>
                                            <p class="text-muted mt-1">Gestiona y visualiza tus reportes de Power BI</p>
                                        </div>
                                        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                            <a href="{{ route('dashboards.create') }}" class="btn btn-success text-white">
                                                <i class="mdi mdi-plus-circle me-1"></i>Crear Dashboard
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($dashboards->isEmpty())
                                <!-- Empty State -->
                                <div class="row">
                                    <div class="col-lg-12 grid-margin stretch-card">
                                        <div class="card">
                                            <div class="card-body text-center py-5">
                                                <i class="mdi mdi-chart-box-outline mdi-48px text-muted mb-3 d-block"></i>
                                                <h4 class="mb-3">No hay dashboards disponibles</h4>
                                                <p class="text-muted mb-4">Aún no se han creado dashboards en el sistema.
                                                </p>
                                                @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                                    <a href="{{ route('dashboards.create') }}" class="btn btn-primary">
                                                        <i class="mdi mdi-plus-circle me-1 text-white"></i>Crear el primer dashboard
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Dashboards Grid -->
                                <div class="row">
                                    @foreach ($dashboards as $dashboard)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <!-- Header con título y badge -->
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h4 class="card-title mb-0">{{ $dashboard->name }}</h4>
                                                        @if (!$dashboard->is_active)
                                                            <span class="badge badge-danger">Inactivo</span>
                                                        @else
                                                            <span class="badge badge-success">Activo</span>
                                                        @endif
                                                    </div>

                                                    <!-- Descripción -->
                                                    <p class="card-description text-muted" style="min-height: 3rem;">
                                                        {{ $dashboard->description ?? 'Sin descripción' }}
                                                    </p>

                                                    <!-- Botones de acción -->
                                                    <div class="d-flex gap-2 mb-3">
                                                        <a href="{{ route('dashboards.show', $dashboard->id) }}"
                                                            class="btn btn-primary btn-sm flex-grow-1 text-white p-3">
                                                            <i class="mdi mdi-chart-areaspline me-1"></i> Ver Dashboard
                                                        </a>

                                                        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                                            <a href="{{ route('dashboards.edit', $dashboard->id) }}"
                                                                class="btn btn-warning btn-sm p-3">
                                                                <i class="mdi mdi-pencil" style="font-size: 1.2rem"></i>
                                                            </a>
                                                        @endif
                                                    </div>

                                                    <!-- Info adicional para admin -->
                                                    @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                                        <div class="border-top pt-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-account-multiple text-primary me-2"></i>
                                                                <small class="text-muted">
                                                                    <strong>{{ $dashboard->users->count() }}</strong>
                                                                    {{ $dashboard->users->count() == 1 ? 'usuario asignado' : 'usuarios asignados' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Fix para gap en navegadores que no lo soporten */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Ajuste de altura mínima para cards */
        .card-description {
            line-height: 1.5;
        }
    </style>
@endsection
