<?php $__env->startSection('title', 'Historial de Ubicaciones GPS'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom"></div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            
                            <!-- TÍTULO -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage"><i class="mdi mdi-history"></i> Historial de Ubicaciones</h3>
                                            <p style="color:#666;">Registro completo de ubicaciones precisas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FILTROS -->
                            <div class="row mt-1">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4"><i class="mdi mdi-filter me-1"></i> Filtros de Búsqueda</h4>
                                            <form method="GET" action="<?php echo route('admin.locations.index'); ?>">
                                                <div class="row">
                                                    <!-- Usuario -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Usuario</label>
                                                        <select name="user_id" class="form-select">
                                                            <option value="">Todos los usuarios</option>
                                                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo $user->id; ?>"
                                                                    <?php echo request('user_id') == $user->id ? 'selected' : ''; ?>>
                                                                    <?php echo $user->name; ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>

                                                    <!-- Ciudad -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Ciudad</label>
                                                        <input type="text" 
                                                               name="city" 
                                                               value="<?php echo request('city'); ?>"
                                                               placeholder="Ej: Lima, Arequipa" 
                                                               class="form-control">
                                                    </div>

                                                    <!-- Fecha Desde -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Desde</label>
                                                        <input type="date" 
                                                               name="date_from"
                                                               value="<?php echo request('date_from'); ?>" 
                                                               class="form-control">
                                                    </div>

                                                    <!-- Fecha Hasta -->
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Fecha Hasta</label>
                                                        <input type="date" 
                                                               name="date_to"
                                                               value="<?php echo request('date_to'); ?>" 
                                                               class="form-control">
                                                    </div>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="mdi mdi-filter me-1"></i>Filtrar
                                                    </button>
                                                    <a href="<?php echo route('admin.locations.index'); ?>" class="btn btn-light">
                                                        <i class="mdi mdi-refresh me-1"></i>Limpiar
                                                    </a>
                                                    <a href="<?php echo route('admin.locations.map'); ?>" class="btn btn-success">
                                                        <i class="mdi mdi-map me-1"></i>Ver Mapa
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA DE UBICACIONES -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h4 class="card-title mb-0">Registros GPS</h4>
                                                    <p class="card-description">
                                                        Total: <code><?php echo $locations->total(); ?></code> ubicaciones
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>📅 Fecha/Hora</th>
                                                            <th>👤 Usuario</th>
                                                            <th>📍 Ubicación GPS</th>
                                                            <th>🎯 Precisión</th>
                                                            <th>📐 Coordenadas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr>
                                                                <!-- Fecha/Hora -->
                                                                <td>
                                                                    <div>
                                                                        <strong><?php echo $location->created_at->format('d/m/Y'); ?></strong>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        <?php echo $location->created_at->format('H:i:s'); ?>

                                                                    </small>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?php echo $location->created_at->diffForHumans(); ?>

                                                                    </small>
                                                                </td>

                                                                <!-- Usuario -->
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="img-xs rounded-circle me-2"
                                                                            src="https://ui-avatars.com/api/?name=<?php echo urlencode($location->user->name); ?>&background=6366f1&color=fff"
                                                                            alt="<?php echo $location->user->name; ?>">
                                                                        <div>
                                                                            <div><?php echo $location->user->name; ?></div>
                                                                            <small class="text-muted"><?php echo $location->user->email; ?></small>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Ubicación GPS -->
                                                                <td>
                                                                    <div class="d-flex align-items-start">
                                                                        <i class="mdi mdi-cellphone-marker text-success me-2" style="font-size:1.3rem;"></i>
                                                                        <div>
                                                                            <?php if($location->formatted_address): ?>
                                                                                <div style="font-size:0.95rem;">
                                                                                    <?php echo $location->formatted_address; ?>

                                                                                </div>
                                                                            <?php elseif($location->city): ?>
                                                                                <div>
                                                                                    <?php if($location->street_name): ?>
                                                                                        <?php echo $location->street_name; ?>

                                                                                        <?php if($location->street_number): ?>
                                                                                            #<?php echo $location->street_number; ?>

                                                                                        <?php endif; ?>
                                                                                        <br>
                                                                                    <?php endif; ?>
                                                                                    <?php if($location->district): ?>
                                                                                        <?php echo $location->district; ?>,
                                                                                    <?php endif; ?>
                                                                                    <?php echo $location->city; ?>

                                                                                </div>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">Ubicación no disponible</span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Precisión -->
                                                                <td>
                                                                    <?php if($location->accuracy): ?>
                                                                        <span class="badge" style="background:
                                                                            <?php if($location->accuracy < 50): ?> #28a745
                                                                            <?php elseif($location->accuracy < 100): ?> #5cb85c
                                                                            <?php elseif($location->accuracy < 500): ?> #ffc107
                                                                            <?php else: ?> #dc3545
                                                                            <?php endif; ?>
                                                                        ;color:white;padding:0.4rem 0.8rem;">
                                                                            <?php echo number_format($location->accuracy, 0); ?>m
                                                                        </span>
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            <?php if($location->accuracy < 50): ?>
                                                                                ⭐ Excelente
                                                                            <?php elseif($location->accuracy < 100): ?>
                                                                                ✓ Buena
                                                                            <?php elseif($location->accuracy < 500): ?>
                                                                                △ Regular
                                                                            <?php else: ?>
                                                                                ○ Baja
                                                                            <?php endif; ?>
                                                                        </small>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">N/A</span>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <!-- Coordenadas -->
                                                                <td>
                                                                    <?php if($location->latitude && $location->longitude): ?>
                                                                        <code style="font-size:0.85rem;">
                                                                            <?php echo number_format($location->latitude, 6); ?>,<br>
                                                                            <?php echo number_format($location->longitude, 6); ?>

                                                                        </code>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">N/A</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-5">
                                                                    <div class="text-muted">
                                                                        <i class="mdi mdi-map-marker-off" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                                                                        <p style="font-size:1.1rem;">No se encontraron ubicaciones GPS</p>
                                                                        <small>Intenta ajustar los filtros de búsqueda</small>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            <?php if($locations->hasPages()): ?>
                                                <div class="mt-4">
                                                    <?php echo $locations->appends(request()->query())->links(); ?>

                                                </div>
                                            <?php endif; ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/locations/index.blade.php ENDPATH**/ ?>