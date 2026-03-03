@extends('layouts.app')

@section('title', 'Pendiente - Entrega Montura')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            {{-- ══════════════════════════════════════════
                                HEADER
                            ══════════════════════════════════════════ --}}
                            <div class="mb-4 row">
                                <div class="col-lg-8">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="me-2 text-primary mdi mdi-glasses"></i>
                                        Pendiente - Entrega Montura
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <span id="lblUltimaActualizacion" class="text-success small">
                                            <i class="me-1 mdi mdi-refresh"></i> Cargando...
                                        </span>
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 col-lg-4">
                                    <span class="me-2 text-muted small">
                                        Auto-refresh: <strong id="lblCountdown" class="text-primary">5:00</strong>
                                    </span>
                                    <button class="btn-outline-primary btn btn-sm" id="btnRefresh">
                                        <i class="me-1 mdi mdi-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>

                            {{-- ══════════════════════════════════════════
                                CARDS RESUMEN
                            ══════════════════════════════════════════ --}}
                            <div class="mb-4 row" id="cardsResumen">

                                {{-- Card Total Órdenes --}}
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-clipboard-list mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Total Órdenes Pendientes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cardTotalOrdenes">
                                                        <span class="placeholder-glow"><span
                                                                class="placeholder col-6"></span></span>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Críticas --}}
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-danger mdi mdi-alert-circle mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Críticas (&gt; 7 días)</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cardCriticas">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Promedio días --}}
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-info mdi mdi-timer-sand mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Promedio Días de Espera</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="cardPromedioDias">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Sede con más pendientes --}}
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-warning mdi mdi-map-marker-alert mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Sede con Más Pendientes</p>
                                                    <h5 class="mb-0 text-white fw-bold" id="cardSedeMayor">—</h5>
                                                    <small class="text-white-50" id="cardSedeMayorCount"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ══════════════════════════════════════════
                             GRÁFICO + DISTRIBUCIÓN POR SEDE
                        ══════════════════════════════════════════ --}}
                            <div class="mb-4 row">
                                <div class="mb-3 col-lg-8">
                                    <div class="h-100 card">
                                        <div class="card-body">
                                            <h4 class="mb-3 card-title">
                                                <i class="me-2 text-primary mdi mdi-chart-bar"></i>
                                                Órdenes Pendientes por Sede
                                            </h4>
                                            <canvas id="chartSedes" height="90"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-lg-4">
                                    <div class="h-100 card">
                                        <div class="card-body">
                                            <h4 class="mb-3 card-title">
                                                <i class="me-2 text-danger mdi mdi-clock-alert"></i>
                                                Distribución por Rango de Días
                                            </h4>
                                            <canvas id="chartRangos" height="170"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ══════════════════════════════════════════
                             FILTROS + TABLA
                        ══════════════════════════════════════════ --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">

                                            {{-- Filtros --}}
                                            <div
                                                class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
                                                <h4 class="mb-0 card-title">
                                                    <i class="mdi-table me-2 text-info mdi"></i>
                                                    Detalle de Órdenes
                                                    <span class="ms-2 badge badge-info" id="badgeTotalFiltradas">0</span>
                                                </h4>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <select class="form-select-sm form-select" id="filtroSede"
                                                        style="min-width:160px">
                                                        <option value="">Todas las sedes</option>
                                                    </select>
                                                    <select class="form-select-sm form-select" id="filtroRango"
                                                        style="min-width:160px">
                                                        <option value="">Todos los rangos</option>
                                                        <option value="1">1-3 días</option>
                                                        <option value="2">4-7 días</option>
                                                        <option value="3">&gt; 7 días (crítico)</option>
                                                    </select>
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="filtroBuscar" placeholder="Buscar orden, cliente, RUC..."
                                                        style="min-width:220px">
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-hover" id="tablaOrdenes">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>SEDE</th>
                                                            <th>N° ORDEN</th>
                                                            <th>RUC</th>
                                                            <th>CLIENTE</th>
                                                            <th>DISEÑO</th>
                                                            <th>PRODUCTO</th>
                                                            <th class="text-end">IMPORTE</th>
                                                            <th>FECHA</th>
                                                            <th>HORA</th>
                                                            <th>ESTADO</th>
                                                            <th>USUARIO</th>
                                                            <th>UBICACIÓN</th>
                                                            <th>TALLA</th>
                                                            <th>TRAT.</th>
                                                            <th class="text-center">TIEMPO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbodyOrdenes">
                                                        <tr>
                                                            <td colspan="16" class="py-4 text-center">
                                                                <div class="spinner-border text-primary" role="status">
                                                                </div>
                                                                <p class="mt-2 text-muted">Cargando datos...</p>
                                                            </td>
                                                        </tr>
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

    <style>
        .icon-wrapper {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .badge-tiempo {
            font-size: .85rem;
            padding: .35em .65em;
        }

        .row-critica {
            background-color: rgba(255, 0, 0, 0.04) !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            'use strict';

            // ── ESTADO ───────────────────────────────────────────────────
            let todasLasOrdenes = [];
            let chartSedes = null;
            let chartRangos = null;
            let countdownSeg = 300; // 5 min
            let timerInterval = null;
            let countdownTimer = null;

            // ── CARGA DE DATOS ───────────────────────────────────────────
            function cargarDatos(limpiarCache) {
                limpiarCache = limpiarCache || false;

                const doLoad = function() {
                    const sede = document.getElementById('filtroSede').value;
                    const rango = document.getElementById('filtroRango').value;

                    fetch(
                            `{{ route('comercial.api.pendiente-montura.data') }}?sede=${encodeURIComponent(sede)}&rango=${encodeURIComponent(rango)}`
                            )
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(res) {
                            if (!res.success) {
                                mostrarError(res.message || 'Error desconocido');
                                return;
                            }

                            todasLasOrdenes = res.data || [];
                            poblarSedes(res.sedes || []);
                            actualizarCards(todasLasOrdenes);
                            actualizarGraficos(todasLasOrdenes);
                            renderTabla(todasLasOrdenes);

                            const ahora = new Date();
                            document.getElementById('lblUltimaActualizacion').innerHTML =
                                '<i class="me-1 text-success mdi mdi-check-circle"></i> Actualizado: ' +
                                ahora.toLocaleTimeString('es-PE');
                        })
                        .catch(function() {
                            mostrarError('No se pudo conectar al servidor.');
                        });
                };

                if (limpiarCache) {
                    fetch('{{ route('comercial.api.pendiente-montura.clear-cache') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(doLoad).catch(doLoad);
                } else {
                    doLoad();
                }
            }

            // ── CARDS ────────────────────────────────────────────────────
            function actualizarCards(ordenes) {
                const total = ordenes.length;
                const criticas = ordenes.filter(function(o) {
                    return (o.tiempo_dias || 0) > 7;
                }).length;
                const tiempos = ordenes.map(function(o) {
                    return o.tiempo_dias || 0;
                }).filter(function(d) {
                    return d > 0;
                });
                const promedio = tiempos.length > 0 ?
                    (tiempos.reduce(function(a, b) {
                        return a + b;
                    }, 0) / tiempos.length).toFixed(1) :
                    '0';

                // Sede con más pendientes
                const conteoSede = {};
                ordenes.forEach(function(o) {
                    const s = o.sede || 'Sin sede';
                    conteoSede[s] = (conteoSede[s] || 0) + 1;
                });
                const topSede = Object.keys(conteoSede).sort(function(a, b) {
                    return conteoSede[b] - conteoSede[a];
                })[0] || '—';
                const topCount = conteoSede[topSede] || 0;

                document.getElementById('cardTotalOrdenes').textContent = total;
                document.getElementById('cardCriticas').textContent = criticas;
                document.getElementById('cardPromedioDias').textContent = promedio + ' días';
                document.getElementById('cardSedeMayor').textContent = topSede;
                document.getElementById('cardSedeMayorCount').textContent = topCount + ' órdenes';
            }

            // ── GRÁFICOS ─────────────────────────────────────────────────
            function actualizarGraficos(ordenes) {
                // --- Por Sede ---
                const conteoSede = {};
                ordenes.forEach(function(o) {
                    const s = o.sede || 'Sin sede';
                    conteoSede[s] = (conteoSede[s] || 0) + 1;
                });
                const sedesLabels = Object.keys(conteoSede).sort(function(a, b) {
                    return conteoSede[b] - conteoSede[a];
                });
                const sedesData = sedesLabels.map(function(s) {
                    return conteoSede[s];
                });

                const coloresSede = sedesLabels.map(function(_, i) {
                    const palette = [
                        'rgba(54,162,235,.8)', 'rgba(255,206,86,.8)', 'rgba(75,192,192,.8)',
                        'rgba(255,99,132,.8)', 'rgba(153,102,255,.8)', 'rgba(255,159,64,.8)',
                        'rgba(99,255,132,.8)', 'rgba(54,235,200,.8)'
                    ];
                    return palette[i % palette.length];
                });

                if (chartSedes) {
                    chartSedes.data.labels = sedesLabels;
                    chartSedes.data.datasets[0].data = sedesData;
                    chartSedes.data.datasets[0].backgroundColor = coloresSede;
                    chartSedes.update();
                } else {
                    const ctx = document.getElementById('chartSedes').getContext('2d');
                    chartSedes = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: sedesLabels,
                            datasets: [{
                                label: 'Órdenes pendientes',
                                data: sedesData,
                                backgroundColor: coloresSede,
                                borderRadius: 5,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // --- Por Rango de Días ---
                const r1 = ordenes.filter(function(o) {
                    return (o.tiempo_dias || 0) >= 1 && (o.tiempo_dias || 0) <= 3;
                }).length;
                const r2 = ordenes.filter(function(o) {
                    return (o.tiempo_dias || 0) >= 4 && (o.tiempo_dias || 0) <= 7;
                }).length;
                const r3 = ordenes.filter(function(o) {
                    return (o.tiempo_dias || 0) > 7;
                }).length;

                if (chartRangos) {
                    chartRangos.data.datasets[0].data = [r1, r2, r3];
                    chartRangos.update();
                } else {
                    const ctx2 = document.getElementById('chartRangos').getContext('2d');
                    chartRangos = new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: ['1-3 días', '4-7 días', '> 7 días (crítico)'],
                            datasets: [{
                                data: [r1, r2, r3],
                                backgroundColor: [
                                    'rgba(75,192,192,.85)',
                                    'rgba(255,206,86,.85)',
                                    'rgba(255,99,132,.85)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            return ' ' + ctx.label + ': ' + ctx.parsed + ' órdenes';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // ── TABLA ────────────────────────────────────────────────────
            function renderTabla(ordenes) {
                const buscar = document.getElementById('filtroBuscar').value.toLowerCase();
                const rango = document.getElementById('filtroRango').value;

                let filtradas = ordenes;

                if (buscar) {
                    filtradas = filtradas.filter(function(o) {
                        return ((o.numero_orden || '') + (o.cliente || '') + (o.ruc || '') + (o.sede || ''))
                            .toLowerCase().includes(buscar);
                    });
                }

                if (rango === '1') filtradas = filtradas.filter(function(o) {
                    return (o.tiempo_dias || 0) >= 1 && (o.tiempo_dias || 0) <= 3;
                });
                if (rango === '2') filtradas = filtradas.filter(function(o) {
                    return (o.tiempo_dias || 0) >= 4 && (o.tiempo_dias || 0) <= 7;
                });
                if (rango === '3') filtradas = filtradas.filter(function(o) {
                    return (o.tiempo_dias || 0) > 7;
                });

                document.getElementById('badgeTotalFiltradas').textContent = filtradas.length;

                if (filtradas.length === 0) {
                    document.getElementById('tbodyOrdenes').innerHTML =
                        '<tr><td colspan="16" class="py-5 text-muted text-center">' +
                        '<i class="d-block mb-2 mdi mdi-inbox mdi-48px"></i>No hay órdenes pendientes</td></tr>';
                    return;
                }

                const html = filtradas.map(function(o, i) {
                    const dias = o.tiempo_dias;
                    let tiempoBadge;
                    let rowClass = '';

                    if (dias === null || dias === undefined) {
                        tiempoBadge = '<span class="text-muted">—</span>';
                    } else if (dias > 7) {
                        tiempoBadge = '<span class="bg-danger badge badge-tiempo">' + dias + ' días</span>';
                        rowClass = 'row-critica';
                    } else if (dias >= 4) {
                        tiempoBadge = '<span class="bg-warning text-dark badge badge-tiempo">' + dias +
                            ' días</span>';
                    } else {
                        tiempoBadge = '<span class="bg-danger badge badge-tiempo">' + dias + ' día' + (dias ===
                            1 ? '' : 's') + '</span>';
                    }

                    return '<tr class="' + rowClass + '">' +
                        '<td class="fw-bold">' + (i + 1) + '</td>' +
                        '<td><span class="badge badge-primary">' + esc(o.sede) + '</span></td>' +
                        '<td class="text-primary fw-bold">' + esc(o.numero_orden) + '</td>' +
                        '<td><small class="text-muted">' + esc(o.ruc) + '</small></td>' +
                        '<td class="fw-semibold">' + esc(o.cliente) + '</td>' +
                        '<td><small>' + esc(o.diseno) + '</small></td>' +
                        '<td><small>' + esc(o.descripcion_prod) + '</small></td>' +
                        '<td class="text-end">S/ ' + esc(o.importe) + '</td>' +
                        '<td><small>' + esc(o.fecha_orden) + '</small></td>' +
                        '<td><small>' + esc(o.hora_orden) + '</small></td>' +
                        '<td>' + esc(o.estado) + '</td>' +
                        '<td><small>' + esc(o.nombre_usuario) + '</small></td>' +
                        '<td><small>' + esc(o.ubicacion) + '</small></td>' +
                        '<td><small>' + esc(o.descripcion_talla) + '</small></td>' +
                        '<td><small>' + esc(o.tratamiento) + '</small></td>' +
                        '<td class="text-center">' + tiempoBadge + '</td>' +
                        '</tr>';
                }).join('');

                document.getElementById('tbodyOrdenes').innerHTML = html;
            }

            // ── POBLAR SEDES ─────────────────────────────────────────────
            function poblarSedes(sedes) {
                const sel = document.getElementById('filtroSede');
                const actual = sel.value;
                sel.innerHTML = '<option value="">Todas las sedes</option>' +
                    sedes.map(function(s) {
                        return '<option value="' + esc(s) + '">' + esc(s) + '</option>';
                    }).join('');
                if (actual) sel.value = actual;
            }

            // ── COUNTDOWN AUTO-REFRESH ───────────────────────────────────
            function iniciarCountdown() {
                countdownSeg = 300;
                clearInterval(countdownTimer);
                countdownTimer = setInterval(function() {
                    countdownSeg--;
                    const m = Math.floor(countdownSeg / 60);
                    const s = countdownSeg % 60;
                    document.getElementById('lblCountdown').textContent =
                        m + ':' + (s < 10 ? '0' : '') + s;

                    if (countdownSeg <= 0) {
                        cargarDatos(false);
                        countdownSeg = 300;
                    }
                }, 1000);
            }

            // ── HELPERS ──────────────────────────────────────────────────
            function esc(v) {
                if (v === null || v === undefined) return '';
                return String(v)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function mostrarError(msg) {
                document.getElementById('tbodyOrdenes').innerHTML =
                    '<tr><td colspan="16" class="py-4 text-danger text-center">' +
                    '<i class="d-block mb-2 mdi mdi-alert-circle mdi-36px"></i>' + esc(msg) + '</td></tr>';
                document.getElementById('lblUltimaActualizacion').innerHTML =
                    '<i class="me-1 text-danger mdi mdi-alert"></i> Error al cargar';
            }

            // ── EVENTOS ──────────────────────────────────────────────────
            document.getElementById('filtroSede').addEventListener('change', function() {
                cargarDatos(false);
            });
            document.getElementById('filtroRango').addEventListener('change', function() {
                renderTabla(todasLasOrdenes);
            });
            document.getElementById('filtroBuscar').addEventListener('input', function() {
                renderTabla(todasLasOrdenes);
            });
            document.getElementById('btnRefresh').addEventListener('click', function() {
                iniciarCountdown();
                cargarDatos(true);
            });

            // ── INIT ─────────────────────────────────────────────────────
            cargarDatos(false);
            iniciarCountdown();

        })();
    </script>
@endsection
