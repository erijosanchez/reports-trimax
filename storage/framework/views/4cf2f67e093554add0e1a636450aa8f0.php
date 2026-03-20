


<?php $__env->startSection('title', 'Venta Clientes Evolutivo - Mes'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">

                            
                            <div class="mb-4 row">
                                <div class="col-lg-7">
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
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-3 mt-lg-0 col-lg-5">

                                    
                                    <div id="wrapAnio" style="width:110px;">
                                        <label class="mb-1 text-muted form-label small">Año</label>
                                        <select id="selectAnio" class="form-select-sm form-select">
                                            <option>Cargando...</option>
                                        </select>
                                    </div>

                                    
                                    <div style="width:190px;">
                                        <label class="mb-1 text-muted form-label small">Rango de meses</label>
                                        <select id="selectRango" class="form-select-sm form-select">
                                            <option value="">— Año completo —</option>
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
                                                    <p class="mb-1 text-white small">Venta Total Período</p>
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
                                                        <tr id="trHeaders"></tr>
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
        const MESES_NOMBRES = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
            'JULIO', 'AGOSTO', 'SETIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
        ];
        const MESES_CORTOS = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
            'JUL', 'AGO', 'SET', 'OCT', 'NOV', 'DIC'
        ];

        // Cache de datos por año: { 2024: [...], 2025: [...] }
        const cacheData = {};

        let anioActual = <?php echo now()->year; ?>;
        let mesActual = <?php echo now()->month; ?>;
        let aniosDisp = [];

        // Estado del último render — para el buscador
        let estadoActual = {
            clientes: [],
            mesesActivos: [],
            esRango: false
        };

        // ── Formato ───────────────────────────────────────────────────────────
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

        // ── Carga años y genera opciones de rango ─────────────────────────────
        async function cargarAnios() {
            const res = await fetch('<?php echo route('comercial.venta-cliente.anios'); ?>');
            const data = await res.json();
            aniosDisp = (data.anios || []).map(Number).sort((a, b) => b - a);

            const sel = document.getElementById('selectAnio');
            sel.innerHTML = '';
            aniosDisp.forEach(a => {
                const o = document.createElement('option');
                o.value = a;
                o.text = a;
                if (a === anioActual) o.selected = true;
                sel.appendChild(o);
            });

            generarOpcionesRango();
        }

        function generarOpcionesRango() {
            const sel = document.getElementById('selectRango');
            const anioMin = Math.min(...aniosDisp);
            const anioMax = Math.max(...aniosDisp);
            const totalMeses = (anioMax - anioMin) * 12 + mesActual;

            sel.innerHTML = '<option value="">— Año completo —</option>';

            for (let n = 3; n <= totalMeses; n += 3) {
                let mesIni = mesActual - n + 1;
                let anioIni = anioMax;
                while (mesIni <= 0) {
                    mesIni += 12;
                    anioIni--;
                }
                if (anioIni < anioMin) break;

                const o = document.createElement('option');
                o.value = `${n}|${mesIni}|${anioIni}|${mesActual}|${anioMax}`;
                o.text = `Últimos ${n} meses ` +
                    `(${MESES_CORTOS[mesIni-1]} ${anioIni} – ${MESES_CORTOS[mesActual-1]} ${anioMax})`;
                sel.appendChild(o);
            }
        }

        // ── Fetch de un año con cache (siempre devuelve copia para no mutar) ────
        async function fetchAnio(anio) {
            if (!cacheData[anio]) {
                const res = await fetch(`<?php echo route('comercial.venta-cliente.mes.data'); ?>?anio=${anio}`);
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error desconocido');
                cacheData[anio] = data.data || [];
            }
            // Deep clone para que nadie mute el cache original
            return JSON.parse(JSON.stringify(cacheData[anio]));
        }

        // ── Carga principal ───────────────────────────────────────────────────
        async function cargarDatos() {
            const rangoVal = document.getElementById('selectRango').value;
            anioActual = parseInt(document.getElementById('selectAnio').value);

            document.getElementById('tbodyMes').innerHTML =
                `<tr><td colspan="20" class="py-4 text-muted text-center">
                <div class="me-2 spinner-border spinner-border-sm"></div>Cargando...</td></tr>`;
            document.getElementById('tfootMes').innerHTML = '';
            document.getElementById('buscador').value = '';

            try {
                let clientes, mesesActivos, esRango;

                if (!rangoVal) {
                    // ── Año completo ──────────────────────────────────────────
                    document.getElementById('wrapAnio').style.display = '';
                    esRango = false;

                    const raw = await fetchAnio(anioActual);
                    clientes = raw;

                    mesesActivos = MESES_NOMBRES.map((nombre, idx) => ({
                        nombre,
                        corto: MESES_CORTOS[idx],
                        anio: anioActual,
                        idx
                    }));

                    actualizarSubtitulo(String(anioActual));

                } else {
                    // ── Rango de meses ────────────────────────────────────────
                    document.getElementById('wrapAnio').style.display = 'none';
                    esRango = true;

                    const [n, mesIni, anioIni, mesFin, anioFin] = rangoVal.split('|').map(Number);

                    // Construir lista de meses del rango
                    mesesActivos = [];
                    let a = anioIni,
                        m = mesIni;
                    while (a < anioFin || (a === anioFin && m <= mesFin)) {
                        mesesActivos.push({
                            anio: a,
                            idx: m - 1,
                            nombre: MESES_NOMBRES[m - 1],
                            corto: MESES_CORTOS[m - 1],
                        });
                        m++;
                        if (m > 12) {
                            m = 1;
                            a++;
                        }
                    }

                    // Años necesarios
                    const aniosRango = [...new Set(mesesActivos.map(x => x.anio))];
                    const resultados = await Promise.all(aniosRango.map(fetchAnio));

                    // Fusionar clientes de distintos años por clave ruc+sede
                    const mapa = {};
                    aniosRango.forEach((anio, i) => {
                        resultados[i].forEach(c => {
                            const key = `${c.ruc}||${c.sede}`;
                            if (!mapa[key]) {
                                mapa[key] = {
                                    sede: c.sede,
                                    ruc: c.ruc,
                                    razon: c.razon,
                                    meses: {},
                                    total: 0
                                };
                            }
                            // Guardar todos los meses con clave año-MES (incluye ceros)
                            MESES_NOMBRES.forEach(mn => {
                                const v = c.meses?.[mn] ?? 0;
                                mapa[key].meses[`${anio}-${mn}`] = (mapa[key].meses[
                                    `${anio}-${mn}`] || 0) + v;
                            });
                        });
                    });

                    clientes = Object.values(mapa);

                    // Calcular total solo para los meses del rango activo
                    clientes.forEach(c => {
                        c.total = mesesActivos.reduce((s, mx) =>
                            s + (c.meses[`${mx.anio}-${mx.nombre}`] || 0), 0);
                    });

                    const etiqueta = `${mesesActivos[0].corto} ${mesesActivos[0].anio}` +
                        ` – ${mesesActivos.at(-1).corto} ${mesesActivos.at(-1).anio}`;
                    actualizarSubtitulo(etiqueta);
                }

                // Guardar estado para el buscador
                estadoActual = {
                    clientes,
                    mesesActivos,
                    esRango
                };

                renderHeaders(mesesActivos, esRango);
                actualizarKPIs(clientes, mesesActivos, esRango);
                renderTabla(clientes, mesesActivos, esRango);

            } catch (e) {
                document.getElementById('tbodyMes').innerHTML =
                    `<tr><td colspan="20" class="py-3 text-danger text-center">
                    <i class="me-1 mdi mdi-alert"></i>${e.message}</td></tr>`;
            }
        }

        // ── Subtítulo y título tabla ──────────────────────────────────────────
        function actualizarSubtitulo(texto) {
            document.getElementById('subtituloPeriodo').textContent = texto;
            document.getElementById('tituloTabla').textContent = texto;
        }

        // ── Headers dinámicos ─────────────────────────────────────────────────
        function renderHeaders(mesesActivos, esRango) {
            let html = `<th style="width:150px; max-width:150px;">SEDE</th>
                        <th style="min-width:100px;">RUC / DNI</th>
                        <th style="width:300px; max-width:300px;">CLIENTE</th>`;
            mesesActivos.forEach(mx => {
                const label = esRango ?
                    `${mx.corto}<br><small class="text-muted fw-normal">${mx.anio}</small>` :
                    mx.corto;
                html += `<th class="text-end" style="min-width:72px;">${label}</th>`;
            });
            html += `<th class="text-end fw-bold">TOTAL</th>`;
            document.getElementById('trHeaders').innerHTML = html;
        }

        // ── KPIs ──────────────────────────────────────────────────────────────
        function actualizarKPIs(clientes, mesesActivos, esRango) {
            const totalVenta = clientes.reduce((s, c) => s + (c.total || 0), 0);
            const sedes = new Set(clientes.map(c => c.sede)).size;

            const sumaPorMes = {};
            mesesActivos.forEach(mx => {
                const etiq = `${mx.corto}${esRango ? ' ' + mx.anio : ''}`;
                sumaPorMes[etiq] = clientes.reduce((s, c) => {
                    const key = esRango ? `${mx.anio}-${mx.nombre}` : mx.nombre;
                    return s + (esRango ? (c.meses?.[key] || 0) : (c.meses?.[mx.nombre] || 0));
                }, 0);
            });
            const mejorMes = Object.entries(sumaPorMes).sort((a, b) => b[1] - a[1])[0];

            document.getElementById('kpiClientes').textContent = clientes.length;
            document.getElementById('kpiTotal').textContent = 'S/ ' + fmtNum(totalVenta);
            document.getElementById('kpiSedes').textContent = sedes;
            document.getElementById('kpiMejorMes').textContent = mejorMes ?
                mejorMes[0] + ' — S/ ' + fmtNum(mejorMes[1]) : '—';
        }

        // ── Render tabla ──────────────────────────────────────────────────────
        function renderTabla(clientes, mesesActivos, esRango) {
            const tbody = document.getElementById('tbodyMes');
            const tfoot = document.getElementById('tfootMes');
            const totG = Array(mesesActivos.length).fill(0);
            let grandT = 0;
            const cols = mesesActivos.length + 4;

            if (!clientes.length) {
                tbody.innerHTML = `<tr><td colspan="${cols}" class="py-4 text-muted text-center">Sin resultados.</td></tr>`;
                tfoot.innerHTML = '';
                return;
            }

            // Agrupar y ordenar
            const grupos = {};
            clientes.forEach(c => {
                if (!grupos[c.sede]) grupos[c.sede] = [];
                grupos[c.sede].push(c);
            });
            Object.keys(grupos).forEach(s =>
                grupos[s].sort((a, b) => (b.total || 0) - (a.total || 0))
            );
            const sedesOrdenadas = Object.keys(grupos).sort((a, b) => {
                const sA = grupos[a].reduce((s, c) => s + (c.total || 0), 0);
                const sB = grupos[b].reduce((s, c) => s + (c.total || 0), 0);
                return sB - sA;
            });

            let html = '';
            sedesOrdenadas.forEach(sede => {
                html += `<tr class="table-active">
                    <td colspan="${cols}" class="px-3 py-1 text-uppercase fw-semibold small"
                        style="letter-spacing:.4px; color:#1e40af;">
                        <i class="me-1 mdi mdi-map-marker"></i>${sede}
                    </td></tr>`;

                grupos[sede].forEach(c => {
                    const celdas = mesesActivos.map((mx, idx) => {
                        const key = esRango ? `${mx.anio}-${mx.nombre}` : mx.nombre;
                        const v = esRango ? (c.meses?.[key] || 0) : (c.meses?.[mx.nombre] || 0);
                        totG[idx] += v;
                        return `<td class="text-end" style="color:${v > 0 ? '#1e3a5f' : '#ccc'}">${fmt(v)}</td>`;
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
            });

            tbody.innerHTML = html;
            tfoot.innerHTML = `<tr class="table-primary fw-bold">
                <td colspan="3">TOTAL GENERAL</td>
                ${totG.map(v => `<td class="text-primary text-end">S/ ${fmtNum(v)}</td>`).join('')}
                <td class="text-end" style="background:#bfdbfe;">S/ ${fmtNum(grandT)}</td>
            </tr>`;

            document.getElementById('infoRegistros').textContent = `${clientes.length} clientes`;
        }

        // ── Buscador — usa estadoActual directamente ──────────────────────────
        document.getElementById('buscador').addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            const {
                clientes,
                mesesActivos,
                esRango
            } = estadoActual;
            const filtrados = q ? clientes.filter(c =>
                c.sede.toLowerCase().includes(q) ||
                c.ruc.toLowerCase().includes(q) ||
                c.razon.toLowerCase().includes(q)
            ) : clientes;
            renderTabla(filtrados, mesesActivos, esRango);
        });

        // ── Eventos ───────────────────────────────────────────────────────────
        document.getElementById('selectAnio').addEventListener('change', cargarDatos);
        document.getElementById('selectRango').addEventListener('change', cargarDatos);

        document.getElementById('btnRefresh').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            Object.keys(cacheData).forEach(k => delete cacheData[k]);
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

        // ── Init ──────────────────────────────────────────────────────────────
        (async () => {
            await cargarAnios();
            await cargarDatos();
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/venta-cliente/evolutivo-mes.blade.php ENDPATH**/ ?>