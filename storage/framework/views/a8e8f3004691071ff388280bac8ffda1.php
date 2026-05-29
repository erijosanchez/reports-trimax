<?php $__env->startSection('title', 'Consulta de Orden'); ?>

<?php $__env->startSection('content'); ?>
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

                            
                            <div class="mb-4 row">
                                <div class="mb-3 mb-md-0 col-md-3">
                                    <div class="h-100 card">
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
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 mb-md-0 col-md-3">
                                    <div class="h-100 card">
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

                                <div class="mb-3 mb-md-0 col-md-3">
                                    <div class="h-100 card">
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

                                
                                <div class="mb-3 mb-md-0 col-md-3">
                                    <div class="border-info h-100 card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Importe Total</p>
                                                    <h4 class="mb-0 font-weight-bold text-info" id="importeTotal">
                                                        <span class="spinner-border spinner-border-sm"></span>
                                                    </h4>
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-map-marker"></i> En Sede y Tránsito
                                                    </small>
                                                </div>
                                                <div class="bg-info-gradient rounded-circle text-white icon-lg">
                                                    <i class="mdi mdi-cash-multiple mdi-24px"></i>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <small class="d-block text-muted">
                                                    <i class="mdi mdi-home-variant"></i> En Sede:
                                                    <strong class="text-info" id="importeSede">S/ 0.00</strong>
                                                </small>
                                                <small class="d-block text-muted">
                                                    <i class="mdi mdi-truck"></i> En Tránsito:
                                                    <strong class="text-warning" id="importeTransito">S/ 0.00</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <div class="btn-group btn-group-lg w-100" role="group">
                                        <input type="radio" class="btn-check" name="vistaMode" id="vistaReciente"
                                            autocomplete="off" checked>
                                        <label class="btn-outline-primary btn" for="vistaReciente">
                                            <i class="mdi mdi-clock-fast"></i> Últimas 1000 Órdenes (Carga Rápida)
                                        </label>

                                        <input type="radio" class="btn-check" name="vistaMode" id="vistaHistorico"
                                            autocomplete="off">
                                        <label class="btn-outline-warning btn" for="vistaHistorico">
                                            <i class="mdi mdi-database-search"></i> Búsqueda en Histórico
                                        </label>
                                    </div>
                                    <small class="d-block mt-2 text-muted" id="mensajeVista">
                                        ⚡ Mostrando las 1000 órdenes más recientes para carga rápida
                                    </small>
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="grid-margin col-lg-12 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            
                                            <div class="mb-3 row">
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
                                                        <option value="SOLICITADO">Solicitado</option>
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
                                                        <button class="btn btn-sm btn-info" id="btnOrdenarFecha">
                                                            <i class="mdi mdi-sort-calendar-ascending"></i> Ordenar por
                                                            Fecha
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div id="loadingSpinner" class="py-5 text-center">
                                                <div class="spinner-border text-primary" role="status"
                                                    style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3">Cargando todas las ordenes...</p>
                                            </div>

                                            
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            
                                            <div class="table-responsive p-2" id="tablaContainer" style="display: none;">
                                                <table class="table table-hover table-sm" id="tablaOrdenes"
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
                                                        
                                                    </tbody>
                                                </table>
                                            </div>

                                            
                                            <div class="mt-3 row" id="paginacionContainer" style="display: none;">
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
                                                        <ul class="justify-content-end pagination" id="paginacion">
                                                            
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

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let ordenesData = [];
        let ordenesFiltered = [];
        let currentPage = 1;
        const perPage = 100;
        let modoHistorico = false;

        $(document).ready(function() {
            // Cargar estadísticas primero
            cargarEstadisticasGenerales();

            // Cargar vista de tabla
            cargarVista();
            cargarSedes();

            // Toggle entre vistas
            $('input[name="vistaMode"]').on('change', function() {
                modoHistorico = $('#vistaHistorico').is(':checked');

                if (modoHistorico) {
                    $('#mensajeVista').html(
                        '🔍 <strong>Modo histórico activado</strong> - Buscando todos los registros registros (puede tardar)'
                    );
                } else {
                    $('#mensajeVista').html('⚡ Mostrando las 1000 órdenes más recientes para carga rápida');
                }

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
                cargarEstadisticasGenerales(true);
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
         * Cargar estadísticas generales
         */
        function cargarEstadisticasGenerales(forceRefresh = false) {
            const startTime = Date.now();

            const params = {};
            if (forceRefresh) {
                params.nocache = Date.now();
            }

            $.ajax({
                url: "<?php echo route('comercial.ordenes.estadisticas'); ?>",
                method: 'GET',
                data: params,
                timeout: 60000,
                success: function(response) {
                    const endTime = Date.now();
                    const loadTime = ((endTime - startTime) / 1000).toFixed(2);

                    if (response.success) {
                        console.log('📊 Estadísticas generales cargadas en', loadTime, 's');
                        actualizarEstadisticas(response.stats);

                        if (forceRefresh) {
                            console.log('✅ Estadísticas actualizadas');
                        }
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al cargar estadísticas:', xhr);
                }
            });
        }

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
         * Cargar solo 100 recientes
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
                url: "<?php echo route('comercial.ordenes.recientes'); ?>",
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
                                timer: 2000,
                                showConfirmButton: false
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
         * Cargar histórico completo
         */
        function cargarHistorico(forceRefresh = false) {
            const startTime = Date.now();
            $('#loadingSpinner').show();
            $('#errorMessage, #tablaContainer, #paginacionContainer').hide();

            // ✅ limite: 0 = sin límite, trae TODOS los registros
            const params = {
                sede: $('#filtroSede').val(),
                estado: $('#filtroEstado').val(),
                tipo_orden: $('#filtroTipoOrden').val(),
                buscar: $('#buscarPedido').val(),
                limite: 0,
            };
            if (forceRefresh) params.nocache = Date.now();

            $.ajax({
                url: "<?php echo route('comercial.ordenes.obtener'); ?>",
                method: 'GET',
                data: params,
                timeout: 120000,
                success: function(response) {
                    const loadTime = ((Date.now() - startTime) / 1000).toFixed(2);
                    if (response.success) {
                        ordenesData = response.data;
                        ordenesFiltered = ordenesData;
                        currentPage = 1;
                        renderizarTabla();
                        $('#loadingSpinner').hide();
                        $('#tablaContainer, #paginacionContainer').show();
                        $('#tiempoCarga').text('Cargado en ' + loadTime + 's');
                        if (forceRefresh) Swal.fire({
                            icon: 'success',
                            title: '¡Histórico Cargado!',
                            text: ordenesData.length.toLocaleString() + ' registros en ' + loadTime +
                                's',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    mostrarError('Error al cargar histórico');
                }
            });
        }

        function aplicarFiltros() {
            currentPage = 1;
            cargarVista();
        }

        function cargarSedes() {
            $.ajax({
                url: "<?php echo route('comercial.ordenes.sedes'); ?>",
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

        function actualizarEstadisticas(stats) {
            $('#totalOrdenes').text(stats.total.toLocaleString());
            $('#ordenesFacturar').text(stats.disponibles_facturar.toLocaleString());
            $('#ordenesTransito').html(
                `<span class="text-warning">${stats.en_transito}</span> / <span class="text-info">${stats.en_sede}</span>`
            );

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
         * 🔥 Limpiar importe - VERSIÓN CON DETECCIÓN DE FECHAS
         */
        function limpiarImporte(importeStr) {
            if (!importeStr) return 0;

            // Convertir a string
            let limpio = String(importeStr).trim();

            // Si está vacío, retornar 0
            if (limpio === '' || limpio === '-') return 0;

            // 🔥 DETECTAR FECHAS (2 o más barras = fecha)
            if ((limpio.match(/\//g) || []).length >= 2) {
                return 0;
            }

            // 🔥 DETECTAR si tiene 4 dígitos después de barra (año)
            if (/\/\d{4}/.test(limpio)) {
                return 0;
            }

            // Quitar símbolos de moneda
            limpio = limpio.replace(/S\/\s?/g, '').replace(/\s/g, '');

            // Quitar comas (separador de miles)
            limpio = limpio.replace(/,/g, '');

            // Convertir a número
            const numero = parseFloat(limpio);

            // 🔥 VALIDAR que no sea un número absurdo
            if (numero > 100000) {
                return 0;
            }

            return isNaN(numero) ? 0 : numero;
        }

        function renderizarTabla() {
            const inicio = (currentPage - 1) * perPage;
            const fin = inicio + perPage;
            const ordenesPagina = ordenesFiltered.slice(inicio, fin);

            console.log(`📄 Renderizando página ${currentPage}: filas ${inicio}-${fin}`);

            let html = '';

            if (ordenesPagina.length === 0) {
                html = `
                    <tr>
                        <td colspan="18" class="py-5 text-center">
                            <i class="text-muted mdi mdi-database-search mdi-48px"></i>
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
                    const importe = orden.importe || '0.00';
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
                            <td><input class="form-check-input row-checkbox" type="checkbox" value="${numeroOrden}"></td>
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
            return texto.length > max ? texto.substring(0, max) + '...' : texto;
        }

        function obtenerBadgeEstado(ubicacion) {
            if (!ubicacion || ubicacion === '-') return '<span class="badge badge-secondary">Sin estado</span>';
            const ub = String(ubicacion).toUpperCase();
            if (ub.includes('FACTURADO') || ub.includes('ENTREGADO')) {
                return `<span class="badge badge-success"><i class="mdi mdi-check-circle"></i> ${truncar(ubicacion, 20)}</span>`;
            } else if (ub.includes('TRANSITO')) {
                return `<span class="text-dark badge badge-warning"><i class="mdi mdi-truck"></i> ${truncar(ubicacion, 20)}</span>`;
            } else if (ub.includes('SEDE')) {
                return `<span class="text-dark badge badge-warning"><i class="mdi mdi-home"></i> ${truncar(ubicacion, 20)}</span>`;
            }
            return `<span class="badge badge-dark">${truncar(ubicacion, 20)}</span>`;
        }

        function obtenerBadgeSede(sede) {
            if (!sede || sede === '-') return '-';
            return `<span class="badge badge-primary">${sede}</span>`;
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

            let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1}); return false;">Anterior</a>
            </li>`;

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

        function mostrarError(mensaje) {
            $('#loadingSpinner').hide();
            $('#errorMessage').show();
            $('#errorText').text(mensaje);
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/consulta-orden.blade.php ENDPATH**/ ?>