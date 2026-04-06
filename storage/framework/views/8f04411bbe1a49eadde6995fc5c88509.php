<?php $__env->startSection('title', 'Órdenes x Usuario'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between px-4 py-3">
                    <div>
                        <h4 class="mb-0 fw-bold">
                            <i class="mdi mdi-account-multiple-outline me-2 text-primary"></i>Órdenes x Usuario
                        </h4>
                        <p class="mb-0 text-muted small">Análisis diario de órdenes registradas por usuario y hora</p>
                    </div>
                    <span class="badge bg-primary fs-6" id="badge-fecha-actual"><?php echo now()->format('d/m/Y'); ?></span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">

                        
                        <div class="col-md-4 col-sm-12">
                            <label class="form-label small fw-bold mb-1">Fecha</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" id="btn-dia-anterior" title="Día anterior">
                                    <i class="mdi mdi-chevron-left"></i>
                                </button>
                                <input type="date" id="filtro-fecha" class="form-control text-center"
                                    value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                                <button class="btn btn-outline-secondary" id="btn-dia-siguiente" title="Día siguiente">
                                    <i class="mdi mdi-chevron-right"></i>
                                </button>
                                <button class="btn btn-outline-primary" id="btn-hoy" title="Ir a hoy">
                                    Hoy
                                </button>
                            </div>
                        </div>

                        
                        <?php if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()): ?>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Sede</label>
                            <select id="filtro-sede" class="form-select form-select-sm">
                                <option value="">Todas las sedes</option>
                                <?php $__currentLoopData = $sedesDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" id="filtro-sede" value="<?php echo auth()->user()->sede; ?>">
                        <?php endif; ?>

                        
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Usuario</label>
                            <select id="filtro-usuario" class="form-select form-select-sm">
                                <option value="">Todos los usuarios</option>
                            </select>
                        </div>

                        
                        <div class="col-md-2 col-sm-12">
                            <button id="btn-filtrar" class="btn btn-primary btn-sm w-100">
                                <i class="mdi mdi-magnify me-1"></i>Filtrar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div id="section-loading" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted small">Cargando datos...</p>
    </div>

    
    <div id="section-content" class="d-none">

        
        <div class="row mb-3 g-3">
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Total Órdenes</div>
                        <div id="kpi-total" class="display-6 fw-bold text-primary">—</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Usuarios con órdenes</div>
                        <div id="kpi-usuarios" class="display-6 fw-bold text-success">—</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Promedio x Usuario</div>
                        <div id="kpi-promedio" class="display-6 fw-bold text-warning">—</div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-chart-bar me-2 text-primary"></i>Órdenes por Hora y Usuario
                        </h6>
                        <span class="text-muted small" id="lbl-fecha-grafico">—</span>
                    </div>
                    <div class="card-body p-2">
                        
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div id="chart-hora-wrap" style="position:relative; height:320px; min-width:600px;">
                                <canvas id="chart-por-hora"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mb-3 g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-chart-bar-stacked me-2 text-success"></i>Total por Usuario
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-por-usuario-wrap" style="position:relative; min-height:200px;">
                            <canvas id="chart-por-usuario"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-table me-2 text-info"></i>Ranking de Usuarios
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:340px; overflow-y:auto;">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Usuario</th>
                                        <th class="text-center">Órdenes</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-usuarios">
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin datos</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-format-list-bulleted me-2 text-secondary"></i>Detalle de Órdenes
                        </h6>
                        <span class="text-muted small" id="lbl-paginacion-info">—</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Orden</th>
                                        <th>Usuario</th>
                                        <th>Sede</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-detalle">
                                    <tr><td colspan="7" class="text-center text-muted py-3">—</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-2 px-3" id="footer-paginacion">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" id="btn-pag-primera" title="Primera página">
                                <i class="mdi mdi-page-first"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-pag-anterior" title="Anterior">
                                <i class="mdi mdi-chevron-left"></i>
                            </button>
                            <span class="text-muted small" id="lbl-pag-actual">Pág. 1 de 1</span>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-pag-siguiente" title="Siguiente">
                                <i class="mdi mdi-chevron-right"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-pag-ultima" title="Última página">
                                <i class="mdi mdi-page-last"></i>
                            </button>
                        </div>
                        <span class="text-muted small" id="lbl-filas-rango">—</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <div id="section-empty" class="d-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-inbox-outline text-muted" style="font-size:3rem;"></i>
                <p class="mt-2 mb-0 text-muted">No se encontraron órdenes para la fecha y filtros seleccionados.</p>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const API_DATA     = <?php echo json_encode(route('productividad.ordenes-x-usuario.data'), 15, 512) ?>;
    const API_USUARIOS = <?php echo json_encode(route('productividad.ordenes-x-usuario.usuarios'), 15, 512) ?>;

    let chartHora    = null;
    let chartUsuario = null;

    // Estado de paginación
    let pagActual   = 1;
    let pagTotal    = 1;
    let totalFilas  = 0;

    const COLORES = [
        '#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6',
        '#06b6d4','#ec4899','#84cc16','#f97316','#6366f1',
        '#14b8a6','#f43f5e','#a855f7','#0ea5e9','#22c55e',
    ];

    // ── Init ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        cargarUsuarios();
        cargarDatos(1);

        // Botones fecha
        document.getElementById('btn-dia-anterior').addEventListener('click', () => moverFecha(-1));
        document.getElementById('btn-dia-siguiente').addEventListener('click', () => moverFecha(1));
        document.getElementById('btn-hoy').addEventListener('click', () => {
            document.getElementById('filtro-fecha').value = hoy();
            recargarTodo();
        });

        // Filtros
        document.getElementById('btn-filtrar').addEventListener('click', () => cargarDatos(1));
        document.getElementById('filtro-fecha').addEventListener('change', recargarTodo);

        const filtroSede = document.getElementById('filtro-sede');
        if (filtroSede && filtroSede.tagName === 'SELECT') {
            filtroSede.addEventListener('change', recargarTodo);
        }

        // Paginación
        document.getElementById('btn-pag-primera').addEventListener('click',   () => cargarPagina(1));
        document.getElementById('btn-pag-anterior').addEventListener('click',  () => cargarPagina(pagActual - 1));
        document.getElementById('btn-pag-siguiente').addEventListener('click', () => cargarPagina(pagActual + 1));
        document.getElementById('btn-pag-ultima').addEventListener('click',    () => cargarPagina(pagTotal));
    });

    function recargarTodo() {
        cargarUsuarios();
        cargarDatos(1);
    }

    // ── Fecha ─────────────────────────────────────────────────────
    function hoy() {
        return new Date().toISOString().slice(0, 10);
    }

    function moverFecha(dias) {
        const input = document.getElementById('filtro-fecha');
        const d = new Date(input.value + 'T12:00:00');
        d.setDate(d.getDate() + dias);
        const nueva = d.toISOString().slice(0, 10);
        const hoyStr = hoy();
        if (nueva > hoyStr) return; // no avanzar más allá de hoy
        input.value = nueva;
        recargarTodo();
    }

    // ── Usuarios filtro ───────────────────────────────────────────
    function cargarUsuarios() {
        const params = new URLSearchParams();
        const sede   = getVal('filtro-sede');
        const fecha  = getVal('filtro-fecha');
        if (sede)  params.set('sede', sede);
        if (fecha) params.set('fecha', fecha);

        fetch(API_USUARIOS + '?' + params.toString())
            .then(r => r.json())
            .then(lista => {
                const sel = document.getElementById('filtro-usuario');
                const cur = sel.value;
                sel.innerHTML = '<option value="">Todos los usuarios</option>';
                lista.forEach(u => {
                    const o = document.createElement('option');
                    o.value = u; o.textContent = u;
                    if (u === cur) o.selected = true;
                    sel.appendChild(o);
                });
            })
            .catch(() => {});
    }

    // ── Carga de datos (charts + tabla pág 1) ─────────────────────
    function cargarDatos(page) {
        mostrarEstado('loading');
        pagActual = page;

        const params = buildParams(page);

        fetch(API_DATA + '?' + params.toString())
            .then(r => r.json())
            .then(resp => {
                if (resp.error || !resp.totales || resp.totales.ordenes === 0) {
                    mostrarEstado('empty');
                    return;
                }

                // Actualizar estado paginación
                const pag = resp.paginacion;
                pagActual = pag.page;
                pagTotal  = pag.total_pages;
                totalFilas = pag.total;

                renderKpis(resp.totales);
                renderChartHora(resp.porHora);
                renderChartUsuario(resp.porUsuario);
                renderTablaUsuarios(resp.porUsuario);
                renderTablaDetalle(resp.tabla, pag);
                actualizarBotonesPaginacion();

                // Actualizar header
                const f = resp.totales.fecha;
                const parts = f.split('-');
                const fFmt = parts[2] + '/' + parts[1] + '/' + parts[0];
                document.getElementById('badge-fecha-actual').textContent = fFmt;
                document.getElementById('lbl-fecha-grafico').textContent  = fFmt;

                mostrarEstado('content');
            })
            .catch(() => mostrarEstado('empty'));
    }

    // Solo recarga la tabla (para paginación sin re-renderizar charts)
    function cargarPagina(page) {
        if (page < 1 || page > pagTotal) return;
        pagActual = page;

        const params = buildParams(page);

        // Deshabilitar botones momentáneamente
        ['btn-pag-primera','btn-pag-anterior','btn-pag-siguiente','btn-pag-ultima']
            .forEach(id => document.getElementById(id).disabled = true);

        fetch(API_DATA + '?' + params.toString())
            .then(r => r.json())
            .then(resp => {
                if (!resp.tabla) return;
                const pag = resp.paginacion;
                pagActual  = pag.page;
                pagTotal   = pag.total_pages;
                totalFilas = pag.total;
                renderTablaDetalle(resp.tabla, pag);
                actualizarBotonesPaginacion();
            })
            .catch(() => {})
            .finally(() => actualizarBotonesPaginacion());
    }

    function buildParams(page) {
        return new URLSearchParams({
            fecha:   getVal('filtro-fecha') || hoy(),
            sede:    getVal('filtro-sede'),
            usuario: getVal('filtro-usuario'),
            page:    page,
        });
    }

    // ── KPIs ──────────────────────────────────────────────────────
    function renderKpis(t) {
        const prom = t.usuarios > 0 ? Math.round(t.ordenes / t.usuarios) : 0;
        document.getElementById('kpi-total').textContent    = t.ordenes.toLocaleString('es-PE');
        document.getElementById('kpi-usuarios').textContent = t.usuarios;
        document.getElementById('kpi-promedio').textContent = prom.toLocaleString('es-PE');
    }

    // ── Chart: por hora ────────────────────────────────────────────
    function renderChartHora(data) {
        const canvas = document.getElementById('chart-por-hora');
        const wrap   = document.getElementById('chart-hora-wrap');
        if (chartHora) chartHora.destroy();

        // Ancho dinámico: cada hora necesita espacio según número de datasets
        const numHoras    = data.labels.length;
        const numUsuarios = data.datasets.length;
        const barWidth    = Math.max(28, 18 * numUsuarios); // px por hora
        const minWidth    = Math.max(600, numHoras * barWidth + 80);
        wrap.style.minWidth = minWidth + 'px';

        chartHora = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: { labels: data.labels, datasets: data.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 14 } },
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Hora del día' },
                        ticks: { font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'N° de Órdenes' },
                        ticks: { precision: 0 },
                    }
                }
            }
        });
    }

    // ── Chart: por usuario (horizontal) ────────────────────────────
    function renderChartUsuario(data) {
        const wrap  = document.getElementById('chart-por-usuario-wrap');
        const ctx   = document.getElementById('chart-por-usuario').getContext('2d');
        const count = data.labels.length;
        wrap.style.minHeight = Math.max(200, count * 32 + 60) + 'px';

        if (chartUsuario) chartUsuario.destroy();

        chartUsuario = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Órdenes',
                    data: data.data,
                    backgroundColor: data.labels.map((_, i) => COLORES[i % COLORES.length] + 'cc'),
                    borderColor:     data.labels.map((_, i) => COLORES[i % COLORES.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // ── Tabla ranking usuarios ─────────────────────────────────────
    function renderTablaUsuarios(data) {
        const tbody = document.getElementById('tbody-usuarios');
        const total = data.data.reduce((a, b) => a + b, 0);

        if (!data.labels.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin datos</td></tr>';
            return;
        }

        tbody.innerHTML = data.labels.map((u, i) => {
            const cant = data.data[i];
            const pct  = total > 0 ? ((cant / total) * 100).toFixed(1) : 0;
            const color = COLORES[i % COLORES.length];
            return `<tr>
                <td class="text-muted">${i + 1}</td>
                <td>
                    <span class="d-inline-block me-1" style="width:10px;height:10px;border-radius:2px;background:${color};"></span>
                    ${esc(u)}
                </td>
                <td class="text-center fw-bold">${cant.toLocaleString('es-PE')}</td>
                <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">${pct}%</span></td>
            </tr>`;
        }).join('');
    }

    // ── Tabla detalle ──────────────────────────────────────────────
    function renderTablaDetalle(filas, pag) {
        const tbody = document.getElementById('tbody-detalle');
        const desde = (pag.page - 1) * pag.per_page + 1;
        const hasta = Math.min(pag.page * pag.per_page, pag.total);

        document.getElementById('lbl-paginacion-info').textContent =
            pag.total > 0 ? `${pag.total.toLocaleString('es-PE')} registros` : '—';
        document.getElementById('lbl-filas-rango').textContent =
            pag.total > 0 ? `Mostrando ${desde}–${hasta} de ${pag.total.toLocaleString('es-PE')}` : '';
        document.getElementById('lbl-pag-actual').textContent =
            `Pág. ${pag.page} de ${pag.total_pages}`;

        if (!filas.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Sin datos</td></tr>';
            return;
        }

        tbody.innerHTML = filas.map(r => `<tr>
            <td class="font-monospace small">${esc(r.numero_orden ?? '—')}</td>
            <td class="small">${esc(r.nombre_usuario ?? '—')}</td>
            <td><span class="badge bg-success-subtle text-success small">${esc(r.descripcion_sede ?? '—')}</span></td>
            <td class="small fw-semibold">${esc(r.hora_orden ?? '—')}</td>
            <td class="small">${esc(r.cliente ?? '—')}</td>
            <td class="small">${esc(r.tipo_orden ?? '—')}</td>
            <td>${estadoBadge(r.estado_orden)}</td>
        </tr>`).join('');
    }

    // ── Paginación ─────────────────────────────────────────────────
    function actualizarBotonesPaginacion() {
        document.getElementById('btn-pag-primera').disabled  = pagActual <= 1;
        document.getElementById('btn-pag-anterior').disabled = pagActual <= 1;
        document.getElementById('btn-pag-siguiente').disabled = pagActual >= pagTotal;
        document.getElementById('btn-pag-ultima').disabled    = pagActual >= pagTotal;
        document.getElementById('lbl-pag-actual').textContent = `Pág. ${pagActual} de ${pagTotal}`;
    }

    // ── Helpers ───────────────────────────────────────────────────
    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    function mostrarEstado(estado) {
        ['section-loading','section-content','section-empty'].forEach(id =>
            document.getElementById(id).classList.add('d-none')
        );
        const map = { loading:'section-loading', content:'section-content', empty:'section-empty' };
        document.getElementById(map[estado]).classList.remove('d-none');
    }

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function estadoBadge(e) {
        if (!e) return '<span class="badge bg-secondary-subtle text-secondary">—</span>';
        const u = e.toUpperCase();
        let cls = 'bg-secondary-subtle text-secondary';
        if      (u === 'FACTURADO')                              cls = 'bg-success-subtle text-success';
        else if (u === 'ANULADO' || u === 'MERMA')               cls = 'bg-danger-subtle text-danger';
        else if (u === 'SOLICITADO')                             cls = 'bg-warning-subtle text-warning';
        else if (u.includes('PROCESO') || u.includes('PRODUC')) cls = 'bg-info-subtle text-info';
        return `<span class="badge ${cls}">${esc(e)}</span>`;
    }

})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/productividad/ordenes-x-usuario/index.blade.php ENDPATH**/ ?>