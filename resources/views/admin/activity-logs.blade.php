@extends('layouts.app')

@section('title', 'Logs de Actividad')

@section('content')
    <h1>Logs de Actividad</h1>

    <table style="width:100%;border-collapse:collapse;margin-top:2rem;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Fecha</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Usuario</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Acción</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">Descripción</th>
                <th style="padding:0.75rem;text-align:left;border:1px solid #ddd;">IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->user->name }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">
                        <span
                            style="padding:0.25rem 0.5rem;background:#17a2b8;color:white;border-radius:3px;font-size:0.85rem;">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->description }}</td>
                    <td style="padding:0.75rem;border:1px solid #ddd;">{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:1rem;">
        {{ $logs->links() }}
    </div>

    <div style="margin-top:2rem;">
        <a href="{{ route('admin.dashboard') }}" style="color:#007bff;">← Volver al Dashboard</a>
    </div>
@endsection
