<?php $__env->startSection('title', 'Seguridad y Analisis'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    </div>
                    <div class="tab-content tab-content-basic">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="statistics-details d-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="rate-percentage">Seguridad & Analisis</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">IPs Bloqueadas (<?php echo $blockedIps->total(); ?>)</h4>
                        <?php if($blockedIps->isEmpty()): ?>
                            <p class="card-description">
                                No hay IPs bloqueadas actualmente.
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>IP</th>
                                            <th>Razón</th>
                                            <th>Bloqueado Hasta</th>
                                            <th>Fecha de Bloqueo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $blockedIps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo $ip->ip_address; ?></td>
                                                <td><?php echo $ip->reason; ?></td>
                                                <td>
                                                    <?php if($ip->blocked_until): ?>
                                                        <?php echo $ip->blocked_until->format('d/m/Y H:i'); ?>

                                                    <?php else: ?>
                                                        <label class="badge badge-danger">Permanente</label>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $ip->created_at->format('d/m/Y H:i'); ?></td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- INTENTOS FALLIDOS -->
            <div class="col-lg-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Intentos de Login Fallidos Recientes</h4>
                        <?php if($recentFailedAttempts->isEmpty()): ?>
                            <p class="card-description">
                                No hay intentos fallidos recientes.
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Email</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $recentFailedAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo $attempt->attempted_at->format('d/m/Y H:i:s'); ?></td>
                                                <td><?php echo $attempt->email; ?></td>
                                                <td class="text-danger"><?php echo $attempt->ip_address; ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ANALITICS -->
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Top 10 Usuarios por Tiempo de Uso</h4>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            Usuario
                                        </th>
                                        <th>
                                            Tiempo Total
                                        </th>
                                        <th>
                                            Sesiones
                                        </th>
                                        <th>
                                            Promedio por Sesión
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $usageStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="py-1">
                                                <?php echo $stat['name']; ?>

                                            </td>
                                            <td>
                                                <?php echo floor($stat['total_time'] / 3600); ?>h
                                                <?php echo floor(($stat['total_time'] % 3600) / 60); ?>m
                                            </td>
                                            <td>
                                                <?php echo $stat['sessions_count']; ?>

                                            </td>
                                            <td>
                                                <?php if($stat['sessions_count'] > 0): ?>
                                                    <?php echo floor($stat['total_time'] / $stat['sessions_count'] / 60); ?>m
                                                <?php else: ?>
                                                    0m
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/security.blade.php ENDPATH**/ ?>