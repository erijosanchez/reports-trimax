@extends('layouts.app')
@section('title', 'Gestión de Motorizados')

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <div class="row">
                <div class="grid-margin col-lg-12 stretch-card">
                    <div class="card">
                        <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="me-2 text-primary mdi mdi-motorbike"></i>Motorizados
                                </h4>
                                <p class="mb-0 text-muted small">Gestión de conductores y sus credenciales de app</p>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                <i class="me-1 mdi mdi-plus"></i>Nuevo Motorizado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            {{-- Stats --}}
            <div class="mb-4 row">
                @php
                    $total = $motorizados->whereNull('deleted_at')->count();
                    $activos = $motorizados->where('estado', 'activo')->whereNull('deleted_at')->count();
                @endphp
                <div class="mb-3 col-md-4">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-body">
                            <div>
                                <p class="mb-1 text-muted">Total</p>
                                <h2 class="mb-0 text-primary fw-bold">{{ $total }}</h2>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-primary rounded-circle"
                                style="width:48px;height:48px">
                                <i class="text-white mdi mdi-motorbike" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-4">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-body">
                            <div>
                                <p class="mb-1 text-muted">Activos</p>
                                <h2 class="mb-0 text-success fw-bold">{{ $activos }}</h2>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-success rounded-circle"
                                style="width:48px;height:48px">
                                <i class="text-white mdi mdi-check-circle" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-4">
                    <div class="shadow-sm border-0 card">
                        <div class="d-flex align-items-center justify-content-between card-body">
                            <div>
                                <p class="mb-1 text-muted">Inactivos</p>
                                <h2 class="mb-0 text-warning fw-bold">{{ $total - $activos }}</h2>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-warning rounded-circle"
                                style="width:48px;height:48px">
                                <i class="text-white mdi mdi-pause-circle" style="font-size:22px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="row">
                <div class="col-12">
                    <div class="shadow-sm border-0 card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Sede</th>
                                            <th>Teléfono</th>
                                            <th>Email (app)</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($motorizados as $m)
                                            <tr class="{{ $m->trashed() ? 'table-secondary' : '' }}">
                                                <td class="text-muted small">{{ $m->id }}</td>
                                                <td class="fw-semibold">{{ $m->nombre }}</td>
                                                <td><span class="bg-primary badge">{{ $m->sede }}</span></td>
                                                <td class="small">{{ $m->telefono ?? '—' }}</td>
                                                <td class="text-muted small">{{ $m->email }}</td>
                                                <td>
                                                    @if ($m->trashed())
                                                        <span class="bg-dark badge">Eliminado</span>
                                                    @elseif($m->estado === 'activo')
                                                        <span class="bg-success badge">Activo</span>
                                                    @else
                                                        <span class="bg-warning text-dark badge">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @unless ($m->trashed())
                                                        <button class="btn-outline-warning btn btn-sm btn-editar"
                                                            data-id="{{ $m->id }}" data-nombre="{{ $m->nombre }}"
                                                            data-sede="{{ $m->sede }}"
                                                            data-telefono="{{ $m->telefono ?? '' }}"
                                                            data-email="{{ $m->email }}"
                                                            data-estado="{{ $m->estado }}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button class="btn-outline-danger btn btn-sm btn-eliminar"
                                                            data-id="{{ $m->id }}">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    @endunless
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-5 text-muted text-center">
                                                    <i class="d-block opacity-50 mb-2 mdi mdi-motorbike mdi-36px"></i>
                                                    No hay motorizados registrados
                                                </td>
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
    </div>

    {{-- Modal Crear --}}
    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog">
            <form id="form-crear">
                @csrf
                <div class="modal-content">
                    <div class="bg-primary text-white modal-header">
                        <h5 class="modal-title"><i class="me-1 mdi mdi-motorbike"></i>Nuevo Motorizado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
                            <select name="sede" class="form-select" required>
                                <option value="">Seleccionar</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" placeholder="987654321">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email (para login en app) <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres. El motorizado usará esto para entrar a la app.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="me-1 mdi-content-save mdi"></i>Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <form id="form-editar">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id">
                <div class="modal-content">
                    <div class="bg-warning text-dark modal-header">
                        <h5 class="modal-title"><i class="me-1 mdi mdi-pencil"></i>Editar Motorizado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
                            <select name="sede" id="edit-sede" class="form-select" required>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" id="edit-telefono" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nueva contraseña</label>
                            <input type="password" name="password" class="form-control" minlength="6">
                            <div class="form-text">Dejar vacío para no cambiarla.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" id="edit-estado" class="form-select">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="text-dark btn btn-warning">
                            <i class="me-1 mdi-content-save mdi"></i>Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        function toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
            el.style.zIndex = 9999;
            el.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        // Abrir editar
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-id').value = btn.dataset.id;
                document.getElementById('edit-nombre').value = btn.dataset.nombre;
                document.getElementById('edit-sede').value = btn.dataset.sede;
                document.getElementById('edit-telefono').value = btn.dataset.telefono;
                document.getElementById('edit-email').value = btn.dataset.email;
                document.getElementById('edit-estado').value = btn.dataset.estado;
                new bootstrap.Modal(document.getElementById('modalEditar')).show();
            });
        });

        // Crear
        document.getElementById('form-crear').addEventListener('submit', async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const btn = e.target.querySelector('[type=submit]');
            btn.disabled = true;
            const res = await fetch('/tracking/motorizados', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(fd)),
            });
            btn.disabled = false;
            if (res.ok) {
                toast('Motorizado creado ✓');
                setTimeout(() => location.reload(), 800);
            } else {
                const d = await res.json();
                toast(Object.values(d.errors ?? {}).flat()[0] || d.message || 'Error', 'danger');
            }
        });

        // Actualizar
        document.getElementById('form-editar').addEventListener('submit', async e => {
            e.preventDefault();
            const id = document.getElementById('edit-id').value;
            const fd = new FormData(e.target);
            const btn = e.target.querySelector('[type=submit]');
            btn.disabled = true;
            const res = await fetch(`/tracking/motorizados/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify(Object.fromEntries(fd)),
            });
            btn.disabled = false;
            if (res.ok) {
                toast('Actualizado ✓');
                setTimeout(() => location.reload(), 800);
            } else {
                const d = await res.json();
                toast(Object.values(d.errors ?? {}).flat()[0] || d.message || 'Error', 'danger');
            }
        });

        // Eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('¿Eliminar este motorizado?')) return;
                const res = await fetch(`/tracking/motorizados/${btn.dataset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                });
                if (res.ok) {
                    toast('Eliminado');
                    btn.closest('tr').remove();
                } else toast('Error al eliminar', 'danger');
            });
        });
    </script>
@endpush
