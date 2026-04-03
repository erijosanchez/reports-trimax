<?php $__env->startSection('title', 'Cobranza Sedes'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="me-2 text-primary mdi mdi-currency-usd"></i>Cobranza Sedes
                            </h4>
                            <p class="mb-0 text-muted small">Reporte semanal — límite: sábado 12:00 PM</p>
                        </div>
                        <div class="text-end">
                            <span class="bg-primary badge fs-6">
                                Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                            </span>
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
                        <div id="countdown-display" class="mb-1 display-6 fw-bold">--:--:--</div>
                        <div id="countdown-subtitle" class="text-muted small">Cargando...</div>
                        <hr class="my-3">
                        <div class="text-muted small">
                            Límite: <strong>Sábado 12:00 PM</strong>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->isSede() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()): ?>
            <div class="mb-3 col-lg-4 col-md-6">
                <div class="shadow-sm border-0 h-100 card">
                    <div class="py-4 card-body">
                        <h6 class="mb-3 text-muted text-uppercase fw-bold small">Estado — Semana Actual</h6>
                        <?php if($reporteSemanaActual): ?>
                            <?php
                                $enviado = !is_null($reporteSemanaActual->fecha_envio_original);
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="me-2 text-primary mdi mdi-map-marker"></i>
                                <strong><?php echo $reporteSemanaActual->sede; ?></strong>
                            </div>
                            <?php if($enviado): ?>
                                <div class="mb-2 py-2 alert alert-success">
                                    <i class="me-1 mdi mdi-check-circle"></i>
                                    Reporte enviado el
                                    <?php echo $reporteSemanaActual->fecha_envio_original?->setTimezone('America/Lima')->format('d/m H:i'); ?>

                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="me-2 fw-bold">KPI:</span>
                                    <span class="badge bg-<?php echo $reporteSemanaActual->kpiColor(); ?> fs-6">
                                        <?php echo $reporteSemanaActual->kpiLabel(); ?>

                                    </span>
                                </div>
                                <?php if($reporteSemanaActual->editado_tarde): ?>
                                <div class="mt-2 mb-0 py-1 alert alert-warning small">
                                    <i class="me-1 mdi mdi-alert"></i>
                                    Editado con atraso — KPI ajustado
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="mb-0 py-2 alert alert-warning">
                                    <i class="me-1 mdi mdi-clock-alert"></i>
                                    Pendiente de envío
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="mb-0 text-muted">No tienes sede asignada.</p>
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
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Antes de las 12:00 PM</span>
                                <span class="bg-success badge">100%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 1 hora de atraso</span>
                                <span class="bg-info badge">90%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 2 horas de atraso</span>
                                <span class="bg-primary badge">80%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Hasta 3 horas de atraso</span>
                                <span class="bg-warning text-dark badge">50%</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small">Más de 3 horas o no enviado</span>
                                <span class="bg-danger badge">0%</span>
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
                            <i class="me-2 mdi-view-dashboard-outline text-primary mdi"></i>
                            Estado Sedes — Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                        </h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <span class="bg-success badge">Enviado</span>
                            <span class="bg-danger badge">Pendiente</span>
                        </div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Responsable</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Hora de Envío</th>
                                        <th class="text-center">KPI Semana</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $resumenSedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fila): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo $fila['enviado'] ? '' : 'table-danger bg-opacity-25'; ?>">
                                        <td>
                                            <strong><?php echo $fila['sede']; ?></strong>
                                        </td>
                                        <td class="text-muted small"><?php echo $fila['usuario']; ?></td>
                                        <td class="text-center">
                                            <?php if($fila['enviado']): ?>
                                                <span class="bg-success badge">
                                                    <i class="me-1 mdi mdi-check"></i>Enviado
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-danger badge">
                                                    <i class="me-1 mdi mdi-clock-alert"></i>Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($fila['fecha_envio']): ?>
                                                <span class="fw-semibold"><?php echo $fila['fecha_envio']; ?> hrs</span>
                                                <?php if($fila['editado_tarde']): ?>
                                                    <br><small class="text-warning"><i class="mdi mdi-pencil"></i> Editado tarde</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!is_null($fila['kpi'])): ?>
                                                <span class="badge bg-<?php echo $fila['kpi_color']; ?> fs-6">
                                                    <?php echo $fila['kpi_label']; ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($fila['reporte_id']): ?>
                                                <button class="px-2 py-0 btn-outline-primary btn btn-sm"
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
                        <span>
                            <i class="text-success mdi mdi-check-circle"></i>
                            Enviados: <strong><?php echo $resumenSedes->where('enviado', true)->count(); ?></strong>
                        </span>
                        <span>
                            <i class="text-danger mdi mdi-clock-alert"></i>
                            Pendientes: <strong><?php echo $resumenSedes->where('enviado', false)->count(); ?></strong>
                        </span>
                        <span>
                            <i class="text-primary mdi mdi-chart-line"></i>
                            KPI promedio:
                            <strong>
                                <?php
                                    $kpisValidos = $resumenSedes->whereNotNull('kpi')->pluck('kpi');
                                    echo $kpisValidos->count() ? number_format($kpisValidos->avg(), 1) . '%' : '—';
                                ?>
                            </strong>
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
                            <i class="me-2 text-primary mdi mdi-upload"></i>
                            <?php if($reporteSemanaActual->fecha_envio_original): ?>
                                Editar Reporte — Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                            <?php else: ?>
                                Enviar Reporte — Semana <?php echo $semanaNumero; ?>/<?php echo $anio; ?>

                            <?php endif; ?>
                        </h5>
                        <span class="bg-light border text-dark badge">
                            Sede: <strong><?php echo $reporteSemanaActual->sede; ?></strong>
                        </span>
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
                                                <i class="mdi mdi-<?php echo str_contains($archivo['mime'] ?? '', 'image') ? 'image' : 'file-excel'; ?> text-primary fs-5"></i>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="text-truncate small fw-semibold" title="<?php echo $archivo['name']; ?>"><?php echo $archivo['name']; ?></div>
                                                    <div class="text-muted" style="font-size:11px;"><?php echo number_format(($archivo['size'] ?? 0)/1024, 1); ?> KB</div>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo route('productividad.cobranza-sedes.cobranza.download', [$reporteSemanaActual->id, $idx]); ?>"
                                                       class="px-1 py-0 btn-outline-primary btn btn-sm" title="Descargar">
                                                        <i class="mdi mdi-download"></i>
                                                    </a>
                                                    <button type="button" class="px-1 py-0 btn-outline-danger btn btn-sm"
                                                            onclick="marcarEliminar(<?php echo $idx; ?>, this)" title="Eliminar">
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
                                    <label for="archivos-edit" class="form-label fw-semibold">Agregar archivos adicionales</label>
                                    <div class="drop-zone" id="drop-zone-edit">
                                        <i class="mdi-cloud-upload-outline mdi" style="font-size:2rem;color:#94a3b8;"></i>
                                        <p class="mt-2 mb-1 text-muted small">Arrastra fotos o archivos Excel/PDF aquí</p>
                                        <p class="text-muted" style="font-size:11px;">JPG, PNG, GIF, WEBP, XLSX, XLS, CSV, PDF — máx. 20 MB c/u</p>
                                        <input type="file" id="archivos-edit" name="archivos[]"
                                               multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf"
                                               class="d-none">
                                        <div class="d-flex gap-2 justify-content-center mt-1">
                                            <button type="button" class="btn-outline-primary btn btn-sm"
                                                    onclick="document.getElementById('archivos-edit').click()">
                                                <i class="mdi mdi-paperclip me-1"></i>Seleccionar archivos
                                            </button>
                                            <button type="button" class="btn-outline-secondary btn btn-sm"
                                                    onclick="abrirCamara('archivos-edit', 'preview-edit')">
                                                <i class="mdi mdi-camera me-1"></i>Tomar foto
                                            </button>
                                        </div>
                                    </div>
                                    <div id="preview-edit" class="mt-2 row g-2"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="notas-edit" class="form-label fw-semibold">Notas / Observaciones</label>
                                    <textarea id="notas-edit" name="notas" rows="2"
                                              class="form-control" placeholder="Opcional..."><?php echo $reporteSemanaActual->notas; ?></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning fw-bold" id="btn-editar">
                                        <i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios
                                    </button>
                                </div>
                                <div id="msg-editar" class="mt-2"></div>
                            </form>
                        <?php else: ?>
                            
                            <form id="form-enviar-reporte" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Archivos del reporte <span class="text-danger">*</span></label>
                                    <div class="drop-zone" id="drop-zone-nuevo">
                                        <i class="mdi-cloud-upload-outline mdi" style="font-size:2.5rem;color:#94a3b8;"></i>
                                        <p class="mt-2 mb-1 text-muted">Arrastra tus fotos o archivos Excel/PDF aquí</p>
                                        <p class="text-muted small">JPG, PNG, GIF, WEBP, XLSX, XLS, CSV, PDF — máx. 20 MB c/u</p>
                                        <input type="file" id="archivos-nuevo" name="archivos[]"
                                               multiple accept=".jpg,.jpeg,.png,.gif,.webp,.xlsx,.xls,.csv,.pdf"
                                               class="d-none">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn-outline-primary btn"
                                                    onclick="document.getElementById('archivos-nuevo').click()">
                                                <i class="mdi mdi-paperclip me-1"></i>Seleccionar archivos
                                            </button>
                                            <button type="button" class="btn-outline-secondary btn"
                                                    onclick="abrirCamara('archivos-nuevo', 'preview-nuevo')">
                                                <i class="mdi mdi-camera me-1"></i>Tomar foto
                                            </button>
                                        </div>
                                    </div>
                                    <div id="preview-nuevo" class="mt-2 row g-2"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="notas-nuevo" class="form-label fw-semibold">Notas / Observaciones</label>
                                    <textarea id="notas-nuevo" name="notas" rows="2"
                                              class="form-control" placeholder="Opcional..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary fw-bold" id="btn-enviar">
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
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-chart-line"></i>KPI por Sede
                        </h5>
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
                        <div style="position:relative;height:320px;">
                            <canvas id="kpi-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="shadow-sm border-0 card">
                    <div class="d-flex align-items-center justify-content-between bg-white border-bottom card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="me-2 text-primary mdi mdi-history"></i>Historial de Reportes
                        </h5>
                        <div id="historial-loader" class="spinner-border spinner-border-sm text-primary d-none"></div>
                    </div>
                    <div class="p-0 card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-historial">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Semana</th>
                                        <th>Período</th>
                                        <th>Fecha Límite</th>
                                        <th>Fecha Envío</th>
                                        <th>KPI</th>
                                        <th>Estado</th>
                                        <th>Archivos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="historial-body">
                                    <tr>
                                        <td colspan="9" class="py-4 text-center">
                                            <div class="me-2 spinner-border spinner-border-sm text-primary"></div>
                                            Cargando historial...
                                        </td>
                                    </tr>
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
                    <i class="me-2 mdi-file-document-outline mdi"></i>
                    <span id="modal-titulo">Detalle del Reporte</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="py-4 text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-camara" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="mdi mdi-camera me-2 text-primary"></i>Tomar Foto
                </h5>
                <button type="button" class="btn-close" onclick="cerrarCamara()"></button>
            </div>
            <div class="modal-body text-center p-3">
                <video id="camara-video" autoplay playsinline
                       style="width:100%;border-radius:10px;background:#000;max-height:380px;object-fit:cover;"></video>
                <canvas id="camara-canvas" class="d-none"></canvas>
                <div id="camara-preview-wrap" class="d-none mt-2">
                    <img id="camara-preview-img" src="" alt="Captura"
                         style="width:100%;border-radius:10px;max-height:300px;object-fit:contain;">
                </div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button id="btn-capturar" class="btn btn-primary" onclick="capturarFoto()">
                    <i class="mdi mdi-camera me-1"></i>Capturar
                </button>
                <button id="btn-retomar" class="btn btn-outline-secondary d-none" onclick="retomarFoto()">
                    <i class="mdi mdi-refresh me-1"></i>Retomar
                </button>
                <button id="btn-usar-foto" class="btn btn-success d-none" onclick="usarFoto()">
                    <i class="mdi mdi-check me-1"></i>Usar esta foto
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 32px 20px;
        text-align: center;
        background: #f8fafc;
        transition: border-color .2s, background .2s;
        cursor: pointer;
    }
    .drop-zone.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .preview-thumb {
        position: relative;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #f8fafc;
    }
    .preview-thumb img {
        width: 100%;
        height: 80px;
        object-fit: cover;
    }
    .preview-thumb .preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        background: #ef4444;
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .preview-thumb .preview-name {
        font-size: 10px;
        padding: 2px 4px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #countdown-display {
        font-size: 2rem;
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Config ───────────────────────────────────────────────────────
const ROUTES = {
    store:    "<?php echo route('productividad.cobranza-sedes.cobranza.store'); ?>",
    historial:"<?php echo route('productividad.cobranza-sedes.cobranza.historial'); ?>",
    kpiData:  "<?php echo route('productividad.cobranza-sedes.cobranza.kpi-data'); ?>",
    base:     "<?php echo url('/productividad/cobranza-sedes/cobranza'); ?>",
};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Helpers de URL dinámicas
const urlShow   = (id) => `${ROUTES.base}/${id}/show`;
const urlUpdate = (id) => `${ROUTES.base}/${id}`;
const urlPreview= (id, idx) => `${ROUTES.base}/${id}/preview/${idx}`;
const urlDownload=(id, idx) => `${ROUTES.base}/${id}/download/${idx}`;

// Helper fetch con manejo de errores claro
async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...(options.headers ?? {}) },
        ...options,
    });
    const contentType = res.headers.get('Content-Type') ?? '';
    if (!contentType.includes('application/json')) {
        throw new Error(`Error ${res.status}: el servidor devolvió una respuesta inesperada.`);
    }
    const data = await res.json();
    if (!res.ok) {
        const msg = data.errors
            ? Object.values(data.errors).flat().join('\n')
            : (data.error ?? data.message ?? `Error ${res.status}`);
        throw new Error(msg);
    }
    return data;
}

// ── Countdown ─────────────────────────────────────────────────────
<?php
    use Carbon\Carbon;
    [$semNum, $anioV, $inicio, $fin, $limite] = App\Models\ReporteCobranza::datosSemanActual();
    $limiteTs = \Carbon\Carbon::parse($limite)->setTimezone('America/Lima')->timestamp * 1000;
?>

const DEADLINE_TS = <?php echo $limiteTs; ?>;

function actualizarCountdown() {
    const ahora   = Date.now();
    const diffMs  = DEADLINE_TS - ahora;
    const card    = document.getElementById('card-countdown');
    const display = document.getElementById('countdown-display');
    const sub     = document.getElementById('countdown-subtitle');
    const icon    = document.getElementById('countdown-icon');

    if (diffMs <= 0) {
        display.textContent = 'VENCIDO';
        sub.textContent     = 'El plazo ya venció';
        display.style.color = '#dc2626';
        icon.className      = 'mdi mdi-alert-circle-outline text-danger';
        card.classList.add('border-danger');
        return;
    }

    const h  = Math.floor(diffMs / 3600000);
    const m  = Math.floor((diffMs % 3600000) / 60000);
    const s  = Math.floor((diffMs % 60000) / 1000);
    const hh = String(h).padStart(2, '0');
    const mm = String(m).padStart(2, '0');
    const ss = String(s).padStart(2, '0');
    display.textContent = `${hh}:${mm}:${ss}`;

    if (diffMs <= 3600000) {          // Menos de 1 hora
        display.style.color = '#dc2626';
        icon.className      = 'mdi mdi-timer-alert-outline text-danger';
        card.classList.add('border-danger');
        sub.textContent     = 'Menos de 1 hora para el cierre';
    } else if (diffMs <= 7200000) {   // Menos de 2 horas
        display.style.color = '#d97706';
        icon.className      = 'mdi mdi-timer-outline text-warning';
        sub.textContent     = 'Menos de 2 horas para el cierre';
    } else {
        display.style.color = '#2563eb';
        icon.className      = 'mdi mdi-timer-outline text-primary';
        sub.textContent     = 'Sábado, 12:00 PM hora Lima';
    }
}

actualizarCountdown();
setInterval(actualizarCountdown, 1000);

// ── Drop zones ────────────────────────────────────────────────────
function setupDropZone(zoneId, inputId, previewId) {
    const zone    = document.getElementById(zoneId);
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!zone || !input) return;

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        agregarArchivos(e.dataTransfer.files, input, preview);
    });
    input.addEventListener('change', () => agregarArchivos(input.files, input, preview));
}

function agregarArchivos(files, input, preview) {
    const dt = new DataTransfer();
    // Preservar existentes
    if (input.files) {
        for (const f of input.files) dt.items.add(f);
    }
    for (const f of files) dt.items.add(f);
    input.files = dt.files;
    renderPreview(input.files, preview, input);
}

function renderPreview(files, preview, input) {
    preview.innerHTML = '';
    for (let i = 0; i < files.length; i++) {
        const f   = files[i];
        const col = document.createElement('div');
        col.className = 'col-6 col-md-3 col-lg-2';
        const isImg = f.type.startsWith('image/');
        col.innerHTML = `
            <div class="p-1 preview-thumb">
                ${isImg
                    ? `<img src="${URL.createObjectURL(f)}" alt="${f.name}">`
                    : `<div class="d-flex align-items-center justify-content-center" style="height:80px;font-size:2rem;">
                            <i class="text-success mdi mdi-file-excel"></i>
                       </div>`
                }
                <button type="button" class="preview-remove" onclick="quitarArchivo(${i}, this)">×</button>
                <div class="text-muted preview-name">${f.name}</div>
            </div>`;
        preview.appendChild(col);
    }
}

function quitarArchivo(idx, btn) {
    const preview = btn.closest('[id^="preview"]');
    const inputId = preview.id.replace('preview', 'archivos');
    const input   = document.getElementById(inputId);
    const dt      = new DataTransfer();
    for (let i = 0; i < input.files.length; i++) {
        if (i !== idx) dt.items.add(input.files[i]);
    }
    input.files = dt.files;
    renderPreview(input.files, preview, input);
}

setupDropZone('drop-zone-nuevo', 'archivos-nuevo', 'preview-nuevo');
setupDropZone('drop-zone-edit',  'archivos-edit',  'preview-edit');

// ── Marcar archivos existentes para eliminar ──────────────────────
function marcarEliminar(idx, btn) {
    const hidden = document.getElementById('eliminar-' + idx);
    const card   = document.getElementById('archivo-card-' + idx);
    if (hidden.disabled) {
        hidden.disabled = false;
        card.style.opacity = '0.4';
        btn.innerHTML = '<i class="mdi mdi-undo"></i>';
        btn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
    } else {
        hidden.disabled = true;
        card.style.opacity = '1';
        btn.innerHTML = '<i class="mdi-trash-can-outline mdi"></i>';
        btn.classList.replace('btn-outline-secondary', 'btn-outline-danger');
    }
}

// ── Envío inicial ─────────────────────────────────────────────────
const formEnviar = document.getElementById('form-enviar-reporte');
if (formEnviar) {
    formEnviar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-enviar');
        const msg = document.getElementById('msg-enviar');
        const fd  = new FormData(this);

        btn.disabled  = true;
        btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Enviando...';
        msg.innerHTML = '';

        try {
            const data = await apiFetch(ROUTES.store, { method: 'POST', body: fd });
            msg.innerHTML = `<div class="py-2 alert alert-success">${data.message}</div>`;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`;
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="me-1 mdi mdi-send"></i>Enviar y Registrar';
        }
    });
}

// ── Edición ───────────────────────────────────────────────────────
const formEditar = document.getElementById('form-editar-reporte');
if (formEditar) {
    formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn       = document.getElementById('btn-editar');
        const msg       = document.getElementById('msg-editar');
        const reporteId = this.querySelector('[name="reporte_id"]').value;

        // FormData ya trae _method=PUT del <?php echo method_field('PUT'); ?> en el form
        const fd = new FormData(this);

        btn.disabled  = true;
        btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>Guardando...';
        msg.innerHTML = '';

        try {
            const data = await apiFetch(urlUpdate(reporteId), { method: 'POST', body: fd });
            const tipo = data.tardio ? 'warning' : 'success';
            msg.innerHTML = `<div class="alert alert-${tipo} py-2">${data.message}</div>`;
            setTimeout(() => location.reload(), 1800);
        } catch (err) {
            msg.innerHTML = `<div class="py-2 alert alert-danger">${err.message}</div>`;
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="me-1 mdi-content-save-edit mdi"></i>Guardar Cambios';
        }
    });
}

// ── Historial ─────────────────────────────────────────────────────
async function cargarHistorial() {
    const tbody = document.getElementById('historial-body');
    const loader = document.getElementById('historial-loader');
    loader.classList.remove('d-none');

    try {
        const data = await apiFetch(ROUTES.historial);
        renderHistorial(data.data ?? []);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-3 text-danger text-center">${err.message}</td></tr>`;
    } finally {
        loader.classList.add('d-none');
    }
}

function badgeEstado(estado) {
    const map = { en_tiempo:'success', con_atraso:'warning', pendiente:'warning', no_enviado:'danger' };
    const labels = { en_tiempo:'En tiempo', con_atraso:'Con atraso', pendiente:'Pendiente', no_enviado:'No enviado' };
    return `<span class="badge bg-${map[estado] ?? 'secondary'}">${labels[estado] ?? estado}</span>`;
}

function renderHistorial(rows) {
    const tbody = document.getElementById('historial-body');

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="py-4 text-muted text-center">Sin registros.</td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map(r => `
        <tr>
            <td><strong>${r.sede}</strong></td>
            <td><span class="bg-light border text-dark badge">${r.semana}</span></td>
            <td class="text-muted small">${r.semana_inicio} — ${r.semana_fin}</td>
            <td class="small">${r.fecha_limite ?? '—'}</td>
            <td class="small">
                ${r.fecha_envio ?? '<span class="text-muted">—</span>'}
                ${r.fecha_edicion ? `<br><span class="text-warning small"><i class="mdi mdi-pencil"></i> ${r.fecha_edicion}</span>` : ''}
            </td>
            <td>
                ${r.kpi !== null
                    ? `<span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>`
                    : '<span class="text-muted">—</span>'
                }
                ${r.editado_tarde ? '<br><small class="text-warning"><i class="mdi mdi-alert"></i> Editado tarde</small>' : ''}
            </td>
            <td>${badgeEstado(r.estado)}</td>
            <td class="text-center">
                ${r.num_archivos > 0 ? `<span class="bg-info badge">${r.num_archivos}</span>` : '<span class="text-muted">0</span>'}
            </td>
            <td>
                <button class="px-2 py-0 btn-outline-primary btn btn-sm" onclick="verReporte(${r.id})" title="Ver detalles">
                    <i class="mdi mdi-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

cargarHistorial();

// ── Modal Ver Reporte ─────────────────────────────────────────────
async function verReporte(id) {
    const modal  = new bootstrap.Modal(document.getElementById('modal-reporte'));
    const body   = document.getElementById('modal-body');
    const titulo = document.getElementById('modal-titulo');

    body.innerHTML     = '<div class="py-4 text-center"><div class="spinner-border text-primary"></div></div>';
    titulo.textContent = 'Detalle del Reporte';
    modal.show();

    try {
        const r = await apiFetch(urlShow(id));
        titulo.textContent = `${r.sede} — ${r.semana}`;

        // Archivos: imágenes con preview inline, otros con ícono
        const archivosHTML = (r.archivos ?? []).length
            ? buildArchivosHTML(r.archivos)
            : '<p class="text-muted">Sin archivos.</p>';

        body.innerHTML = `
            <div class="mb-3 row g-3">
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Sede</div>
                    <div class="fw-bold">${r.sede}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Semana</div>
                    <div class="fw-bold">${r.semana}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Fecha Límite</div>
                    <div>${r.fecha_limite ?? '—'}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Fecha Envío</div>
                    <div>${r.fecha_envio ?? '<span class="text-muted">No enviado</span>'}</div>
                </div>
                ${r.fecha_edicion ? `
                <div class="col-12">
                    <div class="mb-0 py-1 alert alert-warning small">
                        <i class="me-1 mdi mdi-pencil"></i>Editado tardíamente el ${r.fecha_edicion}
                    </div>
                </div>` : ''}
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">KPI</div>
                    <span class="badge bg-${r.kpi_color} fs-6">${r.kpi_label}</span>
                </div>
                <div class="col-6">
                    <div class="text-muted text-uppercase small fw-semibold">Estado</div>
                    <div>${r.estado ?? '—'}</div>
                </div>
                ${r.notas ? `
                <div class="col-12">
                    <div class="text-muted text-uppercase small fw-semibold">Notas</div>
                    <div class="bg-light p-2 border rounded small">${r.notas}</div>
                </div>` : ''}
            </div>
            <hr>
            <h6 class="mb-2 fw-semibold">Archivos adjuntos</h6>
            ${archivosHTML}
        `;
    } catch (err) {
        body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
    }
}

function buildArchivosHTML(archivos) {
    // Separar imágenes de otros archivos
    const imagenes = archivos.filter(a => a.es_imagen);
    const otros    = archivos.filter(a => !a.es_imagen);

    let html = '';

    // Grid de imágenes con preview
    if (imagenes.length) {
        html += `<div class="row g-2 mb-3">`;
        imagenes.forEach(a => {
            html += `
                <div class="col-6 col-md-4">
                    <div class="border rounded overflow-hidden position-relative" style="aspect-ratio:4/3;">
                        <img src="${a.preview_url}" alt="${a.name}"
                             style="width:100%;height:100%;object-fit:cover;cursor:pointer;"
                             onclick="window.open('${a.preview_url}','_blank')"
                             title="Click para ampliar">
                        <div class="position-absolute bottom-0 start-0 end-0 d-flex justify-content-between align-items-center px-2 py-1"
                             style="background:rgba(0,0,0,0.45);">
                            <span class="text-white small text-truncate" style="max-width:70%;">${a.name}</span>
                            <a href="${a.download_url}" class="btn btn-sm btn-light py-0 px-1" title="Descargar" download>
                                <i class="mdi mdi-download small"></i>
                            </a>
                        </div>
                    </div>
                </div>`;
        });
        html += `</div>`;
    }

    // Lista de otros archivos
    otros.forEach(a => {
        const ext  = a.name.split('.').pop().toUpperCase();
        const icon = ['XLSX','XLS','CSV'].includes(ext) ? 'mdi-file-excel text-success'
                   : ext === 'PDF' ? 'mdi-file-pdf text-danger'
                   : 'mdi-file-document text-primary';
        html += `
            <div class="d-flex align-items-center gap-2 bg-light mb-1 p-2 border rounded">
                <i class="mdi ${icon} fs-5"></i>
                <span class="flex-grow-1 text-truncate small fw-semibold">${a.name}</span>
                <a href="${a.download_url}" class="px-2 py-0 btn-outline-primary btn btn-sm" title="Descargar" download>
                    <i class="mdi mdi-download"></i> Descargar
                </a>
            </div>`;
    });

    return html;
}

// ── Cámara ────────────────────────────────────────────────────────
let _streamActivo    = null;
let _camaraInputId   = null;
let _camaraPreviewId = null;
let _fotoBlob        = null;
let _modalCamaraInst = null;

async function abrirCamara(inputId, previewId) {
    _camaraInputId   = inputId;
    _camaraPreviewId = previewId;
    _fotoBlob        = null;

    const video   = document.getElementById('camara-video');
    const preview = document.getElementById('camara-preview-wrap');

    preview.classList.add('d-none');
    video.classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');

    try {
        _streamActivo = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
        video.srcObject = _streamActivo;
    } catch (err) {
        alert('No se pudo acceder a la cámara. Verifica los permisos del navegador.');
        return;
    }

    _modalCamaraInst = new bootstrap.Modal(document.getElementById('modal-camara'));
    _modalCamaraInst.show();
}

function capturarFoto() {
    const video  = document.getElementById('camara-video');
    const canvas = document.getElementById('camara-canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(blob => {
        _fotoBlob = blob;
        const imgSrc = URL.createObjectURL(blob);
        document.getElementById('camara-preview-img').src = imgSrc;
        document.getElementById('camara-preview-wrap').classList.remove('d-none');
        video.classList.add('d-none');
        document.getElementById('btn-capturar').classList.add('d-none');
        document.getElementById('btn-retomar').classList.remove('d-none');
        document.getElementById('btn-usar-foto').classList.remove('d-none');

        // Pausar stream mientras se revisa la foto
        _streamActivo?.getTracks().forEach(t => t.enabled = false);
    }, 'image/jpeg', 0.92);
}

function retomarFoto() {
    _fotoBlob = null;
    _streamActivo?.getTracks().forEach(t => t.enabled = true);
    document.getElementById('camara-preview-wrap').classList.add('d-none');
    document.getElementById('camara-video').classList.remove('d-none');
    document.getElementById('btn-capturar').classList.remove('d-none');
    document.getElementById('btn-retomar').classList.add('d-none');
    document.getElementById('btn-usar-foto').classList.add('d-none');
}

function usarFoto() {
    if (!_fotoBlob) return;
    const ts   = new Date().toISOString().replace(/[:.]/g,'-');
    const file = new File([_fotoBlob], `foto-${ts}.jpg`, { type: 'image/jpeg' });

    const input   = document.getElementById(_camaraInputId);
    const preview = document.getElementById(_camaraPreviewId);
    const dt      = new DataTransfer();
    if (input.files) for (const f of input.files) dt.items.add(f);
    dt.items.add(file);
    input.files = dt.files;
    renderPreview(input.files, preview, input);

    cerrarCamara();
}

function cerrarCamara() {
    _streamActivo?.getTracks().forEach(t => t.stop());
    _streamActivo = null;
    _modalCamaraInst?.hide();
}

// ── Gráfico KPI ───────────────────────────────────────────────────
let kpiChart = null;

async function cargarKpiChart(semanas = 8) {
    try {
        const res  = await fetch(`${ROUTES.kpiData}?semanas=${semanas}`, { headers:{'Accept':'application/json', 'X-CSRF-TOKEN': CSRF} });
        const data = await res.json();

        const ctx  = document.getElementById('kpi-chart').getContext('2d');

        if (kpiChart) kpiChart.destroy();

        kpiChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels:   data.labels ?? [],
                datasets: data.datasets ?? [],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: v => v + '%',
                            stepSize: 10,
                        },
                        grid: { color: '#f1f5f9' },
                    },
                    x: { grid: { display: false } },
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y !== null ? ctx.parsed.y + '%' : 'Sin dato'}`,
                        }
                    },
                    annotation: {
                        annotations: {
                            linea100: {
                                type: 'line',
                                yMin: 100, yMax: 100,
                                borderColor: 'rgba(16,185,129,0.3)',
                                borderWidth: 1,
                                borderDash: [6, 4],
                            }
                        }
                    }
                },
            }
        });
    } catch (e) {
        console.error('Error cargando KPI chart:', e);
    }
}

cargarKpiChart(8);

document.getElementById('filtro-semanas')?.addEventListener('change', function() {
    cargarKpiChart(parseInt(this.value));
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/productividad/cobranza-sedes/cobranza.blade.php ENDPATH**/ ?>