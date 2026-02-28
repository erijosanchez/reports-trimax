<!-- <<<<<<<<<<<<<<<<<<<<<<< resources/views/includes/sidebar.blade.php >>>>>>>>>>>>>>>>>>>> -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                <i class="mdi mdi-home menu-icon"></i>
                <span class="menu-title">Home</span>
            </a>
        </li>

        {{-- MÓDULO USUARIOS (Solo usuarios normales, consultores y superadmin) --}}
        @if (!auth()->user()->isMarketing())
            <li class="nav-item nav-category">General</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboards.index') ? 'active' : '' }}"
                    href="{{ route('dashboards.index') }}">
                    <i class="mdi mdi-chart-bar menu-icon"></i>
                    <span class="menu-title">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('files.index') ? 'active' : '' }}"
                    href="{{ route('files.index') }}">
                    <i class="mdi mdi-archive menu-icon"></i>
                    <span class="menu-title">Archivos</span>
                </a>
            </li>
        @endif

        @php
            $user = auth()->user();
            $tieneAccesoComercial =
                $user->puedeVerConsultarOrden() ||
                $user->puedeVerAcuerdosComerciales() ||
                $user->puedeVerDescuentosEspeciales() ||
                $user->puedeVerVentasConsolidadas() ||
                $user->puedeVerLeadTime();
        @endphp

        @if ($tieneAccesoComercial)
            <li class="nav-item nav-category">COMERCIAL</li>

            @if ($user->puedeVerConsultarOrden())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comercial.orden') ? 'active' : '' }}"
                        href="{{ route('comercial.orden') }}">
                        <i class="mdi mdi-file-document-box menu-icon"></i>
                        <span class="menu-title">Consultar Orden</span>
                    </a>
                </li>
            @endif

            @if ($user->puedeVerAcuerdosComerciales())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comercial.acuerdos') ? 'active' : '' }}"
                        href="{{ route('comercial.acuerdos') }}">
                        <i class="mdi mdi-book-open-page-variant menu-icon"></i>
                        <span class="menu-title">Acuerdos Comerciales</span>
                    </a>
                </li>
            @endif

            @if ($user->puedeVerDescuentosEspeciales())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comercial.descuentos.index') ? 'active' : '' }}"
                        href="{{ route('comercial.descuentos.index') }}">
                        <i class="mdi mdi-sale menu-icon"></i>
                        <span class="menu-title">Descuentos Especiales</span>
                    </a>
                </li>
            @endif

            @if ($user->puedeVerVentasConsolidadas())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comercial.ventas.sedes') ? 'active' : '' }}"
                        href="{{ route('comercial.ventas.sedes') }}">
                        <i class="mdi-cart-outline mdi menu-icon"></i>
                        <span class="menu-title">Ventas</span>
                    </a>
                </li>
            @endif

            @if ($user->puedeVerLeadTime())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comercial.lead-time.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#lead-time-menu"
                        aria-expanded="{{ request()->routeIs('comercial.lead-time.*') ? 'true' : 'false' }}"
                        aria-controls="lead-time-menu">

                        <i class="mdi-clock-outline mdi menu-icon"></i>
                        <span class="menu-title">Lead Time</span>
                        <i class="menu-arrow"></i>
                    </a>

                    <div class="collapse {{ request()->routeIs('comercial.lead-time.*') ? 'show' : '' }}"
                        id="lead-time-menu">

                        <ul class="flex-column nav sub-menu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('comercial.lead-time.index') ? 'active' : '' }}"
                                    href="{{ route('comercial.lead-time.index') }}">
                                    Lead Time Dashboard
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('comercial.lead-time.semanal') ? 'active' : '' }}"
                                    href="{{ route('comercial.lead-time.semanal') }}">
                                    Lead Time x Tiempo
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
        @endif

        {{-- MÓDULO MARKETING (Solo marketing y superadmin) --}}
        @if (auth()->user()->isMarketing() || auth()->user()->isSuperAdmin())
            <li class="nav-item nav-category">MARKETING</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.index') ? 'active' : '' }}"
                    href="{{ route('marketing.index') }}">
                    <i class="mdi-grid menu-icon mdi"></i>
                    <span class="menu-title">Dashboard Marketing</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.users.index') ? 'active' : '' }}"
                    href="{{ route('marketing.users.index') }}">
                    <i class="mdi mdi-account-multiple menu-icon"></i>
                    <span class="menu-title">Usuarios Marketing</span>
                </a>
            </li>
        @endif

        {{-- ══════════════════════════════════════════════════════
            MÓDULO RECURSOS HUMANOS
            Acceso: RRHH, Superadmin, o usuarios con permiso puede_crear_requerimientos
        ══════════════════════════════════════════════════════ --}}
        @if ($user->isRrhh() || $user->isSuperAdmin() || $user->puedeCrearRequerimientos())
            <li class="nav-item nav-category">RECURSOS HUMANOS</li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rrhh.requerimientos.*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#rrhh-menu"
                    aria-expanded="{{ request()->routeIs('rrhh.requerimientos.*') ? 'true' : 'false' }}"
                    aria-controls="rrhh-menu">
                    <i class="mdi mdi-account-search menu-icon"></i>
                    <span class="menu-title">Recursos Humanos</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->routeIs('rrhh.requerimientos.*') ? 'show' : '' }}" id="rrhh-menu">
                    <ul class="flex-column nav sub-menu">

                        {{-- Todos los que tienen acceso ven el listado --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('rrhh.requerimientos.index') ? 'active' : '' }}"
                                href="{{ route('rrhh.requerimientos.index') }}">
                                Requerimientos
                            </a>
                        </li>

                        {{-- Solo RRHH y Superadmin ven el dashboard --}}
                        @if ($user->isRrhh() || $user->isSuperAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('rrhh.requerimientos.dashboard') ? 'active' : '' }}"
                                    href="{{ route('rrhh.requerimientos.dashboard') }}">
                                    Dashboard RR.HH.
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>
        @endif

        {{-- MÓDULO ADMINISTRADOR (Solo admin y superadmin) --}}
        @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <li class="nav-item nav-category">Administrador</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#collapse-admin-dashboard"
                    aria-expanded="{{ request()->routeIs('admin.dashboard*') ? 'true' : 'false' }}"
                    aria-controls="collapse-admin-dashboard">
                    <i class="mdi-grid menu-icon mdi"></i>
                    <span class="menu-title">Admin Dashboard</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.dashboard') ? 'show' : '' }}"
                    id="collapse-admin-dashboard">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>

                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#charts"
                    aria-expanded="{{ request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? 'true' : 'false' }}"
                    aria-controls="charts">
                    <i class="menu-icon mdi mdi-account-switch"></i>
                    <span class="menu-title">Admin Usuarios</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? 'show' : '' }}"
                    id="charts">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                                href="{{ route('admin.users') }}">Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.map') ? 'active' : '' }}"
                                href="{{ route('admin.locations.map') }}">Ubicaciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.locations.index') ? 'active' : '' }}"
                                href="{{ route('admin.locations.index') }}">Historial de Ubicaciones</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#icons"
                    aria-expanded="{{ request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? 'true' : 'false' }}"
                    aria-controls="icons">
                    <i class="mdi-lock-outline menu-icon mdi"></i>
                    <span class="menu-title">Seguridad</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? 'show' : '' }}"
                    id="icons">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.security') ? 'active' : '' }}"
                                href="{{ route('admin.security') }}">Seguridad y Análisis</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}"
                                href="{{ route('admin.activity-logs') }}">Logs de Actividad</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        <li class="nav-item nav-category">help</li>
        <li class="nav-item">
            <a class="nav-link" href="http://bootstrapdash.com/demo/star-admin2-free/docs/documentation.html">
                <i class="menu-icon mdi mdi-file-document"></i>
                <span class="menu-title">Documentation</span>
            </a>
        </li>
    </ul>
</nav>
