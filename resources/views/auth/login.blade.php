@extends('layouts.app')

@section('title', 'Login')

@section('content')
<h1>Iniciar Sesión</h1>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div>
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit">Entrar</button>
</form>
<p>Usuario de prueba: admin@example.com / password: admin123</p>
@endsection