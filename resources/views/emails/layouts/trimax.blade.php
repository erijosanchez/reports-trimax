<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'Notificación - CRM Trimax')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .email-wrapper {
            width: 100%;
            padding: 32px 16px;
            background-color: #f0f2f5;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        /* ── HEADER ──────────────────────────────────────────── */
        .email-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            padding: 28px 32px;
            text-align: center;
        }

        .email-header__logo {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .email-header__logo span {
            color: #93c5fd;
        }

        .email-header__sub {
            font-size: 12px;
            color: #bfdbfe;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .email-header__badge {
            display: inline-block;
            margin-top: 12px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        /* ── ALERT BANNER (tipo de notificación) ─────────────── */
        .email-alert {
            padding: 16px 32px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .email-alert--info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #2563eb;
        }

        .email-alert--success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .email-alert--warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .email-alert--danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .email-alert--purple {
            background: #ede9fe;
            color: #4c1d95;
            border-left: 4px solid #7c3aed;
        }

        /* ── BODY ────────────────────────────────────────────── */
        .email-body {
            padding: 28px 32px;
        }

        .email-greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .email-intro {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        /* ── DATA CARD ───────────────────────────────────────── */
        .data-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .data-card__header {
            background: #1e3a8a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 10px 16px;
            text-transform: uppercase;
        }

        .data-card__body {
            padding: 0;
        }

        .data-row {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            width: 40%;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f1f5f9;
        }

        .data-value {
            width: 60%;
            padding: 10px 16px;
            font-size: 13px;
            color: #1e293b;
            word-break: break-word;
        }

        .data-value--mono {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #2563eb;
        }

        /* ── BADGE ESTADO ────────────────────────────────────── */
        .badge-estado {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-pendiente {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-en-proceso {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-contratado {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-cancelado {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-urgente {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-regular {
            background: #f1f5f9;
            color: #475569;
        }

        /* ── NOTA / MENSAJE DESTACADO ─────────────────────────── */
        .highlight-box {
            background: #f0f9ff;
            border-left: 4px solid #0891b2;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 13px;
            color: #0c4a6e;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* ── SLA ALERTA ──────────────────────────────────────── */
        .sla-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        .sla-box__days {
            font-size: 42px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 4px;
        }

        .sla-box__label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .sla-box--warning .sla-box__days {
            color: #d97706;
        }

        .sla-box--warning .sla-box__label {
            color: #92400e;
        }

        .sla-box--critical {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        .sla-box--critical .sla-box__days {
            color: #dc2626;
        }

        .sla-box--critical .sla-box__label {
            color: #991b1b;
        }

        /* ── CTA BUTTON ──────────────────────────────────────── */
        .cta-wrapper {
            text-align: center;
            margin: 24px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* ── DIVIDER ─────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        /* ── FOOTER ──────────────────────────────────────────── */
        .email-footer {
            background: #1e293b;
            padding: 20px 32px;
            text-align: center;
        }

        .email-footer__brand {
            font-size: 13px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .email-footer__sub {
            font-size: 11px;
            color: #475569;
        }

        .email-footer__disclaimer {
            font-size: 11px;
            color: #334155;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #334155;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">

            {{-- HEADER --}}
            <div class="email-header">
                <div class="email-header__logo">TRIMAX<span>PERÚ</span></div>
                <div class="email-header__sub">CRM · Módulo de Recursos Humanos</div>
                <div class="email-header__badge">@yield('badge', 'NOTIFICACIÓN')</div>
            </div>

            {{-- ALERT BANNER --}}
            @yield('alert_banner')

            {{-- BODY --}}
            <div class="email-body">
                @yield('body')
            </div>

            {{-- FOOTER --}}
            <div class="email-footer">
                <div class="email-footer__brand">Glabal Mega S.A.C.</div>
                <div class="email-footer__sub">CRM · Módulo RRHH · Trimax</div>
                <div class="email-footer__disclaimer">
                    Este es un correo automático, por favor no responder directamente.<br>
                    Para consultas, accede al sistema CRM.
                </div>
            </div>

        </div>
    </div>
</body>

</html>
