@extends('layouts.app')

@section('title', 'Configurar 2FA')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-6 col-xl-5 mx-auto grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-1">
                            <i class="mdi mdi-shield-lock-outline text-primary me-2"></i>
                            Configurar autenticación en dos pasos
                        </h4>
                        <p class="text-muted mb-4">
                            Escanea este código con Google Authenticator, Authy o cualquier app
                            compatible con TOTP.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first('code') }}</div>
                        @endif

                        <div class="text-center mb-4">
                            {!! $qrCodeSvg !!}
                        </div>

                        <p class="text-muted small mb-4">
                            ¿No puedes escanear? Ingresa este código manualmente:
                            <br><code class="fs-6">{{ $secret }}</code>
                        </p>

                        <form method="POST" action="{{ route('2fa.enable') }}">
                            @csrf
                            <input type="hidden" name="secret" value="{{ $secret }}">

                            <div class="mb-3">
                                <label class="form-label">Código de verificación</label>
                                <input type="text" name="code" class="form-control" inputmode="numeric"
                                    autocomplete="one-time-code" maxlength="6" autofocus required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Habilitar 2FA
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
