@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="login-container">
        <div class="login-left">
            <div class="logo">
                <img src="assets/img/LOGOTIPO TRIMAX 2025-01.png" alt="Logo Trimax">
            </div>
            <div class="welcome-text">
                <h2>Bienvenido de Consultor</h2>
                <p>Ingresa tus credenciales para acceder a tu cuenta y continuar con tu experiencia.</p>
            </div>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h3>Iniciar Sesión</h3>
            </div>

            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required>
                        <span class="input-icon">📧</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <span class="input-icon" id="togglePassword">👁️</span>
                    </div>
                </div>

                <button type="submit" class="login-button">Iniciar Sesión</button>
            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
@endsection