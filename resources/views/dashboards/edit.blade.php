@extends('layouts.app')

@section('title', 'Editar Dashboard')

@section('content')
    <div style="max-width:1000px;margin:0 auto;">
        <h1>Editar Dashboard: {{ $dashboard->name }}</h1>

        <!-- Tabs -->
        <div style="margin:2rem 0;border-bottom:2px solid #ddd;">
            <button onclick="showTab('info')" id="tab-info"
                style="padding:1rem 2rem;background:#007bff;color:white;border:none;cursor:pointer;margin-right:0.5rem;">
                Información
            </button>
            <button onclick="showTab('users')" id="tab-users"
                style="padding:1rem 2rem;background:#6c757d;color:white;border:none;cursor:pointer;">
                Asignar Usuarios ({{ $dashboard->users->count() }})
            </button>
        </div>

        <!-- TAB: Información del Dashboard -->
        <div id="content-info" style="display:block;">
            <form method="POST" action="{{ route('dashboards.update', $dashboard->id) }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                        Nombre del Dashboard *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $dashboard->name) }}" required
                        style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                        Descripción
                    </label>
                    <textarea name="description" rows="3" style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">{{ old('description', $dashboard->description) }}</textarea>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:bold;">
                        Link de Power BI *
                    </label>
                    <input type="url" name="powerbi_link" value="{{ old('powerbi_link', $dashboard->decrypted_link) }}"
                        required style="width:100%;padding:0.75rem;border:1px solid #ddd;border-radius:4px;">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:flex;align-items:center;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $dashboard->is_active) ? 'checked' : '' }} style="margin-right:0.5rem;">
                        <span>Dashboard Activo</span>
                    </label>
                </div>

                <div style="display:flex;gap:1rem;">
                    <button type="submit"
                        style="padding:0.75rem 2rem;background:#007bff;color:white;border:none;cursor:pointer;border-radius:4px;">
                        Actualizar Dashboard
                    </button>

                    <a href="{{ route('dashboards.index') }}"
                        style="padding:0.75rem 2rem;background:#6c757d;color:white;text-decoration:none;border-radius:4px;display:inline-block;">
                        Volver
                    </a>
                </div>
            </form>

            @if (auth()->user()->isSuperAdmin())
                <div style="margin-top:3rem;padding-top:2rem;border-top:2px solid #ddd;">
                    <h3 style="color:#dc3545;">Zona de Peligro</h3>
                    <form method="POST" action="{{ route('dashboards.destroy', $dashboard->id) }}"
                        onsubmit="return confirm('¿Estás seguro de eliminar este dashboard? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            style="padding:0.75rem 2rem;background:#dc3545;color:white;border:none;cursor:pointer;border-radius:4px;">
                            Eliminar Dashboard
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- TAB: Asignar Usuarios -->
        <div id="content-users" style="display:none;">
            <form method="POST" action="{{ route('dashboards.assign-users', $dashboard->id) }}">
                @csrf

                <div style="margin-bottom:1rem;">
                    <label
                        style="display:flex;align-items:center;cursor:pointer;padding:0.5rem;background:#f8f9fa;border-radius:4px;">
                        <input type="checkbox" id="select-all" onclick="toggleAll(this)" style="margin-right:0.5rem;">
                        <strong>Seleccionar Todos</strong>
                    </label>
                </div>

                <div style="max-height:500px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:1rem;">
                    @foreach ($allUsers as $user)
                        <label
                            style="display:flex;align-items:center;padding:0.75rem;cursor:pointer;border-bottom:1px solid #eee;">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}" class="user-checkbox"
                                {{ in_array($user->id, $assignedUserIds) ? 'checked' : '' }} style="margin-right:1rem;">

                            <div style="flex:1;">
                                <strong>{{ $user->name }}</strong>
                                <span style="color:#666;margin-left:0.5rem;">{{ $user->email }}</span>
                            </div>

                            <div>
                                @foreach ($user->roles as $role)
                                    <span
                                        style="padding:0.25rem 0.5rem;background:#007bff;color:white;border-radius:3px;font-size:0.75rem;margin-left:0.25rem;">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </label>
                    @endforeach
                </div>

                <div style="margin-top:2rem;padding:1rem;background:#f8f9fa;border-radius:4px;">
                    <strong>Total seleccionados:</strong> <span id="selected-count">{{ count($assignedUserIds) }}</span>
                    usuarios
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit"
                        style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;border-radius:4px;">
                        Guardar Asignaciones
                    </button>

                    <a href="{{ route('dashboards.index') }}"
                        style="padding:0.75rem 2rem;background:#6c757d;color:white;text-decoration:none;border-radius:4px;display:inline-block;">
                        Volver
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tab) {
            // Ocultar todos los contenidos
            document.getElementById('content-info').style.display = 'none';
            document.getElementById('content-users').style.display = 'none';

            // Resetear estilos de tabs
            document.getElementById('tab-info').style.background = '#6c757d';
            document.getElementById('tab-users').style.background = '#6c757d';

            // Mostrar tab seleccionado
            document.getElementById('content-' + tab).style.display = 'block';
            document.getElementById('tab-' + tab).style.background = '#007bff';
        }

        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
            updateCount();
        }

        // Actualizar contador cuando cambian los checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateCount);
            });
            updateCount();
        });

        function updateCount() {
            const checked = document.querySelectorAll('.user-checkbox:checked').length;
            document.getElementById('selected-count').textContent = checked;
        }
    </script>
@endsection
