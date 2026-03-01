
@extends('emails.layouts.trimax')

@section('badge', 'NUEVO REQUERIMIENTO')

@section('alert_banner')
    <div class="email-alert email-alert--info">
        📋 Se registró un nuevo requerimiento de personal en espera de asignación.
    </div>
@endsection

@section('body')
    <p class="email-greeting">Hola, {{ $notifiable->name }}</p>
    <p class="email-intro">
        Se ha creado un nuevo requerimiento de personal en el sistema CRM de Trimax.
        El estado inicial es <strong>Pendiente</strong> hasta que se asigne un responsable RH.
    </p>

    <div class="data-card">
        <div class="data-card__header">📄 Datos del Requerimiento</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono">{{ $requerimiento->codigo }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado</div>
                <div class="data-value">
                    <span class="badge-estado badge-pendiente">⏳ Pendiente</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Gerencia</div>
                <div class="data-value">{{ $requerimiento->gerencia }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Puesto</div>
                <div class="data-value"><strong>{{ $requerimiento->puesto }}</strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Sede</div>
                <div class="data-value">{{ $requerimiento->sede }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Jefe Directo</div>
                <div class="data-value">{{ $requerimiento->jefe_directo }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Tipo</div>
                <div class="data-value">
                    @if ($requerimiento->tipo === 'Urgente')
                        <span class="badge-estado badge-urgente">⚡ Urgente</span>
                    @else
                        <span class="badge-estado badge-regular">Regular</span>
                    @endif
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Solicitante</div>
                <div class="data-value">{{ $requerimiento->solicitante->name }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Solicitud</div>
                <div class="data-value">{{ $requerimiento->fecha_solicitud->format('d/m/Y H:i') }}</div>
            </div>
            @if ($requerimiento->condiciones_oferta)
                <div class="data-row">
                    <div class="data-label">Condiciones</div>
                    <div class="data-value">{{ $requerimiento->condiciones_oferta }}</div>
                </div>
            @endif
            @if ($requerimiento->comentarios)
                <div class="data-row">
                    <div class="data-label">Comentarios</div>
                    <div class="data-value">{{ $requerimiento->comentarios }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">Ver Requerimiento en el CRM</a>
    </div>
@endsection
