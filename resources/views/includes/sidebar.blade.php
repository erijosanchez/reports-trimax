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

        {{-- MÓDULO COMERCIAL (Solo consultores y superadmin) --}}
        @if (auth()->user()->isConsultor() || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <li class="nav-item nav-category">COMERCIAL</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('comercial.orden') ? 'active' : '' }}"
                    href="{{ route('comercial.orden') }}">
                    <i class="mdi mdi-file-document-box menu-icon"></i>
                    <span class="menu-title">Consultar Orden</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('comercial.acuerdos') ? 'active' : '' }}"
                    href="{{ route('comercial.acuerdos') }}">
                    <i class="mdi mdi-book-open-page-variant menu-icon"></i>
                    <span class="menu-title">Acuerdos Comerciales</span>
                </a>
            </li>
        @endif

        {{-- Submódulo Descuentos Especiales (Solo usuarios con permiso de ver descuentos especiales) --}}
        @if (auth()->user()->puedeVerDescuentosEspeciales())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('comercial.descuentos.index') ? 'active' : '' }}"
                    href="{{ route('comercial.descuentos.index') }}">
                    <i class="mdi mdi-sale menu-icon"></i>
                    <span class="menu-title">Descuentos Especiales</span>
                </a>
            </li>
        @endif

        {{-- Submódulo Ventas (Solo usuarios con permiso de ver ventas consolidadas) --}}
        @if (auth()->user()->puedeVerVentasConsolidadas())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('comercial.ventas.sedes') ? 'active' : '' }}"
                    href="{{ route('comercial.ventas.sedes') }}">
                    <i class="mdi-cart-outline mdi menu-icon"></i>
                    <span class="menu-title">Ventas</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->puedeVerVentasConsolidadas())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('comercial.lead-time.index') ? 'active' : '' }}"
                    href="{{ route('comercial.lead-time.index') }}">
                    <i class="mdi-clock-outline mdi menu-icon"></i>
                    <span class="menu-title">Lead Time</span>
                </a>
            </li>
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

        {{-- MÓDULO ADMINISTRADOR (Solo admin y superadmin) --}}
        @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <li class="nav-item nav-category">Administrador</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#form-elements"
                    aria-expanded="{{ request()->routeIs('admin.dashboard*') ? 'true' : 'false' }}"
                    aria-controls="form-elements">
                    <i class="mdi-grid menu-icon mdi"></i>
                    <span class="menu-title">Admin Dashboard</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.dashboard') ? 'show' : '' }}" id="form-elements">
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
