@extends('emails.layouts.productividad')

@php($observado = $observado ?? false)

@section('badge', $observado ? 'REPORTE CONFORME OBSERVADO' : 'REPORTE RECHAZADO')

@section('alert_banner')
    <div class="email-alert {{ $observado ? 'email-alert--warning' : 'email-alert--warning' }}">
        {{ $observado
            ? '📝 Tu reporte de ' . $tipo . ' fue marcado como CONFORME OBSERVADO'
            : '⚠️ Tu reporte de ' . $tipo . ' fue rechazado en la revisión' }}
    </div>
@endsection

@section('body')
    <p class="email-greeting">Estimado(a),</p>
    <p class="email-intro">
        @if ($observado)
            El reporte de <strong>{{ $tipo }}</strong> de la sede <strong>{{ $sede }}</strong>
            correspondiente a <strong>{{ $periodo }}</strong> fue marcado como
            <strong style="color:#d97706;">CONFORME OBSERVADO</strong> durante la revisión.
            @if ($penalidad)
                <br><span style="color:#d97706;font-weight:600;">Se aplicó un descuento de −{{ (int) $penalidad }}% sobre el KPI del reporte.</span>
            @endif
        @else
            El reporte de <strong>{{ $tipo }}</strong> de la sede <strong>{{ $sede }}</strong>
            correspondiente a <strong>{{ $periodo }}</strong> fue <strong style="color:#dc2626;">RECHAZADO</strong>
            durante la revisión. Por favor corrige y vuelve a enviar los documentos.
            <br><span style="color:#dc2626;font-weight:600;">Esto afecta el KPI del reporte (queda en 0%).</span>
        @endif
    </p>

    <div class="data-card">
        <div class="data-card__header">Detalle de la Revisión</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Sede</div>
                <div class="data-value"><strong>{{ $sede }}</strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Período</div>
                <div class="data-value">{{ $periodo }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">{{ $observado ? 'Observación' : 'Motivo del rechazo' }}</div>
                <div class="data-value">{{ $motivo }}</div>
            </div>
            @if ($observado && $penalidad)
            <div class="data-row">
                <div class="data-label">Descuento de KPI</div>
                <div class="data-value" style="color:#d97706;font-weight:600;">−{{ (int) $penalidad }}%</div>
            </div>
            @endif
            @if ($revisor)
            <div class="data-row">
                <div class="data-label">Revisado por</div>
                <div class="data-value">{{ $revisor }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">{{ $observado ? 'Ver en el Sistema' : 'Corregir en el Sistema' }}</a>
    </div>
@endsection
