


<?php $__env->startSection('title', 'Venta Clientes Evolutivo - Año'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            
                            <div class="mb-4 row">
                                <div class="col-lg-8">
                                    <h2 class="mb-1 fw-bold">
                                        <i class="me-2 text-primary mdi mdi-chart-timeline-variant"></i>
                                        Venta Clientes Evolutivo — Año
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <i class="me-1 mdi mdi-google-spreadsheet"></i>
                                        Comparativo histórico anual por cliente
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-3 mt-lg-0 col-lg-4">
                                    <div style="margin-top:0;">
                                        <button id="btnRefresh" class="btn-outline-secondary btn btn-sm"
                                            title="Limpiar caché">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row" id="kpiAnios"></div>

                            

                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h4 class="mb-0 card-title">
                                                    <i class="mdi-table me-2 text-info mdi"></i>
                                                    Detalle por Cliente — Histórico Anual
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select id="filtroSede" class="form-select-sm form-select"
                                                        style="width:170px;">
                                                        <option value="">Todas las sedes</option>
                                                    </select>
                                                    <input type="text" id="buscador"
                                                        class="form-control form-control-sm" style="width:220px;"
                                                        placeholder="Buscar cliente, RUC...">
                                                    <span id="infoRegistros" class="text-muted text-nowrap small"></span>
                                                </div>
                                            </div>

                                            <div class="table-responsive" style="max-height:580px; overflow:auto;">
                                                <table class="table table-hover" style="font-size:0.80rem;">
                                                    <thead id="theadAnio" class="table-light"
                                                        style="position:sticky; top:0; z-index:5;"></thead>
                                                    <tbody id="tbodyAnio">
                                                        <tr>
                                                            <td colspan="8" class="py-4 text-muted text-center">
                                                                <div class="me-2 spinner-border spinner-border-sm"></div>
                                                                Cargando datos históricos...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot id="tfootAnio"></tfoot>
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
    </style>

    <script>
        let todosLosClientes = [];
        let aniosDisponibles = [];

        function fmt(n) {
            if (!n) return '<span class="text-muted">—</span>';
            return 'S/ ' + parseFloat(n).toLocaleString('es-PE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function fmtNum(n) {
            return parseFloat(n || 0).toLocaleString('es-PE', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function variacion(actual, anterior) {
            if (!anterior) return null;
            return ((actual - anterior) / anterior * 100).toFixed(1);
        }

        async function cargarDatos() {
            document.getElementById('tbodyAnio').innerHTML =
                `<tr><td colspan="8" class="py-4 text-muted text-center">
            <div class="me-2 spinner-border spinner-border-sm"></div>Cargando datos históricos...</td></tr>`;

            try {
                const res = await fetch('<?php echo route('comercial.venta-cliente.anio.data'); ?>');
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error desconocido');

                todosLosClientes = data.data || [];
                aniosDisponibles = data.anios || [];

                renderCabecera();
                renderKPIs();
                poblarFiltroSede();
                renderTabla(todosLosClientes);

            } catch (e) {
                document.getElementById('tbodyAnio').innerHTML =
                    `<tr><td colspan="8" class="py-3 text-danger text-center">
                <i class="me-1 mdi mdi-alert"></i>${e.message}</td></tr>`;
            }
        }

        function renderCabecera() {
            const cols = aniosDisponibles.map(a => `<th class="text-end" style="min-width:110px;">${a}</th>`).join('');
            document.getElementById('theadAnio').innerHTML = `<tr>
        <th style="min-width:90px;">SEDE</th>
        <th style="min-width:100px;">RUC / DNI</th>
        <th style="min-width:200px;">CLIENTE</th>
        ${cols}
        <th class="text-end fw-bold" style="min-width:110px;">TOTAL</th>
    </tr>`;
        }

        function renderKPIs() {
            const totPorAnio = {};
            aniosDisponibles.forEach(a => totPorAnio[a] = 0);
            todosLosClientes.forEach(c => aniosDisponibles.forEach(a => totPorAnio[a] += c.anios[a] || 0));

            const cardColors = ['card-tale', 'card-dark-blue', 'card-light-blue', 'card-light-danger'];
            const icons = ['mdi-calendar', 'mdi-calendar-check', 'mdi-calendar-star', 'mdi-calendar-clock'];

            document.getElementById('kpiAnios').innerHTML = aniosDisponibles.map((a, i) => {
                const total = totPorAnio[a];
                const prev = i > 0 ? totPorAnio[aniosDisponibles[i - 1]] : null;
                const vp = variacion(total, prev);
                const vpHtml = vp !== null ?
                    `<small class="ms-1 ${parseFloat(vp)>=0?'text-success':'text-danger'}">
                <i class="mdi mdi-${parseFloat(vp)>=0?'arrow-up':'arrow-down'}"></i>${Math.abs(vp)}%
               </small>` :
                    '';

                return `<div class="mb-3 col-lg-3 col-md-6">
            <div class="card ${cardColors[i % cardColors.length]} h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-white me-3 rounded icon-wrapper">
                            <i class="mdi ${icons[i % icons.length]} text-primary mdi-36px"></i>
                        </div>
                        <div>
                            <p class="mb-1 text-white small">${a}</p>
                            <h4 class="mb-0 text-white fw-bold">S/ ${fmtNum(total)} ${vpHtml}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
            }).join('');
        }

        function poblarFiltroSede() {
            const sedes = [...new Set(todosLosClientes.map(c => c.sede))].sort();
            const sel = document.getElementById('filtroSede');
            sel.innerHTML = '<option value="">Todas las sedes</option>';
            sedes.forEach(s => {
                const o = document.createElement('option');
                o.value = s;
                o.text = s;
                sel.appendChild(o);
            });
        }

        function renderTabla(clientes) {
            const tbody = document.getElementById('tbodyAnio');
            const tfoot = document.getElementById('tfootAnio');
            const totGlobal = {};
            aniosDisponibles.forEach(a => totGlobal[a] = 0);
            let grandT = 0;

            if (!clientes.length) {
                tbody.innerHTML =
                    `<tr><td colspan="${aniosDisponibles.length+4}" class="py-4 text-muted text-center">Sin resultados.</td></tr>`;
                tfoot.innerHTML = '';
                return;
            }

            let html = '',
                prevSede = null;

            clientes.forEach(c => {
                if (c.sede !== prevSede) {
                    html += `<tr class="table-active">
                <td colspan="${aniosDisponibles.length+3}" class="px-3 py-1 text-uppercase fw-semibold small"
                    style="letter-spacing:.4px; color:#1e40af;">
                    <i class="me-1 mdi mdi-map-marker"></i>${c.sede}
                </td></tr>`;
                    prevSede = c.sede;
                }

                const celdas = aniosDisponibles.map((a, idx) => {
                    const v = c.anios[a] || 0;
                    const prev = idx > 0 ? (c.anios[aniosDisponibles[idx - 1]] || 0) : null;
                    const vp = prev !== null ? variacion(v, prev) : null;
                    totGlobal[a] += v;

                    const arrow = vp !== null && v > 0 ?
                        ` <small class="${parseFloat(vp)>=0?'text-success':'text-danger'}">
                    <i class="mdi mdi-${parseFloat(vp)>=0?'arrow-up':'arrow-down'}"></i>${Math.abs(vp)}%</small>` :
                        '';

                    return `<td class="text-end" style="color:${v>0?'#1e3a5f':'#ccc'}">${fmt(v)}${arrow}</td>`;
                }).join('');

                grandT += c.total || 0;

                html += `<tr>
            <td class="fw-medium">${c.sede}</td>
            <td class="text-muted small">${c.ruc}</td>
            <td>${c.razon}</td>
            ${celdas}
            <td class="text-end fw-bold" style="background:#eef2ff; color:#1e3a5f;">S/ ${fmtNum(c.total)}</td>
        </tr>`;
            });

            tbody.innerHTML = html;

            tfoot.innerHTML = `<tr class="table-primary fw-bold">
        <td colspan="3">TOTAL GENERAL</td>
        ${aniosDisponibles.map(a => `<td class="text-primary text-end">S/ ${fmtNum(totGlobal[a])}</td>`).join('')}
        <td class="text-end" style="background:#bfdbfe;">S/ ${fmtNum(grandT)}</td>
    </tr>`;

            document.getElementById('infoRegistros').textContent = `${clientes.length} clientes`;
        }

        function aplicarFiltros() {
            const q = document.getElementById('buscador').value.toLowerCase().trim();
            const sede = document.getElementById('filtroSede').value;
            let f = todosLosClientes;
            if (sede) f = f.filter(c => c.sede === sede);
            if (q) f = f.filter(c =>
                c.sede.toLowerCase().includes(q) || c.ruc.toLowerCase().includes(q) || c.razon.toLowerCase().includes(q)
            );
            renderTabla(f);
        }

        document.getElementById('buscador').addEventListener('input', aplicarFiltros);
        document.getElementById('filtroSede').addEventListener('change', aplicarFiltros);

        document.getElementById('btnRefresh').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            await fetch('<?php echo route('comercial.venta-cliente.cache.clear'); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                    'Content-Type': 'application/json'
                }
            });
            await cargarDatos();
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-refresh"></i>';
        });

        cargarDatos();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/venta-cliente/evolutivo-anio.blade.php ENDPATH**/ ?>