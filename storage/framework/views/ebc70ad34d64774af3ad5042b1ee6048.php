

<?php $__env->startSection('title', 'Órdenes por Sede'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="me-2 mdi mdi-store"></i> Órdenes por Sede
                            </h3>
                        </div>
                    </div>

                    <div class="mt-4 tab-content-basic tab-content">
                        <div class="tab-pane fade show active" role="tabpanel">

                            <div class="mb-3 row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="py-3 card-body">
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="mb-0 text-muted small fw-semibold">Mes:</label>
                                                    <select id="filtroMes" class="form-select-sm form-select"
                                                        style="width:130px">
                                                        <?php $meses=[1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; ?>
                                                        <?php $__currentLoopData = $meses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo $num; ?>"
                                                                <?php echo $num == now()->month ? 'selected' : ''; ?>><?php echo $nombre; ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="mb-0 text-muted small fw-semibold">Año:</label>
                                                    <select id="filtroAnio" class="form-select-sm form-select"
                                                        style="width:90px">
                                                        <?php for($y = now()->year; $y >= now()->year - 3; $y--): ?>
                                                            <option value="<?php echo $y; ?>"
                                                                <?php echo $y == now()->year ? 'selected' : ''; ?>><?php echo $y; ?>

                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                                <button id="btnBuscar" class="px-4 btn btn-primary btn-sm">
                                                    <i class="me-1 mdi mdi-magnify"></i> Buscar
                                                </button>
                                                <button id="btnLimpiarCache" class="btn-outline-secondary btn btn-sm"
                                                    title="Recargar datos">
                                                    <i class="mdi mdi-refresh"></i>
                                                </button>
                                                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                                                    <small class="text-muted fw-semibold">Semáforo % facturado:</small>
                                                    <span class="px-2 py-1 badge"
                                                        style="background:#1b5e20;color:#fff;font-size:11px"><i
                                                            class="me-1 mdi mdi-circle"></i>≥ 90%</span>
                                                    <span class="px-2 py-1 badge"
                                                        style="background:#e65100;color:#fff;font-size:11px"><i
                                                            class="me-1 mdi mdi-circle"></i>75–89%</span>
                                                    <span class="px-2 py-1 badge"
                                                        style="background:#b71c1c;color:#fff;font-size:11px"><i
                                                            class="me-1 mdi mdi-circle"></i>&lt; 75%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="grid-margin col-lg-12 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div id="loadingSpinner" class="py-5 text-center">
                                                <div class="spinner-border text-primary" role="status"
                                                    style="width:3rem;height:3rem;">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-3 text-muted">Cargando datos...</p>
                                                <small class="text-muted">Un momento por favor...</small>
                                            </div>
                                            <div id="errorMessage" class="alert alert-danger" style="display:none">
                                                <i class="me-2 mdi mdi-alert-circle"></i><span id="errorText"></span>
                                            </div>
                                            <div id="sinDatos" class="py-5 text-center" style="display:none">
                                                <i class="text-muted mdi mdi-database-search mdi-48px"></i>
                                                <p class="mt-3 text-muted">No hay órdenes para el período seleccionado.</p>
                                            </div>
                                            <div id="infoCarga" class="mb-2" style="display:none">
                                                <small class="text-muted" id="textoInfoCarga"></small>
                                            </div>
                                            <div id="tablaContainer" class="table-responsive p-1"
                                                style="display:none;max-height:95vh;overflow:auto;">
                                                <table id="tablaOrdenes" class="table table-bordered table-sm mb-0">
                                                    <thead id="tablaThead" style="position:sticky;top:0;z-index:10;">
                                                    </thead>
                                                    <tbody id="tablaTbody"></tbody>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        $(document).ready(function() {
            cargarDatos();
            $('#btnBuscar').on('click', cargarDatos);
            $('#filtroMes, #filtroAnio').on('change', cargarDatos);
            $('#btnLimpiarCache').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="mdi mdi-spin mdi-refresh"></i>');
                $.post('<?php echo route('comercial.ordenes.cache'); ?>', {
                        _token: '<?php echo csrf_token(); ?>'
                    })
                    .always(function() {
                        btn.prop('disabled', false).html('<i class="mdi mdi-refresh"></i>');
                        cargarDatos(true);
                    });
            });

            function cargarDatos(nocache) {
                $('#loadingSpinner').show();
                $('#tablaContainer,#errorMessage,#sinDatos,#infoCarga').hide();
                const params = {
                    mes: $('#filtroMes').val(),
                    anio: $('#filtroAnio').val()
                };
                if (nocache === true) params.nocache = 1;
                const t0 = Date.now();
                $.getJSON('<?php echo route('comercial.ordenesPorSede.data'); ?>', params)
                    .done(function(res) {
                        const seg = ((Date.now() - t0) / 1000).toFixed(1);
                        $('#loadingSpinner').hide();
                        if (!res.success && res.sync_pending) {
                            $('#errorText').html(
                                '<i class="me-1 mdi mdi-sync"></i> Los datos se están sincronizando. Espera unos minutos.'
                                );
                            $('#errorMessage').show();
                            return;
                        }
                        if (!res.success) {
                            mostrarError(res.message || 'Error desconocido');
                            return;
                        }
                        const d = res.data;
                        if (!d.fechas || d.fechas.length === 0) {
                            $('#sinDatos').show();
                            return;
                        }
                        renderTabla(d);
                        $('#textoInfoCarga').html(
                            '<i class="me-1 text-success mdi mdi-check-circle"></i><strong>' + d.tabla
                            .length + '</strong> sedes &nbsp;·&nbsp;<strong>' + d.fechas.length +
                            '</strong> días &nbsp;·&nbsp; Cargado en <strong>' + seg +
                            's</strong>');
                        $('#infoCarga,#tablaContainer').show();
                    })
                    .fail(function(xhr) {
                        $('#loadingSpinner').hide();
                        mostrarError('Error de conexión (' + xhr.status + ')');
                    });
            }

            function renderTabla(data) {
                const fechas = data.fechas,
                    tabla = data.tabla,
                    totales = data.totales;
                let th = '<tr><th rowspan="2" class="th-sede">Sede</th>';
                fechas.forEach(f => {
                    const p = f.split('/');
                    th += `<th colspan="4" class="th-fecha">${p[0]}/${p[1]}</th>`;
                });
                th += '<th colspan="4" class="th-total">Total</th></tr><tr>';
                for (let i = 0; i < fechas.length + 1; i++)
                    th += '<th class="th-sub">Cant</th><th class="th-sub">%</th><th class="th-sub th-prod">Prod</th><th class="th-sub th-prod">% Prod</th>';
                th += '</tr>';
                $('#tablaThead').html(th);

                let rows = '';
                tabla.forEach(function(fila) {
                    rows += `<tr><td class="td-sede">${fila.sede}</td>`;
                    fechas.forEach(f => {
                        const dia = fila.dias[f],
                            cant = dia ? dia.cant : 0,
                            pct = (dia && cant > 0) ? dia.pct : null,
                            prod = dia ? dia.prod : 0,
                            pctProd = (dia && cant > 0) ? dia.pct_prod : null;
                        if (cant === 0) {
                            rows += '<td class="td-zero">-</td><td class="td-zero">-</td><td class="td-zero">-</td><td class="td-zero">-</td>';
                        } else {
                            rows += `<td class="td-cant">${cant}</td>${semaforo(pct)}<td class="td-prod">${prod}</td>${semaforoProd(pctProd)}`;
                        }
                    });
                    const tprod = fila.total_prod || 0;
                    const tpctprod = fila.total_pct_prod != null ? fila.total_pct_prod : null;
                    rows += `<td class="td-total-cant">${fila.total_cant||'-'}</td>${semaforoTotal(fila.total_pct)}<td class="td-prod td-total-cant">${tprod||'-'}</td>${semaforoTotalProd(tpctprod)}</tr>`;
                });

                // Fila total
                rows += '<tr class="tr-total"><td class="td-sede-total">TOTAL</td>';
                fechas.forEach(f => {
                    const t = totales[f],
                        c = t ? t.cant : 0,
                        p = t ? t.pct : null,
                        prod = t ? t.prod : 0,
                        pctProd = t ? t.pct_prod : null;
                    if (c === 0) {
                        rows += '<td class="td-zero-dark">-</td><td class="td-zero-dark">-</td><td class="td-zero-dark">-</td><td class="td-zero-dark">-</td>';
                    } else {
                        rows += `<td class="td-total-cant">${c}</td><td class="${pctClass(p)}">${p!==null?p+'%':'-'}</td><td class="td-total-cant">${prod}</td><td class="td-pct td-prod-pct">${pctProd!==null?pctProd+'%':'-'}</td>`;
                    }
                });
                rows += `<td class="td-gran-total">${data.total_cant||'-'}</td><td class="td-gran-pct">${data.total_pct!==null?data.total_pct+'%':'-'}</td><td class="td-gran-total">${data.total_prod||'-'}</td><td class="td-gran-pct-prod">${data.total_pct_prod!==null?data.total_pct_prod+'%':'-'}</td></tr>`;
                $('#tablaTbody').html(rows);
            }

            function pctClass(pct) {
                if (pct === null || pct === undefined) return 'td-zero-dark';
                if (pct >= 90) return 'td-pct td-verde';
                if (pct >= 75) return 'td-pct td-naranja';
                return 'td-pct td-rojo';
            }

            function semaforo(pct) {
                if (pct === null || pct === undefined) return '<td class="td-zero">-</td>';
                return `<td class="td-pct ${pct>=90?'td-verde':pct>=75?'td-naranja':'td-rojo'}">${pct}%</td>`;
            }

            function semaforoTotal(pct) {
                if (pct === null || pct === undefined) return '<td class="td-total-pct">-</td>';
                return `<td class="td-total-pct ${pct>=90?'td-verde':pct>=75?'td-naranja':'td-rojo'}">${pct}%</td>`;
            }

            function semaforoProd(pct) {
                if (pct === null || pct === undefined) return '<td class="td-zero">-</td>';
                return `<td class="td-pct td-prod-pct">${pct}%</td>`;
            }

            function semaforoTotalProd(pct) {
                if (pct === null || pct === undefined) return '<td class="td-total-pct">-</td>';
                return `<td class="td-total-pct td-prod-pct">${pct}%</td>`;
            }

            function mostrarError(msg) {
                $('#errorText').text(msg);
                $('#errorMessage').show();
            }
        });
    </script>

    <style>
        #tablaContainer::-webkit-scrollbar {
            height: 6px;
            width: 6px
        }

        #tablaContainer::-webkit-scrollbar-thumb {
            background: #3949ab;
            border-radius: 3px
        }

        /* THEAD */
        #tablaOrdenes thead tr:first-child th {
            background: #1a237e;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
            border: 1px solid #283593 !important;
            padding: 5px 8px
        }

        #tablaOrdenes thead tr:last-child th {
            background: #283593;
            color: #c5cae9;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
            border: 1px solid #1a237e !important;
            padding: 3px 5px
        }

        .th-sede {
            min-width: 155px !important;
            text-align: left !important
        }

        .th-fecha {
            min-width: 180px
        }

        .th-prod {
            background: #1b3a2d !important;
            color: #a5d6a7 !important
        }

        .th-total {
            background: #0d47a1 !important;
            min-width: 90px
        }

        /* BODY base */
        #tablaOrdenes tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 4px 7px;
            white-space: nowrap;
            font-size: 12px;
            border: 1px solid #dee2e6 !important
        }

        /* Sede */
        .td-sede {
            text-align: left !important;
            font-weight: 600;
            font-size: 11px;
            background: #1a237e !important;
            color:#fff !important;
            min-width: 155px;
            white-space: normal
        }

        /* Cant */
        .td-cant {
            background: #e8eaf6;
            color: #1a237e !important;
            font-weight: 700
        }

        /* Ceros / vacíos */
        .td-zero {
            background: #fafafa;
            color: #bdbdbd !important
        }

        .td-zero-dark {
            background: #e8eaf6;
            color: #9fa8da !important
        }

        /* Semáforos — colores sobre fondo claro, texto oscuro */
        .td-pct {
            font-weight: 700
        }

        .td-verde {
            background: #e8f5e9;
            color: #1b5e20 !important
        }

        .td-naranja {
            background: #fff3e0;
            color: #bf360c !important
        }

        .td-rojo {
            background: #ffebee;
            color: #b71c1c !important
        }

        /* Totales por fecha */
        .td-total-cant {
            background: #e3f2fd;
            color: #0d47a1 !important;
            font-weight: 700
        }

        .td-total-pct {
            background: #f5f5f5;
            color: #424242 !important;
            font-weight: 700
        }

        /* Fila TOTAL */
        .tr-total .td-sede-total {
            background: #0d1257;
            color: #fff !important;
            font-weight: 700;
            font-size: 11px;
            text-align: left !important;
            border: 1px solid #283593 !important
        }

        .tr-total .td-total-cant {
            background: #1565c0;
            color: #fff !important;
            font-weight: 700;
            border: 1px solid #1a237e !important
        }

        .tr-total .td-pct {
            border: 1px solid #1a237e !important
        }

        .tr-total .td-verde {
            background: #2e7d32;
            color: #fff !important
        }

        .tr-total .td-naranja {
            background: #e64a19;
            color: #fff !important
        }

        .tr-total .td-rojo {
            background: #c62828;
            color: #fff !important
        }

        .tr-total .td-zero-dark {
            background: #1e2a5e;
            color: #5c6bc0 !important;
            border: 1px solid #1a237e !important
        }

        .td-gran-total {
            background: #0d47a1;
            color: #fff !important;
            font-weight: 800;
            font-size: 13px;
            border: 1px solid #1a237e !important
        }

        .td-gran-pct {
            background: #1b5e20;
            color: #fff !important;
            font-weight: 800;
            font-size: 13px;
            border: 1px solid #1a237e !important
        }

        /* Prod */
        .td-prod {
            background: #e8f5e9;
            color: #1b5e20 !important;
            font-weight: 700
        }

        .td-prod-pct {
            background: #f1f8e9;
            color: #33691e !important;
            font-weight: 700
        }

        .tr-total .td-prod-pct {
            background: #2e7d32;
            color: #fff !important;
            border: 1px solid #1a237e !important
        }

        .td-gran-pct-prod {
            background: #2e7d32;
            color: #fff !important;
            font-weight: 800;
            font-size: 13px;
            border: 1px solid #1a237e !important
        }

        /* Hover */
        #tablaOrdenes tbody tr:not(.tr-total):hover td {
            filter: brightness(0.93)
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/ordenes-por-sede.blade.php ENDPATH**/ ?>