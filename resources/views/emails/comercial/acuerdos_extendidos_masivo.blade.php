@extends('emails.layouts.comercial')

@section('badge', 'EXTENSIÓN MASIVA')

@section('alert_banner')
<div class="email-alert email-alert--success">
    <span>📅</span>
    <span>{{ $esAdmin ? 'Resumen de Extensión Masiva de Acuerdos' : 'Tus Acuerdos Comerciales han sido Extendidos' }}</span>
</div>
@endsection

@section('body')
<p class="email-greeting">¡Hola, {{ $destinatario }}!</p>

@if($esAdmin)
<p class="email-intro">
    Se ha realizado una <strong>extensión masiva</strong> de acuerdos comerciales.
    A continuación el resumen completo de los <strong>{{ count($acuerdos) }} acuerdo(s)</strong> extendidos.
</p>
@else
<p class="email-intro">
    Los siguientes acuerdos comerciales a tu cargo han sido <strong>extendidos</strong> con una nueva fecha de vencimiento.
    Revisa el detalle a continuación.
</p>
@endif

{{-- Info de la extensión --}}
<div class="data-card" style="margin-bottom:20px;">
    <div class="data-card__header">Detalles de la Extensión</div>
    <div class="data-card__body">
        <div class="data-row">
            <div class="data-label">Nueva Fecha Fin</div>
            <div class="data-value"><strong style="color:#065f46;">{{ \Carbon\Carbon::parse($nuevaFecha)->format('d/m/Y') }}</strong></div>
        </div>
        <div class="data-row">
            <div class="data-label">Motivo</div>
            <div class="data-value">{{ $motivo }}</div>
        </div>
        <div class="data-row">
            <div class="data-label">Extendido por</div>
            <div class="data-value">{{ $extendidoPor }}</div>
        </div>
        <div class="data-row">
            <div class="data-label">Fecha de acción</div>
            <div class="data-value">{{ now()->format('d/m/Y H:i') }}</div>
        </div>
        @if($esAdmin)
        <div class="data-row">
            <div class="data-label">Total extendidos</div>
            <div class="data-value"><strong>{{ count($acuerdos) }} acuerdo(s)</strong></div>
        </div>
        @endif
    </div>
</div>

{{-- Tabla de acuerdos --}}
<div class="data-card">
    <div class="data-card__header">
        {{ $esAdmin ? 'Listado Completo de Acuerdos Extendidos' : 'Tus Acuerdos Extendidos' }}
    </div>
    <div class="data-card__body">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#1e3a8a; color:#fff;">
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">N° ACUERDO</th>
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">RAZÓN SOCIAL</th>
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">SEDE</th>
                    @if($esAdmin)
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">CONSULTOR</th>
                    @endif
                    <th style="padding:8px 12px; text-align:center; font-weight:700; letter-spacing:0.05em;">NUEVA FECHA FIN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($acuerdos as $i => $acuerdo)
                <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f8fafc' }}; border-bottom:1px solid #e2e8f0;">
                    <td style="padding:8px 12px; font-family:'Courier New',monospace; color:#2563eb; font-weight:700;">
                        {{ $acuerdo->numero_acuerdo }}
                    </td>
                    <td style="padding:8px 12px; color:#1e293b;">{{ $acuerdo->razon_social }}</td>
                    <td style="padding:8px 12px; color:#475569;">{{ $acuerdo->sede }}</td>
                    @if($esAdmin)
                    <td style="padding:8px 12px; color:#475569;">{{ $acuerdo->consultor }}</td>
                    @endif
                    <td style="padding:8px 12px; text-align:center;">
                        <span style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-weight:700;">
                            {{ \Carbon\Carbon::parse($acuerdo->fecha_fin)->format('d/m/Y') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="cta-wrapper">
    <a href="{{ url('/comercial/acuerdos') }}" class="cta-button">Ver Acuerdos en el Sistema</a>
</div>

<p style="font-size:13px; color:#64748b; text-align:center; margin-top:8px;">
    Este correo fue generado automáticamente como resultado de una extensión masiva en el CRM.
</p>
@endsection
