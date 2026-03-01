{{-- resources/views/emails/rrhh/requerimiento_alerta_sla.blade.php --}}
@extends('emails.layouts.trimax')

@section('badge', 'ALERTA SLA')

@section('alert_banner')
    <div class="email-alert email-alert--danger">
        🚨 Este requerimiento ha superado el límite de 45 días sin cerrarse.
    </div>
@endsection

@section('body')
    <p class="email-greeting">Atención, {{ $notifiable->name }}</p>
    <p class="email-intro">
        El siguiente requerimiento de personal lleva <strong>{{ $diasTranscurridos }} días</strong> en proceso
        y ha superado el SLA establecido de 45 días. Se requiere acción inmediata.
    </p>

    {{-- Contador de días --}}
    <div class="sla-box {{ $diasTranscurridos > 60 ? 'sla-box--critical' : 'sla-box--warning' }}">
        <div class="sla-box__days">{{ $diasTranscurridos }}</div>
        <div class="sla-box__label">
            días transcurridos &nbsp;·&nbsp; +{{ $diasTranscurridos - 45 }} sobre el SLA
        </div>
    </div>

    <div class="data-card">
        <div class="data-card__header">📄 Datos del Requerimiento</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono">{{ $requerimiento->codigo }}</div>
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
                <div class="data-label">Solicitante</div>
                <div class="data-value">{{ $requerimiento->solicitante->name }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Responsable RH</div>
                <div class="data-value">
                    @if ($requerimiento->responsableRh)
                        {{ $requerimiento->responsableRh->name }}
                    @elseif($requerimiento->responsable_rh_externo)
                        {{ $requerimiento->responsable_rh_externo }}
                    @else
                        <span style="color:#dc2626;font-weight:700;">⚠️ Sin asignar</span>
                    @endif
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Solicitud</div>
                <div class="data-value">{{ $requerimiento->fecha_solicitud->format('d/m/Y') }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">SLA Límite</div>
                <div class="data-value">45 días</div>
            </div>
            <div class="data-row">
                <div class="data-label">Semáforo</div>
                <div class="data-value">
                    @if ($diasTranscurridos > 60)
                        <span class="badge-estado badge-urgente">🔴 Crítico (&gt;60 días)</span>
                    @else
                        <span
                            style="background:#fef3c7;color:#92400e;display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                            🟠 En riesgo (46–60 días)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="highlight-box" style="border-left-color:#dc2626;background:#fef2f2;color:#7f1d1d;">
        ⏰ Este correo se enviará <strong>diariamente</strong> hasta que el requerimiento sea marcado como
        <strong>Contratado</strong> o <strong>Cancelado</strong>.
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button" style="background:linear-gradient(135deg,#991b1b,#dc2626);">
            Ver y Gestionar Ahora
        </a>
    </div>
@endsection
