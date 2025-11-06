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
        <div style="max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;">
            <div>
                <a href="{{ route('home') }}" style="color:white;text-decoration:none;">Reports Trimax</a>
            </div>
            @auth
            <div style="display:flex;gap:1rem;">
                <a href="{{ route('home') }}" style="color:white;">Inicio</a>
                <a href="{{ route('dashboards.index') }}" style="color:white;">Dashboards</a>
                <a href="{{ route('files.index') }}" style="color:white;">Archivos</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" style="color:white;">Admin</a>
                @endif
                <span style="color:white;">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:white;cursor:pointer;">Salir</button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    <div style="max-width:1200px;margin:2rem auto;padding:0 1rem;">
        @if(session('success'))
            <div style="background:#4CAF50;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background:#f44336;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#f44336;color:white;padding:1rem;margin-bottom:1rem;border-radius:4px;">
                <ul style="margin:0;padding-left:1.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>