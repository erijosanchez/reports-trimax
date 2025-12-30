<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trimax Óptico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fv.png') }}" type="image/x-icon">
</head>

<body>
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="brand-panel">
            <div class="logo-wrapper">
                <img src="{{ asset('assets/img/logoblanco.png') }}" alt="Laboratorio Trimax Óptico">
                <h1 class="brand-title">Inteligencia Comercial</h1>
                <p class="brand-subtitle">Laboratorio Óptico Trimax</p>
            </div>

            <ul class="features-list">
                <li>
                    <i class="mdi mdi-chart-line"></i>
                    <span>Dashboards interactivos en tiempo real</span>
                </li>
                <li>
                    <i class="mdi mdi-shield-check"></i>
                    <span>Seguridad y encriptación de datos</span>
                </li>
                <li>
                    <i class="mdi mdi-chart-areaspline"></i>
                    <span>Análisis avanzado de métricas</span>
                </li>
                <li>
                    <i class="mdi mdi-account-multiple"></i>
                    <span>Acceso multi-usuario controlado</span>
                </li>
            </ul>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para acceder al sistema</p>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    <strong>Error:</strong> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <i class="mdi mdi-email-outline"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               placeholder="tu@ejemplo.com" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="mdi mdi-lock-outline"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Ingresa tu contraseña"
                               required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="mdi mdi-eye-outline"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    <a href="#" class="forgot-link" onclick="alert('Contacta con el administrador del sistema para recuperar tu contraseña.'); return false;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Iniciar Sesión
                </button>

                <div class="form-footer">
                    <p>&copy; {{ date('Y') }} Laboratorio Trimax Óptico. Todos los derechos reservados.</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submitBtn');
        const loginForm = document.getElementById('loginForm');

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.className = 'mdi mdi-eye-outline';
            } else {
                icon.className = 'mdi mdi-eye-off-outline';
            }
        });

        // Email validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && emailRegex.test(this.value)) {
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });

        // Form submission loading state
        loginForm.addEventListener('submit', function(e) {
            submitBtn.classList.add('loading');
            submitBtn.textContent = 'Iniciando sesión...';
        });

        // Auto-focus on first input
        window.addEventListener('load', function() {
            emailInput.focus();
        });
    </script>
</body>

</html>