@extends('layouts.app')

@section('title', 'Consulta de Orden')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">Consulta de Órdenes</h3>
                        </div>
                    </div>
                    <div class="mt-4 tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            {{-- Cards de Estadísticas --}}
                            <div class="mb-4 row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Total de órdenes</p>
                                                    <h2 class="mb-0 font-weight-bold" id="totalOrdenes">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-success">
                                                        <i class="mdi mdi-arrow-up"></i> Actualizado
                                                    </small>
                                                </div>
                                                <div class="bg-primary-gradient rounded-circle text-white icon-lg">
                                                    <i class="mdi-cart-outline mdi mdi-24px"></i>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <canvas id="sparklineChart" height="50"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Órdenes disponibles para facturar</p>
                                                    <h2 class="mb-0 font-weight-bold" id="ordenesFacturar">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-success">
                                                        <i class="mdi mdi-arrow-up"></i> En proceso
                                                    </small>
                                                </div>
                                                <div class="bg-success-gradient rounded-circle text-white icon-lg">
                                                    <i class="mdi mdi-file-document-box mdi-24px"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">En Tránsito / En Sede</p>
                                                    <h2 class="mb-0 font-weight-bold" id="ordenesTransito">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-muted">Órdenes en proceso</small>
                                                </div>
                                                <div class="bg-warning-gradient rounded-circle text-white icon-lg">
                                                    <i class="mdi mdi-truck-delivery mdi-24px"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Filtros y Tabla --}}
                            <div class="row">
                                <div class="grid-margin col-lg-12 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- Sección de Filtros --}}
                                            <div class="mb-3 row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Filtrar por Sede</label>
                                                    <select class="form-select" id="filtroSede">
                                                        <option value="">Todas las sucursales</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Estado</label>
                                                    <select class="form-select" id="filtroEstado">
                                                        <option value="">Todos</option>
                                                        <option value="FACTURADO">Facturado y Entregado</option>
                                                        <option value="EN TRANSITO">En Tránsito</option>
                                                        <option value="EN SEDE">En Sede</option>
                                                        <option value="OTROS">Otros Estados</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Tipo de Orden</label>
                                                    <select class="form-select" id="filtroTipoOrden">
                                                        <option value="">Todos</option>
                                                        <option value="FABRICACION">Fabricación</option>
                                                        <option value="STOCK">Stock</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Buscar</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="buscarPedido"
                                                            placeholder="# Orden, Cliente, RUC...">
                                                        <button class="btn btn-primary" type="button" id="btnBuscar">
                                                            <i class="mdi mdi-magnify"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3 row">
                                                <div class="d-flex align-items-center justify-content-between col-md-12">
                                                    <div>
                                                        <button class="btn-outline-primary btn btn-sm"
                                                            id="btnLimpiarFiltros">
                                                            <i class="mdi-filter-remove mdi"></i> Limpiar Filtros
                                                        </button>
                                                        <button class="btn btn-sm btn-info" id="btnRecargar">
                                                            <i class="mdi mdi-refresh"></i> Recargar
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-success" id="btnExportar">
                                                            <i class="mdi mdi-download"></i> Exportar CSV
                                                        </button>
                                                        <button class="btn btn-sm btn-info" id="btnOrdenarFecha">
                                                            <i class="mdi mdi-sort-calendar-ascending"></i> Ordenar por
                                                            Fecha
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Loading Spinner --}}
                                            <div id="loadingSpinner" class="py-5 text-center">
                                                <div class="spinner-border text-primary" role="status"
                                                    style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3">Cargando órdenes desde Google Sheets...</p>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            {{-- Tabla de Órdenes --}}
                                            <div class="table-responsive" id="tablaContainer" style="display: none;">
                                                <table class="table table-hover table-sm" id="tablaOrdenes">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="30">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="selectAll">
                                                                </div>
                                                            </th>
                                                            <th>Sede</th>
                                                            <th># Orden</th>
                                                            <th>RUC</th>
                                                            <th>Cliente</th>
                                                            <th>Diseño</th>
                                                            <th>Producto</th>
                                                            <th>O. Compra</th>
                                                            <th>Fecha</th>
                                                            <th>Hora</th>
                                                            <th>Tipo</th>
                                                            <th>Usuario</th>
                                                            <th>Estado</th>
                                                            <th class="bg-primary text-white">Ubicación</th>
                                                            <th>Tallado</th>
                                                            <th>Tratamiento</th>
                                                            <th>Lead Time</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaOrdenesBody">
                                                        {{-- Los datos se cargan dinámicamente --}}
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- Paginación --}}
                                            <div class="mt-3 row" id="paginacionContainer" style="display: none;">
                                                <div class="col-md-6">
                                                    <p class="text-muted">Mostrando <span id="totalMostrado">0</span> de
                                                        <span id="totalRegistros">0</span> órdenes</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <nav>
                                                        <ul class="justify-content-end pagination" id="paginacion">
                                                            {{-- Se genera dinámicamente --}}
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>

                                            {{-- Leyenda de Estados --}}
                                            <div class="mt-3 alert alert-info" role="alert" style="display: none;"
                                                id="leyendaEstados">
                                                <h6 class="alert-heading"><i class="mdi mdi-information"></i> Leyenda de
                                                    Estados:</h6>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="mb-1">
                                                            <span class="badge badge-success"
                                                                style="background-color: #28a745;">FACTURADO</span> y
                                                            <span class="badge badge-success"
                                                                style="background-color: #28a745;">ENTREGADO</span>
                                                            = Color <strong>Verde</strong>
                                                        </p>
                                                        <p class="mb-1">
                                                            <span class="badge badge-warning"
                                                                style="background-color: #ffc107; color: #000;">EN
                                                                TRANSITO</span> y
                                                            <span class="badge badge-warning"
                                                                style="background-color: #ffc107; color: #000;">EN
                                                                SEDE</span>
                                                            = Color <strong>Amarillo</strong>
                                                        </p>
                                                        <p class="mb-0">
                                                            Todos los demás estados = Color <strong>Negro</strong>
                                                        </p>
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
    </div>

    {{-- Estilos adicionales --}}
    <style>
        .icon-lg {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            cursor: pointer;
        }

        .form-select,
        .form-control {
            border-radius: 5px;
        }

        .btn-icon-text i {
            font-size: 16px;
        }

        /* Columna Ubicación en AZUL - DESTACADA */
        thead th.bg-primary {
            background-color: #4B49AC !important;
            color: white !important;
            font-weight: bold;
        }

        /* Toda la columna de ubicación con fondo azul claro */
        tbody td.col-ubicacion {
            background-color: rgba(75, 73, 172, 0.15) !important;
            font-weight: 600;
            color: #4B49AC;
            border-left: 3px solid #4B49AC;
            border-right: 3px solid #4B49AC;
        }

        /* Tabla más compacta */
        .table-sm td, .table-sm th {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    </style>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let ordenesData = [];
        let ordenesFiltered = [];
        let currentPage = 1;
        const perPage = 15;

        $(document).ready(function() {
            // Cargar datos al iniciar
            cargarOrdenes();
            cargarSedes();

            // Seleccionar todos los checkboxes
            $('#selectAll').on('change', function() {
                $('.row-checkbox').prop('checked', this.checked);
            });

            // Eventos de filtros
            $('#filtroSede, #filtroEstado, #filtroTipoOrden').on('change', function() {
                aplicarFiltros();
            });

            // Buscar
            $('#btnBuscar').on('click', function() {
                aplicarFiltros();
            });

            $('#buscarPedido').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    aplicarFiltros();
                }
            });

            // Limpiar filtros
            $('#btnLimpiarFiltros').on('click', function() {
                $('#filtroSede').val('');
                $('#filtroEstado').val('');
                $('#filtroTipoOrden').val('');
                $('#buscarPedido').val('');
                cargarOrdenes();
            });

            // Recargar datos (sin caché)
            $('#btnRecargar').on('click', function() {
                cargarOrdenes(true);
            });

            // Ordenar por fecha
            let ordenAscendente = true;
            $('#btnOrdenarFecha').on('click', function() {
                ordenAscendente = !ordenAscendente;
                ordenarPorFecha(ordenAscendente);
                $(this).find('i').toggleClass('mdi-sort-calendar-ascending mdi-sort-calendar-descending');
            });

            // Exportar
            $('#btnExportar').on('click', function() {
                exportarCSV();
            });
        });

        /**
         * Cargar sedes disponibles
         */
        function cargarSedes() {
            $.ajax({
                url: '/api/ordenes/sedes',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        let options = '<option value="">Todas las sucursales</option>';
                        response.data.forEach(sede => {
                            options += `<option value="${sede}">${sede}</option>`;
                        });
                        $('#filtroSede').html(options);
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar sedes:', xhr.responseText);
                }
            });
        }

        /**
         * Cargar órdenes desde el backend
         */
        function cargarOrdenes(forceRefresh = false) {
            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();
            $('#paginacionContainer').hide();
            $('#leyendaEstados').hide();

            const params = {
                sede: $('#filtroSede').val(),
                estado: $('#filtroEstado').val(),
                tipo_orden: $('#filtroTipoOrden').val(),
                buscar: $('#buscarPedido').val()
            };

            if (forceRefresh) {
                params.nocache = Date.now();
            }

            $.ajax({
                url: '/api/ordenes',
                method: 'GET',
                data: params,
                timeout: 30000,
                success: function(response) {
                    if (response.success) {
                        ordenesData = response.data;
                        ordenesFiltered = ordenesData;

                        // Actualizar estadísticas
                        actualizarEstadisticas(response.stats);

                        // Renderizar tabla
                        currentPage = 1;
                        renderizarTabla();

                        // Mostrar tabla
                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                        $('#paginacionContainer').show();
                        $('#leyendaEstados').show();

                        // Inicializar gráfico
                        inicializarGrafico();

                        // Mostrar toast de éxito
                        if (forceRefresh) {
                            mostrarToast('Datos actualizados correctamente', 'success');
                        }
                    } else {
                        mostrarError(response.message || 'Error al cargar los datos');
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error de conexión con el servidor';

                    if (xhr.status === 500) {
                        mensaje = 'Error del servidor. Verifica la configuración de Google Sheets.';
                    } else if (xhr.status === 404) {
                        mensaje = 'No se encontró el endpoint. Verifica las rutas de la API.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    } else if (xhr.statusText) {
                        mensaje = `Error: ${xhr.statusText}`;
                    }

                    mostrarError(mensaje);
                    console.error('Error detallado:', xhr);
                }
            });
        }

        /**
         * Aplicar filtros sin recargar desde el servidor
         */
        function aplicarFiltros() {
            cargarOrdenes();
        }

        /**
         * Actualizar estadísticas en los cards
         */
        function actualizarEstadisticas(stats) {
            $('#totalOrdenes').text(stats.total.toLocaleString());
            $('#ordenesFacturar').text(stats.disponibles_facturar.toLocaleString());
            $('#ordenesTransito').html(
                `<span class="text-warning">${stats.en_transito}</span> / <span class="text-info">${stats.en_sede}</span>`
            );
        }

        /**
         * Renderizar tabla
         */
        function renderizarTabla() {
            const inicio = (currentPage - 1) * perPage;
            const fin = inicio + perPage;
            const ordenesPagina = ordenesFiltered.slice(inicio, fin);

            let html = '';

            if (ordenesPagina.length === 0) {
                html = `
                <tr>
                    <td colspan="18" class="py-5 text-center">
                        <i class="text-muted mdi mdi-database-search mdi-48px"></i>
                        <p class="mt-3 text-muted">No se encontraron órdenes con los filtros aplicados</p>
                    </td>
                </tr>
            `;
            } else {
                ordenesPagina.forEach(orden => {
                    const badgeEstado = obtenerBadgeEstado(orden.estado_orden || '');
                    const badgeUbicacion = obtenerBadgeEstado(orden.ubicacion_orden || '');
                    const badgeSede = obtenerBadgeSede(orden.descripcion_sede || '');

                    html += `
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input row-checkbox" type="checkbox" value="${orden.numero_orden || ''}">
                            </div>
                        </td>
                        <td>${badgeSede}</td>
                        <td><a href="#" class="font-weight-bold text-primary">#${orden.numero_orden || 'N/A'}</a></td>
                        <td><small>${orden.RUC || '-'}</small></td>
                        <td title="${orden.Cliente || '-'}">${truncarTexto(orden.Cliente || '-', 25)}</td>
                        <td><small>${orden['Diseño'] || '-'}</small></td>
                        <td title="${orden.descripcion_producto || '-'}">${truncarTexto(orden.descripcion_producto || '-', 30)}</td>
                        <td><small>${orden.orden_compra || '-'}</small></td>
                        <td><small>${orden.fecha_orden || '-'}</small></td>
                        <td><small>${orden.hora_orden || '-'}</small></td>
                        <td><span class="badge badge-${orden.tipo_orden === 'FABRICACION' ? 'primary' : 'secondary'}">${orden.tipo_orden || '-'}</span></td>
                        <td><small>${truncarTexto(orden.nombre_usuario || '-', 20)}</small></td>
                        <td>${badgeEstado}</td>
                        <td class="col-ubicacion"><strong>${orden.ubicacion_orden || '-'}</strong></td>
                        <td><small>${orden.descripcion_tallado || '-'}</small></td>
                        <td><small>${orden.Tratamiento || '-'}</small></td>
                        <td class="font-weight-bold text-primary">${orden.Lead_Time || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-icon-text" title="Ver detalles" onclick="verDetalle('${orden.numero_orden}')">
                                <i class="mdi mdi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                });
            }

            $('#tablaOrdenesBody').html(html);

            // Actualizar paginación
            actualizarPaginacion();

            // Actualizar contador
            $('#totalMostrado').text(ordenesPagina.length);
            $('#totalRegistros').text(ordenesFiltered.length);
        }

        /**
         * Truncar texto largo
         */
        function truncarTexto(texto, maxLength) {
            if (!texto || texto.length <= maxLength) return texto;
            return texto.substring(0, maxLength) + '...';
        }

        /**
         * Obtener badge según estado
         */
        function obtenerBadgeEstado(estado) {
            if (!estado) return '<span class="badge badge-dark">Sin estado</span>';

            const estadoUpper = estado.toUpperCase();

            if (estadoUpper.includes('FACTURADO') || estadoUpper.includes('ENTREGADO')) {
                return `<span class="badge badge-success" style="background-color: #28a745;">
                <i class="mdi mdi-check-circle"></i> ${estado}
            </span>`;
            } else if (estadoUpper.includes('TRANSITO')) {
                return `<span class="badge badge-warning" style="background-color: #ffc107; color: #000;">
                <i class="mdi mdi-truck-delivery"></i> ${estado}
            </span>`;
            } else if (estadoUpper.includes('SEDE')) {
                return `<span class="badge badge-warning" style="background-color: #ffc107; color: #000;">
                <i class="mdi mdi-home-map-marker"></i> ${estado}
            </span>`;
            } else {
                return `<span class="badge badge-dark">
                <i class="mdi-clock-outline mdi"></i> ${estado}
            </span>`;
            }
        }

        /**
         * Obtener badge según sede
         */
        function obtenerBadgeSede(sede) {
            if (!sede) return '<span class="badge badge-secondary">Sin sede</span>';
            
            const badges = {
                'LOS OLIVOS': 'info',
                'AREQUIPA': 'primary',
                'TRUJILLO': 'success',
                'CUSCO': 'warning',
                'PIURA': 'danger',
                'LIMA': 'info'
            };

            const tipo = badges[sede.toUpperCase()] || 'secondary';
            return `<span class="badge badge-${tipo}">${sede}</span>`;
        }

        /**
         * Actualizar paginación
         */
        function actualizarPaginacion() {
            const totalPaginas = Math.ceil(ordenesFiltered.length / perPage);

            if (totalPaginas <= 1) {
                $('#paginacion').html('');
                return;
            }

            let html = '';

            // Anterior
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">Anterior</a>
        </li>`;

            // Páginas
            for (let i = 1; i <= totalPaginas; i++) {
                if (i === 1 || i === totalPaginas || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
                </li>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Siguiente
            html += `<li class="page-item ${currentPage === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1}); return false;">Siguiente</a>
        </li>`;

            $('#paginacion').html(html);
        }

        /**
         * Cambiar página
         */
        function cambiarPagina(pagina) {
            const totalPaginas = Math.ceil(ordenesFiltered.length / perPage);
            if (pagina >= 1 && pagina <= totalPaginas) {
                currentPage = pagina;
                renderizarTabla();
                $('html, body').animate({
                    scrollTop: $('#tablaContainer').offset().top - 100
                }, 'fast');
            }
        }

        /**
         * Ordenar por fecha
         */
        function ordenarPorFecha(ascendente) {
            ordenesFiltered.sort((a, b) => {
                const fechaA = convertirFecha(a.fecha_orden);
                const fechaB = convertirFecha(b.fecha_orden);
                return ascendente ? fechaA - fechaB : fechaB - fechaA;
            });
            renderizarTabla();
        }

        /**
         * Convertir fecha DD/MM/YYYY a objeto Date
         */
        function convertirFecha(fechaStr) {
            if (!fechaStr) return new Date(0);
            const partes = fechaStr.split('/');
            if (partes.length === 3) {
                return new Date(partes[2], partes[1] - 1, partes[0]);
            }
            return new Date(fechaStr);
        }

        /**
         * Ver detalle de orden
         */
        function verDetalle(numeroOrden) {
            const orden = ordenesData.find(o => o.numero_orden === numeroOrden);
            if (orden) {
                // Aquí puedes abrir un modal con los detalles
                alert('Detalle de orden #' + numeroOrden + '\n\nEsta función se puede expandir con un modal');
            }
        }

        /**
         * Exportar a CSV
         */
        function exportarCSV() {
            if (ordenesFiltered.length === 0) {
                alert('No hay datos para exportar');
                return;
            }

            let csv = 'Sede,# Orden,RUC,Cliente,Diseño,Producto,O. Compra,Fecha,Hora,Tipo,Usuario,Estado,Ubicación,Tallado,Tratamiento,Lead Time\n';

            ordenesFiltered.forEach(orden => {
                csv += [
                    orden.descripcion_sede || '',
                    orden.numero_orden || '',
                    orden.RUC || '',
                    `"${(orden.Cliente || '').replace(/"/g, '""')}"`,
                    orden['Diseño'] || '',
                    `"${(orden.descripcion_producto || '').replace(/"/g, '""')}"`,
                    orden.orden_compra || '',
                    orden.fecha_orden || '',
                    orden.hora_orden || '',
                    orden.tipo_orden || '',
                    orden.nombre_usuario || '',
                    orden.estado_orden || '',
                    orden.ubicacion_orden || '',
                    orden.descripcion_tallado || '',
                    orden.Tratamiento || '',
                    orden.Lead_Time || ''
                ].join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `ordenes_trimax_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        /**
         * Mostrar error
         */
        function mostrarError(mensaje) {
            $('#loadingSpinner').hide();
            $('#errorMessage').show();
            $('#errorText').text(mensaje);
            $('#tablaContainer').hide();
            $('#paginacionContainer').hide();
            $('#leyendaEstados').hide();
        }

        /**
         * Mostrar toast
         */
        function mostrarToast(mensaje, tipo = 'success') {
            // Implementa tu sistema de notificaciones aquí
            console.log(`[${tipo}] ${mensaje}`);
        }

        /**
         * Inicializar gráfico sparkline
         */
        function inicializarGrafico() {
            const ctx = document.getElementById('sparklineChart');
            if (ctx && !ctx.chartInstance) {
                ctx.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['', '', '', '', '', '', '', ''],
                        datasets: [{
                            data: [12, 19, 15, 25, 22, 30, 28, 35],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    }
                });
            }
        }
    </script>
@endsection