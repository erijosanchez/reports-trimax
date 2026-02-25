@extends('layouts.app')

@section('title', 'Lead Time')

@section('content')
    <div class="content-wrapper lt-wrapper">
        <div class="row">
            <div class="col-sm-12">

                {{-- PAGE HEADER --}}
                <div class="lt-page-header">
                    <div class="lt-page-header-left">
                        <h1 class="lt-page-title">KPI: On-Time Production (OTP)</h1>
                        <p class="lt-page-subtitle">Trazabilidad y cumplimiento por tipo de trabajo</p>
                    </div>
                </div>

                {{-- FILTER PANEL --}}
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
                        <div class="lt-filter-item">
                            <label class="lt-filter-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Mes
                            </label>
                            <select class="lt-select" id="filterMonth">
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

                {{-- MAIN CONTENT --}}
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
        /* ============================================================
           LEAD TIME — AZUL CORPORATIVO PREMIUM
           Fuente: DM Sans + DM Mono para datos
           Paleta: Azul índigo corporativo + acentos cyan
           ============================================================ */

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
            --lt-shadow-xl: 0 20px 60px rgba(4, 18, 46, .16), 0 4px 16px rgba(4, 18, 46, .08);
            --lt-shadow-blue: 0 4px 20px rgba(45, 101, 216, .25);

            --lt-radius-sm: 8px;
            --lt-radius: 12px;
            --lt-radius-lg: 18px;
            --lt-radius-xl: 24px;
        }

        /* Base */
        .lt-wrapper * {
            box-sizing: border-box;
        }

        .lt-wrapper,
        .lt-wrapper * {
            font-family: 'DM Sans', sans-serif;
        }

        /* ============================================================
           PAGE HEADER
           ============================================================ */
        .lt-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            padding-top: 0.5rem;
        }

        .lt-page-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--lt-green-lt);
            color: var(--lt-green);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 0.6rem;
        }

        .lt-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--lt-green);
            animation: ltDotPulse 2s ease-in-out infinite;
        }

        @keyframes ltDotPulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.8);
            }
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

        /* ============================================================
           FILTER PANEL
           ============================================================ */
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
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
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

        /* Buttons */
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
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .lt-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            transition: background 0.15s;
        }

        .lt-btn:hover::after {
            background: rgba(255, 255, 255, 0.08);
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

        /* Loading state for button */
        .lt-btn-loading #btnIcon {
            animation: ltSpin 0.9s linear infinite;
        }

        @keyframes ltSpin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ============================================================
           LOADING STATE
           ============================================================ */
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
            animation: ltSpin 0.7s linear infinite;
        }

        /* ============================================================
           HERO SUMMARY — INDICADOR GENERAL
           ============================================================ */
        .lt-hero {
            background: linear-gradient(135deg, var(--lt-blue-950) 0%, var(--lt-blue-800) 55%, var(--lt-blue-900) 100%);
            border-radius: var(--lt-radius-xl);
            padding: 2.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            color: white;
        }

        /* Noise texture overlay */
        .lt-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='1'/%3E%3C/svg%3E");
            opacity: 0.025;
            pointer-events: none;
        }

        /* Geometric accent circles */
        .lt-hero::after {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 320px;
            height: 320px;
            border: 1.5px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .lt-hero-circle-2 {
            position: absolute;
            bottom: -100px;
            right: 40px;
            width: 220px;
            height: 220px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .lt-hero-inner {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: auto 1px 1fr;
            align-items: center;
            gap: 2.5rem;
        }

        .lt-hero-divider {
            width: 1px;
            height: 80px;
            background: rgba(255, 255, 255, 0.12);
        }

        /* Ring */
        .lt-ring-wrap {
            position: relative;
            width: 140px;
            height: 140px;
            flex-shrink: 0;
        }

        .lt-ring-svg {
            transform: rotate(-90deg);
            width: 140px;
            height: 140px;
            display: block;
        }

        .lt-ring-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.08);
            stroke-width: 10;
        }

        .lt-ring-fill {
            fill: none;
            stroke-width: 10;
            stroke-linecap: round;
            stroke-dasharray: 376.99;
            stroke-dashoffset: 376.99;
            transition: stroke-dashoffset 1.6s cubic-bezier(0.34, 1.1, 0.64, 1), stroke 0.5s;
        }

        .lt-ring-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            opacity: 0;
        }

        .lt-ring-center.is-visible {
            animation: ltFadeInScale 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.8s forwards;
        }

        @keyframes ltFadeInScale {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }

            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .lt-ring-val {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
            font-family: 'DM Mono', monospace;
        }

        .lt-ring-sym {
            font-size: 0.85rem;
            opacity: 0.55;
            font-weight: 500;
        }

        .lt-ring-lbl {
            font-size: 0.58rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.4;
            margin-top: 1px;
        }

        /* Hero Info */
        .lt-hero-period {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0.45;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }

        .lt-hero-title {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.15rem;
        }

        .lt-hero-desc {
            font-size: 0.83rem;
            opacity: 0.5;
            margin-bottom: 1.4rem;
        }

        .lt-hero-kpis {
            display: flex;
            gap: 1.75rem;
            flex-wrap: wrap;
        }

        .lt-hero-kpi {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .lt-hero-kpi-val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
            font-family: 'DM Mono', monospace;
            letter-spacing: -0.04em;
        }

        .lt-hero-kpi-val.clr-green {
            color: #4ade80;
        }

        .lt-hero-kpi-val.clr-amber {
            color: #fbbf24;
        }

        .lt-hero-kpi-val.clr-cyan {
            color: #38bdf8;
        }

        .lt-hero-kpi-lbl {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.42;
            font-weight: 600;
        }

        /* ============================================================
           CATEGORY CARDS
           ============================================================ */
        .lt-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .lt-card {
            background: var(--lt-white);
            border-radius: var(--lt-radius-lg);
            border: 1px solid var(--lt-gray-200);
            box-shadow: var(--lt-shadow-sm);
            overflow: hidden;
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1),
                border-color 0.22s;
            cursor: default;
        }

        .lt-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--lt-shadow-lg);
            border-color: var(--lt-blue-100);
        }

        .lt-card-full {
            grid-column: 1 / -1;
        }

        /* Card top accent line */
        .lt-card-accent {
            height: 3px;
            width: 100%;
        }

        .lt-card-accent.good {
            background: linear-gradient(90deg, var(--lt-green), #34d399);
        }

        .lt-card-accent.warn {
            background: linear-gradient(90deg, var(--lt-amber), #fcd34d);
        }

        .lt-card-accent.bad {
            background: linear-gradient(90deg, var(--lt-red), #f87171);
        }

        /* Card header */
        .lt-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.4rem 0.9rem;
        }

        .lt-card-head-left {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .lt-card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .lt-card-icon.good {
            background: var(--lt-green-lt);
            color: var(--lt-green);
        }

        .lt-card-icon.warn {
            background: var(--lt-amber-lt);
            color: var(--lt-amber);
        }

        .lt-card-icon.bad {
            background: var(--lt-red-lt);
            color: var(--lt-red);
        }

        .lt-card-name {
            font-size: 0.93rem;
            font-weight: 700;
            color: var(--lt-gray-950);
            letter-spacing: -0.01em;
        }

        .lt-card-cat {
            font-size: 0.7rem;
            color: var(--lt-gray-400);
            font-weight: 400;
            margin-top: 1px;
        }

        /* Big percentage — right side */
        .lt-card-pct-display {
            text-align: right;
        }

        .lt-card-pct-big {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
            font-family: 'DM Mono', monospace;
        }

        .lt-card-pct-big.good {
            color: var(--lt-green);
        }

        .lt-card-pct-big.warn {
            color: var(--lt-amber);
        }

        .lt-card-pct-big.bad {
            color: var(--lt-red);
        }

        .lt-card-pct-label {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--lt-gray-400);
            font-weight: 600;
            margin-top: 2px;
        }

        /* Divider */
        .lt-card-divider {
            height: 1px;
            background: var(--lt-gray-100);
            margin: 0 1.4rem;
        }

        /* Card body — bars */
        .lt-card-body {
            padding: 1rem 1.4rem 1.25rem;
        }

        .lt-bar-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.7rem;
            position: relative;
        }

        .lt-bar-row:last-child {
            margin-bottom: 0;
        }

        .lt-bar-label {
            width: 138px;
            min-width: 138px;
            font-size: 0.75rem;
            color: var(--lt-gray-600);
            font-weight: 500;
            text-align: right;
            line-height: 1.3;
            padding-right: 0.25rem;
            flex-shrink: 0;
        }

        .lt-bar-track {
            flex: 1;
            height: 18px;
            background: var(--lt-gray-100);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }

        .lt-bar-fill {
            height: 100%;
            border-radius: 6px;
            width: 0%;
            transition: width 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }

        .lt-bar-fill.blue {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        }

        .lt-bar-fill.orange {
            background: linear-gradient(90deg, #fb923c, #ea580c);
        }

        .lt-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 6px 6px 0 0;
        }

        /* Quantity badge (número grande a la derecha) */
        .lt-bar-stat {
            min-width: 72px;
            text-align: right;
            flex-shrink: 0;
        }

        .lt-bar-qty {
            font-size: 0.95rem;
            font-weight: 800;
            font-family: 'DM Mono', monospace;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .lt-bar-qty.blue {
            color: #2563eb;
        }

        .lt-bar-qty.orange {
            color: #ea580c;
        }

        .lt-bar-pct-small {
            font-size: 0.62rem;
            color: var(--lt-gray-400);
            font-weight: 500;
            margin-top: 1px;
        }

        /* Card footer */
        .lt-card-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 1.4rem 0.9rem;
            border-top: 1px solid var(--lt-gray-100);
        }

        .lt-card-total {
            font-size: 0.75rem;
            color: var(--lt-gray-400);
            font-weight: 500;
        }

        .lt-card-total strong {
            color: var(--lt-gray-950);
            font-family: 'DM Mono', monospace;
            font-weight: 700;
        }

        .lt-card-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .lt-card-status-pill.good {
            background: var(--lt-green-lt);
            color: var(--lt-green);
        }

        .lt-card-status-pill.warn {
            background: var(--lt-amber-lt);
            color: var(--lt-amber);
        }

        .lt-card-status-pill.bad {
            background: var(--lt-red-lt);
            color: var(--lt-red);
        }

        /* ============================================================
           X AXIS TICKS
           ============================================================ */
        .lt-xaxis {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
            padding-top: 0.4rem;
            border-top: 1px dashed var(--lt-gray-200);
        }

        .lt-xaxis-spacer {
            width: 138px;
            min-width: 138px;
            flex-shrink: 0;
        }

        .lt-xaxis-ticks {
            flex: 1;
            display: flex;
            justify-content: space-between;
        }

        .lt-xaxis-ticks span {
            font-size: 0.58rem;
            color: var(--lt-gray-400);
            font-family: 'DM Mono', monospace;
        }

        .lt-xaxis-end {
            min-width: 72px;
        }

        /* ============================================================
           EMPTY STATE
           ============================================================ */
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
            margin-bottom: 0.35rem;
        }

        .lt-empty p {
            font-size: 0.83rem;
            margin: 0;
        }

        /* ============================================================
           ANIMATIONS
           ============================================================ */
        .lt-fade-up {
            opacity: 0;
            transform: translateY(18px);
            animation: ltFadeUp 0.45s ease forwards;
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
            animation-delay: 0.08s;
        }

        .lt-fade-up:nth-child(3) {
            animation-delay: 0.16s;
        }

        .lt-fade-up:nth-child(4) {
            animation-delay: 0.24s;
        }

        .lt-fade-up:nth-child(5) {
            animation-delay: 0.32s;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 768px) {
            .lt-hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .lt-hero-divider {
                display: none;
            }

            .lt-hero-kpis {
                justify-content: center;
            }

            .lt-ring-wrap {
                margin: 0 auto;
            }

            .lt-cards-grid {
                grid-template-columns: 1fr;
            }

            .lt-filter-panel {
                flex-direction: column;
                align-items: stretch;
            }

            .lt-filter-actions {
                justify-content: flex-end;
            }

            .lt-bar-label {
                width: 100px;
                min-width: 100px;
                font-size: 0.68rem;
            }

            .lt-xaxis-spacer {
                width: 100px;
                min-width: 100px;
            }
        }

        /* ============================================================
                   TABLA ÓRDENES ATRASADAS
                   ============================================================ */
        .lt-table-section {
            margin-top: 1.75rem;
        }

        .lt-table-card {
            background: var(--lt-white);
            border-radius: var(--lt-radius-lg);
            border: 1px solid var(--lt-gray-200);
            box-shadow: var(--lt-shadow-sm);
            overflow: hidden;
        }

        /* --- Header de la tabla --- */
        .lt-table-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.4rem;
            border-bottom: 1px solid var(--lt-gray-100);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .lt-table-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.93rem;
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
            letter-spacing: 0.02em;
        }

        .lt-table-search {
            flex: 1;
            max-width: 320px;
            min-width: 200px;
        }

        .lt-table-input {
            width: 100%;
            background: var(--lt-gray-50);
            border: 1.5px solid var(--lt-gray-200);
            border-radius: var(--lt-radius-sm);
            padding: 0.5rem 0.85rem 0.5rem 2rem;
            font-size: 0.83rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--lt-gray-950);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%238892a8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 0.6rem center;
        }

        .lt-table-input:focus {
            border-color: var(--lt-blue-500);
            box-shadow: 0 0 0 3px rgba(45, 101, 216, .10);
            background-color: var(--lt-white);
        }

        .lt-table-input::placeholder {
            color: var(--lt-gray-400);
        }

        /* --- Scroll wrap --- */
        .lt-table-wrap {
            overflow-x: auto;
            overflow-y: auto;
        }

        .lt-table-wrap::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }

        .lt-table-wrap::-webkit-scrollbar-track {
            background: var(--lt-gray-100);
        }

        .lt-table-wrap::-webkit-scrollbar-thumb {
            background: var(--lt-gray-200);
            border-radius: 10px;
        }

        .lt-table-wrap::-webkit-scrollbar-thumb:hover {
            background: var(--lt-gray-400);
        }

        /* --- Table base --- */
        .lt-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 900px;
        }

        .lt-tbl thead tr {
            background: var(--lt-gray-50);
            border-bottom: 1px solid var(--lt-gray-200);
        }

        .lt-tbl thead th {
            padding: 0.65rem 0.9rem;
            text-align: left;
            font-size: 0.67rem;
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
        }

        /* --- Rows --- */
        .lt-tbl tbody tr {
            border-bottom: 1px solid var(--lt-gray-100);
            transition: background 0.15s;
        }

        .lt-tbl tbody tr:last-child {
            border-bottom: none;
        }

        .lt-tbl tbody tr:hover {
            background: var(--lt-blue-50);
        }

        .lt-tbl tbody td {
            padding: 0.6rem 0.9rem;
            color: var(--lt-gray-600);
            vertical-align: middle;
            line-height: 1.4;
        }

        /* --- Columnas especiales --- */
        .td-orden {
            font-family: 'DM Mono', monospace;
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--lt-blue-600) !important;
            letter-spacing: -0.02em;
            white-space: nowrap;
        }

        .td-atraso {
            font-family: 'DM Mono', monospace;
            font-weight: 800;
            font-size: 0.82rem;
            color: var(--lt-red) !important;
            white-space: nowrap;
        }

        /* --- Pills --- */
        .pill-sede {
            display: inline-block;
            padding: 2px 8px;
            background: var(--lt-blue-50);
            color: var(--lt-blue-700);
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .pill-tipo-trabajo {
            display: inline-block;
            padding: 2px 8px;
            background: var(--lt-gray-100);
            color: var(--lt-gray-600);
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid var(--lt-gray-200);
        }

        .pill-fuera {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: var(--lt-red-lt);
            color: var(--lt-red);
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .pill-fuera::before {
            content: '●';
            font-size: 0.45rem;
        }

        /* --- Footer paginación --- */
        .lt-table-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1.4rem;
            border-top: 1px solid var(--lt-gray-100);
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .lt-table-info {
            font-size: 0.75rem;
            color: var(--lt-gray-400);
            font-weight: 500;
        }

        .lt-table-pages {
            display: flex;
            gap: 3px;
            align-items: center;
        }

        .lt-table-pages button {
            min-width: 30px;
            height: 30px;
            padding: 0 6px;
            border: 1.5px solid var(--lt-gray-200);
            background: var(--lt-white);
            color: var(--lt-gray-600);
            border-radius: 7px;
            font-size: 0.75rem;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .lt-table-pages button:hover:not(:disabled):not(.active) {
            background: var(--lt-gray-50);
            border-color: var(--lt-gray-400);
            color: var(--lt-gray-950);
        }

        .lt-table-pages button.active {
            background: var(--lt-blue-600);
            border-color: var(--lt-blue-600);
            color: white;
            box-shadow: 0 2px 8px rgba(45, 101, 216, .3);
        }

        .lt-table-pages button:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
            'Octubre', 'Noviembre', 'Diciembre'
        ];
        const ORDEN_CATEGORIAS = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        // Tabla atrasadas
        let atrasadasData = [];
        let atrasadasFiltered = [];
        let atrasadasPage = 1;
        const atrasadasPerPage = 50;

        /* ── INIT ── */
        $(document).ready(() => {
            const now = new Date();
            $('#filterMonth').val(now.getMonth() + 1);

            $.ajax({
                url: "{{ route('comercial.lead-time.years') }}",
                method: 'GET',
                success({
                    success,
                    years
                }) {
                    const $y = $('#filterYear').empty();
                    const list = (success && years?.length) ? years : [now.getFullYear()];
                    list.forEach(y => $y.append(
                        `<option value="${y}" ${y == now.getFullYear() ? 'selected' : ''}>${y}</option>`
                    ));
                    loadData();
                },
                error() {
                    $('#filterYear').html(
                        `<option value="${now.getFullYear()}" selected>${now.getFullYear()}</option>`);
                    loadData();
                }
            });
        });

        /* ── LOAD DATA ── */
        function loadData() {
            const year = $('#filterYear').val();
            const month = $('#filterMonth').val();
            if (!year || !month) return;

            const $btn = $('#btnConsultar');
            $btn.addClass('lt-btn-loading').prop('disabled', true);
            $('#btnText').text('Consultando...');
            showLoading();

            $.ajax({
                url: "{{ route('comercial.lead-time.data') }}",
                method: 'GET',
                data: {
                    year,
                    month
                },
                timeout: 60000,
                success({
                    success,
                    data,
                    filters
                }) {
                    $btn.removeClass('lt-btn-loading').prop('disabled', false);
                    $('#btnText').text('Consultar');
                    success && data ? renderDashboard(data, filters) : renderEmpty('Sin datos para este período');
                },
                error() {
                    $btn.removeClass('lt-btn-loading').prop('disabled', false);
                    $('#btnText').text('Consultar');
                    renderEmpty('Error de conexión al servidor');
                }
            });
        }

        function showLoading() {
            $('#mainContent').html(`
                <div class="lt-loading-state">
                    <div class="lt-spinner"></div>
                    <p>Consultando datos de Lead Time...</p>
                </div>
            `);
        }

        /* ── RENDER DASHBOARD ── */
        function renderDashboard(data, filters) {
            const general = data.general || {
                total: 0,
                porcentaje: 0
            };
            const categorias = data.categorias || {};
            const mesNombre = MESES[filters.month] || '';
            const ordenesAtrasadas = data.ordenes_atrasadas || [];

            const circumference = 376.99;
            const pct = general.porcentaje;
            const offset = circumference - (pct / 100) * circumference;

            let ringColor = '#22c55e';
            if (pct < 85) ringColor = '#ef4444';
            else if (pct < 92) ringColor = '#f59e0b';

            let totalAtiempo = 0,
                totalAtraso = 0;
            Object.values(categorias).forEach(cat => {
                cat.barras.forEach(b => {
                    if (b.tipo === 'atraso') totalAtraso += b.cantidad;
                    else totalAtiempo += b.cantidad;
                });
            });

            const catCount = Object.values(categorias).filter(c => c.total > 0).length;

            /* HERO */
            let html = `
                <div class="lt-fade-up" style="margin-bottom:1.75rem;">
                    <div class="lt-hero">
                        <div class="lt-hero-circle-2"></div>
                        <div class="lt-hero-inner">
                            <div class="lt-ring-wrap">
                                <svg class="lt-ring-svg" viewBox="0 0 140 140">
                                    <circle class="lt-ring-bg"   cx="70" cy="70" r="60"/>
                                    <circle class="lt-ring-fill" cx="70" cy="70" r="60"
                                            id="progressRing"
                                            style="stroke:${ringColor}; stroke-dashoffset:${circumference}; stroke-dasharray:${circumference};"/>
                                </svg>
                                <div class="lt-ring-center" id="ringCenter">
                                    <div class="lt-ring-val" id="heroValue">0</div>
                                    <div class="lt-ring-sym">%</div>
                                    <div class="lt-ring-lbl">cumplimiento</div>
                                </div>
                            </div>
                            <div class="lt-hero-divider"></div>
                            <div>
                                <div class="lt-hero-period">${mesNombre} ${filters.year}</div>
                                <div class="lt-hero-title">Cumplimiento General</div>
                                <div class="lt-hero-desc">${general.total.toLocaleString()} órdenes procesadas en el período</div>
                                <div class="lt-hero-kpis">
                                    <div class="lt-hero-kpi">
                                        <span class="lt-hero-kpi-val clr-green">${totalAtiempo.toLocaleString()}</span>
                                        <span class="lt-hero-kpi-lbl">A tiempo</span>
                                    </div>
                                    <div class="lt-hero-kpi">
                                        <span class="lt-hero-kpi-val clr-amber">${totalAtraso.toLocaleString()}</span>
                                        <span class="lt-hero-kpi-lbl">Con atraso</span>
                                    </div>
                                    <div class="lt-hero-kpi">
                                        <span class="lt-hero-kpi-val clr-cyan">${catCount}</span>
                                        <span class="lt-hero-kpi-lbl">Categorías</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            /* CARDS */
            html += `<div class="lt-cards-grid">`;
            let idx = 0;
            ORDEN_CATEGORIAS.forEach(cat => {
                const info = categorias[cat];
                if (!info || info.total === 0) return;
                const isNox = cat === 'NOX';
                html +=
                    `<div class="lt-fade-up ${isNox ? ' lt-card-full' : ''}" style="animation-delay:${0.12 + idx * 0.08}s">${renderCard(info)}</div>`;
                idx++;
            });
            html += `</div>`;

            /* TABLA ATRASADAS */
            if (ordenesAtrasadas.length > 0) {
                atrasadasData = ordenesAtrasadas;
                atrasadasFiltered = [...ordenesAtrasadas];
                atrasadasPage = 1;

                html += `
                    <div class="lt-table-section lt-fade-up" style="animation-delay:${0.12 + idx * 0.08 + 0.1}s">
                        <div class="lt-table-card">
                            <div class="lt-table-head">
                                <div class="lt-table-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    Órdenes Atrasadas
                                    <span class="lt-table-count">${ordenesAtrasadas.length}</span>
                                </div>
                                <div class="lt-table-search">
                                    <input type="text" class="lt-table-input" id="searchAtrasadas"
                                            placeholder="Buscar orden, sede, producto..."
                                            oninput="filterAtrasadas()">
                                </div>
                            </div>
                            <div class="lt-table-wrap" style="max-height: 520px;">
                                <table class="lt-tbl">
                                    <thead>
                                        <tr>
                                            <th>N° Orden</th>
                                            <th>Sede</th>
                                            <th>Tipo</th>
                                            <th>Producto</th>
                                            <th>Tipo Trabajo</th>
                                            <th>Meta</th>
                                            <th>Solicitado</th>
                                            <th>Lead Time</th>
                                            <th>Entrega</th>
                                            <th>Atraso</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblAtrasadasBody"></tbody>
                                </table>
                            </div>
                            <div class="lt-table-foot">
                                <div class="lt-table-info" id="atrasadasInfo"></div>
                                <div class="lt-table-pages" id="atrasadasPages"></div>
                            </div>
                        </div>
                    </div>
                `;
            }

            $('#mainContent').html(html);

            setTimeout(() => {
                const ring = document.getElementById('progressRing');
                if (ring) ring.style.strokeDashoffset = offset;
                document.getElementById('ringCenter')?.classList.add('is-visible');
            }, 150);

            animateCount('heroValue', pct, 1400);
            setTimeout(animateBars, 350);

            if (ordenesAtrasadas.length > 0) renderAtrasadasPage();
        }

        /* ── TABLA ATRASADAS ── */
        function filterAtrasadas() {
            const q = ($('#searchAtrasadas').val() || '').toLowerCase().trim();
            atrasadasFiltered = !q ? [...atrasadasData] : atrasadasData.filter(o =>
                (o.numero_orden + '').toLowerCase().includes(q) ||
                (o.sede || '').toLowerCase().includes(q) ||
                (o.producto || '').toLowerCase().includes(q) ||
                (o.tipo_de_trabajo || '').toLowerCase().includes(q) ||
                (o.tipo || '').toLowerCase().includes(q)
            );
            atrasadasPage = 1;
            renderAtrasadasPage();
        }

        function renderAtrasadasPage() {
            const start = (atrasadasPage - 1) * atrasadasPerPage;
            const end = Math.min(start + atrasadasPerPage, atrasadasFiltered.length);
            const pageData = atrasadasFiltered.slice(start, end);

            let html = '';
            if (pageData.length === 0) {
                html = `<tr><td colspan="11" style="text-align:center;padding:2rem;color:var(--lt-gray-400);">
                            No se encontraron órdenes atrasadas</td></tr>`;
            } else {
                pageData.forEach(o => {
                    html += `
                        <tr>
                            <td class="td-orden">${o.numero_orden}</td>
                            <td><span class="pill-sede">${o.sede}</span></td>
                            <td>${truncar(o.tipo, 22)}</td>
                            <td title="${o.producto}">${truncar(o.producto, 38)}</td>
                            <td><span class="pill-tipo-trabajo">${o.tipo_de_trabajo}</span></td>
                            <td style="text-align:center;font-family:'DM Mono',monospace;font-weight:600;">${o.meta}</td>
                            <td style="font-family:'DM Mono',monospace;font-size:0.75rem;">${o.solicitado}</td>
                            <td style="font-family:'DM Mono',monospace;font-size:0.75rem;">${o.lead_time}</td>
                            <td style="font-family:'DM Mono',monospace;font-size:0.75rem;">${o.time}</td>
                            <td class="td-atraso">${o.atraso}</td>
                            <td style="text-align:center;"><span class="pill-fuera">Fuera de tiempo</span></td>
                        </tr>
                    `;
                });
            }

            $('#tblAtrasadasBody').html(html);
            $('#atrasadasInfo').text(
                `Mostrando ${pageData.length > 0 ? start + 1 : 0} – ${end} de ${atrasadasFiltered.length} órdenes atrasadas`
            );

            const totalPages = Math.ceil(atrasadasFiltered.length / atrasadasPerPage);
            let pHtml = '';
            pHtml +=
                `<button ${atrasadasPage <= 1 ? 'disabled' : ''} onclick="goAtrasadasPage(${atrasadasPage - 1})">‹</button>`;
            for (let p = 1; p <= totalPages; p++) {
                if (totalPages <= 7 || p === 1 || p === totalPages || Math.abs(p - atrasadasPage) <= 1) {
                    pHtml +=
                        `<button class="${p === atrasadasPage ? 'active' : ''}" onclick="goAtrasadasPage(${p})">${p}</button>`;
                } else if (Math.abs(p - atrasadasPage) === 2) {
                    pHtml += `<button disabled>…</button>`;
                }
            }
            pHtml +=
                `<button ${atrasadasPage >= totalPages ? 'disabled' : ''} onclick="goAtrasadasPage(${atrasadasPage + 1})">›</button>`;
            $('#atrasadasPages').html(pHtml);
        }

        function goAtrasadasPage(page) {
            const totalPages = Math.ceil(atrasadasFiltered.length / atrasadasPerPage);
            if (page < 1 || page > totalPages) return;
            atrasadasPage = page;
            renderAtrasadasPage();
            $('html, body').animate({
                scrollTop: $('.lt-table-card').offset().top - 80
            }, 300);
        }

        function truncar(t, max) {
            if (!t) return '—';
            return t.length > max ? t.substring(0, max) + '…' : t;
        }

        /* ── RENDER CARD ── */
        function renderCard(info) {
            const pct = info.porcentaje_cumplimiento;
            const cls = pct >= 92 ? 'good' : pct >= 85 ? 'warn' : 'bad';
            const label = pct >= 92 ? 'Cumple' : pct >= 85 ? 'Alerta' : 'Crítico';
            const icon = pct >= 92 ? '✓' : pct >= 85 ? '!' : '✕';

            let maxPct = 0;
            info.barras.forEach(b => {
                if (b.porcentaje > maxPct) maxPct = b.porcentaje;
            });
            maxPct = Math.max(Math.ceil(maxPct / 10) * 10, 10);

            let barsHtml = '';
            info.barras.forEach(bar => {
                const widthPct = maxPct > 0 ? (bar.porcentaje / maxPct * 100) : 0;
                const colorClass = bar.tipo === 'atraso' ? 'orange' : 'blue';
                barsHtml += `
                    <div class="lt-bar-row">
                        <div class="lt-bar-label">${bar.label}</div>
                        <div class="lt-bar-track">
                            <div class="lt-bar-fill ${colorClass}" data-width="${widthPct}" style="width:0%"></div>
                        </div>
                        <div class="lt-bar-stat">
                            <div class="lt-bar-qty ${colorClass}">${bar.cantidad.toLocaleString()}</div>
                            <div class="lt-bar-pct-small">${bar.porcentaje}%</div>
                        </div>
                    </div>
                `;
            });

            const ticks = [0, 1, 2, 3, 4].map(i => `<span>${((maxPct/4)*i).toFixed(0)}%</span>`).join('');

            return `
                <div class="h-100 lt-card">
                    <div class="lt-card-accent ${cls}"></div>
                    <div class="lt-card-head">
                        <div class="lt-card-head-left">
                            <div class="lt-card-icon ${cls}">${icon}</div>
                            <div>
                                <div class="lt-card-name">${info.nombre}</div>
                                <div class="lt-card-cat">Tipo de trabajo</div>
                            </div>
                        </div>
                        <div class="lt-card-pct-display">
                            <div class="lt-card-pct-big ${cls}">${pct}<span style="font-size:1rem;opacity:.6;">%</span></div>
                            <div class="lt-card-pct-label">cumplimiento</div>
                        </div>
                    </div>
                    <div class="lt-card-divider"></div>
                    <div class="lt-card-body">
                        ${barsHtml}
                        <div class="lt-xaxis">
                            <div class="lt-xaxis-spacer"></div>
                            <div class="lt-xaxis-ticks">${ticks}</div>
                            <div class="lt-xaxis-end"></div>
                        </div>
                    </div>
                    <div class="lt-card-foot">
                        <div class="lt-card-total"><strong>${info.total.toLocaleString()}</strong> órdenes totales</div>
                        <div class="lt-card-status-pill ${cls}">${label}</div>
                    </div>
                </div>
            `;
        }

        /* ── ANIMATE BARS ── */
        function animateBars() {
            $('.lt-bar-fill').each(function(i) {
                const $bar = $(this);
                setTimeout(() => $bar.css('width', $bar.data('width') + '%'), i * 55);
            });
        }

        /* ── ANIMATE COUNT ── */
        function animateCount(id, target, duration) {
            const el = document.getElementById(id);
            if (!el) return;
            const start = performance.now();
            const tick = now => {
                const p = Math.min((now - start) / duration, 1);
                const e = 1 - Math.pow(1 - p, 3);
                el.textContent = (target * e).toFixed(2);
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target;
            };
            setTimeout(() => requestAnimationFrame(tick), 600);
        }

        /* ── EMPTY STATE ── */
        function renderEmpty(msg) {
            $('#mainContent').html(`
                <div class="lt-empty">
                    <div class="lt-empty-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8892a8" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h5>${msg}</h5>
                    <p>Selecciona otro mes o año</p>
                </div>
            `);
        }

        /* ── CLEAR CACHE ── */
        function clearCache() {
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
@endsection
