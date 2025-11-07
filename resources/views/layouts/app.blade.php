<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Reports Trimax</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <nav style="background:#333;color:white;padding:1rem;">
        <div style="max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <a href="{{ route('home') }}" style="color:white;text-decoration:none;font-size:1.2rem;font-weight:bold;">
                    Reports Trimax
                </a>
            </div>
            @auth
                <div style="display:flex;gap:1.5rem;align-items:center;">
                    <a href="{{ route('home') }}" style="color:white;text-decoration:none;">Inicio</a>
                    <a href="{{ route('dashboards.index') }}" style="color:white;text-decoration:none;">Dashboards</a>
                    <a href="{{ route('files.index') }}" style="color:white;text-decoration:none;">Archivos</a>

                    @if (auth()->user()->isAdmin())
                        <div style="position:relative;display:inline-block;">
                            <a href="#" onclick="toggleMenu(event)" style="color:white;text-decoration:none;">
                                Admin ▼
                            </a>
                            <div id="admin-menu"
                                style="display:none;position:absolute;background:#444;min-width:200px;box-shadow:0 2px 5px rgba(0,0,0,0.2);z-index:1000;top:100%;left:0;margin-top:0.5rem;">
                                <a href="{{ route('admin.dashboard') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    📊 Dashboard
                                </a>
                                <a href="{{ route('admin.users') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    👥 Usuarios
                                </a>
                                <a href="{{ route('admin.users-online') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    🟢 Usuarios Online
                                </a>
                                <a href="{{ route('admin.locations.map') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;background:#28a745;">
                                    🌍 Mapa de Ubicaciones
                                </a>
                                <a href="{{ route('admin.locations.index') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    📍 Historial Ubicaciones
                                </a>
                                <a href="{{ route('admin.activity-logs') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    📝 Logs de Actividad
                                </a>
                                <a href="{{ route('admin.security') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    🔒 Seguridad
                                </a>
                                <a href="{{ route('admin.analytics') }}"
                                    style="display:block;padding:0.75rem 1rem;color:white;text-decoration:none;">
                                    📈 Analytics
                                </a>
                            </div>
                        </div>
                    @endif

                    <span style="color:white;">{{ auth()->user()->name }}</span>

                    <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:white;cursor:pointer;padding:0;">
                            Salir
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <div style="max-width:1200px;margin:2rem auto;padding:0 1rem;">
        @if (session('success'))
            <div style="background:#4CAF50;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="background:#f44336;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background:#f44336;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                <ul style="margin:0;padding-left:1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        function toggleMenu(event) {
            event.preventDefault();
            const menu = document.getElementById('admin-menu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        // Cerrar menú al hacer click fuera
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('admin-menu');
            if (menu && !event.target.closest('a')) {
                menu.style.display = 'none';
            }
        });
    </script>
</body>

</html>
