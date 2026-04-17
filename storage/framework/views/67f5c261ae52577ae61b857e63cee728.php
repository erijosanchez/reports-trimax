


<?php $__env->startSection('title', 'Dashboard RRHH'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">

                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="mdi mdi-chart-bar"></i> Dashboard RRHH
                            </h3>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <a href="<?php echo route('rrhh.requerimientos.index'); ?>" class="btn-outline-primary btn">
                                <i class="mdi mdi-view-list"></i> Ver Requerimientos
                            </a>
                            <a href="<?php echo route('rrhh.requerimientos.export'); ?>" class="btn-outline-success btn">
                                <i class="mdi mdi-file-excel"></i> Exportar
                            </a>
                        </div>
                    </div>

                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active">

                            
                            <div class="mb-4 row">
                                <div class="col-md-12">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                                <div>
                                                    <h5 class="mb-1 text-primary">
                                                        <i class="mdi mdi-speedometer"></i> Indicador General de
                                                        Cumplimiento SLA
                                                    </h5>
                                                    <p class="mb-0 text-muted small">
                                                        Fórmula: Reqs. contratados ≤ 45 días / Total × 100
                                                    </p>
                                                </div>
                                                <div class="text-center">
                                                    <h1
                                                        class="mb-0 fw-bold <?php echo $semaforoColor === 'green' ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $porcentajeCumplimiento; ?>%
                                                    </h1>
                                                    <div class="d-flex justify-content-center gap-3 mt-1">
                                                        <small
                                                            class="<?php echo $semaforoColor === 'green' ? 'fw-bold' : 'text-muted'; ?>">
                                                            🟢 ≥ 80% Óptimo
                                                        </small>
                                                        <small
                                                            class="<?php echo $semaforoColor === 'red' ? 'fw-bold' : 'text-muted'; ?>">
                                                            🔴 ≤ 79.9% Bajo
                                                        </small>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span
                                                        class="badge <?php echo $semaforoColor === 'green' ? 'bg-success' : 'bg-danger'; ?> fs-5 px-4 py-3">
                                                        <?php echo $semaforoColor === 'green' ? '✅ EN META' : '⚠️ BAJO META'; ?>

                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row">
                                <div class="mb-3 col-md col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Total</p>
                                                    <h2 class="mb-0 font-weight-bold text-primary"><?php echo $total; ?></h2>
                                                </div>
                                                <div class="bg-primary icon-stat">
                                                    <i class="mdi mdi-clipboard-list"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Pendientes</p>
                                                    <h2 class="mb-0 font-weight-bold text-info"><?php echo $pendientes; ?></h2>
                                                </div>
                                                <div class="bg-info icon-stat">
                                                    <i class="mdi-clock-outline mdi"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">En Proceso</p>
                                                    <h2 class="mb-0 font-weight-bold text-warning"><?php echo $enProceso; ?></h2>
                                                </div>
                                                <div class="bg-warning icon-stat">
                                                    <i class="mdi mdi-hourglass-half"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Contratados</p>
                                                    <h2 class="mb-0 font-weight-bold text-success"><?php echo $contratados; ?></h2>
                                                </div>
                                                <div class="bg-success icon-stat">
                                                    <i class="mdi mdi-account-check"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Cancelados</p>
                                                    <h2 class="mb-0 font-weight-bold text-secondary"><?php echo $cancelados; ?>

                                                    </h2>
                                                </div>
                                                <div class="bg-secondary icon-stat">
                                                    <i class="mdi-close-circle-outline mdi"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row">

                                
                                <div class="mb-4 col-lg-5">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-chart-donut"></i> Distribución por Estado
                                            </h6>
                                            <canvas id="donaEstados" height="220"></canvas>
                                            <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                                                <span class="bg-info badge">Pendiente: <?php echo $pendientes; ?></span>
                                                <span class="bg-warning text-dark badge">En Proceso:
                                                    <?php echo $enProceso; ?></span>
                                                <span class="bg-success badge">Contratado: <?php echo $contratados; ?></span>
                                                <span class="bg-secondary badge">Cancelado: <?php echo $cancelados; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-4 col-lg-7">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-4 text-primary">
                                                <i class="mdi mdi-traffic-light"></i> Semáforo de Cumplimiento SLA
                                            </h6>

                                            <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                                                style="background:#d1fae5;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="text-success mdi mdi-circle mdi-24px"></i>
                                                    <span class="fw-semibold">Óptimo — Dentro de los 45 días</span>
                                                </div>
                                                <h4 class="mb-0 text-success fw-bold"><?php echo $optimo; ?></h4>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                                                style="background:#fef3c7;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="text-warning mdi mdi-circle mdi-24px"></i>
                                                    <span class="fw-semibold">En Riesgo — Entre 46 y 60 días</span>
                                                </div>
                                                <h4 class="mb-0 text-warning fw-bold"><?php echo $riesgo; ?></h4>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                                                style="background:#fee2e2;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="text-danger mdi mdi-circle mdi-24px"></i>
                                                    <span class="fw-semibold">Crítico — Más de 60 días</span>
                                                </div>
                                                <h4 class="mb-0 text-danger fw-bold"><?php echo $critico; ?></h4>
                                            </div>

                                            <hr>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-semibold">% Cumplimiento SLA</span>
                                                <h4
                                                    class="mb-0 <?php echo $semaforoColor === 'green' ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                    <?php echo $porcentajeCumplimiento; ?>%
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            <h6 class="mb-1 text-primary">
                                                <i class="mdi mdi-chart-bar"></i> Días Transcurridos por Requerimiento
                                                (últimos 30)
                                            </h6>
                                            <p class="mb-3 text-muted small">
                                                La línea roja indica el límite del SLA (45 días).
                                                Verde = Óptimo · Naranja = En riesgo · Rojo = Crítico
                                            </p>
                                            <canvas id="barrasSla" height="100"></canvas>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            transition: transform .2s;
        }

        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, .12);
        }

        .icon-stat {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .icon-stat.bg-primary {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
        }

        .icon-stat.bg-info {
            background: linear-gradient(135deg, #06B6D4, #0891B2);
        }

        .icon-stat.bg-warning {
            background: linear-gradient(135deg, #F59E0B, #D97706);
        }

        .icon-stat.bg-success {
            background: linear-gradient(135deg, #10B981, #059669);
        }

        .icon-stat.bg-secondary {
            background: linear-gradient(135deg, #6B7280, #4B5563);
        }

        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08) !important;
            border: none !important;
            border-radius: 10px !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // ── Dona ─────────────────────────────────────────────────
        new Chart(document.getElementById('donaEstados'), {
            type: 'doughnut',
            data: {
                labels: ['Pendiente', 'En Proceso', 'Contratado', 'Cancelado'],
                datasets: [{
                    data: [<?php echo $pendientes; ?>, <?php echo $enProceso; ?>, <?php echo $contratados; ?>,
                        <?php echo $cancelados; ?>

                    ],
                    backgroundColor: ['#06B6D4', '#F59E0B', '#10B981', '#6B7280'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                return ` ${ctx.label}: ${ctx.raw} (${t>0?((ctx.raw/t)*100).toFixed(1):0}%)`;
                            }
                        }
                    }
                }
            }
        });

        // ── Barras ───────────────────────────────────────────────
        const grafData = <?php echo json_encode($requerimientosGrafico, 15, 512) ?>;
        const colorMap = {
            optimo: '#10B981',
            riesgo: '#F59E0B',
            critico: '#EF4444',
            pendiente: '#06B6D4'
        };

        new Chart(document.getElementById('barrasSla'), {
            type: 'bar',
            data: {
                labels: grafData.map(r => r.codigo),
                datasets: [{
                    label: 'KPI Cumplimiento (%)',
                    data: grafData.map(r => r.kpi),
                    backgroundColor: grafData.map(r => (colorMap[r.semaforo] || '#06B6D4') + 'bb'),
                    borderColor: grafData.map(r => colorMap[r.semaforo] || '#06B6D4'),
                    borderWidth: 1,
                    borderRadius: 4,
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
                            afterLabel: (ctx) => {
                                const d = ctx.raw;
                                if (d === 0)   return '⏳ Sin proceso activo';
                                if (d === 100) return '✅ Dentro del SLA';
                                if (d >= 80)   return '⚠️ En riesgo';
                                return '🔴 Cumplimiento crítico';
                            }
                        }
                    },
                    annotation: {
                        annotations: {
                            slaLine: {
                                type: 'line',
                                yMin: 100,
                                yMax: 100,
                                borderColor: '#22C55E',
                                borderWidth: 2,
                                borderDash: [6, 4],
                                label: {
                                    display: true,
                                    content: 'Meta 100%',
                                    position: 'end',
                                    color: '#22C55E',
                                    font: {
                                        size: 11,
                                        weight: 'bold'
                                    }
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: 100,
                        grid: {
                            color: '#f0f0f0'
                        },
                        ticks: {
                            callback: (v) => v + '%'
                        }
                    }
                }
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/rrhh/requerimientos/dashboard.blade.php ENDPATH**/ ?>