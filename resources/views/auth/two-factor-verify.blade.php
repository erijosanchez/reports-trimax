@extends('layouts.app')

@section('title', 'Verificar 2FA')

@section('content')
    <div style="max-width:400px;margin:4rem auto;">
        <h2>Verificación 2FA</h2>

        <p>Ingresa el código de tu aplicación de autenticación:</p>

        <form method="POST" action="{{ route('2fa.verify') }}">
            @csrf

            <div style="margin-bottom:1rem;">
                <label>Código:</label>
                <input type="text" name="code" required autofocus style="width:100%;padding:0.5rem;margin-top:0.25rem;">
            </div>

            <button type="submit"
                style="width:100%;padding:0.75rem;background:#007bff;color:white;border:none;cursor:pointer;">
                Verificar
            </button>
        </form>
    </div>
@endsection
