@extends('layouts.app')

@section('title', $dashboard->title)

@section('others')

    <div class="page-header">
        <div class="breadcrumb">
            <span>Admin</span> / <a href="{{ route('admin.dashboards.index') }}">Dashboards</a> / {{ $dashboard->title }}
        </div>
        <div
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
            <div style="flex: 1 1 auto; min-width: 200px;">
                <h1 style="color: #0a2540;">{{ $dashboard->title }}</h1>
                <p style="color: #666; font-size: 14px;">
                    {{ $dashboard->description ?? 'Visualiza los datos de tu reporte en Power BI' }}</p>
            </div>
            <a href="{{ route('admin.dashboards.index') }}" class="btn btn-outline" style="white-space: nowrap;">
                ← Volver
            </a>
        </div>
    </div>

    <div class="content-area card-mobil">
        <div class="card dashboard-card">
            <div class="dashboard-header">
                <h2>Vista del Dashboard</h2>
            </div>

            <!-- CONTENEDOR PRINCIPAL -->
            <div id="stage" class="dashboard-frame-container">
                <iframe id="dashboardIframe" title="Dashboard Público" frameborder="0" allowfullscreen="true"></iframe>

                <!-- MÁSCARAS que tapan todo excepto el hueco central (botones Power BI) -->
                <div class="mask-left" aria-hidden="true"></div>
                <div class="mask-right" aria-hidden="true"></div>

                <!-- Botón flotante estilo Power BI -->
                <button id="fsBtn" class="fs-button">⛶</button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const encodedUrl = '{{ base64_encode($dashboard->embed_url) }}';
            const iframe = document.getElementById('dashboardIframe');
            const stage = document.getElementById('stage');
            const btnFS = document.getElementById('fsBtn');

            // Asignar URL del dashboard
            iframe.src = atob(encodedUrl);

            // Evitar click derecho dentro del iframe
            iframe.addEventListener('contextmenu', e => e.preventDefault());

            // Pantalla completa
            btnFS.addEventListener('click', async () => {
                try {
                    if (document.fullscreenElement) {
                        await document.exitFullscreen();
                        return;
                    }
                    await stage.requestFullscreen({
                        navigationUI: "hide"
                    });
                } catch (e) {
                    alert('No se pudo activar pantalla completa.');
                }
            });
        })();
    </script>

@endsection
