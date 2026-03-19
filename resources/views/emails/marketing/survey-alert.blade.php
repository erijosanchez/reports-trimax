@extends('emails.layouts.marketing')

@section('badge', 'ALERTA MARKETING')

@section('alert_banner')
    <div
        class="email-alert {{ $survey->experience_rating === 1 || $survey->service_quality_rating === 1 ? 'email-alert--danger' : 'email-alert--warning' }}">
        {{ $survey->experience_rating === 1 || $survey->service_quality_rating === 1 ? '🔴' : '🟡' }}
        Encuesta con calificación negativa recibida
    </div>
@endsection

@section('body')

    <p class="email-greeting">¡Hola {{ $notifiable->name }}!</p>
    <p class="email-intro">
        Se registró una nueva encuesta con calificación baja en el sistema de marketing.
        Te notificamos para que puedas tomar acción a tiempo.
    </p>

    {{-- Evaluado --}}
    <div class="data-card">
        <div class="data-card__header">👤 Evaluado</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Nombre</div>
                <div class="data-value"><strong>{{ $evaluado->name }}</strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Tipo</div>
                <div class="data-value">
                    @if ($evaluado->role === 'consultor')
                        Consultor
                    @elseif($evaluado->role === 'trimax')
                        TRIMAX General
                    @else
                        Sede — {{ $evaluado->location }}
                    @endif
                </div>
            </div>
            @if ($evaluado->location && $evaluado->role === 'sede')
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
            @php
                $labels = [1 => 'Muy Insatisfecho 😞', 2 => 'Insatisfecho 😐', 3 => 'Feliz 🙂', 4 => 'Muy Feliz 😊'];
                $combined = number_format(($survey->experience_rating + $survey->service_quality_rating) / 2, 2);
            @endphp
            <div class="data-row">
                <div class="data-label">Experiencia</div>
                <div class="data-value">
                    <span
                        class="badge-estado {{ $survey->experience_rating <= 1 ? 'badge-urgente' : ($survey->experience_rating === 2 ? 'badge-en-proceso' : 'badge-contratado') }}">
                        {{ $labels[$survey->experience_rating] ?? 'N/A' }}
                    </span>
                    <span
                        style="color:#64748b; font-size:12px; margin-left:6px;">({{ $survey->experience_rating }}/4)</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Atención</div>
                <div class="data-value">
                    <span
                        class="badge-estado {{ $survey->service_quality_rating <= 1 ? 'badge-urgente' : ($survey->service_quality_rating === 2 ? 'badge-en-proceso' : 'badge-contratado') }}">
                        {{ $labels[$survey->service_quality_rating] ?? 'N/A' }}
                    </span>
                    <span
                        style="color:#64748b; font-size:12px; margin-left:6px;">({{ $survey->service_quality_rating }}/4)</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Promedio</div>
                <div class="data-value data-value--mono">{{ $combined }} / 4.00</div>
            </div>
        </div>
    </div>

    {{-- Cliente --}}
    <div class="data-card">
        <div class="data-card__header">🧑 Datos del cliente</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Nombre</div>
                <div class="data-value">{{ $survey->client_name ?: 'Anónimo' }}</div>
            </div>
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
