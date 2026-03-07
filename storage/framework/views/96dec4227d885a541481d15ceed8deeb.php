<?php $__env->startSection('title', 'Dashboard Marketing'); ?>

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
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                Dashboard Marketing
                                            </h3>
                                            <p class="text-muted mt-1">Análisis de satisfacción y encuestas</p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                                <i class="mdi mdi-filter-variant"></i> Filtros
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros Colapsables -->
                            <div class="collapse mb-4" id="filterCollapse">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            <i class="mdi mdi-filter-variant text-primary me-2"></i>
                                            Filtros de Búsqueda
                                        </h5>
                                        <form method="GET" action="<?php echo route('marketing.index'); ?>">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Fecha Inicio</label>
                                                    <input type="date" name="start_date" class="form-control"
                                                        value="<?php echo $startDate; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Fecha Fin</label>
                                                    <input type="date" name="end_date" class="form-control"
                                                        value="<?php echo $endDate; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Consultor / Sede / Trimax</label>
                                                    <select name="user_id" class="form-select">
                                                        <option value="">Todos</option>
                                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userMkt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo $userMkt->id; ?>"
                                                                <?php echo $userId == $userMkt->id ? 'selected' : ''; ?>>
                                                                <?php echo $userMkt->name; ?> -
                                                                <?php if($userMkt->role === 'consultor'): ?>
                                                                    Consultor
                                                                <?php elseif($userMkt->role === 'trimax'): ?>
                                                                    TRIMAX General
                                                                <?php else: ?>
                                                                    Sede <?php echo $userMkt->location; ?>

                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="submit" class="btn btn-primary w-100 text-white">
                                                        <i class="mdi mdi-magnify me-1"></i> Filtrar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-file-document text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1"><?php echo $stats['total']; ?></h3>
                                                <p class="statistics-title mb-0">Total Encuestas</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-success-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-excited text-success icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1"><?php echo $stats['muy_feliz']; ?></h3>
                                                <p class="statistics-title mb-0">Muy Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-info-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-happy text-info icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1"><?php echo $stats['feliz']; ?></h3>
                                                <p class="statistics-title mb-0">Feliz</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-warning-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-neutral text-warning icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1"><?php echo $stats['insatisfecho']; ?></h3>
                                                <p class="statistics-title mb-0">Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-danger-subtle rounded mb-3">
                                                    <i class="mdi mdi-emoticon-sad text-danger icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1"><?php echo $stats['muy_insatisfecho']; ?></h3>
                                                <p class="statistics-title mb-0">Muy Insatisfecho</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 grid-margin stretch-card">
                                    <div class="card card-statistics">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="icon-wrapper bg-primary-subtle rounded mb-3">
                                                    <i class="mdi mdi-star text-primary icon-lg"></i>
                                                </div>
                                                <h3 class="rate-percentage mb-1">
                                                    <?php echo number_format($stats['average_experience'], 2); ?></h3>
                                                <p class="statistics-title mb-0">Promedio General</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs de navegación -->
                            <ul class="nav nav-tabs nav-tabs-line mb-4" id="dashboardTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab"
                                        data-bs-target="#dashboard" type="button">
                                        <i class="mdi mdi-view-dashboard me-1"></i> General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="zona-tab" data-bs-toggle="tab" data-bs-target="#zona"
                                        type="button">
                                        <i class="mdi mdi-map-marker me-1"></i> Por Zona
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="reconocimientos-tab" data-bs-toggle="tab"
                                        data-bs-target="#reconocimientos" type="button">
                                        <i class="mdi mdi-trophy me-1"></i> Reconocimientos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tendencias-tab" data-bs-toggle="tab"
                                        data-bs-target="#tendencias" type="button">
                                        <i class="mdi mdi-chart-line me-1"></i> Tendencias
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="alertas-tab" data-bs-toggle="tab"
                                        data-bs-target="#alertas" type="button">
                                        <i class="mdi mdi-alert-circle me-1"></i> Alertas
                                    </button>
                                </li>
                            </ul>

                            <!-- Contenido de los tabs -->
                            <div class="tab-content" id="dashboardTabsContent">

                                <!-- TAB 1: Dashboard General -->
                                <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                                    <!-- Gráficos -->
                                    <div class="row">
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-donut text-primary me-2"></i>
                                                        Distribución de Experiencia
                                                    </h4>
                                                    <canvas id="ratingChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-bar text-info me-2"></i>
                                                        Calidad de Atención por Usuario
                                                    </h4>
                                                    <canvas id="serviceChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla por Consultor/Sede/Trimax -->
                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-account-group text-primary me-2"></i>
                                                        Estadísticas por Consultor / Sede / Trimax
                                                    </h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nombre</th>
                                                                    <th>Tipo</th>
                                                                    <th>Total</th>
                                                                    <th class="text-center">Muy Feliz</th>
                                                                    <th class="text-center">Feliz</th>
                                                                    <th class="text-center">Insatis.</th>
                                                                    <th class="text-center">Muy Insatis.</th>
                                                                    <th>Promedio</th>
                                                                    <th style="width: 200px;">Distribución</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $userStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <tr
                                                                        <?php if($stat['role'] === 'trimax'): ?> style="background-color: rgba(26,26,46,0.04);" <?php endif; ?>>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <img class="img-xs rounded-circle me-2"
                                                                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($stat['name']); ?>&background=<?php echo $stat['role'] === 'trimax' ? '1a1a2e' : '6366f1'; ?>&color=fff"
                                                                                    alt="profile">
                                                                                <div>
                                                                                    <strong><?php echo $stat['name']; ?></strong>
                                                                                    <?php if($stat['role'] === 'consultor' && $stat['sedes_count'] > 0): ?>
                                                                                        <br>
                                                                                        <small class="text-muted">
                                                                                            <i
                                                                                                class="mdi mdi-office-building"></i>
                                                                                            <?php echo $stat['sedes_count']; ?>

                                                                                            <?php echo $stat['sedes_count'] == 1 ? 'sede' : 'sedes'; ?>

                                                                                        </small>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($stat['role'] === 'consultor'): ?>
                                                                                <span
                                                                                    class="badge badge-primary">Consultor</span>
                                                                                <?php if($stat['sedes_count'] > 0): ?>
                                                                                    <span
                                                                                        class="badge badge-info">+Sedes</span>
                                                                                <?php endif; ?>
                                                                            <?php elseif($stat['role'] === 'trimax'): ?>
                                                                                <span class="badge"
                                                                                    style="background-color: #1a1a2e; color: #fff;">
                                                                                    <i
                                                                                        class="mdi mdi-domain me-1"></i>TRIMAX
                                                                                </span>
                                                                            <?php else: ?>
                                                                                <span class="badge badge-info">
                                                                                    Sede <?php echo $stat['location']; ?>

                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <strong
                                                                                class="text-primary"><?php echo $stat['total_surveys']; ?></strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-success"><?php echo $stat['muy_feliz']; ?></span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-info"><?php echo $stat['feliz']; ?></span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-warning"><?php echo $stat['insatisfecho']; ?></span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-danger"><?php echo $stat['muy_insatisfecho']; ?></span>
                                                                        </td>
                                                                        <td>
                                                                            <strong><?php echo number_format($stat['avg_experience'], 2); ?></strong>
                                                                            <i class="mdi mdi-star text-warning"></i>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($stat['total_surveys'] > 0): ?>
                                                                                <div class="progress"
                                                                                    style="height: 25px;">
                                                                                    <?php
                                                                                        $total = $stat['total_surveys'];
                                                                                        $muyFelizPct =
                                                                                            ($stat['muy_feliz'] /
                                                                                                $total) *
                                                                                            100;
                                                                                        $felizPct =
                                                                                            ($stat['feliz'] / $total) *
                                                                                            100;
                                                                                        $insatisfechoPct =
                                                                                            ($stat['insatisfecho'] /
                                                                                                $total) *
                                                                                            100;
                                                                                        $muyInsatisfechoPct =
                                                                                            ($stat['muy_insatisfecho'] /
                                                                                                $total) *
                                                                                            100;
                                                                                    ?>
                                                                                    <?php if($muyFelizPct > 0): ?>
                                                                                        <div class="progress-bar bg-success"
                                                                                            style="width: <?php echo $muyFelizPct; ?>%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="<?php echo round($muyFelizPct); ?>%">
                                                                                            <?php echo round($muyFelizPct) > 10 ? round($muyFelizPct) . '%' : ''; ?>

                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                    <?php if($felizPct > 0): ?>
                                                                                        <div class="progress-bar bg-info"
                                                                                            style="width: <?php echo $felizPct; ?>%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="<?php echo round($felizPct); ?>%">
                                                                                            <?php echo round($felizPct) > 10 ? round($felizPct) . '%' : ''; ?>

                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                    <?php if($insatisfechoPct > 0): ?>
                                                                                        <div class="progress-bar bg-warning"
                                                                                            style="width: <?php echo $insatisfechoPct; ?>%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="<?php echo round($insatisfechoPct); ?>%">
                                                                                            <?php echo round($insatisfechoPct) > 10 ? round($insatisfechoPct) . '%' : ''; ?>

                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                    <?php if($muyInsatisfechoPct > 0): ?>
                                                                                        <div class="progress-bar bg-danger"
                                                                                            style="width: <?php echo $muyInsatisfechoPct; ?>%"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title="<?php echo round($muyInsatisfechoPct); ?>%">
                                                                                            <?php echo round($muyInsatisfechoPct) > 10 ? round($muyInsatisfechoPct) . '%' : ''; ?>

                                                                                        </div>
                                                                                    <?php endif; ?>
                                                                                </div>
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

                                    <!-- Encuestas Recientes -->
                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-clock-outline text-info me-2"></i>
                                                        Encuestas Recientes
                                                    </h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Cliente</th>
                                                                    <th>Evaluado</th>
                                                                    <th>Experiencia</th>
                                                                    <th>Atención</th>
                                                                    <th>Comentarios</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $recentSurveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <tr>
                                                                        <td>
                                                                            <small
                                                                                class="text-muted"><?php echo $survey->created_at->format('d/m/Y'); ?></small><br>
                                                                            <small><?php echo $survey->created_at->format('H:i'); ?></small>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($survey->client_name): ?>
                                                                                <div class="d-flex align-items-center">
                                                                                    <img class="img-xs rounded-circle me-2"
                                                                                        src="https://ui-avatars.com/api/?name=<?php echo urlencode($survey->client_name); ?>&background=10b981&color=fff"
                                                                                        alt="client">
                                                                                    <strong><?php echo $survey->client_name; ?></strong>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">
                                                                                    <i class="mdi mdi-account-off"></i>
                                                                                    Anónimo
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <strong><?php echo $survey->userMarketing->name; ?></strong><br>
                                                                            <small class="text-muted">
                                                                                <?php if($survey->userMarketing->role === 'consultor'): ?>
                                                                                    Consultor
                                                                                <?php elseif($survey->userMarketing->role === 'trimax'): ?>
                                                                                    TRIMAX General
                                                                                <?php else: ?>
                                                                                    Sede
                                                                                    <?php echo $survey->userMarketing->location; ?>

                                                                                <?php endif; ?>
                                                                            </small>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($survey->experience_rating == 4): ?>
                                                                                <span class="badge badge-success">
                                                                                    <i
                                                                                        class="mdi mdi-emoticon-excited"></i>
                                                                                    Muy Feliz
                                                                                </span>
                                                                            <?php elseif($survey->experience_rating == 3): ?>
                                                                                <span class="badge badge-info">
                                                                                    <i class="mdi mdi-emoticon-happy"></i>
                                                                                    Feliz
                                                                                </span>
                                                                            <?php elseif($survey->experience_rating == 2): ?>
                                                                                <span class="badge badge-warning">
                                                                                    <i
                                                                                        class="mdi mdi-emoticon-neutral"></i>
                                                                                    Insatisfecho
                                                                                </span>
                                                                            <?php else: ?>
                                                                                <span class="badge badge-danger">
                                                                                    <i class="mdi mdi-emoticon-sad"></i>
                                                                                    Muy Insatisfecho
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($survey->service_quality_rating == 4): ?>
                                                                                <span class="badge badge-success">Muy
                                                                                    Feliz</span>
                                                                            <?php elseif($survey->service_quality_rating == 3): ?>
                                                                                <span class="badge badge-info">Feliz</span>
                                                                            <?php elseif($survey->service_quality_rating == 2): ?>
                                                                                <span
                                                                                    class="badge badge-warning">Insatisfecho</span>
                                                                            <?php else: ?>
                                                                                <span class="badge badge-danger">Muy
                                                                                    Insatisfecho</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php if($survey->comments): ?>
                                                                                <small><?php echo Str::limit($survey->comments, 60); ?></small>
                                                                            <?php else: ?>
                                                                                <small class="text-muted">Sin
                                                                                    comentarios</small>
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

                                <!-- TAB 2: Detalles por Zona -->
                                <div class="tab-pane fade" id="zona" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-map-marker text-primary me-2"></i>
                                                Análisis por Ubicación
                                            </h4>
                                        </div>
                                    </div>

                                    <?php
                                        $sedeStats = $userStats->where('role', 'sede');
                                        $zonas = $sedeStats->groupBy('location');
                                    ?>

                                    <div class="row">
                                        <?php $__currentLoopData = $zonas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location => $sedes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $totalZona = $sedes->sum('total_surveys');
                                                $avgZona = $sedes->avg('avg_experience');
                                                $muyFelizZona = $sedes->sum('muy_feliz');
                                                $felizZona = $sedes->sum('feliz');
                                                $insatisfechoZona = $sedes->sum('insatisfecho');
                                                $muyInsatisfechoZona = $sedes->sum('muy_insatisfecho');
                                            ?>
                                            <div class="col-xl-4 col-lg-6 col-md-6 grid-margin stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <h4 class="card-title mb-0">
                                                                <i class="mdi mdi-map-marker text-primary me-1"></i>
                                                                <?php echo $location; ?>

                                                            </h4>
                                                            <span class="badge badge-primary">
                                                                <?php echo $sedes->count(); ?>

                                                                <?php echo $sedes->count() == 1 ? 'Sede' : 'Sedes'; ?>

                                                            </span>
                                                        </div>

                                                        <div class="row text-center mb-3">
                                                            <div class="col-6">
                                                                <div class="icon-wrapper bg-primary-subtle rounded mb-2">
                                                                    <i class="mdi mdi-chart-box text-primary icon-lg"></i>
                                                                </div>
                                                                <h3 class="rate-percentage mb-0"><?php echo $totalZona; ?></h3>
                                                                <p class="statistics-title mb-0">Encuestas</p>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="icon-wrapper bg-warning-subtle rounded mb-2">
                                                                    <i class="mdi mdi-star text-warning icon-lg"></i>
                                                                </div>
                                                                <h3 class="rate-percentage mb-0">
                                                                    <?php echo number_format($avgZona, 2); ?></h3>
                                                                <p class="statistics-title mb-0">Promedio</p>
                                                            </div>
                                                        </div>

                                                        <?php if($totalZona > 0): ?>
                                                            <div class="border-top pt-3">
                                                                <small class="text-muted d-block mb-2">Distribución de
                                                                    satisfacción</small>
                                                                <div class="progress" style="height: 25px;">
                                                                    <?php
                                                                        $mfPct = ($muyFelizZona / $totalZona) * 100;
                                                                        $fPct = ($felizZona / $totalZona) * 100;
                                                                        $iPct = ($insatisfechoZona / $totalZona) * 100;
                                                                        $miPct =
                                                                            ($muyInsatisfechoZona / $totalZona) * 100;
                                                                    ?>
                                                                    <?php if($mfPct > 0): ?>
                                                                        <div class="progress-bar bg-success"
                                                                            style="width: <?php echo $mfPct; ?>%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Muy Feliz: <?php echo round($mfPct); ?>%">
                                                                            <?php echo round($mfPct) > 10 ? round($mfPct) . '%' : ''; ?>

                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php if($fPct > 0): ?>
                                                                        <div class="progress-bar bg-info"
                                                                            style="width: <?php echo $fPct; ?>%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Feliz: <?php echo round($fPct); ?>%">
                                                                            <?php echo round($fPct) > 10 ? round($fPct) . '%' : ''; ?>

                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php if($iPct > 0): ?>
                                                                        <div class="progress-bar bg-warning"
                                                                            style="width: <?php echo $iPct; ?>%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Insatisfecho: <?php echo round($iPct); ?>%">
                                                                            <?php echo round($iPct) > 10 ? round($iPct) . '%' : ''; ?>

                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php if($miPct > 0): ?>
                                                                        <div class="progress-bar bg-danger"
                                                                            style="width: <?php echo $miPct; ?>%"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Muy Insatisfecho: <?php echo round($miPct); ?>%">
                                                                            <?php echo round($miPct) > 10 ? round($miPct) . '%' : ''; ?>

                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-bar text-info me-2"></i>
                                                        Comparativa de Zonas
                                                    </h4>
                                                    <canvas id="zonaChart" height="80"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: Reconocimientos -->
                                <div class="tab-pane fade" id="reconocimientos" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-trophy text-warning me-2"></i>
                                                Reconocimientos y Destacados
                                            </h4>
                                        </div>
                                    </div>

                                    <?php
                                        // Excluir trimax de reconocimientos individuales (es empresa, no persona)
                                        $userStatsPersonal = $userStats->whereNotIn('role', ['trimax']);
                                        $topRated = $userStatsPersonal->sortByDesc('avg_experience')->take(3);
                                        $mostSurveys = $userStatsPersonal->sortByDesc('total_surveys')->take(3);
                                    ?>

                                    <div class="row">
                                        <!-- Mejor Calificados -->
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-trophy text-warning me-2"></i>
                                                        Mejor Calificados
                                                    </h4>
                                                    <?php $__currentLoopData = $topRated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="d-flex align-items-center border-bottom py-3">
                                                            <div class="me-3">
                                                                <?php if($index == 0): ?>
                                                                    <div
                                                                        class="icon-wrapper bg-warning-subtle rounded-circle">
                                                                        <span style="font-size: 2rem;">🥇</span>
                                                                    </div>
                                                                <?php elseif($index == 1): ?>
                                                                    <div class="icon-wrapper bg-secondary rounded-circle">
                                                                        <span style="font-size: 2rem;">🥈</span>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div
                                                                        class="icon-wrapper bg-danger-subtle rounded-circle">
                                                                        <span style="font-size: 2rem;">🥉</span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h5 class="mb-1"><?php echo $user['name']; ?></h5>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="badge badge-success me-2">
                                                                        <i class="mdi mdi-star"></i>
                                                                        <?php echo number_format($user['avg_experience'], 2); ?>

                                                                    </span>
                                                                    <small class="text-muted">
                                                                        <?php echo $user['total_surveys']; ?> encuestas |
                                                                        <?php echo $user['muy_feliz']; ?> Muy Feliz
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Más Evaluados -->
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-4">
                                                        <i class="mdi mdi-chart-line text-primary me-2"></i>
                                                        Más Evaluados
                                                    </h4>
                                                    <?php $__currentLoopData = $mostSurveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="card mb-3"
                                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                            <div class="card-body text-white">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h5 class="text-white mb-1"><?php echo $user['name']; ?>

                                                                        </h5>
                                                                        <p class="mb-0 opacity-75">
                                                                            <i class="mdi mdi-star"></i>
                                                                            <?php echo number_format($user['avg_experience'], 2); ?>

                                                                            Promedio
                                                                        </p>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <h2 class="text-white mb-0">
                                                                            <?php echo $user['total_surveys']; ?></h2>
                                                                        <small class="opacity-75">Encuestas</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: Tendencias -->
                                <div class="tab-pane fade" id="tendencias" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-chart-line text-success me-2"></i>
                                                Tendencias Temporales
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-chart-timeline text-info me-2"></i>
                                                        Tendencia General
                                                    </h4>
                                                    <canvas id="trendChart" height="80"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-calendar-week text-primary me-2"></i>
                                                        Evolución Semanal
                                                    </h4>
                                                    <canvas id="weeklyTrendChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="card-title mb-3">
                                                        <i class="mdi mdi-calendar-month text-success me-2"></i>
                                                        Comparativa Mensual
                                                    </h4>
                                                    <canvas id="monthlyComparisonChart" height="250"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 5: Alertas -->
                                <div class="tab-pane fade" id="alertas" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 mb-3">
                                            <h4 class="card-title">
                                                <i class="mdi mdi-alert-circle text-danger me-2"></i>
                                                Alertas y Atención Requerida
                                            </h4>
                                        </div>
                                    </div>

                                    <?php
                                        $lowRated = $userStats
                                            ->where('avg_experience', '<', 2.5)
                                            ->sortBy('avg_experience');
                                        $recentBad = $recentSurveys->where('experience_rating', '<=', 2)->take(10);
                                    ?>

                                    <div class="row">
                                        <div class="col-lg-12 grid-margin stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <?php if($lowRated->count() > 0): ?>
                                                        <div class="alert alert-danger border-0" role="alert">
                                                            <h5 class="mb-3">
                                                                <i class="mdi mdi-alert-circle me-2"></i>
                                                                Usuarios con Calificación Baja
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Usuario</th>
                                                                            <th>Tipo</th>
                                                                            <th>Promedio</th>
                                                                            <th>Calificaciones Negativas</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php $__currentLoopData = $lowRated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <tr>
                                                                                <td><strong><?php echo $user['name']; ?></strong>
                                                                                </td>
                                                                                <td>
                                                                                    <?php if($user['role'] === 'consultor'): ?>
                                                                                        <span
                                                                                            class="badge badge-primary">Consultor</span>
                                                                                    <?php elseif($user['role'] === 'trimax'): ?>
                                                                                        <span class="badge"
                                                                                            style="background-color: #1a1a2e; color: #fff;">TRIMAX</span>
                                                                                    <?php else: ?>
                                                                                        <span
                                                                                            class="badge badge-info">Sede</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td>
                                                                                    <span class="badge badge-danger">
                                                                                        <?php echo number_format($user['avg_experience'], 2); ?>

                                                                                    </span>
                                                                                </td>
                                                                                <td><?php echo $user['muy_insatisfecho'] + $user['insatisfecho']; ?>

                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-success border-0" role="alert">
                                                            <h5 class="mb-2">
                                                                <i class="mdi mdi-check-circle me-2"></i>
                                                                Todo en Orden
                                                            </h5>
                                                            <p class="mb-0">No hay usuarios con calificaciones críticas
                                                                en este periodo.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if($recentBad->count() > 0): ?>
                                        <div class="row">
                                            <div class="col-lg-12 grid-margin stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h4 class="card-title mb-4">
                                                            <i class="mdi mdi-alert text-warning me-2"></i>
                                                            Encuestas Negativas Recientes
                                                        </h4>
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Cliente</th>
                                                                        <th>Evaluado</th>
                                                                        <th>Calificación</th>
                                                                        <th>Comentario</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php $__currentLoopData = $recentBad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <tr>
                                                                            <td>
                                                                                <small><?php echo $survey->created_at->format('d/m H:i'); ?></small>
                                                                            </td>
                                                                            <td><?php echo $survey->client_name ?? 'Anónimo'; ?>

                                                                            </td>
                                                                            <td>
                                                                                <?php echo $survey->userMarketing->name; ?>

                                                                                <br>
                                                                                <small class="text-muted">
                                                                                    <?php if($survey->userMarketing->role === 'trimax'): ?>
                                                                                        TRIMAX General
                                                                                    <?php elseif($survey->userMarketing->role === 'consultor'): ?>
                                                                                        Consultor
                                                                                    <?php else: ?>
                                                                                        Sede
                                                                                    <?php endif; ?>
                                                                                </small>
                                                                            </td>
                                                                            <td>
                                                                                <?php if($survey->experience_rating == 2): ?>
                                                                                    <span class="badge badge-warning">
                                                                                        <i
                                                                                            class="mdi mdi-emoticon-neutral"></i>
                                                                                        Insatisfecho
                                                                                    </span>
                                                                                <?php else: ?>
                                                                                    <span class="badge badge-danger">
                                                                                        <i
                                                                                            class="mdi mdi-emoticon-sad"></i>
                                                                                        Muy Insatisfecho
                                                                                    </span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                            <td>
                                                                                <small><?php echo Str::limit($survey->comments ?? 'Sin comentarios', 50); ?></small>
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
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-statistics .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-lg {
            font-size: 2rem;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .nav-tabs-line .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs-line .nav-link.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
            background: transparent;
        }

        .nav-tabs-line .nav-link:hover {
            border-bottom-color: #6366f1;
            color: #6366f1;
        }

        .progress {
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }

        [data-bs-toggle="tooltip"] {
            cursor: help;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });

        // Gráfico de distribución de calificaciones
        const ratingCtx = document.getElementById('ratingChart');
        if (ratingCtx) {
            new Chart(ratingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Muy Feliz', 'Feliz', 'Insatisfecho', 'Muy Insatisfecho'],
                    datasets: [{
                        data: [<?php echo $stats['muy_feliz']; ?>, <?php echo $stats['feliz']; ?>,
                            <?php echo $stats['insatisfecho']; ?>, <?php echo $stats['muy_insatisfecho']; ?>

                        ],
                        backgroundColor: ['#4CAF50', '#0dcaf0', '#ffc107', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de calidad de servicio
        const serviceCtx = document.getElementById('serviceChart');
        if (serviceCtx) {
            <?php
                $serviceColors = [];
                foreach ($userStats as $sItem) {
                    $serviceColors[] = $sItem['role'] === 'trimax' ? '#1a1a2e' : '#0dcaf0';
                }
            ?>
            new Chart(serviceCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($userStats->pluck('name'), 15, 512) ?>,
                    datasets: [{
                        label: 'Promedio de Atención',
                        data: <?php echo json_encode($userStats->pluck('avg_service'), 15, 512) ?>,
                        backgroundColor: <?php echo json_encode($serviceColors, 15, 512) ?>,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        // Gráfico por zona
        const zonaCtx = document.getElementById('zonaChart');
        if (zonaCtx) {
            <?php
                $zonaLabels = $zonas->keys();
                $zonaData = $zonas->map(function ($sedes) {
                    return $sedes->avg('avg_experience');
                });
            ?>

            new Chart(zonaCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($zonaLabels, 15, 512) ?>,
                    datasets: [{
                        label: 'Promedio por Zona',
                        data: <?php echo json_encode($zonaData->values(), 15, 512) ?>,
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/marketing/dashboard/index.blade.php ENDPATH**/ ?>