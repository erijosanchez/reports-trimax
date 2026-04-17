<?php $__env->startSection('title', 'Dashboards'); ?>

<?php $__env->startSection('content'); ?>
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
                                        <?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()): ?>
                                            <a href="<?php echo route('dashboards.create'); ?>" class="btn btn-success text-white">
                                                <i class="mdi mdi-plus-circle me-1"></i>Crear Dashboard
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if($dashboards->isEmpty()): ?>
                                <!-- Empty State -->
                                <div class="row">
                                    <div class="col-lg-12 grid-margin stretch-card">
                                        <div class="card">
                                            <div class="card-body text-center py-5">
                                                <i class="mdi mdi-chart-box-outline mdi-48px text-muted mb-3 d-block"></i>
                                                <h4 class="mb-3">No hay dashboards disponibles</h4>
                                                <p class="text-muted mb-4">Aún no se han creado dashboards en el sistema.
                                                </p>
                                                <?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()): ?>
                                                    <a href="<?php echo route('dashboards.create'); ?>" class="btn btn-primary">
                                                        <i class="mdi mdi-plus-circle me-1 text-white"></i>Crear el primer dashboard
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Dashboards Grid -->
                                <div class="row">
                                    <?php $__currentLoopData = $dashboards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dashboard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <!-- Header con título y badge -->
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h4 class="card-title mb-0"><?php echo $dashboard->name; ?></h4>
                                                        <?php if(!$dashboard->is_active): ?>
                                                            <span class="badge badge-danger">Inactivo</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">Activo</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Descripción -->
                                                    <p class="card-description text-muted" style="min-height: 3rem;">
                                                        <?php echo $dashboard->description ?? 'Sin descripción'; ?>

                                                    </p>

                                                    <!-- Botones de acción -->
                                                    <div class="d-flex gap-2 mb-3">
                                                        <a href="<?php echo route('dashboards.show', $dashboard->id); ?>"
                                                            class="btn btn-primary btn-sm flex-grow-1 text-white p-3">
                                                            <i class="mdi mdi-chart-areaspline me-1"></i> Ver Dashboard
                                                        </a>

                                                        <?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()): ?>
                                                            <a href="<?php echo route('dashboards.edit', $dashboard->id); ?>"
                                                                class="btn btn-warning btn-sm p-3">
                                                                <i class="mdi mdi-pencil" style="font-size: 1.2rem"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Info adicional para admin -->
                                                    <?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()): ?>
                                                        <div class="border-top pt-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-account-multiple text-primary me-2"></i>
                                                                <small class="text-muted">
                                                                    <strong><?php echo $dashboard->users->count(); ?></strong>
                                                                    <?php echo $dashboard->users->count() == 1 ? 'usuario asignado' : 'usuarios asignados'; ?>

                                                                </small>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/dashboards/index.blade.php ENDPATH**/ ?>