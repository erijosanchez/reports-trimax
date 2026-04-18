<?php $__env->startSection('title', 'Gestión de Motorizados'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="mdi mdi-motorbike me-2 text-primary"></i>Gestión de Motorizados
                            </h4>
                            <p class="mb-0 text-muted small">Alta, baja y vinculación con dispositivos Traccar</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
                            <i class="mdi mdi-plus me-1"></i>Nuevo Motorizado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        
        <div class="row mb-4">
            <?php
                $total   = $motorizados->whereNull('deleted_at')->count();
                $activos = $motorizados->where('estado','activo')->whereNull('deleted_at')->count();
                $sinGps  = $motorizados->whereNull('traccar_device_id')->whereNull('deleted_at')->count();
            ?>
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Total</p>
                                <h2 class="mb-0 font-weight-bold text-primary"><?php echo $total; ?></h2>
                            </div>
                            <div class="bg-primary icon-stat"><i class="mdi mdi-motorbike"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Activos</p>
                                <h2 class="mb-0 font-weight-bold text-success"><?php echo $activos; ?></h2>
                            </div>
                            <div class="bg-success icon-stat"><i class="mdi mdi-check-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3 col-md-4 col-sm-6">
                <div class="h-100 card card-stat shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Sin GPS</p>
                                <h2 class="mb-0 font-weight-bold text-warning"><?php echo $sinGps; ?></h2>
                            </div>
                            <div class="bg-warning icon-stat"><i class="mdi mdi-alert"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
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
                                        <th>Device Traccar</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $motorizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="<?php echo $m->trashed() ? 'table-secondary' : ''; ?>">
                                        <td class="text-muted small"><?php echo $m->id; ?></td>
                                        <td class="fw-semibold"><?php echo $m->nombre; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $m->sede; ?></span></td>
                                        <td class="small"><?php echo $m->telefono ?? '—'; ?></td>
                                        <td>
                                            <?php if($m->traccar_device_id): ?>
                                                <code class="small bg-light px-1 rounded">ID: <?php echo $m->traccar_device_id; ?></code>
                                            <?php else: ?>
                                                <span class="text-warning small">
                                                    <i class="mdi mdi-alert-outline"></i> Sin asignar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($m->trashed()): ?>
                                                <span class="badge bg-dark">Eliminado</span>
                                            <?php elseif($m->estado === 'activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (! ($m->trashed())): ?>
                                            <button class="btn btn-sm btn-outline-warning btn-editar"
                                                data-id="<?php echo $m->id; ?>"
                                                data-nombre="<?php echo $m->nombre; ?>"
                                                data-sede="<?php echo $m->sede; ?>"
                                                data-telefono="<?php echo $m->telefono ?? ''; ?>"
                                                data-device="<?php echo $m->traccar_device_id ?? ''; ?>"
                                                data-estado="<?php echo $m->estado; ?>"
                                                title="Editar">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar"
                                                data-id="<?php echo $m->id; ?>" title="Eliminar">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="mdi mdi-motorbike mdi-36px d-block mb-2 text-muted"></i>
                                            No hay motorizados registrados
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(count($dispositivosTraccar)): ?>
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-satellite-variant me-1 text-info"></i>
                            Dispositivos registrados en Traccar
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Identificador único</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $dispositivosTraccar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><code><?php echo $d['id']; ?></code></td>
                                        <td><?php echo $d['name']; ?></td>
                                        <td class="text-muted small"><?php echo $d['uniqueId'] ?? '—'; ?></td>
                                        <td>
                                            <span class="badge <?php echo ($d['status'] ?? '') === 'online' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $d['status'] ?? 'offline'; ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>


<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-crear">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-motorbike me-1"></i>Nuevo Motorizado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $__env->make('tracking._form-motorizado', ['sedes' => $sedes], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <form id="form-editar">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="edit-id">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="mdi mdi-pencil me-1"></i>Editar Motorizado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $__env->make('tracking._form-motorizado', ['sedes' => $sedes, 'prefix' => 'edit-'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="mdi mdi-content-save me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
    el.style.zIndex = 9999;
    el.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// Abrir modal editar
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit-id').value                    = btn.dataset.id;
        document.getElementById('edit-nombre').value                = btn.dataset.nombre;
        document.getElementById('edit-sede').value                  = btn.dataset.sede;
        document.getElementById('edit-telefono').value              = btn.dataset.telefono;
        document.getElementById('edit-traccar_device_id').value     = btn.dataset.device;
        document.getElementById('edit-estado').value                = btn.dataset.estado;
        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    });
});

// Crear
document.getElementById('form-crear').addEventListener('submit', async e => {
    e.preventDefault();
    const fd  = new FormData(e.target);
    const res = await fetch('/tracking/motorizados', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    if (res.ok) { toast('Motorizado creado'); setTimeout(() => location.reload(), 800); }
    else        { const d = await res.json(); toast(d.message || 'Error al crear', 'danger'); }
});

// Actualizar
document.getElementById('form-editar').addEventListener('submit', async e => {
    e.preventDefault();
    const id  = document.getElementById('edit-id').value;
    const fd  = new FormData(e.target);
    const res = await fetch(`/tracking/motorizados/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json',
                   'Content-Type': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
        body: JSON.stringify(Object.fromEntries(fd)),
    });
    if (res.ok) { toast('Motorizado actualizado'); setTimeout(() => location.reload(), 800); }
    else        { const d = await res.json(); toast(d.message || 'Error al actualizar', 'danger'); }
});

// Eliminar
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('¿Eliminar este motorizado?')) return;
        const res = await fetch(`/tracking/motorizados/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (res.ok) { toast('Eliminado'); btn.closest('tr').remove(); }
        else        toast('Error al eliminar', 'danger');
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/motorizados.blade.php ENDPATH**/ ?>