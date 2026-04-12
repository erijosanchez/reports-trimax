@extends('layouts.app')

@section('title', 'Órdenes x Usuario')

@section('content')
<div class="content-wrapper">

    {{-- ── Header ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between px-4 py-3">
                    <div>
                        <h4 class="mb-0 fw-bold">
                            <i class="mdi mdi-account-multiple-outline me-2 text-primary"></i>Órdenes x Usuario
                        </h4>
                        <p class="mb-0 text-muted small">Análisis de órdenes registradas por usuario y hora — por día o por mes</p>
                    </div>
                    <span class="badge bg-primary fs-6" id="badge-fecha-actual">{{ now()->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">

                        {{-- Toggle modo día / mes --}}
                        <div class="col-auto">
                            <label class="form-label small fw-bold mb-1">Modo</label>
                            <div class="btn-group btn-group-sm d-flex" role="group">
                                <input type="radio" class="btn-check" name="modo-vista" id="modo-dia" value="dia" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="modo-dia">
                                    <i class="mdi mdi-calendar-today me-1"></i>Día
                                </label>
                                <input type="radio" class="btn-check" name="modo-vista" id="modo-mes" value="mes" autocomplete="off">
                                <label class="btn btn-outline-primary" for="modo-mes">
                                    <i class="mdi mdi-calendar-month me-1"></i>Mes
                                </label>
                            </div>
                        </div>

                        {{-- Navegación de fecha (modo día) --}}
                        <div id="filtro-dia-wrap" class="col-md-4 col-sm-12">
                            <label class="form-label small fw-bold mb-1">Fecha</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" id="btn-dia-anterior" title="Día anterior">
                                    <i class="mdi mdi-chevron-left"></i>
                                </button>
                                <input type="date" id="filtro-fecha" class="form-control text-center"
                                    value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                                <button class="btn btn-outline-secondary" id="btn-dia-siguiente" title="Día siguiente">
                                    <i class="mdi mdi-chevron-right"></i>
                                </button>
                                <button class="btn btn-outline-primary" id="btn-hoy" title="Ir a hoy">Hoy</button>
                            </div>
                        </div>

                        {{-- Navegación de mes (modo mes) --}}
                        <div id="filtro-mes-wrap" class="col-md-4 col-sm-12 d-none">
                            <label class="form-label small fw-bold mb-1">Mes</label>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" id="btn-mes-anterior" title="Mes anterior">
                                    <i class="mdi mdi-chevron-left"></i>
                                </button>
                                <input type="month" id="filtro-mes" class="form-control text-center"
                                    value="{{ date('Y-m') }}" max="{{ date('Y-m') }}">
                                <button class="btn btn-outline-secondary" id="btn-mes-siguiente" title="Mes siguiente">
                                    <i class="mdi mdi-chevron-right"></i>
                                </button>
                                <button class="btn btn-outline-primary" id="btn-mes-actual">Actual</button>
                            </div>
                        </div>

                        {{-- Sede (solo admin/superadmin) --}}
                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Sede</label>
                            <select id="filtro-sede" class="form-select form-select-sm">
                                <option value="">Todas las sedes</option>
                                @foreach($sedesDisponibles as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="filtro-sede" value="{{ auth()->user()->sede }}">
                        @endif

                        {{-- Usuario --}}
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small fw-bold mb-1">Usuario</label>
                            <select id="filtro-usuario" class="form-select form-select-sm">
                                <option value="">Todos</option>
                            </select>
                        </div>

                        {{-- Botón --}}
                        <div class="col-auto">
                            <button id="btn-filtrar" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-magnify me-1"></i>Filtrar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Loader ── --}}
    <div id="section-loading" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted small">Cargando datos...</p>
    </div>

    {{-- ── Contenido ── --}}
    <div id="section-content" class="d-none">

        {{-- KPIs --}}
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

        {{-- Gráfico: órdenes por hora y usuario --}}
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
                        {{-- scroll horizontal cuando hay muchos usuarios/horas --}}
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div id="chart-hora-wrap" style="position:relative; height:320px; min-width:600px;">
                                <canvas id="chart-por-hora"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico: total de órdenes por hora (sin desglose de usuario) --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-chart-timeline-variant me-2 text-warning"></i>Total de Órdenes por Hora
                        </h6>
                        <span class="text-muted small">Todas las órdenes sin desglose por usuario</span>
                    </div>
                    <div class="card-body p-2">
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div id="chart-hora-total-wrap" style="position:relative; height:220px; min-width:500px;">
                                <canvas id="chart-por-hora-total"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico: total por usuario dividido antes/después 5pm --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-chart-bar me-2 text-primary"></i>Total de Órdenes por Usuario — Antes / Después de 5pm
                        </h6>
                        <div class="d-flex gap-3 small">
                            <span><span class="d-inline-block me-1" style="width:12px;height:12px;border-radius:2px;background:#2563eb;"></span>Antes de 5pm</span>
                            <span><span class="d-inline-block me-1" style="width:12px;height:12px;border-radius:2px;background:#ef4444;"></span>Después de 5pm</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div id="chart-usuario-turno-wrap" style="position:relative; height:340px; min-width:600px;">
                                <canvas id="chart-usuario-turno"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico: total por semana dividido antes/después 5pm --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="mdi mdi-chart-bar me-2 text-success"></i>Total de Órdenes por Semana — Antes / Después de 5pm
                        </h6>
                        <div class="d-flex gap-3 small">
                            <span><span class="d-inline-block me-1" style="width:12px;height:12px;border-radius:2px;background:#2563eb;"></span>Antes de 5pm</span>
                            <span><span class="d-inline-block me-1" style="width:12px;height:12px;border-radius:2px;background:#ef4444;"></span>Después de 5pm</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div id="chart-semana-turno-wrap" style="position:relative; height:300px; min-width:400px;">
                                <canvas id="chart-semana-turno"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla resumen --}}
        <div class="row mb-3 g-3">
            <div class="col-lg-12">
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

        {{-- Tabla detallada con paginación --}}
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
                    {{-- Paginación --}}
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

    </div>{{-- /section-content --}}

    {{-- Mensaje vacío --}}
    <div id="section-empty" class="d-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="mdi mdi-inbox-outline text-muted" style="font-size:3rem;"></i>
                <p class="mt-2 mb-0 text-muted">No se encontraron órdenes para la fecha y filtros seleccionados.</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const API_DATA     = @json(route('productividad.ordenes-x-usuario.data'));
    const API_USUARIOS = @json(route('productividad.ordenes-x-usuario.usuarios'));

    let chartHora         = null;
    let chartHoraTotal    = null;
    let chartUsuarioTurno = null;
    let chartSemanaTurno  = null;

    // Estado de paginación
    let pagActual  = 1;
    let pagTotal   = 1;
    let totalFilas = 0;

    const COLORES = [
        '#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6',
        '#06b6d4','#ec4899','#84cc16','#f97316','#6366f1',
        '#14b8a6','#f43f5e','#a855f7','#0ea5e9','#22c55e',
    ];

    const MESES_ES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                      'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    // ── Init ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        cargarUsuarios();
        cargarDatos(1);

        // Toggle modo día/mes
        document.querySelectorAll('input[name="modo-vista"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const esMes = this.value === 'mes';
                document.getElementById('filtro-dia-wrap').classList.toggle('d-none', esMes);
                document.getElementById('filtro-mes-wrap').classList.toggle('d-none', !esMes);
                recargarTodo();
            });
        });

        // Botones fecha (modo día)
        document.getElementById('btn-dia-anterior').addEventListener('click', () => moverFecha(-1));
        document.getElementById('btn-dia-siguiente').addEventListener('click', () => moverFecha(1));
        document.getElementById('btn-hoy').addEventListener('click', () => {
            document.getElementById('filtro-fecha').value = hoy();
            recargarTodo();
        });
        document.getElementById('filtro-fecha').addEventListener('change', recargarTodo);

        // Botones mes (modo mes)
        document.getElementById('btn-mes-anterior').addEventListener('click', () => moverMes(-1));
        document.getElementById('btn-mes-siguiente').addEventListener('click', () => moverMes(1));
        document.getElementById('btn-mes-actual').addEventListener('click', () => {
            document.getElementById('filtro-mes').value = mesActual();
            recargarTodo();
        });
        document.getElementById('filtro-mes').addEventListener('change', recargarTodo);

        // Sede
        const filtroSede = document.getElementById('filtro-sede');
        if (filtroSede && filtroSede.tagName === 'SELECT') {
            filtroSede.addEventListener('change', recargarTodo);
        }

        // Botón filtrar
        document.getElementById('btn-filtrar').addEventListener('click', () => cargarDatos(1));

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

    // ── Modo activo ───────────────────────────────────────────────
    function getModo() {
        const r = document.querySelector('input[name="modo-vista"]:checked');
        return r ? r.value : 'dia';
    }

    // ── Fecha (modo día) ──────────────────────────────────────────
    function hoy() {
        return new Date().toISOString().slice(0, 10);
    }

    function moverFecha(dias) {
        const input = document.getElementById('filtro-fecha');
        const d = new Date(input.value + 'T12:00:00');
        d.setDate(d.getDate() + dias);
        const nueva = d.toISOString().slice(0, 10);
        if (nueva > hoy()) return;
        input.value = nueva;
        recargarTodo();
    }

    // ── Mes (modo mes) ────────────────────────────────────────────
    function mesActual() {
        return new Date().toISOString().slice(0, 7);
    }

    function moverMes(delta) {
        const input = document.getElementById('filtro-mes');
        const [y, m] = input.value.split('-').map(Number);
        const d = new Date(y, m - 1 + delta, 1);
        const nuevo = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        if (nuevo > mesActual()) return;
        input.value = nuevo;
        recargarTodo();
    }

    function formatPeriodo(t) {
        if (t.modo === 'mes') {
            const [y, m] = t.periodo.split('-');
            return MESES_ES[parseInt(m) - 1] + ' ' + y;
        }
        const parts = t.periodo.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    // ── Usuarios filtro ───────────────────────────────────────────
    function cargarUsuarios() {
        const params = new URLSearchParams();
        const sede   = getVal('filtro-sede');
        const modo   = getModo();
        params.set('modo', modo);
        if (sede) params.set('sede', sede);
        if (modo === 'mes') {
            const mes = getVal('filtro-mes') || mesActual();
            params.set('mes', mes);
        } else {
            const fecha = getVal('filtro-fecha') || hoy();
            params.set('fecha', fecha);
        }

        fetch(API_USUARIOS + '?' + params.toString())
            .then(r => r.json())
            .then(lista => {
                const sel = document.getElementById('filtro-usuario');
                const cur = sel.value;
                sel.innerHTML = '<option value="">Todos</option>';
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

        fetch(API_DATA + '?' + buildParams(page).toString())
            .then(r => r.json())
            .then(resp => {
                if (resp.error || !resp.totales || resp.totales.ordenes === 0) {
                    mostrarEstado('empty');
                    return;
                }

                const pag = resp.paginacion;
                pagActual  = pag.page;
                pagTotal   = pag.total_pages;
                totalFilas = pag.total;

                renderKpis(resp.totales);
                renderChartHora(resp.porHora);
                renderChartHoraTotal(resp.porHoraTotal);
                renderChartUsuarioTurno(resp.porUsuarioTurno);
                renderChartSemanaTurno(resp.porSemanaTurno);
                renderTablaUsuarios(resp.porUsuario);
                renderTablaDetalle(resp.tabla, pag);
                actualizarBotonesPaginacion();

                const periodoFmt = formatPeriodo(resp.totales);
                document.getElementById('badge-fecha-actual').textContent = periodoFmt;
                document.getElementById('lbl-fecha-grafico').textContent  = periodoFmt;

                mostrarEstado('content');
            })
            .catch(() => mostrarEstado('empty'));
    }

    // Solo recarga la tabla (paginación sin re-renderizar charts)
    function cargarPagina(page) {
        if (page < 1 || page > pagTotal) return;
        pagActual = page;

        ['btn-pag-primera','btn-pag-anterior','btn-pag-siguiente','btn-pag-ultima']
            .forEach(id => document.getElementById(id).disabled = true);

        fetch(API_DATA + '?' + buildParams(page).toString())
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
        const modo = getModo();
        const p = new URLSearchParams({
            modo:    modo,
            sede:    getVal('filtro-sede'),
            usuario: getVal('filtro-usuario'),
            page:    page,
        });
        if (modo === 'mes') {
            p.set('mes', getVal('filtro-mes') || mesActual());
        } else {
            p.set('fecha', getVal('filtro-fecha') || hoy());
        }
        return p;
    }

    // ── KPIs ──────────────────────────────────────────────────────
    function renderKpis(t) {
        const prom = t.usuarios > 0 ? Math.round(t.ordenes / t.usuarios) : 0;
        document.getElementById('kpi-total').textContent    = t.ordenes.toLocaleString('es-PE');
        document.getElementById('kpi-usuarios').textContent = t.usuarios;
        document.getElementById('kpi-promedio').textContent = prom.toLocaleString('es-PE');
    }

    // ── Chart: por hora por usuario ────────────────────────────────
    function renderChartHora(data) {
        const canvas = document.getElementById('chart-por-hora');
        const wrap   = document.getElementById('chart-hora-wrap');
        if (chartHora) chartHora.destroy();

        const numHoras    = data.labels.length;
        const numUsuarios = data.datasets.length;
        const barWidth    = Math.max(28, 18 * numUsuarios);
        wrap.style.minWidth = Math.max(600, numHoras * barWidth + 80) + 'px';

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
                    x: { title: { display: true, text: 'Hora del día' }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, title: { display: true, text: 'N° de Órdenes' }, ticks: { precision: 0 } }
                }
            }
        });
    }

    // ── Chart: total órdenes por hora (sin usuario) ────────────────
    function renderChartHoraTotal(data) {
        const canvas = document.getElementById('chart-por-hora-total');
        const wrap   = document.getElementById('chart-hora-total-wrap');
        if (chartHoraTotal) chartHoraTotal.destroy();

        wrap.style.minWidth = Math.max(500, data.labels.length * 42 + 60) + 'px';

        chartHoraTotal = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Total órdenes',
                    data: data.data,
                    backgroundColor: '#f59e0b99',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.parsed.y.toLocaleString('es-PE') + ' órdenes'
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'Hora del día' }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, title: { display: true, text: 'N° de Órdenes' }, ticks: { precision: 0 } }
                }
            }
        });
    }

    // ── Chart: total por usuario antes/después 5pm (apilado) ───────
    function renderChartUsuarioTurno(data) {
        const canvas = document.getElementById('chart-usuario-turno');
        const wrap   = document.getElementById('chart-usuario-turno-wrap');
        if (chartUsuarioTurno) chartUsuarioTurno.destroy();

        const numUsuarios = data.labels.length;
        wrap.style.minWidth = Math.max(600, numUsuarios * 48 + 80) + 'px';

        chartUsuarioTurno = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Antes de 5pm',
                        data: data.antes_5pm,
                        backgroundColor: '#2563ebcc',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 0,
                    },
                    {
                        label: 'Después de 5pm',
                        data: data.despues_5pm,
                        backgroundColor: '#ef4444cc',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 0,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterBody: function(items) {
                                const idx   = items[0].dataIndex;
                                const antes  = data.antes_5pm[idx]   || 0;
                                const despues = data.despues_5pm[idx] || 0;
                                const total  = antes + despues;
                                return ['─────────────', 'Total: ' + total.toLocaleString('es-PE')];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: { font: { size: 10 }, maxRotation: 35, minRotation: 25 }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'N° de Órdenes' },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // ── Chart: total por semana antes/después 5pm (apilado) ────────
    function renderChartSemanaTurno(data) {
        const canvas = document.getElementById('chart-semana-turno');
        const wrap   = document.getElementById('chart-semana-turno-wrap');
        if (chartSemanaTurno) chartSemanaTurno.destroy();

        wrap.style.minWidth = Math.max(400, data.labels.length * 72 + 80) + 'px';

        // Plugin para mostrar el total encima de cada barra apilada
        const totalEncima = {
            id: 'totalEncimaSemana',
            afterDatasetsDraw(chart) {
                const { ctx, scales: { x, y } } = chart;
                ctx.save();
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = '#111827';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                data.labels.forEach((_, i) => {
                    const total = data.totales[i] || 0;
                    if (!total) return;
                    const xPos = x.getPixelForValue(i);
                    const yPos = y.getPixelForValue(total);
                    ctx.fillText(total.toLocaleString('es-PE'), xPos, yPos - 4);
                });
                ctx.restore();
            }
        };

        chartSemanaTurno = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Antes de 5pm',
                        data: data.antes_5pm,
                        backgroundColor: '#2563ebcc',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 0,
                    },
                    {
                        label: 'Después de 5pm',
                        data: data.despues_5pm,
                        backgroundColor: '#ef4444cc',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 0,
                    },
                ]
            },
            plugins: [totalEncima],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterBody: function(items) {
                                const idx   = items[0].dataIndex;
                                const total = data.totales[idx] || 0;
                                return ['─────────────', 'Total semana: ' + total.toLocaleString('es-PE')];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: { font: { size: 10 }, maxRotation: 30, minRotation: 20 }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'N° de Órdenes' },
                        ticks: { precision: 0 }
                    }
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
            const cant  = data.data[i];
            const pct   = total > 0 ? ((cant / total) * 100).toFixed(1) : 0;
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
        document.getElementById('btn-pag-primera').disabled   = pagActual <= 1;
        document.getElementById('btn-pag-anterior').disabled  = pagActual <= 1;
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
@endpush
