<?php $__env->startSection('title', 'Dashboard Marketing'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h3 class="mb-0 rate-percentage">
                                        <i class="me-2 text-primary mdi mdi-chart-line"></i>Dashboard Marketing
                                    </h3>
                                    <p class="mt-1 mb-0 text-muted">Análisis de satisfacción y encuestas</p>
                                </div>
                                <button class="btn-outline-primary btn btn-sm" data-bs-toggle="collapse"
                                    data-bs-target="#filterCollapse">
                                    <i class="mdi-filter-variant mdi"></i> Filtros
                                </button>
                            </div>

                            
                            <div class="collapse mb-4" id="filterCollapse">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-3 card-title"><i
                                                class="mdi-filter-variant me-2 text-primary mdi"></i>Filtros</h5>
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
                                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo $u->id; ?>"
                                                                <?php echo $userId == $u->id ? 'selected' : ''; ?>>
                                                                <?php echo $u->name; ?> —
                                                                <?php if($u->role === 'consultor'): ?>
                                                                    Consultor
                                                                <?php elseif($u->role === 'trimax'): ?>
                                                                    TRIMAX
                                                                <?php else: ?>
                                                                    Sede <?php echo $u->location; ?>

                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="d-flex align-items-end col-md-2">
                                                    <button type="submit" class="w-100 text-white btn btn-primary">
                                                        <i class="me-1 mdi mdi-magnify"></i> Filtrar
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-2 row">
                                <?php
                                    $statCards = [
                                        [
                                            'label' => 'Total Encuestas',
                                            'value' => $stats['total'],
                                            'icon' => 'mdi-file-document',
                                            'color' => 'primary',
                                        ],
                                        [
                                            'label' => 'Muy Feliz',
                                            'value' => $stats['muy_feliz'],
                                            'icon' => 'mdi-emoticon-excited',
                                            'color' => 'success',
                                        ],
                                        [
                                            'label' => 'Feliz',
                                            'value' => $stats['feliz'],
                                            'icon' => 'mdi-emoticon-happy',
                                            'color' => 'info',
                                        ],
                                        [
                                            'label' => 'Insatisfecho',
                                            'value' => $stats['insatisfecho'],
                                            'icon' => 'mdi-emoticon-neutral',
                                            'color' => 'warning',
                                        ],
                                        [
                                            'label' => 'Muy Insatisfecho',
                                            'value' => $stats['muy_insatisfecho'],
                                            'icon' => 'mdi-emoticon-sad',
                                            'color' => 'danger',
                                        ],
                                    ];
                                ?>

                                <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="grid-margin col-xl col-lg-4 col-md-4 col-sm-6 stretch-card">
                                        <div class="card card-statistics">
                                            <div class="card-body">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="icon-wrapper bg-<?php echo $card['color']; ?>-subtle rounded mb-3">
                                                        <i
                                                            class="mdi <?php echo $card['icon']; ?> text-<?php echo $card['color']; ?> icon-lg"></i>
                                                    </div>
                                                    <h3 class="mb-1 rate-percentage"><?php echo $card['value']; ?></h3>
                                                    <p class="mb-0 statistics-title"><?php echo $card['label']; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                
                                <div class="grid-margin col-xl col-lg-4 col-md-4 col-sm-6 stretch-card">
                                    <div class="card card-statistics" style="border-top: 3px solid #6366f1;">
                                        <div class="card-body">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-primary-subtle mb-2 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-star icon-lg"></i>
                                                </div>
                                                <h3 class="mb-0 rate-percentage">
                                                    <?php echo number_format($stats['average_combined'], 2); ?></h3>
                                                <p class="mb-0 text-center statistics-title" style="font-size:0.72rem;">
                                                    Promedio Combinado</p>
                                                <div class="d-flex gap-3 mt-2">
                                                    <small class="text-muted">
                                                        <span
                                                            class="text-success fw-bold"><?php echo number_format($stats['average_experience'], 2); ?></span>
                                                        Exp
                                                    </small>
                                                    <small class="text-muted">
                                                        <span
                                                            class="text-info fw-bold"><?php echo number_format($stats['average_service'], 2); ?></span>
                                                        Atc
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <ul class="mb-4 nav nav-tabs nav-tabs-line" id="dashboardTabs" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#tab-general" type="button"><i
                                            class="me-1 mdi mdi-view-dashboard"></i>General</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-zona" type="button"><i class="me-1 mdi mdi-map-marker"></i>Por
                                        Zona</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-reconocimientos" type="button"><i
                                            class="me-1 mdi mdi-trophy"></i>Reconocimientos</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-tendencias" type="button"><i
                                            class="me-1 mdi mdi-chart-line"></i>Tendencias</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-alertas" type="button">
                                        <i class="me-1 mdi mdi-alert-circle"></i>Alertas
                                        <?php if($stats['muy_insatisfecho'] + $stats['insatisfecho'] > 0): ?>
                                            <span
                                                class="bg-danger ms-1 badge"><?php echo $stats['muy_insatisfecho'] + $stats['insatisfecho']; ?></span>
                                        <?php endif; ?>
                                    </button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tab-encuestas" type="button" id="btn-tab-encuestas">
                                        <i class="me-1 mdi mdi-clipboard-list"></i>Todas las Encuestas
                                        <span class="bg-primary ms-1 badge"
                                            id="badge-total-encuestas"><?php echo $stats['total']; ?></span>
                                    </button></li>
                            </ul>

                            <div class="tab-content" id="dashboardTabsContent">

                                
                                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

                                    <div class="row">
                                        
                                        <div class="grid-margin col-lg-4 stretch-card">
                                            <div class="h-100 card">
                                                <div class="card-body">
                                                    <h5 class="mb-1 card-title"><i
                                                            class="me-1 text-success mdi mdi-emoticon-happy"></i>Experiencia
                                                        General</h5>
                                                    <p class="mb-3 text-muted small">Promedio: <strong
                                                            class="text-success"><?php echo number_format($stats['average_experience'], 2); ?>/4</strong>
                                                    </p>
                                                    <?php if($stats['total'] > 0): ?>
                                                        <canvas id="chartExperience" height="220"></canvas>
                                                    <?php else: ?>
                                                        <div class="py-5 text-muted text-center"><i
                                                                class="d-block mb-2 mdi mdi-chart-donut mdi-48px"></i>Sin
                                                            datos en este período</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="grid-margin col-lg-4 stretch-card">
                                            <div class="h-100 card">
                                                <div class="card-body">
                                                    <h5 class="mb-1 card-title"><i
                                                            class="me-1 text-info mdi mdi-account-check"></i>Calidad de
                                                        Atención</h5>
                                                    <p class="mb-3 text-muted small">Promedio: <strong
                                                            class="text-info"><?php echo number_format($stats['average_service'], 2); ?>/4</strong>
                                                    </p>
                                                    <?php if($stats['total'] > 0): ?>
                                                        <canvas id="chartService" height="220"></canvas>
                                                    <?php else: ?>
                                                        <div class="py-5 text-muted text-center"><i
                                                                class="d-block mb-2 mdi mdi-chart-donut mdi-48px"></i>Sin
                                                            datos en este período</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="grid-margin col-lg-4 stretch-card">
                                            <div class="h-100 card">
                                                <div class="card-body">
                                                    <h5 class="mb-1 card-title"><i
                                                            class="me-1 text-primary mdi mdi-chart-bar"></i>Ranking
                                                        Combinado</h5>
                                                    <p class="mb-3 text-muted small">Promedio (Exp + Atc) / 2 por persona
                                                    </p>
                                                    <?php if($userStats->where('total', '>', 0)->count() > 0): ?>
                                                        <canvas id="chartRanking" height="220"></canvas>
                                                    <?php else: ?>
                                                        <div class="py-5 text-muted text-center"><i
                                                                class="d-block mb-2 mdi mdi-chart-bar mdi-48px"></i>Sin
                                                            datos</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title"><i
                                                    class="mdi-account-group me-2 text-primary mdi"></i>Estadísticas por
                                                Consultor / Sede / Trimax</h4>
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tableUserStats">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Tipo</th>
                                                            <th class="text-center">Total</th>
                                                            <th class="text-center">😊</th>
                                                            <th class="text-center">🙂</th>
                                                            <th class="text-center">😐</th>
                                                            <th class="text-center">😞</th>
                                                            <th class="text-center">Exp.</th>
                                                            <th class="text-center">Atc.</th>
                                                            <th class="text-center">Combinado</th>
                                                            <th style="width:180px;">Distribución</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__empty_1 = true; $__currentLoopData = $userStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr
                                                                <?php if($s['role'] === 'trimax'): ?> style="background:rgba(26,26,46,.04)" <?php endif; ?>>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <img class="me-2 rounded-circle img-xs"
                                                                            src="https://ui-avatars.com/api/?name=<?php echo urlencode($s['name']); ?>&background=<?php echo $s['role'] === 'trimax' ? '1a1a2e' : '6366f1'; ?>&color=fff"
                                                                            alt="">
                                                                        <div>
                                                                            <strong><?php echo $s['name']; ?></strong>
                                                                            <?php if($s['role'] === 'consultor' && $s['sedes_count'] > 0): ?>
                                                                                <br><small class="text-muted"><i
                                                                                        class="mdi mdi-office-building"></i>
                                                                                    <?php echo $s['sedes_count']; ?> sede(s)</small>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <?php if($s['role'] === 'consultor'): ?>
                                                                        <span class="badge badge-primary">Consultor</span>
                                                                    <?php elseif($s['role'] === 'trimax'): ?>
                                                                        <span class="badge"
                                                                            style="background:#1a1a2e;color:#fff">TRIMAX</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-info">Sede
                                                                            <?php echo $s['location']; ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center"><strong
                                                                        class="text-primary"><?php echo $s['total']; ?></strong>
                                                                </td>
                                                                <td class="text-center"><span
                                                                        class="badge badge-success"><?php echo $s['muy_feliz']; ?></span>
                                                                </td>
                                                                <td class="text-center"><span
                                                                        class="badge badge-info"><?php echo $s['feliz']; ?></span>
                                                                </td>
                                                                <td class="text-center"><span
                                                                        class="badge badge-warning"><?php echo $s['insatisfecho']; ?></span>
                                                                </td>
                                                                <td class="text-center"><span
                                                                        class="badge badge-danger"><?php echo $s['muy_insatisfecho']; ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="text-success fw-bold"><?php echo number_format($s['average_experience'], 2); ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="text-info fw-bold"><?php echo number_format($s['average_service'], 2); ?></span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php $comb = $s['average_combined']; ?>
                                                                    <span
                                                                        class="badge <?php echo $comb >= 3.5 ? 'badge-success' : ($comb >= 2.5 ? 'badge-warning' : 'badge-danger'); ?> px-3 py-2"
                                                                        style="font-size:.85rem;">
                                                                        ⭐ <?php echo number_format($comb, 2); ?>

                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if($s['total'] > 0): ?>
                                                                        <?php
                                                                            $t = $s['total'];
                                                                            $p1 = round(($s['muy_feliz'] / $t) * 100);
                                                                            $p2 = round(($s['feliz'] / $t) * 100);
                                                                            $p3 = round(
                                                                                ($s['insatisfecho'] / $t) * 100,
                                                                            );
                                                                            $p4 = round(
                                                                                ($s['muy_insatisfecho'] / $t) * 100,
                                                                            );
                                                                        ?>
                                                                        <div class="progress"
                                                                            style="height:22px;border-radius:4px;">
                                                                            <?php if($p1 > 0): ?>
                                                                                <div class="bg-success progress-bar"
                                                                                    style="width:<?php echo $p1; ?>%"
                                                                                    title="Muy Feliz <?php echo $p1; ?>%">
                                                                                    <?php echo $p1 > 10 ? $p1 . '%' : ''; ?></div>
                                                                            <?php endif; ?>
                                                                            <?php if($p2 > 0): ?>
                                                                                <div class="bg-info progress-bar"
                                                                                    style="width:<?php echo $p2; ?>%"
                                                                                    title="Feliz <?php echo $p2; ?>%">
                                                                                    <?php echo $p2 > 10 ? $p2 . '%' : ''; ?></div>
                                                                            <?php endif; ?>
                                                                            <?php if($p3 > 0): ?>
                                                                                <div class="bg-warning progress-bar"
                                                                                    style="width:<?php echo $p3; ?>%"
                                                                                    title="Insatisfecho <?php echo $p3; ?>%">
                                                                                    <?php echo $p3 > 10 ? $p3 . '%' : ''; ?></div>
                                                                            <?php endif; ?>
                                                                            <?php if($p4 > 0): ?>
                                                                                <div class="bg-danger progress-bar"
                                                                                    style="width:<?php echo $p4; ?>%"
                                                                                    title="Muy Insatisfecho <?php echo $p4; ?>%">
                                                                                    <?php echo $p4 > 10 ? $p4 . '%' : ''; ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <small class="text-muted">Sin encuestas</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <tr>
                                                                <td colspan="11" class="py-4 text-muted text-center">No
                                                                    hay datos disponibles</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title"><i
                                                    class="me-2 mdi-clock-outline text-info mdi"></i>Encuestas Recientes
                                                <small class="text-muted fw-normal">(clic en la fila para ver
                                                    detalle)</small></h4>
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tableSurveys">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Fecha</th>
                                                            <th>Cliente</th>
                                                            <th>Evaluado</th>
                                                            <th class="text-center">Experiencia</th>
                                                            <th class="text-center">Atención</th>
                                                            <th class="text-center">Combinado</th>
                                                            <th>Comentario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $recentSurveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php $comb = round(($sv->experience_rating + $sv->service_quality_rating)/2, 2); ?>
                                                            <tr class="survey-row" data-survey-id="<?php echo $sv->id; ?>"
                                                                style="cursor:pointer;"
                                                                title="Clic para ver detalle completo">
                                                                <td><small class="text-muted">#<?php echo $sv->id; ?></small>
                                                                </td>
                                                                <td>
                                                                    <small
                                                                        class="text-muted"><?php echo $sv->created_at->format('d/m/Y'); ?></small><br>
                                                                    <small><?php echo $sv->created_at->format('H:i'); ?></small>
                                                                </td>
                                                                <td>
                                                                    <?php if($sv->client_name): ?>
                                                                        <div class="d-flex align-items-center">
                                                                            <img class="me-2 rounded-circle img-xs"
                                                                                src="https://ui-avatars.com/api/?name=<?php echo urlencode($sv->client_name); ?>&background=10b981&color=fff"
                                                                                alt="">
                                                                            <strong><?php echo $sv->client_name; ?></strong>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted"><i
                                                                                class="mdi mdi-account-off"></i>
                                                                            Anónimo</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <strong><?php echo $sv->userMarketing->name; ?></strong><br>
                                                                    <small class="text-muted">
                                                                        <?php if($sv->userMarketing->role === 'consultor'): ?>
                                                                            Consultor
                                                                        <?php elseif($sv->userMarketing->role === 'trimax'): ?>
                                                                            TRIMAX General
                                                                        <?php else: ?>
                                                                            Sede <?php echo $sv->userMarketing->location; ?>

                                                                        <?php endif; ?>
                                                                    </small>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php echo $__env->make(
                                                                        'marketing.dashboard._rating_badge',
                                                                        ['rating' => $sv->experience_rating]
                                                                    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php echo $__env->make(
                                                                        'marketing.dashboard._rating_badge',
                                                                        ['rating' => $sv->service_quality_rating]
                                                                    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span
                                                                        class="badge <?php echo $comb >= 3.5 ? 'badge-success' : ($comb >= 2.5 ? 'badge-warning' : 'badge-danger'); ?>">
                                                                        <?php echo number_format($comb, 2); ?>

                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <?php if($sv->comments): ?>
                                                                        <small><?php echo Str::limit($sv->comments, 45); ?></small>
                                                                    <?php else: ?>
                                                                        <small class="text-muted">—</small>
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

                                
                                <div class="tab-pane fade" id="tab-zona" role="tabpanel">
                                    <?php
                                        $sedeStats = $userStats->where('role', 'sede');
                                        $zonas = $sedeStats->groupBy('location');
                                    ?>
                                    <div class="row">
                                        <?php $__currentLoopData = $zonas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc => $sedes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $zt = $sedes->sum('total');
                                                $zavg = $sedes->avg('average_combined');
                                            ?>
                                            <div class="grid-margin col-xl-4 col-lg-6 col-md-6 stretch-card">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                                            <h5 class="mb-0 card-title"><i
                                                                    class="me-1 text-primary mdi mdi-map-marker"></i><?php echo $loc; ?>

                                                            </h5>
                                                            <span class="badge badge-primary"><?php echo $sedes->count(); ?>

                                                                sede(s)</span>
                                                        </div>
                                                        <div class="mb-3 text-center row">
                                                            <div class="col-6">
                                                                <h3 class="mb-0 rate-percentage"><?php echo $zt; ?></h3>
                                                                <p class="mb-0 statistics-title">Encuestas</p>
                                                            </div>
                                                            <div class="col-6">
                                                                <h3 class="mb-0 rate-percentage">
                                                                    <?php echo number_format($zavg, 2); ?></h3>
                                                                <p class="mb-0 statistics-title">Promedio ⭐</p>
                                                            </div>
                                                        </div>
                                                        <?php if($zt > 0): ?>
                                                            <?php
                                                                $zmf = $sedes->sum('muy_feliz');
                                                                $zf = $sedes->sum('feliz');
                                                                $zi = $sedes->sum('insatisfecho');
                                                                $zmi = $sedes->sum('muy_insatisfecho');
                                                            ?>
                                                            <div class="progress" style="height:22px;">
                                                                <?php if($zmf > 0): ?>
                                                                    <div class="bg-success progress-bar"
                                                                        style="width:<?php echo round(($zmf / $zt) * 100); ?>%">
                                                                        <?php echo round(($zmf / $zt) * 100) > 10 ? round(($zmf / $zt) * 100) . '%' : ''; ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if($zf > 0): ?>
                                                                    <div class="bg-info progress-bar"
                                                                        style="width:<?php echo round(($zf / $zt) * 100); ?>%">
                                                                        <?php echo round(($zf / $zt) * 100) > 10 ? round(($zf / $zt) * 100) . '%' : ''; ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if($zi > 0): ?>
                                                                    <div class="bg-warning progress-bar"
                                                                        style="width:<?php echo round(($zi / $zt) * 100); ?>%">
                                                                        <?php echo round(($zi / $zt) * 100) > 10 ? round(($zi / $zt) * 100) . '%' : ''; ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if($zmi > 0): ?>
                                                                    <div class="bg-danger progress-bar"
                                                                        style="width:<?php echo round(($zmi / $zt) * 100); ?>%">
                                                                        <?php echo round(($zmi / $zt) * 100) > 10 ? round(($zmi / $zt) * 100) . '%' : ''; ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <?php if($zonas->count() > 1): ?>
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <h5 class="mb-3 card-title"><i
                                                        class="me-2 text-info mdi mdi-chart-bar"></i>Comparativa de Zonas
                                                    (Promedio Combinado)</h5>
                                                <canvas id="chartZona" height="80"></canvas>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="tab-pane fade" id="tab-reconocimientos" role="tabpanel">
                                    <?php
                                        $personal = $userStats->whereNotIn('role', ['trimax'])->where('total', '>', 0);
                                        $topRated = $personal->sortByDesc('average_combined')->take(3)->values();
                                        $mostSurveys = $personal->sortByDesc('total')->take(3)->values();
                                    ?>
                                    <div class="row">
                                        <div class="grid-margin col-lg-6 stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="mb-4 card-title"><i
                                                            class="me-2 text-warning mdi mdi-trophy"></i>Mejor Promedio
                                                        Combinado</h4>
                                                    <?php $__empty_1 = true; $__currentLoopData = $topRated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <div class="d-flex align-items-center py-3 border-bottom">
                                                            <span class="me-3"
                                                                style="font-size:2rem"><?php echo ['🥇', '🥈', '🥉'][$i] ?? '#' . ($i + 1); ?></span>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1"><?php echo $u['name']; ?></h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <span class="badge badge-success">⭐
                                                                        <?php echo number_format($u['average_combined'], 2); ?>

                                                                        combinado</span>
                                                                    <small class="text-muted">Exp:
                                                                        <?php echo number_format($u['average_experience'], 2); ?> |
                                                                        Atc:
                                                                        <?php echo number_format($u['average_service'], 2); ?></small>
                                                                    <small class="text-muted"><?php echo $u['total']; ?>

                                                                        encuestas</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <p class="py-4 text-muted text-center">Sin datos suficientes</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid-margin col-lg-6 stretch-card">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="mb-4 card-title"><i
                                                            class="me-2 text-primary mdi mdi-chart-line"></i>Más Evaluados
                                                    </h4>
                                                    <?php $__empty_1 = true; $__currentLoopData = $mostSurveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <div class="mb-3 card"
                                                            style="background:linear-gradient(135deg,#667eea,#764ba2)">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between text-white card-body">
                                                                <div>
                                                                    <h6 class="mb-1 text-white"><?php echo $u['name']; ?></h6>
                                                                    <p class="opacity-75 mb-0" style="font-size:.8rem">
                                                                        ⭐ <?php echo number_format($u['average_combined'], 2); ?>

                                                                        promedio combinado
                                                                    </p>
                                                                </div>
                                                                <div class="text-end">
                                                                    <h2 class="mb-0 text-white"><?php echo $u['total']; ?></h2>
                                                                    <small class="opacity-75">encuestas</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <p class="py-4 text-muted text-center">Sin datos suficientes</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="tab-pane fade" id="tab-tendencias" role="tabpanel">
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <h5 class="mb-1 card-title"><i
                                                    class="me-2 text-info mdi mdi-chart-timeline"></i>Evolución Diaria</h5>
                                            <p class="mb-3 text-muted small">Promedio de experiencia, atención y combinado
                                                por día</p>
                                            <?php if($dailyTrend->count() > 0): ?>
                                                <canvas id="chartTrend" height="80"></canvas>
                                            <?php else: ?>
                                                <div class="py-5 text-muted text-center"><i
                                                        class="d-block mb-2 mdi mdi-chart-line mdi-48px"></i>Sin datos en
                                                    el período seleccionado</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <h5 class="mb-1 card-title"><i
                                                    class="me-2 text-primary mdi mdi-chart-bar"></i>Volumen diario de
                                                encuestas</h5>
                                            <?php if($dailyTrend->count() > 0): ?>
                                                <canvas id="chartVolume" height="80"></canvas>
                                            <?php else: ?>
                                                <div class="py-4 text-muted text-center">Sin datos</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="tab-pane fade" id="tab-alertas" role="tabpanel">
                                    <?php
                                        $lowRated = $userStats
                                            ->filter(fn($u) => $u['average_combined'] < 2.5 && $u['total'] > 0)
                                            ->sortBy('average_combined');
                                        $recentBad = $recentSurveys
                                            ->filter(
                                                fn($s) => $s->experience_rating <= 2 || $s->service_quality_rating <= 2,
                                            )
                                            ->take(20);
                                    ?>

                                    <?php if($lowRated->count() > 0): ?>
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <div class="mb-0 border-0 alert alert-danger">
                                                    <h5 class="mb-3"><i class="me-2 mdi mdi-alert-circle"></i>Usuarios
                                                        con Promedio Combinado Bajo (&lt; 2.5)</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Usuario</th>
                                                                    <th>Tipo</th>
                                                                    <th class="text-center">Exp.</th>
                                                                    <th class="text-center">Atc.</th>
                                                                    <th class="text-center">Combinado</th>
                                                                    <th class="text-center">Negativos</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__currentLoopData = $lowRated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <tr>
                                                                        <td><strong><?php echo $u['name']; ?></strong></td>
                                                                        <td>
                                                                            <?php if($u['role'] === 'consultor'): ?>
                                                                                <span
                                                                                    class="badge badge-primary">Consultor</span>
                                                                            <?php elseif($u['role'] === 'trimax'): ?>
                                                                                <span class="badge"
                                                                                    style="background:#1a1a2e;color:#fff">TRIMAX</span>
                                                                            <?php else: ?><span
                                                                                    class="badge badge-info">Sede</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="text-center"><span
                                                                                class="badge badge-danger"><?php echo number_format($u['average_experience'], 2); ?></span>
                                                                        </td>
                                                                        <td class="text-center"><span
                                                                                class="badge badge-danger"><?php echo number_format($u['average_service'], 2); ?></span>
                                                                        </td>
                                                                        <td class="text-center"><span
                                                                                class="badge badge-danger fw-bold"><?php echo number_format($u['average_combined'], 2); ?></span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?php echo $u['muy_insatisfecho'] + $u['insatisfecho']; ?>

                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <div class="mb-0 border-0 alert alert-success">
                                                    <h5><i class="me-2 mdi mdi-check-circle"></i>Todo en Orden</h5>
                                                    <p class="mb-0">Ningún usuario tiene promedio combinado crítico en
                                                        este período.</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if($recentBad->count() > 0): ?>
                                        <div class="grid-margin card">
                                            <div class="card-body">
                                                <h4 class="mb-3 card-title"><i
                                                        class="me-2 text-warning mdi mdi-alert"></i>Encuestas Negativas
                                                    Recientes <small class="text-muted fw-normal">(clic para ver
                                                        detalle)</small></h4>
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Fecha</th>
                                                                <th>Cliente</th>
                                                                <th>Evaluado</th>
                                                                <th class="text-center">Exp.</th>
                                                                <th class="text-center">Atc.</th>
                                                                <th>Comentario</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $recentBad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr class="survey-row"
                                                                    data-survey-id="<?php echo $sv->id; ?>"
                                                                    style="cursor:pointer;">
                                                                    <td><small
                                                                            class="text-muted">#<?php echo $sv->id; ?></small>
                                                                    </td>
                                                                    <td><small><?php echo $sv->created_at->format('d/m H:i'); ?></small>
                                                                    </td>
                                                                    <td><?php echo $sv->client_name ?: 'Anónimo'; ?></td>
                                                                    <td><?php echo $sv->userMarketing->name; ?></td>
                                                                    <td class="text-center"><?php echo $__env->make(
                                                                        'marketing.dashboard._rating_badge',
                                                                        ['rating' => $sv->experience_rating]
                                                                    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                                    </td>
                                                                    <td class="text-center"><?php echo $__env->make(
                                                                        'marketing.dashboard._rating_badge',
                                                                        ['rating' => $sv->service_quality_rating]
                                                                    , array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                                    </td>
                                                                    <td><small><?php echo Str::limit($sv->comments ?? '—', 50); ?></small>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="tab-pane fade" id="tab-encuestas" role="tabpanel">

                                    
                                    <div class="grid-margin card">
                                        <div class="py-3 card-body">
                                            <div class="align-items-end row g-2">
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Fecha Inicio</label>
                                                    <input type="date" id="enc-start"
                                                        class="form-control form-control-sm" value="<?php echo $startDate; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Fecha Fin</label>
                                                    <input type="date" id="enc-end"
                                                        class="form-control form-control-sm" value="<?php echo $endDate; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="mb-1 form-label small">Evaluado</label>
                                                    <select id="enc-user" class="form-select-sm form-select">
                                                        <option value="">Todos</option>
                                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo $u->id; ?>"
                                                                <?php echo $userId == $u->id ? 'selected' : ''; ?>>
                                                                <?php echo $u->name; ?>

                                                                <?php if($u->role === 'consultor'): ?>
                                                                    (Consultor)
                                                                <?php elseif($u->role === 'trimax'): ?>
                                                                    (TRIMAX)
                                                                <?php else: ?>
                                                                    (Sede)
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="mb-1 form-label small">Calificación</label>
                                                    <select id="enc-rating" class="form-select-sm form-select">
                                                        <option value="">Todas</option>
                                                        <option value="4">😊 Muy Feliz</option>
                                                        <option value="3">🙂 Feliz</option>
                                                        <option value="2">😐 Insatisfecho</option>
                                                        <option value="1">😞 Muy Insatisfecho</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex gap-1 col-md-1">
                                                    <button class="w-100 text-white btn btn-primary btn-sm"
                                                        onclick="loadEncuestas(1)">
                                                        <i class="mdi mdi-magnify"></i>
                                                    </button>
                                                    <button class="btn-outline-secondary btn btn-sm"
                                                        onclick="resetEncuestas()" title="Limpiar filtros">
                                                        <i class="mdi mdi-refresh"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="grid-margin card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h4 class="mb-0 card-title">
                                                    <i class="me-2 text-primary mdi mdi-clipboard-list"></i>
                                                    Listado de Encuestas
                                                    <small class="ms-2 text-muted fw-normal" id="enc-count-label"></small>
                                                </h4>
                                                <small class="text-muted"><i class="me-1 mdi-cursor-pointer mdi"></i>Clic
                                                    en la fila para ver detalle</small>
                                            </div>

                                            
                                            <div id="enc-loading" class="py-5 text-center" style="display:none;">
                                                <div class="spinner-border text-primary" role="status"></div>
                                                <p class="mt-2 text-muted small">Cargando encuestas...</p>
                                            </div>

                                            
                                            <div class="table-responsive" id="enc-table-wrapper">
                                                <table class="table table-hover" id="enc-table">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Fecha</th>
                                                            <th>Cliente</th>
                                                            <th>Evaluado</th>
                                                            <th>Tipo</th>
                                                            <th class="text-center">Experiencia</th>
                                                            <th class="text-center">Atención</th>
                                                            <th class="text-center">Combinado</th>
                                                            <th>Comentario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="enc-tbody">
                                                        <tr>
                                                            <td colspan="9" class="py-4 text-muted text-center">
                                                                <i
                                                                    class="d-block mdi-filter-variant mb-2 mdi mdi-36px"></i>
                                                                Usa los filtros y presiona <strong>buscar</strong> para
                                                                cargar encuestas
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            
                                            <div id="enc-pagination"
                                                class="d-flex align-items-center justify-content-between mt-3"
                                                style="display:none!important;">
                                                <small class="text-muted" id="enc-pagination-info"></small>
                                                <div id="enc-pagination-links" class="d-flex gap-1"></div>
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

    
    <div class="modal fade" id="surveyDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="shadow border-0 modal-content">
                <div class="text-white modal-header" id="modalHeader"
                    style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <div>
                        <h5 class="mb-0 modal-title"><i class="me-2 mdi mdi-clipboard-text"></i>Detalle de Encuesta <span
                                id="modalSurveyId"></span></h5>
                        <small class="opacity-75" id="modalDate"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-0 modal-body" id="modalBody">
                    <div class="py-5 text-center">
                        <div class="spinner-border text-primary" role="status"><span
                                class="visually-hidden">Cargando...</span></div>
                        <p class="mt-2 text-muted">Cargando detalle...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-lg {
            font-size: 2rem;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-tabs-line .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            padding: .75rem 1.25rem;
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

        .progress-bar {
            font-size: .7rem;
            font-weight: 600;
        }

        .survey-row:hover {
            background: rgba(99, 102, 241, .06) !important;
        }

        .bg-primary-subtle {
            background: rgba(99, 102, 241, .1) !important;
        }

        .bg-success-subtle {
            background: rgba(76, 175, 80, .1) !important;
        }

        .bg-info-subtle {
            background: rgba(13, 202, 240, .1) !important;
        }

        .bg-warning-subtle {
            background: rgba(255, 193, 7, .1) !important;
        }

        .bg-danger-subtle {
            background: rgba(220, 53, 69, .1) !important;
        }

        /* modal rating pills */
        .modal-rating-pill {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 12px 20px;
            border-radius: 12px;
        }

        .modal-rating-pill .emoji {
            font-size: 2.5rem;
        }

        .modal-rating-pill .label {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .modal-rating-pill.muy-feliz {
            background: rgba(76, 175, 80, .1);
            color: #2e7d32;
        }

        .modal-rating-pill.feliz {
            background: rgba(13, 202, 240, .1);
            color: #0277bd;
        }

        .modal-rating-pill.insatisfecho {
            background: rgba(255, 193, 7, .1);
            color: #e65100;
        }

        .modal-rating-pill.muy-insatisfecho {
            background: rgba(220, 53, 69, .1);
            color: #c62828;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // ─── DATOS PARA GRÁFICOS ────────────────────────────────────────────────
        const statsData = {
            muy_feliz: <?php echo $stats['muy_feliz']; ?>,
            feliz: <?php echo $stats['feliz']; ?>,
            insatisfecho: <?php echo $stats['insatisfecho']; ?>,
            muy_insatisfecho: <?php echo $stats['muy_insatisfecho']; ?>,
            avg_experience: <?php echo $stats['average_experience']; ?>,
            avg_service: <?php echo $stats['average_service']; ?>,
        };

        const userStatsData = <?php echo json_encode($userStats->values()->where('total', '>', 0)->take(12)->values()) ?>;

        const dailyData = <?php echo json_encode($dailyTrend, 15, 512) ?>;

        <?php
            $zonas = $userStats->where('role', 'sede')->groupBy('location');
            $zonaLabels = $zonas->keys();
            $zonaAvgs = $zonas->map(fn($s) => round($s->avg('average_combined'), 2))->values();
        ?>
        const zonaLabels = <?php echo json_encode($zonaLabels, 15, 512) ?>;
        const zonaAvgs = <?php echo json_encode($zonaAvgs, 15, 512) ?>;

        // ─── HELPERS ────────────────────────────────────────────────────────────
        const COLORS = {
            muyFeliz: '#4CAF50',
            feliz: '#0dcaf0',
            insatisfecho: '#ffc107',
            muyInsatisfecho: '#dc3545'
        };
        const LABELS = [null, 'Muy Insatisfecho', 'Insatisfecho', 'Feliz', 'Muy Feliz'];
        const EMOJIS = [null, '😞', '😐', '🙂', '😊'];
        const BADGE = [null, 'danger', 'warning', 'info', 'success'];
        const CHART_DEFAULTS = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        };

        function donut(id, data, labels, colors) {
            const el = document.getElementById(id);
            if (!el) return;
            return new Chart(el, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    ...CHART_DEFAULTS,
                    plugins: {
                        ...CHART_DEFAULTS.plugins,
                        tooltip: {
                            callbacks: {
                                label(ctx) {
                                    const tot = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    return `${ctx.label}: ${ctx.parsed} (${tot?((ctx.parsed/tot)*100).toFixed(1):0}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ─── CHARTS ─────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {

            // Donut Experiencia
            if (statsData.muy_feliz + statsData.feliz + statsData.insatisfecho + statsData.muy_insatisfecho > 0) {
                donut('chartExperience',
                    [statsData.muy_feliz, statsData.feliz, statsData.insatisfecho, statsData.muy_insatisfecho],
                    ['Muy Feliz', 'Feliz', 'Insatisfecho', 'Muy Insatisfecho'],
                    [COLORS.muyFeliz, COLORS.feliz, COLORS.insatisfecho, COLORS.muyInsatisfecho]
                );

                // Donut Atención — contamos por service_quality usando datos generales
                // Nota: los counts de servicio por nivel se calculan en el servidor si se desea granular.
                // Aquí re-usamos el promedio para hacer una barra comparativa.
                const svcEl = document.getElementById('chartService');
                if (svcEl) {
                    // Barra horizontal comparando Exp vs Atc promedio
                    new Chart(svcEl, {
                        type: 'bar',
                        data: {
                            labels: ['Promedio Experiencia', 'Promedio Atención', 'Promedio Combinado'],
                            datasets: [{
                                data: [statsData.avg_experience, statsData.avg_service,
                                    +((statsData.avg_experience + statsData.avg_service) / 2)
                                    .toFixed(2)
                                ],
                                backgroundColor: ['#4CAF50', '#0dcaf0', '#6366f1'],
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            ...CHART_DEFAULTS,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: false,
                                tooltip: {
                                    callbacks: {
                                        label(ctx) {
                                            return ` ${ctx.parsed.x}/4`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 4,
                                    ticks: {
                                        stepSize: 1
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Bar ranking combinado
            const withData = userStatsData.filter(u => u.total > 0);
            const rankEl = document.getElementById('chartRanking');
            if (rankEl && withData.length > 0) {
                new Chart(rankEl, {
                    type: 'bar',
                    data: {
                        labels: withData.map(u => u.name.split(' ')[0]),
                        datasets: [{
                                label: 'Experiencia',
                                data: withData.map(u => u.average_experience),
                                backgroundColor: 'rgba(76,175,80,.7)',
                                borderRadius: 4
                            },
                            {
                                label: 'Atención',
                                data: withData.map(u => u.average_service),
                                backgroundColor: 'rgba(13,202,240,.7)',
                                borderRadius: 4
                            },
                            {
                                label: 'Combinado',
                                data: withData.map(u => u.average_combined),
                                backgroundColor: 'rgba(99,102,241,.9)',
                                borderRadius: 4
                            },
                        ]
                    },
                    options: {
                        ...CHART_DEFAULTS,
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

            // Tendencia diaria — líneas
            const trendEl = document.getElementById('chartTrend');
            if (trendEl && dailyData.length > 0) {
                const labels = dailyData.map(d => d.date);
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                                label: 'Experiencia',
                                data: dailyData.map(d => d.avg_experience),
                                borderColor: '#4CAF50',
                                backgroundColor: 'rgba(76,175,80,.1)',
                                tension: .3,
                                fill: true,
                                pointRadius: 4
                            },
                            {
                                label: 'Atención',
                                data: dailyData.map(d => d.avg_service),
                                borderColor: '#0dcaf0',
                                backgroundColor: 'rgba(13,202,240,.1)',
                                tension: .3,
                                fill: true,
                                pointRadius: 4
                            },
                            {
                                label: 'Combinado',
                                data: dailyData.map(d => d.avg_combined),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,.08)',
                                tension: .3,
                                fill: false,
                                pointRadius: 4,
                                borderWidth: 2.5
                            },
                        ]
                    },
                    options: {
                        ...CHART_DEFAULTS,
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

            // Volumen diario — barras
            const volEl = document.getElementById('chartVolume');
            if (volEl && dailyData.length > 0) {
                new Chart(volEl, {
                    type: 'bar',
                    data: {
                        labels: dailyData.map(d => d.date),
                        datasets: [{
                            label: 'Encuestas',
                            data: dailyData.map(d => d.total),
                            backgroundColor: '#6366f1',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        ...CHART_DEFAULTS,
                        plugins: {
                            ...CHART_DEFAULTS.plugins,
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
            }

            // Zona chart
            const zonaEl = document.getElementById('chartZona');
            if (zonaEl && zonaLabels.length > 0) {
                new Chart(zonaEl, {
                    type: 'bar',
                    data: {
                        labels: zonaLabels,
                        datasets: [{
                            label: 'Promedio Combinado',
                            data: zonaAvgs,
                            backgroundColor: '#6366f1',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        ...CHART_DEFAULTS,
                        plugins: {
                            ...CHART_DEFAULTS.plugins,
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

            // Tooltips Bootstrap
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        });

        // ─── MODAL DETALLE ENCUESTA ─────────────────────────────────────────────
        document.querySelectorAll('.survey-row').forEach(row => {
            row.addEventListener('click', () => openSurveyModal(row.dataset.surveyId));
        });

        function openSurveyModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('surveyDetailModal'));
            const body = document.getElementById('modalBody');
            const header = document.getElementById('modalHeader');

            document.getElementById('modalSurveyId').textContent = '#' + id;
            document.getElementById('modalDate').textContent = '';
            body.innerHTML =
                `<div class="py-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Cargando...</p></div>`;
            header.style.background = 'linear-gradient(135deg,#667eea,#764ba2)';
            modal.show();

            fetch(`/marketing/surveys/${id}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error('Error');
                    const sv = data.survey;

                    // Color del header según rating más bajo
                    const worst = Math.min(sv.experience_rating, sv.service_quality_rating);
                    const headerBg = worst === 1 ? 'linear-gradient(135deg,#c62828,#e53935)' :
                        worst === 2 ? 'linear-gradient(135deg,#e65100,#f57c00)' :
                        worst === 3 ? 'linear-gradient(135deg,#0277bd,#039be5)' :
                        'linear-gradient(135deg,#2e7d32,#43a047)';
                    header.style.background = headerBg;
                    document.getElementById('modalDate').textContent = sv.created_at;

                    function ratingPill(rating) {
                        const cls = {
                            4: 'muy-feliz',
                            3: 'feliz',
                            2: 'insatisfecho',
                            1: 'muy-insatisfecho'
                        } [rating] ?? '';
                        const emoji = EMOJIS[rating] ?? '?';
                        const label = LABELS[rating] ?? 'N/A';
                        return `<div class="modal-rating-pill ${cls}"><span class="emoji">${emoji}</span><span class="label">${label}</span><small>(${rating}/4)</small></div>`;
                    }

                    const comb = sv.average_combined;
                    const combCls = comb >= 3.5 ? 'success' : comb >= 2.5 ? 'warning' : 'danger';

                    body.innerHTML = `
            <div class="p-4">
                
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <img class="me-3 rounded-circle" width="56" height="56"
                         src="https://ui-avatars.com/api/?name=${encodeURIComponent(sv.evaluado_name)}&background=6366f1&color=fff&size=128" alt="">
                    <div>
                        <h5 class="mb-1">${sv.evaluado_name}</h5>
                        <span class="badge badge-primary">${sv.evaluado_role === 'consultor' ? 'Consultor' : sv.evaluado_role === 'trimax' ? 'TRIMAX' : 'Sede — '+sv.evaluado_location}</span>
                    </div>
                    <div class="ms-auto text-end">
                        <small class="d-block text-muted">Cliente</small>
                        <strong>${sv.client_name}</strong>
                    </div>
                </div>

                
                <div class="mb-4 row">
                    <div class="text-center col-4">
                        <small class="d-block mb-2 text-muted fw-bold">Experiencia</small>
                        ${ratingPill(sv.experience_rating)}
                    </div>
                    <div class="text-center col-4">
                        <small class="d-block mb-2 text-muted fw-bold">Atención</small>
                        ${ratingPill(sv.service_quality_rating)}
                    </div>
                    <div class="text-center col-4">
                        <small class="d-block mb-2 text-muted fw-bold">Promedio Combinado</small>
                        <div class="d-flex flex-column align-items-center gap-1">
                            <span class="badge bg-${combCls} px-4 py-3" style="font-size:1.4rem;">⭐ ${comb}</span>
                            <small class="text-muted">/ 4.00</small>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Distribución del promedio combinado</small>
                        <small class="fw-bold">${((comb/4)*100).toFixed(1)}%</small>
                    </div>
                    <div class="progress" style="height:12px;border-radius:6px;">
                        <div class="progress-bar bg-${combCls}" style="width:${(comb/4)*100}%"></div>
                    </div>
                </div>

                
                <div>
                    <p class="mb-2 fw-bold"><i class="me-1 text-primary mdi mdi-comment-text"></i>Comentarios del cliente</p>
                    ${sv.comments
                        ? `<blockquote class="p-3 rounded blockquote-style" style="background:#fff8e1;border-left:4px solid #ffc107;font-style:italic;color:#555;">"${sv.comments}"</blockquote>`
                        : `<p class="text-muted">El cliente no dejó comentarios.</p>`
                    }
                </div>
            </div>`;
                })
                .catch(() => {
                    body.innerHTML =
                        `<div class="py-5 text-danger text-center"><i class="d-block mb-2 mdi mdi-alert-circle mdi-48px"></i>Error al cargar el detalle</div>`;
                });
        }

        // ─── TAB: TODAS LAS ENCUESTAS (AJAX + paginación) ───────────────────────────
        const RATING_BADGE = {
            4: '<span class="badge badge-success"><i class="mdi mdi-emoticon-excited"></i> Muy Feliz</span>',
            3: '<span class="badge badge-info"><i class="mdi mdi-emoticon-happy"></i> Feliz</span>',
            2: '<span class="badge badge-warning"><i class="mdi mdi-emoticon-neutral"></i> Insatisfecho</span>',
            1: '<span class="badge badge-danger"><i class="mdi mdi-emoticon-sad"></i> Muy Insatisfecho</span>',
        };

        let encCurrentPage = 1;

        function loadEncuestas(page = 1) {
            encCurrentPage = page;
            const start = document.getElementById('enc-start').value;
            const end = document.getElementById('enc-end').value;
            const userId = document.getElementById('enc-user').value;
            const rating = document.getElementById('enc-rating').value;

            const params = new URLSearchParams({
                page
            });
            if (start) params.append('start_date', start);
            if (end) params.append('end_date', end);
            if (userId) params.append('user_id', userId);
            if (rating) params.append('rating', rating);

            document.getElementById('enc-loading').style.display = 'block';
            document.getElementById('enc-table-wrapper').style.opacity = '0.4';

            fetch(`/marketing/encuestas/ajax?${params}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('enc-loading').style.display = 'none';
                    document.getElementById('enc-table-wrapper').style.opacity = '1';

                    const tbody = document.getElementById('enc-tbody');

                    if (!data.data || data.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="9" class="py-5 text-muted text-center">
                    <i class="d-block mb-2 mdi mdi-inbox mdi-36px"></i>No se encontraron encuestas con los filtros aplicados
                </td></tr>`;
                        document.getElementById('enc-count-label').textContent = '';
                        document.getElementById('enc-pagination').style.display = 'none';
                        return;
                    }

                    tbody.innerHTML = data.data.map(sv => {
                        const comb = ((sv.experience_rating + sv.service_quality_rating) / 2).toFixed(2);
                        const combCls = comb >= 3.5 ? 'badge-success' : comb >= 2.5 ? 'badge-warning' :
                            'badge-danger';
                        const tipo = sv.evaluado_role === 'consultor' ? 'Consultor' :
                            sv.evaluado_role === 'trimax' ? 'TRIMAX' :
                            `Sede`;
                        return `<tr class="survey-row" data-survey-id="${sv.id}" style="cursor:pointer;" title="Clic para ver detalle">
                    <td><small class="text-muted">#${sv.id}</small></td>
                    <td>
                        <small class="d-block text-muted">${sv.date}</small>
                        <small>${sv.time}</small>
                    </td>
                    <td>
                        ${sv.client_name
                            ? `<div class="d-flex align-items-center">
                                    <img class="me-2 rounded-circle img-xs"
                                        src="https://ui-avatars.com/api/?name=${encodeURIComponent(sv.client_name)}&background=10b981&color=fff" alt="">
                                    <strong>${sv.client_name}</strong>
                                   </div>`
                            : '<span class="text-muted"><i class="mdi mdi-account-off"></i> Anónimo</span>'
                        }
                    </td>
                    <td><strong>${sv.evaluado_name}</strong></td>
                    <td><span class="badge badge-secondary">${tipo}</span></td>
                    <td class="text-center">${RATING_BADGE[sv.experience_rating] ?? sv.experience_rating}</td>
                    <td class="text-center">${RATING_BADGE[sv.service_quality_rating] ?? sv.service_quality_rating}</td>
                    <td class="text-center"><span class="badge ${combCls}">${comb}</span></td>
                    <td><small class="text-muted">${sv.comments ? sv.comments.substring(0,45) + (sv.comments.length > 45 ? '…' : '') : '—'}</small></td>
                </tr>`;
                    }).join('');

                    // Registrar event listeners para el modal
                    document.querySelectorAll('#enc-tbody .survey-row').forEach(row => {
                        row.addEventListener('click', () => openSurveyModal(row.dataset.surveyId));
                    });

                    // Info paginación
                    document.getElementById('enc-count-label').textContent =
                        `(${data.from}–${data.to} de ${data.total})`;
                    document.getElementById('enc-pagination').style.display = 'flex';
                    document.getElementById('enc-pagination-info').textContent =
                        `Mostrando ${data.from}–${data.to} de ${data.total} encuestas`;

                    // Links de paginación
                    const linksEl = document.getElementById('enc-pagination-links');
                    linksEl.innerHTML = '';
                    if (data.prev_page_url) {
                        linksEl.innerHTML += `<button class="btn-outline-primary btn btn-sm" onclick="loadEncuestas(${data.current_page - 1})">
                    <i class="mdi-chevron-left mdi"></i>
                </button>`;
                    }
                    linksEl.innerHTML +=
                        `<span class="text-white btn btn-sm btn-primary disabled">${data.current_page} / ${data.last_page}</span>`;
                    if (data.next_page_url) {
                        linksEl.innerHTML += `<button class="btn-outline-primary btn btn-sm" onclick="loadEncuestas(${data.current_page + 1})">
                    <i class="mdi-chevron-right mdi"></i>
                </button>`;
                    }

                    // Actualizar badge del tab
                    document.getElementById('badge-total-encuestas').textContent = data.total;
                })
                .catch(err => {
                    document.getElementById('enc-loading').style.display = 'none';
                    document.getElementById('enc-table-wrapper').style.opacity = '1';
                    console.error('Error cargando encuestas:', err);
                });
        }

        function resetEncuestas() {
            document.getElementById('enc-start').value = '';
            document.getElementById('enc-end').value = '';
            document.getElementById('enc-user').value = '';
            document.getElementById('enc-rating').value = '';
            loadEncuestas(1);
        }

        // Cargar automáticamente al activar el tab — sin filtros de fecha iniciales
        document.getElementById('btn-tab-encuestas').addEventListener('shown.bs.tab', () => {
            document.getElementById('enc-start').value = '';
            document.getElementById('enc-end').value = '';
            document.getElementById('enc-user').value = '';
            document.getElementById('enc-rating').value = '';
            loadEncuestas(1);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/marketing/dashboard/index.blade.php ENDPATH**/ ?>