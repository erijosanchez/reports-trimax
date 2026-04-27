
<?php $__env->startSection('title', 'Resumen Diario'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .progress {
            height: 8px;
            border-radius: 4px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-primary mdi mdi-view-dashboard"></i>Resumen Diario
                                </h4>
                                <p class="mb-0 text-muted small">Control de km y entregas por motorizado</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="date" id="sel-fecha" class="form-control form-control-sm"
                                    value="<?php echo $fecha; ?>" max="<?php echo today()->toDateString(); ?>" style="width:160px">
                                <button id="btn-fecha" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            
            <div class="mb-4 row">
                <?php
                    $kpis = [
                        [
                            'label' => 'Rutas del día',
                            'val' => $kpi['total_rutas'],
                            'color' => 'primary',
                            'icon' => 'mdi-route',
                        ],
                        [
                            'label' => 'Km totales',
                            'val' => $kpi['total_km'] . ' km',
                            'color' => 'success',
                            'icon' => 'mdi-map-marker-distance',
                        ],
                        [
                            'label' => 'Entregas ok',
                            'val' => $kpi['completadas'],
                            'color' => 'info',
                            'icon' => 'mdi-check-circle',
                        ],
                        [
                            'label' => 'Fallidas',
                            'val' => $kpi['fallidas'],
                            'color' => 'danger',
                            'icon' => 'mdi-close-circle',
                        ],
                        [
                            'label' => 'En ruta ahora',
                            'val' => $kpi['en_ruta'],
                            'color' => 'warning',
                            'icon' => 'mdi-motorbike',
                        ],
                    ];
                ?>
                <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3 col-md col-sm-6">
                        <div class="shadow-sm border-0 h-100 card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-1 text-muted small"><?php echo $k['label']; ?></p>
                                        <h3 class="mb-0 fw-bold text-<?php echo $k['color']; ?>"><?php echo $k['val']; ?></h3>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-<?php echo $k['color']; ?>"
                                        style="width:48px;height:48px;opacity:.85">
                                        <i class="mdi <?php echo $k['icon']; ?> text-white" style="font-size:22px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="row">

                
                <div class="mb-4 col-lg-8">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="me-1 text-primary mdi mdi-motorbike"></i>Detalle por motorizado
                            </h6>
                            <span class="bg-primary badge"><?php echo \Carbon\Carbon::parse($fecha)->format('d/m/Y'); ?></span>
                        </div>
                        <div class="p-0 card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Motorizado</th>
                                            <th>Sede</th>
                                            <th>Km</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Entregas</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $rutas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $comp = $r->entregas->where('estado', 'completado')->count();
                                                $fall = $r->entregas->where('estado', 'fallido')->count();
                                                $tot = $r->entregas->count();
                                                $pct = $tot > 0 ? round(($comp / $tot) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo $r->motorizado->nombre; ?></td>
                                                <td><span class="bg-secondary badge"><?php echo $r->motorizado->sede; ?></span></td>
                                                <td>
                                                    <span class="text-primary fw-bold">
                                                        <?php echo number_format($r->distance_km, 2); ?> km
                                                    </span>
                                                </td>
                                                <td class="small">
                                                    <?php echo $r->started_at?->setTimezone('America/Lima')->format('H:i') ?? '—'; ?>

                                                </td>
                                                <td class="small">
                                                    <?php echo $r->ended_at?->setTimezone('America/Lima')->format('H:i') ?? '—'; ?>

                                                </td>
                                                <td style="min-width:140px">
                                                    <div class="d-flex justify-content-between mb-1" style="font-size:11px">
                                                        <span><?php echo $comp; ?>/<?php echo $tot; ?></span>
                                                        <span><?php echo $pct; ?>%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="bg-success progress-bar"
                                                            style="width:<?php echo $tot > 0 ? round(($comp / $tot) * 100) : 0; ?>%"></div>
                                                        <div class="bg-danger progress-bar"
                                                            style="width:<?php echo $tot > 0 ? round(($fall / $tot) * 100) : 0; ?>%"></div>
                                                    </div>
                                                    <div class="d-flex gap-2 mt-1" style="font-size:10px">
                                                        <span class="text-success">✓ <?php echo $comp; ?></span>
                                                        <span class="text-danger">✗ <?php echo $fall; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($r->status === 'completada'): ?>
                                                        <span class="bg-success badge">Completada</span>
                                                    <?php elseif($r->status === 'activa'): ?>
                                                        <span class="bg-warning text-dark badge">En ruta</span>
                                                    <?php else: ?>
                                                        <span class="bg-secondary badge">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="7" class="py-5 text-muted text-center">
                                                    <i class="d-block opacity-50 mb-2 mdi mdi-route mdi-36px"></i>
                                                    Sin rutas para esta fecha
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4 col-lg-4">
                    <div class="shadow-sm border-0 card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">
                                <i class="me-1 text-info mdi mdi-store"></i>Por sede
                            </h6>
                        </div>
                        <div class="p-3 card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $porSede; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-semibold small"><?php echo $sede; ?></span>
                                        <span class="text-primary fw-bold small"><?php echo $data['km']; ?> km</span>
                                    </div>
                                    <div class="text-muted" style="font-size:11px">
                                        <?php echo $data['rutas']; ?> ruta<?php echo $data['rutas'] !== 1 ? 's' : ''; ?> ·
                                        <?php echo $data['entregas']; ?> entrega<?php echo $data['entregas'] !== 1 ? 's' : ''; ?>

                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="py-3 text-muted text-center small">Sin datos</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.getElementById('btn-fecha').addEventListener('click', () => {
            const f = document.getElementById('sel-fecha').value;
            if (f) window.location.href = `/tracking/resumen?fecha=${f}`;
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/resumen-diario.blade.php ENDPATH**/ ?>