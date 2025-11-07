<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="mdi mdi-home menu-icon"></i>
                <span class="menu-title">Home</span>
            </a>
        </li>
        <li class="nav-item nav-category">Vistas</li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                aria-controls="ui-basic">
                <i class="mdi mdi-account-multiple menu-icon"></i>
                <span class="menu-title">Usuarios</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="{{ route('dashboards.index') }}">Dashboard</a></li>
                    <li class="nav-item"> <a class="nav-link" href="{{ route('files.index') }}">Archivos</a></li>
                </ul>
            </div>
        </li>
        @if (auth()->user()->isAdmin())
            <li class="nav-item nav-category">Administrador</li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false"
                    aria-controls="form-elements">
                    <i class="menu-icon mdi mdi-grid"></i>
                    <span class="menu-title">Admin Dashboard</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="form-elements">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false"
                    aria-controls="charts">
                    <i class="menu-icon mdi mdi-account-switch"></i>
                    <span class="menu-title">Admin Usuarios</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="charts">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.users') }}">Usuarios</a></li>
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.users-online') }}">Usuarios
                                Online</a></li>
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.locations.map') }}">Mapa de
                                Ubicaciones</a></li>
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.locations.index') }}">Historial
                                de Ubicaciones</a></li>
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.activity-logs') }}">Logs de
                                Actividad</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false"
                    aria-controls="icons">
                    <i class="menu-icon mdi mdi-lock-outline"></i>
                    <span class="menu-title">Seguridad</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="icons">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.security') }}">Seguridad</a>
                        </li>
                        <li class="nav-item"> <a class="nav-link" href="{{ route('admin.analytics') }}">Analisis</a>
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
