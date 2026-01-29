@extends('layouts.app')

@section('title', 'Descuentos Especiales')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="mdi mdi-tag-multiple"></i> Descuentos Especiales
                            </h3>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-lg" id="btnNuevoDescuento">
                                <i class="mdi mdi-plus"></i> Agregar Nuevo Descuento
                            </button>
                        </div>
                    </div>

                    <div class="tab-content tab-content-basic mt-4">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            {{-- Cards de Estadísticas --}}
                            <div class="row mb-4">
                                <div class="col-md-3 pb-2">
                                    <div class="card card-stat h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Total Descuentos</p>
                                                    <h2 class="mb-0 font-weight-bold text-primary" id="totalDescuentos">
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
                                                    <p class="text-muted mb-1">Aprobados</p>
                                                    <h2 class="mb-0 font-weight-bold text-success" id="descuentosAprobados">
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
                                                    <h2 class="mb-0 font-weight-bold text-warning"
                                                        id="descuentosPendientes">
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
                                                    <p class="text-muted mb-1">Rechazados</p>
                                                    <h2 class="mb-0 font-weight-bold text-danger" id="descuentosRechazados">
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
                                                <div class="col-md-2">
                                                    <label class="form-label"><i class="mdi mdi-flag"></i>
                                                        Validado</label>
                                                    <select class="form-select" id="filtroValidado">
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
                                                <p class="mt-3 text-muted">Cargando descuentos especiales...</p>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            {{-- Tabla de Descuentos --}}
                                            <div id="tablaContainer" style="display: none;">
                                                <div class="table-responsive"
                                                    style="overflow-x: auto; margin: 0 -1.5rem; padding: 0 1.5rem;">
                                                    <table class="table table-hover align-middle" id="tablaDescuentos"
                                                        style="min-width: 2200px;">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th width="50">#</th>
                                                                <th style="min-width: 130px;">N° Descuento</th>
                                                                <th style="min-width: 120px;">Sede</th>
                                                                <th style="min-width: 130px;">RUC</th>
                                                                <th style="min-width: 250px;">Razón Social</th>
                                                                <th style="min-width: 130px;">Consultor</th>
                                                                <th style="min-width: 130px;">Ciudad</th>
                                                                <th style="min-width: 400px;">Descuento Especial</th>
                                                                <th style="min-width: 180px;">Tipo</th>
                                                                <th style="min-width: 130px;">Marca</th>
                                                                <th style="min-width: 130px;">AR</th>
                                                                <th style="min-width: 130px;">Diseños</th>
                                                                <th style="min-width: 130px;">Material</th>
                                                                <th style="min-width: 110px;">Fecha Registro</th>
                                                                <th style="min-width: 130px;"
                                                                    class="bg-warning text-dark">Validado</th>
                                                                <th style="min-width: 130px;" class="bg-info text-white">
                                                                    Aprobado</th>
                                                                <th style="min-width: 150px;">Creado Por</th>
                                                                <th style="min-width: 200px;" class="text-center">Acciones
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tablaDescuentosBody">
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

    {{-- Modal para Crear/Editar Descuento --}}
    <div class="modal fade" id="modalDescuento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-document-edit"></i>
                        <span id="modalTitle">Nuevo Descuento Especial</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDescuento" enctype="multipart/form-data">
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
                                <h6 class="text-primary mb-3"><i class="mdi mdi-package-variant"></i> Detalles del
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

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-3"><i class="mdi mdi-paperclip"></i> Archivos y Comentarios
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
                                    <label class="form-label">Comentarios</label>
                                    <textarea class="form-control" name="comentarios" rows="3" placeholder="Observaciones adicionales"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarDescuento">
                            <i class="mdi mdi-content-save"></i> Guardar Descuento
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
                        <i class="mdi mdi-information"></i> Detalles del Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesDescuentoContent">
                    {{-- Contenido dinámico --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Deshabilitar Descuento --}}
    <div class="modal fade" id="modalDeshabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-cancel"></i> Deshabilitar Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDeshabilitar">
                    @csrf
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

    {{-- Modal para Rehabilitar Descuento --}}
    <div class="modal fade" id="modalRehabilitar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-circle"></i> Rehabilitar Descuento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRehabilitar">
                    @csrf
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let descuentosData = [];
        const userEmail = "{{ Auth::user()->email }}";
        const canValidate = userEmail === 'planeamiento.comercial@trimaxperu.com';
        const canApprove = userEmail === 'smonopoli@trimaxperu.com' || userEmail ===
        'planeamiento.comercial@trimaxperu.com';
        const canManageDescuentos = userEmail === 'smonopoli@trimaxperu.com' || userEmail ===
            'planeamiento.comercial@trimaxperu.com';

        $(document).ready(function() {
            cargarDescuentos();
            cargarUsuarios();

            // Eventos de filtros
            $('#filtroUsuario, #filtroSede, #filtroValidado, #filtroAprobado').on('change', function() {
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
                $('#filtroValidado').val('');
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
                url: "{{ route('comercial.descuentos.usuarios') }}",
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
                url: "{{ route('comercial.descuentos.obtener') }}",
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
            const filtroValidado = $('#filtroValidado').val();
            const filtroAprobado = $('#filtroAprobado').val();
            const busqueda = $('#buscarGeneral').val().toLowerCase().trim();

            let descuentosFiltrados = descuentosData;

            // Filtro por usuario
            if (filtroUsuario) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.creador && d.creador.id == filtroUsuario
                );
            }

            // Filtro por sede
            if (filtroSede) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.sede === filtroSede
                );
            }

            // Filtro por validado
            if (filtroValidado) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.validado === filtroValidado
                );
            }

            // Filtro por aprobado
            if (filtroAprobado) {
                descuentosFiltrados = descuentosFiltrados.filter(d =>
                    d.aprobado === filtroAprobado
                );
            }

            // Búsqueda general
            if (busqueda) {
                descuentosFiltrados = descuentosFiltrados.filter(d => {
                    const numero = (d.numero_descuento || '').toLowerCase();
                    const ruc = (d.ruc || '').toLowerCase();
                    const razon = (d.razon_social || '').toLowerCase();
                    const consultor = (d.consultor || '').toLowerCase();
                    const descuento = (d.descuento_especial || '').toLowerCase();
                    const ciudad = (d.ciudad || '').toLowerCase();

                    return numero.includes(busqueda) ||
                        ruc.includes(busqueda) ||
                        razon.includes(busqueda) ||
                        consultor.includes(busqueda) ||
                        descuento.includes(busqueda) ||
                        ciudad.includes(busqueda);
                });
            }

            // Actualizar con los datos filtrados
            const descuentosOriginal = descuentosData;
            descuentosData = descuentosFiltrados;

            actualizarEstadisticas();
            renderizarTabla();

            descuentosData = descuentosOriginal;

            $('#loadingSpinner').hide();
            $('#tablaContainer').show();
        }

        /**
         * Actualizar estadísticas
         */
        function actualizarEstadisticas() {
            const total = descuentosData.length;
            const aprobados = descuentosData.filter(d => d.validado === 'Aprobado' && d.aprobado === 'Aprobado').length;
            const pendientes = descuentosData.filter(d => d.validado === 'Pendiente' || d.aprobado === 'Pendiente').length;
            const rechazados = descuentosData.filter(d => d.validado === 'Rechazado' || d.aprobado === 'Rechazado').length;

            $('#totalDescuentos').text(total);
            $('#descuentosAprobados').text(aprobados);
            $('#descuentosPendientes').text(pendientes);
            $('#descuentosRechazados').text(rechazados);
        }

        /**
         * Renderizar tabla
         */
        function renderizarTabla() {
            let html = '';

            if (descuentosData.length === 0) {
                html = `
                <tr>
                    <td colspan="18" class="text-center py-5">
                        <i class="mdi mdi-file-document-outline mdi-48px text-muted"></i>
                        <p class="mt-3 text-muted">No se encontraron descuentos especiales</p>
                    </td>
                </tr>
            `;
            } else {
                descuentosData.forEach((descuento, index) => {
                    const badgeValidado = obtenerBadgeAprobacion(descuento.validado);
                    const badgeAprobado = obtenerBadgeAprobacion(descuento.aprobado);

                    const esDeshabilitado = !descuento.habilitado;
                    const esCreador = descuento.creador && descuento.creador.id == {{ Auth::id() }};
                    const puedeEditar = esCreador || canManageDescuentos;

                    html += `
                    <tr ${esDeshabilitado ? 'class="table-secondary"' : ''}>
                        <td class="text-center">${index + 1}</td>
                        <td><strong class="text-primary">${descuento.numero_descuento}</strong></td>
                        <td>${descuento.sede}</td>
                        <td>${descuento.ruc}</td>
                        <td title="${descuento.razon_social}"><strong>${descuento.razon_social}</strong></td>
                        <td>${descuento.consultor}</td>
                        <td>${descuento.ciudad}</td>
                        <td title="${descuento.descuento_especial}">${truncar(descuento.descuento_especial, 70)}</td>
                        <td><span class="badge bg-secondary">${descuento.tipo}</span></td>
                        <td>${descuento.marca}</td>
                        <td>${descuento.ar || '-'}</td>
                        <td>${descuento.disenos || '-'}</td>
                        <td>${descuento.material || '-'}</td>
                        <td><small>${formatearFecha(descuento.created_at)}</small></td>
                        <td>
                            ${badgeValidado}
                            ${canValidate && descuento.validado === 'Pendiente' && !esDeshabilitado ? `
                                    <div class="btn-group mt-1" role="group">
                                        <button class="btn btn-sm btn-success" onclick="validarDescuento(${descuento.id}, 'Aprobado')">
                                            <i class="mdi mdi-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="validarDescuento(${descuento.id}, 'Rechazado')">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                    </div>
                                ` : ''}
                            ${canValidate && (descuento.validado === 'Aprobado' || descuento.validado === 'Rechazado') && !esDeshabilitado ? `
                                    <button class="btn btn-sm btn-outline-info mt-1" onclick="cambiarValidacion(${descuento.id})" title="Cambiar validación">
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
                                    <button class="btn btn-sm btn-outline-info mt-1" onclick="cambiarAprobacion(${descuento.id})" title="Cambiar aprobación">
                                        <i class="mdi mdi-swap-horizontal"></i>
                                    </button>
                                ` : ''}
                        </td>
                        <td>${descuento.creador ? descuento.creador.name : '-'}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="verDetalles(${descuento.id})" title="Ver detalles">
                                    <i class="mdi mdi-eye"></i>
                                </button>
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
        }

        /**
         * Crear descuento
         */
        function crearDescuento() {
            const descuentoId = $('#formDescuento').attr('data-descuento-id');
            const formData = new FormData($('#formDescuento')[0]);

            let url = "{{ route('comercial.descuentos.crear') }}";
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
                        '<i class="mdi mdi-content-save"></i> Guardar Descuento');
                }
            });
        }

        /**
         * Validar descuento
         */
        function validarDescuento(id, accion) {
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
                        url: `/comercial/descuentos-especiales/${id}/validar`,
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
                                cargarDescuentos();
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Error al validar descuento',
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
                    <li class="list-group-item d-flex justify-content-between align-items-center">
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
                        <tr><th>Tipo:</th><td><span class="badge bg-secondary">${descuento.tipo}</span></td></tr>
                        <tr><th>Marca:</th><td>${descuento.marca}</td></tr>
                        <tr><th>AR:</th><td>${descuento.ar || '-'}</td></tr>
                        <tr><th>Diseños:</th><td>${descuento.disenos || '-'}</td></tr>
                        <tr><th>Material:</th><td>${descuento.material || '-'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="text-primary"><i class="mdi mdi-check-circle"></i> Aprobaciones</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Validado:</th>
                            <td>${obtenerBadgeAprobacion(descuento.validado)}</td>
                            <td>${descuento.validador ? 'Por: ' + descuento.validador.name : ''}</td>
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
                    <p>${descuento.comentarios || 'Sin comentarios'}</p>
                </div>
            </div>
            <div class="row mt-3">
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
         * Cambiar validación
         */
        function cambiarValidacion(id) {
            const descuento = descuentosData.find(d => d.id === id);
            if (!descuento) return;

            Swal.fire({
                title: 'Cambiar Validación',
                text: `Estado actual: ${descuento.validado}`,
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
                        url: `/comercial/descuentos-especiales/${id}/cambiar-validacion`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
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
                                    'Error al cambiar validación',
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
                            _token: '{{ csrf_token() }}',
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
    </script>
@endsection
