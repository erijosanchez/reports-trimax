<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize" aria-label="Contraer/expandir menú lateral">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('home') }}">
                <img src="{{ asset('assets/img/ltr.png') }}" alt="logo" />
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('home') }}">
                <img src="{{ asset('assets/img/fv.png') }}" alt="logo" />
            </a>
        </div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item font-weight-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text">👋Bienvenido, <span class="text-black fw-bold">{{ auth()->user()->name }}</span>
                </h1>
                <h3 class="welcome-sub-text">Usa el sistema responsablemente</h3>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-none d-lg-block">
                <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
                    <span class="input-group-addon input-group-prepend border-right">
                        <span class="icon-calendar input-group-text calendar-icon"></span>
                    </span>
                    <input type="text" class="form-control">
                </div>
            </li>
            <li class="nav-item dropdown d-lg-block">
                <a class="nav-link position-relative" id="bellDropdown" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false" title="Notificaciones">
                    <i class="mdi mdi-bell-outline" style="font-size:1.35rem;"></i>
                    <span id="notif-badge" class="badge rounded-pill bg-danger position-absolute"
                        style="top:2px; right:0; font-size:.6rem; display:none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown notif-dropdown" aria-labelledby="bellDropdown">
                    <div class="dropdown-header d-flex align-items-center justify-content-between flex-wrap gap-1">
                        <span class="font-weight-semibold">Notificaciones</span>
                        <button type="button" class="btn btn-link btn-sm p-0" id="notif-marcar-todas">Marcar
                            todas leídas</button>
                    </div>
                    <div id="notif-list">
                        <div class="notif-item text-center text-muted small py-3">Cargando...</div>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown d-lg-block user-dropdown">
                <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-avatar-iniciales class="rounded-circle" :nombre="Auth::user()->name" :size="32" /> </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <x-avatar-iniciales class="rounded-circle d-inline-flex" :nombre="Auth::user()->name" :size="64" />
                        <p class="mb-1 mt-3 font-weight-semibold">{{ auth()->user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->email }}</p>
                    </div>
                    <a class="dropdown-item" href="{{ route('firma.index') }}">
                        <i class="dropdown-item-icon mdi mdi-draw text-primary me-2"></i>
                        Mi Firma Digital
                        @if(!auth()->user()->tieneFirmaRegistrada())
                            <span class="badge bg-warning text-dark ms-1" style="font-size:9px;">Pendiente</span>
                        @endif
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit"
                            class="dropdown-item">
                            <i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>
                            Salir
                        </button>
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas" aria-label="Abrir menú">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

{{-- Aviso grande — se muestra solo, sin que el usuario abra la campanita --}}
<div class="modal fade" id="avisoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered aviso-memo-dialog">
        <div class="modal-content aviso-memo">
            <div class="aviso-memo-header">
                <div class="aviso-memo-heading">
                    <span class="aviso-memo-eyebrow">
                        <i class="mdi mdi-bullhorn"></i>Aviso
                        <span id="avisoModalContador" class="aviso-memo-contador" style="display:none;"></span>
                    </span>
                    <h4 class="aviso-memo-titulo" id="avisoModalTitulo"></h4>
                </div>
                <button type="button" class="btn-close btn-close-white aviso-memo-close" id="avisoModalClose" aria-label="Cerrar"></button>
            </div>
            <div class="aviso-memo-body">
                <p class="aviso-memo-mensaje" id="avisoModalMensaje"></p>
            </div>
            <div class="aviso-memo-footer">
                <small class="aviso-memo-byline" id="avisoModalFooter"></small>
                <button type="button" class="btn btn-primary aviso-memo-cta" id="avisoModalEntendido">Entendido</button>
            </div>
        </div>
    </div>
</div>

<style>
    .notif-dropdown {
        width: 380px;
        max-width: min(380px, 92vw);
        max-height: 70vh;
        overflow-y: auto;
        padding: 0;
    }

    .notif-dropdown .dropdown-header {
        padding: .75rem 1rem;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
        border-bottom: 1px solid #eee;
    }

    .notif-item {
        display: block;
        width: 100%;
        padding: .65rem 1rem;
        border-bottom: 1px solid #f1f1f4;
        white-space: normal;
        overflow-wrap: break-word;
        word-break: break-word;
        text-decoration: none;
        color: inherit;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background: #f8f9fb;
        color: inherit;
    }

    .notif-item .notif-row {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
    }

    .notif-item .notif-body {
        min-width: 0;
        flex: 1 1 auto;
    }

    .notif-item .notif-body>* {
        white-space: normal;
        overflow-wrap: break-word;
    }

    .notif-item .notif-dot {
        flex: 0 0 auto;
        margin-top: .35rem;
    }

    @media (max-width: 480px) {
        .notif-dropdown {
            width: 92vw;
        }
    }

    /* Aviso — modal de comunicado interno, se posa sobre cualquier página */
    .aviso-memo-dialog {
        max-width: 440px;
    }

    .aviso-memo {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 32px -14px rgba(15, 23, 68, .4);
    }

    .aviso-memo-header {
        position: relative;
        display: flex;
        align-items: flex-start;
        padding: 1.35rem 3rem 1.25rem 1.5rem;
        background: linear-gradient(180deg, var(--bs-primary, #1F3BB3) 0%, #17296E 100%);
        border-bottom: 2px solid #B6902E;
        color: #fff;
    }

    .aviso-memo-heading {
        min-width: 0;
        flex: 1 1 auto;
    }

    .aviso-memo-eyebrow {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #D9BE7B;
        margin-bottom: .45rem;
    }

    .aviso-memo-eyebrow .mdi {
        font-size: .85rem;
    }

    .aviso-memo-contador {
        font-weight: 400;
        text-transform: none;
        letter-spacing: normal;
        color: rgba(255, 255, 255, .6);
    }

    .aviso-memo-contador::before {
        content: "· ";
    }

    .aviso-memo-titulo {
        margin: 0;
        font-weight: 700;
        font-size: 1.3rem;
        line-height: 1.35;
        word-break: break-word;
    }

    .aviso-memo-close {
        position: absolute;
        top: 1.35rem;
        right: 1.3rem;
    }

    .aviso-memo-body {
        padding: 1.75rem 1.9rem;
        background: #F7F8FC;
    }

    .aviso-memo-mensaje {
        margin: 0;
        color: #2A2F45;
        font-size: 1rem;
        line-height: 1.7;
        white-space: pre-line;
        word-break: break-word;
    }

    .aviso-memo-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .9rem 1.9rem;
        background: #fff;
        border-top: 1px solid #E7E9F2;
    }

    .aviso-memo-byline {
        color: #8891A8;
    }

    .aviso-memo-cta {
        font-weight: 600;
        padding: .45rem 1.5rem;
        border-radius: 6px;
    }

    @media (max-width: 480px) {
        .aviso-memo-header {
            padding: 1.2rem 2.75rem 1.1rem 1.2rem;
        }

        .aviso-memo-body {
            padding: 1.4rem;
        }

        .aviso-memo-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .aviso-memo-cta {
            width: 100%;
        }
    }
</style>

@push('scripts')
    <script>
        (function() {
            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');
            const bell = document.getElementById('bellDropdown');
            const marcarTodasBtn = document.getElementById('notif-marcar-todas');
            let cargando = false;

            // ── Cola de avisos grandes (tipo "aviso") sin leer ──
            let colaAvisos = [];
            const avisoModalEl = document.getElementById('avisoModal');
            const avisoModal = new bootstrap.Modal(avisoModalEl);

            function marcarLeida(id) {
                return fetch(`/notificaciones/${id}/leer`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
            }

            function mostrarSiguienteAviso() {
                if (!colaAvisos.length) return;
                const aviso = colaAvisos[0];
                document.getElementById('avisoModalTitulo').textContent = aviso.titulo;
                document.getElementById('avisoModalMensaje').textContent = aviso.mensaje;
                document.getElementById('avisoModalFooter').textContent = aviso.enviado_por ?
                    `Enviado por ${aviso.enviado_por} · ${aviso.fecha}` : aviso.fecha;

                const pendientes = colaAvisos.length - 1;
                const contador = document.getElementById('avisoModalContador');
                if (pendientes > 0) {
                    contador.textContent = `${pendientes} pendiente${pendientes === 1 ? '' : 's'}`;
                    contador.style.display = '';
                } else {
                    contador.style.display = 'none';
                }

                avisoModal.show();
            }

            function cerrarAvisoActual() {
                const aviso = colaAvisos.shift();
                if (aviso) marcarLeida(aviso.id).finally(() => cargarNotificaciones());
                avisoModal.hide();
                if (colaAvisos.length) {
                    setTimeout(mostrarSiguienteAviso, 400);
                }
            }

            document.getElementById('avisoModalEntendido').addEventListener('click', cerrarAvisoActual);
            document.getElementById('avisoModalClose').addEventListener('click', cerrarAvisoActual);

            function renderLista(notificaciones) {
                if (!notificaciones.length) {
                    list.innerHTML = '<div class="notif-item text-center text-muted small py-3">Sin notificaciones</div>';
                    return;
                }
                list.innerHTML = notificaciones.map(n => `
                    <a href="#" class="notif-item ${n.leida ? '' : 'bg-light'}" data-id="${n.id}" data-url="${n.url ?? ''}">
                        <div class="notif-row">
                            <div class="notif-body">
                                <div class="fw-semibold small">${n.tipo === 'aviso' ? '<i class="mdi mdi-bullhorn text-primary me-1"></i>' : ''}${n.titulo}</div>
                                <div class="text-muted small">${n.mensaje}</div>
                                <div class="text-muted mt-1" style="font-size:10px;">${n.fecha}</div>
                            </div>
                            ${n.leida ? '' : '<span class="notif-dot badge bg-primary" style="font-size:8px;">&nbsp;</span>'}
                        </div>
                    </a>
                `).join('');

                list.querySelectorAll('.notif-item[data-id]').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const url = this.dataset.url;
                        marcarLeida(id).finally(() => {
                            if (url) window.location.href = url;
                            else cargarNotificaciones();
                        });
                    });
                });
            }

            function cargarNotificaciones() {
                if (cargando) return;
                cargando = true;
                fetch('/notificaciones')
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        renderLista(data.notificaciones);
                        if (data.no_leidas > 0) {
                            badge.textContent = data.no_leidas > 99 ? '99+' : data.no_leidas;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }

                        // Avisos sin leer que todavía no están en cola → encolar y mostrar
                        const nuevosAvisos = data.notificaciones.filter(n => n.tipo === 'aviso' && !n.leida &&
                            !colaAvisos.some(a => a.id === n.id));
                        if (nuevosAvisos.length) {
                            const yaMostrandoUno = colaAvisos.length > 0;
                            colaAvisos = colaAvisos.concat(nuevosAvisos);
                            if (!yaMostrandoUno) mostrarSiguienteAviso();
                        }
                    })
                    .catch(() => {
                        list.innerHTML = '<div class="notif-item text-center text-muted small py-3">Error al cargar</div>';
                    })
                    .finally(() => cargando = false);
            }

            if (bell) {
                bell.closest('.dropdown').addEventListener('show.bs.dropdown', cargarNotificaciones);
            }
            if (marcarTodasBtn) {
                marcarTodasBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch('/notificaciones/leer-todas', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(() => cargarNotificaciones());
                });
            }

            // Badge inicial + chequeo de avisos al cargar la página
            document.addEventListener('DOMContentLoaded', cargarNotificaciones);
        })();
    </script>
@endpush
