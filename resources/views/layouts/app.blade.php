<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboards Power BI')</title>
</head>
<body>
    @auth
    <div>
        <a href="{{ route('home') }}">Inicio</a>
        @if(auth()->user()->is_admin)
            | <a href="{{ route('admin.dashboards.index') }}">Gestionar Dashboards</a>
            | <a href="{{ route('admin.access.index') }}">Gestionar Accesos</a>
        @endif
        | <span>{{ auth()->user()->name }}</span>
        | <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Cerrar Sesión</button>
        </form>
    </div>
    <hr>
    @endauth

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div style="color:red">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @yield('content')
</body>
</html>