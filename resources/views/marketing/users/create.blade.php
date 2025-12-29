@extends('layouts.app')

@section('title', 'Crear Usuario')

@push('styles')
    <style>
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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

        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: border-color 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
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
    </style>
@endpush

@section('content')

<body>

    <div class="main-content">
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-person-plus-fill"></i> Crear Nuevo Usuario</h2>
                <a href="{{ route('marketing.users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-triangle"></i> Errores de validación:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('marketing.users.store') }}" id="createUserForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person"></i> Nombre Completo *
                    </label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                        placeholder="Ej: Juan Pérez - Consultor Lima">
                    <small class="text-muted">Nombre que aparecerá en las encuestas</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-tag"></i> Tipo de Usuario *
                    </label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <option value="consultor" {{ old('role') == 'consultor' ? 'selected' : '' }}>Consultor</option>
                        <option value="sede" {{ old('role') == 'sede' ? 'selected' : '' }}>Sede</option>
                    </select>
                </div>

                <div class="mb-3 location-field" id="locationField">
                    <label class="form-label">
                        <i class="bi bi-geo-alt"></i> Ubicación *
                    </label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}"
                        placeholder="Ej: Lima Centro, Arequipa">
                    <small class="text-muted">Requerido solo para sedes</small>
                </div>

                <div class="alert alert-info mt-4">
                    <strong><i class="bi bi-info-circle"></i> Información:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Este es un usuario <strong>referencial</strong> solo para encuestas</li>
                        <li>Se generará automáticamente un <strong>link único</strong> de encuesta</li>
                        <li>El usuario estará <strong>activo</strong> por defecto</li>
                        <li>No requiere email ni contraseña</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Crear Usuario
                    </button>
                    <a href="{{ route('marketing.users.index') }}" class="btn btn-outline-secondary">
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
            const locationInput = locationField.querySelector('input');

            if (this.value === 'sede') {
                locationField.classList.add('show');
                locationInput.required = true;
            } else {
                locationField.classList.remove('show');
                locationInput.required = false;
                locationInput.value = '';
            }
        });

        // Verificar estado inicial
        if (document.getElementById('role').value === 'sede') {
            document.getElementById('locationField').classList.add('show');
        }
    </script>
</body>

</html>

@endsection
