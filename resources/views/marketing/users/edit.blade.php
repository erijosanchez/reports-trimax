<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - TRIMAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1565C0;
        }

        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: var(--primary-color);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-size: 28px;
            font-weight: 900;
        }

        .main-content {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .form-card h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: border-color 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(21, 101, 192, 0.25);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #0D47A1;
        }

        .alert-danger {
            border-left: 4px solid #F44336;
            background: #FFEBEE;
            color: #C62828;
        }

        .location-field {
            display: none;
        }

        .location-field.show {
            display: block;
        }

        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 8px;
            transition: all 0.3s;
        }

        .strength-weak { background: #F44336; width: 33%; }
        .strength-medium { background: #FF9800; width: 66%; }
        .strength-strong { background: #4CAF50; width: 100%; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
            <span class="navbar-brand">TRIMAX Admin</span>
        </div>
    </nav>

    <div class="main-content">
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-pencil-fill"></i> Editar Usuario</h2>
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            @if($errors->any())
            <div class="alert alert-danger">
                <strong><i class="bi bi-exclamation-triangle"></i> Errores de validación:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user->id) }}" id="createUserForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Nombre Completo *
                        </label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="Ej: Juan Pérez Consultor">
                        <small class="text-muted">Nombre que aparecerá en las encuestas</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Email *
                        </label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required placeholder="ejemplo@trimax.com">
                        <small class="text-muted">Email único para acceso al sistema</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock"></i> Nueva Contraseña (opcional)
                        </label>
                        <input type="password" name="password" id="password" class="form-control" minlength="8" placeholder="Dejar en blanco para mantener">
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted" id="strengthText"></small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock-fill"></i> Confirmar Nueva Contraseña
                        </label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" placeholder="Confirmar si cambia contraseña">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-tag"></i> Tipo de Usuario *
                        </label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="consultor" {{ old('role', $user->role) == 'consultor' ? 'selected' : '' }}>Consultor</option>
                            <option value="sede" {{ old('role', $user->role) == 'sede' ? 'selected' : '' }}>Sede</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3 location-field {{ $user->role == 'sede' ? 'show' : '' }}" id="locationField">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i> Ubicación *
                        </label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $user->location) }}" placeholder="Ej: Lima Centro, Arequipa">
                        <small class="text-muted">Requerido solo para sedes</small>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <strong><i class="bi bi-info-circle"></i> Información:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Si no cambias la contraseña, se mantendrá la actual</li>
                        <li>El <strong>link de encuesta</strong> no cambiará al editar</li>
                        <li>Las estadísticas y encuestas se mantendrán intactas</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campo de ubicación
        document.getElementById('role').addEventListener('change', function() {
            const locationField = document.getElementById('locationField');
            if (this.value === 'sede') {
                locationField.classList.add('show');
                locationField.querySelector('input').required = true;
            } else {
                locationField.classList.remove('show');
                locationField.querySelector('input').required = false;
                locationField.querySelector('input').value = '';
            }
        });

        // Verificar estado inicial
        if (document.getElementById('role').value === 'sede') {
            document.getElementById('locationField').classList.add('show');
        }

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Contraseña débil';
                strengthText.style.color = '#F44336';
            } else if (strength == 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Contraseña media';
                strengthText.style.color = '#FF9800';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Contraseña fuerte';
                strengthText.style.color = '#4CAF50';
            }
        });

        // Validación antes de enviar
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const password = document.querySelector('[name="password"]').value;
            const passwordConfirm = document.querySelector('[name="password_confirmation"]').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
