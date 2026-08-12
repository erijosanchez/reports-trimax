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
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="bellDropdown"
                    style="width:340px; max-height:420px; overflow-y:auto;">
                    <div class="dropdown-header d-flex align-items-center justify-content-between">
                        <span class="font-weight-semibold">Notificaciones</span>
                        <button type="button" class="btn btn-link btn-sm p-0" id="notif-marcar-todas">Marcar
                            todas leídas</button>
                    </div>
                    <div id="notif-list">
                        <div class="dropdown-item text-center text-muted small py-3">Cargando...</div>
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

@push('scripts')
    <script>
        (function() {
            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');
            const bell = document.getElementById('bellDropdown');
            const marcarTodasBtn = document.getElementById('notif-marcar-todas');
            let cargando = false;

            function renderLista(notificaciones) {
                if (!notificaciones.length) {
                    list.innerHTML = '<div class="dropdown-item text-center text-muted small py-3">Sin notificaciones</div>';
                    return;
                }
                list.innerHTML = notificaciones.map(n => `
                    <a href="#" class="dropdown-item notif-item py-2 ${n.leida ? '' : 'bg-light'}" data-id="${n.id}" data-url="${n.url ?? ''}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-semibold small">${n.titulo}</div>
                                <div class="text-muted small">${n.mensaje}</div>
                            </div>
                            ${n.leida ? '' : '<span class="badge bg-primary" style="font-size:8px;">&nbsp;</span>'}
                        </div>
                        <div class="text-muted" style="font-size:10px;">${n.fecha}</div>
                    </a>
                `).join('');

                list.querySelectorAll('.notif-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const id = this.dataset.id;
                        const url = this.dataset.url;
                        fetch(`/notificaciones/${id}/leer`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).finally(() => {
                            if (url) window.location.href = url;
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
                    })
                    .catch(() => {
                        list.innerHTML = '<div class="dropdown-item text-center text-muted small py-3">Error al cargar</div>';
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

            // Badge inicial al cargar la página, sin abrir el dropdown
            document.addEventListener('DOMContentLoaded', cargarNotificaciones);
        })();
    </script>
@endpush
