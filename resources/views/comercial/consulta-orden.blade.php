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
                    <div class="tab-content tab-content-basic mt-4">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            {{-- Cards de Estadísticas --}}
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Total de órdenes</p>
                                                    <h2 class="mb-0 font-weight-bold" id="totalOrdenes">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-success">
                                                        <i class="mdi mdi-arrow-up"></i> Actualizado
                                                    </small>
                                                </div>
                                                <div class="icon-lg bg-primary-gradient text-white rounded-circle">
                                                    <i class="mdi mdi-cart-outline mdi-24px"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Órdenes disponibles para facturar</p>
                                                    <h2 class="mb-0 font-weight-bold" id="ordenesFacturar">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-success">
                                                        <i class="mdi mdi-arrow-up"></i> En proceso
                                                    </small>
                                                </div>
                                                <div class="icon-lg bg-success-gradient text-white rounded-circle">
                                                    <i class="mdi mdi-file-document-box mdi-24px"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">En Tránsito / En Sede</p>
                                                    <h2 class="mb-0 font-weight-bold" id="ordenesTransito">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h2>
                                                    <small class="text-muted">Órdenes en proceso</small>
                                                </div>
                                                <div class="icon-lg bg-warning-gradient text-white rounded-circle">
                                                    <i class="mdi mdi-truck-delivery mdi-24px"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 🔥 NUEVO CARD DE IMPORTE --}}
                                <div class="col-md-3">
                                    <div class="card h-100 border-info">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted mb-1">Importe Total</p>
                                                    <h4 class="mb-0 font-weight-bold text-info" id="importeTotal">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h4>
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-map-marker"></i> En Sede y Tránsito
                                                    </small>
                                                </div>
                                                <div class="icon-lg bg-info-gradient text-white rounded-circle">
                                                    <i class="mdi mdi-cash-multiple mdi-24px"></i>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <small class="text-muted d-block">
                                                    <i class="mdi mdi-home-variant"></i> En Sede:
                                                    <strong class="text-info" id="importeSede">S/ 0.00</strong>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="mdi mdi-truck"></i> En Tránsito:
                                                    <strong class="text-warning" id="importeTransito">S/ 0.00</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="btn-group btn-group-lg w-100" role="group">
                                        <input type="radio" class="btn-check" name="vistaMode" id="vistaReciente"
                                            autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" for="vistaReciente">
                                            <i class="mdi mdi-clock-fast"></i> Últimas 100 Órdenes (Carga Rápida)
                                        </label>

                                        <input type="radio" class="btn-check" name="vistaMode" id="vistaHistorico"
                                            autocomplete="off">
                                        <label class="btn btn-outline-warning" for="vistaHistorico">
                                            <i class="mdi mdi-database-search"></i> Búsqueda en Histórico
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="mensajeVista">
                                        ⚡ Mostrando las 100 órdenes más recientes para carga rápida
                                    </small>
                                </div>
                            </div>

                            {{-- Filtros y Tabla --}}
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- Sección de Filtros --}}
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label"><i class="mdi mdi-office-building"></i>
                                                        Filtrar por Sede</label>
                                                    <select class="form-select" id="filtroSede">
                                                        <option value="">Todas las sucursales</option>
                                                        <option value="LOS OLIVOS">LOS OLIVOS</option>
                                                        <option value="AREQUIPA">AREQUIPA</option>
                                                        <option value="TRUJILLO">TRUJILLO</option>
                                                        <option value="CUSCO">CUSCO</option>
                                                        <option value="PIURA">PIURA</option>
                                                        <option value="CHICLAYO">CHICLAYO</option>
                                                        <option value="JUNIN">JUNIN</option>
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

                                            <div class="row mb-3">
                                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            id="btnLimpiarFiltros">
                                                            <i class="mdi mdi-filter-remove"></i> Limpiar Filtros
                                                        </button>
                                                        <button class="btn btn-sm btn-info" id="btnRecargar">
                                                            <i class="mdi mdi-refresh"></i> Recargar
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-info" id="btnOrdenarFecha">
                                                            <i class="mdi mdi-sort-calendar-ascending"></i> Ordenar por
                                                            Fecha
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
                                                <p class="mt-3">Cargando órdenes desde Google Sheets...</p>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            {{-- Tabla con columnas más anchas --}}
                                            <div class="table-responsive p-2" id="tablaContainer" style="display: none;">
                                                <table class="table table-hover table-sm " id="tablaOrdenes"
                                                    style="min-width: 2000px;">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="min-width: 100px;">

                                                                <input class="form-check-input" type="checkbox"
                                                                    id="selectAll">

                                                            </th>
                                                            <th style="min-width: 120px;">Sede</th>
                                                            <th style="min-width: 100px;">N° Orden</th>
                                                            <th style="min-width: 120px;">RUC</th>
                                                            <th style="min-width: 200px;">Cliente</th>
                                                            <th style="min-width: 150px;">Diseño</th>
                                                            <th style="min-width: 200px;">Descripción Producto</th>
                                                            <th class="bg-success text-white" style="min-width: 120px;">
                                                                Importe</th>
                                                            <th style="min-width: 120px;">Orden Compra</th>
                                                            <th style="min-width: 100px;">Fecha</th>
                                                            <th style="min-width: 80px;">Hora</th>
                                                            <th style="min-width: 120px;">Tipo Orden</th>
                                                            <th style="min-width: 150px;">Usuario</th>
                                                            <th style="min-width: 120px;">Estado</th>
                                                            <th class="bg-primary text-white" style="min-width: 180px;">
                                                                Ubicación</th>
                                                            <th style="min-width: 150px;">Descripción Tallado</th>
                                                            <th style="min-width: 120px;">Tratamiento</th>
                                                            <th class="bg-info text-white" style="min-width: 100px;">Lead
                                                                Time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaOrdenesBody">
                                                        {{-- Los datos se cargan dinámicamente --}}
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- Paginación mejorada --}}
                                            <div class="row mt-3" id="paginacionContainer" style="display: none;">
                                                <div class="col-md-6">
                                                    <p class="text-muted">
                                                        Mostrando <span id="rangoInicio">0</span> - <span
                                                            id="rangoFin">0</span>
                                                        de <span id="totalRegistros">0</span> órdenes
                                                        <span class="badge badge-info" id="tiempoCarga"></span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <nav>
                                                        <ul class="pagination justify-content-end" id="paginacion">
                                                            {{-- Se genera dinámicamente --}}
                                                        </ul>
                                                    </nav>
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
            background: #667eea;
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

        /* Columna Lead Time en azul claro */
        tbody td:nth-child(17) {
            background-color: rgba(23, 162, 184, 0.1);
            font-weight: 600;
            color: #17a2b8;
        }

        /* Columna Ubicación en azul */
        tbody td:nth-child(14) {
            background-color: rgba(75, 73, 172, 0.1);
            font-weight: 600;
        }

        /* Tabla más legible */
        .table-sm td,
        .table-sm th {
            padding: 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        /* Scroll horizontal suave */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Card de importe */
        .bg-info-gradient {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        }

        /* Columna Importe en verde claro */
        tbody td:nth-child(8) {
            background-color: rgba(40, 167, 69, 0.1);
            font-weight: 700;
            color: #28a745;
            text-align: right;
        }

        /* Ajustar índices de otras columnas resaltadas */
        tbody td:nth-child(15) {
            /* Ubicación */
            background-color: rgba(75, 73, 172, 0.1);
            font-weight: 600;
        }

        tbody td:nth-child(18) {
            /* Lead Time */
            background-color: rgba(23, 162, 184, 0.1);
            font-weight: 600;
            color: #17a2b8;
        }
    </style>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 🔥 SISTEMA DE CARGA PROGRESIVA ESTILO MINECRAFT
        let ordenesData = []; // Todos los datos
        let ordenesFiltered = []; // Datos filtrados
        let currentPage = 1;
        const perPage = 20; // Solo 20 por página
        let modoHistorico = false;

        $(document).ready(function() {
            // 🔥 CARGAR VISTA RECIENTE POR DEFECTO
            cargarVista();
            cargarSedes();

            // 🔥 TOGGLE ENTRE VISTAS
            $('input[name="vistaMode"]').on('change', function() {
                modoHistorico = $('#vistaHistorico').is(':checked');

                if (modoHistorico) {
                    $('#mensajeVista').html(
                        '🔍 <strong>Modo histórico activado</strong> - Buscando en todos los registros (puede tardar)'
                    );
                } else {
                    $('#mensajeVista').html('⚡ Mostrando las 100 órdenes más recientes para carga rápida');
                }

                // Limpiar filtros al cambiar
                $('#filtroSede').val('');
                $('#filtroEstado').val('');
                $('#filtroTipoOrden').val('');
                $('#buscarPedido').val('');

                cargarVista();
            });

            $('#selectAll').on('change', function() {
                $('.row-checkbox').prop('checked', this.checked);
            });

            $('#filtroSede, #filtroEstado, #filtroTipoOrden').on('change', function() {
                aplicarFiltros();
            });

            $('#btnBuscar').on('click', function() {
                aplicarFiltros();
            });

            $('#buscarPedido').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    aplicarFiltros();
                }
            });

            $('#btnLimpiarFiltros').on('click', function() {
                $('#filtroSede').val('');
                $('#filtroEstado').val('');
                $('#filtroTipoOrden').val('');
                $('#buscarPedido').val('');
                currentPage = 1;
                cargarVista();
            });

            $('#btnRecargar').on('click', function() {
                ordenesData = [];
                ordenesFiltered = [];
                currentPage = 1;
                cargarVista(true);
            });

            let ordenAscendente = false;
            $('#btnOrdenarFecha').on('click', function() {
                ordenAscendente = !ordenAscendente;
                ordenarPorFecha(ordenAscendente);
                $(this).find('i').toggleClass('mdi-sort-calendar-ascending mdi-sort-calendar-descending');
            });
        });

        /**
         * Cargar vista según el modo
         */
        function cargarVista(forceRefresh = false) {
            if (modoHistorico) {
                cargarHistorico(forceRefresh);
            } else {
                cargarRecientes(forceRefresh);
            }
        }

        /**
         * Cargar solo 100 recientes (RÁPIDO)
         */
        function cargarRecientes(forceRefresh = false) {
            const startTime = Date.now();

            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();
            $('#paginacionContainer').hide();

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
                url: "{{ route('comercial.ordenes.recientes') }}", // 🔥 NUEVA RUTA
                method: 'GET',
                data: params,
                timeout: 60000,
                success: function(response) {
                    const endTime = Date.now();
                    const loadTime = ((endTime - startTime) / 1000).toFixed(2);

                    if (response.success) {
                        ordenesData = response.data;
                        ordenesFiltered = ordenesData;

                        console.log('✅ Vista reciente cargada:', ordenesData.length, 'órdenes en', loadTime,
                            's');

                        actualizarEstadisticas(response.stats);
                        currentPage = 1;
                        renderizarTabla();

                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                        $('#paginacionContainer').show();
                        $('#tiempoCarga').text(`Cargado en ${loadTime}s`);

                        if (forceRefresh) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: `Datos recientes cargados en ${loadTime}s`,
                                timer: 2000
                            });
                        }
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al cargar datos recientes');
                    console.error('❌ Error:', xhr);
                }
            });
        }

        /**
         * CARGAR HISTÓRICO COMPLETO (LENTO - solo cuando buscan)
         */
        function cargarHistorico(forceRefresh = false) {
            const startTime = Date.now();

            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();
            $('#paginacionContainer').hide();

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
                url: "{{ route('comercial.ordenes.obtener') }}", // RUTA ORIGINAL
                method: 'GET',
                data: params,
                timeout: 120000,
                success: function(response) {
                    const endTime = Date.now();
                    const loadTime = ((endTime - startTime) / 1000).toFixed(2);

                    if (response.success) {
                        ordenesData = response.data;
                        ordenesFiltered = ordenesData;

                        console.log('✅ Histórico completo cargado:', ordenesData.length, 'órdenes en', loadTime,
                            's');

                        actualizarEstadisticas(response.stats);
                        currentPage = 1;
                        renderizarTabla();

                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                        $('#paginacionContainer').show();
                        $('#tiempoCarga').text(`Cargado en ${loadTime}s`);

                        if (forceRefresh) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Histórico Cargado!',
                                text: `${ordenesData.length} registros en ${loadTime}s`,
                                timer: 3000
                            });
                        }
                    }
                },
                error: function(xhr) {
                    mostrarError('Error al cargar histórico completo');
                    console.error('❌ Error:', xhr);
                }
            });
        }

        function aplicarFiltros() {
            currentPage = 1;
            cargarVista();
        }

        function cargarSedes() {
            $.ajax({
                url: "{{ route('comercial.ordenes.sedes') }}",
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        let options = '<option value="">Todas las sucursales</option>';
                        response.data.forEach(sede => {
                            options += `<option value="${sede}">${sede}</option>`;
                        });
                        $('#filtroSede').html(options);
                    }
                }
            });
        }

        /**
         * CARGA PROGRESIVA - Solo cargar datos cuando se necesiten
         */
        function cargarEstadisticas(forceRefresh = false) {
            const startTime = Date.now();

            $('#loadingSpinner').show();
            $('#errorMessage').hide();
            $('#tablaContainer').hide();
            $('#paginacionContainer').hide();

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
                url: "{{ route('comercial.ordenes.obtener') }}",
                method: 'GET',
                data: params,
                timeout: 120000, // 2 minutos
                success: function(response) {
                    const endTime = Date.now();
                    const loadTime = ((endTime - startTime) / 1000).toFixed(2);

                    if (response.success) {
                        ordenesData = response.data;
                        ordenesFiltered = ordenesData;

                        console.log('✅ Datos cargados:', ordenesData.length, 'órdenes en', loadTime,
                            'segundos');

                        if (ordenesData.length > 0) {
                            console.log('📋 Columnas:', Object.keys(ordenesData[0]));
                            console.log('📄 Primera orden:', ordenesData[0]);
                        }

                        actualizarEstadisticas(response.stats);
                        currentPage = 1;
                        renderizarTabla();

                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                        $('#paginacionContainer').show();
                        $('#leyendaEstados').show();
                        $('#tiempoCarga').text(`Cargado en ${loadTime}s`);

                        inicializarGrafico();

                        if (forceRefresh) {
                            alert('✅ Datos actualizados correctamente en ' + loadTime + ' segundos');
                        }
                    } else {
                        mostrarError(response.message || 'Error al cargar los datos');
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error de conexión';
                    if (xhr.status === 500) {
                        mensaje = 'Error del servidor. ' + (xhr.responseJSON?.message || '');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    mostrarError(mensaje);
                    console.error('❌ Error:', xhr);
                }
            });
        }

        function aplicarFiltros() {
            currentPage = 1;
            cargarEstadisticas();
        }

        function actualizarEstadisticas(stats) {
            $('#totalOrdenes').text(stats.total.toLocaleString());
            $('#ordenesFacturar').text(stats.disponibles_facturar.toLocaleString());
            $('#ordenesTransito').html(
                `<span class="text-warning">${stats.en_transito}</span> / <span class="text-info">${stats.en_sede}</span>`
            );

            // NUEVO - Mostrar importes
            $('#importeTotal').html(`S/ ${formatearImporte(stats.importe_total || 0)}`);
            $('#importeSede').text(`S/ ${formatearImporte(stats.importe_sede || 0)}`);
            $('#importeTransito').text(`S/ ${formatearImporte(stats.importe_transito || 0)}`);
        }

        function formatearImporte(valor) {
            if (!valor || isNaN(valor)) return '0.00';
            return parseFloat(valor).toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        /**
         * NUEVA FUNCIÓN - Limpiar importe (quitar S/, comas, etc)
         */
        function limpiarImporte(importeStr) {
            if (!importeStr) return 0;

            // Convertir a string y limpiar
            let limpio = String(importeStr)
                .replace(/S\/\s?/g, '') // Quitar S/
                .replace(/\s/g, '') // Quitar espacios
                .replace(/,/g, ''); // Quitar comas

            const numero = parseFloat(limpio);
            return isNaN(numero) ? 0 : numero;
        }

        /**
         * RENDERIZAR SOLO LA PÁGINA ACTUAL (Estilo Minecraft)
         */
        function renderizarTabla() {
            const inicio = (currentPage - 1) * perPage;
            const fin = inicio + perPage;
            const ordenesPagina = ordenesFiltered.slice(inicio, fin);

            console.log(`📄 Renderizando página ${currentPage}: filas ${inicio}-${fin}`);

            let html = '';

            if (ordenesPagina.length === 0) {
                html = `
            <tr>
                <td colspan="19" class="text-center py-5">
                    <i class="mdi mdi-database-search mdi-48px text-muted"></i>
                    <p class="mt-3 text-muted">No se encontraron órdenes</p>
                </td>
            </tr>
        `;
            } else {
                ordenesPagina.forEach((orden, index) => {
                    const sede = orden.descripcion_sede || '-';
                    const numeroOrden = orden.numero_orden || '-';
                    const ruc = orden.ruc || '-';
                    const cliente = orden.cliente || '-';
                    const diseno = orden.diseño || orden.diseno || '-';
                    const descripcionProducto = orden.descripcion_producto || '-';
                    const importe = orden.importe || '0.00'; // 🔥 NUEVO
                    const ordenCompra = orden.orden_compra || '-';
                    const fechaOrden = orden.fecha_orden || '-';
                    const horaOrden = orden.hora_orden || '-';
                    const tipoOrden = orden.tipo_orden || '-';
                    const nombreUsuario = orden.nombre_usuario || '-';
                    const estadoOrden = orden.estado_orden || '-';
                    const ubicacionOrden = orden.ubicacion_orden || '-';
                    const descripcionTallado = orden.descripcion_tallado || '-';
                    const tratamiento = orden.tratamiento || '-';
                    const leadTime = orden.lead_time || '-';

                    const badgeEstado = obtenerBadgeEstado(ubicacionOrden);
                    const badgeSede = obtenerBadgeSede(sede);
                    const badgeTipoOrden = obtenerBadgeTipoOrden(tipoOrden);

                    html += `
                <tr>
                    <td>
                        <input class="form-check-input row-checkbox" type="checkbox" value="${numeroOrden}">
                    </td>
                    <td>${badgeSede}</td>
                    <td><strong>${numeroOrden}</strong></td>
                    <td>${ruc}</td>
                    <td title="${cliente}">${truncar(cliente, 25)}</td>
                    <td>${diseno}</td>
                    <td title="${descripcionProducto}">${truncar(descripcionProducto, 25)}</td>
                    <td class="text-end"><strong>S/ ${formatearImporte(limpiarImporte(importe))}</strong></td>
                    <td>${ordenCompra}</td>
                    <td><small>${fechaOrden}</small></td>
                    <td><small>${horaOrden}</small></td>
                    <td>${badgeTipoOrden}</td>
                    <td title="${nombreUsuario}">${truncar(nombreUsuario, 20)}</td>
                    <td><small>${estadoOrden}</small></td>
                    <td>${badgeEstado}</td>
                    <td>${truncar(descripcionTallado, 20)}</td>
                    <td>${tratamiento}</td>
                    <td class="font-weight-bold text-info">${leadTime}</td>
                </tr>
            `;
                });
            }

            $('#tablaOrdenesBody').html(html);
            actualizarPaginacion();

            const rangoInicio = ordenesPagina.length > 0 ? inicio + 1 : 0;
            const rangoFin = inicio + ordenesPagina.length;
            $('#rangoInicio').text(rangoInicio);
            $('#rangoFin').text(rangoFin);
            $('#totalRegistros').text(ordenesFiltered.length);
        }

        function truncar(texto, max) {
            if (!texto || texto === '-') return '-';
            texto = String(texto);
            if (texto.length <= max) return texto;
            return texto.substring(0, max) + '...';
        }

        function obtenerBadgeEstado(ubicacion) {
            if (!ubicacion || ubicacion === '-') return '<span class="badge badge-secondary">Sin estado</span>';

            const ub = String(ubicacion).toUpperCase();

            if (ub.includes('FACTURADO') || ub.includes('ENTREGADO')) {
                return `<span class="badge badge-success">
                <i class="mdi mdi-check-circle"></i> ${truncar(ubicacion, 20)}
            </span>`;
            } else if (ub.includes('TRANSITO')) {
                return `<span class="badge badge-warning text-dark">
                <i class="mdi mdi-truck"></i> ${truncar(ubicacion, 20)}
            </span>`;
            } else if (ub.includes('SEDE')) {
                return `<span class="badge badge-warning text-dark">
                <i class="mdi mdi-home"></i> ${truncar(ubicacion, 20)}
            </span>`;
            } else {
                return `<span class="badge badge-dark">${truncar(ubicacion, 20)}</span>`;
            }
        }

        function obtenerBadgeSede(sede) {
            if (!sede || sede === '-') return '-';
            const sedeUpper = String(sede).toUpperCase();
            const badges = {
                'LOS OLIVOS': 'primary',
                'AREQUIPA': 'primary',
                'TRUJILLO': 'primary',
                'CUSCO': 'primary',
                'PIURA': 'primary',
                'CHICLAYO': 'primary',
                'JUNIN': 'primary',
                'ICA': 'primary',
                'HUANCAYO': 'primary',
                'CALLAO': 'success',
                'VENTANILLA': 'success',
                'CAILLOMA': 'primary',
                'SJM': 'primary',
                'COMAS': 'primary',
                'LINCE': 'primary',
                'HUARAZ': 'primary',
                'AYACUCHO': 'primary',
                'HUANUCO': 'primary',
                'CHIMBOTE': 'primary',
                'PUENTE PIEDRA': 'primary',
                'NAPO': 'primary',
                'TACNA': 'primary',
                'ATE': 'primary',
                'CALL CENTER': 'primary',
                'IQUITOS': 'primary',
                'CAJAMARCA': 'primary',
                'PUCALLPA': 'primary',
                'SJL': 'primary',
            };
            const tipo = badges[sedeUpper] || 'secondary';
            return `<span class="badge badge-${tipo}">${sede}</span>`;
        }

        function obtenerBadgeTipoOrden(tipo) {
            if (!tipo || tipo === '-') return '-';
            const color = String(tipo).toUpperCase() === 'FABRICACION' ? 'primary' : 'success';
            return `<span class="badge badge-${color}">${tipo}</span>`;
        }

        function actualizarPaginacion() {
            const totalPaginas = Math.ceil(ordenesFiltered.length / perPage);
            if (totalPaginas <= 1) {
                $('#paginacion').html('');
                return;
            }

            let html = '';
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">Anterior</a>
        </li>`;

            // Mostrar páginas inteligentemente
            const maxVisible = 7;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPaginas, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html +=
                    `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
            </li>`;
            }

            if (endPage < totalPaginas) {
                if (endPage < totalPaginas - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html +=
                    `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${totalPaginas}); return false;">${totalPaginas}</a></li>`;
            }

            html += `<li class="page-item ${currentPage === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1}); return false;">Siguiente</a>
        </li>`;

            $('#paginacion').html(html);
        }

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

        function ordenarPorFecha(ascendente) {
            ordenesFiltered.sort((a, b) => {
                const fechaA = new Date(convertirFecha(a.fecha_orden));
                const fechaB = new Date(convertirFecha(b.fecha_orden));
                return ascendente ? fechaA - fechaB : fechaB - fechaA;
            });
            renderizarTabla();
        }

        function convertirFecha(fechaStr) {
            if (!fechaStr || fechaStr === '-') return new Date(0);
            const partes = fechaStr.split('/');
            if (partes.length === 3) {
                return new Date(partes[2], partes[1] - 1, partes[0]);
            }
            return new Date(fechaStr);
        }

        function verDetalle(numeroOrden) {
            const orden = ordenesData.find(o => o.numero_orden === numeroOrden);
            if (orden) {
                alert('📋 Detalle de orden #' + numeroOrden + '\n\n✨ Función a implementar');
            }
        }

        function mostrarError(mensaje) {
            $('#loadingSpinner').hide();
            $('#errorMessage').show();
            $('#errorText').text(mensaje);
        }

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
