<?php $__env->startSection('title', 'Caja Chica Sedes'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="me-2 text-success mdi mdi-cash-multiple"></i>Caja Chica Sedes
                            </h4>
                            <p class="mb-0 text-muted small">Reporte semanal — límite: sábado 11:59 PM</p>
                        </div>
                        <div class="text-end">
                            <span class="bg-success badge fs-6">Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        
        <div class="mb-4 row">
            
            <div class="mb-3 col-lg-4 col-md-6">
                <div class="shadow-sm border-0 h-100 card" id="card-countdown">
                    <div class="py-4 text-center card-body">
                        <div class="mb-2">
                            <i class="mdi-timer-outline mdi" style="font-size:2.5rem;" id="countdown-icon"></i>
                        </div>
                        <h6 class="mb-1 text-muted text-uppercase fw-bold small">Tiempo restante</h6>
                        <div id="countdown-display" class="mb-1 fw-bold" style="font-size:2rem;font-family:'Courier New',monospace;letter-spacing:2px;">--:--:--</div>
                        <div id="countdown-subtitle" class="text-muted small">Cargando...</div>
                        <hr class="my-3">
                        <div class="text-muted small">Límite: <strong>Sábado 11:59 PM</strong></div>
                    </div>
                </div>
            </div>

            
            <?php if(auth()->guard()->check()): ?>
            <?php if((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual): ?>
            <div class="mb-3 col-lg-4 col-md-6">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="py-4 card-body">
                        <h6 class="mb-3 text-muted text-uppercase fw-bold small">Estado — Semana Actual</h6>
                        <?php $enviado = !is_null($reporteSemanaActual->fecha_envio_original); ?>
                        <div class="d-flex align-items-center mb-2">
                            <i class="me-2 text-success mdi mdi-map-marker"></i>
                            <strong><?php echo $reporteSemanaActual->sede; ?></strong>
                        </div>
                        <?php if($enviado): ?>
                            <div class="mb-2 py-2 alert alert-success">
                                <i class="me-1 mdi mdi-check-circle"></i>
                                Enviado el <?php echo $reporteSemanaActual->fecha_envio_original?->setTimezone('America/Lima')->format('d/m H:i'); ?>

                            </div>
                            <div class="d-flex align-items-center">
                                <span class="me-2 fw-bold">KPI:</span>
                                <span class="badge bg-<?php echo $reporteSemanaActual->kpiColor(); ?> fs-6">
                                    <?php echo $reporteSemanaActual->kpiLabel(); ?>

                                </span>
                            </div>
                            <?php if($reporteSemanaActual->editado_tarde): ?>
                            <div class="mt-2 mb-0 py-1 alert alert-warning small">
                                <i class="me-1 mdi mdi-alert"></i>Editado con atraso — KPI ajustado
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="mb-0 py-2 alert alert-warning">
                                <i class="me-1 mdi mdi-clock-alert"></i>Pendiente de envío
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            
            <div class="mb-3 col-lg-4 col-md-12">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="py-4 card-body">
                        <h6 class="mb-3 text-muted text-uppercase fw-bold small">Escala KPI</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded"
                                 style="background:#d1fae5;border-left:4px solid #10b981;">
                                <div>
                                    <div class="text-success fw-bold small">Enviado el sábado</div>
                                    <div class="text-muted" style="font-size:11px;">Hasta las 11:59 PM</div>
                                </div>
                                <span class="bg-success badge fs-6">100%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded"
                                 style="background:#cffafe;border-left:4px solid #06b6d4;">
                                <div>
                                    <div class="fw-bold small" style="color:#0e7490;">Enviado el domingo</div>
                                    <div class="text-muted" style="font-size:11px;">1 día de atraso</div>
                                </div>
                                <span class="bg-info badge fs-6">75%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded"
                                 style="background:#fef9c3;border-left:4px solid #eab308;">
                                <div>
                                    <div class="text-warning fw-bold small">Enviado el lunes</div>
                                    <div class="text-muted" style="font-size:11px;">2 días de atraso</div>
                                </div>
                                <span class="bg-warning badge fs-6">50%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded"
                                 style="background:#fee2e2;border-left:4px solid #ef4444;">
                                <div>
                                    <div class="text-danger fw-bold small">Martes en adelante / No enviado</div>
                                    <div class="text-muted" style="font-size:11px;">3+ días de atraso</div>
                                </div>
                                <span class="bg-danger badge fs-6">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if($resumenSedes && $resumenSedes->count() > 0): ?>
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 mdi-view-dashboard-outline text-success mdi"></i>
                            Estado Sedes — Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                        </h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <span class="bg-success badge filtro-estado" data-filtro="enviado">Enviado</span>
                            <span class="bg-danger badge filtro-estado" data-filtro="pendiente">Pendiente</span>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-estado-sedes">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Responsable</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Hora Envío</th>
                                        <th class="text-center">KPI</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $resumenSedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo $fila['enviado'] ? '' : 'table-danger bg-opacity-25'; ?>" data-estado="<?php echo $fila['enviado'] ? 'enviado' : 'pendiente'; ?>">
                                        <td><strong><?php echo $fila['sede']; ?></strong></td>
                                        <td class="text-muted small"><?php echo $fila['usuario']; ?></td>
                                        <td class="text-center">
                                            <?php if($fila['enviado']): ?>
                                                <span class="bg-success badge"><i class="me-1 mdi mdi-check"></i>Enviado</span>
                                            <?php else: ?>
                                                <span class="bg-danger badge"><i class="me-1 mdi mdi-clock-alert"></i>Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $fila['fecha_envio'] ? $fila['fecha_envio'].' hrs' : '—'; ?>

                                            <?php if($fila['editado_tarde']): ?>
                                                <br><small class="text-warning"><i class="mdi mdi-pencil"></i> Editado tarde</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!is_null($fila['kpi'])): ?>
                                                <span class="badge bg-<?php echo $fila['kpi_color']; ?> fs-6"><?php echo $fila['kpi_label']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($fila['reporte_id']): ?>
                                                <button class="px-2 py-0 btn-outline-success btn btn-sm"
                                                        onclick="verReporte(<?php echo $fila['reporte_id']; ?>)" title="Ver detalle">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Sin reporte</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex gap-4 bg-white px-4 py-2 border-top text-muted card-footer small">
                        <span><i class="text-success mdi mdi-check-circle"></i> Enviados: <strong><?php echo $resumenSedes->where('enviado', true)->count(); ?></strong></span>
                        <span><i class="text-danger mdi mdi-clock-alert"></i> Pendientes: <strong><?php echo $resumenSedes->where('enviado', false)->count(); ?></strong></span>
                        <span><i class="text-success mdi mdi-chart-line"></i> KPI promedio:
                            <strong><?php $kpisValidos = $resumenSedes->whereNotNull('kpi')->pluck('kpi'); echo $kpisValidos->count() ? number_format($kpisValidos->avg(), 1).'%' : '—'; ?></strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if(auth()->guard()->check()): ?>
        <?php if((auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && $reporteSemanaActual): ?>
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-success mdi mdi-upload"></i>
                            <?php if($reporteSemanaActual->fecha_envio_original): ?> Editar Reporte <?php else: ?> Enviar Reporte <?php endif; ?>
                            — Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                        </h5>
                        <span class="bg-light border text-dark badge">Sede: <strong><?php echo $reporteSemanaActual->sede; ?></strong></span>
                    </div>
                    <div class="card-body">
                        <?php if($reporteSemanaActual->fecha_envio_original): ?>
                        
                        <form id="form-editar-reporte" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="reporte_id" value="<?php echo $reporteSemanaActual->id; ?>">

                            <?php if(count($reporteSemanaActual->archivos ?? []) > 0): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Archivos enviados</label>
                                <div class="row g-2" id="archivos-existentes">
                                    <?php $__currentLoopData = $reporteSemanaActual->archivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $archivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-4 col-sm-6" id="archivo-card-<?php echo $idx; ?>">
                                        <div class="d-flex align-items-center gap-2 bg-light p-2 border rounded">
                                            <i class="mdi mdi-<?php echo str_contains($archivo['mime'] ?? '', 'image') ? 'image' : 'file-excel'; ?> text-success fs-5"></i>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="text-truncate small fw-semibold" title="<?php echo $archivo['name']; ?>"><?php echo $archivo['name']; ?></div>
                                                <div class="text-muted" style="font-size:11px;"><?php echo number_format(($archivo['size'] ?? 0)/1024, 1); ?> KB</div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="<?php echo route('productividad.cobranza-sedes.caja-chica.download', [$reporteSemanaActual->id, $idx]); ?>"
                                                   class="px-1 py-0 btn-outline-success btn btn-sm">
                                                    <i class="mdi mdi-download"></i>
                                                </a>
                                                <button type="button" class="px-1 py-0 btn-outline-danger btn btn-sm"
                                                        onclick="marcarEliminar(<?php echo $idx; ?>, this)">
                                                    <i class="mdi-trash-can-outline mdi"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="eliminar_indices[]" id="eliminar-<?php echo $idx; ?>" value="<?php echo $idx; ?>" disabled>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Agregar archivos adicionales</label>
                                <div class="drop-zone" id="drop-zone-edit">
                                    <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                                    <p class="mt-2 mb-1 text-muted small">Arrastra Excel, PDF o fotos aquí</p>
                                    <p class="text-muted" style="font-size:11px;">XLSX, XLS, CSV, PDF, imágenes — máx. 20 MB c/u</p>
                                    <input type="file" id="archivos-edit" name="archivos[]"
                                           multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                                    <div class="d-flex justify-content-center gap-2 mt-1">
                                        <button type="button" class="btn-outline-success btn btn-sm"
                                                onclick="document.getElementById('archivos-edit').click()">
                                            <i class="me-1 mdi mdi-paperclip"></i>Seleccionar archivos
                                        </button>
                                        <button type="button" class="btn-outline-secondary btn btn-sm"
                                                onclick="abrirCamara('archivos-edit','preview-edit')">
                                            <i class="me-1 mdi mdi-camera"></i>Tomar foto
                                        </button>
                                    </div>
                                </div>
                                <div id="preview-edit" class="mt-2 row g-2"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" rows="2" class="form-control" placeholder="Opcional..."><?php echo $reporteSemanaActual->notas; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-warning fw-bold" id="btn-editar">
                                <i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios
                            </button>
                            <div id="msg-editar" class="mt-2"></div>
                        </form>
                        <?php else: ?>
                        
                        <form id="form-enviar-reporte" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Archivos del reporte <span class="text-danger">*</span></label>
                                <div class="drop-zone" id="drop-zone-nuevo">
                                    <i class="mdi-cloud-upload-outline mdi" style="font-size:2.5rem;color:#94a3b8;"></i>
                                    <p class="mt-2 mb-1 text-muted">Arrastra tu Excel, PDF o fotos aquí</p>
                                    <p class="text-muted small">XLSX, XLS, CSV, PDF, imágenes — máx. 20 MB c/u</p>
                                    <input type="file" id="archivos-nuevo" name="archivos[]"
                                           multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn-outline-success btn"
                                                onclick="document.getElementById('archivos-nuevo').click()">
                                            <i class="me-1 mdi mdi-paperclip"></i>Seleccionar archivos
                                        </button>
                                        <button type="button" class="btn-outline-secondary btn"
                                                onclick="abrirCamara('archivos-nuevo','preview-nuevo')">
                                            <i class="me-1 mdi mdi-camera"></i>Tomar foto
                                        </button>
                                    </div>
                                </div>
                                <div id="preview-nuevo" class="mt-2 row g-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notas</label>
                                <textarea name="notas" rows="2" class="form-control" placeholder="Opcional..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success fw-bold" id="btn-enviar">
                                <i class="me-1 mdi mdi-send"></i>Enviar y Registrar
                            </button>
                            <div id="msg-enviar" class="mt-2"></div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold"><i class="me-2 text-success mdi mdi-chart-line"></i>KPI Semanal por Sede</h5>
                        <div class="d-flex align-items-center gap-2">
                            <label class="me-1 mb-0 text-muted small">Semanas:</label>
                            <select id="filtro-semanas" class="form-select-sm form-select" style="width:80px;">
                                <option value="4">4</option>
                                <option value="8" selected>8</option>
                                <option value="12">12</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:300px;">
                            <canvas id="kpi-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->puede_ver_productividad_sedes): ?>
        <div class="mb-4 row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-chart-bar"></i>Cumplimiento Mensual por Sede
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <label class="me-1 mb-0 text-muted small">Meses:</label>
                            <select id="filtro-meses-mensual" class="form-select-sm form-select" style="width:80px;">
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="6">6</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position:relative;height:320px;">
                            <canvas id="kpi-chart-mensual"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold"><i class="me-2 text-success mdi mdi-history"></i>Historial de Reportes</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <select id="filtro-sede" class="form-select form-select-sm" style="width:auto" onchange="aplicarFiltros()">
                                <option value="">Todas las sedes</option>
                            </select>
                            <select id="filtro-semana" class="form-select form-select-sm" style="width:auto" onchange="aplicarFiltros()">
                                <option value="">Todas las semanas</option>
                            </select>
                            <select id="sort-fecha" class="form-select form-select-sm" style="width:auto" onchange="aplicarFiltros()">
                                <option value="desc">Más reciente primero</option>
                                <option value="asc">Más antiguo primero</option>
                            </select>
                            <div id="historial-loader" class="spinner-border spinner-border-sm text-success d-none"></div>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th><th>Semana</th><th>Período</th>
                                        <th>Fecha Límite</th><th>Fecha Envío</th>
                                        <th>KPI</th><th>Estado</th><th>Archivos</th><th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historial-body">
                                    <tr><td colspan="9" class="py-4 text-center">
                                        <div class="me-2 spinner-border spinner-border-sm text-success"></div>Cargando...
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-reporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="me-2 text-success mdi mdi-cash-multiple"></i>
                    <span id="modal-titulo">Detalle del Reporte</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="py-4 text-center"><div class="spinner-border text-success"></div></div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-enviar-atrasado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="me-2 text-warning mdi mdi-clock-alert-outline"></i>Enviar Reporte Atrasado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-atrasado" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" id="atrasado-reporte-id">
                <div class="modal-body">
                    <div class="mb-3 py-2 alert alert-warning small">
                        <i class="me-1 mdi mdi-alert"></i>
                        El plazo ya venció. Este envío se registrará como <strong>atrasado</strong> y el KPI será penalizado.
                    </div>
                    <p class="mb-3 small"><strong>Sede:</strong> <span id="atrasado-sede-label" class="text-primary fw-bold"></span></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Archivos <span class="text-danger">*</span></label>
                        <div class="drop-zone" id="drop-zone-atrasado">
                            <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                            <p class="mt-2 mb-1 text-muted small">Arrastra archivos aquí o selecciona</p>
                            <input type="file" id="archivos-atrasado" name="archivos[]"
                                   multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf" class="d-none">
                            <button type="button" class="mt-1 btn-outline-primary btn btn-sm"
                                    onclick="document.getElementById('archivos-atrasado').click()">
                                <i class="me-1 mdi mdi-paperclip"></i>Seleccionar
                            </button>
                        </div>
                        <div id="preview-atrasado" class="mt-2 row g-2"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notas" rows="2" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
                    </div>
                    <div id="msg-atrasado"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary btn btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold btn-sm" id="btn-enviar-atrasado">
                        <i class="me-1 mdi mdi-clock-alert-outline"></i>Enviar Atrasado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-camara" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="me-2 text-success mdi mdi-camera"></i>Tomar Foto</h5>
                <button type="button" class="btn-close" onclick="cerrarCamara()"></button>
            </div>
            <div class="p-3 text-center modal-body">
                <video id="camara-video" autoplay playsinline
                       style="width:100%;border-radius:10px;background:#000;max-height:380px;object-fit:cover;"></video>
                <canvas id="camara-canvas" class="d-none"></canvas>
                <div id="camara-preview-wrap" class="mt-2 d-none">
                    <img id="camara-preview-img" src="" alt="Captura"
                         style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;">
                </div>
            </div>
            <div class="justify-content-center gap-2 modal-footer">
                <button id="btn-capturar" class="btn btn-success" onclick="capturarFoto()">
                    <i class="me-1 mdi mdi-camera"></i>Capturar
                </button>
                <button id="btn-retomar" class="btn-outline-secondary btn d-none" onclick="retomarFoto()">
                    <i class="me-1 mdi mdi-refresh"></i>Retomar
                </button>
                <button id="btn-usar-foto" class="btn btn-success d-none" onclick="usarFoto()">
                    <i class="me-1 mdi mdi-check"></i>Usar esta foto
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.drop-zone { border:2px dashed #cbd5e1;border-radius:12px;padding:32px 20px;text-align:center;background:#f8fafc;transition:border-color .2s,background .2s;cursor:pointer; }
.drop-zone.dragover { border-color:#10b981;background:#ecfdf5; }
.preview-thumb { position:relative;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#f8fafc; }
.preview-thumb img { width:100%;height:80px;object-fit:cover; }
.preview-thumb .preview-remove { position:absolute;top:4px;right:4px;width:22px;height:22px;background:#ef4444;border:none;border-radius:50%;color:#fff;font-size:12px;display:flex;align-items:center;justify-content:center;cursor:pointer; }
.preview-thumb .preview-name { font-size:10px;padding:2px 4px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.filtro-estado { cursor:pointer;user-select:none;transition:opacity .2s,text-decoration .2s; }
.filtro-estado.inactivo { opacity:.35;text-decoration:line-through; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ROUTES = {
    store:    "<?php echo route('productividad.cobranza-sedes.caja-chica.store'); ?>",
    historial:"<?php echo route('productividad.cobranza-sedes.caja-chica.historial'); ?>",
    kpiData:  "<?php echo route('productividad.cobranza-sedes.caja-chica.kpi-data'); ?>",
    base:     "<?php echo url('/productividad/cobranza-sedes/caja-chica'); ?>",
};
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const urlShow    = (id)      => `${ROUTES.base}/${id}/show`;
const urlUpdate  = (id)      => `${ROUTES.base}/${id}`;
const urlPreview = (id, idx) => `${ROUTES.base}/${id}/preview/${idx}`;
const urlDownload= (id, idx) => `${ROUTES.base}/${id}/download/${idx}`;

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...(options.headers ?? {}) },
        ...options,
    });
    const ct = res.headers.get('Content-Type') ?? '';
    if (!ct.includes('application/json')) throw new Error(`Error ${res.status}: respuesta inesperada del servidor.`);
    const data = await res.json();
    if (!res.ok) {
        const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.error ?? data.message ?? `Error ${res.status}`);
        throw new Error(msg);
    }
    return data;
}

// ── Countdown ──────────────────────────────────────────────────────
const DEADLINE_TS = <?php echo $fechaLimiteTs; ?>;

function actualizarCountdown() {
    const diffMs  = DEADLINE_TS - Date.now();
    const display = document.getElementById('countdown-display');
    const sub     = document.getElementById('countdown-subtitle');
    const icon    = document.getElementById('countdown-icon');
    const card    = document.getElementById('card-countdown');
    if (diffMs <= 0) {
        display.textContent = 'VENCIDO'; display.style.color = '#dc2626';
        sub.textContent = 'El plazo ya venció';
        icon.className  = 'mdi mdi-alert-circle-outline text-danger';
        card.classList.add('border-danger'); return;
    }
    const h = String(Math.floor(diffMs/3600000)).padStart(2,'0');
    const m = String(Math.floor((diffMs%3600000)/60000)).padStart(2,'0');
    const s = String(Math.floor((diffMs%60000)/1000)).padStart(2,'0');
    display.textContent = `${h}:${m}:${s}`;
    if (diffMs <= 3600000) { display.style.color='#dc2626'; icon.className='mdi mdi-timer-alert-outline text-danger'; card.classList.add('border-danger'); sub.textContent='Menos de 1 hora — ¡envía ahora!'; }
    else if (diffMs <= 7200000) { display.style.color='#d97706'; icon.className='mdi mdi-timer-outline text-warning'; sub.textContent='Menos de 2 horas'; }
    else { display.style.color='#10b981'; icon.className='mdi mdi-timer-outline text-success'; sub.textContent='Sábado, 11:59 PM hora Lima'; }
}
actualizarCountdown();
setInterval(actualizarCountdown, 1000);

// ── Drop zones (acumulador para preservar archivos entre selecciones) ──
const _acumulados = {};

function setupDropZone(zoneId, inputId, previewId) {
    const zone    = document.getElementById(zoneId);
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!zone || !input) return;

    _acumulados[inputId] = new DataTransfer();

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        agregarArchivos(e.dataTransfer.files, inputId, preview);
    });
    input.addEventListener('change', () => {
        agregarArchivos(input.files, inputId, preview);
        input.value = ''; // permite re-seleccionar el mismo archivo
    });
}

function agregarArchivos(newFiles, inputId, preview) {
    const dt = _acumulados[inputId];
    for (const f of newFiles) dt.items.add(f);
    renderPreview(dt.files, preview, inputId);
}

function renderPreview(files, preview, inputId) {
    preview.innerHTML = '';
    for (let i = 0; i < files.length; i++) {
        const f   = files[i];
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3 col-lg-2';
        const isImg = f.type.startsWith('image/');
        col.innerHTML = `<div class="p-1 preview-thumb">${isImg ? `<img src="${URL.createObjectURL(f)}" alt="${f.name}">` : `<div class="d-flex align-items-center justify-content-center" style="height:80px;font-size:2rem;"><i class="text-success mdi mdi-file-excel"></i></div>`}<button type="button" class="preview-remove" onclick="quitarArchivo(${i}, '${inputId}')">×</button><div class="text-muted preview-name">${f.name}</div></div>`;
        preview.appendChild(col);
    }
}

function quitarArchivo(idx, inputId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(inputId.replace('archivos', 'preview'));
    const dt      = new DataTransfer();
    const current = _acumulados[inputId].files;
    for (let i = 0; i < current.length; i++) {
        if (i !== idx) dt.items.add(current[i]);
    }
    _acumulados[inputId] = dt;
    input.files = dt.files;
    renderPreview(dt.files, preview, inputId);
}

setupDropZone('drop-zone-nuevo', 'archivos-nuevo', 'preview-nuevo');
setupDropZone('drop-zone-edit',     'archivos-edit',     'preview-edit');
setupDropZone('drop-zone-atrasado', 'archivos-atrasado', 'preview-atrasado');

function marcarEliminar(idx, btn) {
    const hidden = document.getElementById('eliminar-' + idx);
    const card   = document.getElementById('archivo-card-' + idx);
    if (hidden.disabled) { hidden.disabled=false; card.style.opacity='0.4'; btn.innerHTML='<i class="mdi mdi-undo"></i>'; btn.classList.replace('btn-outline-danger','btn-outline-secondary'); }
    else { hidden.disabled=true; card.style.opacity='1'; btn.innerHTML='<i class="mdi-trash-can-outline mdi"></i>'; btn.classList.replace('btn-outline-secondary','btn-outline-danger'); }
}

// ── Envío ──────────────────────────────────────────────────────────
const formEnviar = document.getElementById('form-enviar-reporte');
if (formEnviar) {
    formEnviar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-enviar');
        const msg = document.getElementById('msg-enviar');
        btn.disabled=true; btn.innerHTML='<span class="me-1 spinner-border spinner-border-sm"></span>Enviando...'; msg.innerHTML='';
        try {
            document.getElementById('archivos-nuevo').files = _acumulados['archivos-nuevo'].files;
            const data = await apiFetch(ROUTES.store, { method:'POST', body: new FormData(this) });
            msg.innerHTML = `<div class="py-2 alert alert-success">${data.message}</div>`;
            setTimeout(() => location.reload(), 1500);
        } catch(err) { msg.innerHTML=`<div class="py-2 alert alert-danger">${err.message}</div>`; }
        finally { btn.disabled=false; btn.innerHTML='<i class="me-1 mdi mdi-send"></i>Enviar y Registrar'; }
    });
}

// ── Edición ────────────────────────────────────────────────────────
const formEditar = document.getElementById('form-editar-reporte');
if (formEditar) {
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-editar');
        const msg = document.getElementById('msg-editar');
        const reporteId = this.querySelector('[name="reporte_id"]').value;
        btn.disabled=true; btn.innerHTML='<span class="me-1 spinner-border spinner-border-sm"></span>Guardando...'; msg.innerHTML='';
        try {
            document.getElementById('archivos-edit').files = _acumulados['archivos-edit'].files;
            const data = await apiFetch(urlUpdate(reporteId), { method:'POST', body: new FormData(this) });
            msg.innerHTML=`<div class="alert alert-${data.tardio?'warning':'success'} py-2">${data.message}</div>`;
            setTimeout(()=>location.reload(),1800);
        } catch(err) { msg.innerHTML=`<div class="py-2 alert alert-danger">${err.message}</div>`; }
        finally { btn.disabled=false; btn.innerHTML='<i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios'; }
    });
}

// ── Historial ──────────────────────────────────────────────────────
let _historialData = [];

function badgeEstadoCaja(estado) {
    const colorMap = { en_tiempo:'success', con_atraso:'danger', pendiente:'warning', no_enviado:'danger' };
    const labelMap = { en_tiempo:'En tiempo', con_atraso:'Con atraso', pendiente:'Pendiente', no_enviado:'No enviado' };
    return `<span class="badge bg-${colorMap[estado] ?? 'secondary'}">${labelMap[estado] ?? estado}</span>`;
}

async function cargarHistorial() {
    const loader = document.getElementById('historial-loader');
    loader.classList.remove('d-none');
    try {
        const data = await apiFetch(ROUTES.historial);
        _historialData = data.data ?? [];
        poblarFiltroSede(_historialData);
        poblarFiltroSemana(_historialData);
        aplicarFiltros();
    } catch(err) { document.getElementById('historial-body').innerHTML=`<tr><td colspan="9" class="py-3 text-danger text-center">${err.message}</td></tr>`; }
    finally { loader.classList.add('d-none'); }
}

function poblarFiltroSede(rows) {
    const sel = document.getElementById('filtro-sede');
    if (!sel) return;
    const sedes = [...new Set(rows.map(r => r.sede))].sort();
    sedes.forEach(s => { const o = document.createElement('option'); o.value = s; o.textContent = s; sel.appendChild(o); });
}

function poblarFiltroSemana(rows) {
    const sel = document.getElementById('filtro-semana');
    if (!sel) return;
    const vistas = new Set();
    [...rows].sort((a, b) => (b.semana_inicio_iso ?? '').localeCompare(a.semana_inicio_iso ?? '')).forEach(r => {
        if (!r.semana || vistas.has(r.semana)) return;
        vistas.add(r.semana);
        const label = r.semana_inicio && r.semana_fin ? `${r.semana} — ${r.semana_inicio} al ${r.semana_fin}` : r.semana;
        const o = document.createElement('option'); o.value = r.semana; o.textContent = label; sel.appendChild(o);
    });
}

function aplicarFiltros() {
    const sede   = document.getElementById('filtro-sede')?.value ?? '';
    const semana = document.getElementById('filtro-semana')?.value ?? '';
    const dir    = document.getElementById('sort-fecha')?.value ?? 'desc';
    let rows = [..._historialData];
    if (sede)   rows = rows.filter(r => r.sede === sede);
    if (semana) rows = rows.filter(r => r.semana === semana);
    rows.sort((a, b) => {
        const da = a.semana_inicio_iso ?? '', db = b.semana_inicio_iso ?? '';
        return dir === 'asc' ? da.localeCompare(db) : db.localeCompare(da);
    });
    renderHistorial(rows);
}

function renderHistorial(rows) {
    const tbody = document.getElementById('historial-body');
    if (!rows.length) { tbody.innerHTML='<tr><td colspan="9" class="py-4 text-muted text-center">Sin registros.</td></tr>'; return; }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><strong>${r.sede}</strong></td>
            <td><span class="bg-light border text-dark badge">${r.semana}</span></td>
            <td class="text-muted small">${r.semana_inicio} — ${r.semana_fin}</td>
            <td class="small">${r.fecha_limite ?? '—'}</td>
            <td class="small">${r.fecha_envio ?? '<span class="text-muted">—</span>'}${r.fecha_edicion ? `<br><span class="text-warning small"><i class="mdi mdi-pencil"></i> ${r.fecha_edicion}</span>` : ''}</td>
            <td>${r.kpi !== null ? `<span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${badgeEstadoCaja(r.estado)}</td>
            <td class="text-center">${r.num_archivos > 0 ? `<span class="bg-info badge">${r.num_archivos}</span>` : '0'}</td>
            <td class="text-nowrap">
                <button class="px-2 py-0 btn-outline-success btn btn-sm" onclick="verReporte(${r.id})"><i class="mdi mdi-eye"></i></button>
                ${r.puede_enviar_atrasado ? `<button class="px-2 py-0 btn-outline-danger btn btn-sm ms-1" onclick="abrirEnviarAtrasado(${r.id},'${r.sede}')" title="Enviar atrasado"><i class="mdi mdi-clock-alert-outline"></i></button>` : ''}
            </td>
        </tr>`).join('');
}

cargarHistorial();

// ── Modal Ver ──────────────────────────────────────────────────────
async function verReporte(id) {
    const modal  = new bootstrap.Modal(document.getElementById('modal-reporte'));
    const body   = document.getElementById('modal-body');
    const titulo = document.getElementById('modal-titulo');
    body.innerHTML='<div class="py-4 text-center"><div class="spinner-border text-success"></div></div>';
    titulo.textContent='Detalle'; modal.show();
    try {
        const r = await apiFetch(urlShow(id));
        titulo.textContent = `${r.sede} — ${r.semana}`;
        const imagenes = (r.archivos??[]).filter(a=>a.es_imagen);
        const otros    = (r.archivos??[]).filter(a=>!a.es_imagen);
        let archivosHTML = '';
        if (imagenes.length) {
            archivosHTML += `<div class="mb-3 row g-2">${imagenes.map(a=>`<div class="col-6 col-md-4"><div class="position-relative border rounded overflow-hidden" style="aspect-ratio:4/3;"><img src="${a.preview_url}" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onclick="window.open('${a.preview_url}','_blank')"><div class="bottom-0 position-absolute d-flex justify-content-between px-2 py-1 start-0 end-0" style="background:rgba(0,0,0,0.45);"><span class="text-white text-truncate small" style="max-width:70%;">${a.name}</span><a href="${a.download_url}" class="px-1 py-0 btn btn-sm btn-light" download><i class="mdi mdi-download small"></i></a></div></div></div>`).join('')}</div>`;
        }
        otros.forEach(a => {
            const ext = a.name.split('.').pop().toUpperCase();
            const icon = ['XLSX','XLS','CSV'].includes(ext) ? 'mdi-file-excel text-success' : ext==='PDF' ? 'mdi-file-pdf text-danger' : 'mdi-file-document text-primary';
            archivosHTML += `<div class="d-flex align-items-center gap-2 bg-light mb-1 p-2 border rounded"><i class="mdi ${icon} fs-5"></i><span class="flex-grow-1 text-truncate small fw-semibold">${a.name}</span><a href="${a.download_url}" class="px-2 py-0 btn-outline-success btn btn-sm" download><i class="mdi mdi-download"></i> Descargar</a></div>`;
        });
        if (!archivosHTML) archivosHTML = '<p class="text-muted">Sin archivos.</p>';
        body.innerHTML = `
            <div class="mb-3 row g-3">
                <div class="col-6"><div class="text-muted text-uppercase small fw-semibold">Sede</div><div class="fw-bold">${r.sede}</div></div>
                <div class="col-6"><div class="text-muted text-uppercase small fw-semibold">Semana</div><div class="fw-bold">${r.semana}</div></div>
                <div class="col-6"><div class="text-muted text-uppercase small fw-semibold">Fecha Límite</div><div>${r.fecha_limite??'—'}</div></div>
                <div class="col-6"><div class="text-muted text-uppercase small fw-semibold">Fecha Envío</div><div>${r.fecha_envio??'<span class="text-muted">No enviado</span>'}</div></div>
                ${r.fecha_edicion?`<div class="col-12"><div class="mb-0 py-1 alert alert-warning small"><i class="me-1 mdi mdi-pencil"></i>Editado tardíamente el ${r.fecha_edicion}</div></div>`:''}
                <div class="col-6"><div class="text-muted text-uppercase small fw-semibold">KPI</div><span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span></div>
                ${r.notas?`<div class="col-12"><div class="text-muted text-uppercase small fw-semibold">Notas</div><div class="bg-light p-2 border rounded small">${r.notas}</div></div>`:''}
            </div><hr>
            <h6 class="mb-2 fw-semibold">Archivos adjuntos</h6>${archivosHTML}`;
    } catch(err) { body.innerHTML=`<div class="alert alert-danger">${err.message}</div>`; }
}

// ── Gráfico KPI ────────────────────────────────────────────────────
function opcionesChart(tooltipExtra = '') {
    return {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%', stepSize: 25 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } },
        },
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: { callbacks: { label: c => `${c.dataset.label}: ${c.parsed.y !== null ? c.parsed.y + '%' : 'Sin dato'}` + tooltipExtra } },
        },
    };
}

let kpiChart = null;
async function cargarKpiChart(semanas = 8) {
    try {
        const data = await apiFetch(`${ROUTES.kpiData}?tipo=semanas&semanas=${semanas}`);
        const ctx  = document.getElementById('kpi-chart').getContext('2d');
        if (kpiChart) kpiChart.destroy();
        kpiChart = new Chart(ctx, { type: 'line', data, options: opcionesChart() });
    } catch(e) { console.error(e); }
}
cargarKpiChart(8);
document.getElementById('filtro-semanas')?.addEventListener('change', function () { cargarKpiChart(parseInt(this.value)); });

<?php if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->puede_ver_productividad_sedes): ?>
let kpiChartMensual = null;
async function cargarKpiChartMensual(meses = 3) {
    try {
        const data = await apiFetch(`${ROUTES.kpiData}?tipo=mensual&meses=${meses}`);
        const ctx  = document.getElementById('kpi-chart-mensual').getContext('2d');
        if (kpiChartMensual) kpiChartMensual.destroy();
        kpiChartMensual = new Chart(ctx, { type: 'line', data, options: opcionesChart(' (prom.)') });
    } catch(e) { console.error(e); }
}
cargarKpiChartMensual(3);
document.getElementById('filtro-meses-mensual')?.addEventListener('change', function () { cargarKpiChartMensual(parseInt(this.value)); });
<?php endif; ?>

// ── Cámara ─────────────────────────────────────────────────────────
let _streamActivo=null,_camaraInputId=null,_camaraPreviewId=null,_fotoBlob=null,_modalCamaraInst=null;
async function abrirCamara(inputId, previewId) {
    _camaraInputId=inputId; _camaraPreviewId=previewId; _fotoBlob=null;
    document.getElementById('camara-preview-wrap').classList.add('d-none');
    document.getElementById('camara-video').classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');
    try { _streamActivo=await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'},audio:false}); document.getElementById('camara-video').srcObject=_streamActivo; }
    catch { alert('No se pudo acceder a la cámara. Verifica los permisos.'); return; }
    _modalCamaraInst=new bootstrap.Modal(document.getElementById('modal-camara')); _modalCamaraInst.show();
}
function capturarFoto() {
    const video=document.getElementById('camara-video'), canvas=document.getElementById('camara-canvas');
    canvas.width=video.videoWidth; canvas.height=video.videoHeight;
    canvas.getContext('2d').drawImage(video,0,0);
    canvas.toBlob(blob=>{ _fotoBlob=blob; document.getElementById('camara-preview-img').src=URL.createObjectURL(blob); document.getElementById('camara-preview-wrap').classList.remove('d-none'); video.classList.add('d-none'); document.getElementById('btn-capturar').classList.add('d-none'); document.getElementById('btn-retomar').classList.remove('d-none'); document.getElementById('btn-usar-foto').classList.remove('d-none'); _streamActivo?.getTracks().forEach(t=>t.enabled=false); },'image/jpeg',0.92);
}
function retomarFoto() { _fotoBlob=null; _streamActivo?.getTracks().forEach(t=>t.enabled=true); document.getElementById('camara-preview-wrap').classList.add('d-none'); document.getElementById('camara-video').classList.remove('d-none'); document.getElementById('btn-capturar').classList.remove('d-none'); document.getElementById('btn-retomar').classList.add('d-none'); document.getElementById('btn-usar-foto').classList.add('d-none'); }
function usarFoto() {
    if (!_fotoBlob) return;
    const ts   = new Date().toISOString().replace(/[:.]/g, '-');
    const file = new File([_fotoBlob], `foto-${ts}.jpg`, { type: 'image/jpeg' });
    const preview = document.getElementById(_camaraPreviewId);
    agregarArchivos([file], _camaraInputId, preview);
    cerrarCamara();
}
function cerrarCamara() { _streamActivo?.getTracks().forEach(t=>t.stop()); _streamActivo=null; _modalCamaraInst?.hide(); }

// ── Filtros tabla Estado Sedes ────────────────────────────────────
(function () {
    const activos = new Set(['enviado', 'pendiente']);
    function aplicar() {
        document.querySelectorAll('#tabla-estado-sedes tbody tr[data-estado]').forEach(tr => {
            tr.style.display = activos.has(tr.dataset.estado) ? '' : 'none';
        });
    }
    document.querySelectorAll('.filtro-estado').forEach(badge => {
        badge.addEventListener('click', function () {
            const key = this.dataset.filtro;
            if (activos.has(key)) { activos.delete(key); this.classList.add('inactivo'); }
            else { activos.add(key); this.classList.remove('inactivo'); }
            aplicar();
        });
    });
})();

// ── Envío atrasado ────────────────────────────────────────────────
function abrirEnviarAtrasado(id, sede) {
    document.getElementById('atrasado-reporte-id').value = id;
    document.getElementById('atrasado-sede-label').textContent = sede;
    document.getElementById('msg-atrasado').innerHTML = '';
    _acumulados['archivos-atrasado'] = new DataTransfer();
    document.getElementById('preview-atrasado').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modal-enviar-atrasado')).show();
}

document.getElementById('form-atrasado')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-enviar-atrasado');
    const msg = document.getElementById('msg-atrasado');
    const id  = document.getElementById('atrasado-reporte-id').value;
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...'; msg.innerHTML = '';
    try {
        document.getElementById('archivos-atrasado').files = _acumulados['archivos-atrasado'].files;
        const data = await apiFetch(urlUpdate(id), { method: 'POST', body: new FormData(this) });
        msg.innerHTML = `<div class="alert alert-warning py-2">${data.message}</div>`;
        setTimeout(() => location.reload(), 1800);
    } catch(err) { msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`; }
    finally { btn.disabled = false; btn.innerHTML = '<i class="me-1 mdi mdi-clock-alert-outline"></i>Enviar Atrasado'; }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/productividad/cobranza-sedes/caja-chica.blade.php ENDPATH**/ ?>