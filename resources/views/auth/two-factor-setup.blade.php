@extends('layouts.app')

@section('title', 'Configurar 2FA')

@section('content')
    <div style="max-width:600px;margin:2rem auto;">
        <h2>Configurar Autenticación de Dos Factores</h2>

        <p>Escanea este código QR con tu app de autenticación (Google Authenticator, Authy, etc.):</p>

        <div style="text-align:center;margin:2rem 0;">
            <img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={{ urlencode($qrCodeUrl) }}"
                alt="QR Code">
        </div>

        <p>O ingresa este código manualmente: <strong>{{ $secret }}</strong></p>

        <form method="POST" action="{{ route('2fa.enable') }}">
            @csrf
            <input type="hidden" name="secret" value="{{ $secret }}">

            <div style="margin-bottom:1rem;">
                <label>Código de verificación:</label>
                <input type="text" name="code" required style="width:100%;padding:0.5rem;margin-top:0.25rem;">
            </div>

            <button type="submit" style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;">
                Habilitar 2FA
            </button>
        </form>
    </div>
@endsection
