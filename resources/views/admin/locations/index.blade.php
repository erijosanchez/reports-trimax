@extends('layouts.app')

@section('title', 'Historial de Ubicaciones')

@section('content')
    <h1>Historial de Ubicaciones</h1>

    <!-- Filtros -->
    <form method="GET" style="background:#f8f9fa;padding:1.5rem;border-radius:4px;margin:2rem 0;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
            <div>
                <label style="display:block;margin-bottom:0.5rem;">Usuario:</label>
                <select name="user_id" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block;margin-bottom:0.5rem;">Ciudad:</label>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="Buscar ciudad"
                    style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;">
            </div>

            <div>
                <label style="display:block;margin-bottom:0.5rem;">Fecha Desde:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;">
            </div>

            <div>
                <label style="display:block;margin-bottom:0.5rem;">Fecha Hasta:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;">
            </div>
        </div>

        <div style="margin-top:1rem;display:flex;gap:0.5rem;">
            <button type="submit"
                style="padding:0.5rem 1.5rem;background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px;">
                Filtrar
            </button>
            <a href="{{ route('admin.locations.index') }}"
                style="padding:0.5rem 1.5rem;background:#6c757d;color:white;text-decoration:none;border-radius:4px;display:inline-block;">
                Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de Ubicaciones -->
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha/Hora</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Ubicación</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Coordenadas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locations as $location)
                <tr>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        {{ $location->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        {{ $location->user->name }}
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        {{ $location->formatted_location }}
                        @if ($location->is_vpn)
                            <span
                                style="padding:0.25rem 0.5rem;background:#dc3545;color:white;border-radius:3px;font-size:0.75rem;">VPN</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;font-size:0.85rem;">
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
            @empty
                <tr>
                    <td colspan="5" style="padding:2rem;text-align:center;color:#666;">
                        No se encontraron ubicaciones
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $locations->links() }}
    </div>
@endsection
