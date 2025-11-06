@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div style="max-width:400px;margin:4rem auto;">
    <h2>Iniciar Sesión</h2>
    
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div style="margin-bottom:1rem;">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" required 
                   style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>Contraseña:</label>
            <input type="password" name="password" required 
                   style="width:100%;padding:0.5rem;margin-top:0.25rem;">
        </div>

        <div style="margin-bottom:1rem;">
            <label>
                <input type="checkbox" name="remember"> Recordarme
            </label>
        </div>

        <button type="submit" style="width:100%;padding:0.75rem;background:#007bff;color:white;border:none;cursor:pointer;">
            Iniciar Sesión
        </button>
    </form>
</div>
@endsection