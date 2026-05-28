<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trimax CRM — Acceso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/fv.png') }}" type="image/x-icon">
</head>

<body>
    <div class="login-wrapper">

        {{-- ── Panel Izquierdo: Branding ── --}}
        <div class="panel-brand">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="grid-overlay"></div>

            <div class="brand-content">
                <img src="{{ asset('assets/img/logoblanco.png') }}"
                     alt="Laboratorio Trimax Óptico"
                     class="brand-logo">

                <div class="brand-text">
                    <h1 class="brand-headline">
                        Inteligencia<br>
                        <span class="brand-gradient">Comercial</span>
                    </h1>
                    <p class="brand-tagline">
                        Plataforma empresarial de reportes, dashboards
                        y gestión operativa en tiempo real.
                    </p>
                </div>

                <div class="brand-stats">
                    <div class="stat-item">
                        <span class="stat-value">+20</span>
                        <span class="stat-label">Sedes</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-value">
                            <i class="mdi mdi-circle pulse-dot"></i> Vivo
                        </span>
                        <span class="stat-label">Datos</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-value">SSL</span>
                        <span class="stat-label">Cifrado</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Panel Derecho: Formulario ── --}}
        <div class="panel-form">
            <div class="form-card">

                <div class="form-header">
                    <span class="form-badge">
                        <i class="mdi mdi-shield-check"></i> Sistema Interno
                    </span>
                    <h2 class="form-title">Bienvenido de vuelta</h2>
                    <p class="form-subtitle">Ingresa tus credenciales para acceder al sistema</p>
                </div>

                @if ($errors->any())
                <div class="alert-error">
                    <i class="mdi-alert-circle-outline mdi"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="field-group">
                        <label class="field-label" for="email">Correo electrónico</label>
                        <div class="field-wrap">
                            <i class="mdi-email-outline mdi field-icon"></i>
                            <input type="email" id="email" name="email"
                                   class="field-input"
                                   placeholder="usuario@trimaxperu.com"
                                   value="{{ old('email') }}"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field-top">
                            <label class="field-label" for="password">Contraseña</label>
                            <a href="#" class="link-forgot"
                               onclick="alert('Contacta con el administrador del sistema para recuperar tu contraseña.'); return false;">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        <div class="field-wrap">
                            <i class="mdi-lock-outline mdi field-icon"></i>
                            <input type="password" id="password" name="password"
                                   class="field-input"
                                   placeholder="••••••••" required>
                            <button type="button" id="togglePassword" class="field-eye">
                                <i class="mdi-eye-outline mdi"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-row">
                        <label class="check-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="check-box"><i class="mdi mdi-check"></i></span>
                            <span>Mantener sesión iniciada</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">Ingresar al sistema</span>
                        <span class="btn-arrow">
                            <i class="mdi-arrow-right mdi"></i>
                        </span>
                    </button>
                </form>

                <p class="form-footer">
                    &copy; {{ date('Y') }} Laboratorio Trimax Óptico &middot; Todos los derechos reservados
                </p>
            </div>
        </div>

    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input   = document.getElementById('password');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            this.querySelector('i').className = visible
                ? 'mdi mdi-eye-outline'
                : 'mdi mdi-eye-off-outline';
        });

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.querySelector('.btn-text').textContent = 'Verificando credenciales...';
            btn.querySelector('.btn-arrow i').className = 'mdi mdi-loading mdi-spin';
            btn.disabled = true;
        });
    </script>
</body>

</html>
