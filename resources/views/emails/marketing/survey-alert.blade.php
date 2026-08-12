@extends('emails.layouts.marketing')

@section('badge', 'ALERTA MARKETING')

@section('alert_banner')
    <div class="email-alert {{ $peorRating === 1 ? 'email-alert--danger' : 'email-alert--warning' }}">
        {{ $peorRating === 1 ? '🔴' : '🟡' }}
        Encuesta con calificación negativa recibida
    </div>
@endsection

@section('body')

    <p class="email-greeting">¡Hola {{ $notifiable->name }}!</p>
    <p class="email-intro">
        Se registró una nueva encuesta con calificación baja en el sistema de marketing.
        Te notificamos para que puedas tomar acción a tiempo.
    </p>

    {{-- Sede --}}
    <div class="data-card">
        <div class="data-card__header">📍 Sede</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Nombre</div>
                <div class="data-value"><strong>{{ $evaluado->name }}</strong></div>
            </div>
            @if ($evaluado->role === 'trimax')
                <div class="data-row">
                    <div class="data-label">Tipo</div>
                    <div class="data-value">TRIMAX General</div>
                </div>
            @elseif ($evaluado->location)
                <div class="data-row">
                    <div class="data-label">Ubicación</div>
                    <div class="data-value">{{ $evaluado->location }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Calificaciones --}}
    <div class="data-card">
        <div class="data-card__header">⭐ Calificaciones recibidas</div>
        <div class="data-card__body">
            @foreach ($preguntas as $p)
                <div class="data-row">
                    <div class="data-label">{{ $p['label'] }}</div>
                    <div class="data-value">
                        <span
                            class="badge-estado {{ $p['valor'] <= 1 ? 'badge-urgente' : ($p['valor'] === 2 ? 'badge-en-proceso' : 'badge-contratado') }}">
                            {{ $p['texto'] }}
                        </span>
                        <span style="color:#64748b; font-size:12px; margin-left:6px;">({{ $p['valor'] }}/4)</span>
                    </div>
                </div>
            @endforeach
            <div class="data-row">
                <div class="data-label">Promedio</div>
                <div class="data-value data-value--mono">{{ number_format($promedio, 2) }} / 4.00</div>
            </div>
        </div>
    </div>

    {{-- Cliente --}}
    <div class="data-card">
        <div class="data-card__header">🧑 Datos del cliente</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Razón social</div>
                <div class="data-value">{{ $survey->client_name ?: 'Anónimo' }}</div>
            </div>
            @if ($survey->ruc)
                <div class="data-row">
                    <div class="data-label">RUC</div>
                    <div class="data-value">{{ $survey->ruc }}</div>
                </div>
            @endif
            <div class="data-row">
                <div class="data-label">Fecha</div>
                <div class="data-value">{{ $survey->created_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Comentarios --}}
    @if ($survey->comments)
        <div class="highlight-box">
            💬 <strong>Comentario del cliente:</strong><br>
            "{{ $survey->comments }}"
        </div>
    @endif

    {{-- CTA --}}
    <div class="cta-wrapper">
        <a href="{{ url('/marketing') }}" class="cta-button">
            Ver Dashboard Marketing
        </a>
    </div>

    <hr class="divider">

    <p style="font-size:12px; color:#94a3b8; text-align:center;">
        Este correo se generó automáticamente porque se recibió una encuesta con calificación ≤ 2.<br>
    </p>

@endsection
