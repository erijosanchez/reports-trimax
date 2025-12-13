@extends('layouts.app')

@section('title', 'Acuerdos Comerciales')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="mdi mdi-handshake"></i> Acuerdos Comerciales
                            </h3>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-lg" id="btnNuevoAcuerdo">
                                <i class="mdi mdi-plus"></i> Agregar Nuevo Acuerdo
                            </button>
                        </div>
                    </div>

                    <div class="tab-content tab-content-basic mt-4">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            {{-- Cards de Estadísticas --}}
                            <div class="row mb-4 ">
                                <div class="col-md-3 pb-2">
                                    <div class="card card-stat h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Total Acuerdos</p>
                                                    <h2 class="mb-0 font-weight-bold text-primary" id="totalAcuerdos">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="icon-stat bg-primary">
                                                    <i class="mdi mdi-file-document-multiple"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 pb-2">
                                    <div class="card card-stat h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Vigentes</p>
                                                    <h2 class="mb-0 font-weight-bold text-success" id="acuerdosVigentes">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="icon-stat bg-success">
                                                    <i class="mdi mdi-check-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 pb-2">
                                    <div class="card card-stat h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Pendientes</p>
                                                    <h2 class="mb-0 font-weight-bold text-warning" id="acuerdosPendientes">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="icon-stat bg-warning">
                                                    <i class="mdi mdi-clock-alert"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 pb-2">
                                    <div class="card card-stat h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Vencidos</p>
                                                    <h2 class="mb-0 font-weight-bold text-danger" id="acuerdosVencidos">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                </div>
                                                <div class="icon-stat bg-danger">
                                                    <i class="mdi mdi-alert-circle"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Filtros --}}
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            {{-- Sección de Filtros --}}
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-account"></i>
                                                        Consultor </label>
                                                    <select class="form-select" id="filtroUsuario">
                                                        <option value="">Todos los usuarios</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
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
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-flag"></i> Estado</label>
                                                    <select class="form-select" id="filtroEstado">
                                                        <option value="">Todos los estados</option>
                                                        <option value="Solicitado">Solicitado</option>
                                                        <option value="Vigente">Vigente</option>
                                                        <option value="Vencido">Vencido</option>
                                                        <option value="Deshabilitado">Deshabilitado</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-magnify"></i>
                                                        Buscar</label>
                                                    <div class="input-group input-group-lg">
                                                        <input type="text" class="form-control" id="buscarGeneral"
                                                            placeholder="# Acuerdo, RUC...">
                                                        <button class="btn btn-primary" type="button" id="btnBuscar">
                                                            <i class="mdi mdi-magnify"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <button class="btn btn-outline-primary" id="btnLimpiarFiltros">
                                                            <i class="mdi mdi-filter-remove"></i> Limpiar Filtros
                                                        </button>
                                                        <button class="btn btn-info" id="btnRecargar">
                                                            <i class="mdi mdi-refresh"></i> Recargar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Loading Spinner --}}
                                            <div id="loadingSpinner" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status"
                                                    style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3 text-muted">Cargando acuerdos comerciales...</p>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            {{-- Tabla de Acuerdos --}}
                                            <div id="tablaContainer" style="display: none;">
                                                <div class="table-responsive"
                                                    style="overflow-x: auto; margin: 0 -1.5rem; padding: 0 1.5rem;">
                                                    <table class="table table-hover align-middle" id="tablaAcuerdos"
                                                        style="min-width: 2500px;">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th width="50">#</th>
                                                                <th style="min-width: 130px;">N° Acuerdo</th>
                                                                <th style="min-width: 120px;">Sede</th>
                                                                <th style="min-width: 130px;">RUC</th>
                                                                <th style="min-width: 250px;">Razón Social</th>
                                                                <th style="min-width: 130px;">Consultor</th>
                                                                <th style="min-width: 130px;">Ciudad</th>
                                                                <th style="min-width: 400px;">Acuerdo Comercial</th>
                                                                <th style="min-width: 300px;">Tipo Promoción</th>
                                                                <th style="min-width: 130px;">Marca</th>
                                                                <th style="min-width: 130px;">AR</th>
                                                                <th style="min-width: 130px;">Diseños</th>
                                                                <th style="min-width: 130px;">Material</th>
                                                                <th style="min-width: 110px;">Fecha Inicio</th>
                                                                <th style="min-width: 110px;">Fecha Fin</th>
                                                                <th style="min-width: 130px;"
                                                                    class="bg-primary text-white">Estado</th>
                                                                <th style="min-width: 130px;"
                                                                    class="bg-warning text-dark">Validado</th>
                                                                <th style="min-width: 130px;" class="bg-info text-white">
                                                                    Aprobado</th>
                                                                <th style="min-width: 150px;">Creado Por</th>
                                                                <th style="min-width: 200px;" class="text-center">Acciones
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tablaAcuerdosBody">
                                                            {{-- Los datos se cargan dinámicamente --}}
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
    </div>

    {{-- Modal para Crear/Editar Acuerdo --}}
    <div class="modal fade" id="modalAcuerdo" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-document-edit"></i>
                        <span id="modalTitle">Nuevo Acuerdo Comercial</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAcuerdo" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            {{-- Columna 1 --}}
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="mdi mdi-account-circle"></i> Datos del Cliente
                                </h6>

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

                            {{-- Columna 2 --}}
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="mdi mdi-package-variant"></i> Detalles del Acuerdo
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label">Acuerdo Comercial <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="acuerdo_comercial" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo de Promoción <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tipo_promocion"
                                        placeholder="Ej: 50% DSCT EN AR NOX Y DEVABLUE" required>
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

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3"><i class="mdi mdi-calendar-range"></i> Vigencia y Detalles
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_inicio" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_fin" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Archivos Adjuntos</label>
                                    <input type="file" class="form-control" name="archivos[]" multiple
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <small class="text-muted">Máximo 10MB por archivo</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Comentarios</label>
                                    <textarea class="form-control" name="comentarios" rows="3" placeholder="NO APLICA SOBRE PROMOCIONES VIGENTES"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarAcuerdo">
                            <i class="mdi mdi-content-save"></i> Guardar Acuerdo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Ver Detalles --}}
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-information"></i> Detalles del Acuerdo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesAcuerdoContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Deshabilitar Acuerdo --}}
    <div class="modal fade" id="modalDeshabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-cancel"></i> Deshabilitar Acuerdo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDeshabilitar">
                    @csrf
                    <input type="hidden" id="acuerdoIdDeshabilitar">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            Esta acción deshabilitará el acuerdo comercial y enviará notificaciones a los responsables.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la Deshabilitación <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo" rows="4" required
                                placeholder="Explique detalladamente el motivo por el cual se deshabilita este acuerdo..."></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btnConfirmarDeshabilitar">
                            <i class="mdi mdi-cancel"></i> Deshabilitar Acuerdo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Rehabilitar Acuerdo --}}
    <div class="modal fade" id="modalRehabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-circle"></i> Rehabilitar Acuerdo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRehabilitar">
                    @csrf
                    <input type="hidden" id="acuerdoIdRehabilitar">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            Esta acción rehabilitará el acuerdo comercial y enviará notificaciones a los responsables.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la Rehabilitación <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo" rows="4" required
                                placeholder="Explique detalladamente el motivo por el cual se rehabilita este acuerdo..."></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnConfirmarRehabilitar">
                            <i class="mdi mdi-check-circle"></i> Rehabilitar Acuerdo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Extender Acuerdo --}}
    <div class="modal fade" id="modalExtender" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-calendar-clock"></i> Extender Acuerdo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formExtender">
                    @csrf
                    <input type="hidden" id="acuerdoIdExtender">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i>
                            Fecha actual de fin: <strong id="fechaFinActual"></strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="nueva_fecha_fin" required>
                            <small class="text-muted">Debe ser posterior a la fecha de fin actual</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo de la Extensión <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="motivo" rows="4" required
                                placeholder="Explique el motivo por el cual se extiende este acuerdo..."></textarea>
                            <small class="text-muted">Mínimo 10 caracteres</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="btnConfirmarExtender">
                            <i class="mdi mdi-calendar-check"></i> Extender Acuerdo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Estilos --}}
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

        .badge-solicitado {
            background-color: #F59E0B;
            color: white;
        }

        .badge-vigente {
            background-color: #10B981;
            color: white;
        }

        .badge-vencido {
            background-color: #EF4444;
            color: white;
        }

        .badge-pendiente {
            background-color: #F59E0B;
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let acuerdosData = [];
        const userEmail = "{{ Auth::user()->email }}";
        const canValidate = userEmail === 'planeamiento.comercial@trimaxperu.com';
        const canApprove = userEmail === 'smonopoli@trimaxperu.com';
        const canManageAcuerdos = userEmail === 'smonopoli@trimaxperu.com' || userEmail ===
            'planeamiento.comercial@trimaxperu.com';

        $(document).ready(function() {
            cargarAcuerdos();
            cargarUsuarios();

            // Eventos de filtros
            $('#filtroUsuario, #filtroSede, #filtroEstado').on('change', function() {
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
                $('#filtroEstado').val('');
                $('#buscarGeneral').val('');
                cargarAcuerdos();
            });

            $('#btnRecargar').on('click', function() {
                cargarAcuerdos();
            });

            // Modal nuevo acuerdo
            $('#btnNuevoAcuerdo').on('click', function() {
                $('#formAcuerdo')[0].reset();
                $('#modalTitle').text('Nuevo Acuerdo Comercial');
                $('#modalAcuerdo').modal('show');
            });

            // Submit formularios
            $('#formAcuerdo').on('submit', function(e) {
                e.preventDefault();
                crearAcuerdo();
            });

            $('#formDeshabilitar').on('submit', function(e) {
                e.preventDefault();
                deshabilitarAcuerdo();
            });

            $('#formExtender').on('submit', function(e) {
                e.preventDefault();
                extenderAcuerdo();
            });

            $('#formRehabilitar').on('submit', function(e) {
                e.preventDefault();
                rehabilitarAcuerdo();
            });
        });

        /**
         * Cargar usuarios creadores para el select
         */
        function cargarUsuarios() {
            $.ajax({
                url: "{{ route('comercial.acuerdos.usuarios') }}",
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
         * Cargar acuerdos
         */
        /**
         * Cargar acuerdos
         */
        function cargarAcuerdos() {
            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();

            $.ajax({
                url: "{{ route('comercial.acuerdos.obtener') }}",
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        acuerdosData = response.data;
                        console.log('✅ Acuerdos cargados:', acuerdosData.length);

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
        /**
         * Aplicar filtros
         */
        function aplicarFiltros() {
            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();

            const filtroUsuario = $('#filtroUsuario').val();
            const filtroSede = $('#filtroSede').val();
            const filtroEstado = $('#filtroEstado').val();
            const busqueda = $('#buscarGeneral').val().toLowerCase().trim();

            let acuerdosFiltrados = acuerdosData;

            // Filtro por usuario
            if (filtroUsuario) {
                acuerdosFiltrados = acuerdosFiltrados.filter(a =>
                    a.creador && a.creador.id == filtroUsuario
                );
            }

            // Filtro por sede
            if (filtroSede) {
                acuerdosFiltrados = acuerdosFiltrados.filter(a =>
                    a.sede === filtroSede
                );
            }

            // Filtro por estado
            if (filtroEstado) {
                acuerdosFiltrados = acuerdosFiltrados.filter(a =>
                    a.estado === filtroEstado
                );
            }

            // Búsqueda general (flexible)
            if (busqueda) {
                acuerdosFiltrados = acuerdosFiltrados.filter(a => {
                    const numero = (a.numero_acuerdo || '').toLowerCase();
                    const ruc = (a.ruc || '').toLowerCase();
                    const razon = (a.razon_social || '').toLowerCase();
                    const consultor = (a.consultor || '').toLowerCase();
                    const acuerdo = (a.acuerdo_comercial || '').toLowerCase();
                    const ciudad = (a.ciudad || '').toLowerCase();

                    return numero.includes(busqueda) ||
                        ruc.includes(busqueda) ||
                        razon.includes(busqueda) ||
                        consultor.includes(busqueda) ||
                        acuerdo.includes(busqueda) ||
                        ciudad.includes(busqueda);
                });
            }

            // Actualizar con los datos filtrados
            const acuerdosOriginal = acuerdosData;
            acuerdosData = acuerdosFiltrados;

            actualizarEstadisticas();
            renderizarTabla();

            acuerdosData = acuerdosOriginal; // Restaurar datos originales

            $('#loadingSpinner').hide();
            $('#tablaContainer').show();
        }

        /**
         * Actualizar estadísticas
         */
        function actualizarEstadisticas() {
            const total = acuerdosData.length;
            const vigentes = acuerdosData.filter(a => a.estado === 'Vigente').length;
            const pendientes = acuerdosData.filter(a => a.estado === 'Solicitado').length;
            const vencidos = acuerdosData.filter(a => a.estado === 'Vencido').length;

            $('#totalAcuerdos').text(total);
            $('#acuerdosVigentes').text(vigentes);
            $('#acuerdosPendientes').text(pendientes);
            $('#acuerdosVencidos').text(vencidos);
        }

        /**
         * Renderizar tabla
         */
        function renderizarTabla() {
            let html = '';

            if (acuerdosData.length === 0) {
                html = `
                <tr>
                    <td colspan="17" class="text-center py-5">
                        <i class="mdi mdi-file-document-outline mdi-48px text-muted"></i>
                        <p class="mt-3 text-muted">No se encontraron acuerdos comerciales</p>
                    </td>
                </tr>
            `;
            } else {
                acuerdosData.forEach((acuerdo, index) => {
                    const badgeEstado = obtenerBadgeEstado(acuerdo.estado);
                    const badgeValidado = obtenerBadgeAprobacion(acuerdo.validado);
                    const badgeAprobado = obtenerBadgeAprobacion(acuerdo.aprobado);

                    const esDeshabilitado = acuerdo.estado === 'Deshabilitado';
                    const esVigente = acuerdo.estado === 'Vigente';

                    html += `
                    <tr ${esDeshabilitado ? 'class="table-secondary"' : ''}>
                        <td class="text-center">${index + 1}</td>
                        <td><strong class="text-primary">${acuerdo.numero_acuerdo}</strong></td>
                        <td>${acuerdo.sede}</td>
                        <td>${acuerdo.ruc}</td>
                        <td title="${acuerdo.razon_social}"><strong>${acuerdo.razon_social}</strong></td>
                        <td>${acuerdo.consultor}</td>
                        <td>${acuerdo.ciudad}</td>
                        <td title="${acuerdo.acuerdo_comercial}">${truncar(acuerdo.acuerdo_comercial, 70)}</td>
                        <td>${acuerdo.tipo_promocion}</td>
                        <td>${acuerdo.marca}</td>
                        <td>${acuerdo.ar}</td>
                        <td>${acuerdo.disenos}</td>
                        <td>${acuerdo.material}</td>
                        <td><small>${formatearFecha(acuerdo.fecha_inicio)}</small></td>
                        <td><small>${formatearFecha(acuerdo.fecha_fin)}</small></td>
                        <td>${badgeEstado}</td>
                        <td>
                            ${badgeValidado}
                            ${canValidate && acuerdo.validado === 'Pendiente' && !esDeshabilitado ? `
                                                <div class="btn-group mt-1" role="group">
                                                    <button class="btn btn-sm btn-success" onclick="validarAcuerdo(${acuerdo.id}, 'Aprobado')">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="validarAcuerdo(${acuerdo.id}, 'Rechazado')">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </div>
                                            ` : ''}
                        </td>
                        <td>
                            ${badgeAprobado}
                            ${canApprove && acuerdo.aprobado === 'Pendiente' && !esDeshabilitado ? `
                                                <div class="btn-group mt-1" role="group">
                                                    <button class="btn btn-sm btn-success" onclick="aprobarAcuerdo(${acuerdo.id}, 'Aprobado')">
                                                        <i class="mdi mdi-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="aprobarAcuerdo(${acuerdo.id}, 'Rechazado')">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </div>
                                            ` : ''}
                        </td>
                        <td>${acuerdo.creador ? acuerdo.creador.name : '-'}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="verDetalles(${acuerdo.id})" title="Ver detalles">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                ${canManageAcuerdos && esVigente && !esDeshabilitado ? `
                                                <button class="btn btn-sm btn-success" onclick="abrirModalExtender(${acuerdo.id})" title="Extender vigencia">
                                                    <i class="mdi mdi-calendar-clock"></i>
                                                </button>
                                            ` : ''}
                                ${canManageAcuerdos && !esDeshabilitado ? `
                                                <button class="btn btn-sm btn-danger" onclick="abrirModalDeshabilitar(${acuerdo.id})" title="Deshabilitar">
                                                    <i class="mdi mdi-cancel"></i>
                                                </button>
                                            ` : ''}
                                ${canManageAcuerdos && esDeshabilitado ? `
                                                <button class="btn btn-sm btn-success" onclick="abrirModalRehabilitar(${acuerdo.id})" title="Rehabilitar">
                                                    <i class="mdi mdi-check-circle"></i>
                                                </button>
                                            ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
                });
            }

            $('#tablaAcuerdosBody').html(html);
        }

        /**
         * Crear acuerdo
         */
        function crearAcuerdo() {
            const formData = new FormData($('#formAcuerdo')[0]);

            $('#btnGuardarAcuerdo').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Guardando...');

            $.ajax({
                url: "{{ route('comercial.acuerdos.crear') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalAcuerdo').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            confirmButtonColor: '#3B82F6'
                        });
                        cargarAcuerdos();
                        cargarUsuarios();
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al crear acuerdo';
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
                    $('#btnGuardarAcuerdo').prop('disabled', false).html(
                        '<i class="mdi mdi-content-save"></i> Guardar Acuerdo');
                }
            });
        }

        /**
         * Abrir modal deshabilitar
         */
        function abrirModalDeshabilitar(id) {
            if (!canManageAcuerdos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para deshabilitar acuerdos',
                    confirmButtonColor: '#EF4444'
                });
                return;
            }

            $('#acuerdoIdDeshabilitar').val(id);
            $('#formDeshabilitar')[0].reset();
            $('#modalDeshabilitar').modal('show');
        }

        /**
         * Deshabilitar acuerdo
         */
        function deshabilitarAcuerdo() {
            const id = $('#acuerdoIdDeshabilitar').val();
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
                url: `/comercial/acuerdos/${id}/deshabilitar`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
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
                        cargarAcuerdos();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al deshabilitar acuerdo',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnConfirmarDeshabilitar').prop('disabled', false).html(
                        '<i class="mdi mdi-cancel"></i> Deshabilitar Acuerdo');
                }
            });
        }

        /**
         * Abrir modal extender
         */
        function abrirModalExtender(id) {
            if (!canManageAcuerdos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para extender acuerdos',
                    confirmButtonColor: '#EF4444'
                });
                return;
            }

            const acuerdo = acuerdosData.find(a => a.id === id);
            if (!acuerdo) return;

            $('#acuerdoIdExtender').val(id);
            $('#fechaFinActual').text(formatearFecha(acuerdo.fecha_fin));
            $('#formExtender')[0].reset();

            // Establecer fecha mínima
            $('input[name="nueva_fecha_fin"]', '#formExtender').attr('min', acuerdo.fecha_fin);

            $('#modalExtender').modal('show');
        }

        /**
         * Extender acuerdo
         */
        function extenderAcuerdo() {
            const id = $('#acuerdoIdExtender').val();
            const nuevaFecha = $('input[name="nueva_fecha_fin"]', '#formExtender').val();
            const motivo = $('textarea[name="motivo"]', '#formExtender').val();

            if (motivo.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Motivo insuficiente',
                    text: 'El motivo debe tener al menos 10 caracteres',
                    confirmButtonColor: '#F59E0B'
                });
                return;
            }

            $('#btnConfirmarExtender').prop('disabled', true).html(
                '<i class="mdi mdi-loading mdi-spin"></i> Procesando...');

            $.ajax({
                url: `/comercial/acuerdos/${id}/extender`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nueva_fecha_fin: nuevaFecha,
                    motivo: motivo
                },
                success: function(response) {
                    if (response.success) {
                        $('#modalExtender').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '¡Extendido!',
                            text: response.message,
                            confirmButtonColor: '#3B82F6'
                        });
                        cargarAcuerdos();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al extender acuerdo',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnConfirmarExtender').prop('disabled', false).html(
                        '<i class="mdi mdi-calendar-check"></i> Extender Acuerdo');
                }
            });
        }

        /**
         * Abrir modal rehabilitar
         */
        function abrirModalRehabilitar(id) {
            if (!canManageAcuerdos) {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para rehabilitar acuerdos',
                    confirmButtonColor: '#EF4444'
                });
                return;
            }

            $('#acuerdoIdRehabilitar').val(id);
            $('#formRehabilitar')[0].reset();
            $('#modalRehabilitar').modal('show');
        }

        /**
         * Rehabilitar acuerdo
         */
        function rehabilitarAcuerdo() {
            const id = $('#acuerdoIdRehabilitar').val();
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
                url: `/comercial/acuerdos/${id}/rehabilitar`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
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
                        cargarAcuerdos();
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al rehabilitar acuerdo',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    $('#btnConfirmarRehabilitar').prop('disabled', false).html(
                        '<i class="mdi mdi-check-circle"></i> Rehabilitar Acuerdo');
                }
            });
        }

        /**
         * Validar acuerdo (Planeamiento)
         */
        function validarAcuerdo(id, accion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${accion === 'Aprobado' ? 'APROBAR' : 'RECHAZAR'} este acuerdo?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: accion === 'Aprobado' ? '#10B981' : '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/acuerdos/${id}/validar`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            accion: accion
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Validado!',
                                    text: response.message,
                                    confirmButtonColor: '#3B82F6'
                                });
                                cargarAcuerdos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al validar acuerdo',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Aprobar acuerdo (Gerencia)
         */
        function aprobarAcuerdo(id, accion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas ${accion === 'Aprobado' ? 'APROBAR' : 'RECHAZAR'} este acuerdo?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: accion === 'Aprobado' ? '#10B981' : '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comercial/acuerdos/${id}/aprobar`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
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
                                cargarAcuerdos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al aprobar acuerdo',
                                confirmButtonColor: '#EF4444'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Ver detalles
         */
        function verDetalles(id) {
            const acuerdo = acuerdosData.find(a => a.id === id);
            if (!acuerdo) return;

            let archivosHTML = '';
            if (acuerdo.archivos_adjuntos && acuerdo.archivos_adjuntos.length > 0) {
                archivosHTML = '<ul class="list-group">';
                acuerdo.archivos_adjuntos.forEach((archivo, index) => {
                    archivosHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="mdi mdi-file-document"></i> ${archivo.nombre}</span>
                        <a href="/comercial/acuerdos/${acuerdo.id}/archivo/${index}" class="btn btn-sm btn-primary" download>
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
            if (acuerdo.motivo_deshabilitacion) {
                historialHTML += `
                    <div class="alert alert-danger">
                        <h6><i class="mdi mdi-cancel"></i> Deshabilitado</h6>
                        <p><strong>Motivo:</strong> ${acuerdo.motivo_deshabilitacion}</p>
                        <p><strong>Por:</strong> ${acuerdo.deshabilitador?.name || '-'}</p>
                        <p><strong>Fecha:</strong> ${formatearFecha(acuerdo.deshabilitado_at)}</p>
                    </div>
                `;
            }
            if (acuerdo.motivo_rehabilitacion) {
                historialHTML += `
                    <div class="alert alert-success">
                        <h6><i class="mdi mdi-check-circle"></i> Rehabilitado</h6>
                        <p><strong>Motivo:</strong> ${acuerdo.motivo_rehabilitacion}</p>
                        <p><strong>Por:</strong> ${acuerdo.rehabilitador?.name || '-'}</p>
                        <p><strong>Fecha:</strong> ${formatearFecha(acuerdo.rehabilitado_at)}</p>
                    </div>
                `;
            }
            if (acuerdo.motivo_extension) {
                historialHTML += `
                    <div class="alert alert-info">
                        <h6><i class="mdi mdi-calendar-clock"></i> Extensión</h6>
                        <p><strong>Motivo:</strong> ${acuerdo.motivo_extension}</p>
                        <p><strong>Por:</strong> ${acuerdo.extensor?.name || '-'}</p>
                        <p><strong>Fecha:</strong> ${formatearFecha(acuerdo.extendido_at)}</p>
                    </div>
                `;
            }

            const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="mdi mdi-information"></i> Información General</h6>
                    <table class="table table-borderless table-sm">
                        <tr><th>N° Acuerdo:</th><td><strong>${acuerdo.numero_acuerdo}</strong></td></tr>
                        <tr><th>Estado:</th><td>${obtenerBadgeEstado(acuerdo.estado)}</td></tr>
                        <tr><th>Razón Social:</th><td>${acuerdo.razon_social}</td></tr>
                        <tr><th>RUC:</th><td>${acuerdo.ruc}</td></tr>
                        <tr><th>Sede:</th><td>${acuerdo.sede}</td></tr>
                        <tr><th>Ciudad:</th><td>${acuerdo.ciudad}</td></tr>
                        <tr><th>Consultor:</th><td>${acuerdo.consultor}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="mdi mdi-package"></i> Detalles del Acuerdo</h6>
                    <table class="table table-borderless table-sm">
                        <tr><th>Acuerdo:</th><td style="max-width: 250px; max-height: 80px; overflow-y: auto; overflow-x: hidden; word-wrap: break-word; white-space: normal;">${acuerdo.acuerdo_comercial}</td></tr>
                        <tr><th>Promoción:</th><td>${acuerdo.tipo_promocion}</td></tr>
                        <tr><th>Marca:</th><td>${acuerdo.marca}</td></tr>
                        <tr><th>AR:</th><td>${acuerdo.ar || '-'}</td></tr>
                        <tr><th>Diseños:</th><td>${acuerdo.disenos || '-'}</td></tr>
                        <tr><th>Material:</th><td>${acuerdo.material || '-'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="text-primary"><i class="mdi mdi-calendar"></i> Vigencia</h6>
                    <p><strong>Inicio:</strong> ${formatearFecha(acuerdo.fecha_inicio)} | <strong>Fin:</strong> ${formatearFecha(acuerdo.fecha_fin)}</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="text-primary"><i class="mdi mdi-check-circle"></i> Aprobaciones</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Validado:</th>
                            <td>${obtenerBadgeAprobacion(acuerdo.validado)}</td>
                            <td>${acuerdo.validador ? 'Por: ' + acuerdo.validador.name : ''}</td>
                        </tr>
                        <tr>
                            <th>Aprobado:</th>
                            <td>${obtenerBadgeAprobacion(acuerdo.aprobado)}</td>
                            <td>${acuerdo.aprobador ? 'Por: ' + acuerdo.aprobador.name : ''}</td>
                        </tr>
                    </table>
                </div>
            </div>
            ${historialHTML ? `
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="mdi mdi-history"></i> Historial</h6>
                                ${historialHTML}
                            </div>
                        </div>
                        ` : ''}
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="text-primary"><i class="mdi mdi-comment-text"></i> Comentarios</h6>
                    <p>${acuerdo.comentarios || 'Sin comentarios'}</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="text-primary"><i class="mdi mdi-paperclip"></i> Archivos Adjuntos</h6>
                    ${archivosHTML}
                </div>
            </div>
        `;

            $('#detallesAcuerdoContent').html(html);
            $('#modalDetalles').modal('show');
        }

        /**
         * Helpers
         */
        function obtenerBadgeEstado(estado) {
            const badges = {
                'Solicitado': '<span class="badge badge-estado badge-solicitado">Solicitado</span>',
                'Vigente': '<span class="badge badge-estado badge-vigente">Vigente</span>',
                'Vencido': '<span class="badge badge-estado badge-vencido">Vencido</span>',
                'Deshabilitado': '<span class="badge badge-estado badge-deshabilitado">Deshabilitado</span>'
            };
            return badges[estado] || '<span class="badge badge-secondary">' + estado + '</span>';
        }

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
    </script>
@endsection
