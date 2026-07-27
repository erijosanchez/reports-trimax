@extends('emails.layouts.trimax')

@php
    $alertClass = match ($tipo) {
        'cambio_estado' => match ($estadoNuevo) {
            'Contratado' => 'email-alert--success',
            'Cancelado' => 'email-alert--warning',
            default => 'email-alert--info',
        },
        'asignacion_rh' => 'email-alert--purple',
        'etapa' => 'email-alert--info',
        default => 'email-alert--info',
    };

    $alertIcon = match ($tipo) {
        'cambio_estado' => match ($estadoNuevo) {
            'Contratado' => '✅',
            'Cancelado' => '🚫',
            default => '🔄',
        },
        'asignacion_rh' => '👤',
        'etapa' => '📌',
        default => '💬',
    };

    $alertText = match ($tipo) {
        'cambio_estado' => "El estado del requerimiento fue actualizado a <strong>{$estadoNuevo}</strong>.",
        'asignacion_rh' => 'Se asignó un responsable RH al requerimiento.',
        'etapa' => 'Se registró un nuevo avance en el proceso de selección.',
        default => 'Hay una actualización en el requerimiento.',
    };

    $badgeLabel = match ($tipo) {
        'cambio_estado' => 'CAMBIO DE ESTADO',
        'asignacion_rh' => 'RESPONSABLE ASIGNADO',
        'etapa' => 'AVANCE DEL PROCESO',
        default => 'ACTUALIZACIÓN',
    };
@endphp

@section('badge', $badgeLabel)

@section('alert_banner')
    <div class="email-alert {{ $alertClass }}">
        {{ $alertIcon }} {!! $alertText !!}
    </div>
@endsection

@section('body')
    <p class="email-greeting">Hola, {{ $notifiable->name }}</p>

    {{-- Descripción según tipo --}}
    @if ($tipo === 'cambio_estado')
        <p class="email-intro">
            El requerimiento <strong>{{ $requerimiento->codigo }}</strong> ha cambiado de estado.
        </p>
        {{-- Cambio de estado visual --}}
        <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin:20px 0;">
            @php
                $bc1 = match ($estadoAnterior) {
                    'Pendiente' => 'badge-pendiente',
                    'En Proceso' => 'badge-en-proceso',
                    'Contratado' => 'badge-contratado',
                    'Cancelado' => 'badge-cancelado',
                    default => 'badge-regular',
                };
                $bc2 = match ($estadoNuevo) {
                    'Pendiente' => 'badge-pendiente',
                    'En Proceso' => 'badge-en-proceso',
                    'Contratado' => 'badge-contratado',
                    'Cancelado' => 'badge-cancelado',
                    default => 'badge-regular',
                };
            @endphp
            <span class="badge-estado {{ $bc1 }}"
                style="font-size:13px;padding:6px 16px;">{{ $estadoAnterior }}</span>
            <span style="font-size:18px;color:#94a3b8;">→</span>
            <span class="badge-estado {{ $bc2 }}" style="font-size:13px;padding:6px 16px;">{{ $estadoNuevo }}</span>
        </div>
    @elseif($tipo === 'asignacion_rh')
        <p class="email-intro">
            El requerimiento <strong>{{ $requerimiento->codigo }}</strong> tiene un responsable RH asignado y
            su proceso ha <strong>iniciado oficialmente</strong>.
        </p>
        <div class="highlight-box">
            👤 <strong>Responsable asignado:</strong> {{ $extra }}
        </div>
    @elseif($tipo === 'etapa')
        <p class="email-intro">
            El responsable RH registró un nuevo avance en el proceso de selección del requerimiento
            <strong>{{ $requerimiento->codigo }}</strong>.
        </p>
        <div class="highlight-box">
            📌 {{ $extra }}
        </div>
    @endif

    {{-- Datos del requerimiento --}}
    <div class="data-card">
        <div class="data-card__header">📄 Datos del Requerimiento</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono">{{ $requerimiento->codigo }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado Actual</div>
                <div class="data-value">
                    <span class="badge-estado {{ $bc2 ?? 'badge-en-proceso' }}">
                        {{ $requerimiento->estado }}
                    </span>
                </div>
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
                        <span style="color:#94a3b8;">Sin asignar</span>
                    @endif
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Solicitud</div>
                <div class="data-value">{{ $requerimiento->fecha_solicitud->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">Ver Detalle en el CRM</a>
    </div>
@endsection
