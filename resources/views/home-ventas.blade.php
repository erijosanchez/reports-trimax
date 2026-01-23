@extends('layouts.app')

@section('title', 'Dashboard de Ventas')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            <!-- Header -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-1 fw-bold">
                                                <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                Dashboard de Ventas
                                            </h2>
                                            <p class="text-muted mb-0">
                                                <i class="mdi mdi-office-building me-2"></i>
                                                <strong>{{ $sedeUsuario }}</strong>
                                                <span class="mx-2">|</span>
                                                <i class="mdi mdi-calendar-today me-2"></i>
                                                {{ $mesActual }} {{ $anioActual }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Principales -->
                            <div class="row mb-4">
                                <!-- Card 1: Venta General -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-tale h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-cart text-primary mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta General</p>
                                                    <h4 class="mb-0 text-white fw-bold">
                                                        S/ {{ number_format($datos['venta_general'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2: Venta Proyectada -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-cart-arrow-right text-info mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta Proyectada</p>
                                                    <h4 class="mb-0 text-white fw-bold">
                                                        S/ {{ number_format($datos['venta_proyectada'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 3: Cuota -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-dark-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-target text-warning mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Cuota del Mes</p>
                                                    <h4 class="mb-0 text-white fw-bold">
                                                        S/ {{ number_format($datos['cuota'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 4: Cumplimiento -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div
                                        class="card 
                                    @if ($datos['cumplimiento_cuota'] >= 100) card-light-blue
                                    @elseif($datos['cumplimiento_cuota'] >= 70) card-light-danger
                                    @else card-light-danger @endif h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i
                                                        class="mdi mdi-chart-line 
                                                    @if ($datos['cumplimiento_cuota'] >= 100) text-success
                                                    @elseif($datos['cumplimiento_cuota'] >= 70) text-warning
                                                    @else text-danger @endif mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold">
                                                        {{ number_format($datos['cumplimiento_cuota'], 1) }}%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar 
                                                @if ($datos['cumplimiento_cuota'] >= 100) bg-success
                                                @elseif($datos['cumplimiento_cuota'] >= 70) bg-warning
                                                @else bg-danger @endif"
                                                    style="width: {{ min($datos['cumplimiento_cuota'], 100) }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="row mb-4">
                                <!-- Gráfico Ventas vs Cuota -->
                                <div class="col-lg-8 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-bar text-primary me-2"></i>
                                                Ventas vs Cuota - {{ $anioActual }}
                                            </h4>
                                            <canvas id="ventasCuotaChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gráfico Cumplimiento -->
                                <div class="col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-percent text-success me-2"></i>
                                                % Cumplimiento
                                            </h4>
                                            <canvas id="cumplimientoChart" height="160"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla Resumen -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-table text-info me-2"></i>
                                                Resumen Histórico {{ $anioActual }}
                                            </h4>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Mes</th>
                                                            <th>Venta General</th>
                                                            <th>Cuota</th>
                                                            <th>Cumplimiento</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($historico['meses'] as $index => $mes)
                                                            <tr>
                                                                <td class="fw-semibold">{{ $mes }}</td>
                                                                <td>S/
                                                                    {{ number_format($historico['ventas'][$index], 0, '.', ',') }}
                                                                </td>
                                                                <td>S/
                                                                    {{ number_format($historico['cuotas'][$index], 0, '.', ',') }}
                                                                </td>
                                                                <td
                                                                    class="fw-bold 
                                                            @if ($historico['cumplimientos'][$index] >= 100) text-success
                                                            @elseif($historico['cumplimientos'][$index] >= 70) text-warning
                                                            @else text-danger @endif">
                                                                    {{ number_format($historico['cumplimientos'][$index], 1) }}%
                                                                </td>
                                                                <td class="text-center">
                                                                    @if ($historico['cumplimientos'][$index] >= 100)
                                                                        <span class="badge badge-success">
                                                                            <i
                                                                                class="mdi mdi-check-circle me-1"></i>Cumplido
                                                                        </span>
                                                                    @elseif($historico['cumplimientos'][$index] >= 70)
                                                                        <span class="badge badge-warning">
                                                                            <i class="mdi mdi-alert me-1"></i>En Progreso
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-danger">
                                                                            <i class="mdi mdi-close-circle me-1"></i>Bajo
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted">
                                                                    <i class="mdi mdi-inbox mdi-48px d-block mb-3"></i>
                                                                    No hay datos disponibles para esta sede
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
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
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.3);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de Ventas vs Cuota
        const ctxVentas = document.getElementById('ventasCuotaChart').getContext('2d');
        new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: @json($historico['meses']),
                datasets: [{
                    label: 'Ventas',
                    data: @json($historico['ventas']),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }, {
                    label: 'Cuota',
                    data: @json($historico['cuotas']),
                    backgroundColor: 'rgba(255, 206, 86, 0.7)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Cumplimiento
        const ctxCumplimiento = document.getElementById('cumplimientoChart').getContext('2d');
        new Chart(ctxCumplimiento, {
            type: 'line',
            data: {
                labels: @json($historico['meses']),
                datasets: [{
                    label: '% Cumplimiento',
                    data: @json($historico['cumplimientos']),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 120,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
