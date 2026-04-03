@extends('emails.layouts.productividad')

@section('badge', $esEdicion ? 'ACTUALIZACIÓN DE REPORTE' : 'NUEVO REPORTE')

@section('alert_banner')
    <div class="email-alert {{ $esEdicion ? 'email-alert--warning' : 'email-alert--success' }}">
        {{ $esEdicion ? '✏️' : '💬' }}
        {{ $esEdicion ? 'Se actualizó un reporte de Comentarios' : 'Nuevo reporte de Comentarios recibido' }}
    </div>
@endsection

@section('body')
    <p class="email-greeting">Estimado(a),</p>
    <p class="email-intro">
        @if ($esEdicion)
            Se ha <strong>editado</strong> el reporte de Comentarios de la sede <strong>{{ $reporte->sede }}</strong>.
            @if ($reporte->editado_tarde)
                <br><span style="color:#dc2626;font-weight:600;">⚠️ Edición realizada después del límite (jueves 2:00 PM). KPI afectado.</span>
            @endif
        @else
            Se recibió el <strong>reporte semanal de Comentarios</strong> de la sede <strong>{{ $reporte->sede }}</strong>.
        @endif
    </p>

    <div class="data-card">
        <div class="data-card__header">Detalle del Reporte</div>
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
                <div class="data-label">Período</div>
                <div class="data-value">{{ $reporte->semana_inicio?->format('d/m/Y') }} — {{ $reporte->semana_fin?->format('d/m/Y') }}</div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Límite</div>
                <div class="data-value">{{ $reporte->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i') }} hrs</div>
            </div>
            <div class="data-row">
                <div class="data-label">{{ $esEdicion ? 'Fecha Edición' : 'Fecha Envío' }}</div>
                <div class="data-value">
                    {{ ($esEdicion ? $reporte->fecha_ultimo_envio : $reporte->fecha_envio_original)?->setTimezone('America/Lima')->format('d/m/Y H:i') }} hrs
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">KPI</div>
                <div class="data-value">
                    @php $kpi = (float)$reporte->kpi_porcentaje; @endphp
                    <span style="font-weight:700;font-size:16px;color:{{ $kpi>=100?'#059669':($kpi>=75?'#d97706':($kpi>=50?'#0891b2':'#dc2626')) }}">
                        {{ $reporte->kpiLabel() }}
                    </span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Archivos</div>
                <div class="data-value">{{ count($reporte->archivos ?? []) }} archivo(s)</div>
            </div>
            @if ($reporte->notas)
            <div class="data-row">
                <div class="data-label">Notas</div>
                <div class="data-value">{{ $reporte->notas }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="highlight-box">
        💡 <strong>KPI Comentarios:</strong> Jueves = 100% · Viernes = 75% · Sábado = 50% · 3+ días = 0%
    </div>

    <div class="cta-wrapper">
        <a href="{{ $url }}" class="cta-button">Ver en el Sistema</a>
    </div>

    <p style="font-size:12px;color:#94a3b8;text-align:center;">
        Enviado por: {{ $reporte->user?->name ?? 'Sistema' }}
    </p>
@endsection
