<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboards Power BI')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/global.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fv.png') }}" type="image/x-icon">
</head>

<body>
    @auth
        <div class="dashboard-container">
            @include('includes.sidebar')

            <!-- Main Content -->
            <main class="main-content" id="mainContent">
                <!-- Top Navigation -->
                <div class="top-nav">
                    <button class="menu-toggle" id="menuToggle">☰</button>

                    <div class="top-nav-right">
                        <div class="search-box">
                            <span class="search-icon">🔍</span>
                            <input type="text" placeholder="Buscar...">
                        </div>
                        <div class="notification-icon">
                            🔔
                            <span class="notification-badge">3</span>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="content-area">
                    @if (session('success'))
                        <div class="alert alert-success">
                            ✓ {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            ✗ {{ session('error') }}
                        </div>
                    @endif
                    @yield('content')
                    @yield('others')

                </div>
            </main>
        </div>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <script>
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const menuItems = document.querySelectorAll('.menu-item');

            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });

            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        </script>
    @endauth
</body>

</html>
