@extends('layouts.app')

@section('title', 'KPI Semanal')

@section('content')
    <div class="content-wrapper kw-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- PAGE HEADER --}}
                <div class="kw-page-header">
                    <div class="kw-page-header-left">
                        <h1 class="kw-page-title">KPI: On-Time Production</h1>
                        <p class="kw-page-subtitle">Comparativo semanal y mensual por categoría</p>
                    </div>
                    <div class="kw-page-header-right">
                        <div class="kw-legend">
                            <span class="kw-legend-dot green"></span><span>≥ 95% Verde</span>
                            <span class="kw-legend-dot yellow"></span><span>89.90 – 94.99% Amarillo</span>
                            <span class="kw-legend-dot red"></span><span>≤ 89.89% Rojo</span>
                        </div>
                    </div>
                </div>

                {{-- FILTER PANEL --}}
                <div class="kw-filter-panel">
                    <div class="kw-filter-group">
                        <div class="kw-filter-item">
                            <label class="kw-filter-label">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Año
                            </label>
                            <select class="kw-select" id="kwYear"></select>
                        </div>
                        <div class="kw-filter-item">
                            <label class="kw-filter-label">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Mes
                            </label>
                            <select class="kw-select" id="kwMonth">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                    <div class="kw-filter-actions">
                        <button class="kw-btn kw-btn-ghost" onclick="kwClearCache()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 1 0 .49-3" />
                            </svg>
                            Limpiar caché
                        </button>
                        <button class="kw-btn kw-btn-primary" id="kwBtnConsultar" onclick="kwLoadData()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" id="kwBtnIcon">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <span id="kwBtnText">Consultar</span>
                        </button>
                    </div>
                </div>

                {{-- MAIN CONTENT --}}
                <div id="kwMainContent">
                    <div class="kw-loading-state">
                        <div class="kw-spinner"></div>
                        <p>Cargando datos...</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500;700&display=swap');

        :root {
            --kw-blue-950: #04122e;
            --kw-blue-900: #0a1f4d;
            --kw-blue-800: #0f2d6e;
            --kw-blue-700: #1a3f94;
            --kw-blue-600: #2252b8;
            --kw-blue-500: #2d65d8;
            --kw-blue-400: #5b87e8;
            --kw-blue-300: #93b5f3;
            --kw-blue-100: #dce8fb;
            --kw-blue-50: #eef4fd;

            --kw-green: #10b981;
            --kw-green-lt: #d1fae5;
            --kw-green-dk: #065f46;
            --kw-amber: #f59e0b;
            --kw-amber-lt: #fef3c7;
            --kw-amber-dk: #92400e;
            --kw-red: #ef4444;
            --kw-red-lt: #fee2e2;
            --kw-red-dk: #991b1b;

            --kw-gray-950: #0c0e13;
            --kw-gray-800: #1e2130;
            --kw-gray-600: #4b5573;
            --kw-gray-400: #8892a8;
            --kw-gray-200: #e4e8f0;
            --kw-gray-100: #f2f4f8;
            --kw-gray-50: #f8f9fc;
            --kw-white: #ffffff;

            --kw-shadow-sm: 0 1px 3px rgba(4, 18, 46, .06), 0 2px 8px rgba(4, 18, 46, .04);
            --kw-shadow-md: 0 4px 16px rgba(4, 18, 46, .08), 0 1px 3px rgba(4, 18, 46, .05);
            --kw-shadow-lg: 0 8px 32px rgba(4, 18, 46, .12), 0 2px 8px rgba(4, 18, 46, .06);
            --kw-shadow-blue: 0 4px 20px rgba(45, 101, 216, .25);

            --kw-radius-sm: 8px;
            --kw-radius: 12px;
            --kw-radius-lg: 18px;
            --kw-radius-xl: 24px;
        }

        .kw-wrapper * {
            box-sizing: border-box;
        }

        .kw-wrapper,
        .kw-wrapper * {
            font-family: 'DM Sans', sans-serif;
        }

        /* ── PAGE HEADER ── */
        .kw-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            padding-top: 0.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .kw-page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--kw-gray-950);
            letter-spacing: -0.04em;
            line-height: 1.1;
            margin: 0 0 0.25rem;
        }

        .kw-page-subtitle {
            font-size: 0.875rem;
            color: var(--kw-gray-400);
            font-weight: 400;
            margin: 0;
        }

        .kw-legend {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius);
            padding: 0.6rem 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--kw-gray-600);
            flex-wrap: wrap;
            box-shadow: var(--kw-shadow-sm);
        }

        .kw-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .kw-legend-dot.green {
            background: var(--kw-green);
        }

        .kw-legend-dot.yellow {
            background: var(--kw-amber);
        }

        .kw-legend-dot.red {
            background: var(--kw-red);
        }

        /* ── FILTER PANEL ── */
        .kw-filter-panel {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-lg);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--kw-shadow-sm);
            flex-wrap: wrap;
        }

        .kw-filter-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .kw-filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            min-width: 160px;
        }

        .kw-filter-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--kw-gray-600);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .kw-select {
            appearance: none;
            background: var(--kw-gray-50) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238892a8' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
            border: 1.5px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-sm);
            padding: 0.6rem 2.2rem 0.6rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--kw-gray-950);
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }

        .kw-select:focus {
            outline: none;
            border-color: var(--kw-blue-500);
            box-shadow: 0 0 0 3px rgba(45, 101, 216, .12);
        }

        .kw-select:hover {
            border-color: var(--kw-blue-300);
        }

        .kw-filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* ── BUTTONS ── */
        .kw-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0.6rem 1.2rem;
            border-radius: var(--kw-radius-sm);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all .18s cubic-bezier(.4, 0, .2, 1);
            white-space: nowrap;
        }

        .kw-btn:active {
            transform: translateY(1px);
        }

        .kw-btn-primary {
            background: linear-gradient(135deg, var(--kw-blue-600) 0%, var(--kw-blue-800) 100%);
            color: white;
            box-shadow: var(--kw-shadow-blue);
        }

        .kw-btn-primary:hover {
            box-shadow: 0 6px 24px rgba(45, 101, 216, .35);
            transform: translateY(-1px);
        }

        .kw-btn-ghost {
            background: transparent;
            color: var(--kw-gray-600);
            border: 1.5px solid var(--kw-gray-200);
        }

        .kw-btn-ghost:hover {
            background: var(--kw-gray-50);
            color: var(--kw-gray-950);
            border-color: var(--kw-gray-400);
        }

        .kw-btn-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--kw-white);
            border: 1.5px solid var(--kw-gray-200);
            color: var(--kw-gray-600);
            cursor: pointer;
            transition: all .18s;
            font-family: 'DM Sans', sans-serif;
        }

        .kw-btn-nav:hover:not(:disabled) {
            background: var(--kw-blue-50);
            border-color: var(--kw-blue-300);
            color: var(--kw-blue-600);
        }

        .kw-btn-nav:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .kw-btn-loading #kwBtnIcon {
            animation: kwSpin .9s linear infinite;
        }

        @keyframes kwSpin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ── LOADING ── */
        .kw-loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5rem 2rem;
            gap: 1rem;
            color: var(--kw-gray-400);
            font-size: 0.875rem;
        }

        .kw-spinner {
            width: 36px;
            height: 36px;
            border: 3px solid var(--kw-gray-200);
            border-top-color: var(--kw-blue-500);
            border-radius: 50%;
            animation: kwSpin .7s linear infinite;
        }

        /* ── MINI STAT CARDS (top) ── */
        .kw-mini-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .kw-mini-card {
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-lg);
            padding: 1.25rem 1.4rem;
            box-shadow: var(--kw-shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            position: relative;
            overflow: hidden;
            transition: transform .22s, box-shadow .22s;
        }

        .kw-mini-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--kw-shadow-md);
        }

        .kw-mini-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .kw-mini-card.green::before {
            background: linear-gradient(90deg, var(--kw-green), #34d399);
        }

        .kw-mini-card.amber::before {
            background: linear-gradient(90deg, var(--kw-amber), #fcd34d);
        }

        .kw-mini-card.red::before {
            background: linear-gradient(90deg, var(--kw-red), #f87171);
        }

        .kw-mini-card.blue::before {
            background: linear-gradient(90deg, var(--kw-blue-500), var(--kw-blue-700));
        }

        .kw-mini-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--kw-gray-400);
        }

        .kw-mini-val {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
            font-family: 'DM Mono', monospace;
        }

        .kw-mini-val.green {
            color: var(--kw-green);
        }

        .kw-mini-val.amber {
            color: var(--kw-amber);
        }

        .kw-mini-val.red {
            color: var(--kw-red);
        }

        .kw-mini-val.blue {
            color: var(--kw-blue-600);
        }

        .kw-mini-sub {
            font-size: 0.72rem;
            color: var(--kw-gray-400);
            font-weight: 500;
        }

        /* ── TREND SPARKLINE SECTION ── */
        .kw-trend-section {
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-lg);
            box-shadow: var(--kw-shadow-sm);
            padding: 1.4rem 1.6rem;
            margin-bottom: 1.75rem;
            overflow: hidden;
        }

        .kw-trend-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .kw-trend-title {
            font-size: 0.93rem;
            font-weight: 700;
            color: var(--kw-gray-950);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kw-trend-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kw-trend-period {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--kw-gray-600);
            background: var(--kw-gray-50);
            border: 1px solid var(--kw-gray-200);
            border-radius: 8px;
            padding: 0.3rem 0.75rem;
            font-family: 'DM Mono', monospace;
            min-width: 120px;
            text-align: center;
        }

        .kw-chart-area {
            width: 100%;
            overflow-x: auto;
        }

        .kw-chart-area::-webkit-scrollbar {
            height: 4px;
        }

        .kw-chart-area::-webkit-scrollbar-track {
            background: var(--kw-gray-100);
            border-radius: 10px;
        }

        .kw-chart-area::-webkit-scrollbar-thumb {
            background: var(--kw-gray-200);
            border-radius: 10px;
        }

        canvas#kwTrendChart {
            display: block;
            min-width: 600px;
            height: 300px !important;
        }

        /* ── TABLE SECTION ── */
        .kw-tables-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .kw-table-card {
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-lg);
            box-shadow: var(--kw-shadow-sm);
            overflow: hidden;
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }

        .kw-table-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--kw-shadow-md);
            border-color: var(--kw-blue-100);
        }

        .kw-table-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.3rem;
            border-bottom: 1px solid var(--kw-gray-100);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .kw-table-card-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--kw-gray-950);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kw-table-nav-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .kw-table-period-badge {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--kw-blue-700);
            background: var(--kw-blue-50);
            border: 1px solid var(--kw-blue-100);
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-family: 'DM Mono', monospace;
            white-space: nowrap;
        }

        /* ── TABLE BASE ── */
        .kw-tbl-wrap {
            overflow-x: auto;
        }

        .kw-tbl-wrap::-webkit-scrollbar {
            height: 4px;
        }

        .kw-tbl-wrap::-webkit-scrollbar-track {
            background: var(--kw-gray-100);
        }

        .kw-tbl-wrap::-webkit-scrollbar-thumb {
            background: var(--kw-gray-200);
            border-radius: 10px;
        }

        .kw-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.79rem;
            min-width: 380px;
        }

        .kw-tbl thead tr {
            background: var(--kw-gray-50);
            border-bottom: 1px solid var(--kw-gray-200);
        }

        .kw-tbl thead th {
            padding: 0.55rem 0.8rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--kw-gray-400);
            white-space: nowrap;
            text-align: right;
        }

        .kw-tbl thead th:first-child {
            text-align: left;
        }

        .kw-tbl-th-week {
            text-align: center !important;
            line-height: 1.5;
            vertical-align: bottom;
            padding-bottom: 0.65rem !important;
        }

        .kw-tbl-th-week span:first-child {
            display: block;
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--kw-gray-950);
            letter-spacing: 0.01em;
        }

        .kw-tbl-th-week span:last-child {
            display: block;
            font-size: 0.6rem;
            font-weight: 500;
            color: var(--kw-gray-400);
            letter-spacing: 0.02em;
            margin-top: 1px;
        }

        .kw-tbl tbody tr {
            border-bottom: 1px solid var(--kw-gray-100);
            transition: background .13s;
        }

        .kw-tbl tbody tr:last-child {
            border-bottom: none;
        }

        .kw-tbl tbody tr.kw-row-kpi {
            background: var(--kw-blue-950);
            border-bottom: 2px solid var(--kw-blue-700);
        }

        .kw-tbl tbody tr.kw-row-kpi td {
            color: white !important;
            font-weight: 800;
            font-size: 0.82rem;
        }

        .kw-tbl tbody tr:not(.kw-row-kpi):hover {
            background: var(--kw-blue-50);
        }

        .kw-tbl tbody td {
            padding: 0.55rem 0.8rem;
            color: var(--kw-gray-600);
            vertical-align: middle;
            text-align: right;
            font-family: 'DM Mono', monospace;
            font-size: 0.78rem;
        }

        .kw-tbl tbody td:first-child {
            text-align: left;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            color: var(--kw-gray-950);
            font-size: 0.79rem;
        }

        /* KPI cell color chips */
        .kw-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: 'DM Mono', monospace;
            font-weight: 800;
            font-size: 0.78rem;
            min-width: 68px;
        }

        .kw-chip.green {
            background: var(--kw-green-lt);
            color: var(--kw-green-dk);
        }

        .kw-chip.amber {
            background: var(--kw-amber-lt);
            color: var(--kw-amber-dk);
        }

        .kw-chip.red {
            background: var(--kw-red-lt);
            color: var(--kw-red-dk);
        }

        .kw-chip.none {
            background: var(--kw-gray-100);
            color: var(--kw-gray-400);
        }

        /* KPI row chips (inverted — dark bg) */
        .kw-row-kpi .kw-chip.green {
            background: #065f46;
            color: #6ee7b7;
        }

        .kw-row-kpi .kw-chip.amber {
            background: #78350f;
            color: #fde68a;
        }

        .kw-row-kpi .kw-chip.red {
            background: #7f1d1d;
            color: #fca5a5;
        }

        .kw-row-kpi .kw-chip.none {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.4);
        }

        /* ── HEAT MAP SECTION ── */
        .kw-heat-section {
            background: var(--kw-white);
            border: 1px solid var(--kw-gray-200);
            border-radius: var(--kw-radius-lg);
            box-shadow: var(--kw-shadow-sm);
            overflow: hidden;
            margin-bottom: 1.75rem;
        }

        .kw-heat-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.3rem;
            border-bottom: 1px solid var(--kw-gray-100);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .kw-heat-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--kw-gray-950);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kw-heat-body {
            padding: 1.2rem 1.3rem;
            overflow-x: auto;
        }

        .kw-heat-legend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kw-heat-leg {
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.68rem;
            font-weight: 700;
            font-family: 'DM Mono', monospace;
        }

        .kw-heat-leg.green {
            background: var(--kw-green-lt);
            color: var(--kw-green-dk);
        }

        .kw-heat-leg.amber {
            background: var(--kw-amber-lt);
            color: var(--kw-amber-dk);
        }

        .kw-heat-leg.red {
            background: var(--kw-red-lt);
            color: var(--kw-red-dk);
        }

        /* ── HEAT MAP — tabla scrollable ── */
        .kw-heat-scroll-wrap {
            overflow-x: auto;
            overflow-y: visible;
        }

        .kw-heat-scroll-wrap::-webkit-scrollbar {
            height: 5px;
        }

        .kw-heat-scroll-wrap::-webkit-scrollbar-track {
            background: var(--kw-gray-100);
            border-radius: 10px;
        }

        .kw-heat-scroll-wrap::-webkit-scrollbar-thumb {
            background: var(--kw-gray-200);
            border-radius: 10px;
        }

        .kw-heat-scroll-wrap::-webkit-scrollbar-thumb:hover {
            background: var(--kw-gray-400);
        }

        .kw-heat-tbl {
            border-collapse: separate;
            border-spacing: 5px;
            min-width: max-content;
        }

        /* Header columnas (semanas) */
        .kw-heat-th-label {
            min-width: 100px;
            width: 100px;
            text-align: right;
            padding-right: 0.5rem;
        }

        .kw-heat-th-col {
            min-width: 72px;
            width: 72px;
            text-align: center;
            font-size: 0.62rem;
            font-weight: 700;
            color: var(--kw-gray-400);
            letter-spacing: 0.04em;
            padding-bottom: 6px;
            line-height: 1.4;
            vertical-align: bottom;
            white-space: nowrap;
        }

        /* Label izquierdo de cada fila */
        .kw-heat-td-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--kw-gray-600);
            text-align: right;
            padding-right: 0.75rem;
            white-space: nowrap;
            vertical-align: middle;
        }

        .kw-heat-row-kpi .kw-heat-td-label {
            font-weight: 800;
            color: var(--kw-gray-950);
        }

        .kw-heat-kpi-badge {
            display: inline-block;
            background: var(--kw-blue-950);
            color: white;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 2px 8px;
            border-radius: 5px;
            font-family: 'DM Mono', monospace;
        }

        /* Celda de dato */
        .kw-heat-td {
            padding: 0;
            vertical-align: middle;
        }

        .kw-heat-cell {
            width: 72px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Mono', monospace;
            font-size: 0.73rem;
            font-weight: 700;
            cursor: default;
            transition: transform .15s, box-shadow .15s, filter .15s;
            position: relative;
            white-space: nowrap;
        }

        .kw-heat-cell:hover {
            transform: scale(1.12);
            z-index: 10;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .18);
            filter: brightness(1.05);
        }

        .kw-heat-cell.kpi {
            height: 44px;
            font-size: 0.78rem;
            font-weight: 800;
            border-radius: 9px;
        }

        .kw-heat-cell.green {
            background: var(--kw-green-lt);
            color: var(--kw-green-dk);
        }

        .kw-heat-cell.amber {
            background: var(--kw-amber-lt);
            color: var(--kw-amber-dk);
        }

        .kw-heat-cell.red {
            background: var(--kw-red-lt);
            color: var(--kw-red-dk);
        }

        .kw-heat-cell.none {
            background: var(--kw-gray-100);
            color: var(--kw-gray-400);
        }

        /* Fila KPI: celdas ligeramente más saturadas */
        .kw-heat-row-kpi .kw-heat-cell.green {
            background: #a7f3d0;
            color: var(--kw-green-dk);
        }

        .kw-heat-row-kpi .kw-heat-cell.amber {
            background: #fde68a;
            color: var(--kw-amber-dk);
        }

        .kw-heat-row-kpi .kw-heat-cell.red {
            background: #fca5a5;
            color: var(--kw-red-dk);
        }

        /* ── EMPTY ── */
        .kw-empty {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--kw-gray-400);
        }

        .kw-empty-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: var(--kw-gray-100);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kw-empty h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--kw-gray-600);
            margin-bottom: .35rem;
        }

        .kw-empty p {
            font-size: 0.83rem;
            margin: 0;
        }

        /* ── ANIMATIONS ── */
        .kw-fade-up {
            opacity: 0;
            transform: translateY(16px);
            animation: kwFadeUp .42s ease forwards;
        }

        @keyframes kwFadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .kw-fade-up:nth-child(1) {
            animation-delay: 0s;
        }

        .kw-fade-up:nth-child(2) {
            animation-delay: .07s;
        }

        .kw-fade-up:nth-child(3) {
            animation-delay: .14s;
        }

        .kw-fade-up:nth-child(4) {
            animation-delay: .21s;
        }

        .kw-fade-up:nth-child(5) {
            animation-delay: .28s;
        }

        .kw-fade-up:nth-child(6) {
            animation-delay: .35s;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .kw-tables-row {
                grid-template-columns: 1fr;
            }

            .kw-mini-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .kw-mini-grid {
                grid-template-columns: 1fr 1fr;
            }

            .kw-filter-panel {
                flex-direction: column;
                align-items: stretch;
            }

            .kw-page-title {
                font-size: 1.5rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /* ═══════════════════════════════════════════════════════
       KPI SEMANAL — Estado global
    ═══════════════════════════════════════════════════════ */
        const KW_MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        const KW_CATS = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        let kwRawData = null; // data completa del backend
        let kwWeekOffset = 0; // desplazamiento semanas (tabla semanal)
        let kwMonthOffset = 0; // desplazamiento meses (tabla mensual)
        let kwWeeksPerView = 6; // columnas visibles en tabla semanal
        let kwMonthsPerView = 4; // columnas visibles en tabla mensual
        let kwTrendChart = null;
        let kwCurrentYear = null;
        let kwCurrentMonth = null;

        /* ── INIT ── */
        $(document).ready(() => {
            const now = new Date();
            kwCurrentMonth = now.getMonth() + 1;
            kwCurrentYear = now.getFullYear();
            $('#kwMonth').val(kwCurrentMonth);

            // Poblar años
            $.ajax({
                url: "{{ route('comercial.lead-time.years') }}",
                method: 'GET',
                success({
                    success,
                    years
                }) {
                    const $y = $('#kwYear').empty();
                    const list = (success && years?.length) ? years : [now.getFullYear()];
                    list.forEach(y => $y.append(
                        `<option value="${y}" ${y == now.getFullYear() ? 'selected':''}>${y}</option>`
                        ));
                    kwLoadData();
                },
                error() {
                    $('#kwYear').html(
                        `<option value="${now.getFullYear()}" selected>${now.getFullYear()}</option>`);
                    kwLoadData();
                }
            });
        });

        /* ── LOAD DATA ── */
        function kwLoadData() {
            kwCurrentYear = $('#kwYear').val();
            kwCurrentMonth = $('#kwMonth').val();
            if (!kwCurrentYear || !kwCurrentMonth) return;

            const $btn = $('#kwBtnConsultar');
            $btn.addClass('kw-btn-loading').prop('disabled', true);
            $('#kwBtnText').text('Consultando...');
            $('#kwMainContent').html(
                `<div class="kw-loading-state"><div class="kw-spinner"></div><p>Consultando datos KPI semanal...</p></div>`
                );

            $.ajax({
                url: "{{ route('comercial.lead-time.semanal-data') }}",
                method: 'GET',
                data: {
                    year: kwCurrentYear,
                    month: kwCurrentMonth
                },
                timeout: 60000,
                success({
                    success,
                    data,
                    filters
                }) {
                    $btn.removeClass('kw-btn-loading').prop('disabled', false);
                    $('#kwBtnText').text('Consultar');
                    if (success && data) {
                        kwRawData = kwBuildData(data, filters);
                        kwWeekOffset = Math.max(0, (kwRawData.semanas.length) - kwWeeksPerView);
                        kwMonthOffset = Math.max(0, (kwRawData.meses.length) - kwMonthsPerView);
                        kwRenderAll();
                    } else {
                        kwRenderEmpty('Sin datos para este período');
                    }
                },
                error() {
                    $btn.removeClass('kw-btn-loading').prop('disabled', false);
                    $('#kwBtnText').text('Consultar');
                    // Para demo: generar datos de ejemplo
                    kwRawData = kwGenerateDemoData(parseInt(kwCurrentYear), parseInt(kwCurrentMonth));
                    kwWeekOffset = Math.max(0, kwRawData.semanas.length - kwWeeksPerView);
                    kwMonthOffset = Math.max(0, kwRawData.meses.length - kwMonthsPerView);
                    kwRenderAll();
                }
            });
        }

        /* ── BUILD DATA desde respuesta del backend ── */
        /* Adapta la respuesta real de lead-time.data al formato interno.
           Si tu backend ya devuelve datos semanales/mensuales, reemplaza esta función. */
        function kwBuildData(data, filters) {
            // Fallback: si el backend no da semanas, usa demo
            if (!data.semanas) return kwGenerateDemoData(parseInt(filters.year), parseInt(filters.month));
            return data;
        }

        /* ── DEMO DATA GENERATOR ── */
        function kwGenerateDemoData(year, month) {
            // Calcular semanas del año hasta el mes actual
            const daysInMonth = new Date(year, month, 0).getDate();
            const firstDay = new Date(year, 0, 1);
            const endOfMonth = new Date(year, month - 1, daysInMonth);
            const totalWeeks = Math.ceil((endOfMonth - firstDay) / (7 * 24 * 60 * 60 * 1000)) + 1;
            const startWeek = Math.max(1, totalWeeks - 11); // últimas ~12 semanas

            const semanas = [];
            for (let w = startWeek; w <= totalWeeks; w++) {
                semanas.push({
                    label: `Sem ${w}`,
                    num: w
                });
            }

            const meses = [];
            for (let m = Math.max(1, month - 5); m <= month; m++) {
                meses.push({
                    label: KW_MESES[m].substring(0, 3),
                    num: m,
                    year
                });
            }

            function rnd(base, spread = 12) {
                return Math.max(60, Math.min(100, +(base + (Math.random() - 0.5) * spread).toFixed(2)));
            }

            const cats = KW_CATS;
            const bases = {
                NOX: 91,
                TD: 80,
                DEVABLUE: 84,
                BLANCO: 90,
                COLOREADO: 88
            };

            // Datos semanales por categoría
            const weekData = {};
            const weekKpi = {};
            cats.forEach(cat => {
                weekData[cat] = semanas.map(() => rnd(bases[cat]));
            });
            semanas.forEach((_, i) => {
                const vals = cats.map(c => weekData[c][i]);
                weekKpi[i] = +(vals.reduce((a, b) => a + b) / vals.length).toFixed(2);
            });

            // Datos mensuales por categoría
            const monthData = {};
            const monthKpi = {};
            cats.forEach(cat => {
                monthData[cat] = meses.map(() => rnd(bases[cat], 8));
            });
            meses.forEach((_, i) => {
                const vals = cats.map(c => monthData[c][i]);
                monthKpi[i] = +(vals.reduce((a, b) => a + b) / vals.length).toFixed(2);
            });

            return {
                semanas,
                meses,
                weekData,
                weekKpi,
                monthData,
                monthKpi,
                cats
            };
        }

        /* ── COLOR HELPER ── */
        function kwChipClass(val) {
            if (val === null || val === undefined || val === '—') return 'none';
            const n = parseFloat(val);
            if (isNaN(n)) return 'none';
            if (n >= 95) return 'green';
            if (n >= 89.90) return 'amber';
            return 'red';
        }

        /* ── RENDER ALL ── */
        function kwRenderAll() {
            const d = kwRawData;
            if (!d) return;

            // Calcular métricas generales
            const allKpiWeek = Object.values(d.weekKpi);
            const lastKpi = allKpiWeek[allKpiWeek.length - 1] ?? 0;
            const avgKpi = allKpiWeek.length ? +(allKpiWeek.reduce((a, b) => a + b, 0) / allKpiWeek.length).toFixed(2) : 0;
            const peakKpi = allKpiWeek.length ? Math.max(...allKpiWeek).toFixed(2) : 0;
            const lowKpi = allKpiWeek.length ? Math.min(...allKpiWeek).toFixed(2) : 0;

            const clsLast = kwChipClass(lastKpi);
            const clsAvg = kwChipClass(avgKpi);

            let html = '';



            /* ─── TREND CHART ─── */
            html += `
    <div class="kw-trend-section kw-fade-up">
        <div class="kw-trend-header">
            <div class="kw-trend-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--kw-blue-500)" stroke-width="2.5">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Tendencia KPI General — Semanas
            </div>
            <div class="kw-trend-nav">
                <span class="kw-trend-period" id="kwTrendPeriod">${KW_MESES[parseInt(kwCurrentMonth)]} ${kwCurrentYear}</span>
            </div>
        </div>
        <div class="kw-chart-area">
            <canvas id="kwTrendChart"></canvas>
        </div>
    </div>`;

            /* ─── TABLAS ─── */
            html += `<div class="kw-tables-row kw-fade-up">`;
            html += kwRenderWeekTable();
            html += kwRenderMonthTable();
            html += `</div>`;

            /* ─── HEAT MAP ─── */
            html += kwRenderHeatMap();

            $('#kwMainContent').html(html);

            // Render chart después de que el DOM esté listo
            setTimeout(() => {
                kwRenderTrendChart();
            }, 100);
        }

        /* ── FORMATO HEADER SEMANA (global) ── */
        function fmtWeekHeader(s) {
            if (!s.inicio) return `<span style="font-weight:700;">${s.label}</span>`;
            const dt = new Date(s.inicio + 'T00:00:00');
            const day = dt.getDate();
            const mon = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][dt.getMonth()];
            return `<span style="font-weight:800;">${s.label}</span><br><span style="font-weight:400;opacity:.65;">${day} ${mon}</span>`;
        }

        /* ── TABLA SEMANAL ── */
        function kwRenderWeekTable() {
            const d = kwRawData;
            const max = d.semanas.length;
            const end = Math.min(kwWeekOffset + kwWeeksPerView, max);
            const vis = d.semanas.slice(kwWeekOffset, end);

            const canPrev = kwWeekOffset > 0;
            const canNext = end < max;

            let html = `
    <div class="kw-table-card">
        <div class="kw-table-card-head">
            <div class="kw-table-card-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--kw-blue-500)" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Por Semana
            </div>
            <div class="kw-table-nav-group">
                <span class="kw-table-period-badge" id="kwWeekBadge">${vis[0]?.label ?? ''} – ${vis[vis.length-1]?.label ?? ''}</span>
                <button class="kw-btn-nav" onclick="kwNavWeek(-1)" ${!canPrev?'disabled':''} title="Anterior">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="kw-btn-nav" onclick="kwNavWeek(1)" ${!canNext?'disabled':''} title="Siguiente">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
        <div class="kw-tbl-wrap">
        <table class="kw-tbl">
            <thead>
                <tr>
                    <th>Categoría</th>
                    ${vis.map(s => `<th class="kw-tbl-th-week">${fmtWeekHeader(s)}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                <tr class="kw-row-kpi">
                    <td>KPI</td>
                    ${vis.map((_, i) => {
                        const vi = kwWeekOffset + i;
                        const v  = d.weekKpi[vi] ?? null;
                        return `<td><span class="kw-chip ${kwChipClass(v)}">${v !== null ? v+'%' : '—'}</span></td>`;
                    }).join('')}
                </tr>
                ${d.cats.map(cat => `
                    <tr>
                        <td>${cat}</td>
                        ${vis.map((_, i) => {
                            const vi = kwWeekOffset + i;
                            const v  = d.weekData[cat]?.[vi] ?? null;
                            return `<td><span class="kw-chip ${kwChipClass(v)}">${v !== null ? v+'%' : '—'}</span></td>`;
                        }).join('')}
                    </tr>`).join('')}
            </tbody>
        </table>
        </div>
    </div>`;
            return html;
        }

        /* ── TABLA MENSUAL ── */
        function kwRenderMonthTable() {
            const d = kwRawData;
            const max = d.meses.length;
            const end = Math.min(kwMonthOffset + kwMonthsPerView, max);
            const vis = d.meses.slice(kwMonthOffset, end);

            const canPrev = kwMonthOffset > 0;
            const canNext = end < max;

            let html = `
    <div class="kw-table-card">
        <div class="kw-table-card-head">
            <div class="kw-table-card-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--kw-blue-500)" stroke-width="2.5">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Por Mes
            </div>
            <div class="kw-table-nav-group">
                <span class="kw-table-period-badge" id="kwMonthBadge">${vis[0]?.label ?? ''} – ${vis[vis.length-1]?.label ?? ''}</span>
                <button class="kw-btn-nav" onclick="kwNavMonth(-1)" ${!canPrev?'disabled':''} title="Anterior">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="kw-btn-nav" onclick="kwNavMonth(1)" ${!canNext?'disabled':''} title="Siguiente">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
        <div class="kw-tbl-wrap">
        <table class="kw-tbl">
            <thead>
                <tr>
                    <th>Categoría</th>
                    ${vis.map(m => `<th>${m.label}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                <tr class="kw-row-kpi">
                    <td>KPI</td>
                    ${vis.map((_, i) => {
                        const vi = kwMonthOffset + i;
                        const v  = d.monthKpi[vi] ?? null;
                        return `<td><span class="kw-chip ${kwChipClass(v)}">${v !== null ? v+'%' : '—'}</span></td>`;
                    }).join('')}
                </tr>
                ${d.cats.map(cat => `
                    <tr>
                        <td>${cat}</td>
                        ${vis.map((_, i) => {
                            const vi = kwMonthOffset + i;
                            const v  = d.monthData[cat]?.[vi] ?? null;
                            return `<td><span class="kw-chip ${kwChipClass(v)}">${v !== null ? v+'%' : '—'}</span></td>`;
                        }).join('')}
                    </tr>`).join('')}
            </tbody>
        </table>
        </div>
    </div>`;
            return html;
        }

        /* ── HEAT MAP MEJORADO ── */
        function kwRenderHeatMap() {
            const d = kwRawData;
            // Mostrar TODAS las semanas disponibles en el heatmap (no solo las visibles)
            const allWeeks = d.semanas;

            // fmtWeekHeader() definida en scope global

            // Tooltip detallado al hover
            function makeTooltip(cat, s, v) {
                if (v === null) return `${cat} · ${s.label}: Sin datos`;
                const status = v >= 95 ? '✓ Verde' : v >= 89.90 ? '⚠ Amarillo' : '✕ Rojo';
                return `${cat} · ${s.label}\n${s.inicio ? s.inicio + ' → ' + s.fin : ''}\nKPI: ${v}%  ${status}`;
            }

            // Calcular KPI general por semana para la fila superior
            function kpiGeneral(i) {
                const v = d.weekKpi[i] ?? null;
                return v;
            }

            let html = `
            <div class="kw-heat-section kw-fade-up">
                <div class="kw-heat-head">
                    <div class="kw-heat-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--kw-blue-500)" stroke-width="2.5">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Mapa de calor — Todas las semanas del período
                    </div>
                    <div class="kw-heat-legend">
                        <span class="kw-heat-leg green">≥ 95%</span>
                        <span class="kw-heat-leg amber">89.9 – 94.9%</span>
                        <span class="kw-heat-leg red">≤ 89.8%</span>
                    </div>
                </div>
                <div class="kw-heat-body">
                    <div class="kw-heat-scroll-wrap">
                        <table class="kw-heat-tbl">
                            <thead>
                                <tr>
                                    <th class="kw-heat-th-label"></th>
                                    ${allWeeks.map(s => `<th class="kw-heat-th-col">${fmtWeekHeader(s)}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Fila KPI General -->
                                <tr class="kw-heat-row-kpi">
                                    <td class="kw-heat-td-label">
                                        <span class="kw-heat-kpi-badge">KPI</span>
                                    </td>
                                    ${allWeeks.map((s, i) => {
                                        const v = kpiGeneral(i);
                                        const cls = kwChipClass(v);
                                        return `<td class="kw-heat-td">
                                                <div class="kw-heat-cell kpi ${cls}" title="${makeTooltip('KPI General', s, v)}">
                                                    ${v !== null ? v+'%' : '—'}
                                                </div>
                                            </td>`;
                                    }).join('')}
                                </tr>
                                <!-- Filas por categoría -->
                                ${d.cats.map(cat => `
                                    <tr>
                                        <td class="kw-heat-td-label">${cat}</td>
                                        ${allWeeks.map((s, i) => {
                                            const v = d.weekData[cat]?.[i] ?? null;
                                            const cls = kwChipClass(v);
                                            return `<td class="kw-heat-td">
                                            <div class="kw-heat-cell ${cls}" title="${makeTooltip(cat, s, v)}">
                                                ${v !== null ? v+'%' : '—'}
                                            </div>
                                        </td>`;
                                        }).join('')}
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;
            return html;
        }

        /* ── TREND CHART ── */
        function kwRenderTrendChart() {
            const d = kwRawData;
            const canvas = document.getElementById('kwTrendChart');
            if (!canvas) return;

            if (kwTrendChart) {
                kwTrendChart.destroy();
                kwTrendChart = null;
            }

            const labels = d.semanas.map(s => s.label);
            const kpiVals = labels.map((_, i) => d.weekKpi[i] ?? null);

            // Datasets por categoría + KPI general
            const catColors = {
                NOX: '#2d65d8',
                TD: '#8b5cf6',
                DEVABLUE: '#0ea5e9',
                BLANCO: '#10b981',
                COLOREADO: '#f59e0b'
            };

            const datasets = [{
                    label: 'KPI General',
                    data: kpiVals,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.35,
                    order: 0,
                },
                ...d.cats.map(cat => ({
                    label: cat,
                    data: d.weekData[cat] ?? [],
                    borderColor: catColors[cat] ?? '#8892a8',
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    pointRadius: 2.5,
                    pointHoverRadius: 5,
                    fill: false,
                    tension: 0.35,
                    borderDash: [4, 3],
                    order: 1,
                }))
            ];

            kwTrendChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: 'DM Sans',
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#4b5573',
                                padding: 16,
                                usePointStyle: true,
                                pointStyleWidth: 16,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0a1f4d',
                            titleFont: {
                                family: 'DM Sans',
                                size: 12,
                                weight: '700'
                            },
                            bodyFont: {
                                family: 'DM Mono',
                                size: 11
                            },
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y?.toFixed(2) ?? '—'}%`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#f2f4f8',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: 'DM Mono',
                                    size: 10
                                },
                                color: '#8892a8'
                            }
                        },
                        y: {
                            min: 60,
                            max: 100,
                            grid: {
                                color: '#f2f4f8',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: 'DM Mono',
                                    size: 10
                                },
                                color: '#8892a8',
                                callback: v => v + '%'
                            }
                        }
                    }
                }
            });

            // Reference lines: 95 verde, 89.9 rojo
            // Plugin inline
            const refPlugin = {
                id: 'refLines',
                afterDraw(chart) {
                    const {
                        ctx,
                        chartArea: {
                            left,
                            right
                        },
                        scales: {
                            y
                        }
                    } = chart;
                    [
                        [95, '#10b981'],
                        [89.90, '#ef4444']
                    ].forEach(([val, color]) => {
                        const yPos = y.getPixelForValue(val);
                        ctx.save();
                        ctx.strokeStyle = color;
                        ctx.setLineDash([6, 4]);
                        ctx.lineWidth = 1.2;
                        ctx.globalAlpha = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(left, yPos);
                        ctx.lineTo(right, yPos);
                        ctx.stroke();
                        ctx.restore();
                        ctx.save();
                        ctx.font = '500 9px DM Mono, monospace';
                        ctx.fillStyle = color;
                        ctx.globalAlpha = 0.8;
                        ctx.fillText(val + '%', right + 4, yPos + 3);
                        ctx.restore();
                    });
                }
            };
            kwTrendChart.options.plugins.refLines = {};
            Chart.register(refPlugin);
            kwTrendChart.update();
        }

        /* ── NAVEGACIÓN SEMANAS ── */
        function kwNavWeek(dir) {
            const d = kwRawData;
            const max = d.semanas.length;
            kwWeekOffset = Math.max(0, Math.min(kwWeekOffset + dir, max - kwWeeksPerView));
            // Re-render solo tablas + heatmap (sin destruir chart)
            $('.kw-tables-row').replaceWith(
                `<div class="kw-tables-row">${kwRenderWeekTable()}${kwRenderMonthTable()}</div>`);
            $('.kw-heat-section').replaceWith($(kwRenderHeatMap()));
        }

        /* ── NAVEGACIÓN MESES ── */
        function kwNavMonth(dir) {
            const d = kwRawData;
            const max = d.meses.length;
            kwMonthOffset = Math.max(0, Math.min(kwMonthOffset + dir, max - kwMonthsPerView));
            $('.kw-tables-row').replaceWith(
                `<div class="kw-tables-row">${kwRenderWeekTable()}${kwRenderMonthTable()}</div>`);
        }

        /* ── EMPTY ── */
        function kwRenderEmpty(msg) {
            $('#kwMainContent').html(`
        <div class="kw-empty">
            <div class="kw-empty-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8892a8" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <h5>${msg}</h5>
            <p>Selecciona otro mes o año</p>
        </div>
    `);
        }

        /* ── CLEAR CACHE ── */
        function kwClearCache() {
            $.ajax({
                url: "{{ route('comercial.lead-time.clear-cache') }}",
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
                    kwLoadData();
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
@endsection
