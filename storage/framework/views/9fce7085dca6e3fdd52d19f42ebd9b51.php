<?php $__env->startSection('title', 'Lead Time Objetivo +'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper lt-wrapper">
        <div class="row">
            <div class="col-sm-12">

                
                <div class="lt-page-header">
                    <div class="lt-page-header-left">
                        <h1 class="lt-page-title">Lead Time: Objetivo +</h1>
                        <p class="lt-page-subtitle">Órdenes fuera de tiempo agrupadas por días de atraso y semana del año</p>
                    </div>
                </div>

                
                <div class="lt-filter-panel">
                    <div class="lt-filter-group">
                        <div class="lt-filter-item">
                            <label class="lt-filter-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Año
                            </label>
                            <select class="lt-select" id="filterYear"></select>
                        </div>
                    </div>
                    <div class="lt-filter-actions">
                        <button class="lt-btn lt-btn-ghost" onclick="clearCache()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 1 0 .49-3" />
                            </svg>
                            Limpiar caché
                        </button>
                        <button class="lt-btn lt-btn-primary" id="btnConsultar" onclick="loadData()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" id="btnIcon">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <span id="btnText">Consultar</span>
                        </button>
                    </div>
                </div>

                
                <div id="mainContent">
                    <div class="lt-loading-state">
                        <div class="lt-spinner"></div>
                        <p>Cargando datos...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

        :root {
            --lt-blue-950: #04122e;
            --lt-blue-900: #0a1f4d;
            --lt-blue-800: #0f2d6e;
            --lt-blue-700: #1a3f94;
            --lt-blue-600: #2252b8;
            --lt-blue-500: #2d65d8;
            --lt-blue-400: #5b87e8;
            --lt-blue-300: #93b5f3;
            --lt-blue-100: #dce8fb;
            --lt-blue-50: #eef4fd;
            --lt-cyan: #0ea5e9;
            --lt-cyan-lt: #e0f2fe;
            --lt-green: #10b981;
            --lt-green-lt: #d1fae5;
            --lt-amber: #f59e0b;
            --lt-amber-lt: #fef3c7;
            --lt-red: #ef4444;
            --lt-red-lt: #fee2e2;
            --lt-gray-950: #0c0e13;
            --lt-gray-800: #1e2130;
            --lt-gray-600: #4b5573;
            --lt-gray-400: #8892a8;
            --lt-gray-200: #e4e8f0;
            --lt-gray-100: #f2f4f8;
            --lt-gray-50: #f8f9fc;
            --lt-white: #ffffff;
            --lt-shadow-sm: 0 1px 3px rgba(4, 18, 46, .06), 0 2px 8px rgba(4, 18, 46, .04);
            --lt-shadow-md: 0 4px 16px rgba(4, 18, 46, .08), 0 1px 3px rgba(4, 18, 46, .05);
            --lt-shadow-lg: 0 8px 32px rgba(4, 18, 46, .12), 0 2px 8px rgba(4, 18, 46, .06);
            --lt-shadow-blue: 0 4px 20px rgba(45, 101, 216, .25);
            --lt-radius-sm: 8px;
            --lt-radius: 12px;
            --lt-radius-lg: 18px;
            --lt-radius-xl: 24px;
        }

        .lt-wrapper * {
            box-sizing: border-box;
        }

        .lt-wrapper,
        .lt-wrapper * {
            font-family: 'DM Sans', sans-serif;
        }

        .lt-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            padding-top: 0.5rem;
        }

        .lt-page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--lt-gray-950);
            letter-spacing: -0.04em;
            line-height: 1.1;
            margin: 0 0 0.25rem;
        }

        .lt-page-subtitle {
            font-size: 0.875rem;
            color: var(--lt-gray-400);
            font-weight: 400;
            margin: 0;
        }

        .lt-filter-panel {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            background: var(--lt-white);
            border: 1px solid var(--lt-gray-200);
            border-radius: var(--lt-radius-lg);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--lt-shadow-sm);
            flex-wrap: wrap;
        }

        .lt-filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .lt-filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            min-width: 160px;
        }

        .lt-filter-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--lt-gray-600);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .lt-select {
            appearance: none;
            background: var(--lt-gray-50) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238892a8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
            border: 1.5px solid var(--lt-gray-200);
            border-radius: var(--lt-radius-sm);
            padding: 0.6rem 2.2rem 0.6rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--lt-gray-950);
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s, background .2s;
            width: 100%;
        }

        .lt-select:focus {
            outline: none;
            border-color: var(--lt-blue-500);
            box-shadow: 0 0 0 3px rgba(45, 101, 216, .12);
            background-color: var(--lt-white);
        }

        .lt-select:hover {
            border-color: var(--lt-blue-300);
        }

        .lt-filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .lt-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0.6rem 1.2rem;
            border-radius: var(--lt-radius-sm);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .18s cubic-bezier(.4, 0, .2, 1);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .lt-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            transition: background .15s;
        }

        .lt-btn:hover::after {
            background: rgba(255, 255, 255, .08);
        }

        .lt-btn:active {
            transform: translateY(1px);
        }

        .lt-btn-primary {
            background: linear-gradient(135deg, var(--lt-blue-600) 0%, var(--lt-blue-800) 100%);
            color: white;
            box-shadow: var(--lt-shadow-blue);
        }

        .lt-btn-primary:hover {
            box-shadow: 0 6px 24px rgba(45, 101, 216, .35);
            transform: translateY(-1px);
        }

        .lt-btn-ghost {
            background: transparent;
            color: var(--lt-gray-600);
            border: 1.5px solid var(--lt-gray-200);
        }

        .lt-btn-ghost:hover {
            background: var(--lt-gray-50);
            color: var(--lt-gray-950);
            border-color: var(--lt-gray-400);
        }

        .lt-btn-loading #btnIcon {
            animation: ltSpin .9s linear infinite;
        }

        @keyframes ltSpin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .om-btn-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--lt-white);
            border: 1.5px solid var(--lt-gray-200);
            color: var(--lt-gray-600);
            cursor: pointer;
            transition: all .18s;
        }

        .om-btn-nav:hover:not(:disabled) {
            background: var(--lt-blue-50);
            border-color: var(--lt-blue-300);
            color: var(--lt-blue-600);
        }

        .om-btn-nav:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .lt-loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5rem 2rem;
            gap: 1rem;
            color: var(--lt-gray-400);
            font-size: 0.875rem;
        }

        .lt-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid var(--lt-gray-200);
            border-top-color: var(--lt-blue-500);
            border-radius: 50%;
            animation: ltSpin .7s linear infinite;
        }

        .lt-empty {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--lt-gray-400);
        }

        .lt-empty-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: var(--lt-gray-100);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lt-empty h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--lt-gray-600);
            margin-bottom: .35rem;
        }

        .lt-empty p {
            font-size: 0.83rem;
            margin: 0;
        }

        .lt-fade-up {
            opacity: 0;
            transform: translateY(18px);
            animation: ltFadeUp .45s ease forwards;
        }

        @keyframes ltFadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .lt-fade-up:nth-child(1) {
            animation-delay: 0s;
        }

        .lt-fade-up:nth-child(2) {
            animation-delay: .08s;
        }

        .lt-fade-up:nth-child(3) {
            animation-delay: .16s;
        }

        .lt-fade-up:nth-child(4) {
            animation-delay: .24s;
        }

        .lt-fade-up:nth-child(5) {
            animation-delay: .32s;
        }

        .lt-fade-up:nth-child(6) {
            animation-delay: .38s;
        }

        .lt-fade-up:nth-child(7) {
            animation-delay: .44s;
        }

        /* ── LAYOUT ── */
        .om-section {
            margin-bottom: 2rem;
        }

        .om-cats-grid {
            display: grid;
            /* 2 columnas en pantallas grandes (monitor de escritorio) */
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        /* En laptop y pantallas medianas: 1 columna para que las tablas no se corten */
        @media (max-width: 1400px) {
            .om-cats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Evita que los grid items desborden su contenedor */
        .lt-table-card {
            background: var(--lt-white);
            border-radius: var(--lt-radius-lg);
            border: 1px solid var(--lt-gray-200);
            box-shadow: var(--lt-shadow-sm);
            overflow: hidden;
            min-width: 0;
            /* fix: evita que el grid item desborde */
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }

        .lt-table-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--lt-shadow-md);
            border-color: var(--lt-blue-100);
        }

        .lt-table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.3rem;
            border-bottom: 1px solid var(--lt-gray-100);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .lt-table-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--lt-gray-950);
            letter-spacing: -0.01em;
        }

        .lt-table-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--lt-red-lt);
            color: var(--lt-red);
            font-size: 0.68rem;
            font-weight: 800;
            font-family: 'DM Mono', monospace;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .om-nav-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .om-period-badge {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--lt-blue-700);
            background: var(--lt-blue-50);
            border: 1px solid var(--lt-blue-100);
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-family: 'DM Mono', monospace;
            white-space: nowrap;
        }

        .om-cat-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .om-icon-general {
            background: linear-gradient(135deg, var(--lt-blue-800), var(--lt-blue-600));
            color: #fff;
        }

        .om-icon-nox {
            background: #ede9fe;
            color: #5b21b6;
        }

        .om-icon-td {
            background: var(--lt-blue-50);
            color: var(--lt-blue-700);
        }

        .om-icon-devablue {
            background: var(--lt-cyan-lt);
            color: #0369a1;
        }

        .om-icon-blanco {
            background: var(--lt-green-lt);
            color: #15803d;
        }

        .om-icon-coloreado {
            background: var(--lt-amber-lt);
            color: #b45309;
        }

        /* Scroll horizontal en la tabla — la card no se rompe */
        .lt-table-wrap {
            overflow-x: auto;
        }

        .lt-table-wrap::-webkit-scrollbar {
            height: 5px;
        }

        .lt-table-wrap::-webkit-scrollbar-track {
            background: var(--lt-gray-100);
        }

        .lt-table-wrap::-webkit-scrollbar-thumb {
            background: var(--lt-gray-200);
            border-radius: 10px;
        }

        /* ── TABLA ── */
        .om-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            /* min-width garantiza que la tabla no se aplaste: col label + 4 sem x 2 cols + acum */
            min-width: 520px;
        }

        .om-tbl thead tr {
            background: var(--lt-gray-50);
        }

        .om-tbl thead th {
            padding: 0.6rem 0.85rem;
            text-align: center;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--lt-gray-400);
            white-space: nowrap;
            position: sticky;
            top: 0;
            background: var(--lt-gray-50);
            z-index: 2;
            border-bottom: 1px solid var(--lt-gray-200);
            border-right: 1px solid var(--lt-gray-200);
        }

        .om-tbl thead th:last-child {
            border-right: none;
        }

        .om-tbl thead th.th-label {
            text-align: left;
            min-width: 115px;
        }

        .om-tbl thead tr.om-tr-sub th {
            border-bottom: 1px solid var(--lt-gray-200);
            font-size: 0.6rem;
            padding: 0.38rem 0.85rem;
            color: var(--lt-gray-400);
        }

        .om-tbl thead th.th-acum,
        .om-tbl tbody td.td-om-acum {
            border-left: 2px solid var(--lt-gray-200) !important;
            background: var(--lt-gray-50) !important;
        }

        .om-tbl tbody tr {
            border-bottom: 1px solid var(--lt-gray-100);
            transition: background .15s;
        }

        .om-tbl tbody tr:last-child {
            border-bottom: none;
        }

        .om-tbl tbody tr:hover {
            background: var(--lt-blue-50);
        }

        .om-tbl tbody td {
            padding: 0.58rem 0.85rem;
            color: var(--lt-gray-600);
            vertical-align: middle;
            text-align: center;
            border-right: 1px solid var(--lt-gray-100);
        }

        .om-tbl tbody td:last-child {
            border-right: none;
        }

        .td-om-label {
            font-family: 'DM Mono', monospace;
            font-weight: 800;
            font-size: 0.85rem;
            color: var(--lt-red) !important;
            text-align: left !important;
            background: var(--lt-gray-50) !important;
            border-right: 1px solid var(--lt-gray-200) !important;
        }

        .td-om-cant {
            font-family: 'DM Mono', monospace;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--lt-gray-950) !important;
        }

        .td-om-cant.is-zero {
            color: var(--lt-gray-300) !important;
            font-weight: 400;
        }

        .td-om-pct {
            font-family: 'DM Mono', monospace;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--lt-gray-400) !important;
        }

        .td-om-pct.is-zero {
            color: var(--lt-gray-200) !important;
        }

        .td-sem-sep {
            border-right: 1px solid var(--lt-gray-200) !important;
        }

        .td-om-acum {
            text-align: center !important;
        }

        .td-om-acum .acum-cant {
            display: block;
            font-family: 'DM Mono', monospace;
            font-weight: 800;
            font-size: 0.92rem;
            color: var(--lt-blue-700);
            line-height: 1.2;
        }

        .td-om-acum .acum-pct {
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 0.65rem;
            color: var(--lt-gray-400);
            margin-top: 2px;
        }

        .om-tbl tbody tr.tr-om-total {
            background: var(--lt-gray-50) !important;
            border-top: 2px solid var(--lt-gray-200);
        }

        .om-tbl tbody tr.tr-om-total .td-om-label {
            color: var(--lt-gray-800) !important;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            background: var(--lt-gray-100) !important;
        }

        .om-tbl tbody tr.tr-om-total .td-om-cant {
            color: var(--lt-blue-600) !important;
            font-size: 0.9rem;
        }

        .om-tbl tbody tr.tr-om-total .td-om-acum .acum-cant {
            color: var(--lt-blue-900);
        }

        /* ── HEAT MAP ── */
        .heat-1 {
            background: #ffd5d5 !important;
        }

        .heat-2 {
            background: #ffb3b3 !important;
        }

        .heat-3 {
            background: #ff8a8a !important;
        }

        .heat-4 {
            background: #f95f5f !important;
            color: #fff !important;
        }

        .heat-5 {
            background: #e83232 !important;
            color: #fff !important;
        }

        .heat-4.td-om-cant,
        .heat-5.td-om-cant {
            color: #fff !important;
        }

        .heat-4.td-om-pct,
        .heat-5.td-om-pct {
            color: rgba(255, 255, 255, .75) !important;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .lt-filter-panel {
                flex-direction: column;
                align-items: stretch;
            }

            .lt-filter-actions {
                justify-content: flex-end;
            }

            .om-cats-grid {
                grid-template-columns: 1fr !important;
            }

            .lt-page-title {
                font-size: 1.5rem;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const CAT_ORDER = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];
        const CAT_META = {
            GENERAL: {
                label: 'General',
                iconCls: 'om-icon-general',
                icon: '⊕'
            },
            NOX: {
                label: 'NOX',
                iconCls: 'om-icon-nox',
                icon: 'N'
            },
            TD: {
                label: 'TRIDUREX',
                iconCls: 'om-icon-td',
                icon: 'T'
            },
            DEVABLUE: {
                label: 'DEVABLUE',
                iconCls: 'om-icon-devablue',
                icon: 'D'
            },
            BLANCO: {
                label: 'BLANCOS',
                iconCls: 'om-icon-blanco',
                icon: 'B'
            },
            COLOREADO: {
                label: 'COLOREADO',
                iconCls: 'om-icon-coloreado',
                icon: 'C'
            },
        };

        const WEEKS_PER_VIEW = 4;
        let omData = null;
        let omOffsets = {};

        /* ── INIT ── */
        $(document).ready(() => {
            $.ajax({
                url: "<?php echo route('produccion.lead-time.years'); ?>",
                method: 'GET',
                success({
                    success,
                    years
                }) {
                    const $y = $('#filterYear').empty();
                    const now = new Date();
                    const list = (success && years?.length) ? years : [now.getFullYear()];
                    list.forEach(y => $y.append(
                        `<option value="${y}" ${y == now.getFullYear() ? 'selected' : ''}>${y}</option>`
                    ));
                    loadData();
                },
                error() {
                    $('#filterYear').html(
                        `<option value="${new Date().getFullYear()}" selected>${new Date().getFullYear()}</option>`
                    );
                    loadData();
                }
            });
        });

        /* ── LOAD DATA ── */
        function loadData() {
            const year = $('#filterYear').val();
            if (!year) return;

            const $btn = $('#btnConsultar');
            $btn.addClass('lt-btn-loading').prop('disabled', true);
            $('#btnText').text('Consultando...');
            showLoading();

            $.ajax({
                url: "<?php echo route('produccion.lead-time.objetivo-mas.data'); ?>",
                method: 'GET',
                data: {
                    year
                },
                timeout: 90000,
                success({
                    success,
                    data,
                    filters
                }) {
                    $btn.removeClass('lt-btn-loading').prop('disabled', false);
                    $('#btnText').text('Consultar');
                    if (success && data) {
                        omData = data;
                        // Inicializar offsets apuntando a las semanas mas recientes
                        const total = (data.semanas || []).length;
                        const initOffset = Math.max(0, total - WEEKS_PER_VIEW);
                        omOffsets['GENERAL'] = initOffset;
                        CAT_ORDER.forEach(c => {
                            omOffsets[c] = initOffset;
                        });
                        renderDashboard(data, filters);
                    } else {
                        renderEmpty('Sin datos para este año');
                    }
                },
                error() {
                    $btn.removeClass('lt-btn-loading').prop('disabled', false);
                    $('#btnText').text('Consultar');
                    renderEmpty('Error de conexión al servidor');
                }
            });
        }

        function showLoading() {
            $('#mainContent').html(
                `<div class="lt-loading-state"><div class="lt-spinner"></div><p>Consultando Lead Time Objetivo +...</p></div>`
            );
        }

        /* ── RENDER DASHBOARD ── */
        function renderDashboard(data, filters) {
            const semanas = data.semanas || [];
            const general = data.general || {};
            const categorias = data.categorias || {};

            if (!semanas.length) {
                renderEmpty('Sin órdenes fuera de tiempo para este año');
                return;
            }

            let html = '';

            /* GENERAL — ancho completo */
            html += `
                <div class="om-section lt-fade-up" id="section-GENERAL">
                    <div class="lt-table-card">
                        ${buildTableHead('GENERAL', filters.year, general.totales, null, semanas)}
                        <div id="tbody-GENERAL">${buildTableInner(semanas, general, 'GENERAL')}</div>
                    </div>
                </div>
            `;

            /* CATEGORIAS — grid responsivo */
            html += `<div class="om-cats-grid">`;
            CAT_ORDER.forEach((cat, i) => {
                const info = categorias[cat];
                if (!info) return;
                html += `
                    <div class="lt-fade-up" style="animation-delay:${0.06 + i * 0.07}s;" id="section-${cat}">
                        <div class="lt-table-card">
                            ${buildTableHead(cat, filters.year, info.totales, info.nombre, semanas)}
                            <div id="tbody-${cat}">${buildTableInner(semanas, info, cat)}</div>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;

            $('#mainContent').html(html);
        }

        /* ── CARD HEADER ── */
        function buildTableHead(key, year, totales, nombreDisplay, semanas) {
            const meta = CAT_META[key] || {
                label: key,
                iconCls: 'om-icon-general',
                icon: '?'
            };
            const label = nombreDisplay || meta.label;
            const total = totales?.acum?.cant ?? 0;
            const offset = omOffsets[key] ?? 0;
            const maxSem = (semanas || []).length;
            const end = Math.min(offset + WEEKS_PER_VIEW, maxSem);
            const visSems = semanas.slice(offset, end);
            const canPrev = offset > 0;
            const canNext = end < maxSem;

            const badgeTxt = visSems.length ?
                `Sem ${visSems[0].num} – Sem ${visSems[visSems.length - 1].num}` :
                '';

            return `
                <div class="lt-table-head">
                    <div class="lt-table-title">
                        <div class="om-cat-icon ${meta.iconCls}">${meta.icon}</div>
                        <div>
                            <span>${label}</span>
                            <span style="display:block;font-size:.7rem;font-weight:400;color:var(--lt-gray-400);margin-top:1px;">
                                Órdenes fuera de tiempo — ${year}
                            </span>
                        </div>
                        ${total > 0 ? `<span class="lt-table-count">${total.toLocaleString()} atrasadas</span>` : ''}
                    </div>
                    <div class="om-nav-group">
                        <span class="om-period-badge" id="badge-${key}">${badgeTxt}</span>
                        <button class="om-btn-nav" onclick="navTable('${key}', -1)" ${!canPrev ? 'disabled' : ''} title="Semanas anteriores">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button class="om-btn-nav" onclick="navTable('${key}', 1)" ${!canNext ? 'disabled' : ''} title="Semanas siguientes">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
            `;
        }

        /* ── NAVEGACION ── */
        function navTable(key, dir) {
            if (!omData) return;
            const semanas = omData.semanas || [];
            const maxSem = semanas.length;
            const cur = omOffsets[key] ?? 0;
            omOffsets[key] = Math.max(0, Math.min(cur + dir, maxSem - WEEKS_PER_VIEW));

            const tableData = key === 'GENERAL' ? omData.general : (omData.categorias?.[key] || {});
            $(`#tbody-${key}`).html(buildTableInner(semanas, tableData, key));

            const offset = omOffsets[key];
            const end = Math.min(offset + WEEKS_PER_VIEW, maxSem);
            const visSems = semanas.slice(offset, end);
            const canPrev = offset > 0;
            const canNext = end < maxSem;

            $(`#badge-${key}`).text(
                visSems.length ? `Sem ${visSems[0].num} – Sem ${visSems[visSems.length - 1].num}` : ''
            );

            const $nav = $(`#section-${key} .lt-table-head .om-nav-group`);
            $nav.find('button').first().prop('disabled', !canPrev);
            $nav.find('button').last().prop('disabled', !canNext);
        }

        /* ── TABLE INNER ── */
        function buildTableInner(semanas, tableData, key) {
            const filas = tableData.filas || [];
            const totales = tableData.totales || {};
            const offset = omOffsets[key] ?? 0;
            const end = Math.min(offset + WEEKS_PER_VIEW, semanas.length);
            const visSems = semanas.slice(offset, end);

            if (!filas.length) {
                return `<div style="padding:2.5rem;text-align:center;color:var(--lt-gray-400);font-size:.83rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                         style="display:block;margin:0 auto .6rem;opacity:.35;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>Sin órdenes atrasadas en este período</div>`;
            }

            /* Maximo por semana visible para heat map */
            const maxBySem = {};
            visSems.forEach(s => {
                maxBySem[s.num] = 0;
            });
            filas.forEach(f => {
                visSems.forEach(s => {
                    const v = f.semanas?.[s.num]?.cant ?? 0;
                    if (v > maxBySem[s.num]) maxBySem[s.num] = v;
                });
            });

            /* THEAD */
            let thead = `<thead><tr>`;
            thead += `<th class="th-label" rowspan="2">Días de<br>Atraso</th>`;
            visSems.forEach(s => {
                thead += `<th colspan="2" style="text-align:center;">
                    Semana ${s.num}
                    <span style="display:block;font-size:.57rem;font-weight:500;text-transform:none;letter-spacing:0;color:var(--lt-gray-400);">${s.rango}</span>
                </th>`;
            });
            thead += `<th class="th-acum" rowspan="2" style="text-align:center;">Acumulado</th></tr>`;
            thead += `<tr class="om-tr-sub">`;
            visSems.forEach((s, idx) => {
                const isLast = idx === visSems.length - 1;
                thead +=
                    `<th>Total</th><th${isLast ? ' style="border-right:1px solid var(--lt-gray-200);"' : ''}>%</th>`;
            });
            thead += `</tr></thead>`;

            /* TBODY */
            let tbody = `<tbody>`;
            filas.forEach(f => {
                tbody += `<tr><td class="td-om-label">${f.label}</td>`;
                visSems.forEach((s, idx) => {
                    const cell = f.semanas?.[s.num] ?? {
                        cant: 0,
                        pct: 0
                    };
                    const cant = cell.cant ?? 0;
                    const pct = cell.pct ?? 0;
                    const isLast = idx === visSems.length - 1;
                    let heat = '';
                    if (cant > 0 && maxBySem[s.num] > 0) {
                        const r = cant / maxBySem[s.num];
                        heat = r >= .8 ? 'heat-5' : r >= .6 ? 'heat-4' : r >= .4 ? 'heat-3' : r >= .2 ?
                            'heat-2' : 'heat-1';
                    }
                    tbody +=
                        `<td class="td-om-cant ${cant === 0 ? 'is-zero' : ''} ${heat}">${cant > 0 ? cant.toLocaleString() : '—'}</td>`;
                    tbody +=
                        `<td class="td-om-pct  ${pct  === 0 ? 'is-zero' : ''} ${heat}${isLast ? ' td-sem-sep' : ''}">${pct > 0 ? pct + '%' : '—'}</td>`;
                });
                const acum = f.acum ?? {
                    cant: 0,
                    pct: 0
                };
                tbody += `<td class="td-om-acum">
                    <span class="acum-cant">${acum.cant > 0 ? acum.cant.toLocaleString() : '—'}</span>
                    <span class="acum-pct">${acum.pct > 0 ? acum.pct + '%' : ''}</span>
                </td></tr>`;
            });

            /* Fila TOTAL */
            if (totales?.semanas) {
                tbody += `<tr class="tr-om-total"><td class="td-om-label">TOTAL</td>`;
                visSems.forEach((s, idx) => {
                    const cant = totales.semanas?.[s.num]?.cant ?? 0;
                    const isLast = idx === visSems.length - 1;
                    tbody +=
                        `<td class="td-om-cant ${cant === 0 ? 'is-zero' : ''}">${cant > 0 ? cant.toLocaleString() : '—'}</td>`;
                    tbody +=
                        `<td class="td-om-pct${isLast ? ' td-sem-sep' : ''}" style="color:var(--lt-gray-300)!important;">100%</td>`;
                });
                const acumT = totales.acum ?? {
                    cant: 0
                };
                tbody += `<td class="td-om-acum">
                    <span class="acum-cant" style="color:var(--lt-blue-900);">${acumT.cant > 0 ? acumT.cant.toLocaleString() : '—'}</span>
                    <span class="acum-pct">100%</span>
                </td></tr>`;
            }
            tbody += `</tbody>`;

            return `<div class="lt-table-wrap"><table class="om-tbl">${thead}${tbody}</table></div>`;
        }

        /* ── EMPTY / CACHE ── */
        function renderEmpty(msg) {
            $('#mainContent').html(
                `<div class="lt-empty"><div class="lt-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8892a8" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div><h5>${msg}</h5><p>Selecciona otro año</p></div>`
            );
        }

        function clearCache() {
            $.ajax({
                url: "<?php echo route('produccion.lead-time.clear-cache'); ?>",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Caché limpiado',
                        text: 'Los datos se recargarán desde Google Sheets',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadData();
                },
                error() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo limpiar el caché'
                    });
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/comercial/lead-time-objetivo-mas.blade.php ENDPATH**/ ?>