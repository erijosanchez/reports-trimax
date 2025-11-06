@extends('layouts.app')

@section('title', 'Seguridad')

@section('content')
    <h1>Panel de Seguridad</h1>

    <div style="margin-top:2rem;">
        <h2>IPs Bloqueadas ({{ $blockedIps->total() }})</h2>

        @if ($blockedIps->isEmpty())
            <p>No hay IPs bloqueadas actualmente.</p>
        @else
            <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Razón</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Bloqueado Hasta</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha Bloqueo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($blockedIps as $ip)
                        <tr>
                            <td style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;">{{ $ip->ip_address }}
                            </td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $ip->reason }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                @if ($ip->blocked_until)
                                    {{ $ip->blocked_until->format('d/m/Y H:i') }}
                                @else
                                    <span style="color:#dc3545;">Permanente</span>
                                @endif
                            </td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $ip->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top:1rem;">
                {{ $blockedIps->links() }}
            </div>
        @endif
    </div>

    <div style="margin-top:3rem;">
        <h2>Intentos de Login Fallidos Recientes</h2>

        @if ($recentFailedAttempts->isEmpty())
            <p>No hay intentos fallidos recientes.</p>
        @else
            <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Email</th>
                        <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentFailedAttempts as $attempt)
                        <tr>
                            <td style="padding:0.75rem;border:1px solid #ddd;">
                                {{ $attempt->attempted_at->format('d/m/Y H:i:s') }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;">{{ $attempt->email }}</td>
                            <td style="padding:0.75rem;border:1px solid #ddd;font-family:monospace;">
                                {{ $attempt->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div style="margin-top:2rem;">
        <a href="{{ route('admin.dashboard') }}" style="color:#007bff;">← Volver al Dashboard</a>
    </div>
@endsection
