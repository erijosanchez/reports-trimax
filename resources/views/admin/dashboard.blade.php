@extends('layouts.app')

@section('title', 'Panel Admin')

@section('content')
    <h1>Panel de Administración</h1>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin:2rem 0;">
        <div style="padding:1.5rem;background:#007bff;color:white;border-radius:4px;">
            <h3>{{ $stats['total_users'] }}</h3>
            <p>Usuarios Totales</p>
        </div>
        <div style="padding:1.5rem;background:#28a745;color:white;border-radius:4px;">
            <h3>{{ $stats['users_online'] }}</h3>
            <p>Usuarios Online</p>
        </div>
        <div style="padding:1.5rem;background:#ffc107;color:white;border-radius:4px;">
            <h3>{{ $stats['total_sessions_today'] }}</h3>
            <p>Sesiones Hoy</p>
        </div>
        <div style="padding:1.5rem;background:#dc3545;color:white;border-radius:4px;">
            <h3>{{ $stats['blocked_ips'] }}</h3>
            <p>IPs Bloqueadas</p>
        </div>
    </div>

    <div style="margin-top:2rem;">
        <h2>Usuarios Online Ahora</h2>
        @if ($usersOnline->isEmpty())
            <p>No hay usuarios online.</p>
        @else
            <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Última Actividad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usersOnline as $user)
                        @foreach ($user->activeSessions as $session)
                            <tr>
                                <td style="padding:0.75rem;border:1px solid #ddd;">{{ $user->name }}</td>
                                <td style="padding:0.75rem;border:1px solid #ddd;">{{ $session->ip_address }}</td>
                                <td style="padding:0.75rem;border:1px solid #ddd;">
                                    {{ $session->last_activity->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div style="margin-top:2rem;">
        <h2>Actividad Reciente</h2>
        <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Acción</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Descripción</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentActivity as $log)
                    <tr>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->user->name }}</td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->action }}</td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->description }}</td>
                        <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:2rem;">
        <a href="{{ route('admin.users') }}"
            style="padding:0.5rem 1rem;background:#007bff;color:white;text-decoration:none;border-radius:4px;margin-right:0.5rem;">
            Gestionar Usuarios
        </a>
        <a href="{{ route('admin.users-online') }}"
            style="padding:0.5rem 1rem;background:#28a745;color:white;text-decoration:none;border-radius:4px;margin-right:0.5rem;">
            Usuarios Online
        </a>
        <a href="{{ route('admin.activity-logs') }}"
            style="padding:0.5rem 1rem;background:#17a2b8;color:white;text-decoration:none;border-radius:4px;margin-right:0.5rem;">
            Logs de Actividad
        </a>
        <a href="{{ route('admin.security') }}"
            style="padding:0.5rem 1rem;background:#dc3545;color:white;text-decoration:none;border-radius:4px;">
            Seguridad
        </a>
    </div>
@endsection
