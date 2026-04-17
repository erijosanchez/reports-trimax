<?php $__env->startSection('title', 'Descuentos Especiales'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="mdi mdi-sale"></i> Descuentos Especiales
                            </h3>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-lg" id="btnNuevoDescuento">
                                <i class="mdi mdi-plus"></i> Agregar Nuevo Descuento
                            </button>
                        </div>
                    </div>

                    
                    <div class="pt-3">
                        <ul class="nav nav-tabs" id="descuentosTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tabDescuentos" role="tab">
                                    <i class="mdi-table mdi"></i> Descuentos
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tabEstadisticasDescLink" data-bs-toggle="tab" href="#tabEstadisticasDesc" role="tab">
                                    <i class="mdi mdi-chart-bar"></i> Estadísticas
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-3 tab-content">
                        <div class="tab-pane fade show active" id="tabDescuentos" role="tabpanel">

                            
                            <div class="mb-4 row">
                                <div class="pb-2 col-md-3">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Total Descuentos</p>
                                                    <h2 class="mb-0 font-weight-bold text-primary" id="totalDescuentos">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="bg-primary icon-stat">
                                                    <i class="mdi mdi-sale"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pb-2 col-md-3">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Aprobados</p>
                                                    <h2 class="mb-0 font-weight-bold text-success" id="descuentosAprobados">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="bg-success icon-stat">
                                                    <i class="mdi mdi-check-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pb-2 col-md-3">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Pendientes</p>
                                                    <h2 class="mb-0 font-weight-bold text-warning"
                                                        id="descuentosPendientes">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="bg-warning icon-stat">
                                                    <i class="mdi mdi-clock-alert"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pb-2 col-md-3">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Rechazados</p>
                                                    <h2 class="mb-0 font-weight-bold text-danger" id="descuentosRechazados">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="bg-danger icon-stat">
                                                    <i class="mdi mdi-alert-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="grid-margin col-lg-12 stretch-card">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            
                                            <div class="mb-3 row">
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-account"></i> Consultor
                                                    </label>
                                                    <select class="form-select" id="filtroUsuario">
                                                        <option value="">Todos los usuarios</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label"><i class="mdi mdi-office-building"></i>
                                                        Sede</label>
                                                    <select class="form-select" id="filtroSede">
                                                        <option value="">Todas las sedes</option>
                                                        <option value="LOS OLIVOS">LOS OLIVOS</option>
                                                        <option value="AREQUIPA">AREQUIPA</option>
                                                        <option value="TRUJILLO">TRUJILLO</option>
                                                        <option value="CUSCO">CUSCO</option>
                                                        <option value="PIURA">PIURA</option>
                                                        <option value="CHICLAYO">CHICLAYO</option>
                                                        <option value="ICA">ICA</option>
                                                        <option value="HUANCAYO">HUANCAYO</option>
                                                        <option value="CAILLOMA">CAILLOMA</option>
                                                        <option value="SJM">SJM</option>
                                                        <option value="COMAS">COMAS</option>
                                                        <option value="LINCE">LINCE</option>
                                                        <option value="HUARAZ">HUARAZ</option>
                                                        <option value="AYACUCHO">AYACUCHO</option>
                                                        <option value="HUANUCO">HUANUCO</option>
                                                        <option value="CHIMBOTE">CHIMBOTE</option>
                                                        <option value="PUENTE PIEDRA">PUENTE PIEDRA</option>
                                                        <option value="NAPO">NAPO</option>
                                                        <option value="TACNA">TACNA</option>
                                                        <option value="ATE">ATE</option>
                                                        <option value="CALL CENTER">CALL CENTER</option>
                                                        <option value="IQUITOS">IQUITOS</option>
                                                        <option value="CAJAMARCA">CAJAMARCA</option>
                                                        <option value="PUCALLPA">PUCALLPA</option>
                                                        <option value="SJL">SJL</option>
                                                        <option value="TODOS">TODOS</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label"><i class="mdi mdi-flag"></i>
                                                        Aplicado</label>
                                                    <select class="form-select" id="filtroAplicado">
                                                        <option value="">Todos</option>
                                                        <option value="Pendiente">Pendiente</option>
                                                        <option value="Aprobado">Aprobado</option>
                                                        <option value="Rechazado">Rechazado</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label"><i class="mdi mdi-flag"></i>
                                                        Aprobado</label>
                                                    <select class="form-select" id="filtroAprobado">
                                                        <option value="">Todos</option>
                                                        <option value="Pendiente">Pendiente</option>
                                                        <option value="Aprobado">Aprobado</option>
                                                        <option value="Rechazado">Rechazado</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-magnify"></i>
                                                        Buscar</label>
                                                    <div class="input-group input-group-lg">
                                                        <input type="text" class="form-control" id="buscarGeneral"
                                                            placeholder="# Descuento, RUC...">
                                                        <button class="btn btn-primary" type="button" id="btnBuscar">
                                                            <i class="mdi mdi-magnify"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4 row">
                                                <div class="d-flex align-items-center justify-content-between col-md-12">
                                                    <div>
                                                        <button class="btn-outline-primary btn" id="btnLimpiarFiltros">
                                                            <i class="mdi-filter-remove mdi"></i> Limpiar Filtros
                                                        </button>
                                                        <button class="btn btn-info" id="btnRecargar">
                                                            <i class="mdi mdi-refresh"></i> Recargar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div id="loadingSpinner" class="py-5 text-center">
                                                <div class="spinner-border text-primary" role="status"
                                                    style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3 text-muted">Cargando descuentos especiales...</p>
                                            </div>

                                            
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            
                                            <div id="tablaContainer" style="display: none;">
                                                <div class="table-responsive"
                                                    style="overflow-x: auto; margin: 0 -1.5rem; padding: 0 1.5rem;">
                                                    <table class="table table-hover align-middle" id="tablaDescuentos"
                                                        style="min-width: 2500px;">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th width="50">#</th>
                                                                <th style="min-width: 130px;">N° Descuento</th>
                                                                <th style="min-width: 130px;" class="bg-warning text-dark">Aplicado</th>
                                                                <th style="min-width: 130px;" class="bg-info text-white">Aprobado</th>
                                                                <th style="min-width: 130px;">N° Factura</th>
                                                                <th style="min-width: 130px;">N° Orden</th>
                                                                <th style="min-width: 120px;">Sede</th>
                                                                <th style="min-width: 130px;">RUC</th>
                                                                <th style="min-width: 250px;">Razón Social</th>
                                                                <th style="min-width: 150px;">Consultor</th>
                                                                <th style="min-width: 130px;">Ciudad</th>
                                                                <th style="min-width: 220px;">Descuento Especial</th>
                                                                <th style="min-width: 180px;">Tipo</th>
                                                                <th style="min-width: 130px;">Marca</th>
                                                                <th style="min-width: 130px;">AR</th>
                                                                <th style="min-width: 130px;">Diseños</th>
                                                                <th style="min-width: 130px;">Material</th>
                                                                <th style="min-width: 110px;">Fecha Registro</th>
                                                                <th style="min-width: 150px;">Creado Por</th>
                                                                <th style="min-width: 200px;" class="text-center">Acciones
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tablaDescuentosBody">
                                                            
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                            <div id="paginacionContainer" class="mt-3 px-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="tabEstadisticasDesc" role="tabpanel">
                            <div class="mt-3 row">

                                
                                <div class="mb-4 col-md-6">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-account-star"></i> Ranking de Descuentos por Consultor
                                            </h6>
                                            <div style="position:relative; height:320px;">
                                                <canvas id="chartDescUsuarios"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 col-md-6">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-chart-line"></i> Total de Descuentos por Mes
                                            </h6>
                                            <div style="position:relative; height:320px;">
                                                <canvas id="chartDescPorMes"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-4 col-md-6">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-office-building"></i> Total de Descuentos por Sede
                                            </h6>
                                            <div style="position:relative; height:340px;">
                                                <canvas id="chartDescPorSede"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 col-md-6">
                                    <div class="shadow-sm h-100 card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-chart-bar-stacked"></i> Descuentos por Sede y Mes
                                                <small class="text-muted">(Top 8 sedes)</small>
                                            </h6>
                                            <div style="position:relative; height:340px;">
                                                <canvas id="chartDescSedePorMes"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-4 col-md-12">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                                <h6 class="mb-0 text-primary">
                                                    <i class="mdi-account-group mdi"></i> Descuentos por Consultor en el Mes
                                                </h6>
                                                <select id="filtroMesConsultorDesc" class="form-select-sm form-select" style="width:180px;">
                                                </select>
                                            </div>
                                            <div style="position:relative; height:300px;">
                                                <canvas id="chartDescConsultorMes"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-4 col-md-12">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                                <h6 class="mb-0 text-primary">
                                                    <i class="mdi mdi-account-clock"></i> Descuentos por Mes de un Consultor
                                                </h6>
                                                <select id="filtroConsultorGraficoDesc" class="form-select-sm form-select" style="width:220px;">
                                                    <option value="">— Todos los consultores —</option>
                                                </select>
                                            </div>
                                            <div style="position:relative; height:260px;">
                                                <canvas id="chartDescMesPorConsultor"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mb-4 col-md-12">
                                    <div class="shadow-sm card">
                                        <div class="card-body">
                                            <h6 class="mb-3 text-primary">
                                                <i class="mdi mdi-trophy"></i> Top 15 — Tipos de Descuento Especial más Frecuentes
                                            </h6>
                                            <div style="position:relative; height:360px;">
                                                <canvas id="chartTopDescuentos"></canvas>
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

    
    <div class="modal fade" id="modalDescuento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="bg-primary text-white modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-document-edit"></i>
                        <span id="modalTitle">Nuevo Descuento Especial</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDescuento" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row">
                            
                            <div class="col-md-6">
                                <h6 class="mb-3 text-primary"><i class="mdi mdi-account-circle"></i> Datos del Cliente
                                </h6>

                                
                                <div class="mb-3">
                                    <label class="form-label">N° Factura</label>
                                    <input type="text" class="form-control" name="numero_factura"
                                        placeholder="Ej: F001-00012345">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">N° Orden</label>
                                    <input type="text" class="form-control" name="numero_orden"
                                        placeholder="Ej: ORD-2025-00123">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Sede <span class="text-danger">*</span></label>
                                    <select class="form-select" name="sede" required>
                                        <option value="">Seleccionar sede</option>
                                        <option value="LOS OLIVOS">LOS OLIVOS</option>
                                        <option value="AREQUIPA">AREQUIPA</option>
                                        <option value="TRUJILLO">TRUJILLO</option>
                                        <option value="CUSCO">CUSCO</option>
                                        <option value="PIURA">PIURA</option>
                                        <option value="CHICLAYO">CHICLAYO</option>
                                        <option value="JUNIN">JUNIN</option>
                                        <option value="ICA">ICA</option>
                                        <option value="HUANCAYO">HUANCAYO</option>
                                        <option value="CALLAO">CALLAO</option>
                                        <option value="VENTANILLA">VENTANILLA</option>
                                        <option value="CAILLOMA">CAILLOMA</option>
                                        <option value="SJM">SJM</option>
                                        <option value="COMAS">COMAS</option>
                                        <option value="LINCE">LINCE</option>
                                        <option value="HUARAZ">HUARAZ</option>
                                        <option value="AYACUCHO">AYACUCHO</option>
                                        <option value="HUANUCO">HUANUCO</option>
                                        <option value="CHIMBOTE">CHIMBOTE</option>
                                        <option value="PUENTE PIEDRA">PUENTE PIEDRA</option>
                                        <option value="NAPO">NAPO</option>
                                        <option value="TACNA">TACNA</option>
                                        <option value="ATE">ATE</option>
                                        <option value="CALL CENTER">CALL CENTER</option>
                                        <option value="IQUITOS">IQUITOS</option>
                                        <option value="CAJAMARCA">CAJAMARCA</option>
                                        <option value="PUCALLPA">PUCALLPA</option>
                                        <option value="SJL">SJL</option>
                                        <option value="TODOS">TODOS</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">RUC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ruc" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="razon_social" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Consultor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="consultor" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ciudad" required>
                                </div>
                            </div>

                            
                            <div class="col-md-6">
                                <h6 class="mb-3 text-primary"><i class="mdi mdi-package-variant"></i> Detalles del
                                    Descuento
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">Descuento Especial <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="descuento_especial" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="ANULACION">ANULACION</option>
                                        <option value="CORTESIA">CORTESIA</option>
                                        <option value="DESCUENTO ADICIONAL">DESCUENTO ADICIONAL</option>
                                        <option value="DESCUENTO TOTAL">DESCUENTO TOTAL</option>
                                        <option value="OTROS">OTROS</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Marca <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="marca" placeholder="Ej: DEVABLUE"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">AR</label>
                                    <input type="text" class="form-control" name="ar"
                                        placeholder="Ej: DEVABLUE">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Diseños</label>
                                    <input type="text" class="form-control" name="disenos" placeholder="Ej: TODOS">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Material</label>
                                    <input type="text" class="form-control" name="material" placeholder="Ej: TODOS">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 row">
                            <div class="col-md-12">
                                <h6 class="mb-3 text-primary"><i class="mdi mdi-paperclip"></i> Archivos y Comentarios
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Archivos Adjuntos</label>
                                    <input type="file" class="form-control" name="archivos[]" multiple
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <small class="text-muted">Máximo 10MB por archivo</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Comentarios <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="comentarios" rows="3" placeholder="Observaciones adicionales" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarDescuento">
                            <i class="mdi-content-save mdi"></i> Guardar Descuento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="bg-info text-white modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-information"></i> Detalles del Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesDescuentoContent">
                    
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalDeshabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="bg-danger text-white modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-cancel"></i> Deshabilitar Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDeshabilitar">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="descuentoIdDeshabilitar">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            Esta acción deshabilitará el descuento especial y enviará notificaciones a los responsables.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la Deshabilitación <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo" rows="4" required
                                placeholder="Explique detalladamente el motivo por el cual se deshabilita este descuento..."></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmarDeshabilitar">
                            <i class="mdi mdi-cancel"></i> Deshabilitar Descuento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalRehabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="bg-success text-white modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-circle"></i> Rehabilitar Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRehabilitar">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="descuentoIdRehabilitar">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            Esta acción rehabilitará el descuento especial y enviará notificaciones a los responsables.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la Rehabilitación <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo" rows="4" required
                                placeholder="Explique detalladamente el motivo por el cual se rehabilita este descuento..."></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnConfirmarRehabilitar">
                            <i class="mdi mdi-check-circle"></i> Rehabilitar Descuento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <style>
        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .badge-deshabilitado {
            background-color: #6B7280;
            color: white;
        }

        .badge-rechazado {
            background-color: #EF4444;
            color: white;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .icon-stat {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .icon-stat.bg-primary {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        }

        .icon-stat.bg-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }

        .icon-stat.bg-warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .icon-stat.bg-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        .table thead th {
            background-color: #1e3a8a !important;
            color: white !important;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: background-color 0.15s;
        }

        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }

        .table tbody td {
            padding: 0.875rem 0.75rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .badge-estado {
            padding: 0.5rem 0.9rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 6px;
        }

        .badge-pendiente {
            background-color: #fcd914;
            color: white;
        }

        .badge-aprobado {
            background-color: #10B981;
            color: white;
        }

        .badge-rechazado {
            background-color: #EF4444;
            color: white;
        }

        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .modal-xl {
            max-width: 1200px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let descuentosData = [];
        let descuentosActuales = [];
        let currentPage = 1;
        const PER_PAGE = 20;
        const userEmail = "<?php echo Auth::user()->email; ?>";
        const canAplicar = userEmail === 'auditor.junior@trimaxperu.com';
        const canApprove = userEmail === 'smonopoli@trimaxperu.com' || userEmail ===
            'planeamiento.comercial@trimaxperu.com';
        const canManageDescuentos = userEmail === 'smonopoli@trimaxperu.com' || userEmail ===
            'planeamiento.comercial@trimaxperu.com';

        $(document).ready(function() {
            cargarDescuentos();
            cargarUsuarios();

            // Eventos de filtros
            $('#filtroUsuario, #filtroSede, #filtroAplicado, #filtroAprobado').on('change', function() {
                aplicarFiltros();
            });

            $('#btnBuscar').on('click', function() {
                aplicarFiltros();
            });

            $('#buscarGeneral').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    aplicarFiltros();
                }
            });

            $('#btnLimpiarFiltros').on('click', function() {
                $('#filtroUsuario').val('');
                $('#filtroSede').val('');
                $('#filtroAplicado').val('');
                $('#filtroAprobado').val('');
                $('#buscarGeneral').val('');
                cargarDescuentos();
            });

            $('#btnRecargar').on('click', function() {
                cargarDescuentos();
            });

            // Modal nuevo descuento
            $('#btnNuevoDescuento').on('click', function() {
                $('#formDescuento')[0].reset();
                $('#modalTitle').text('Nuevo Descuento Especial');
                $('#modalDescuento').modal('show');
            });

            // Submit formularios
            $('#formDescuento').on('submit', function(e) {
                e.preventDefault();
                crearDescuento();
            });

            $('#formDeshabilitar').on('submit', function(e) {
                e.preventDefault();
                deshabilitarDescuento();
            });

            $('#formRehabilitar').on('submit', function(e) {
                e.preventDefault();
                rehabilitarDescuento();
            });
        });

        /**
         * Cargar usuarios creadores para el select
         */
        function cargarUsuarios() {
            $.ajax({
                url: "<?php echo route('comercial.descuentos.usuarios'); ?>",
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">Todos los usuarios</option>';
                        response.data.forEach(usuario => {
                            options += `<option value="${usuario.id}">${usuario.name}</option>`;
                        });
                        $('#filtroUsuario').html(options);
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar usuarios:', xhr);
                }
            });
        }

        /**
         * Cargar descuentos
         */
        function cargarDescuentos() {
            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();

            $.ajax({
                url: "<?php echo route('comercial.descuentos.obtener'); ?>",
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        descuentosData = response.data;
                        console.log('✅ Descuentos cargados:', descuentosData.length);

                        actualizarEstadisticas();
                        renderizarTabla();

                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error de conexión';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    mostrarError(mensaje);
                    console.error('❌ Error:', xhr);
                }
            });
        }

        /**
         * Aplicar filtros
         */
        function aplicarFiltros() {
            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();

            const filtroUsuario = $('#filtroUsuario').val();
            const filtroSede = $('#filtroSede').val();
            const filtroAplicado = $('#filtroAplicado').val(); // 🔥 CAMBIO
            const filtroAprobado = $('#filtroAprobado').val();
            const busqueda = $('#buscarGeneral').val().toLowerCase().trim();

            let descuentosFiltrados = descuentosData;

            if (filtroUsuario) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.creador && d.creador.id == filtroUsuario
                );
            }

            if (filtroSede) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.sede === filtroSede
                );
            }

            // 🔥 CAMBIO: Filtro por aplicado
            if (filtroAplicado) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.aplicado === filtroAplicado
                );
            }

            if (filtroAprobado) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.aprobado === filtroAprobado
                );
            }

            if (busqueda) {
                descuentosFiltrados = descuentosFiltrados.filter(d => {
                    const numero = (d.numero_descuento || '').toLowerCase();
                    const ruc = (d.ruc || '').toLowerCase();
                    const razon = (d.razon_social || '').toLowerCase();
                    const consultor = (d.consultor || '').toLowerCase();
                    const descuento = (d.descuento_especial || '').toLowerCase();
                    const ciudad = (d.ciudad || '').toLowerCase();
                    const numFactura = (d.numero_factura || '').toLowerCase();
                    const numOrden = (d.numero_orden || '').toLowerCase();

                    return numero.includes(busqueda) ||
                        ruc.includes(busqueda) ||
                        razon.includes(busqueda) ||
                        consultor.includes(busqueda) ||
                        descuento.includes(busqueda) ||
                        ciudad.includes(busqueda) ||
                        numFactura.includes(busqueda) ||
                        numOrden.includes(busqueda);
                });
            }

            const descuentosOriginal = descuentosData;
            descuentosData = descuentosFiltrados;
            actualizarEstadisticas();
            descuentosData = descuentosOriginal;

            descuentosActuales = [...descuentosFiltrados];
            currentPage = 1;
            renderizarPagina();

            $('#loadingSpinner').hide();
            $('#tablaContainer').show();
        }

        /**
         * Actualizar estadísticas
         */
        function actualizarEstadisticas() {
            const total = descuentosData.length;
            const aprobados = descuentosData.filter(d => d.aplicado === 'Aprobado' && d.aprobado === 'Aprobado').length;
            const pendientes = descuentosData.filter(d => d.aplicado === 'Pendiente' || d.aprobado === 'Pendiente').length;
            const rechazados = descuentosData.filter(d => d.aplicado === 'Rechazado' || d.aprobado === 'Rechazado').length;

            $('#totalDescuentos').text(total);
            $('#descuentosAprobados').text(aprobados);
            $('#descuentosPendientes').text(pendientes);
            $('#descuentosRechazados').text(rechazados);
        }

        /**
         * Renderizar tabla (captura datos actuales y va a página 1)
         */
        function renderizarTabla() {
            descuentosActuales = [...descuentosData];
            currentPage = 1;
            renderizarPagina();
        }

        /**
         * Renderizar la página actual
         */
        function renderizarPagina() {
            const inicio = (currentPage - 1) * PER_PAGE;
            const paginados = descuentosActuales.slice(inicio, inicio + PER_PAGE);
            let html = '';

            if (descuentosActuales.length === 0) {
                html = `
                    <tr>
                        <td colspan="20" class="py-5 text-center">
                            <i class="mdi-file-document-outline text-muted mdi mdi-48px"></i>
                            <p class="mt-3 text-muted">No se encontraron descuentos especiales</p>
                        </td>
                    </tr>
                `;
            } else {
                paginados.forEach((descuento, index) => {
                    const numFila = inicio + index + 1;
                    const badgeAplicado = obtenerBadgeAprobacion(descuento.aplicado);
                    const badgeAprobado = obtenerBadgeAprobacion(descuento.aprobado);

                    const esDeshabilitado = !descuento.habilitado;
                    const esCreador = descuento.creador && descuento.creador.id == <?php echo Auth::id(); ?>;
                    const puedeEditar = esCreador || canManageDescuentos;

                    html += `
                <tr ${esDeshabilitado ? 'class="table-secondary"' : ''}>
                    <td class="text-center">${numFila}</td>
                    <td><a href="#" onclick="verDetalles(${descuento.id}); return false;" title="Ver detalles" class="text-primary fw-bold text-decoration-none">${descuento.numero_descuento}</a></td>
                    <td>
                        ${badgeAplicado}
                        ${canAplicar && descuento.aplicado === 'Pendiente' && !esDeshabilitado ? `
                            <div class="btn-group mt-1" role="group">
                                <button class="btn btn-sm btn-success" onclick="aplicarDescuento(${descuento.id}, 'Aprobado')">
                                    <i class="mdi mdi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="aplicarDescuento(${descuento.id}, 'Rechazado')">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        ` : ''}
                        ${canAplicar && (descuento.aplicado === 'Aprobado' || descuento.aplicado === 'Rechazado') && !esDeshabilitado ? `
                            <button class="mt-1 btn-outline-info btn btn-sm" onclick="cambiarAplicacion(${descuento.id})" title="Cambiar aplicación">
                                <i class="mdi mdi-swap-horizontal"></i>
                            </button>
                        ` : ''}
                    </td>
                    <td>
                        ${badgeAprobado}
                        ${canApprove && descuento.aprobado === 'Pendiente' && !esDeshabilitado ? `
                            <div class="btn-group mt-1" role="group">
                                <button class="btn btn-sm btn-success" onclick="aprobarDescuento(${descuento.id}, 'Aprobado')">
                                    <i class="mdi mdi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="aprobarDescuento(${descuento.id}, 'Rechazado')">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        ` : ''}
                        ${canApprove && (descuento.aprobado === 'Aprobado' || descuento.aprobado === 'Rechazado') && !esDeshabilitado ? `
                            <button class="mt-1 btn-outline-info btn btn-sm" onclick="cambiarAprobacion(${descuento.id})" title="Cambiar aprobación">
                                <i class="mdi mdi-swap-horizontal"></i>
                            </button>
                        ` : ''}
                    </td>
                    <td>${descuento.numero_factura || '-'}</td>
                    <td>${descuento.numero_orden || '-'}</td>
                    <td>${descuento.sede}</td>
                    <td>${descuento.ruc}</td>
                    <td title="${descuento.razon_social}"><strong>${descuento.razon_social}</strong></td>
                    <td>${descuento.consultor}</td>
                    <td>${descuento.ciudad}</td>
                    <td title="${descuento.descuento_especial}">${truncar(descuento.descuento_especial, 70)}</td>
                    <td><span class="bg-primary badge">${descuento.tipo}</span></td>
                    <td>${descuento.marca}</td>
                    <td>${descuento.ar || '-'}</td>
                    <td>${descuento.disenos || '-'}</td>
                    <td>${descuento.material || '-'}</td>
                    <td><small>${formatearFecha(descuento.created_at)}</small></td>
                    <td>${descuento.creador ? descuento.creador.name : '-'}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            ${puedeEditar && !esDeshabilitado ? `
                                <button class="btn btn-sm btn-warning" onclick="editarDescuento(${descuento.id})" title="Editar">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                            ` : ''}
                            ${canManageDescuentos && !esDeshabilitado ? `
                                <button class="btn btn-sm btn-danger" onclick="abrirModalDeshabilitar(${descuento.id})" title="Deshabilitar">
                                    <i class="mdi mdi-cancel"></i>
                                </button>
                            ` : ''}
                            ${canManageDescuentos && esDeshabilitado ? `
                                <button class="btn btn-sm btn-success" onclick="abrirModalRehabilitar(${descuento.id})" title="Rehabilitar">
                                    <i class="mdi mdi-check-circle"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
                });
            }

            $('#tablaDescuentosBody').html(html);
            renderizarPaginacion();
        }

        /**
         * Renderizar controles de paginación
         */
        function renderizarPaginacion() {
            const total = descuentosActuales.length;
            const totalPaginas = Math.ceil(total / PER_PAGE);

            if (total === 0) { $('#paginacionContainer').html(''); return; }

            const inicio = (currentPage - 1) * PER_PAGE + 1;
            const fin = Math.min(currentPage * PER_PAGE, total);

            let html = `<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <small class="text-muted">Mostrando <strong>${inicio}–${fin}</strong> de <strong>${total}</strong> descuento(s)</small>
                <nav aria-label="Paginación"><ul class="mb-0 pagination pagination-sm">`;

            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="irAPagina(event,${currentPage - 1})"><i class="mdi-chevron-left mdi"></i></a></li>`;

            let startP = Math.max(1, currentPage - 2);
            let endP   = Math.min(totalPaginas, startP + 4);
            if (endP - startP < 4) startP = Math.max(1, endP - 4);

            if (startP > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="irAPagina(event,1)">1</a></li>`;
                if (startP > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            }
            for (let p = startP; p <= endP; p++) {
                html += `<li class="page-item ${p === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="irAPagina(event,${p})">${p}</a></li>`;
            }
            if (endP < totalPaginas) {
                if (endP < totalPaginas - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="#" onclick="irAPagina(event,${totalPaginas})">${totalPaginas}</a></li>`;
            }

            html += `<li class="page-item ${currentPage >= totalPaginas ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="irAPagina(event,${currentPage + 1})"><i class="mdi-chevron-right mdi"></i></a></li>`;
            html += `</ul></nav></div>`;
            $('#paginacionContainer').html(html);
        }

        function irAPagina(e, pagina) {
            e.preventDefault();
            const totalPaginas = Math.ceil(descuentosActuales.length / PER_PAGE);
            if (pagina < 1 || pagina > totalPaginas) return;
            currentPage = pagina;
            renderizarPagina();
            $('html, body').animate({ scrollTop: $('#tablaDescuentos').offset().top - 120 }, 200);
        }

        /**
         * Crear descuento
         */
        function crearDescuento() {
            const descuentoId = $('#formDescuento').attr('data-descuento-id');
            const formData = new FormData($('#formDescuento')[0]);

            let url = "<?php echo route('comercial.descuentos.crear'); ?>";
            let method = 'POST';

            if (descuentoId) {
                url = `/comercial/descuentos-especiales/${descuentoId}/editar`;
                formData.append('_method', 'PUT');
            }

            $('#btnGuardarDescuento').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalDescuento').modal('hide');

                        let mensaje = response.message;
                        if (descuentoId) {
                            mensaje += '\n\n⚠️ Las validaciones y aprobaciones se han reseteado a Pendiente.';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: mensaje,
                            confirmButtonColor: '#3B82F6'
                        });

                        $('#formDescuento').removeAttr('data-descuento-id');
                        cargarDescuentos();
                        cargarUsuarios();
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al guardar descuento';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje,
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnGuardarDescuento').prop('disabled', false).html(
                        '<i class="mdi-content-save mdi"></i> Guardar Descuento');
                }
            });
        }

        /**
         * Aplicar descuento (Auditor Junior)
         */
        function aplicarDescuento(id, accion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${accion === 'Aprobado' ? 'APLICAR' : 'RECHAZAR'} este descuento?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: accion === 'Aprobado' ? '#10B981' : '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/descuentos-especiales/${id}/aplicar`,
                        method: 'POST',
                        data: {
                            _token: '<?php echo csrf_token(); ?>',
                            accion: accion
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Aplicado!',
                                    text: response.message,
                                    confirmButtonColor: '#3B82F6'
                                });
                                cargarDescuentos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al aplicar descuento',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Aprobar descuento
         */
        function aprobarDescuento(id, accion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${accion === 'Aprobado' ? 'APROBAR' : 'RECHAZAR'} este descuento?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: accion === 'Aprobado' ? '#10B981' : '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/descuentos-especiales/${id}/aprobar`,
                        method: 'POST',
                        data: {
                            _token: '<?php echo csrf_token(); ?>',
                            accion: accion
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Aprobado!',
                                    text: response.message,
                                    confirmButtonColor: '#3B82F6'
                                });
                                cargarDescuentos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al aprobar descuento',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Abrir modal deshabilitar
         */
        function abrirModalDeshabilitar(id) {
            if (!canManageDescuentos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para deshabilitar descuentos',
                    confirmButtonColor: '#EF4444'
                });
                return;
            }

            $('#descuentoIdDeshabilitar').val(id);
            $('#formDeshabilitar')[0].reset();
            $('#modalDeshabilitar').modal('show');
        }

        /**
         * Deshabilitar descuento
         */
        function deshabilitarDescuento() {
            const id = $('#descuentoIdDeshabilitar').val();
            const motivo = $('textarea[name="motivo"]', '#formDeshabilitar').val();

            if (motivo.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Motivo insuficiente',
                    text: 'El motivo debe tener al menos 10 caracteres',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }

            $('#btnConfirmarDeshabilitar').prop('disabled', true).html(
                '<i class="mdi mdi-loading mdi-spin"></i> Procesando...');

            $.ajax({
                url: `/comercial/descuentos-especiales/${id}/deshabilitar`,
                method: 'POST',
                data: {
                    _token: '<?php echo csrf_token(); ?>',
                    motivo: motivo
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalDeshabilitar').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Deshabilitado!',
                            text: response.message,
                            confirmButtonColor: '#3B82F6'
                        });
                        cargarDescuentos();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al deshabilitar descuento',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnConfirmarDeshabilitar').prop('disabled', false).html(
                        '<i class="mdi mdi-cancel"></i> Deshabilitar Descuento');
                }
            });
        }

        /**
         * Abrir modal rehabilitar
         */
        function abrirModalRehabilitar(id) {
            if (!canManageDescuentos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para rehabilitar descuentos',
                    confirmButtonColor: '#EF4444'
                });
                return;
            }

            $('#descuentoIdRehabilitar').val(id);
            $('#formRehabilitar')[0].reset();
            $('#modalRehabilitar').modal('show');
        }

        /**
         * Rehabilitar descuento
         */
        function rehabilitarDescuento() {
            const id = $('#descuentoIdRehabilitar').val();
            const motivo = $('textarea[name="motivo"]', '#formRehabilitar').val();

            if (motivo.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Motivo insuficiente',
                    text: 'El motivo debe tener al menos 10 caracteres',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }

            $('#btnConfirmarRehabilitar').prop('disabled', true).html(
                '<i class="mdi mdi-loading mdi-spin"></i> Procesando...');

            $.ajax({
                url: `/comercial/descuentos-especiales/${id}/rehabilitar`,
                method: 'POST',
                data: {
                    _token: '<?php echo csrf_token(); ?>',
                    motivo: motivo
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalRehabilitar').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Rehabilitado!',
                            text: response.message,
                            confirmButtonColor: '#3B82F6'
                        });
                        cargarDescuentos();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al rehabilitar descuento',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnConfirmarRehabilitar').prop('disabled', false).html(
                        '<i class="mdi mdi-check-circle"></i> Rehabilitar Descuento');
                }
            });
        }

        /**
         * Ver detalles
         */
        function verDetalles(id) {
            const descuento = descuentosData.find(d => d.id === id);
            if (!descuento) return;

            let archivosHTML = '';
            if (descuento.archivos_adjuntos && descuento.archivos_adjuntos.length > 0) {
                archivosHTML = '<ul class="list-group">';
                descuento.archivos_adjuntos.forEach((archivo, index) => {
                    archivosHTML += `
                <li class="list-group-item d-flex align-items-center justify-content-between">
                    <span><i class="mdi mdi-file-document"></i> ${archivo.nombre}</span>
                    <a href="/comercial/descuentos-especiales/${descuento.id}/archivo/${index}" class="btn btn-sm btn-primary" download>
                        <i class="mdi mdi-download"></i> Descargar
                    </a>
                </li>
            `;
                });
                archivosHTML += '</ul>';
            } else {
                archivosHTML = '<p class="text-muted">No hay archivos adjuntos</p>';
            }

            let historialHTML = '';
            if (descuento.motivo_deshabilitacion) {
                historialHTML += `
                <div class="alert alert-danger">
                    <h6><i class="mdi mdi-cancel"></i> Deshabilitado</h6>
                    <p><strong>Motivo:</strong> ${descuento.motivo_deshabilitacion}</p>
                    <p><strong>Por:</strong> ${descuento.deshabilitador?.name || '-'}</p>
                    <p><strong>Fecha:</strong> ${formatearFecha(descuento.deshabilitado_at)}</p>
                </div>
            `;
            }
            if (descuento.motivo_rehabilitacion) {
                historialHTML += `
                <div class="alert alert-success">
                    <h6><i class="mdi mdi-check-circle"></i> Rehabilitado</h6>
                    <p><strong>Motivo:</strong> ${descuento.motivo_rehabilitacion}</p>
                    <p><strong>Por:</strong> ${descuento.rehabilitador?.name || '-'}</p>
                    <p><strong>Fecha:</strong> ${formatearFecha(descuento.rehabilitado_at)}</p>
                </div>
            `;
            }

            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="mdi mdi-information"></i> Información General</h6>
                        <table class="table table-borderless table-sm">
                            <tr><th>N° Descuento:</th><td><strong>${descuento.numero_descuento}</strong></td></tr>
                            <tr><th>N° Factura:</th><td>${descuento.numero_factura || '-'}</td></tr>
                            <tr><th>N° Orden:</th><td>${descuento.numero_orden || '-'}</td></tr>
                            <tr><th>Razón Social:</th><td>${descuento.razon_social}</td></tr>
                            <tr><th>RUC:</th><td>${descuento.ruc}</td></tr>
                            <tr><th>Sede:</th><td>${descuento.sede}</td></tr>
                            <tr><th>Ciudad:</th><td>${descuento.ciudad}</td></tr>
                            <tr><th>Consultor:</th><td>${descuento.consultor}</td></tr>
                            <tr><th>Fecha Registro:</th><td>${formatearFecha(descuento.created_at)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="mdi mdi-package"></i> Detalles del Descuento</h6>
                        <table class="table table-borderless table-sm">
                            <tr><th>Descuento:</th><td style="max-width: 250px; max-height: 80px; overflow-y: auto; overflow-x: hidden; word-wrap: break-word; white-space: normal;">${descuento.descuento_especial}</td></tr>
                            <tr><th>Tipo:</th><td><span class="bg-secondary badge">${descuento.tipo}</span></td></tr>
                            <tr><th>Marca:</th><td>${descuento.marca}</td></tr>
                            <tr><th>AR:</th><td>${descuento.ar || '-'}</td></tr>
                            <tr><th>Diseños:</th><td>${descuento.disenos || '-'}</td></tr>
                            <tr><th>Material:</th><td>${descuento.material || '-'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="mt-3 row">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="mdi mdi-check-circle"></i> Aprobaciones</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Aplicado:</th>
                                <td>${obtenerBadgeAprobacion(descuento.aplicado)}</td>
                                <td>${descuento.aplicador ? 'Por: ' + descuento.aplicador.name : ''}</td>
                            </tr>
                            <tr>
                                <th>Aprobado:</th>
                                <td>${obtenerBadgeAprobacion(descuento.aprobado)}</td>
                                <td>${descuento.aprobador ? 'Por: ' + descuento.aprobador.name : ''}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                ${historialHTML ? `
                                    <div class="mt-3 row">
                                        <div class="col-md-12">
                                            <h6 class="text-primary"><i class="mdi mdi-history"></i> Historial</h6>
                                            ${historialHTML}
                                        </div>
                                    </div>
                                ` : ''}
                <div class="mt-3 row">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="mdi mdi-comment-text"></i> Comentarios</h6>
                        <p>${descuento.comentarios || 'Sin comentarios'}</p>
                    </div>
                </div>
                <div class="mt-3 row">
                    <div class="col-md-12">
                        <h6 class="text-primary"><i class="mdi mdi-paperclip"></i> Archivos Adjuntos</h6>
                        ${archivosHTML}
                    </div>
                </div>
            `;

            $('#detallesDescuentoContent').html(html);
            $('#modalDetalles').modal('show');
        }

        /**
         * Editar descuento
         */
        function editarDescuento(id) {
            const descuento = descuentosData.find(d => d.id === id);
            if (!descuento) return;

            $('input[name="numero_factura"]', '#formDescuento').val(descuento.numero_factura || '');
            $('input[name="numero_orden"]', '#formDescuento').val(descuento.numero_orden || '');
            $('select[name="sede"]', '#formDescuento').val(descuento.sede);
            $('input[name="ruc"]', '#formDescuento').val(descuento.ruc);
            $('input[name="razon_social"]', '#formDescuento').val(descuento.razon_social);
            $('input[name="consultor"]', '#formDescuento').val(descuento.consultor);
            $('input[name="ciudad"]', '#formDescuento').val(descuento.ciudad);
            $('input[name="descuento_especial"]', '#formDescuento').val(descuento.descuento_especial);
            $('select[name="tipo"]', '#formDescuento').val(descuento.tipo);
            $('input[name="marca"]', '#formDescuento').val(descuento.marca);
            $('input[name="ar"]', '#formDescuento').val(descuento.ar || '');
            $('input[name="disenos"]', '#formDescuento').val(descuento.disenos || '');
            $('input[name="material"]', '#formDescuento').val(descuento.material || '');
            $('textarea[name="comentarios"]', '#formDescuento').val(descuento.comentarios || '');

            $('#modalTitle').text('Editar Descuento Especial');
            $('#formDescuento').attr('data-descuento-id', id);

            $('#modalDescuento').modal('show');
        }

        /**
         * Cambiar aplicación
         */
        function cambiarAplicacion(id) {
            const descuento = descuentosData.find(d => d.id === id);
            if (!descuento) return;

            Swal.fire({
                title: 'Cambiar Aplicación',
                text: `Estado actual: ${descuento.aplicado}`,
                input: 'select',
                inputOptions: {
                    'Aprobado': 'Aprobado',
                    'Rechazado': 'Rechazado',
                    'Pendiente': 'Pendiente'
                },
                inputPlaceholder: 'Seleccionar nuevo estado',
                showCancelButton: true,
                confirmButtonText: 'Cambiar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3B82F6',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes seleccionar un estado';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/descuentos-especiales/${id}/cambiar-aplicacion`,
                        method: 'POST',
                        data: {
                            _token: '<?php echo csrf_token(); ?>',
                            nuevo_estado: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Actualizado!',
                                    text: response.message,
                                    confirmButtonColor: '#3B82F6'
                                });
                                cargarDescuentos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Error al cambiar aplicación',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Cambiar aprobación
         */
        function cambiarAprobacion(id) {
            const descuento = descuentosData.find(d => d.id === id);
            if (!descuento) return;

            Swal.fire({
                title: 'Cambiar Aprobación',
                text: `Estado actual: ${descuento.aprobado}`,
                input: 'select',
                inputOptions: {
                    'Aprobado': 'Aprobado',
                    'Rechazado': 'Rechazado',
                    'Pendiente': 'Pendiente'
                },
                inputPlaceholder: 'Seleccionar nuevo estado',
                showCancelButton: true,
                confirmButtonText: 'Cambiar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3B82F6',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes seleccionar un estado';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/descuentos-especiales/${id}/cambiar-aprobacion`,
                        method: 'POST',
                        data: {
                            _token: '<?php echo csrf_token(); ?>',
                            nuevo_estado: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Actualizado!',
                                    text: response.message,
                                    confirmButtonColor: '#3B82F6'
                                });
                                cargarDescuentos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Error al cambiar aprobación',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Helpers
         */
        function obtenerBadgeAprobacion(estado) {
            const badges = {
                'Pendiente': '<span class="badge badge-estado badge-pendiente">Pendiente</span>',
                'Aprobado': '<span class="badge badge-estado badge-aprobado">Aprobado</span>',
                'Rechazado': '<span class="badge badge-estado badge-rechazado">Rechazado</span>'
            };
            return badges[estado] || '<span class="badge badge-secondary">' + estado + '</span>';
        }

        function truncar(texto, max) {
            if (!texto) return '-';
            return texto.length > max ? texto.substring(0, max) + '...' : texto;
        }

        function formatearFecha(fecha) {
            if (!fecha) return '-';
            const d = new Date(fecha);
            return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
        }

        function mostrarError(mensaje) {
            $('#loadingSpinner').hide();
            $('#errorMessage').show();
            $('#errorText').text(mensaje);
        }

        /* =====================================================
         *  GRÁFICOS — Chart.js
         * ===================================================== */
        const COLORES_DESC = [
            '#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6',
            '#06B6D4','#EC4899','#F97316','#14B8A6','#6366F1',
            '#84CC16','#F43F5E','#A855F7','#0EA5E9','#D946EF'
        ];

        let chartsDescInicializados = false;
        let chartDescConsultorMesInst = null;

        function labelMesDesc(ym) {
            if (!ym) return '?';
            const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            const [y, m] = ym.split('-');
            return `${meses[parseInt(m,10)-1]} ${y}`;
        }

        function crearGraficoDesc(id, config) {
            const canvas = document.getElementById(id);
            if (!canvas) return null;
            return new Chart(canvas.getContext('2d'), config);
        }

        function inicializarGraficosDesc() {
            if (chartsDescInicializados) return;
            chartsDescInicializados = true;

            const data = descuentosData;

            /* ---- 1. Ranking consultores ---- */
            const porConsultor = {};
            data.forEach(d => {
                const n = d.consultor ? d.consultor : 'Sin asignar';
                porConsultor[n] = (porConsultor[n] || 0) + 1;
            });
            const consultoresSort = Object.entries(porConsultor).sort((a,b) => b[1]-a[1]);
            crearGraficoDesc('chartDescUsuarios', {
                type: 'bar',
                data: {
                    labels: consultoresSort.map(e => e[0]),
                    datasets: [{
                        label: 'Descuentos',
                        data: consultoresSort.map(e => e[1]),
                        backgroundColor: consultoresSort.map((_,i) => COLORES_DESC[i % COLORES_DESC.length]),
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { ticks: { autoSkip: false } } }
                }
            });

            /* ---- 2. Total por mes ---- */
            const porMes = {};
            data.forEach(d => {
                const ym = d.created_at ? d.created_at.substring(0,7) : null;
                if (ym) porMes[ym] = (porMes[ym] || 0) + 1;
            });
            const mesesSort = Object.keys(porMes).sort();
            crearGraficoDesc('chartDescPorMes', {
                type: 'line',
                data: {
                    labels: mesesSort.map(labelMesDesc),
                    datasets: [{
                        label: 'Descuentos creados',
                        data: mesesSort.map(m => porMes[m]),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.12)',
                        pointBackgroundColor: '#10B981',
                        pointRadius: 5,
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });

            /* ---- 3. Total por sede ---- */
            const porSede = {};
            data.forEach(d => { if (d.sede) porSede[d.sede] = (porSede[d.sede] || 0) + 1; });
            const sedesSort = Object.entries(porSede).sort((a,b) => b[1]-a[1]);
            crearGraficoDesc('chartDescPorSede', {
                type: 'bar',
                data: {
                    labels: sedesSort.map(e => e[0]),
                    datasets: [{
                        label: 'Descuentos',
                        data: sedesSort.map(e => e[1]),
                        backgroundColor: sedesSort.map((_,i) => COLORES_DESC[i % COLORES_DESC.length]),
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { ticks: { autoSkip: false } } }
                }
            });

            /* ---- 4. Por sede por mes (stacked, top 8 sedes) ---- */
            const top8Sedes = sedesSort.slice(0,8).map(e => e[0]);
            const todosMeses = [...new Set(
                data.map(d => d.created_at ? d.created_at.substring(0,7) : null).filter(Boolean)
            )].sort();
            crearGraficoDesc('chartDescSedePorMes', {
                type: 'bar',
                data: {
                    labels: todosMeses.map(labelMesDesc),
                    datasets: top8Sedes.map((sede, i) => ({
                        label: sede,
                        data: todosMeses.map(mes =>
                            data.filter(d => d.sede === sede && d.created_at && d.created_at.startsWith(mes)).length
                        ),
                        backgroundColor: COLORES_DESC[i % COLORES_DESC.length],
                        borderRadius: 3
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12 } } },
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            /* ---- 5. Descuentos por consultor en un mes (filtro por mes) ---- */
            const mesesUnicosDesc = [...new Set(data.map(d => d.created_at ? d.created_at.substring(0,7) : null).filter(Boolean))].sort();
            const selectMesDesc = document.getElementById('filtroMesConsultorDesc');
            mesesUnicosDesc.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = labelMesDesc(m);
                selectMesDesc.appendChild(opt);
            });

            const mesActualDesc = new Date().toISOString().substring(0,7);
            selectMesDesc.value = mesesUnicosDesc.includes(mesActualDesc) ? mesActualDesc : (mesesUnicosDesc[mesesUnicosDesc.length - 1] || '');

            function calcDataConsultorPorMesDesc(mes) {
                const filtrado = mes ? data.filter(d => d.created_at && d.created_at.startsWith(mes)) : data;
                const cMap = {};
                filtrado.forEach(d => {
                    const c = d.consultor ? d.consultor : 'Sin asignar';
                    cMap[c] = (cMap[c] || 0) + 1;
                });
                const sorted = Object.entries(cMap).sort((a,b) => b[1]-a[1]);
                return {
                    labels: sorted.map(e => e[0]),
                    data: sorted.map(e => e[1])
                };
            }

            const initData = calcDataConsultorPorMesDesc(selectMesDesc.value);
            chartDescConsultorMesInst = crearGraficoDesc('chartDescConsultorMes', {
                type: 'bar',
                data: {
                    labels: initData.labels,
                    datasets: [{
                        label: 'Descuentos',
                        data: initData.data,
                        backgroundColor: initData.labels.map((_,i) => COLORES_DESC[i % COLORES_DESC.length]),
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });

            $('#filtroMesConsultorDesc').on('change', function() {
                const d = calcDataConsultorPorMesDesc(this.value);
                chartDescConsultorMesInst.data.labels = d.labels;
                chartDescConsultorMesInst.data.datasets[0].data = d.data;
                chartDescConsultorMesInst.data.datasets[0].backgroundColor = d.labels.map((_,i) => COLORES_DESC[i % COLORES_DESC.length]);
                chartDescConsultorMesInst.update();
            });

            /* ---- 6. Descuentos por mes de un consultor (filtro por consultor) ---- */
            let chartDescMesPorConsultorInst = null;
            const consultoresUnicosDesc = [...new Set(data.map(d => d.consultor).filter(Boolean))].sort();
            const selectConsultorDesc = document.getElementById('filtroConsultorGraficoDesc');
            consultoresUnicosDesc.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c; opt.textContent = c;
                selectConsultorDesc.appendChild(opt);
            });

            function calcDataMesPorConsultor(consultor) {
                const filtrado = consultor ? data.filter(d => d.consultor === consultor) : data;
                const mMap = {};
                filtrado.forEach(d => {
                    const ym = d.created_at ? d.created_at.substring(0,7) : null;
                    if (ym) mMap[ym] = (mMap[ym] || 0) + 1;
                });
                const meses = Object.keys(mMap).sort();
                return { labels: meses.map(labelMesDesc), data: meses.map(m => mMap[m]) };
            }

            const initMesPorConsultor = calcDataMesPorConsultor('');
            chartDescMesPorConsultorInst = crearGraficoDesc('chartDescMesPorConsultor', {
                type: 'bar',
                data: {
                    labels: initMesPorConsultor.labels,
                    datasets: [{
                        label: 'Todos los consultores',
                        data: initMesPorConsultor.data,
                        backgroundColor: 'rgba(16,185,129,0.75)',
                        borderColor: '#10B981',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });

            $('#filtroConsultorGraficoDesc').on('change', function() {
                const d = calcDataMesPorConsultor(this.value);
                chartDescMesPorConsultorInst.data.labels = d.labels;
                chartDescMesPorConsultorInst.data.datasets[0].data = d.data;
                chartDescMesPorConsultorInst.data.datasets[0].label = this.value || 'Todos los consultores';
                chartDescMesPorConsultorInst.update();
            });

            /* ---- 7. Top 15 tipos de descuento especial ---- */
            const porTipo = {};
            data.forEach(d => {
                const k = d.descuento_especial ? d.descuento_especial.trim().substring(0,60) : 'Sin definir';
                porTipo[k] = (porTipo[k] || 0) + 1;
            });
            const top15 = Object.entries(porTipo).sort((a,b) => b[1]-a[1]).slice(0,15);
            crearGraficoDesc('chartTopDescuentos', {
                type: 'bar',
                data: {
                    labels: top15.map(e => e[0].length > 55 ? e[0].substring(0,55)+'…' : e[0]),
                    datasets: [{
                        label: 'Cantidad',
                        data: top15.map(e => e[1]),
                        backgroundColor: top15.map((_,i) => COLORES_DESC[i % COLORES_DESC.length]),
                        borderRadius: 5
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { ticks: { autoSkip: false } } }
                }
            });
        }

        document.getElementById('tabEstadisticasDescLink')?.addEventListener('shown.bs.tab', function () {
            inicializarGraficosDesc();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/descuentos-especiales.blade.php ENDPATH**/ ?>