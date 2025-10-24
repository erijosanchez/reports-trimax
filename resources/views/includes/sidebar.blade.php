<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img class="aside" src="{{ asset('assets/img/LOGOTIPO TRIMAX 2025-01.png') }}" alt="">
        </div>
    </div>

    <div class="user-info">
        <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
        <div class="user-details">
            <h4>{{ Auth::user()->name }}</h4>
            <p>
                {{ Auth::user()->is_admin ? 'Administrador' : 'Usuario' }}
            </p>
        </div>
    </div>

    <nav class="sidebar-menu">

        <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="menu-icon"><i data-lucide="house"></i></span>
            <span>Inicio</span>
        </a>
        @if (auth()->user()->is_admin)
            <a href="{{ route('admin.dashboards.index') }}"
                class="menu-item {{ request()->routeIs('admin.dashboards.*') ? 'active' : '' }}">
                <span class="menu-icon"><i data-lucide="layout-dashboard"></i></span>
                <span>Gestionar Dashboards</span>
            </a>

            <a href="{{ route('admin.access.index') }}"
                class="menu-item {{ request()->routeIs('admin.access.*') ? 'active' : '' }}">
                <span class="menu-icon"><i data-lucide="scan-eye"></i></span>
                <span>Gestionar Accesos</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="menu-icon"><i data-lucide="users"></i></span>
                <span>Gestionar Usuarios</span>
            </a>
        @endif
        <div style="height: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin: 10px 20px;"></div>
        <form method="POST" class="menu-item-1" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit"><span class="menu-icon"><i data-lucide="log-out" style=""></i></span></button>
        </form>
    </nav>
</aside>
