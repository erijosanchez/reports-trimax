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
            <p>{{ Auth::user()->role ?? 'Usuario' }}</p>
        </div>
    </div>

    <nav class="sidebar-menu">

        <a href="{{ route('home') }}" class="menu-item active {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="menu-icon">🏠</span>
            <span>Inicio</span>
        </a>
        @if (auth()->user()->is_admin)
            <a href="{{ route('admin.dashboards.index') }}" class="menu-item {{ request()->routeIs('dashboards') ? 'active' : '' }}">
                <span class="menu-icon">📊</span>
                <span>Gestionar Dashboards</span>
            </a>
            <a href="{{ route('admin.access.index') }}" class="menu-item {{ request()->routeIs('access') ? 'active' : '' }}">
                <span class="menu-icon">📈</span>
                <span>Gestionar Accesos</span>
            </a>
        @endif
        <div style="height: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin: 10px 20px;"></div>
        <form method="POST" class="menu-item" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Cerrar Sesión</button>
        </form>
    </nav>
</aside>
