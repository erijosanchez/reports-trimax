@extends('layouts.app')

@section('title', 'Usuarios Online')

@section('content')
    <h1>Usuarios Online ({{ $usersOnline->count() }})</h1>

    <p style="color:#666;margin-top:0.5rem;">Usuarios activos en los últimos 5 minutos</p>

    @if ($usersOnline->isEmpty())
        <p style="margin-top:2rem;">No hay usuarios online en este momento.</p>
    @else
        <table style="width:100%;border-collapse:collapse;margin-top:2rem;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Email</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Última Actividad</th>
                    <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Tiempo en Sesión</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usersOnline as $user)
                    @foreach ($user->activeSessions as $session)
                        <tr>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                <span
                                    style="display:inline-block;width:10px;height:10px;background:#28a745;border-radius:50%;margin-right:0.5rem;"></span>
                                {{ $user->name }}
                            </td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $user->email }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $session->ip_address }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                {{ $session->last_activity->diffForHumans() }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                {{ $session->login_at->diffForHumans(null, true) }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top:2rem;">
        <a href="{{ route('admin.dashboard') }}" style="color:#007bff;">← Volver al Dashboard</a>
    </div>
@endsection
