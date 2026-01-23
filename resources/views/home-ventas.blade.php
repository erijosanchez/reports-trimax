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

                                <!-- ✅ NUEVO: Selectores de Año y Mes -->
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
                                                <option value="Enero" {{ $mesActual == 'Enero' ? 'selected' : '' }}>Enero
                                                </option>
                                                <option value="Febrero" {{ $mesActual == 'Febrero' ? 'selected' : '' }}>
                                                    Febrero</option>
                                                <option value="Marzo" {{ $mesActual == 'Marzo' ? 'selected' : '' }}>Marzo
                                                </option>
                                                <option value="Abril" {{ $mesActual == 'Abril' ? 'selected' : '' }}>Abril
                                                </option>
                                                <option value="Mayo" {{ $mesActual == 'Mayo' ? 'selected' : '' }}>Mayo
                                                </option>
                                                <option value="Junio" {{ $mesActual == 'Junio' ? 'selected' : '' }}>Junio
                                                </option>
                                                <option value="Julio" {{ $mesActual == 'Julio' ? 'selected' : '' }}>Julio
                                                </option>
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
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaGeneral">
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

                                <!-- Card 2: Cuota -->
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

                                <!-- Card 3: Cumplimiento -->
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

                            <!-- Gráficos -->
                            <div class="row mb-4">
                                <!-- Gráfico Ventas vs Cuota -->
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

                            <!-- ✅ NUEVO: Gráfico de Ventas Anuales -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-areaspline text-info me-2"></i>
                                                Comparación de Ventas Anuales - {{ $sedeUsuario }}
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

        // Datos iniciales
        let historico = {
            meses: @json($historico['meses']),
            ventas: @json($historico['ventas']),
            cuotas: @json($historico['cuotas']),
            cumplimientos: @json($historico['cumplimientos'])
        };

        // ✅ Inicializar gráficos
        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();

            // ✅ Event listeners para los selectores
            document.getElementById('anioSelect').addEventListener('change', actualizarDatos);
            document.getElementById('mesSelect').addEventListener('change', actualizarDatos);
        });

        // ✅ Función para actualizar datos vía AJAX
        async function actualizarDatos() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            try {
                const response = await fetch(`{{ route('api.ventas.data') }}?anio=${anio}&mes=${mes}`);
                const data = await response.json();

                // Actualizar cards
                document.getElementById('ventaGeneral').textContent = 'S/ ' + formatNumber(data.datos.venta_general);
                document.getElementById('cuota').textContent = 'S/ ' + formatNumber(data.datos.cuota);
                document.getElementById('cumplimiento').textContent = data.datos.cumplimiento_cuota.toFixed(2) + '%';
                document.getElementById('ventaTotal').textContent = 'S/ ' + formatNumber(data.datos.venta_total);

                // Actualizar color del card de cumplimiento
                actualizarColorCumplimiento(data.datos.cumplimiento_cuota);

                // Actualizar gráficos
                historico = data.historico;
                actualizarGraficos();

                // Actualizar títulos
                document.getElementById('anioGrafico').textContent = anio;
                document.getElementById('anioTabla').textContent = anio;

                // Actualizar tabla
                actualizarTabla(data.historico);

            } catch (error) {
                console.error('Error al actualizar datos:', error);
            }
        }

        // ✅ Actualizar color del card de cumplimiento
        function actualizarColorCumplimiento(cumplimiento) {
            const card = document.getElementById('cardCumplimiento');
            const icon = document.getElementById('iconCumplimiento');
            const progress = document.getElementById('progressCumplimiento');

            // Remover clases anteriores
            card.classList.remove('card-light-blue', 'card-light-danger');
            icon.classList.remove('text-success', 'text-warning', 'text-danger');
            progress.classList.remove('bg-success', 'bg-warning', 'bg-danger');

            // Agregar nuevas clases
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

        // ✅ Formatear números
        function formatNumber(num) {
            return Math.round(num).toLocaleString('es-PE');
        }

        // ✅ Inicializar todos los gráficos
        function inicializarGraficos() {
            // Gráfico Ventas vs Cuota
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

            // Gráfico Cumplimiento
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
                                callback: function(value) {
                                    return value.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });

            // ✅ NUEVO: Gráfico de Ventas Anuales
            const ctxAnuales = document.getElementById('ventasAnualesChart').getContext('2d');
            ventasAnualesChart = new Chart(ctxAnuales, {
                type: 'line',
                data: {
                    labels: @json($datosAnuales['anios']),
                    datasets: [{
                        label: 'Venta Total Anual',
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
                                label: function(context) {
                                    return 'Total: S/ ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ✅ Actualizar gráficos con nuevos datos
        function actualizarGraficos() {
            // Actualizar Ventas vs Cuota
            ventasCuotaChart.data.labels = historico.meses;
            ventasCuotaChart.data.datasets[0].data = historico.ventas;
            ventasCuotaChart.data.datasets[1].data = historico.cuotas;
            ventasCuotaChart.update();

            // Actualizar Cumplimiento
            cumplimientoChart.data.labels = historico.meses;
            cumplimientoChart.data.datasets[0].data = historico.cumplimientos;
            cumplimientoChart.update();
        }

        // ✅ Actualizar tabla
        function actualizarTabla(historico) {
            const tbody = document.getElementById('tablaBody');

            if (historico.meses.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="mdi mdi-inbox mdi-48px d-block mb-3"></i>
                    No hay datos disponibles
                </td>
            </tr>
        `;
                return;
            }

            let html = '';
            historico.meses.forEach((mes, index) => {
                const cumplimiento = historico.cumplimientos[index];
                let badgeClass = 'badge-danger';
                let badgeIcon = 'mdi-close-circle';
                let badgeText = 'Bajo';
                let textClass = 'text-danger';

                if (cumplimiento >= 100) {
                    badgeClass = 'badge-success';
                    badgeIcon = 'mdi-check-circle';
                    badgeText = 'Cumplido';
                    textClass = 'text-success';
                } else if (cumplimiento >= 70) {
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
                <td class="fw-bold ${textClass}">${cumplimiento.toFixed(2)}%</td>
                <td class="text-center">
                    <span class="badge ${badgeClass}">
                        <i class="mdi ${badgeIcon} me-1"></i>${badgeText}
                    </span>
                </td>
            </tr>
        `;
            });

            tbody.innerHTML = html;
        }
    </script>
@endsection
