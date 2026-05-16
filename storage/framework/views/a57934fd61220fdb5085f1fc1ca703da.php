<?php $__env->startSection('title', 'Productivy Sedes'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Productivy page ─────────────────────────────── */
.week-nav-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    border: 2px solid var(--bs-border-color);
    background: transparent;
    color: var(--bs-body-color);
    transition: all .2s;
    cursor: pointer;
}
.week-nav-btn:hover { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; }

.kpi-stat-card { border-radius: 16px; overflow: hidden; }
.kpi-stat-card .kpi-icon { font-size: 2.4rem; opacity: .15; }
.kpi-progress { height: 6px; border-radius: 3px; }

/* Tabla */
.productivy-table thead th {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    vertical-align: middle;
}
.productivy-table tbody td { vertical-align: middle; }
.productivy-table tbody tr { transition: background .15s; }

/* Día pills */
.day-pill {
    width: 26px; height: 26px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 700;
    cursor: default;
    transition: transform .15s;
}
.day-pill:hover { transform: scale(1.15); }
.day-pill.estado-en-tiempo  { background: #198754; color: #fff; }
.day-pill.estado-con-atraso { background: #fd7e14; color: #fff; }
.day-pill.estado-no-enviado { background: #dc3545; color: #fff; }
.day-pill.estado-futuro     { background: rgba(120,120,120,.15); color: rgba(120,120,120,.5); border: 1.5px dashed rgba(120,120,120,.3); }

/* Badge enviado/pendiente */
.badge-enviado  { background: rgba(25,135,84,.12); color: #198754; border: 1px solid rgba(25,135,84,.25); font-weight: 600; }
.badge-pendiente { background: rgba(220,53,69,.1); color: #dc3545; border: 1px solid rgba(220,53,69,.2); font-weight: 600; }

/* Productivy badge */
.pvy-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px;
    font-size: .95rem; font-weight: 700; letter-spacing: .02em;
}
.pvy-100  { background: rgba(25,135,84,.12); color: #198754; }
.pvy-high { background: rgba(13,202,240,.12); color: #0dcaf0; }
.pvy-mid  { background: rgba(255,193,7,.15); color: #856404; }
.pvy-low  { background: rgba(220,53,69,.12); color: #dc3545; }
.pvy-zero { background: rgba(108,117,125,.1); color: #6c757d; }

/* Rank badge */
.rank-badge {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700;
    background: rgba(108,117,125,.1);
    color: var(--bs-body-color);
}
.rank-1 { background: #ffd700; color: #6d5a00; }
.rank-2 { background: #c0c0c0; color: #555; }
.rank-3 { background: #cd7f32; color: #fff; }

/* Search */
#searchSede { max-width: 260px; }

/* Responsive pills */
@media (max-width: 768px) {
    .day-pill { width: 22px; height: 22px; font-size: .58rem; }
    .pvy-badge { padding: 4px 10px; font-size: .82rem; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="page-header">
        <div class="row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="px-4 py-3 card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

                            
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 mdi mdi-chart-bar" style="color:#7367f0"></i>Productivy Sedes
                                </h4>
                                <p class="mt-1 mb-0 text-muted small">
                                    Cumplimiento semanal: Depósitos · Caja Chica · Comentarios
                                </p>
                            </div>

                            
                            <div class="d-flex align-items-center gap-3">
                                <a href="<?php echo route('productividad.productivy.index', ['semana' => $prevSemana, 'anio' => $prevAnio]); ?>"
                                   class="text-decoration-none week-nav-btn" title="Semana anterior">
                                    <i class="mdi-chevron-left mdi"></i>
                                </a>

                                <div class="text-center">
                                    <div class="fw-bold" style="font-size:.95rem">
                                        Semana <?php echo $semana; ?>

                                        <?php if($isCurrentWeek): ?> <span class="bg-primary ms-1 badge small">Actual</span> <?php endif; ?>
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        <?php echo $weekStart->format('d M'); ?> — <?php echo $weekEnd->format('d M Y'); ?>

                                    </div>
                                </div>

                                <?php if(!$isFutureWeek): ?>
                                <a href="<?php echo route('productividad.productivy.index', ['semana' => $nextSemana, 'anio' => $nextAnio]); ?>"
                                   class="text-decoration-none week-nav-btn" title="Semana siguiente">
                                    <i class="mdi-chevron-right mdi"></i>
                                </a>
                                <?php else: ?>
                                <span class="week-nav-btn" style="opacity:.3;cursor:not-allowed">
                                    <i class="mdi-chevron-right mdi"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        
        <?php
            $kpiColor = fn($v) => $v >= 90 ? '#198754' : ($v >= 70 ? '#0dcaf0' : ($v >= 50 ? '#fd7e14' : '#dc3545'));
        ?>
        <div class="mb-4 row g-3">

            
            <?php $c = $kpiColor($avgDepositos); ?>
            <div class="col-lg-3 col-md-6">
                <div class="shadow-sm border-0 h-100 card kpi-stat-card" style="border-left:4px solid <?php echo $c; ?> !important">
                    <div class="position-relative p-4 overflow-hidden card-body">
                        <div class="top-0 position-absolute p-3 end-0 kpi-icon">
                            <i class="mdi mdi-bank-transfer" style="color:<?php echo $c; ?>"></i>
                        </div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">KPI Depósitos</p>
                        <h2 class="mb-0 fw-bold" style="color:<?php echo $c; ?>"><?php echo $avgDepositos; ?>%</h2>
                        <div class="mt-2 kpi-progress" style="background:rgba(0,0,0,.07)">
                            <div class="kpi-progress" style="background:<?php echo $c; ?>;width:<?php echo $avgDepositos; ?>%"></div>
                        </div>
                        <p class="mt-2 mb-0 text-muted small">Promedio KPI · <?php echo $ccEnviadas; ?> enviaron hoy</p>
                    </div>
                </div>
            </div>

            
            <?php $c = $kpiColor($avgCajaChica); ?>
            <div class="col-lg-3 col-md-6">
                <div class="shadow-sm border-0 h-100 card kpi-stat-card" style="border-left:4px solid <?php echo $c; ?> !important">
                    <div class="position-relative p-4 overflow-hidden card-body">
                        <div class="top-0 position-absolute p-3 end-0 kpi-icon">
                            <i class="mdi mdi-cash-multiple" style="color:<?php echo $c; ?>"></i>
                        </div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">KPI Caja Chica</p>
                        <h2 class="mb-0 fw-bold" style="color:<?php echo $c; ?>"><?php echo $avgCajaChica; ?>%</h2>
                        <div class="mt-2 kpi-progress" style="background:rgba(0,0,0,.07)">
                            <div class="kpi-progress" style="background:<?php echo $c; ?>;width:<?php echo $avgCajaChica; ?>%"></div>
                        </div>
                        <p class="mt-2 mb-0 text-muted small"><?php echo $ccEnviadas; ?>/<?php echo $totalSedes; ?> sedes enviaron</p>
                    </div>
                </div>
            </div>

            
            <?php $c = $kpiColor($avgComentarios); ?>
            <div class="col-lg-3 col-md-6">
                <div class="shadow-sm border-0 h-100 card kpi-stat-card" style="border-left:4px solid <?php echo $c; ?> !important">
                    <div class="position-relative p-4 overflow-hidden card-body">
                        <div class="top-0 position-absolute p-3 end-0 kpi-icon">
                            <i class="mdi-comment-text-multiple mdi" style="color:<?php echo $c; ?>"></i>
                        </div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">KPI Comentarios</p>
                        <h2 class="mb-0 fw-bold" style="color:<?php echo $c; ?>"><?php echo $avgComentarios; ?>%</h2>
                        <div class="mt-2 kpi-progress" style="background:rgba(0,0,0,.07)">
                            <div class="kpi-progress" style="background:<?php echo $c; ?>;width:<?php echo $avgComentarios; ?>%"></div>
                        </div>
                        <p class="mt-2 mb-0 text-muted small"><?php echo $comEnviados; ?>/<?php echo $totalSedes; ?> sedes enviaron</p>
                    </div>
                </div>
            </div>

            
            <?php $c = $kpiColor($avgProductivy); ?>
            <div class="col-lg-3 col-md-6">
                <div class="shadow-sm border-0 h-100 card kpi-stat-card" style="border-left:4px solid <?php echo $c; ?> !important">
                    <div class="position-relative p-4 overflow-hidden card-body">
                        <div class="top-0 position-absolute p-3 end-0 kpi-icon">
                            <i class="mdi mdi-trophy" style="color:<?php echo $c; ?>"></i>
                        </div>
                        <p class="mb-1 text-muted text-uppercase small fw-semibold">Productivy General</p>
                        <h2 class="mb-0 fw-bold" style="color:<?php echo $c; ?>"><?php echo $avgProductivy; ?>%</h2>
                        <div class="mt-2 kpi-progress" style="background:rgba(0,0,0,.07)">
                            <div class="kpi-progress" style="background:<?php echo $c; ?>;width:<?php echo $avgProductivy; ?>%;transition:width .6s ease"></div>
                        </div>
                        <p class="mt-2 mb-0 text-muted small">Promedio (Dep + CC + Com) / 3</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="shadow-sm border-0 card">
            <div class="px-4 pt-4 pb-2 card-body">

                
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi-table me-1 mdi"></i>Detalle por Sede
                        </h6>
                        <p class="mb-0 text-muted small"><?php echo $totalSedes; ?> sedes · Productivy = promedio KPI (Dep + CC + Com) / 3</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        
                        <div class="d-flex align-items-center gap-2 me-2 text-muted small">
                            <span class="day-pill estado-en-tiempo" style="width:18px;height:18px;font-size:.5rem">L</span> En tiempo
                            <span class="day-pill estado-con-atraso" style="width:18px;height:18px;font-size:.5rem">M</span> Con atraso
                            <span class="day-pill estado-no-enviado" style="width:18px;height:18px;font-size:.5rem">M</span> No enviado
                            <span class="day-pill estado-futuro" style="width:18px;height:18px;font-size:.5rem">J</span> Pendiente
                        </div>
                        <input type="text" id="searchSede" class="form-control form-control-sm"
                               placeholder="&#xf002; Buscar sede..." style="font-family:inherit">
                    </div>
                </div>

                
                <div class="table-responsive">
                    <table class="productivy-table table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:42px">#</th>
                                <th>Sede</th>
                                <th style="min-width:230px">
                                    Depósitos
                                    <span class="ms-1 text-muted fw-normal" style="font-size:.68rem">(L M M J V S)</span>
                                </th>
                                <th style="min-width:160px">Caja Chica</th>
                                <th style="min-width:160px">Comentarios</th>
                                <th style="min-width:130px">
                                    <i class="me-1 mdi mdi-trophy-variant" style="color:#7367f0"></i>Productivy
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <?php $__empty_1 = true; $__currentLoopData = $tableData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $rank = $i + 1;
                                $pvy  = $row['productivy'];
                                if ($pvy >= 100)    { $pvyCls = 'pvy-100';  $pvyIcon = 'mdi-trophy text-warning'; }
                                elseif ($pvy >= 70) { $pvyCls = 'pvy-high'; $pvyIcon = 'mdi-trending-up'; }
                                elseif ($pvy >= 50) { $pvyCls = 'pvy-mid';  $pvyIcon = 'mdi-minus'; }
                                elseif ($pvy > 0)   { $pvyCls = 'pvy-low';  $pvyIcon = 'mdi-trending-down'; }
                                else                { $pvyCls = 'pvy-zero'; $pvyIcon = 'mdi-close-circle-outline'; }
                                $rankCls = $rank <= 3 ? "rank-{$rank}" : '';

                                // Colores KPI por columna — igual que los cards (sin caso gris)
                                $kpiCls    = fn($v) => $v >= 90 ? 'text-success' : ($v >= 70 ? 'text-info' : ($v >= 50 ? 'text-warning' : 'text-danger'));
                                $kpiBg     = fn($v) => $v >= 90 ? 'rgba(25,135,84,.13)' : ($v >= 70 ? 'rgba(13,202,240,.13)' : ($v >= 50 ? 'rgba(255,193,7,.18)' : 'rgba(220,53,69,.12)'));
                                $kpiBorder = fn($v) => $v >= 90 ? 'rgba(25,135,84,.35)' : ($v >= 70 ? 'rgba(13,202,240,.3)' : ($v >= 50 ? 'rgba(255,193,7,.4)' : 'rgba(220,53,69,.3))'));
                                $kpiBar    = fn($v) => $v >= 90 ? '#198754' : ($v >= 70 ? '#0dcaf0' : ($v >= 50 ? '#fd7e14' : '#dc3545'));
                            ?>
                            <tr data-sede="<?php echo strtolower($row['sede']); ?>">

                                
                                <td><span class="rank-badge <?php echo $rankCls; ?>"><?php echo $rank; ?></span></td>

                                
                                <td><span class="fw-semibold" style="font-size:.9rem"><?php echo $row['sede']; ?></span></td>

                                
                                <td>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                                        <?php $letras = ['L','M','M','J','V','S']; ?>
                                        <?php $__currentLoopData = $row['dias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $di => $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="day-pill estado-<?php echo $dia['estado']; ?>"
                                              title="<?php echo $dia['estado'] === 'futuro' ? 'Día no llegado' : ($dia['estado'] === 'en_tiempo' ? 'En tiempo · KPI '.$dia['kpi'].'%' : ($dia['estado'] === 'con_atraso' ? 'Con atraso · KPI '.$dia['kpi'].'%' : 'No enviado · 0%')); ?>">
                                            <?php echo $letras[$di]; ?>

                                        </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <span class="px-3 py-2 rounded-pill badge fw-bold"
                                          style="background:<?php echo $kpiBg($row['dep_kpi']); ?>;color:inherit;border:1px solid <?php echo $kpiBorder($row['dep_kpi']); ?>">
                                        <span class="<?php echo $kpiCls($row['dep_kpi']); ?> fw-bold"><?php echo $row['dep_kpi']; ?>%</span>
                                        <span class="ms-1 text-muted fw-normal" style="font-size:.75em"><?php echo $row['depositos']; ?>/6 días</span>
                                    </span>
                                </td>

                                
                                <td>
                                    <span class="px-3 py-2 rounded-pill badge fw-bold"
                                          style="background:<?php echo $kpiBg($row['cc_kpi']); ?>;color:inherit;border:1px solid <?php echo $kpiBorder($row['cc_kpi']); ?>">
                                        <i class="mdi <?php echo $row['cc_enviada'] ? 'mdi-check-circle' : 'mdi-clock-alert-outline'; ?> me-1 <?php echo $kpiCls($row['cc_kpi']); ?>"></i>
                                        <span class="<?php echo $kpiCls($row['cc_kpi']); ?> fw-bold"><?php echo $row['cc_kpi']; ?>%</span>
                                    </span>
                                    <?php if($row['cc_fecha']): ?>
                                    <div class="mt-1 text-muted" style="font-size:.72rem"><?php echo $row['cc_fecha']; ?></div>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <span class="px-3 py-2 rounded-pill badge fw-bold"
                                          style="background:<?php echo $kpiBg($row['com_kpi']); ?>;color:inherit;border:1px solid <?php echo $kpiBorder($row['com_kpi']); ?>">
                                        <i class="mdi <?php echo $row['com_enviados'] ? 'mdi-check-circle' : 'mdi-clock-alert-outline'; ?> me-1 <?php echo $kpiCls($row['com_kpi']); ?>"></i>
                                        <span class="<?php echo $kpiCls($row['com_kpi']); ?> fw-bold"><?php echo $row['com_kpi']; ?>%</span>
                                    </span>
                                    <?php if($row['com_fecha']): ?>
                                    <div class="mt-1 text-muted" style="font-size:.72rem"><?php echo $row['com_fecha']; ?></div>
                                    <?php endif; ?>
                                </td>

                                
                                <td>
                                    <span class="pvy-badge <?php echo $pvyCls; ?>">
                                        <i class="mdi <?php echo $pvyIcon; ?>"></i><?php echo $pvy; ?>%
                                    </span>
                                </td>

                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="py-5 text-muted text-center">
                                    <i class="d-block mb-2 mdi-information-outline mdi fs-4"></i>
                                    No hay datos para esta semana
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-3 pb-1 border-top">
                    <span class="text-muted small" id="filteredCount">
                        Mostrando <?php echo $totalSedes; ?> de <?php echo $totalSedes; ?> sedes
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const searchInput = document.getElementById('searchSede');
    const rows        = document.querySelectorAll('#tablaBody tr[data-sede]');
    const counter     = document.getElementById('filteredCount');
    const total       = rows.length;

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const match = row.dataset.sede.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        counter.textContent = `Mostrando ${visible} de ${total} sedes`;
    });

    // Tooltips
    const pills = document.querySelectorAll('.day-pill[title]');
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        pills.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top' }));
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/productividad/productivy/index.blade.php ENDPATH**/ ?>