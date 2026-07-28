<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — Trimax CRM</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fv.png') }}" type="image/x-icon">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .error-card {
            text-align: center;
            padding: 2rem;
        }

        .error-number {
            font-size: 8rem;
            font-weight: 900;
            color: #e3e6f0;
            line-height: 1;
        }

        .error-number .accent {
            color: #3B82F6;
        }

        .error-icon {
            font-size: 4rem;
            color: #F59E0B;
            margin-bottom: 0.75rem;
        }

        h3 {
            margin: 0 0 0.5rem;
            font-weight: 700;
            color: #212529;
        }

        p {
            color: #6c757d;
            margin: 0 0 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #3B82F6;
            color: #fff;
        }

        .btn-outline {
            border: 1px solid #ced4da;
            color: #495057;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="mb-4">
            <img src="{{ asset('assets/img/ltr.png') }}" alt="TRIMAX" style="height: 60px;"
                onerror="this.style.display='none'">
        </div>

        <h1 class="error-number">4<span class="accent">0</span>4</h1>

        <div class="error-icon">
            <i class="mdi mdi-file-search-outline"></i>
        </div>

        <h3>Página no encontrada</h3>
        <p>
            La dirección a la que intentas acceder no existe o fue movida.<br>
            Revisa la URL o vuelve al inicio.
        </p>

        <div class="btn-group">
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="mdi mdi-home"></i> Ir al Inicio
            </a>
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="mdi mdi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</body>

</html>
