@extends('layouts.app')

@section('title', 'Avisos')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-0 rate-percentage">
                            <i class="me-2 text-primary mdi mdi-bullhorn"></i>Avisos
                        </h3>
                        <p class="mt-1 mb-0 text-muted">Avisos manuales dentro del sistema — llegan por la
                            campanita de notificaciones, nunca por correo.</p>
                    </div>
                    <button type="button" class="text-white btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalNuevoAviso">
                        <i class="mdi mdi-plus me-1"></i>Nuevo aviso
                    </button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="grid-margin card">
                    <div class="card-body">
                        <h4 class="mb-4 card-title"><i class="me-2 text-primary mdi mdi-history"></i>Historial
                            de avisos</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Mensaje</th>
                                        <th>Destinatarios</th>
                                        <th class="text-center">Enviado a</th>
                                        <th>Enviado por</th>
                                        <th>Fecha</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($avisos as $aviso)
                                        <tr>
                                            <td><strong>{{ $aviso->titulo }}</strong></td>
                                            <td><small class="text-muted">{{ Str::limit($aviso->mensaje, 60) }}</small></td>
                                            <td><span class="badge badge-info">{{ $aviso->destinatariosLabel() }}</span></td>
                                            <td class="text-center">{{ $aviso->total_destinatarios }}</td>
                                            <td>{{ $aviso->creador->name ?? '—' }}</td>
                                            <td><small class="text-muted">{{ $aviso->created_at->format('d/m/Y H:i') }}</small></td>
                                            <td class="text-center">
                                                @if ($aviso->user_id === auth()->id() || auth()->user()->isSuperAdmin())
                                                    <form method="POST" action="{{ route('avisos.destroy', $aviso->id) }}"
                                                        onsubmit="return confirm('¿Eliminar este aviso del historial? No se puede deshacer.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-4 text-muted text-center">Todavía no se ha
                                                enviado ningún aviso.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal: Nuevo aviso --}}
    <div class="modal fade" id="modalNuevoAviso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('avisos.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="me-1 mdi mdi-bullhorn"></i>Nuevo aviso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" name="titulo" maxlength="150" required
                                placeholder="Ej: Corte de sistema programado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje</label>
                            <textarea class="form-control" name="mensaje" rows="14" maxlength="2000" required
                                placeholder="Escribe el aviso..." style="min-height: 320px;"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Destinatarios</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="todos" value="1" id="aviso-todos" checked>
                                <label class="form-check-label fw-semibold" for="aviso-todos">
                                    Todos los usuarios
                                </label>
                            </div>
                        </div>
                        <div id="aviso-roles-wrapper" class="ps-1" style="display:none;">
                            <p class="mb-1 text-muted small">O elige roles específicos:</p>
                            <div class="row">
                                @foreach ($roles as $rol)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input aviso-rol"
                                                name="roles[]" value="{{ $rol }}" id="aviso-rol-{{ $rol }}">
                                            <label class="form-check-label" for="aviso-rol-{{ $rol }}">
                                                {{ ucfirst($rol) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="mb-0 mt-2 text-muted small">
                            <i class="mdi mdi-information-outline me-1"></i>Solo llega por la campanita de
                            notificaciones — no se envía correo.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="text-white btn btn-primary">
                            <i class="mdi mdi-send me-1"></i>Enviar aviso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const todosCheckbox = document.getElementById('aviso-todos');
            const rolesWrapper = document.getElementById('aviso-roles-wrapper');
            const rolesCheckboxes = document.querySelectorAll('.aviso-rol');

            function actualizarVisibilidadRoles() {
                rolesWrapper.style.display = todosCheckbox.checked ? 'none' : 'block';
                if (todosCheckbox.checked) {
                    rolesCheckboxes.forEach(cb => cb.checked = false);
                }
            }

            todosCheckbox.addEventListener('change', actualizarVisibilidadRoles);
            rolesCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    if (cb.checked) {
                        todosCheckbox.checked = false;
                        rolesWrapper.style.display = 'block';
                    }
                });
            });

            actualizarVisibilidadRoles();
        });
    </script>
@endpush
