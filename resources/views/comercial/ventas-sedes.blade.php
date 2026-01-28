@extends('layouts.app')

@section('title', 'Ventas por Sedes')

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
                                        Ventas por Sedes
                                    </h2>
                                    <p class="text-muted mb-0">
                                        <i class="mdi mdi-store me-2"></i>
                                        Monitoreo de todas las sedes
                                    </p>
                                </div>

                                <!-- Selectores de Año y Mes -->
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

                            <!-- Cards Resumen Global -->
                            <div class="row mb-4" id="cardsGlobales">
                                @php
                                    $totalVentas = collect($todasLasSedes)->sum('venta_general');
                                    $totalCuotas = collect($todasLasSedes)->sum('cuota');
                                    $cumplimientoPromedio = ($totalCuotas > 0) ? ($totalVentas / $totalCuotas) * 100 : 0;
                                    $sedesCumplen = collect($todasLasSedes)
                                        ->filter(fn($s) => $s['cumplimiento_cuota'] >= 100)
                                        ->count();
                                @endphp

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-tale h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-cart text-primary mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Total</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentas">
                                                        S/ {{ number_format($totalVentas, 0, '.', ',') }}
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
                                                    <p class="mb-1 text-white small">Cuota Total</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCuotas">
                                                        S/ {{ number_format($totalCuotas, 0, '.', ',') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-blue h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-chart-line text-success mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Cumplimiento Promedio</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cumplimientoPromedio">
                                                        {{ number_format($cumplimientoPromedio, 2) }}%
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card card-light-danger h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-wrapper bg-white rounded me-3">
                                                    <i class="mdi mdi-office-building text-info mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Sedes que Cumplen</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="sedesCumplen">
                                                        <span>{{ $sedesCumplen }}</span> / <span
                                                            id="totalSedes">{{ count($todasLasSedes) }}</span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="row mb-4">
                                <!-- Gráfico Consolidado Mensual -->
                                <div class="col-lg-8 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-bar text-primary me-2"></i>
                                                Ventas Consolidadas Mensuales - <span
                                                    id="anioConsolidado">{{ $anioActual }}</span>
                                            </h4>
                                            <canvas id="consolidadoChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Top 5 Sedes -->
                                <div class="col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-trophy text-warning me-2"></i>
                                                Top 5 Sedes
                                            </h4>
                                            <canvas id="topSedesChart" height="160"></canvas>
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
                                                Comparación de Ventas Anuales - Todas las Sedes
                                            </h4>
                                            <canvas id="comparacionAnualChart" height="60"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Todas las Sedes -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-table text-info me-2"></i>
                                                    Detalle por Sede - <span id="periodoTabla">{{ $mesActual }}
                                                        {{ $anioActual }}</span>
                                                </h4>
                                                <div>
                                                    <input type="text" id="buscarSede"
                                                        class="form-control form-control-sm" placeholder="Buscar sede...">
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tablaVentas">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Sede</th>
                                                            <th>Venta General</th>
                                                            <th>Cuota</th>
                                                            <th>Diferencia</th>
                                                            <th>% Cumplimiento</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaBody">
                                                        @forelse($todasLasSedes as $index => $sede)
                                                            <tr data-sede="{{ $sede['sede'] }}">
                                                                <td class="fw-bold">{{ $index + 1 }}</td>
                                                                <td class="fw-semibold">{{ $sede['sede'] }}</td>
                                                                <td>S/
                                                                    {{ number_format($sede['venta_general'], 0, '.', ',') }}
                                                                </td>
                                                                <td>S/ {{ number_format($sede['cuota'], 0, '.', ',') }}
                                                                </td>
                                                                <td
                                                                    class="{{ $sede['diferencia'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                                    {{ $sede['diferencia'] >= 0 ? '+' : '' }}S/
                                                                    {{ number_format($sede['diferencia'], 0, '.', ',') }}
                                                                </td>
                                                                <td
                                                                    class="fw-bold 
                                                            @if ($sede['cumplimiento_cuota'] >= 100) text-success
                                                            @elseif($sede['cumplimiento_cuota'] >= 70) text-warning
                                                            @else text-danger @endif">
                                                                    {{ number_format($sede['cumplimiento_cuota'], 2) }}%
                                                                </td>
                                                                <td class="text-center">
                                                                    @if ($sede['cumplimiento_cuota'] >= 100)
                                                                        <span class="badge badge-success">
                                                                            <i
                                                                                class="mdi mdi-check-circle me-1"></i>Cumplido
                                                                        </span>
                                                                    @elseif($sede['cumplimiento_cuota'] >= 70)
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
                                                                <td colspan="7" class="text-center py-4 text-muted">
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

        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        #buscarSede {
            max-width: 200px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let consolidadoChart, topSedesChart, comparacionAnualChart;

        // Datos iniciales
        let datosConsolidados = {
            meses: @json($datosConsolidados['meses']),
            ventas: @json($datosConsolidados['ventas']),
            cuotas: @json($datosConsolidados['cuotas'])
        };

        let comparacionAnual = {
            anios: @json($comparacionAnual['anios']),
            ventas: @json($comparacionAnual['ventas'])
        };

        let sedesActuales = @json($todasLasSedes);

        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();

            // Event listeners
            document.getElementById('anioSelect').addEventListener('change', actualizarDatos);
            document.getElementById('mesSelect').addEventListener('change', actualizarDatos);
            document.getElementById('buscarSede').addEventListener('input', filtrarTabla);
        });

        // Función para actualizar datos vía AJAX
        async function actualizarDatos() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            try {
                const response = await fetch(`{{ route('comercial.ventas.sedes.data') }}?anio=${anio}&mes=${mes}`);
                const data = await response.json();

                // Actualizar sedes actuales
                sedesActuales = data.sedes;

                // Actualizar cards globales
                actualizarCardsGlobales(data.sedes);

                // Actualizar consolidado
                datosConsolidados = data.consolidado;
                actualizarGraficoConsolidado();

                // Actualizar top sedes
                actualizarGraficoTopSedes(data.sedes);

                // Actualizar tabla
                actualizarTabla(data.sedes);

                // Actualizar títulos
                document.getElementById('anioConsolidado').textContent = anio;
                document.getElementById('periodoTabla').textContent = mes + ' ' + anio;

            } catch (error) {
                console.error('Error al actualizar datos:', error);
            }
        }

        // Actualizar cards globales
        function actualizarCardsGlobales(sedes) {
            const totalVentas = sedes.reduce((sum, s) => sum + s.venta_general, 0);
            const totalCuotas = sedes.reduce((sum, s) => sum + s.cuota, 0);
            const cumplimientoPromedio = (totalCuotas > 0) ? (totalVentas / totalCuotas) * 100 : 0; //PROMEDIO
            const sedesCumplen = sedes.filter(s => s.cumplimiento_cuota >= 100).length;

            document.getElementById('totalVentas').textContent = 'S/ ' + formatNumber(totalVentas);
            document.getElementById('totalCuotas').textContent = 'S/ ' + formatNumber(totalCuotas);
            document.getElementById('cumplimientoPromedio').textContent = cumplimientoPromedio.toFixed(2) + '%';
            document.getElementById('sedesCumplen').innerHTML =
                `<span>${sedesCumplen}</span> / <span id="totalSedes">${sedes.length}</span>`;
        }

        // Formatear números
        function formatNumber(num) {
            return Math.round(num).toLocaleString('es-PE');
        }

        // Inicializar gráficos
        function inicializarGraficos() {
            // Gráfico Consolidado
            const ctxConsolidado = document.getElementById('consolidadoChart').getContext('2d');
            consolidadoChart = new Chart(ctxConsolidado, {
                type: 'bar',
                data: {
                    labels: datosConsolidados.meses,
                    datasets: [{
                        label: 'Ventas',
                        data: datosConsolidados.ventas,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }, {
                        label: 'Cuota',
                        data: datosConsolidados.cuotas,
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
                                    return 'S/ ' + (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico Top 5 Sedes
            const top5 = sedesActuales.slice(0, 5);
            const ctxTop = document.getElementById('topSedesChart').getContext('2d');
            topSedesChart = new Chart(ctxTop, {
                type: 'doughnut',
                data: {
                    labels: top5.map(s => s.sede),
                    datasets: [{
                        data: top5.map(s => s.cumplimiento_cuota),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico Comparación Anual
            const ctxAnual = document.getElementById('comparacionAnualChart').getContext('2d');
            comparacionAnualChart = new Chart(ctxAnual, {
                type: 'line',
                data: {
                    labels: comparacionAnual.anios,
                    datasets: [{
                        label: 'Venta Total Anual',
                        data: comparacionAnual.ventas,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 6,
                        pointBackgroundColor: 'rgba(153, 102, 255, 1)',
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

        // Actualizar gráfico consolidado
        function actualizarGraficoConsolidado() {
            consolidadoChart.data.labels = datosConsolidados.meses;
            consolidadoChart.data.datasets[0].data = datosConsolidados.ventas;
            consolidadoChart.data.datasets[1].data = datosConsolidados.cuotas;
            consolidadoChart.update();
        }

        // Actualizar gráfico top sedes
        function actualizarGraficoTopSedes(sedes) {
            const top5 = sedes.slice(0, 5);
            topSedesChart.data.labels = top5.map(s => s.sede);
            topSedesChart.data.datasets[0].data = top5.map(s => s.cumplimiento_cuota);
            topSedesChart.update();
        }

        // Actualizar tabla
        function actualizarTabla(sedes) {
            const tbody = document.getElementById('tablaBody');

            if (sedes.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="mdi mdi-inbox mdi-48px d-block mb-3"></i>
                    No hay datos disponibles
                </td>
            </tr>
        `;
                return;
            }

            let html = '';
            sedes.forEach((sede, index) => {
                const cumplimiento = sede.cumplimiento_cuota;
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

                const diferencia = sede.venta_general - sede.cuota;
                const diferenciaClass = diferencia >= 0 ? 'text-success' : 'text-danger';
                const diferenciaSigno = diferencia >= 0 ? '+' : '';

                html += `
            <tr data-sede="${sede.sede}">
                <td class="fw-bold">${index + 1}</td>
                <td class="fw-semibold">${sede.sede}</td>
                <td>S/ ${formatNumber(sede.venta_general)}</td>
                <td>S/ ${formatNumber(sede.cuota)}</td>
                <td class="${diferenciaClass} fw-bold">${diferenciaSigno}S/ ${formatNumber(diferencia)}</td>
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

        // Filtrar tabla
        function filtrarTabla() {
            const filtro = document.getElementById('buscarSede').value.toUpperCase();
            const filas = document.querySelectorAll('#tablaBody tr[data-sede]');

            filas.forEach(fila => {
                const sede = fila.getAttribute('data-sede');
                if (sede.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }
    </script>
@endsection
