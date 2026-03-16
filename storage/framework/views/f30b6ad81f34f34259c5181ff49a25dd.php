

<?php $__env->startSection('title', 'Evolutivo — Asignación de Bases'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="mb-1 fw-bold">
                            <i class="me-2 text-primary mdi mdi-chart-timeline-variant"></i>
                            Evolutivo — Asignación de Bases
                        </h2>
                        <p class="mb-0 text-muted small">
                            <i class="me-1 mdi mdi-database"></i>
                            Rectificados vs Normal · KPI = Rectificado / Normal
                        </p>
                    </div>

                    
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <label class="mb-1 text-muted form-label small">Año</label>
                            <select id="anioSelect" class="form-select-sm form-select">
                                <?php $__currentLoopData = $aniosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo $anio; ?>" <?php echo $anio == $anioActual ? 'selected' : ''; ?>>
                                        <?php echo $anio; ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <button id="btnLimpiarCache" class="btn-outline-secondary btn btn-sm" title="Limpiar caché">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h5 class="mb-0 card-title">
                                <i class="me-2 text-primary mdi mdi-calendar-today"></i>
                                Evolutivo Diario
                            </h5>
                            
                            <div class="d-flex align-items-center gap-2">
                                <select id="mesSelectDiario" class="form-select-sm form-select" style="width:140px">
                                    <?php $__currentLoopData = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo $num; ?>" <?php echo $num == $mesActual ? 'selected' : ''; ?>>
                                            <?php echo $nombre; ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <span class="bg-primary badge" id="labelPeriodoDiario"><?php echo $mesNombre; ?>

                                    <?php echo $anioActual; ?></span>
                            </div>
                        </div>

                        
                        <p class="mb-2 text-muted small fw-semibold">
                            <i class="me-1 mdi mdi-counter"></i>Cantidad
                        </p>
                        <div class="table-responsive mb-4">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaDiariaCantidad">
                                <thead>
                                    <tr class="table-dark" id="headerDiarioCantidad">
                                        <th style="min-width:90px">Estado</th>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th class="text-center small">
                                                <?php echo str_pad($d['dia'], 2, '0', STR_PAD_LEFT); ?>/<?php echo str_pad($mesActual, 2, '0', STR_PAD_LEFT); ?>/<?php echo $anioActual; ?>

                                            </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyDiarioCantidad">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo $d['R_cantidad'] ?: ''; ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-danger text-white text-center fw-bold"><?php echo $diario['total_R']; ?></td>
                                        <td class="text-center fw-bold"><?php echo $diario['total_R']; ?></td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo $d['N_cantidad'] ?: ''; ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-success text-white text-center fw-bold"><?php echo $diario['total_N']; ?></td>
                                        <td class="text-center fw-bold"><?php echo $diario['total_N']; ?></td>
                                    </tr>
                                    <tr class="fila-kpi">
                                        <td class="fw-bold" style="background:#fff3cd;">KPI</td>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small" style="background:#fff3cd;">
                                                <?php if($d['kpi'] > 0): ?>
                                                    <span
                                                        class="badge <?php echo $d['kpi'] < 5 ? 'bg-success' : ($d['kpi'] < 7 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                                        <?php echo $d['kpi']; ?>%
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <span
                                                class="badge <?php echo $diario['total_kpi'] < 5 ? 'bg-success' : ($diario['total_kpi'] < 7 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                                <?php echo $diario['total_kpi']; ?>%
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <?php echo $diario['total_kpi']; ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        
                        <p class="mb-2 text-muted small fw-semibold">
                            <i class="me-1 mdi mdi-currency-usd"></i>Monto (S/)
                        </p>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaDiariaPrecio">
                                <thead>
                                    <tr class="table-dark" id="headerDiarioPrecio">
                                        <th style="min-width:90px">Estado</th>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th class="text-center small">
                                                <?php echo str_pad($d['dia'], 2, '0', STR_PAD_LEFT); ?>/<?php echo str_pad($mesActual, 2, '0', STR_PAD_LEFT); ?>/<?php echo $anioActual; ?>

                                            </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyDiarioPrecio">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small">
                                                <?php echo $d['R_precio'] > 0 ? 'S/ ' . number_format($d['R_precio'], 0, '.', ',') : ''; ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-danger text-white text-center fw-bold">S/
                                            <?php echo number_format($diario['total_R_precio'], 0, '.', ','); ?></td>
                                        <td class="text-center fw-bold">S/
                                            <?php echo number_format($diario['total_R_precio'], 0, '.', ','); ?></td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        <?php $__currentLoopData = $diario['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small">
                                                <?php echo $d['N_precio'] > 0 ? 'S/ ' . number_format($d['N_precio'], 0, '.', ',') : ''; ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-success text-white text-center fw-bold">S/
                                            <?php echo number_format($diario['total_N_precio'], 0, '.', ','); ?></td>
                                        <td class="text-center fw-bold">S/
                                            <?php echo number_format($diario['total_N_precio'], 0, '.', ','); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 card-title">
                                <i class="me-2 text-info mdi mdi-calendar-month"></i>
                                Evolutivo Mensual — Cantidad
                            </h5>
                            <span class="bg-info badge" id="labelAnioMensual"><?php echo $anioActual; ?></span>
                        </div>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th class="text-center small"><?php echo Str::upper(substr($m['label'], 0, 3)); ?></th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMensualCantidad">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo $m['R_cantidad'] ?: ''; ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-danger text-white text-center fw-bold"><?php echo $mensual['total_R']; ?>

                                        </td>
                                        <td class="text-center fw-bold"><?php echo $mensual['total_R']; ?></td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center"><?php echo $m['N_cantidad'] ?: ''; ?></td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-success text-white text-center fw-bold"><?php echo $mensual['total_N']; ?>

                                        </td>
                                        <td class="text-center fw-bold"><?php echo $mensual['total_N']; ?></td>
                                    </tr>
                                    <tr class="fila-kpi">
                                        <td class="fw-bold" style="background:#fff3cd;">KPI</td>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small" style="background:#fff3cd;">
                                                <?php if($m['kpi'] > 0): ?>
                                                    <span
                                                        class="badge <?php echo $m['kpi'] < 5 ? 'bg-success' : ($m['kpi'] < 7 ? 'bg-warning text-dark' : 'bg-danger'); ?>"><?php echo $m['kpi']; ?>%</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <span
                                                class="badge <?php echo $mensual['total_kpi'] < 5 ? 'bg-success' : ($mensual['total_kpi'] < 7 ? 'bg-warning text-dark' : 'bg-danger'); ?>"><?php echo $mensual['total_kpi']; ?>%</span>
                                        </td>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <?php echo $mensual['total_kpi']; ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 card">
                    <div class="card-body">
                        <h5 class="mb-3 card-title">
                            <i class="me-2 text-warning mdi mdi-currency-usd"></i>
                            Evolutivo Mensual — Monto (S/)
                        </h5>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <th class="text-center small"><?php echo Str::upper(substr($m['label'], 0, 3)); ?></th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMensualPrecio">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small">
                                                <?php echo $m['R_precio'] > 0 ? 'S/ ' . number_format($m['R_precio'], 0, '.', ',') : ''; ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-danger text-white text-center fw-bold">S/
                                            <?php echo number_format($mensual['total_R_precio'], 0, '.', ','); ?></td>
                                        <td class="text-center fw-bold">S/
                                            <?php echo number_format($mensual['total_R_precio'], 0, '.', ','); ?></td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        <?php $__currentLoopData = $mensual['meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center small">
                                                <?php echo $m['N_precio'] > 0 ? 'S/ ' . number_format($m['N_precio'], 0, '.', ',') : ''; ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <td class="bg-success text-white text-center fw-bold">S/
                                            <?php echo number_format($mensual['total_N_precio'], 0, '.', ','); ?></td>
                                        <td class="text-center fw-bold">S/
                                            <?php echo number_format($mensual['total_N_precio'], 0, '.', ','); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 row">
                    <div class="mb-3 col-lg-6">
                        <div class="h-100 card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                    <i class="me-2 text-danger mdi mdi-chart-line"></i>
                                    Rectificados por Día — Cantidad
                                </h5>
                                <canvas id="grafLinealDiario" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <div class="h-100 card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                    <i class="me-2 text-warning mdi mdi-chart-bar"></i>
                                    Rectificados por Mes — Monto (S/)
                                </h5>
                                <canvas id="grafBarrasMensual" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .evolutivo-table th,
        .evolutivo-table td {
            white-space: nowrap;
            font-size: 0.78rem;
            padding: 4px 7px;
        }

        .fila-rectifica td {
            background-color: #fff5f5;
        }

        .fila-normal td {
            background-color: #f0fff4;
        }

        .fila-kpi td {
            background-color: #fffbeb;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let grafLinea, grafBarras;

        const grafLineaData = <?php echo json_encode($grafLinea, 15, 512) ?>;
        const grafBarrasData = <?php echo json_encode($grafBarras, 15, 512) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();
            document.getElementById('anioSelect').addEventListener('change', actualizarMensual);
            document.getElementById('mesSelectDiario').addEventListener('change', actualizarDiario);
            document.getElementById('btnLimpiarCache').addEventListener('click', limpiarCache);
        });

        function inicializarGraficos() {
            grafLinea = new Chart(document.getElementById('grafLinealDiario'), {
                type: 'line',
                data: {
                    labels: grafLineaData.labels,
                    datasets: [{
                        label: 'Rectificados',
                        data: grafLineaData.data,
                        borderColor: 'rgba(220,53,69,1)',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(220,53,69,1)',
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
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            grafBarras = new Chart(document.getElementById('grafBarrasMensual'), {
                type: 'bar',
                data: {
                    labels: grafBarrasData.labels,
                    datasets: [{
                        label: 'S/ Rectificados',
                        data: grafBarrasData.data,
                        backgroundColor: 'rgba(255,193,7,0.8)',
                        borderColor: 'rgba(255,193,7,1)',
                        borderWidth: 2,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'S/ ' + ctx.parsed.y.toLocaleString('es-PE')
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + val.toLocaleString('es-PE')
                            }
                        }
                    }
                }
            });
        }

        // ── Actualizar tablas DIARIAS + gráfico línea (cambio de mes o año) ────────
        async function actualizarDiario() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelectDiario').value;

            try {
                const res = await fetch(
                    `<?php echo route('produccion.asignacion-bases.evolutivo-diario-data'); ?>?anio=${anio}&mes=${mes}`);
                const data = await res.json();

                renderTablaDiariaCantidad(data);
                renderTablaDiariaPrecio(data);

                // Actualizar gráfico de línea con datos del mes seleccionado
                grafLinea.data.labels = data.grafLinea.labels;
                grafLinea.data.datasets[0].data = data.grafLinea.data;
                grafLinea.update();

                const mesOpt = document.getElementById('mesSelectDiario');
                document.getElementById('labelPeriodoDiario').textContent =
                    mesOpt.options[mesOpt.selectedIndex].text + ' ' + anio;

            } catch (e) {
                console.error('Error diario:', e);
            }
        }

        // ── Actualizar tablas MENSUALES (cambio de año) ───────────────────────────
        async function actualizarMensual() {
            const anio = document.getElementById('anioSelect').value;

            try {
                const res = await fetch(`<?php echo route('produccion.asignacion-bases.evolutivo-data'); ?>?anio=${anio}`);
                const data = await res.json();

                renderTablaMensualCantidad(data.mensual);
                renderTablaMensualPrecio(data.mensual);

                grafBarras.data.labels = data.grafBarras.labels;
                grafBarras.data.datasets[0].data = data.grafBarras.data;
                grafBarras.update();

                document.getElementById('labelAnioMensual').textContent = anio;

                // También actualizar diario con el nuevo año
                await actualizarDiario();

            } catch (e) {
                console.error('Error mensual:', e);
            }
        }

        // ── Renders ───────────────────────────────────────────────────────────────
        function badgeKpi(kpi) {
            if (!kpi) return '';
            let cls;
            if (kpi < 5) cls = 'bg-success';
            else if (kpi < 7) cls = 'bg-warning text-dark';
            else cls = 'bg-danger';
            return `<span class="badge ${cls}">${kpi}%</span>`;
        }

        function renderTablaDiariaCantidad(data) {
            const dias = data.dias;
            const thead = document.getElementById('headerDiarioCantidad');
            const tbody = document.getElementById('bodyDiarioCantidad');

            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelectDiario').value.toString().padStart(2, '0');

            // Rebuild headers con fecha dd/mm/yyyy
            let headers = '<tr class="table-dark"><th style="min-width:90px">Estado</th>';
            dias.forEach(d => {
                const dia = d.dia.toString().padStart(2, '0');
                headers += `<th class="text-center small">${dia}/${mes}/${anio}</th>`;
            });
            headers += '<th class="text-center">TOTAL</th><th class="text-center">ACUM</th></tr>';
            thead.innerHTML = headers;

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;
            let rowK = `<tr class="fila-kpi"><td class="fw-bold" style="background:#fff3cd;">KPI</td>`;

            dias.forEach(d => {
                rowR += `<td class="text-center">${d.R_cantidad || ''}</td>`;
                rowN += `<td class="text-center">${d.N_cantidad || ''}</td>`;
                rowK += `<td class="text-center small" style="background:#fff3cd;">${badgeKpi(d.kpi)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">${data.total_R}</td><td class="text-center fw-bold">${data.total_R}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">${data.total_N}</td><td class="text-center fw-bold">${data.total_N}</td></tr>`;
            rowK +=
                `<td class="text-center fw-bold" style="background:#fff3cd;">${badgeKpi(data.total_kpi)}</td><td class="text-center fw-bold" style="background:#fff3cd;">${data.total_kpi}%</td></tr>`;

            tbody.innerHTML = rowR + rowN + rowK;
        }

        function renderTablaDiariaPrecio(data) {
            const dias = data.dias;
            const thead = document.getElementById('headerDiarioPrecio');
            const tbody = document.getElementById('bodyDiarioPrecio');
            const fmt = v => v > 0 ? 'S/ ' + Math.round(v).toLocaleString('es-PE') : '';

            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelectDiario').value.toString().padStart(2, '0');

            let headers = '<tr class="table-dark"><th style="min-width:90px">Estado</th>';
            dias.forEach(d => {
                const dia = d.dia.toString().padStart(2, '0');
                headers += `<th class="text-center small">${dia}/${mes}/${anio}</th>`;
            });
            headers += '<th class="text-center">TOTAL</th><th class="text-center">ACUM</th></tr>';
            thead.innerHTML = headers;

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;

            dias.forEach(d => {
                rowR += `<td class="text-center small">${fmt(d.R_precio)}</td>`;
                rowN += `<td class="text-center small">${fmt(d.N_precio)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">S/ ${Math.round(data.total_R_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(data.total_R_precio).toLocaleString('es-PE')}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">S/ ${Math.round(data.total_N_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(data.total_N_precio).toLocaleString('es-PE')}</td></tr>`;

            tbody.innerHTML = rowR + rowN;
        }

        function renderTablaMensualCantidad(mensual) {
            const tbody = document.getElementById('bodyMensualCantidad');
            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;
            let rowK = `<tr class="fila-kpi"><td class="fw-bold" style="background:#fff3cd;">KPI</td>`;

            mensual.meses.forEach(m => {
                rowR += `<td class="text-center">${m.R_cantidad || ''}</td>`;
                rowN += `<td class="text-center">${m.N_cantidad || ''}</td>`;
                rowK += `<td class="text-center small" style="background:#fff3cd;">${badgeKpi(m.kpi)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">${mensual.total_R}</td><td class="text-center fw-bold">${mensual.total_R}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">${mensual.total_N}</td><td class="text-center fw-bold">${mensual.total_N}</td></tr>`;
            rowK +=
                `<td class="text-center fw-bold" style="background:#fff3cd;">${badgeKpi(mensual.total_kpi)}</td><td class="text-center fw-bold" style="background:#fff3cd;">${mensual.total_kpi}%</td></tr>`;

            tbody.innerHTML = rowR + rowN + rowK;
        }

        function renderTablaMensualPrecio(mensual) {
            const tbody = document.getElementById('bodyMensualPrecio');
            const fmt = v => v > 0 ? 'S/ ' + Math.round(v).toLocaleString('es-PE') : '';

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;

            mensual.meses.forEach(m => {
                rowR += `<td class="text-center small">${fmt(m.R_precio)}</td>`;
                rowN += `<td class="text-center small">${fmt(m.N_precio)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">S/ ${Math.round(mensual.total_R_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(mensual.total_R_precio).toLocaleString('es-PE')}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">S/ ${Math.round(mensual.total_N_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(mensual.total_N_precio).toLocaleString('es-PE')}</td></tr>`;

            tbody.innerHTML = rowR + rowN;
        }

        async function limpiarCache() {
            const btn = document.getElementById('btnLimpiarCache');
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';
            try {
                await fetch('<?php echo route('produccion.asignacion-bases.clear-cache'); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                    }
                });
                await actualizarMensual();
            } catch (e) {
                console.error(e);
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-refresh"></i>';
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/produccion/asignacion-bases/evolutivo.blade.php ENDPATH**/ ?>