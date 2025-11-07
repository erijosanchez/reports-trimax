@extends('layouts.app')

@section('title', 'Historial de ' . $user->name)

@section('content')
    <div style="margin-bottom:2rem;">
        <a href="{{ route('admin.locations.map') }}" style="color:#007bff;text-decoration:none;">
            ← Volver al Mapa
        </a>
    </div>

    <h1>Historial de Ubicaciones: {{ $user->name }}</h1>
    <p style="color:#666;">{{ $user->email }}</p>

    <!-- Estadísticas -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin:2rem 0;">
        <div style="padding:1.5rem;background:#007bff;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $stats['total_locations'] }}</h3>
            <p style="margin:0.5rem 0 0 0;">Ubicaciones Registradas</p>
        </div>
        <div style="padding:1.5rem;background:#28a745;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $stats['cities_visited'] }}</h3>
            <p style="margin:0.5rem 0 0 0;">Ciudades Visitadas</p>
        </div>
        <div style="padding:1.5rem;background:#ffc107;color:white;border-radius:4px;">
            <h3 style="margin:0;font-size:2rem;">{{ $stats['countries_visited'] }}</h3>
            <p style="margin:0.5rem 0 0 0;">Países Visitados</p>
        </div>
    </div>

    <!-- Ciudades Únicas -->
    <div style="background:#f8f9fa;padding:1.5rem;border-radius:4px;margin-bottom:2rem;">
        <h3>Ciudades Visitadas:</h3>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:1rem;">
            @foreach ($uniqueCities as $city)
                <span style="padding:0.5rem 1rem;background:white;border:1px solid #ddd;border-radius:20px;">
                    📍 {{ $city->city }}, {{ $city->region }}
                </span>
            @endforeach
        </div>
    </div>

    <!-- Historial -->
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha/Hora</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Ubicación</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Coordenadas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($locations as $location)
                <tr>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        {{ $location->created_at->format('d/m/Y H:i:s') }}<br>
                        <small style="color:#666;">{{ $location->created_at->diffForHumans() }}</small>
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        <strong>{{ $location->formatted_location }}</strong>
                        @if ($location->is_vpn)
                            <br><span style="color:#dc3545;font-size:0.85rem;">⚠️ VPN Detectado</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;">
                        {{ $location->ip_address }}
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;font-size:0.85rem;">
                        @if ($location->latitude && $location->longitude)
                            {{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}
                        @else
                            <span style="color:#999;">N/A</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $locations->appends(['user_id' => $user->id])->links() }}
    </div>
@endsection
