@extends('layouts.app')

@section('title', 'Códigos de recuperación 2FA')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-6 col-xl-5 mx-auto grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-1">
                            <i class="mdi mdi-key-variant text-warning me-2"></i>
                            Guarda tus códigos de recuperación
                        </h4>
                        <p class="text-muted mb-4">
                            2FA ya está habilitado. Si pierdes el acceso a tu app de autenticación,
                            cada uno de estos códigos te permite entrar <strong>una sola vez</strong>.
                            Solo se muestran ahora — guárdalos en un lugar seguro (gestor de
                            contraseñas, papel). Si los pierdes, pide a un administrador que
                            reinicie tu 2FA.
                        </p>

                        <div class="bg-light rounded p-3 mb-4 text-center" style="font-family:monospace; letter-spacing:0.05em;">
                            @foreach ($codigos as $codigo)
                                <div class="py-1 fs-5">{{ $codigo }}</div>
                            @endforeach
                        </div>

                        <a href="{{ route('home') }}" class="btn btn-primary w-100">
                            Ya los guardé, continuar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
