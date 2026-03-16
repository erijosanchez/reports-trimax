@extends('layouts.app')

@section('title', 'Evolutivo — Asignación de Bases')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- HEADER --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="mb-1 fw-bold">
                            <i class="me-2 text-primary mdi mdi-chart-timeline-variant"></i>
                            Evolutivo — Asignación de Bases
                        </h2>
                        <p class="mb-0 text-muted small">
                            <i class="me-1 mdi mdi-database"></i>
                            Rectificados vs Normal · KPI = Rectificado / Normal
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        {{-- Selector de año --}}
                        <div>
                            <label class="mb-1 text-muted form-label small">Año</label>
                            <select id="anioSelect" class="form-select-sm form-select">
                                @foreach ($aniosDisponibles as $anio)
                                    <option value="{{ $anio }}" {{ $anio == $anioActual ? 'selected' : '' }}>
                                        {{ $anio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botón limpiar caché --}}
                        <div class="mt-3">
                            <button id="btnLimpiarCache" class="btn-outline-secondary btn btn-sm" title="Limpiar caché">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA EVOLUTIVO SEMANAL — CANTIDAD
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 card-title">
                                <i class="mdi-table me-2 text-primary mdi"></i>
                                Evolutivo Semanal — Cantidad
                            </h5>
                            <span class="bg-primary badge" id="labelAnioSemanal">{{ $anioActual }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaSemanalCantidad">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        @foreach ($semanal['semanas'] as $s)
                                            <th class="text-center small">{{ $s['label'] }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodySemanaCantidad">
                                    @php
                                        $acumR = 0;
                                        $acumN = 0;
                                        $acumT = 0;
                                    @endphp
                                    {{-- Rectifica --}}
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        @foreach ($semanal['semanas'] as $s)
                                            <td class="text-center">{{ $s['R_cantidad'] ?: '' }}</td>
                                        @endforeach
                                        <td class="bg-danger text-white text-center fw-bold">{{ $semanal['total_R'] }}</td>
                                        <td class="text-center fw-bold">{{ $semanal['total_R'] }}</td>
                                    </tr>
                                    {{-- Normal --}}
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        @foreach ($semanal['semanas'] as $s)
                                            <td class="text-center">{{ $s['N_cantidad'] ?: '' }}</td>
                                        @endforeach
                                        <td class="bg-success text-white text-center fw-bold">{{ $semanal['total_N'] }}</td>
                                        <td class="text-center fw-bold">{{ $semanal['total_N'] }}</td>
                                    </tr>
                                    {{-- KPI --}}
                                    <tr class="fila-kpi">
                                        <td class="fw-bold" style="background:#fff3cd;">KPI</td>
                                        @foreach ($semanal['semanas'] as $s)
                                            <td class="text-center small" style="background:#fff3cd;">
                                                @if ($s['kpi'] > 0)
                                                    <span
                                                        class="badge {{ $s['kpi'] <= 20 ? 'bg-success' : ($s['kpi'] <= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $s['kpi'] }}%
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <span
                                                class="badge {{ $semanal['total_kpi'] <= 20 ? 'bg-success' : ($semanal['total_kpi'] <= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $semanal['total_kpi'] }}%
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            {{ $semanal['total_kpi'] }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA EVOLUTIVO SEMANAL — PRECIO
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <h5 class="mb-3 card-title">
                            <i class="me-2 text-warning mdi mdi-currency-usd"></i>
                            Evolutivo Semanal — Monto (S/)
                        </h5>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaSemanalPrecio">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        @foreach ($semanal['semanas'] as $s)
                                            <th class="text-center small">{{ $s['label'] }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodySemanaPrecios">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        @foreach ($semanal['semanas'] as $s)
                                            <td class="text-center small">
                                                {{ $s['R_precio'] > 0 ? 'S/ ' . number_format($s['R_precio'], 0, '.', ',') : '' }}
                                            </td>
                                        @endforeach
                                        <td class="bg-danger text-white text-center fw-bold">S/
                                            {{ number_format($semanal['total_R_precio'], 0, '.', ',') }}</td>
                                        <td class="text-center fw-bold">S/
                                            {{ number_format($semanal['total_R_precio'], 0, '.', ',') }}</td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        @foreach ($semanal['semanas'] as $s)
                                            <td class="text-center small">
                                                {{ $s['N_precio'] > 0 ? 'S/ ' . number_format($s['N_precio'], 0, '.', ',') : '' }}
                                            </td>
                                        @endforeach
                                        <td class="bg-success text-white text-center fw-bold">S/
                                            {{ number_format($semanal['total_N_precio'], 0, '.', ',') }}</td>
                                        <td class="text-center fw-bold">S/
                                            {{ number_format($semanal['total_N_precio'], 0, '.', ',') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA EVOLUTIVO MENSUAL — CANTIDAD
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <h5 class="mb-3 card-title">
                            <i class="me-2 text-info mdi mdi-calendar-month"></i>
                            Evolutivo Mensual — Cantidad
                        </h5>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaMensualCantidad">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        @foreach ($mensual['meses'] as $m)
                                            <th class="text-center small">{{ Str::upper(substr($m['label'], 0, 3)) }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMensualCantidad">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        @foreach ($mensual['meses'] as $m)
                                            <td class="text-center">{{ $m['R_cantidad'] ?: '' }}</td>
                                        @endforeach
                                        <td class="bg-danger text-white text-center fw-bold">{{ $mensual['total_R'] }}</td>
                                        <td class="text-center fw-bold">{{ $mensual['total_R'] }}</td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        @foreach ($mensual['meses'] as $m)
                                            <td class="text-center">{{ $m['N_cantidad'] ?: '' }}</td>
                                        @endforeach
                                        <td class="bg-success text-white text-center fw-bold">{{ $mensual['total_N'] }}
                                        </td>
                                        <td class="text-center fw-bold">{{ $mensual['total_N'] }}</td>
                                    </tr>
                                    <tr class="fila-kpi">
                                        <td class="fw-bold" style="background:#fff3cd;">KPI</td>
                                        @foreach ($mensual['meses'] as $m)
                                            <td class="text-center small" style="background:#fff3cd;">
                                                @if ($m['kpi'] > 0)
                                                    <span
                                                        class="badge {{ $m['kpi'] <= 20 ? 'bg-success' : ($m['kpi'] <= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $m['kpi'] }}%
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            <span
                                                class="badge {{ $mensual['total_kpi'] <= 20 ? 'bg-success' : ($mensual['total_kpi'] <= 50 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $mensual['total_kpi'] }}%
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold" style="background:#fff3cd;">
                                            {{ $mensual['total_kpi'] }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA EVOLUTIVO MENSUAL — MONTO
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <h5 class="mb-3 card-title">
                            <i class="me-2 text-warning mdi mdi-currency-usd"></i>
                            Evolutivo Mensual — Monto (S/)
                        </h5>
                        <div class="table-responsive">
                            <table class="evolutivo-table table table-bordered table-sm" id="tablaMensualPrecio">
                                <thead>
                                    <tr class="table-dark">
                                        <th style="min-width:90px">Estado</th>
                                        @foreach ($mensual['meses'] as $m)
                                            <th class="text-center small">{{ Str::upper(substr($m['label'], 0, 3)) }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">ACUM</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMensualPrecio">
                                    <tr class="fila-rectifica">
                                        <td class="bg-danger text-white fw-bold">Rectifica</td>
                                        @foreach ($mensual['meses'] as $m)
                                            <td class="text-center small">
                                                {{ $m['R_precio'] > 0 ? 'S/ ' . number_format($m['R_precio'], 0, '.', ',') : '' }}
                                            </td>
                                        @endforeach
                                        <td class="bg-danger text-white text-center fw-bold">S/
                                            {{ number_format($mensual['total_R_precio'], 0, '.', ',') }}</td>
                                        <td class="text-center fw-bold">S/
                                            {{ number_format($mensual['total_R_precio'], 0, '.', ',') }}</td>
                                    </tr>
                                    <tr class="fila-normal">
                                        <td class="bg-success text-white fw-bold">Normal</td>
                                        @foreach ($mensual['meses'] as $m)
                                            <td class="text-center small">
                                                {{ $m['N_precio'] > 0 ? 'S/ ' . number_format($m['N_precio'], 0, '.', ',') : '' }}
                                            </td>
                                        @endforeach
                                        <td class="bg-success text-white text-center fw-bold">S/
                                            {{ number_format($mensual['total_N_precio'], 0, '.', ',') }}</td>
                                        <td class="text-center fw-bold">S/
                                            {{ number_format($mensual['total_N_precio'], 0, '.', ',') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                GRÁFICOS
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 row">
                    <div class="mb-3 col-lg-6">
                        <div class="h-100 card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                    <i class="me-2 text-danger mdi mdi-chart-line"></i>
                                    Rectificados por Día — Cantidad
                                </h5>
                                <canvas id="grafLinealDiario" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 col-lg-6">
                        <div class="h-100 card">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                    <i class="me-2 text-warning mdi mdi-chart-bar"></i>
                                    Rectificados por Mes — Monto (S/)
                                </h5>
                                <canvas id="grafBarrasMensual" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .evolutivo-table th,
        .evolutivo-table td {
            white-space: nowrap;
            font-size: 0.8rem;
            padding: 5px 8px;
        }

        .fila-rectifica td {
            background-color: #fff5f5;
        }

        .fila-normal td {
            background-color: #f0fff4;
        }

        .fila-kpi td {
            background-color: #fffbeb;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let grafLinea, grafBarras;

        const grafLineaData = @json($grafLinea);
        const grafBarrasData = @json($grafBarras);

        document.addEventListener('DOMContentLoaded', function() {
            inicializarGraficos();

            document.getElementById('anioSelect').addEventListener('change', actualizarTodo);
            document.getElementById('btnLimpiarCache').addEventListener('click', limpiarCache);
        });

        function inicializarGraficos() {
            // Línea diaria
            grafLinea = new Chart(document.getElementById('grafLinealDiario'), {
                type: 'line',
                data: {
                    labels: grafLineaData.labels,
                    datasets: [{
                        label: 'Rectificados',
                        data: grafLineaData.data,
                        borderColor: 'rgba(220,53,69,1)',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(220,53,69,1)',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rectificados: ' + ctx.parsed.y
                            }
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

            // Barras mensual
            grafBarras = new Chart(document.getElementById('grafBarrasMensual'), {
                type: 'bar',
                data: {
                    labels: grafBarrasData.labels,
                    datasets: [{
                        label: 'S/ Rectificados',
                        data: grafBarrasData.data,
                        backgroundColor: 'rgba(255,193,7,0.8)',
                        borderColor: 'rgba(255,193,7,1)',
                        borderWidth: 2,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'S/ ' + ctx.parsed.y.toLocaleString('es-PE')
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'S/ ' + val.toLocaleString('es-PE')
                            }
                        }
                    }
                }
            });
        }

        async function actualizarTodo() {
            const anio = document.getElementById('anioSelect').value;

            try {
                const res = await fetch(`{{ route('produccion.asignacion-bases.evolutivo-data') }}?anio=${anio}`);
                const data = await res.json();

                actualizarTablaSemanalCantidad(data.semanal);
                actualizarTablaSemanalPrecio(data.semanal);
                actualizarTablaMensualCantidad(data.mensual);
                actualizarTablaMensualPrecio(data.mensual);

                // Gráficos
                grafLinea.data.labels = data.grafLinea.labels;
                grafLinea.data.datasets[0].data = data.grafLinea.data;
                grafLinea.update();

                grafBarras.data.labels = data.grafBarras.labels;
                grafBarras.data.datasets[0].data = data.grafBarras.data;
                grafBarras.update();

                document.getElementById('labelAnioSemanal').textContent = anio;

            } catch (e) {
                console.error('Error al actualizar evolutivo:', e);
            }
        }

        function badgeKpi(kpi) {
            const cls = kpi <= 20 ? 'bg-success' : (kpi <= 50 ? 'bg-warning' : 'bg-danger');
            return kpi > 0 ? `<span class="badge ${cls}">${kpi}%</span>` : '';
        }

        function actualizarTablaSemanalCantidad(semanal) {
            const semanas = semanal.semanas;
            const filas = ['fila-rectifica', 'fila-normal', 'fila-kpi'];
            const tbody = document.getElementById('bodySemanaCantidad');

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;
            let rowK = `<tr class="fila-kpi"><td class="fw-bold" style="background:#fff3cd;">KPI</td>`;

            semanas.forEach(s => {
                rowR += `<td class="text-center">${s.R_cantidad || ''}</td>`;
                rowN += `<td class="text-center">${s.N_cantidad || ''}</td>`;
                rowK += `<td class="text-center small" style="background:#fff3cd;">${badgeKpi(s.kpi)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">${semanal.total_R}</td><td class="text-center fw-bold">${semanal.total_R}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">${semanal.total_N}</td><td class="text-center fw-bold">${semanal.total_N}</td></tr>`;
            rowK +=
                `<td class="text-center fw-bold" style="background:#fff3cd;">${badgeKpi(semanal.total_kpi)}</td><td class="text-center fw-bold" style="background:#fff3cd;">${semanal.total_kpi}%</td></tr>`;

            tbody.innerHTML = rowR + rowN + rowK;
        }

        function actualizarTablaSemanalPrecio(semanal) {
            const semanas = semanal.semanas;
            const tbody = document.getElementById('bodySemanaPrecios');
            const fmt = v => v > 0 ? 'S/ ' + v.toLocaleString('es-PE') : '';

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;

            semanas.forEach(s => {
                rowR += `<td class="text-center small">${fmt(s.R_precio)}</td>`;
                rowN += `<td class="text-center small">${fmt(s.N_precio)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">S/ ${semanal.total_R_precio.toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${semanal.total_R_precio.toLocaleString('es-PE')}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">S/ ${semanal.total_N_precio.toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${semanal.total_N_precio.toLocaleString('es-PE')}</td></tr>`;

            tbody.innerHTML = rowR + rowN;
        }

        function actualizarTablaMensualCantidad(mensual) {
            const meses = mensual.meses;
            const tbody = document.getElementById('bodyMensualCantidad');

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;
            let rowK = `<tr class="fila-kpi"><td class="fw-bold" style="background:#fff3cd;">KPI</td>`;

            meses.forEach(m => {
                rowR += `<td class="text-center">${m.R_cantidad || ''}</td>`;
                rowN += `<td class="text-center">${m.N_cantidad || ''}</td>`;
                rowK += `<td class="text-center small" style="background:#fff3cd;">${badgeKpi(m.kpi)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">${mensual.total_R}</td><td class="text-center fw-bold">${mensual.total_R}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">${mensual.total_N}</td><td class="text-center fw-bold">${mensual.total_N}</td></tr>`;
            rowK +=
                `<td class="text-center fw-bold" style="background:#fff3cd;">${badgeKpi(mensual.total_kpi)}</td><td class="text-center fw-bold" style="background:#fff3cd;">${mensual.total_kpi}%</td></tr>`;

            tbody.innerHTML = rowR + rowN + rowK;
        }

        function actualizarTablaMensualPrecio(mensual) {
            const meses = mensual.meses;
            const tbody = document.getElementById('bodyMensualPrecio');
            const fmt = v => v > 0 ? 'S/ ' + Math.round(v).toLocaleString('es-PE') : '';

            let rowR = `<tr class="fila-rectifica"><td class="bg-danger text-white fw-bold">Rectifica</td>`;
            let rowN = `<tr class="fila-normal"><td class="bg-success text-white fw-bold">Normal</td>`;

            meses.forEach(m => {
                rowR += `<td class="text-center small">${fmt(m.R_precio)}</td>`;
                rowN += `<td class="text-center small">${fmt(m.N_precio)}</td>`;
            });

            rowR +=
                `<td class="bg-danger text-white text-center fw-bold">S/ ${Math.round(mensual.total_R_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(mensual.total_R_precio).toLocaleString('es-PE')}</td></tr>`;
            rowN +=
                `<td class="bg-success text-white text-center fw-bold">S/ ${Math.round(mensual.total_N_precio).toLocaleString('es-PE')}</td><td class="text-center fw-bold">S/ ${Math.round(mensual.total_N_precio).toLocaleString('es-PE')}</td></tr>`;

            tbody.innerHTML = rowR + rowN;
        }

        async function limpiarCache() {
            const btn = document.getElementById('btnLimpiarCache');
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i>';

            try {
                await fetch('{{ route('produccion.asignacion-bases.clear-cache') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                await actualizarTodo();
            } catch (e) {
                console.error(e);
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-refresh"></i>';
        }
    </script>
@endsection
