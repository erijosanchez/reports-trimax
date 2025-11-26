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
                                <div class="col-md-4">
                                    <div class="card">
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
                                            <div class="mt-3">
                                                <canvas id="sparklineChart" height="50"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
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
                                <div class="col-md-4">
                                    <div class="card">
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
                            </div>

                            {{-- Filtros y Tabla --}}
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            {{-- Sección de Filtros --}}
                                            <div class="row mb-3">
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

                                            <div class="row mb-3">
                                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary" id="btnLimpiarFiltros">
                                                            <i class="mdi mdi-filter-remove"></i> Limpiar Filtros
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
                                                            <i class="mdi mdi-sort-calendar-ascending"></i> Ordenar por Fecha
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Loading Spinner --}}
                                            <div id="loadingSpinner" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3">Cargando órdenes desde Google Sheets...</p>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <span id="errorText"></span>
                                            </div>

                                            {{-- Tabla de Órdenes con TODAS las columnas --}}
                                            <div class="table-responsive" id="tablaContainer" style="display: none;">
                                                <table class="table table-hover table-sm" id="tablaOrdenes">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="30">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                                                </div>
                                                            </th>
                                                            <th>Sede</th>
                                                            <th>N° Orden</th>
                                                            <th>RUC</th>
                                                            <th>Cliente</th>
                                                            <th>Diseño</th>
                                                            <th>Descripción Producto</th>
                                                            <th>Orden Compra</th>
                                                            <th>Fecha</th>
                                                            <th>Hora</th>
                                                            <th>Tipo Orden</th>
                                                            <th>Usuario</th>
                                                            <th>Estado</th>
                                                            <th class="bg-primary text-white">Ubicación</th>
                                                            <th>Descripción Tallado</th>
                                                            <th>Tratamiento</th>
                                                            <th class="bg-info text-white">Lead Time</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tablaOrdenesBody">
                                                        {{-- Los datos se cargan dinámicamente --}}
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- Paginación --}}
                                            <div class="row mt-3" id="paginacionContainer" style="display: none;">
                                                <div class="col-md-6">
                                                    <p class="text-muted">Mostrando <span id="totalMostrado">0</span> de <span id="totalRegistros">0</span> órdenes</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <nav>
                                                        <ul class="pagination justify-content-end" id="paginacion">
                                                            {{-- Se genera dinámicamente --}}
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>

                                            {{-- Leyenda de Estados --}}
                                            <div class="alert alert-info mt-3" role="alert" style="display: none;" id="leyendaEstados">
                                                <h6 class="alert-heading"><i class="mdi mdi-information"></i> Leyenda de Estados:</h6>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="mb-1">
                                                            <span class="badge badge-success" style="background-color: #28a745;">FACTURADO</span> y 
                                                            <span class="badge badge-success" style="background-color: #28a745;">ENTREGADO</span> 
                                                            = Color <strong>Verde</strong>
                                                        </p>
                                                        <p class="mb-1">
                                                            <span class="badge badge-warning" style="background-color: #ffc107; color: #000;">EN TRANSITO</span> y 
                                                            <span class="badge badge-warning" style="background-color: #ffc107; color: #000;">EN SEDE</span> 
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

        .form-select, .form-control {
            border-radius: 5px;
        }

        .btn-icon-text i {
            font-size: 16px;
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

        /* Hacer la tabla más compacta */
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
        cargarOrdenes();
        cargarSedes();

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
            cargarOrdenes();
        });

        $('#btnRecargar').on('click', function() {
            cargarOrdenes(true);
        });

        let ordenAscendente = true;
        $('#btnOrdenarFecha').on('click', function() {
            ordenAscendente = !ordenAscendente;
            ordenarPorFecha(ordenAscendente);
            $(this).find('i').toggleClass('mdi-sort-calendar-ascending mdi-sort-calendar-descending');
        });

        $('#btnExportar').on('click', function() {
            window.location.href = "{{ route('comercial.ordenes.exportar') }}";
        });
    });

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
            url: "{{ route('comercial.ordenes.obtener') }}",
            method: 'GET',
            data: params,
            timeout: 60000,
            success: function(response) {
                if (response.success) {
                    ordenesData = response.data;
                    ordenesFiltered = ordenesData;
                    
                    // DEBUG
                    if (ordenesData.length > 0) {
                        console.log('📋 Columnas:', Object.keys(ordenesData[0]));
                        console.log('📋 Primera orden:', ordenesData[0]);
                    }
                    
                    actualizarEstadisticas(response.stats);
                    currentPage = 1;
                    renderizarTabla();
                    
                    $('#loadingSpinner').hide();
                    $('#tablaContainer').show();
                    $('#paginacionContainer').show();
                    $('#leyendaEstados').show();
                    inicializarGrafico();

                    if (forceRefresh) {
                        alert('Datos actualizados correctamente');
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
                console.error('Error:', xhr);
            }
        });
    }

    function aplicarFiltros() {
        cargarOrdenes();
    }

    function actualizarEstadisticas(stats) {
        $('#totalOrdenes').text(stats.total.toLocaleString());
        $('#ordenesFacturar').text(stats.disponibles_facturar.toLocaleString());
        $('#ordenesTransito').html(
            `<span class="text-warning">${stats.en_transito}</span> / <span class="text-info">${stats.en_sede}</span>`
        );
    }

    /**
     * Renderizar tabla con TODAS las columnas del Sheet
     */
    function renderizarTabla() {
        const inicio = (currentPage - 1) * perPage;
        const fin = inicio + perPage;
        const ordenesPagina = ordenesFiltered.slice(inicio, fin);

        let html = '';
        
        if (ordenesPagina.length === 0) {
            html = `
                <tr>
                    <td colspan="18" class="text-center py-5">
                        <i class="mdi mdi-database-search mdi-48px text-muted"></i>
                        <p class="mt-3 text-muted">No se encontraron órdenes</p>
                    </td>
                </tr>
            `;
        } else {
            ordenesPagina.forEach(orden => {
                // Mapear TODAS las columnas exactamente como vienen del Sheet
                const sede = orden.descripcion_sede || '';
                const numeroOrden = orden.numero_orden || '';
                const ruc = orden.RUC || '';
                const cliente = orden.Cliente || '';
                const diseno = orden['Diseño'] || orden.Diseno || '';
                const descripcionProducto = orden.descripcion_producto || '';
                const ordenCompra = orden.orden_compra || '';
                const fechaOrden = orden.fecha_orden || '';
                const horaOrden = orden.hora_orden || '';
                const tipoOrden = orden.tipo_orden || '';
                const nombreUsuario = orden.nombre_usuario || '';
                const estadoOrden = orden.estado_orden || '';
                const ubicacionOrden = orden.ubicacion_orden || '';
                const descripcionTallado = orden.descripcion_tallado || orden.descripcio_n_tallado || '';
                const tratamiento = orden.Tratamiento || '';
                const leadTime = orden['Lead Time'] || orden.Lead_Time || '';
                
                const badgeEstado = obtenerBadgeEstado(ubicacionOrden);
                const badgeSede = obtenerBadgeSede(sede);
                const badgeTipoOrden = obtenerBadgeTipoOrden(tipoOrden);
                
                html += `
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input row-checkbox" type="checkbox" value="${numeroOrden}">
                            </div>
                        </td>
                        <td>${badgeSede}</td>
                        <td><strong>${numeroOrden || 'N/A'}</strong></td>
                        <td><small>${ruc || '-'}</small></td>
                        <td title="${cliente}">${truncar(cliente, 25)}</td>
                        <td>${diseno || '-'}</td>
                        <td title="${descripcionProducto}">${truncar(descripcionProducto, 30)}</td>
                        <td>${ordenCompra || '-'}</td>
                        <td><small>${fechaOrden || '-'}</small></td>
                        <td><small>${horaOrden || '-'}</small></td>
                        <td>${badgeTipoOrden}</td>
                        <td><small>${truncar(nombreUsuario, 20)}</small></td>
                        <td><small>${estadoOrden || '-'}</small></td>
                        <td>${badgeEstado}</td>
                        <td><small>${descripcionTallado || '-'}</small></td>
                        <td><small>${tratamiento || '-'}</small></td>
                        <td class="font-weight-bold text-info">${leadTime || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-icon" title="Ver" onclick="verDetalle('${numeroOrden}')">
                                <i class="mdi mdi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        $('#tablaOrdenesBody').html(html);
        actualizarPaginacion();
        $('#totalMostrado').text(ordenesPagina.length);
        $('#totalRegistros').text(ordenesFiltered.length);
    }

    function truncar(texto, max) {
        if (!texto) return '-';
        if (texto.length <= max) return texto;
        return texto.substring(0, max) + '...';
    }

    function obtenerBadgeEstado(ubicacion) {
        if (!ubicacion) return '<span class="badge badge-secondary">Sin estado</span>';
        
        const ub = ubicacion.toUpperCase();
        
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
        if (!sede) return '-';
        const badges = {
            'LOS OLIVOS': 'info',
            'AREQUIPA': 'primary',
            'TRUJILLO': 'success',
            'CUSCO': 'warning',
            'PIURA': 'danger'
        };
        const tipo = badges[sede.toUpperCase()] || 'secondary';
        return `<span class="badge badge-${tipo}">${sede}</span>`;
    }

    function obtenerBadgeTipoOrden(tipo) {
        if (!tipo) return '-';
        const color = tipo.toUpperCase() === 'FABRICACION' ? 'primary' : 'success';
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

        for (let i = 1; i <= totalPaginas; i++) {
            if (i === 1 || i === totalPaginas || (i >= currentPage - 2 && i <= currentPage + 2)) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
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
            $('html, body').animate({ scrollTop: $('#tablaContainer').offset().top - 100 }, 'fast');
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
        if (!fechaStr) return new Date(0);
        const partes = fechaStr.split('/');
        if (partes.length === 3) {
            return new Date(partes[2], partes[1] - 1, partes[0]);
        }
        return new Date(fechaStr);
    }

    function verDetalle(numeroOrden) {
        const orden = ordenesData.find(o => o.numero_orden === numeroOrden);
        if (orden) {
            alert('Detalle de orden #' + numeroOrden + '\n\nFunción a implementar');
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
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }
    }
</script>
@endsection