<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Control</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>

<body>
    <!-- Background animation -->
    <div class="bg-animation">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Login -->
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo-container">
                <div class="logo">
                    <img src="{{ asset('assets/img/ltr.png') }}" style="width: 18rem" alt="">
                </div>
                <h1 class="welcome-text">Inteligencia Comercial</h1>
                <p class="welcome-subtext">Ingresa tus credenciales para continuar</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" class="form-input" placeholder="tu@ejemplo.com" value="{{ old('email') }}" name="email" required>
                        <span class="input-icon">📧</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Ingresa tu contraseña"
                            required>
                        <span class="input-icon">🔒</span>
                        <button type="button" class="toggle-password" id="togglePassword">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    <a href="#" class="forgot-link"
                        onclick="alert('Funcionalidad de recuperación'); return false;">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="submit-btn">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>


    <script>
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });

        // Input validation on blur
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        passwordInput.addEventListener('blur', function() {
            if (this.value && this.value.length < 6) {
                this.classList.add('error');
                setTimeout(() => this.classList.remove('error'), 500);
            } else if (this.value) {
                this.classList.add('success');
                setTimeout(() => this.classList.remove('success'), 300);
            }
        });

        // Floating label effect
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.querySelector('.form-label').style.transform =
                    'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.parentElement.querySelector('.form-label').style.transform = '';
            });
        });
    </script>
</body>

</html>
