@extends('layouts.app')

@section('title', 'Demanda — Asignación de Bases')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- HEADER --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="mb-1 fw-bold">
                            <i class="me-2 text-info mdi mdi-chart-scatter-plot"></i>
                            Demanda — Asignación de Bases
                        </h2>
                        <p class="mb-0 text-muted small">
                            <i class="me-1 mdi-cube-outline mdi"></i>
                            Análisis por base · Clasificación ABC (Pareto)
                        </p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
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
                        <div>
                            <label class="mb-1 text-muted form-label small">Mes</label>
                            <select id="mesSelect" class="form-select-sm form-select">
                                @foreach ($mesesNombres as $num => $nombre)
                                    <option value="{{ $num }}" {{ $num == $mesActual ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <button id="btnLimpiarCache" class="btn-outline-secondary btn btn-sm" title="Limpiar caché">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA DEMANDA SEMANAL
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h5 class="mb-0 card-title">
                                <i class="me-2 text-info mdi mdi-view-week"></i>
                                Demanda Semanal —
                                <span id="labelMesSemanal">{{ $mesesNombres[$mesActual] }}</span>
                                <span id="labelAnioSemanal">{{ $anioActual }}</span>
                            </h5>
                            <div class="d-flex gap-2">
                                @foreach (['A' => 'success', 'B' => 'primary', 'C' => 'warning', 'D' => 'secondary'] as $cat => $color)
                                    <span class="badge bg-{{ $color }}">{{ $cat }}</span>
                                @endforeach
                                <small class="align-self-center text-muted">= clasificación ABC</small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="demanda-table table table-bordered table-sm" id="tablaSemanal">
                                <thead>
                                    <tr class="table-dark">
                                        <th class="text-center" style="width:40px">Cat.</th>
                                        <th style="min-width:120px">Código Base</th>
                                        <th style="min-width:200px">Descripción</th>
                                        <th class="text-center">Semana 1</th>
                                        <th class="text-center">Semana 2</th>
                                        <th class="text-center">Semana 3</th>
                                        <th class="text-center">Semana 4</th>
                                        <th class="text-center">Semana 5</th>
                                        <th class="text-center fw-bold">Total Acum.</th>
                                    </tr>
                                </thead>
                                <tbody id="bodySemanal">
                                    @forelse($semanal['productos'] as $p)
                                        @php
                                            $catColor = match ($p['categoria']) {
                                                'A' => 'success',
                                                'B' => 'primary',
                                                'C' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $catColor }}">{{ $p['categoria'] }}</span>
                                            </td>
                                            <td class="small">{{ $p['codigo'] }}</td>
                                            <td class="small">{{ $p['descripcion'] }}</td>
                                            @for ($s = 1; $s <= 5; $s++)
                                                <td class="text-center small">{{ $p['semanas_fmt'][$s] ?? '0' }}</td>
                                            @endfor
                                            <td class="text-center fw-bold">{{ $p['total_fmt'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-4 text-muted text-center">
                                                <i class="d-block mb-2 mdi mdi-inbox mdi-48px"></i>
                                                No hay datos para este período
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Leyenda ranking --}}
                        <div class="mt-3 p-3 rounded" style="background:#f8f9fa; border:1px solid #dee2e6;">
                            <p class="mb-2 text-muted small fw-bold">
                                <i class="me-1 mdi-information-outline mdi"></i>
                                Clasificación ABC (Pareto) — de mayor a menor
                            </p>
                            <div class="d-flex flex-wrap gap-3" id="rankingSemanal">
                                @php
                                    $categorias = collect($semanal['productos'])->groupBy('categoria');
                                    $totalGenSem = $semanal['total_general'];
                                @endphp
                                @foreach (['A', 'B', 'C', 'D'] as $cat)
                                    @php
                                        $items = $categorias[$cat] ?? collect();
                                        $sumCat = $items->sum('total');
                                        $pct = $totalGenSem > 0 ? round(($sumCat / $totalGenSem) * 100) : 0;
                                        $colors = [
                                            'A' => 'success',
                                            'B' => 'primary',
                                            'C' => 'warning',
                                            'D' => 'secondary',
                                        ];
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-{{ $colors[$cat] }}">{{ $cat }}</span>
                                        <span class="text-muted small">
                                            {{ $items->count() }} ítems · {{ $sumCat }} uds · {{ $pct }}%
                                        </span>
                                    </div>
                                @endforeach
                                <div class="ms-auto small fw-bold">Total: {{ $totalGenSem }} uds</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                TABLA DEMANDA MENSUAL
            ══════════════════════════════════════════════ --}}
                <div class="mb-4 card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h5 class="mb-0 card-title">
                                <i class="me-2 text-primary mdi mdi-calendar-month"></i>
                                Demanda Mensual — <span id="labelAnioMensual">{{ $anioActual }}</span>
                            </h5>
                        </div>

                        <div class="table-responsive">
                            <table class="demanda-table table table-bordered table-sm" id="tablaMensual">
                                <thead>
                                    <tr class="table-dark">
                                        <th class="text-center" style="width:40px">Cat.</th>
                                        <th style="min-width:120px">Código Base</th>
                                        <th style="min-width:200px">Descripción</th>
                                        @foreach ($mensual['meses_labels'] as $m)
                                            <th class="text-center small">{{ Str::upper(substr($m, 0, 3)) }}</th>
                                        @endforeach
                                        <th class="text-center fw-bold">Total Acum.</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyMensual">
                                    @forelse($mensual['productos'] as $p)
                                        @php
                                            $catColor = match ($p['categoria']) {
                                                'A' => 'success',
                                                'B' => 'primary',
                                                'C' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $catColor }}">{{ $p['categoria'] }}</span>
                                            </td>
                                            <td class="small">{{ $p['codigo'] }}</td>
                                            <td class="small">{{ $p['descripcion'] }}</td>
                                            @for ($m = 1; $m <= 12; $m++)
                                                <td class="text-center small">{{ $p['meses_fmt'][$m] ?? '0' }}</td>
                                            @endfor
                                            <td class="text-center fw-bold">{{ $p['total_fmt'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="16" class="py-4 text-muted text-center">
                                                <i class="d-block mb-2 mdi mdi-inbox mdi-48px"></i>
                                                No hay datos para este año
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Leyenda ranking mensual --}}
                        <div class="mt-3 p-3 rounded" style="background:#f8f9fa; border:1px solid #dee2e6;">
                            <p class="mb-2 text-muted small fw-bold">
                                <i class="me-1 mdi-information-outline mdi"></i>
                                Clasificación ABC (Pareto) — de mayor a menor
                            </p>
                            <div class="d-flex flex-wrap gap-3" id="rankingMensual">
                                @php
                                    $categoriasMen = collect($mensual['productos'])->groupBy('categoria');
                                    $totalGenMen = $mensual['total_general'];
                                @endphp
                                @foreach (['A', 'B', 'C', 'D'] as $cat)
                                    @php
                                        $items = $categoriasMen[$cat] ?? collect();
                                        $sumCat = $items->sum('total');
                                        $pct = $totalGenMen > 0 ? round(($sumCat / $totalGenMen) * 100) : 0;
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-{{ $colors[$cat] }}">{{ $cat }}</span>
                                        <span class="text-muted small">
                                            {{ $items->count() }} ítems · {{ $sumCat }} uds · {{ $pct }}%
                                        </span>
                                    </div>
                                @endforeach
                                <div class="ms-auto small fw-bold">Total: {{ $totalGenMen }} uds</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .demanda-table th,
        .demanda-table td {
            font-size: 0.78rem;
            padding: 5px 7px;
            white-space: nowrap;
        }

        .demanda-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('anioSelect').addEventListener('change', actualizarTodo);
            document.getElementById('mesSelect').addEventListener('change', actualizarSemanal);
            document.getElementById('btnLimpiarCache').addEventListener('click', limpiarCache);
        });

        async function actualizarSemanal() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            try {
                const res = await fetch(
                    `{{ route('produccion.asignacion-bases.demanda-semanal-data') }}?anio=${anio}&mes=${mes}`);
                const data = await res.json();
                renderTablaSemanal(data);

                // Actualizar labels
                const mesOpt = document.getElementById('mesSelect');
                document.getElementById('labelMesSemanal').textContent =
                    mesOpt.options[mesOpt.selectedIndex].text + ' ';
                document.getElementById('labelAnioSemanal').textContent = anio;
            } catch (e) {
                console.error('Error semanal:', e);
            }
        }

        async function actualizarTodo() {
            const anio = document.getElementById('anioSelect').value;
            const mes = document.getElementById('mesSelect').value;

            await actualizarSemanal();

            try {
                const res = await fetch(`{{ route('produccion.asignacion-bases.demanda-mensual-data') }}?anio=${anio}`);
                const data = await res.json();
                renderTablaMensual(data);
                document.getElementById('labelAnioMensual').textContent = anio;
            } catch (e) {
                console.error('Error mensual:', e);
            }
        }

        function catBadge(cat) {
            const colors = {
                A: 'success',
                B: 'primary',
                C: 'warning',
                D: 'secondary'
            };
            return `<span class="badge bg-${colors[cat] || 'secondary'}">${cat}</span>`;
        }

        function renderTablaSemanal(data) {
            const tbody = document.getElementById('bodySemanal');
            if (!data.productos || data.productos.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="py-4 text-muted text-center">
                <i class="d-block mb-2 mdi mdi-inbox mdi-48px"></i>No hay datos para este período</td></tr>`;
                return;
            }

            let html = '';
            data.productos.forEach(p => {
                html += `<tr>
                <td class="text-center">${catBadge(p.categoria)}</td>
                <td class="small">${p.codigo}</td>
                <td class="small">${p.descripcion}</td>`;
                for (let s = 1; s <= 5; s++) {
                    html += `<td class="text-center small">${p.semanas_fmt?.[s] ?? '0'}</td>`;
                }
                html += `<td class="text-center fw-bold">${p.total_fmt}</td></tr>`;
            });

            tbody.innerHTML = html;

            // Actualizar leyenda ABC
            actualizarLeyendaABC('rankingSemanal', data.productos, data.total_general);
        }

        function renderTablaMensual(data) {
            const tbody = document.getElementById('bodyMensual');
            if (!data.productos || data.productos.length === 0) {
                tbody.innerHTML = `<tr><td colspan="16" class="py-4 text-muted text-center">
                <i class="d-block mb-2 mdi mdi-inbox mdi-48px"></i>No hay datos para este año</td></tr>`;
                return;
            }

            let html = '';
            data.productos.forEach(p => {
                html += `<tr>
                <td class="text-center">${catBadge(p.categoria)}</td>
                <td class="small">${p.codigo}</td>
                <td class="small">${p.descripcion}</td>`;
                for (let m = 1; m <= 12; m++) {
                    html += `<td class="text-center small">${p.meses_fmt?.[m] ?? '0'}</td>`;
                }
                html += `<td class="text-center fw-bold">${p.total_fmt}</td></tr>`;
            });

            tbody.innerHTML = html;

            actualizarLeyendaABC('rankingMensual', data.productos, data.total_general);
        }

        function actualizarLeyendaABC(containerId, productos, totalGeneral) {
            const container = document.getElementById(containerId);
            const colors = {
                A: 'success',
                B: 'primary',
                C: 'warning',
                D: 'secondary'
            };
            const grupos = {
                A: [],
                B: [],
                C: [],
                D: []
            };

            productos.forEach(p => {
                if (grupos[p.categoria] !== undefined) grupos[p.categoria].push(p);
            });

            let html = '';
            ['A', 'B', 'C', 'D'].forEach(cat => {
                const items = grupos[cat];
                const sumCat = items.reduce((acc, p) => acc + p.total, 0);
                const pct = totalGeneral > 0 ? Math.round((sumCat / totalGeneral) * 100) : 0;
                html += `<div class="d-flex align-items-center gap-2">
                <span class="badge bg-${colors[cat]}">${cat}</span>
                <span class="text-muted small">${items.length} ítems · ${sumCat} uds · ${pct}%</span>
            </div>`;
            });

            html += `<div class="ms-auto small fw-bold">Total: ${totalGeneral} uds</div>`;
            container.innerHTML = html;
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
