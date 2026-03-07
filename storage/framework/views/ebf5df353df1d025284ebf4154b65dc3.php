<?php $__env->startSection('title', 'Historial GPS de ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                
                <!-- Navegación -->
                <div style="margin-bottom:2rem;">
                    <a href="<?php echo route('admin.locations.map'); ?>" class="btn btn-secondary">
                        ← Volver al Mapa
                    </a>
                </div>

                <!-- Header -->
                <div class="card">
                    <div class="card-body">
                        <h2><i class="mdi mdi-history"></i> Historial GPS: <?php echo $user->name; ?></h2>
                        <p style="color:#666;"><?php echo $user->email; ?></p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h3><?php echo $stats['total_locations']; ?></h3>
                                <p class="mb-0"><i class="mdi mdi-map-marker"></i> Ubicaciones GPS</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h3><?php echo $stats['cities_visited']; ?></h3>
                                <p class="mb-0"><i class="mdi mdi-city"></i> Ciudades Visitadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h3><?php echo $stats['countries_visited']; ?></h3>
                                <p class="mb-0"><i class="mdi mdi-earth"></i> Países Visitados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ciudades Únicas -->
                <?php if($uniqueCities->count() > 0): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4><i class="mdi mdi-city"></i> Ciudades Visitadas:</h4>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:1rem;">
                            <?php $__currentLoopData = $uniqueCities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-primary" style="padding:0.5rem 1rem;font-size:0.9rem;">
                                    <i class="mdi mdi-map-marker"></i> <?php echo $city; ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabla de Historial -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h4><i class="mdi mdi-history"></i> Historial Completo</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>📅 Fecha/Hora</th>
                                        <th>📍 Ubicación GPS</th>
                                        <th>🎯 Precisión</th>
                                        <th>📐 Coordenadas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td>
                                                <?php echo $location->created_at->format('d/m/Y H:i:s'); ?><br>
                                                <small class="text-muted"><?php echo $location->created_at->diffForHumans(); ?></small>
                                            </td>
                                            <td>
                                                <?php if($location->formatted_address): ?>
                                                    <?php echo $location->formatted_address; ?>

                                                <?php elseif($location->city): ?>
                                                    <?php echo $location->city; ?>

                                                    <?php if($location->region): ?>, <?php echo $location->region; ?><?php endif; ?>
                                                    <?php if($location->country): ?>, <?php echo $location->country; ?><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Ubicación no disponible</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($location->accuracy): ?>
                                                    <span class="badge bg-
                                                        <?php if($location->accuracy < 50): ?> success
                                                        <?php elseif($location->accuracy < 100): ?> primary
                                                        <?php elseif($location->accuracy < 500): ?> warning
                                                        <?php else: ?> danger
                                                        <?php endif; ?>
                                                    ">
                                                        <?php echo number_format($location->accuracy, 0); ?>m
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($location->latitude && $location->longitude): ?>
                                                    <small class="text-muted">
                                                        <?php echo number_format($location->latitude, 4); ?>, 
                                                        <?php echo number_format($location->longitude, 4); ?>

                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No hay ubicaciones GPS registradas
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-3">
                            <?php echo $locations->links(); ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/locations/user-history.blade.php ENDPATH**/ ?>