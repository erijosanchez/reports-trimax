
<?php $__env->startSection('title', 'Historial de Km'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-success mdi mdi-map-marker-distance"></i>Historial de Km
                                </h4>
                                <p class="mb-0 text-muted small">Control de kilometraje para justificar gastos de gasolina
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="py-3 card-body">
                            <form method="GET" action="<?php echo route('tracking.historial'); ?>" class="align-items-end row g-2">
                                <div class="col-md-3">
                                    <label class="mb-1 form-label small fw-bold">Motorizado</label>
                                    <select name="motorizado_id" class="form-select-sm form-select">
                                        <option value="">Todos</option>
                                        <?php $__currentLoopData = $motorizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo $m->id; ?>"
                                                <?php echo $motorizadoId == $m->id ? 'selected' : ''; ?>>
                                                <?php echo $m->nombre; ?> — <?php echo $m->sede; ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Desde</label>
                                    <input type="date" name="desde" class="form-control form-control-sm"
                                        value="<?php echo $desde; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="mb-1 form-label small fw-bold">Hasta</label>
                                    <input type="date" name="hasta" class="form-control form-control-sm"
                                        value="<?php echo $hasta; ?>">
                                </div>
                                <div class="col-md-2">
                                    <button class="w-100 btn btn-sm btn-primary">
                                        <i class="me-1 mdi mdi-magnify"></i>Filtrar
                                    </button>
                                </div>
                                <?php if($motorizadoId || $desde || $hasta): ?>
                                    <div class="col-md-1">
                                        <a href="<?php echo route('tracking.historial'); ?>"
                                            class="btn-outline-danger w-100 btn btn-sm">
                                            <i class="mdi mdi-close"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="p-0 card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Motorizado</th>
                                            <th>Sede</th>
                                            <th>Fecha</th>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Duración</th>
                                            <th>Km</th>
                                            <th>Entregas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $rutas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $comp = $r->entregas->where('estado', 'completado')->count();
                                                $tot = $r->entregas->count();
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo $r->motorizado->nombre; ?></td>
                                                <td><span class="bg-secondary badge"><?php echo $r->motorizado->sede; ?></span></td>
                                                <td><?php echo $r->fecha->format('d/m/Y'); ?></td>
                                                <td class="small">
                                                    <?php echo $r->started_at?->setTimezone('America/Lima')->format('H:i') ?? '—'; ?>

                                                </td>
                                                <td class="small">
                                                    <?php echo $r->ended_at?->setTimezone('America/Lima')->format('H:i') ?? '—'; ?>

                                                </td>
                                                <td class="small"><?php echo $r->duracion; ?></td>
                                                <td>
                                                    <span class="text-primary fw-bold fs-6">
                                                        <?php echo number_format($r->distance_km, 2); ?> km
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-success fw-semibold"><?php echo $comp; ?></span>
                                                    <span class="text-muted">/<?php echo $tot; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="8" class="py-5 text-muted text-center">
                                                    <i
                                                        class="d-block opacity-50 mb-2 mdi mdi-map-marker-distance mdi-36px"></i>
                                                    Sin registros para este período
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if($rutas->hasPages()): ?>
                                <div class="px-3 py-2 border-top">
                                    <?php echo $rutas->links(); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/historial-km.blade.php ENDPATH**/ ?>