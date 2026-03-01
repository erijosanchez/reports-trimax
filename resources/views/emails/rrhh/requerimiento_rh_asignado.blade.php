@extends('emails.layouts.trimax')

@section('badge', 'ASIGNACIÓN RH')

@section('alert_banner')
    <div class="email-alert email-alert--purple">
        👤 Has sido asignado como responsable de un proceso de selección.
    </div>
@endsection

@section('body')
    <p class="email-greeting">Hola, {{ $notifiable->name }}</p>
    <p class="email-intro">
        Se te ha asignado como <strong>Responsable RH</strong> del siguiente requerimiento de personal.
        El proceso de selección está ahora <strong>En Proceso</strong> a tu cargo.
    </p>

    <div class="data-card">
        <div class="data-card__header">📄 Requerimiento Asignado</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono">{{ $requerimiento->codigo }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado</div>
                <div class="data-value">
                    <span class="badge-estado badge-en-proceso">En Proceso</span>
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
                <div class="data-value">{{ $requerimiento->fecha_solicitud->format('d/m/Y') }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">SLA Máximo</div>
                <div class="data-value"><strong>45 días</strong> desde la fecha de solicitud</div>
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

    <div class="highlight-box">
        💡 Recuerda registrar los avances del proceso en el sistema CRM usando las etapas:
        Publicación de oferta → Revisión de CVs → Entrevistas → Evaluación → Oferta al candidato.
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">Gestionar Requerimiento</a>
    </div>
@endsection
