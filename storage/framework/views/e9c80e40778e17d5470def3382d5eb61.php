<?php $__env->startSection('title', 'Ventas por Sedes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            <!-- Header con Selectores -->
                            <div class="mb-4 row">
                                <div class="col-lg-8">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="me-2 text-primary mdi mdi-chart-line"></i>
                                        Ventas por Sedes
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <i class="me-2 mdi mdi-store"></i>
                                        Monitoreo de todas las sedes
                                    </p>
                                </div>

                                <div class="col-lg-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="mb-1 text-muted form-label small">Año</label>
                                            <select id="anioSelect" class="form-select">
                                                <?php $__currentLoopData = $aniosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $anio; ?>"
                                                        <?php echo $anio == $anioActual ? 'selected' : ''; ?>>
                                                        <?php echo $anio; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="mb-1 text-muted form-label small">Mes</label>
                                            <select id="mesSelect" class="form-select">
                                                <option value="Enero" <?php echo $mesActual == 'Enero' ? 'selected' : ''; ?>>
                                                    Enero</option>
                                                <option value="Febrero" <?php echo $mesActual == 'Febrero' ? 'selected' : ''; ?>>
                                                    Febrero</option>
                                                <option value="Marzo" <?php echo $mesActual == 'Marzo' ? 'selected' : ''; ?>>
                                                    Marzo</option>
                                                <option value="Abril" <?php echo $mesActual == 'Abril' ? 'selected' : ''; ?>>
                                                    Abril</option>
                                                <option value="Mayo" <?php echo $mesActual == 'Mayo' ? 'selected' : ''; ?>>
                                                    Mayo</option>
                                                <option value="Junio" <?php echo $mesActual == 'Junio' ? 'selected' : ''; ?>>
                                                    Junio</option>
                                                <option value="Julio" <?php echo $mesActual == 'Julio' ? 'selected' : ''; ?>>
                                                    Julio</option>
                                                <option value="Agosto" <?php echo $mesActual == 'Agosto' ? 'selected' : ''; ?>>
                                                    Agosto</option>
                                                <option value="Septiembre"
                                                    <?php echo $mesActual == 'Septiembre' || $mesActual == 'Setiembre' ? 'selected' : ''; ?>>
                                                    Septiembre</option>
                                                <option value="Octubre" <?php echo $mesActual == 'Octubre' ? 'selected' : ''; ?>>
                                                    Octubre</option>
                                                <option value="Noviembre"
                                                    <?php echo $mesActual == 'Noviembre' ? 'selected' : ''; ?>>Noviembre</option>
                                                <option value="Diciembre"
                                                    <?php echo $mesActual == 'Diciembre' ? 'selected' : ''; ?>>Diciembre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="fw-bold" style="color: #c8a000;">
                                        <i class="me-1 mdi mdi-crown"></i>
                                        VENTA TOTAL TRIMAX
                                        <small class="ms-2 text-muted fw-normal" style="font-size: 0.75rem;">(Lunas +
                                            Monturas)</small>
                                    </h6>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-trimax">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-cart mdi-36px" style="color: #b8860b;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">Venta Total</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="trimaxVentaGeneral">
                                                        S/
                                                        <?php echo number_format($totalesTrimax['venta_general'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-trimax">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-currency-usd mdi-36px" style="color: #b8860b;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">Venta Proyectada</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="trimaxVentaProy">
                                                        S/
                                                        <?php echo number_format($totalesTrimax['venta_proyectada'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-trimax">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-target mdi-36px" style="color: #b8860b;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">PPTO del Mes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="trimaxCuota">
                                                        S/ <?php echo number_format($totalesTrimax['cuota'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-trimax" id="cardTrimaxCum">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-chart-line mdi-36px" style="color: #b8860b;"
                                                        id="iconTrimaxCum"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="trimaxCumplimiento">
                                                        <?php echo number_format($totalesTrimax['cumplimiento_cuota'], 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" id="progressTrimax"
                                                    style="width: <?php echo min($totalesTrimax['cumplimiento_cuota'], 100); ?>%; background-color: #ffd700;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row" id="cardsGlobales">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="text-muted">
                                        <i class="me-1 mdi mdi-store"></i> Ventas Generales (Lunas) —
                                        <span id="periodoGeneral"><?php echo $mesActual; ?> <?php echo $anioActual; ?></span>
                                    </h6>
                                </div>

                                <?php
                                    $totalVentas = collect($todasLasSedes)->sum('venta_general');
                                    $ventaProyectada = collect($todasLasSedes)->sum('venta_proyectada');
                                    $totalCuotas = collect($todasLasSedes)->sum('cuota');
                                    $cumplimientoPromedio =
                                        $totalCuotas > 0 ? ($ventaProyectada / $totalCuotas) * 100 : 0;
                                ?>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-cart mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta General</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentas">
                                                        S/ <?php echo number_format($totalVentas, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-currency-usd mdi-36px" style="color: #4747A1;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Proyectada</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="ventaProyectada">
                                                        S/ <?php echo number_format($ventaProyectada, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-target mdi-36px" style="color: #7978E9"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">PPTO General</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCuotas">
                                                        S/ <?php echo number_format($totalCuotas, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger" id="cardGeneralCum">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-chart-line mdi-36px" style="color: #F3797E"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cumplimientoPromedio">
                                                        <?php echo number_format($cumplimientoPromedio, 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" id="progressGeneral"
                                                    style="width: <?php echo min($cumplimientoPromedio, 100); ?>%; background-color: #7dd3fc;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row" id="cardsDigitales">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="text-muted">
                                        <i class="me-1 mdi mdi-monitor"></i> Ventas Digitales —
                                        <span id="periodoDigital"><?php echo $mesActual; ?> <?php echo $anioActual; ?></span>
                                    </h6>
                                </div>

                                <?php
                                    $totalVentaDigital = collect($todasLasSedes)->sum('venta_digital');
                                    $totalVentaProyDigital = collect($todasLasSedes)->sum('venta_proy_digital');
                                    $totalCuotaDigital = collect($todasLasSedes)->sum('cuota_digital');
                                    $cumDigital =
                                        $totalCuotaDigital > 0
                                            ? ($totalVentaProyDigital / $totalCuotaDigital) * 100
                                            : 0;
                                ?>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-monitor-dashboard mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentaDigital">
                                                        S/ <?php echo number_format($totalVentaDigital, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-currency-usd mdi-36px" style="color: #4747A1;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Proy. Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalVentaProyDigital">
                                                        S/ <?php echo number_format($totalVentaProyDigital, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-target mdi-36px" style="color: #7978E9"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">PPTO Digital</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCuotaDigital">
                                                        S/ <?php echo number_format($totalCuotaDigital, 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger" id="cardDigitalCum">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-chart-line mdi-36px" style="color: #F3797E"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="totalCumCuotaDigital">
                                                        <?php echo number_format($cumDigital, 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" id="progressDigital"
                                                    style="width: <?php echo min($cumDigital, 100); ?>%; background-color: #7dd3fc;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row" id="cardsMonturas">
                                <div class="mb-2 col-lg-12">
                                    <h6 class="text-muted">
                                        <i class="me-1 mdi mdi-glasses"></i> Monturas —
                                        <span id="periodoMonturas"><?php echo $mesActual; ?> <?php echo $anioActual; ?></span>
                                    </h6>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-monturas">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-cart mdi-36px" style="color: #0f766e;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">Venta General</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="monturasVentaGeneral">
                                                        S/
                                                        <?php echo number_format($datosMonturas['venta_general'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-monturas">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-currency-usd mdi-36px" style="color: #0f766e;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">Venta Proyectada</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="monturasVentaProy">
                                                        S/
                                                        <?php echo number_format($datosMonturas['venta_proyectada'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-monturas">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-target mdi-36px" style="color: #0f766e;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">PPTO del Mes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="monturasCuota">
                                                        S/ <?php echo number_format($datosMonturas['cuota'], 0, '.', ','); ?>

                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-monturas" id="cardMonturasCum">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="mdi mdi-chart-line mdi-36px" style="color: #0f766e;"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small fw-semibold">% Cumplimiento</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="monturasCumplimiento">
                                                        <?php echo number_format($datosMonturas['cumplimiento_cuota'], 2); ?>%
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" id="progressMonturas"
                                                    style="width: <?php echo min($datosMonturas['cumplimiento_cuota'], 100); ?>%;
                                                    background-color: <?php echo $datosMonturas['cumplimiento_cuota'] >= 100 ? '#6ee7b7' : ($datosMonturas['cumplimiento_cuota'] >= 70 ? '#fcd34d' : '#fca5a5'); ?>;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos -->
                            <div class="mb-4 row">
                                <div class="mb-3 col-lg-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-primary mdi mdi-chart-bar"></i>
                                                Ventas Consolidadas Mensuales - <span
                                                    id="anioConsolidado"><?php echo $anioActual; ?></span>
                                            </h4>
                                            <canvas id="consolidadoChart" height="80"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-warning mdi mdi-trophy"></i>
                                                Top 5 Sedes
                                            </h4>
                                            <canvas id="topSedesChart" height="160"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráfico Comparación Anual -->
                            <div class="mb-4 row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-info mdi mdi-chart-areaspline"></i>
                                                Comparación de <?php echo $mesActual; ?> - Histórico de Años (Todas las Sedes)
                                            </h4>
                                            <canvas id="comparacionAnualChart" height="60"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de Todas las Sedes -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <h4 class="mb-0 card-title">
                                                    <i class="mdi-table me-2 text-info mdi"></i>
                                                    Detalle por Sede - <span id="periodoTabla"><?php echo $mesActual; ?>

                                                        <?php echo $anioActual; ?></span>
                                                </h4>
                                                <input type="text" id="buscarSede"
                                                    class="form-control form-control-sm" placeholder="Buscar sede...">
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tablaVentas">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Sede</th>
                                                            <th>Venta General</th>
                                                            <th>Venta Proy.</th>
                                                            <th>PPTO</th>
                                                            <th>Diferencia</th>
                                                            <th>% Cumplimiento</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaBody">
                                                        <?php $__empty_1 = true; $__currentLoopData = $todasLasSedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr data-sede="<?php echo $sede['sede']; ?>">
                                                                <td class="fw-bold"><?php echo $index + 1; ?></td>
                                                                <td class="fw-semibold"><?php echo $sede['sede']; ?></td>
                                                                <td>S/
                                                                    <?php echo number_format($sede['venta_general'], 0, '.', ','); ?>

                                                                </td>
                                                                <td>S/ <?php echo number_format($sede['venta_proyectada'], 0, '.', ','); ?></td>
                                                                <td>S/ <?php echo number_format($sede['cuota'], 0, '.', ','); ?>

                                                                </td>
                                                                <td
                                                                    class="<?php echo $sede['diferencia'] >= 0 ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                                    <?php echo $sede['diferencia'] >= 0 ? '+' : ''; ?>S/
                                                                    <?php echo number_format($sede['diferencia'], 0, '.', ','); ?>

                                                                </td>
                                                                <td
                                                                    class="fw-bold
                                                                    <?php if($sede['cumplimiento_cuota'] >= 100): ?> text-success
                                                                    <?php elseif($sede['cumplimiento_cuota'] >= 70): ?> text-warning
                                                                    <?php else: ?> text-danger <?php endif; ?>">
                                                                    <?php echo number_format($sede['cumplimiento_cuota'], 2); ?>%
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php if($sede['cumplimiento_cuota'] >= 100): ?>
                                                                        <span class="badge badge-success"><i
                                                                                class="me-1 mdi mdi-check-circle"></i>Cumplido</span>
                                                                    <?php elseif($sede['cumplimiento_cuota'] >= 70): ?>
                                                                        <span class="badge badge-warning"><i
                                                                                class="me-1 mdi mdi-alert"></i>En
                                                                            Progreso</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-danger"><i
                                                                                class="me-1 mdi mdi-close-circle"></i>Bajo</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <tr>
                                                                <td colspan="7" class="py-4 text-muted text-center">
                                                                    <i class="d-block mb-3 mdi mdi-inbox mdi-48px"></i>
                                                                    No hay datos disponibles
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
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

    <style>
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        #buscarSede {
            max-width: 200px;
        }

        .card-trimax {
            background: linear-gradient(135deg, #92710a, #c8a000, #a07800);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(184, 134, 11, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-trimax:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(184, 134, 11, 0.5);
        }

        .card-monturas {
            background: linear-gradient(135deg, #0f766e, #14b8a6, #0d9488);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(15, 118, 110, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-monturas:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(15, 118, 110, 0.5);
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.3);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let consolidadoChart, topSedesChart, comparacionAnualChart;

        let datosConsolidados = {
            meses: <?php echo json_encode($datosConsolidados['meses'], 15, 512) ?>,
            ventas: <?php echo json_encode($datosConsolidados['ventas'], 15, 512) ?>,
            cuotas: <?php echo json_encode($datosConsolidados['cuotas'], 15, 512) ?>
        };

        let comparacionAnual = {
            anios: <?php echo json_encode($comparacionAnual['anios'], 15, 512) ?>,
            ventas: <?php echo json_encode($comparacionAnual['ventas'], 15, 512) ?>
        };

        let sedesActuales = <?php echo json_encode($todasLasSedes, 15, 512) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();
            document.getElementById('anioSelect').addEventListener('change', actualizarDatos);
            document.getElementById('mesSelect').addEventListener('change', actualizarDatos);
            document.getElementById('buscarSede').addEventListener('input', filtrarTabla);
        });

        async function actualizarDatos() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            try {
                const response = await fetch(`<?php echo route('comercial.ventas.sedes.data'); ?>?anio=${anio}&mes=${mes}`);
                const data = await response.json();

                sedesActuales = data.sedes;

                // ── TRIMAX ─────────────────────────────────────────────────
                const t = data.totales_trimax;
                document.getElementById('trimaxVentaGeneral').textContent = 'S/ ' + formatNumber(t.venta_general);
                document.getElementById('trimaxVentaProy').textContent = 'S/ ' + formatNumber(t.venta_proyectada);
                document.getElementById('trimaxCuota').textContent = 'S/ ' + formatNumber(t.cuota);
                document.getElementById('trimaxCumplimiento').textContent = t.cumplimiento_cuota.toFixed(2) + '%';
                document.getElementById('progressTrimax').style.width = Math.min(t.cumplimiento_cuota, 100) + '%';

                // ── VENTAS GENERALES + DIGITALES ───────────────────────────
                actualizarCardsGlobales(data.sedes);

                // ── MONTURAS ───────────────────────────────────────────────
                const m = data.monturas;
                document.getElementById('monturasVentaGeneral').textContent = 'S/ ' + formatNumber(m.venta_general);
                document.getElementById('monturasVentaProy').textContent = 'S/ ' + formatNumber(m.venta_proyectada);
                document.getElementById('monturasCuota').textContent = 'S/ ' + formatNumber(m.cuota);
                document.getElementById('monturasCumplimiento').textContent = m.cumplimiento_cuota.toFixed(2) + '%';
                const cum = m.cumplimiento_cuota;
                const colorMonturas = cum >= 100 ? '#6ee7b7' : (cum >= 70 ? '#fcd34d' : '#fca5a5');
                const progressMonturas = document.getElementById('progressMonturas');
                progressMonturas.style.width = Math.min(cum, 100) + '%';
                progressMonturas.style.backgroundColor = colorMonturas;

                // ── Gráficos ───────────────────────────────────────────────
                datosConsolidados = data.consolidado;
                actualizarGraficoConsolidado();

                comparacionAnual = data.comparacion;
                actualizarGraficoComparacionAnual();

                actualizarGraficoTopSedes(data.sedes);
                actualizarTabla(data.sedes);

                // ── Títulos ────────────────────────────────────────────────
                const periodo = mes + ' ' + anio;
                document.getElementById('anioConsolidado').textContent = anio;
                document.getElementById('periodoTabla').textContent = periodo;
                document.getElementById('periodoGeneral').textContent = periodo;
                document.getElementById('periodoDigital').textContent = periodo;
                document.getElementById('periodoMonturas').textContent = periodo;

            } catch (error) {
                console.error('Error al actualizar datos:', error);
            }
        }

        function actualizarCardsGlobales(sedes) {
            // ── Generales ──────────────────────────────────────────────────
            const totalVentas = sedes.reduce((s, x) => s + x.venta_general, 0);
            const totalVentaProy = sedes.reduce((s, x) => s + x.venta_proyectada, 0);
            const totalCuotas = sedes.reduce((s, x) => s + x.cuota, 0);
            // ✅ % Cumplimiento = Venta Proyectada / PPTO General
            const cumplimiento = totalCuotas > 0 ? (totalVentaProy / totalCuotas) * 100 : 0;

            document.getElementById('totalVentas').textContent = 'S/ ' + formatNumber(totalVentas);
            document.getElementById('ventaProyectada').textContent = 'S/ ' + formatNumber(totalVentaProy);
            document.getElementById('totalCuotas').textContent = 'S/ ' + formatNumber(totalCuotas);
            document.getElementById('cumplimientoPromedio').textContent = cumplimiento.toFixed(2) + '%';

            const progressGeneral = document.getElementById('progressGeneral');
            if (progressGeneral) progressGeneral.style.width = Math.min(cumplimiento, 100) + '%';

            // ── Digitales ──────────────────────────────────────────────────
            const totalVentaDigital = sedes.reduce((s, x) => s + (x.venta_digital || 0), 0);
            const totalVentaProyDigital = sedes.reduce((s, x) => s + (x.venta_proy_digital || 0), 0);
            const totalCuotaDigital = sedes.reduce((s, x) => s + (x.cuota_digital || 0), 0);
            // ✅ % Cumplimiento Digital = Venta Proy Digital / PPTO Digital
            const cumDigital = totalCuotaDigital > 0 ? (totalVentaProyDigital / totalCuotaDigital) * 100 : 0;

            document.getElementById('totalVentaDigital').textContent = 'S/ ' + formatNumber(totalVentaDigital);
            document.getElementById('totalVentaProyDigital').textContent = 'S/ ' + formatNumber(totalVentaProyDigital);
            document.getElementById('totalCuotaDigital').textContent = 'S/ ' + formatNumber(totalCuotaDigital);
            document.getElementById('totalCumCuotaDigital').textContent = cumDigital.toFixed(2) + '%';

            const progressDigital = document.getElementById('progressDigital');
            if (progressDigital) progressDigital.style.width = Math.min(cumDigital, 100) + '%';
        }

        function formatNumber(num) {
            return Math.round(num).toLocaleString('es-PE');
        }

        function inicializarGraficos() {
            const ctxConsolidado = document.getElementById('consolidadoChart').getContext('2d');
            consolidadoChart = new Chart(ctxConsolidado, {
                type: 'bar',
                data: {
                    labels: datosConsolidados.meses,
                    datasets: [{
                        label: 'Ventas',
                        data: datosConsolidados.ventas,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }, {
                        label: 'PPTO',
                        data: datosConsolidados.cuotas,
                        backgroundColor: 'rgba(255, 206, 86, 0.7)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': S/ ' + ctx.parsed.y.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + (val / 1000000).toFixed(1) + 'M'
                            }
                        }
                    }
                }
            });

            const top5 = sedesActuales.slice(0, 5);
            const ctxTop = document.getElementById('topSedesChart').getContext('2d');
            topSedesChart = new Chart(ctxTop, {
                type: 'doughnut',
                data: {
                    labels: top5.map(s => s.sede),
                    datasets: [{
                        data: top5.map(s => s.cumplimiento_cuota),
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': ' + ctx.parsed.toFixed(2) + '%'
                            }
                        }
                    }
                }
            });

            const ctxAnual = document.getElementById('comparacionAnualChart').getContext('2d');
            comparacionAnualChart = new Chart(ctxAnual, {
                type: 'line',
                data: {
                    labels: comparacionAnual.anios,
                    datasets: [{
                        label: 'Venta Total del Mes',
                        data: comparacionAnual.ventas,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 6,
                        pointBackgroundColor: 'rgba(153, 102, 255, 1)',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Total: S/ ' + ctx.parsed.y.toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + (val / 1000000).toFixed(1) + 'M'
                            }
                        }
                    }
                }
            });
        }

        function actualizarGraficoConsolidado() {
            consolidadoChart.data.labels = datosConsolidados.meses;
            consolidadoChart.data.datasets[0].data = datosConsolidados.ventas;
            consolidadoChart.data.datasets[1].data = datosConsolidados.cuotas;
            consolidadoChart.update();
        }

        function actualizarGraficoTopSedes(sedes) {
            const top5 = sedes.slice(0, 5);
            topSedesChart.data.labels = top5.map(s => s.sede);
            topSedesChart.data.datasets[0].data = top5.map(s => s.cumplimiento_cuota);
            topSedesChart.update();
        }

        function actualizarGraficoComparacionAnual() {
            comparacionAnualChart.data.labels = comparacionAnual.anios;
            comparacionAnualChart.data.datasets[0].data = comparacionAnual.ventas;
            comparacionAnualChart.update();
        }

        function actualizarTabla(sedes) {
            const tbody = document.getElementById('tablaBody');
            const diferencia = sede.venta_proyectada - sede.cuota;

            if (sedes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="py-4 text-muted text-center">
                            <i class="d-block mb-3 mdi mdi-inbox mdi-48px"></i>
                            No hay datos disponibles
                        </td>
                    </tr>`;
                return;
            }

            let html = '';
            sedes.forEach((sede, index) => {
                const cum = sede.cumplimiento_cuota;
                let badgeClass = 'badge-danger',
                    badgeIcon = 'mdi-close-circle',
                    badgeText = 'Bajo',
                    textClass = 'text-danger';

                if (cum >= 100) {
                    badgeClass = 'badge-success';
                    badgeIcon = 'mdi-check-circle';
                    badgeText = 'Cumplido';
                    textClass = 'text-success';
                } else if (cum >= 70) {
                    badgeClass = 'badge-warning';
                    badgeIcon = 'mdi-alert';
                    badgeText = 'En Progreso';
                    textClass = 'text-warning';
                }

                const diferencia = sede.venta_general - sede.cuota;
                const diferenciaClass = diferencia >= 0 ? 'text-success' : 'text-danger';
                const diferenciaSigno = diferencia >= 0 ? '+' : '';

                html += `
                    <tr data-sede="${sede.sede}">
                        <td class="fw-bold">${index + 1}</td>
                        <td class="fw-semibold">${sede.sede}</td>
                        <td>S/ ${formatNumber(sede.venta_general)}</td>
                        <td>S/ ${formatNumber(sede.venta_proyectada)}</td>
                        <td>S/ ${formatNumber(sede.cuota)}</td>
                        <td class="${diferenciaClass} fw-bold">${diferenciaSigno}S/ ${formatNumber(diferencia)}</td>
                        <td class="fw-bold ${textClass}">${cum.toFixed(2)}%</td>
                        <td class="text-center">
                            <span class="badge ${badgeClass}">
                                <i class="mdi ${badgeIcon} me-1"></i>${badgeText}
                            </span>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html;
        }

        function filtrarTabla() {
            const filtro = document.getElementById('buscarSede').value.toUpperCase();
            document.querySelectorAll('#tablaBody tr[data-sede]').forEach(fila => {
                fila.style.display = fila.getAttribute('data-sede').includes(filtro) ? '' : 'none';
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/ventas-sedes.blade.php ENDPATH**/ ?>