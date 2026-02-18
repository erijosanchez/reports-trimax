@extends('layouts.app')

@section('title', 'Dashboard de Ventas')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            <!-- Header con Selectores -->
                            <div class="row mb-4">
                                <div class="col-lg-8">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="mdi mdi-chart-line text-primary me-2"></i>
                                        Dashboard de Ventas
                                    </h2>
                                    <p class="text-muted mb-0">
                                        <i class="mdi mdi-office-building me-2"></i>
                                        <strong>{{ $sedeUsuario }}</strong>
                                    </p>
                                </div>

                                <div class="col-lg-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1">Año</label>
                                            <select id="anioSelect" class="form-select">
                                                @foreach ($aniosDisponibles as $anio)
                                                    <option value="{{ $anio }}"
                                                        {{ $anio == $anioActual ? 'selected' : '' }}>
                                                        {{ $anio }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1">Mes</label>
                                            <select id="mesSelect" class="form-select">
                                                <option value="Enero" {{ $mesActual == 'Enero' ? 'selected' : '' }}>
                                                    Enero</option>
                                                <option value="Febrero" {{ $mesActual == 'Febrero' ? 'selected' : '' }}>
                                                    Febrero</option>
                                                <option value="Marzo" {{ $mesActual == 'Marzo' ? 'selected' : '' }}>
                                                    Marzo</option>
                                                <option value="Abril" {{ $mesActual == 'Abril' ? 'selected' : '' }}>
                                                    Abril</option>
                                                <option value="Mayo" {{ $mesActual == 'Mayo' ? 'selected' : '' }}>
                                                    Mayo</option>
                                                <option value="Junio" {{ $mesActual == 'Junio' ? 'selected' : '' }}>
                                                    Junio</option>
                                                <option value="Julio" {{ $mesActual == 'Julio' ? 'selected' : '' }}>
                                                    Julio</option>
                                                <option value="Agosto" {{ $mesActual == 'Agosto' ? 'selected' : '' }}>
                                                    Agosto</option>
                                                <option value="Septiembre"
                                                    {{ $mesActual == 'Septiembre' || $mesActual == 'Setiembre' ? 'selected' : '' }}>
                                                    Septiembre</option>
                                                <option value="Octubre" {{ $mesActual == 'Octubre' ? 'selected' : '' }}>
                                                    Octubre</option>
                                                <option value="Noviembre"
                                                    {{ $mesActual == 'Noviembre' ? 'selected' : '' }}>Noviembre</option>
                                                <option value="Diciembre"
                                                    {{ $mesActual == 'Diciembre' ? 'selected' : '' }}>Diciembre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Principales -->
                            <div class="row mb-4" id="cardsContainer">
                                <div class="col-lg-12 mb-2">
                                    <h6 class="text-muted">
                                        <i class="mdi mdi-store me-1"></i> Ventas Generales
                                    </h6>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-tale h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-cart text-primary mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta General</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaGeneral">
                                                        S/ {{ number_format($datos['venta_general'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-currency-usd text-info mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta Proyectada</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaTotal">
                                                        S/ {{ number_format($datos['venta_proyectada'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-dark-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-target text-warning mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Cuota del Mes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cuota">
                                                        S/ {{ number_format($datos['cuota'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100" id="cardCumplimiento">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-chart-line text-success mdi-36px"
                                                        id="iconCumplimiento"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cumplimiento">
                                                        {{ number_format($datos['cumplimiento_cuota'], 2) }}%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" id="progressCumplimiento"
                                                    style="width: {{ min($datos['cumplimiento_cuota'], 100) }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 🔥 Cards Ventas Digitales -->
                            <div class="row mb-4" id="cardsDigitales">
                                <div class="col-lg-12 mb-2">
                                    <h6 class="text-muted">
                                        <i class="mdi mdi-monitor me-1"></i> Ventas Digitales
                                    </h6>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-tale h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-monitor-dashboard text-primary mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaDigital">
                                                        S/ {{ number_format($datos['venta_digital'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-chart-line text-info mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Venta Proy. Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaProyDigital">
                                                        S/ {{ number_format($datos['venta_proy_digital'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-dark-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-target text-warning mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Cuota Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cuotaDigital">
                                                        S/ {{ number_format($datos['cuota_digital'], 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100" id="cardCumDigital">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-percent text-success mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white">Cum. Cuota Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cumCuotaDigital">
                                                        {{ number_format($datos['cum_cuota_digital'], 2) }}%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" id="progressCumDigital"
                                                    style="width: {{ min($datos['cum_cuota_digital'], 100) }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="row mb-4">
                                <div class="col-lg-8 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-bar text-primary me-2"></i>
                                                Ventas vs Cuota - <span id="anioGrafico">{{ $anioActual }}</span>
                                            </h4>
                                            <canvas id="ventasCuotaChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

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

                            <!-- Gráfico Comparación Anual -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-areaspline text-info me-2"></i>
                                                Comparación de {{ $datosAnuales['mes'] ?? 'Mes Actual' }} - Histórico de
                                                Años
                                            </h4>
                                            <canvas id="ventasAnualesChart" height="60"></canvas>
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
                                                Resumen Mensual - <span id="anioTabla">{{ $anioActual }}</span>
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
                                                    <tbody id="tablaBody">
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
                                                                    {{ number_format($historico['cumplimientos'][$index], 2) }}%
                                                                </td>
                                                                <td class="text-center">
                                                                    @if ($historico['cumplimientos'][$index] >= 100)
                                                                        <span class="badge badge-success"><i
                                                                                class="mdi mdi-check-circle me-1"></i>Cumplido</span>
                                                                    @elseif($historico['cumplimientos'][$index] >= 70)
                                                                        <span class="badge badge-warning"><i
                                                                                class="mdi mdi-alert me-1"></i>En
                                                                            Progreso</span>
                                                                    @else
                                                                        <span class="badge badge-danger"><i
                                                                                class="mdi mdi-close-circle me-1"></i>Bajo</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted">
                                                                    <i class="mdi mdi-inbox mdi-48px d-block mb-3"></i>
                                                                    No hay datos disponibles
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

        .form-select {
            font-size: 0.875rem;
        }

        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let ventasCuotaChart, cumplimientoChart, ventasAnualesChart;

        let historico = {
            meses: @json($historico['meses']),
            ventas: @json($historico['ventas']),
            cuotas: @json($historico['cuotas']),
            cumplimientos: @json($historico['cumplimientos'])
        };

        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();
            document.getElementById('anioSelect').addEventListener('change', actualizarDatos);
            document.getElementById('mesSelect').addEventListener('change', actualizarDatos);
        });

        async function actualizarDatos() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            try {
                const response = await fetch(`{{ route('api.ventas.data') }}?anio=${anio}&mes=${mes}`);
                const data = await response.json();

                // Cards normales
                document.getElementById('ventaGeneral').textContent = 'S/ ' + formatNumber(data.datos.venta_general);
                document.getElementById('cuota').textContent = 'S/ ' + formatNumber(data.datos.cuota);
                document.getElementById('cumplimiento').textContent = data.datos.cumplimiento_cuota.toFixed(2) + '%';
                document.getElementById('ventaTotal').textContent = 'S/ ' + formatNumber(data.datos.venta_proyectada);
                actualizarColorCumplimiento(data.datos.cumplimiento_cuota);

                // 🔥 Cards digitales
                document.getElementById('ventaDigital').textContent = 'S/ ' + formatNumber(data.datos.venta_digital);
                document.getElementById('ventaProyDigital').textContent = 'S/ ' + formatNumber(data.datos
                    .venta_proy_digital);
                document.getElementById('cuotaDigital').textContent = 'S/ ' + formatNumber(data.datos.cuota_digital);
                document.getElementById('cumCuotaDigital').textContent = (data.datos.cum_cuota_digital ?? 0).toFixed(
                    2) + '%';
                actualizarColorCumDigital(data.datos.cum_cuota_digital ?? 0);

                // Gráficos y tabla
                historico = data.historico;
                actualizarGraficos();
                actualizarTabla(data.historico);

                document.getElementById('anioGrafico').textContent = anio;
                document.getElementById('anioTabla').textContent = anio;

            } catch (error) {
                console.error('Error al actualizar datos:', error);
            }
        }

        function actualizarColorCumplimiento(cumplimiento) {
            const card = document.getElementById('cardCumplimiento');
            const icon = document.getElementById('iconCumplimiento');
            const progress = document.getElementById('progressCumplimiento');

            card.classList.remove('card-light-blue', 'card-light-danger');
            icon.classList.remove('text-success', 'text-warning', 'text-danger');
            progress.classList.remove('bg-success', 'bg-warning', 'bg-danger');

            if (cumplimiento >= 100) {
                card.classList.add('card-light-blue');
                icon.classList.add('text-success');
                progress.classList.add('bg-success');
            } else if (cumplimiento >= 70) {
                card.classList.add('card-light-danger');
                icon.classList.add('text-warning');
                progress.classList.add('bg-warning');
            } else {
                card.classList.add('card-light-danger');
                icon.classList.add('text-danger');
                progress.classList.add('bg-danger');
            }

            progress.style.width = Math.min(cumplimiento, 100) + '%';
        }

        // 🔥 Color dinámico para card cum digital
        function actualizarColorCumDigital(cum) {
            const card = document.getElementById('cardCumDigital');
            const progress = document.getElementById('progressCumDigital');

            card.classList.remove('card-light-blue', 'card-light-danger');
            progress.classList.remove('bg-success', 'bg-warning', 'bg-danger');

            if (cum >= 100) {
                card.classList.add('card-light-blue');
                progress.classList.add('bg-success');
            } else if (cum >= 70) {
                card.classList.add('card-light-danger');
                progress.classList.add('bg-warning');
            } else {
                card.classList.add('card-light-danger');
                progress.classList.add('bg-danger');
            }

            progress.style.width = Math.min(cum, 100) + '%';
        }

        function formatNumber(num) {
            return Math.round(num).toLocaleString('es-PE');
        }

        function inicializarGraficos() {
            const ctxVentas = document.getElementById('ventasCuotaChart').getContext('2d');
            ventasCuotaChart = new Chart(ctxVentas, {
                type: 'bar',
                data: {
                    labels: historico.meses,
                    datasets: [{
                        label: 'Ventas',
                        data: historico.ventas,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }, {
                        label: 'Cuota',
                        data: historico.cuotas,
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
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': S/ ' + ctx.parsed.y.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + val.toLocaleString()
                            }
                        }
                    }
                }
            });

            const ctxCumplimiento = document.getElementById('cumplimientoChart').getContext('2d');
            cumplimientoChart = new Chart(ctxCumplimiento, {
                type: 'line',
                data: {
                    labels: historico.meses,
                    datasets: [{
                        label: '% Cumplimiento',
                        data: historico.cumplimientos,
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
                                callback: val => val.toFixed(2) + '%'
                            }
                        }
                    }
                }
            });

            const ctxAnuales = document.getElementById('ventasAnualesChart').getContext('2d');
            ventasAnualesChart = new Chart(ctxAnuales, {
                type: 'line',
                data: {
                    labels: @json($datosAnuales['anios']),
                    datasets: [{
                        label: 'Ventas en {{ $datosAnuales['mes'] ?? 'Mes Actual' }}',
                        data: @json($datosAnuales['ventas']),
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgba(153, 102, 255, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Total: S/ ' + ctx.parsed.y.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + (val / 1000000).toFixed(1) + 'M'
                            }
                        }
                    }
                }
            });
        }

        function actualizarGraficos() {
            ventasCuotaChart.data.labels = historico.meses;
            ventasCuotaChart.data.datasets[0].data = historico.ventas;
            ventasCuotaChart.data.datasets[1].data = historico.cuotas;
            ventasCuotaChart.update();

            cumplimientoChart.data.labels = historico.meses;
            cumplimientoChart.data.datasets[0].data = historico.cumplimientos;
            cumplimientoChart.update();
        }

        function actualizarTabla(historico) {
            const tbody = document.getElementById('tablaBody');

            if (historico.meses.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="mdi mdi-inbox mdi-48px d-block mb-3"></i>
                            No hay datos disponibles
                        </td>
                    </tr>`;
                return;
            }

            let html = '';
            historico.meses.forEach((mes, index) => {
                const cum = historico.cumplimientos[index];
                let badgeClass = 'badge-danger',
                    badgeIcon = 'mdi-close-circle',
                    badgeText = 'Bajo',
                    textClass = 'text-danger';

                if (cum >= 100) {
                    badgeClass = 'badge-success';
                    badgeIcon = 'mdi-check-circle';
                    badgeText = 'Cumplido';
                    textClass = 'text-success';
                } else if (cum >= 70) {
                    badgeClass = 'badge-warning';
                    badgeIcon = 'mdi-alert';
                    badgeText = 'En Progreso';
                    textClass = 'text-warning';
                }

                html += `
                    <tr>
                        <td class="fw-semibold">${mes}</td>
                        <td>S/ ${formatNumber(historico.ventas[index])}</td>
                        <td>S/ ${formatNumber(historico.cuotas[index])}</td>
                        <td class="fw-bold ${textClass}">${cum.toFixed(2)}%</td>
                        <td class="text-center">
                            <span class="badge ${badgeClass}">
                                <i class="mdi ${badgeIcon} me-1"></i>${badgeText}
                            </span>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html;
        }
    </script>
@endsection
