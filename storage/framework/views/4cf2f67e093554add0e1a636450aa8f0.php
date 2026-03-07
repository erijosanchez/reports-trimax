


<?php $__env->startSection('title', 'Venta Clientes Evolutivo - Mes'); ?>

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
                                        <i class="mdi-account-group me-2 text-primary mdi"></i>
                                        Venta Clientes Evolutivo — Mes
                                    </h2>
                                    <p class="mb-0 text-muted">
                                        <i class="me-1 mdi mdi-google-spreadsheet"></i>
                                        Importes mensuales por cliente &nbsp;|&nbsp;
                                        <span id="subtituloPeriodo"><?php echo now()->year; ?></span>
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-3 mt-lg-0 col-lg-4">
                                    <div style="width:140px;">
                                        <label class="mb-1 text-muted form-label small">Año</label>
                                        <select id="selectAnio" class="form-select-sm form-select">
                                            <option>Cargando...</option>
                                        </select>
                                    </div>
                                    <div style="margin-top:22px;">
                                        <button id="btnRefresh" class="btn-outline-secondary btn btn-sm"
                                            title="Limpiar caché">
                                            <i class="mdi mdi-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-4 row">
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-tale">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-primary mdi mdi-account-multiple mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Total Clientes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="kpiClientes">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-dark-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-warning mdi mdi-cash-multiple mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Venta Total Año</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="kpiTotal">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-blue">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-success mdi mdi-map-marker-multiple mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Sedes Activas</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="kpiSedes">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-lg-3 col-md-6">
                                    <div class="h-100 card card-light-danger">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-white me-3 rounded icon-wrapper">
                                                    <i class="text-info mdi mdi-star mdi-36px"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-1 text-white small">Mejor Mes</p>
                                                    <h4 class="mb-0 text-white fw-bold" id="kpiMejorMes">—</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h4 class="mb-0 card-title">
                                                    <i class="mdi-table me-2 text-info mdi"></i>
                                                    Detalle por Cliente —
                                                    <span id="tituloTabla"><?php echo now()->year; ?></span>
                                                </h4>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="text" id="buscador"
                                                        class="form-control form-control-sm" style="width:220px;"
                                                        placeholder="Buscar cliente, RUC, sede...">
                                                    <span id="infoRegistros" class="text-muted text-nowrap small"></span>
                                                </div>
                                            </div>

                                            <div class="table-responsive" style="max-height:580px; overflow:auto;">
                                                <table class="table table-hover"
                                                    style="font-size:0.80rem; min-width:1400px;">
                                                    <thead class="table-light" style="position:sticky; top:0; z-index:5;">
                                                        <tr>
                                                            <th style="width:150px; max-width:150px;">SEDE</th>
                                                            <th style="min-width:100px;">RUC / DNI</th>
                                                            <th style="width:300px; max-width:300px;">CLIENTE</th>
                                                            <th class="text-end">ENE</th>
                                                            <th class="text-end">FEB</th>
                                                            <th class="text-end">MAR</th>
                                                            <th class="text-end">ABR</th>
                                                            <th class="text-end">MAY</th>
                                                            <th class="text-end">JUN</th>
                                                            <th class="text-end">JUL</th>
                                                            <th class="text-end">AGO</th>
                                                            <th class="text-end">SET</th>
                                                            <th class="text-end">OCT</th>
                                                            <th class="text-end">NOV</th>
                                                            <th class="text-end">DIC</th>
                                                            <th class="text-end fw-bold">TOTAL</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbodyMes">
                                                        <tr>
                                                            <td colspan="16" class="py-4 text-muted text-center">
                                                                <div class="me-2 spinner-border spinner-border-sm"></div>
                                                                Cargando datos...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot id="tfootMes"></tfoot>
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
        const MESES = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SETIEMBRE', 'OCTUBRE',
            'NOVIEMBRE', 'DICIEMBRE'
        ];

        let todosLosClientes = [];
        let anioActual = <?php echo now()->year; ?>;

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

        async function cargarAnios() {
            const res = await fetch('<?php echo route('comercial.venta-cliente.anios'); ?>');
            const data = await res.json();
            const sel = document.getElementById('selectAnio');
            sel.innerHTML = '';
            (data.anios || []).forEach(a => {
                const o = document.createElement('option');
                o.value = a;
                o.text = a;
                if (a == anioActual) o.selected = true;
                sel.appendChild(o);
            });
        }

        async function cargarDatos(anio) {
            document.getElementById('tbodyMes').innerHTML =
                `<tr><td colspan="16" class="py-4 text-muted text-center">
            <div class="me-2 spinner-border spinner-border-sm"></div>Cargando ${anio}...</td></tr>`;
            document.getElementById('tfootMes').innerHTML = '';
            document.getElementById('buscador').value = '';

            try {
                const res = await fetch(`<?php echo route('comercial.venta-cliente.mes.data'); ?>?anio=${anio}`);
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error desconocido');

                todosLosClientes = data.data || [];

                document.getElementById('tituloTabla').textContent = anio;
                document.getElementById('subtituloPeriodo').textContent = anio;

                actualizarKPIs(todosLosClientes);
                renderTabla(todosLosClientes);

            } catch (e) {
                document.getElementById('tbodyMes').innerHTML =
                    `<tr><td colspan="16" class="py-3 text-danger text-center">
                <i class="me-1 mdi mdi-alert"></i>${e.message}</td></tr>`;
            }
        }

        function actualizarKPIs(clientes) {
            const totalVenta = clientes.reduce((s, c) => s + (c.total || 0), 0);
            const sedes = new Set(clientes.map(c => c.sede)).size;

            const sumaMes = {};
            MESES.forEach(m => sumaMes[m] = 0);
            clientes.forEach(c => MESES.forEach(m => sumaMes[m] += c.meses[m] || 0));
            const mejorMes = Object.entries(sumaMes).sort((a, b) => b[1] - a[1])[0];

            document.getElementById('kpiClientes').textContent = clientes.length;
            document.getElementById('kpiTotal').textContent = 'S/ ' + fmtNum(totalVenta);
            document.getElementById('kpiSedes').textContent = sedes;
            document.getElementById('kpiMejorMes').textContent = mejorMes ?
                mejorMes[0].slice(0, 3) + ' — S/ ' + fmtNum(mejorMes[1]) :
                '—';
        }

        function renderTabla(clientes) {
            const tbody = document.getElementById('tbodyMes');
            const tfoot = document.getElementById('tfootMes');
            const totG = Array(12).fill(0);
            let grandT = 0;

            if (!clientes.length) {
                tbody.innerHTML = `<tr><td colspan="16" class="py-4 text-muted text-center">Sin resultados.</td></tr>`;
                tfoot.innerHTML = '';
                return;
            }

            let html = '',
                prevSede = null;

            clientes.forEach(c => {
                if (c.sede !== prevSede) {
                    html += `<tr class="table-active">
                <td colspan="16" class="px-3 py-1 text-uppercase fw-semibold small"
                    style="letter-spacing:.4px; color:#1e40af;">
                    <i class="me-1 mdi mdi-map-marker"></i>${c.sede}
                </td></tr>`;
                    prevSede = c.sede;
                }

                const celdas = MESES.map((m, idx) => {
                    const v = c.meses[m] || 0;
                    totG[idx] += v;
                    return `<td class="text-end" style="color:${v>0?'#1e3a5f':'#ccc'}">${fmt(v)}</td>`;
                }).join('');

                grandT += c.total || 0;

                html += `<tr>
            <td class="fw-medium" style="width:150px; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${c.sede}</td>
            <td class="text-muted small">${c.ruc}</td>
            <td style="width:300px; max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${c.razon}</td>
            ${celdas}
            <td class="text-end fw-bold" style="background:#eef2ff; color:#1e3a5f;">S/ ${fmtNum(c.total)}</td>
        </tr>`;
            });

            tbody.innerHTML = html;

            tfoot.innerHTML = `<tr class="table-primary fw-bold">
        <td colspan="3">TOTAL GENERAL</td>
        ${totG.map(v => `<td class="text-primary text-end">S/ ${fmtNum(v)}</td>`).join('')}
        <td class="text-end" style="background:#bfdbfe;">S/ ${fmtNum(grandT)}</td>
    </tr>`;

            document.getElementById('infoRegistros').textContent =
                `${clientes.length} clientes`;
        }

        document.getElementById('buscador').addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            renderTabla(q ? todosLosClientes.filter(c =>
                c.sede.toLowerCase().includes(q) || c.ruc.toLowerCase().includes(q) || c.razon.toLowerCase()
                .includes(q)
            ) : todosLosClientes);
        });

        document.getElementById('selectAnio').addEventListener('change', function() {
            anioActual = this.value;
            cargarDatos(anioActual);
        });

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
            await cargarDatos(anioActual);
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-refresh"></i>';
        });

        (async () => {
            await cargarAnios();
            await cargarDatos(anioActual);
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/venta-cliente/evolutivo-mes.blade.php ENDPATH**/ ?>