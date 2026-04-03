@extends('emails.layouts.productividad')

@section('badge', 'ALERTA DE VENCIMIENTO')

@section('alert_banner')
    <div class="email-alert email-alert--warning">
        ⏰ Tu reporte de Comentarios vence en 1 hora — límite jueves 2:00 PM
    </div>
@endsection

@section('body')
    <p class="email-greeting">Hola, {{ $notifiable->name ?? 'usuario' }}.</p>
    <p class="email-intro">
        Tienes <strong>1 hora</strong> para enviar tu reporte de Comentarios.
        Recuerda que el límite es el <strong>jueves a las 2:00 PM</strong>.
        Enviar después del jueves reduce tu KPI.
    </p>

    <div class="sla-box sla-box--warning">
        <div class="sla-box__days">1h</div>
        <div class="sla-box__label">Tiempo restante para enviar</div>
    </div>

    <div class="data-card">
        <div class="data-card__header">Datos del Reporte Pendiente</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Sede</div>
                <div class="data-value"><strong>{{ $reporte->sede }}</strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Semana</div>
                <div class="data-value data-value--mono">S{{ $reporte->semana_numero }}/{{ $reporte->anio }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Límite</div>
                <div class="data-value" style="color:#d97706;font-weight:700;">
                    {{ $reporte->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i') }} hrs
                </div>
            </div>
        </div>
    </div>

    <div class="highlight-box">
        💡 <strong>Recuerda:</strong> Jueves = 100% · Viernes = 75% · Sábado = 50% · Después = 0%
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">Enviar Reporte Ahora</a>
    </div>
@endsection
