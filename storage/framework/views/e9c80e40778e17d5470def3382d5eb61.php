<?php $__env->startSection('title', 'Ventas por Sedes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            <!-- Header con Selectores -->
                            <div class="mb-4 row">
                                <div class="col-lg-8">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="me-2 text-primary mdi mdi-chart-line"></i>
                                        Ventas por Sedes
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <i class="me-2 mdi mdi-store"></i>
                                        Monitoreo de todas las sedes
                                    </p>
                                </div>

                                <!-- Selectores de Año y Mes -->
                                <div class="col-lg-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="mb-1 text-muted form-label small">Año</label>
                                            <select id="anioSelect" class="form-select">
                                                <?php $__currentLoopData = $aniosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $anio; ?>"
                                                        <?php echo $anio == $anioActual ? 'selected' : ''; ?>>
                                                        <?php echo $anio; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="mb-1 text-muted form-label small">Mes</label>
                                            <select id="mesSelect" class="form-select">
                                                <option value="Enero" <?php echo $mesActual == 'Enero' ? 'selected' : ''; ?>>Enero
                                                </option>
                                                <option value="Febrero" <?php echo $mesActual == 'Febrero' ? 'selected' : ''; ?>>
                                                    Febrero</option>
                                                <option value="Marzo" <?php echo $mesActual == 'Marzo' ? 'selected' : ''; ?>>Marzo
                                                </option>
                                                <option value="Abril" <?php echo $mesActual == 'Abril' ? 'selected' : ''; ?>>Abril
                                                </option>
                                                <option value="Mayo" <?php echo $mesActual == 'Mayo' ? 'selected' : ''; ?>>Mayo
                                                </option>
                                                <option value="Junio" <?php echo $mesActual == 'Junio' ? 'selected' : ''; ?>>Junio
                                                </option>
                                                <option value="Julio" <?php echo $mesActual == 'Julio' ? 'selected' : ''; ?>>Julio
                                                </option>
                                                <option value="Agosto" <?php echo $mesActual == 'Agosto' ? 'selected' : ''; ?>>
                                                    Agosto</option>
                                                <option value="Septiembre"
                                                    <?php echo $mesActual == 'Septiembre' || $mesActual == 'Setiembre' ? 'selected' : ''; ?>>
                                                    Septiembre</option>
                                                <option value="Octubre" <?php echo $mesActual == 'Octubre' ? 'selected' : ''; ?>>
                                                    Octubre</option>
                                                <option value="Noviembre"
                                                    <?php echo $mesActual == 'Noviembre' ? 'selected' : ''; ?>>Noviembre</option>
                                                <option value="Diciembre"
                                                    <?php echo $mesActual == 'Diciembre' ? 'selected' : ''; ?>>Diciembre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Resumen Global -->
                            <div class="mb-4 row" id="cardsGlobales">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="text-muted">
                                        <i class="me-1 mdi mdi-store"></i> Ventas Generales —
                                        <span id="periodoDigital"><?php echo $mesActual; ?> <?php echo $anioActual; ?></span>
                                    </h6>
                                </div>
                                <?php
                                    $totalVentas = collect($todasLasSedes)->sum('venta_general');
                                    $totalCuotas = collect($todasLasSedes)->sum('cuota');
                                    $cumplimientoPromedio = $totalCuotas > 0 ? ($totalVentas / $totalCuotas) * 100 : 0;
                                    $sedesCumplen = collect($todasLasSedes)
                                        ->filter(fn($s) => $s['cumplimiento_cuota'] >= 100)
                                        ->count();
                                ?>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-cart mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Total</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentas">
                                                        S/ <?php echo number_format($totalVentas, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-warning mdi mdi-target mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Cuota Total</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCuotas">
                                                        S/ <?php echo number_format($totalCuotas, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-success mdi mdi-chart-line mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Cumplimiento Promedio</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cumplimientoPromedio">
                                                        <?php echo number_format($cumplimientoPromedio, 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-info mdi mdi-office-building mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Sedes que Cumplen</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="sedesCumplen">
                                                        <span><?php echo $sedesCumplen; ?></span> / <span
                                                            id="totalSedes"><?php echo count($todasLasSedes); ?></span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cards Ventas Digitales -->
                            <div class="mb-4 row" id="cardsDigitales">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="text-muted">
                                        <i class="me-1 mdi mdi-monitor"></i> Ventas Digitales —
                                        <span id="periodoDigital"><?php echo $mesActual; ?> <?php echo $anioActual; ?></span>
                                    </h6>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-monitor-dashboard mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentaDigital">
                                                        S/
                                                        <?php echo number_format(collect($todasLasSedes)->sum('venta_digital'), 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-warning mdi mdi-chart-line mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Proy. Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentaProyDigital">
                                                        S/
                                                        <?php echo number_format(collect($todasLasSedes)->sum('venta_proy_digital'), 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-success mdi mdi-target mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Cuota Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCuotaDigital">
                                                        S/
                                                        <?php echo number_format(collect($todasLasSedes)->sum('cuota_digital'), 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-info mdi mdi-percent mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Cum. Cuota Digital</p>
                                                    <?php
                                                        $totalVentaDigital = collect($todasLasSedes)->sum(
                                                            'venta_digital',
                                                        );
                                                        $totalCuotaDigital = collect($todasLasSedes)->sum(
                                                            'cuota_digital',
                                                        );
                                                        $cumDigitalPromedio =
                                                            $totalCuotaDigital > 0
                                                                ? ($totalVentaDigital / $totalCuotaDigital) * 100
                                                                : 0;
                                                    ?>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCumCuotaDigital">
                                                        <?php echo number_format($cumDigitalPromedio, 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="mb-4 row">
                                <!-- Gráfico Consolidado Mensual -->
                                <div class="mb-3 col-lg-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-primary mdi mdi-chart-bar"></i>
                                                Ventas Consolidadas Mensuales - <span
                                                    id="anioConsolidado"><?php echo $anioActual; ?></span>
                                            </h4>
                                            <canvas id="consolidadoChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Top 5 Sedes -->
                                <div class="mb-3 col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-warning mdi mdi-trophy"></i>
                                                Top 5 Sedes
                                            </h4>
                                            <canvas id="topSedesChart" height="160"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráfico Comparación Anual -->
                            <div class="mb-4 row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-info mdi mdi-chart-areaspline"></i>
                                                Comparación de <?php echo $mesActual; ?> - Histórico
                                                de Años (Todas las Sedes)
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
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <h4 class="mb-0 card-title">
                                                    <i class="mdi-table me-2 text-info mdi"></i>
                                                    Detalle por Sede - <span id="periodoTabla"><?php echo $mesActual; ?>

                                                        <?php echo $anioActual; ?></span>
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
                                                        <?php $__empty_1 = true; $__currentLoopData = $todasLasSedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr data-sede="<?php echo $sede['sede']; ?>">
                                                                <td class="fw-bold"><?php echo $index + 1; ?></td>
                                                                <td class="fw-semibold"><?php echo $sede['sede']; ?></td>
                                                                <td>S/
                                                                    <?php echo number_format($sede['venta_general'], 0, '.', ','); ?>

                                                                </td>
                                                                <td>S/ <?php echo number_format($sede['cuota'], 0, '.', ','); ?>

                                                                </td>
                                                                <td
                                                                    class="<?php echo $sede['diferencia'] >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                                    <?php echo $sede['diferencia'] >= 0 ? '+' : ''; ?>S/
                                                                    <?php echo number_format($sede['diferencia'], 0, '.', ','); ?>

                                                                </td>
                                                                <td
                                                                    class="fw-bold 
                                                            <?php if($sede['cumplimiento_cuota'] >= 100): ?> text-success
                                                            <?php elseif($sede['cumplimiento_cuota'] >= 70): ?> text-warning
                                                            <?php else: ?> text-danger <?php endif; ?>">
                                                                    <?php echo number_format($sede['cumplimiento_cuota'], 2); ?>%
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if($sede['cumplimiento_cuota'] >= 100): ?>
                                                                        <span class="badge badge-success">
                                                                            <i
                                                                                class="me-1 mdi mdi-check-circle"></i>Cumplido
                                                                        </span>
                                                                    <?php elseif($sede['cumplimiento_cuota'] >= 70): ?>
                                                                        <span class="badge badge-warning">
                                                                            <i class="me-1 mdi mdi-alert"></i>En Progreso
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-danger">
                                                                            <i class="me-1 mdi mdi-close-circle"></i>Bajo
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <tr>
                                                                <td colspan="7" class="py-4 text-muted text-center">
                                                                    <i class="d-block mb-3 mdi mdi-inbox mdi-48px"></i>
                                                                    No hay datos disponibles
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
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
            meses: <?php echo json_encode($datosConsolidados['meses'], 15, 512) ?>,
            ventas: <?php echo json_encode($datosConsolidados['ventas'], 15, 512) ?>,
            cuotas: <?php echo json_encode($datosConsolidados['cuotas'], 15, 512) ?>
        };

        let comparacionAnual = {
            anios: <?php echo json_encode($comparacionAnual['anios'], 15, 512) ?>,
            ventas: <?php echo json_encode($comparacionAnual['ventas'], 15, 512) ?>
        };

        let sedesActuales = <?php echo json_encode($todasLasSedes, 15, 512) ?>;

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
                const response = await fetch(`<?php echo route('comercial.ventas.sedes.data'); ?>?anio=${anio}&mes=${mes}`);
                const data = await response.json();

                // Actualizar sedes actuales
                sedesActuales = data.sedes;

                // Actualizar cards globales
                actualizarCardsGlobales(data.sedes);

                // Actualizar consolidado
                datosConsolidados = data.consolidado;
                actualizarGraficoConsolidado();

                // Actualizar comparación anual
                comparacionAnual = data.comparacion;
                actualizarGraficoComparacionAnual();

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
            // Cards normales
            const totalVentas = sedes.reduce((sum, s) => sum + s.venta_general, 0);
            const totalCuotas = sedes.reduce((sum, s) => sum + s.cuota, 0);
            const cumplimientoPromedio = (totalCuotas > 0) ? (totalVentas / totalCuotas) * 100 : 0;
            const sedesCumplen = sedes.filter(s => s.cumplimiento_cuota >= 100).length;

            document.getElementById('totalVentas').textContent = 'S/ ' + formatNumber(totalVentas);
            document.getElementById('totalCuotas').textContent = 'S/ ' + formatNumber(totalCuotas);
            document.getElementById('cumplimientoPromedio').textContent = cumplimientoPromedio.toFixed(2) + '%';
            document.getElementById('sedesCumplen').innerHTML =
                `<span>${sedesCumplen}</span> / <span id="totalSedes">${sedes.length}</span>`;

            // Cards digitales
            const totalVentaDigital = sedes.reduce((sum, s) => sum + (s.venta_digital || 0), 0);
            const totalVentaProyDigital = sedes.reduce((sum, s) => sum + (s.venta_proy_digital || 0), 0);
            const totalCuotaDigital = sedes.reduce((sum, s) => sum + (s.cuota_digital || 0), 0);
            const cumDigital = totalCuotaDigital > 0 ? (totalVentaDigital / totalCuotaDigital) * 100 : 0;

            document.getElementById('totalVentaDigital').textContent = 'S/ ' + formatNumber(totalVentaDigital);
            document.getElementById('totalVentaProyDigital').textContent = 'S/ ' + formatNumber(totalVentaProyDigital);
            document.getElementById('totalCuotaDigital').textContent = 'S/ ' + formatNumber(totalCuotaDigital);

            const cumEl = document.getElementById('totalCumCuotaDigital');
            cumEl.textContent = cumDigital.toFixed(2) + '%';
            cumEl.className = 'mb-0 fw-bold ' + (cumDigital >= 100 ? 'text-success' : cumDigital >= 80 ? 'text-warning' :
                'text-danger');

            // Actualizar periodo digital
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;
            document.getElementById('periodoDigital').textContent = mes + ' ' + anio;
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
                        label: 'Venta Total del Mes',
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
                <td colspan="7" class="py-4 text-muted text-center">
                    <i class="d-block mb-3 mdi mdi-inbox mdi-48px"></i>
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

        function actualizarGraficoComparacionAnual() {
            comparacionAnualChart.data.labels = comparacionAnual.anios;
            comparacionAnualChart.data.datasets[0].data = comparacionAnual.ventas;
            comparacionAnualChart.update();
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/ventas-sedes.blade.php ENDPATH**/ ?>