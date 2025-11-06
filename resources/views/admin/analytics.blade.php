@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
    <h1>Analytics y Estadísticas de Uso</h1>

    <div style="margin-top:2rem;">
        <h2>Top 10 Usuarios por Tiempo de Uso</h2>

        <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Tiempo Total</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Sesiones</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Promedio por Sesión</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usageStats as $stat)
                    <tr>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $stat['name'] }}</td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">
                            {{ floor($stat['total_time'] / 3600) }}h {{ floor(($stat['total_time'] % 3600) / 60) }}m
                        </td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $stat['sessions_count'] }}</td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">
                            @if ($stat['sessions_count'] > 0)
                                {{ floor($stat['total_time'] / $stat['sessions_count'] / 60) }}m
                            @else
                                0m
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:2rem;">
        <a href="{{ route('admin.dashboard') }}" style="color:#007bff;">← Volver al Dashboard</a>
    </div>
@endsection
