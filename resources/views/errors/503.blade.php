<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trimax — En mantenimiento</title>
    {{-- La página se recarga sola cada 45s: cuando termine el mantenimiento,
         los usuarios vuelven a entrar automáticamente sin tener que refrescar. --}}
    <meta http-equiv="refresh" content="45">
    <link rel="shortcut icon" href="/assets/img/fv.png" type="image/x-icon">
    <style>
        :root {
            --dark-950: #020c1b;
            --dark-900: #030d1e;
            --blue-950: #0c1a38;
            --blue-600: #2563eb;
            --blue-400: #60a5fa;
            --indigo: #818cf8;
            --purple: #a78bfa;
            --cyan: #22d3ee;
            --green: #4ade80;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--dark-950);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            text-align: center;
            padding: 24px;
        }

        /* ── Fondo: orbs flotantes + rejilla ── */
        .bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(1200px 800px at 70% -10%, var(--blue-950) 0%, transparent 60%),
                radial-gradient(1000px 700px at -10% 110%, #0a1730 0%, transparent 55%),
                var(--dark-950);
        }

        .grid {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.022) 1px, transparent 1px);
            background-size: 46px 46px;
            mask-image: radial-gradient(circle at 50% 45%, #000 0%, transparent 78%);
            -webkit-mask-image: radial-gradient(circle at 50% 45%, #000 0%, transparent 78%);
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(38px);
            z-index: 1;
            opacity: .8;
        }

        .orb-1 {
            width: 420px;
            height: 420px;
            top: -120px;
            left: -80px;
            background: radial-gradient(circle, rgba(37, 99, 235, .50) 0%, transparent 65%);
            animation: drift1 16s ease-in-out infinite;
        }

        .orb-2 {
            width: 360px;
            height: 360px;
            bottom: -110px;
            right: -70px;
            background: radial-gradient(circle, rgba(129, 140, 248, .40) 0%, transparent 65%);
            animation: drift2 20s ease-in-out infinite;
        }

        .orb-3 {
            width: 260px;
            height: 260px;
            top: 55%;
            left: 60%;
            background: radial-gradient(circle, rgba(34, 211, 238, .24) 0%, transparent 65%);
            animation: drift3 24s ease-in-out infinite;
        }

        @keyframes drift1 {

            0%,
            100% {
                transform: translate(0, 0)
            }

            50% {
                transform: translate(60px, 40px)
            }
        }

        @keyframes drift2 {

            0%,
            100% {
                transform: translate(0, 0)
            }

            50% {
                transform: translate(-50px, -30px)
            }
        }

        @keyframes drift3 {

            0%,
            100% {
                transform: translate(0, 0) scale(1)
            }

            50% {
                transform: translate(-40px, 30px) scale(1.12)
            }
        }

        /* Partículas / estrellas titilando */
        .spark {
            position: fixed;
            z-index: 1;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: #fff;
            opacity: .0;
            animation: twinkle 3.5s ease-in-out infinite;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 0;
                transform: scale(.6)
            }

            50% {
                opacity: .7;
                transform: scale(1)
            }
        }

        /* ── Contenido ── */
        .content {
            position: relative;
            z-index: 2;
            max-width: 560px;
            width: 100%;
        }

        .logo {
            height: 54px;
            margin-bottom: 30px;
            filter: drop-shadow(0 6px 22px rgba(37, 99, 235, .45));
            animation: fadeUp .8s ease both;
        }

        /* ── Engranajes animados ── */
        .gears {
            position: relative;
            width: 150px;
            height: 120px;
            margin: 0 auto 26px;
            animation: fadeUp .8s .1s ease both;
        }

        .gear {
            position: absolute;
        }

        .gear svg {
            display: block;
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 4px 16px rgba(96, 165, 250, .35));
        }

        .gear-a {
            width: 96px;
            height: 96px;
            top: 0;
            left: 12px;
            color: var(--blue-400);
            animation: spin 8s linear infinite;
        }

        .gear-b {
            width: 64px;
            height: 64px;
            bottom: 2px;
            right: 8px;
            color: var(--purple);
            animation: spin-r 6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spin-r {
            to {
                transform: rotate(-360deg);
            }
        }

        .headline {
            font-size: clamp(1.7rem, 5vw, 2.5rem);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -.02em;
            margin-bottom: 14px;
            animation: fadeUp .8s .2s ease both;
        }

        .grad {
            background: linear-gradient(135deg, var(--blue-400) 0%, var(--indigo) 45%, var(--purple) 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            animation: shimmerText 5s linear infinite;
        }

        @keyframes shimmerText {
            to {
                background-position: 200% center;
            }
        }

        .sub {
            color: var(--gray-300);
            font-size: 1.02rem;
            line-height: 1.6;
            max-width: 440px;
            margin: 0 auto 8px;
            animation: fadeUp .8s .3s ease both;
        }

        /* Mensaje rotativo */
        .cycle-wrap {
            height: 26px;
            margin: 18px 0 26px;
            animation: fadeUp .8s .35s ease both;
        }

        .cycle {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--cyan);
            font-size: .95rem;
            font-weight: 600;
            transition: opacity .3s ease;
        }

        .cycle .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 0 rgba(74, 222, 128, .6);
            animation: pulse 1.6s ease-out infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, .55)
            }

            70% {
                box-shadow: 0 0 0 12px rgba(74, 222, 128, 0)
            }

            100% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0)
            }
        }

        /* Barra shimmer */
        .bar {
            position: relative;
            height: 6px;
            width: 260px;
            max-width: 80%;
            margin: 0 auto 30px;
            background: rgba(255, 255, 255, .08);
            border-radius: 99px;
            overflow: hidden;
            animation: fadeUp .8s .4s ease both;
        }

        .bar::before {
            content: '';
            position: absolute;
            inset: 0;
            width: 45%;
            border-radius: 99px;
            background: linear-gradient(90deg, transparent, var(--blue-400), var(--indigo), transparent);
            animation: slide 1.8s ease-in-out infinite;
        }

        @keyframes slide {
            0% {
                transform: translateX(-120%)
            }

            100% {
                transform: translateX(340%)
            }
        }

        .footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--gray-500);
            font-size: .8rem;
            animation: fadeUp .8s .5s ease both;
        }

        .footer .sep {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--gray-500);
        }

        .footer b {
            color: var(--gray-400);
            font-weight: 600;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before {
                animation: none !important;
            }
        }

        @media (max-width: 480px) {
            .logo {
                height: 44px;
                margin-bottom: 22px;
            }

            .gears {
                transform: scale(.9);
            }
        }
    </style>
</head>

<body>
    <div class="bg"></div>
    <div class="grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    {{-- Chispas (estrellas titilando) --}}
    <div class="spark" style="top:18%; left:22%; animation-delay:0s"></div>
    <div class="spark" style="top:30%; left:78%; animation-delay:.8s"></div>
    <div class="spark" style="top:68%; left:15%; animation-delay:1.4s"></div>
    <div class="spark" style="top:75%; left:85%; animation-delay:2.1s"></div>
    <div class="spark" style="top:12%; left:60%; animation-delay:1.1s"></div>
    <div class="spark" style="top:52%; left:40%; animation-delay:2.6s"></div>

    <div class="content">
        <img src="/assets/img/logoblanco.png" alt="Trimax" class="logo" onerror="this.style.display='none'">

        {{-- Engranajes girando --}}
        <div class="gears" aria-hidden="true">
            <div class="gear gear-a">
                <svg viewBox="0 0 100 100">
                    <g fill="currentColor">
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(0 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(45 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(90 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(135 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(180 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(225 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(270 50 50)" />
                        <rect x="43" y="2" width="14" height="21" rx="3" transform="rotate(315 50 50)" />
                    </g>
                    <circle cx="50" cy="50" r="31" fill="currentColor" />
                    <circle cx="50" cy="50" r="13" fill="var(--dark-950)" />
                </svg>
            </div>
            <div class="gear gear-b">
                <svg viewBox="0 0 100 100">
                    <g fill="currentColor">
                        <rect x="43" y="3" width="14" height="20" rx="3" transform="rotate(0 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3" transform="rotate(45 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3" transform="rotate(90 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3" transform="rotate(135 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3" transform="rotate(180 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3"
                            transform="rotate(225 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3"
                            transform="rotate(270 50 50)" />
                        <rect x="43" y="3" width="14" height="20" rx="3"
                            transform="rotate(315 50 50)" />
                    </g>
                    <circle cx="50" cy="50" r="31" fill="currentColor" />
                    <circle cx="50" cy="50" r="13" fill="var(--dark-950)" />
                </svg>
            </div>
        </div>

        <h1 class="headline">
            Estamos <span class="grad">afinando la máquina</span>
        </h1>

        <p class="sub">
            Trimax está recibiendo mejoras para que todo funcione mejor.
            Volvemos en unos minutos — gracias por tu paciencia. 💙
        </p>

        <div class="cycle-wrap">
            <span class="cycle" id="cycle">
                <span class="dot"></span>
                <span id="cycle-text">Engrasando los engranajes…</span>
            </span>
        </div>

        <div class="bar"></div>

        <div class="footer">
            <b>Trimax CRM</b>
            <span class="sep"></span>
            <span>Inteligencia Comercial</span>
            <span class="sep"></span>
            <span id="year">2026</span>
        </div>
    </div>

    <script>
        // Mensajes rotativos (puro toque de personalidad)
        (function() {
            var msgs = [
                'Engrasando los engranajes…',
                'Puliendo los KPIs ✨',
                'Afinando los reportes…',
                'Optimizando la base de datos…',
                'Ordenando los vouchers 🧾',
                'Casi listo, ya casi volvemos 🚀'
            ];
            var i = 0;
            var box = document.getElementById('cycle');
            var text = document.getElementById('cycle-text');
            if (box && text) {
                setInterval(function() {
                    i = (i + 1) % msgs.length;
                    box.style.opacity = '0';
                    setTimeout(function() {
                        text.textContent = msgs[i];
                        box.style.opacity = '1';
                    }, 300);
                }, 2600);
            }
            var y = document.getElementById('year');
            if (y) y.textContent = new Date().getFullYear();
        })();
    </script>
</body>

</html>
