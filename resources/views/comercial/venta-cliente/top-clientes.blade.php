{{-- resources/views/comercial/venta-cliente/top-clientes.blade.php --}}
@extends('layouts.app')

@section('title', 'Venta Clientes - Top Clientes')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            {{-- ══ HEADER ══ --}}
                            <div class="mb-4 row">
                                <div class="col-lg-7">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="mdi-trophy-outline me-2 text-primary mdi"></i>
                                        Top Clientes por Sede
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        Top 25 por promedio de los 3 meses anteriores, vs. proyección del mes actual
                                        &nbsp;|&nbsp;
                                        <span id="subtituloPeriodo">Cargando...</span>
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-3 mt-lg-0 col-lg-5">
                                    <div style="width:200px;">
                                        <label class="mb-1 text-muted form-label small">Sede</label>
                                        <select id="selectSede" class="form-select-sm form-select">
                                            <option>Cargando...</option>
                                        </select>
                                    </div>
                                    <div style="width:220px;">
                                        <label class="mb-1 text-muted form-label small">Buscar cliente / RUC</label>
                                        <input type="text" id="buscador" class="form-control form-control-sm"
                                            placeholder="Buscar...">
                                    </div>
                                    <div style="margin-top:22px;">
                                        <button id="btnRefresh" class="btn-outline-secondary btn btn-sm"
                                            title="Sincronizar ahora">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- ══ INFO DE CORTE ══ --}}
                            <div class="mb-4 info-corte-bar d-flex align-items-center flex-wrap gap-3" id="infoCorte">
                                <span><i class="me-1 mdi mdi-calendar-clock"></i>Cargando información de corte...</span>
                            </div>

                            {{-- ══ CUADROS POR SEDE ══ --}}
                            <div id="contenedorSedes">
                                <div class="py-5 text-muted text-center">
                                    <div class="mb-2 spinner-border spinner-border-sm"></div>
                                    <div>Cargando datos...</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .top-clientes-card .table td,
        .top-clientes-card .table th {
            font-size: 0.80rem;
            vertical-align: middle;
        }

        .badge-semaforo {
            display: inline-block;
            min-width: 64px;
            padding: .35em .5em;
            font-weight: 600;
        }

        /* Estilos propios (no usar .alert-light: el CSS del template lo pisa
           con color casi blanco más abajo en la hoja, dejando el texto invisible). */
        .info-corte-bar {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: .375rem;
            padding: .75rem 1rem;
            color: #1e3a5f;
            font-size: .85rem;
        }

        .info-corte-bar .text-muted {
            color: #5b6b82 !important;
        }
    </style>

    <script>
        // ── Formato ───────────────────────────────────────────────────────────
        function fmt(n) {
            if (!n) return '<span class="text-muted">—</span>';
            return 'S/ ' + parseFloat(n).toLocaleString('es-PE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function badgeSemaforo(pct, semaforo) {
            const clases = {
                rojo: 'bg-danger text-white',
                amarillo: 'bg-warning text-dark',
                verde: 'bg-success text-white',
            };
            const clase = clases[semaforo] || 'bg-secondary text-white';
            const signo = pct > 0 ? '+' : '';
            return `<span class="badge badge-semaforo ${clase}">${signo}${pct.toFixed(1)}%</span>`;
        }

        let datosOriginales = [];
        let periodosActuales = null;

        // ── Carga principal ───────────────────────────────────────────────────
        async function cargarDatos() {
            document.getElementById('contenedorSedes').innerHTML = `
                <div class="py-5 text-muted text-center">
                    <div class="mb-2 spinner-border spinner-border-sm"></div>
                    <div>Cargando datos...</div>
                </div>`;

            try {
                const res = await fetch('{{ route('comercial.venta-cliente.top.data') }}');
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error desconocido');

                datosOriginales = data.sedes || [];
                periodosActuales = data.periodos;

                document.getElementById('subtituloPeriodo').textContent =
                    `${data.periodos.m3} · ${data.periodos.m2} · ${data.periodos.m1} → proyección ${data.periodos.actual}`;

                document.getElementById('infoCorte').innerHTML = `
                    <span><i class="me-1 mdi mdi-calendar-clock"></i>
                    Fecha de corte: <strong>${data.fecha_corte}</strong></span>
                    <span><i class="me-1 mdi mdi-calendar-check"></i>
                    Días hábiles transcurridos: <strong>${data.dias_habiles_transcurridos}</strong> de
                    <strong>${data.dias_habiles_totales}</strong> del mes</span>
                    <span class="text-muted small">(la proyección del mes actual se calcula por días hábiles: no cuenta domingos ni feriados)</span>
                `;

                poblarSelectSede(datosOriginales);
                aplicarFiltros();
            } catch (e) {
                document.getElementById('contenedorSedes').innerHTML = `
                    <div class="py-3 text-danger text-center">
                        <i class="me-1 mdi mdi-alert"></i>${e.message}
                    </div>`;
            }
        }

        // ── Selector de sede: evita tener que scrollear todas las tarjetas ───
        function poblarSelectSede(sedes) {
            const sel = document.getElementById('selectSede');
            const valorPrevio = sel.value;
            sel.innerHTML = '';

            if (sedes.length > 1) {
                const oTodas = document.createElement('option');
                oTodas.value = '__todas__';
                oTodas.text = `— Todas (${sedes.length}) —`;
                sel.appendChild(oTodas);
            }

            sedes.forEach(s => {
                const o = document.createElement('option');
                o.value = s.sede;
                o.text = s.sede;
                sel.appendChild(o);
            });

            // Por defecto se muestra solo la primera sede (evita una página con
            // scroll interminable cuando hay 25+ sedes); "Todas" queda disponible
            // para quien de verdad quiera el reporte completo (ej. imprimir).
            if (valorPrevio && [...sel.options].some(o => o.value === valorPrevio)) {
                sel.value = valorPrevio;
            } else if (sedes.length) {
                sel.value = sedes[0].sede;
            }
        }

        // ── Render de cuadros por sede ───────────────────────────────────────
        function renderSedes(sedes) {
            const cont = document.getElementById('contenedorSedes');

            if (!sedes.length) {
                cont.innerHTML = `<div class="py-4 text-muted text-center">Sin resultados.</div>`;
                return;
            }

            const p = periodosActuales || {};

            cont.innerHTML = sedes.map(s => `
                <div class="mb-4 card top-clientes-card">
                    <div class="card-body">
                        <h4 class="mb-3 card-title">
                            <i class="me-2 text-primary mdi mdi-map-marker"></i>${s.sede}
                            <span class="text-muted fw-normal small">(${s.clientes.length} clientes)</span>
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:260px;">Cliente</th>
                                        <th class="text-end">${p.m3 || 'Mes -3'}</th>
                                        <th class="text-end">${p.m2 || 'Mes -2'}</th>
                                        <th class="text-end">${p.m1 || 'Mes -1'}</th>
                                        <th class="text-end">PROM</th>
                                        <th class="text-end">${p.actual || 'Mes actual'} (proy.)</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${s.clientes.map(c => `
                                        <tr>
                                            <td>
                                                <div class="fw-medium">${c.razon || '—'}</div>
                                                <div class="text-muted small">${c.ruc}</div>
                                            </td>
                                            <td class="text-end">${fmt(c.venta_m3)}</td>
                                            <td class="text-end">${fmt(c.venta_m2)}</td>
                                            <td class="text-end">${fmt(c.venta_m1)}</td>
                                            <td class="text-end fw-semibold">${fmt(c.prom)}</td>
                                            <td class="text-end fw-semibold">${fmt(c.venta_actual)}</td>
                                            <td class="text-center">${badgeSemaforo(c.variacion_pct, c.semaforo)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // ── Combina selector de sede + buscador de cliente/RUC ───────────────
        function aplicarFiltros() {
            const sedeSel = document.getElementById('selectSede').value;
            const q = document.getElementById('buscador').value.toLowerCase().trim();

            let filtrado = sedeSel && sedeSel !== '__todas__'
                ? datosOriginales.filter(s => s.sede === sedeSel)
                : datosOriginales;

            if (q) {
                filtrado = filtrado
                    .map(s => {
                        const clientes = s.clientes.filter(c =>
                            (c.razon || '').toLowerCase().includes(q) ||
                            (c.ruc || '').toLowerCase().includes(q)
                        );
                        return clientes.length ? { ...s, clientes } : null;
                    })
                    .filter(Boolean);
            }

            renderSedes(filtrado);
        }

        document.getElementById('selectSede').addEventListener('change', aplicarFiltros);
        document.getElementById('buscador').addEventListener('input', aplicarFiltros);

        // ── Refresh: fuerza resincronización desde el Sheet y recarga ───────
        document.getElementById('btnRefresh').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            await fetch('{{ route('comercial.venta-cliente.cache.clear') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            await cargarDatos();
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-refresh"></i>';
        });

        // ── Init ──────────────────────────────────────────────────────────────
        cargarDatos();
    </script>
@endsection
