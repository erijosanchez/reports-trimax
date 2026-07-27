@extends('layouts.app')

@section('title', 'Verificar 2FA')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-5 col-xl-4 mx-auto grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-1">
                            <i class="mdi mdi-shield-key-outline text-primary me-2"></i>
                            Verificación en dos pasos
                        </h4>
                        <p class="text-muted mb-4">
                            Ingresa el código de tu app de autenticación, o uno de tus códigos de
                            recuperación si no tienes acceso a ella.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first('code') }}</div>
                        @endif

                        <form method="POST" action="{{ route('2fa.verify') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Código</label>
                                <input type="text" name="code" class="form-control"
                                    autocomplete="one-time-code" autofocus required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Verificar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
