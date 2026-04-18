<?php $__env->startSection('title', 'Rutas de Entrega'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="page-header">
        <div class="row">
            <div class="grid-margin col-lg-12 stretch-card">
                <div class="card">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 card-body">
                        <div>
                            <h4 class="mb-0 fw-bold">
                                <i class="mdi mdi-route me-2 text-primary"></i>Rutas de Entrega
                            </h4>
                            <p class="mb-0 text-muted small">Gestión de rutas diarias y paradas por motorizado</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRuta">
                            <i class="mdi mdi-plus me-1"></i>Nueva Ruta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">

        
        <div class="row">
            <div class="mb-4 col-lg-12 grid-margin stretch-card">
                <div class="shadow-sm border-0 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Motorizado</th>
                                        <th>Sede</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $rutas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-muted small"><?php echo $r->id; ?></td>
                                        <td class="fw-semibold"><?php echo $r->motorizado->nombre; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo $r->motorizado->sede; ?></span></td>
                                        <td><?php echo $r->fecha->format('d/m/Y'); ?></td>
                                        <td>
                                            <?php if($r->estado === 'completado'): ?>
                                                <span class="badge bg-success">Completado</span>
                                            <?php elseif($r->estado === 'en_ruta'): ?>
                                                <span class="badge bg-warning text-dark">En ruta</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?php echo route('tracking.ruta-detalle', $r->id); ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-eye me-1"></i>Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="mdi mdi-route mdi-36px d-block mb-2"></i>
                                            No hay rutas registradas
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($rutas->hasPages()): ?>
                        <div class="mt-3">
                            <?php echo $rutas->links(); ?>

                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="modalCrearRuta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="form-ruta">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-route me-1"></i>Nueva Ruta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Motorizado <span class="text-danger">*</span></label>
                            <select id="sel-motorizado-ruta" name="motorizado_id" class="form-select" required>
                                <option value="">Seleccionar motorizado</option>
                                <?php $__currentLoopData = $motorizados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo $m->id; ?>" data-sede="<?php echo $m->sede; ?>">
                                        <?php echo $m->nombre; ?> — <?php echo $m->sede; ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                   value="<?php echo today()->toDateString(); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas</label>
                            <textarea name="notas" class="form-control" rows="2"
                                      placeholder="Observaciones de la ruta…"></textarea>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">
                            <i class="mdi mdi-map-marker-multiple me-1 text-primary"></i>
                            Paradas
                            <span id="badge-paradas-sel" class="badge bg-primary ms-1">0</span>
                        </h6>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="sel-orden-agregar" class="form-select form-select-sm" style="min-width:280px">
                                <option value="">— Seleccionar orden disponible —</option>
                            </select>
                            <button type="button" id="btn-agregar-parada" class="btn btn-sm btn-primary"
                                    title="Agregar orden a la ruta">
                                <i class="mdi mdi-plus"></i>
                            </button>
                        </div>
                    </div>

                    
                    <div id="msg-ordenes" class="alert alert-info small py-2 mb-2 d-none">
                        <span class="spinner-border spinner-border-sm me-1"></span>Cargando órdenes disponibles…
                    </div>
                    <div id="msg-sin-ordenes" class="alert alert-warning small py-2 mb-2 d-none">
                        <i class="mdi mdi-alert-outline me-1"></i>
                        No hay órdenes pendientes para esta sede.
                        <a href="/tracking/ordenes" target="_blank">Crear orden</a>
                    </div>

                    
                    <div id="contenedor-paradas">
                        <p class="text-muted small text-center py-3" id="msg-vacio">
                            Selecciona el motorizado y agrega órdenes arriba
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-crear-ruta" disabled>
                        <i class="mdi mdi-content-save me-1"></i>Crear Ruta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf       = document.querySelector('meta[name="csrf-token"]')?.content;
let paradasSeleccionadas = [];  // [{id, cliente, direccion, sede}]
let ordenesDisponibles   = [];

// Cuando se elige motorizado → cargar órdenes disponibles de esa sede
document.getElementById('sel-motorizado-ruta').addEventListener('change', async function () {
    const sede = this.options[this.selectedIndex]?.dataset.sede;
    if (!sede) return;

    const sel    = document.getElementById('sel-orden-agregar');
    const msgLoad = document.getElementById('msg-ordenes');
    const msgVacio = document.getElementById('msg-sin-ordenes');

    sel.innerHTML = '<option value="">Cargando…</option>';
    msgLoad.classList.remove('d-none');
    msgVacio.classList.add('d-none');

    const res  = await fetch(`/tracking/api/ordenes-disponibles?sede=${sede}`);
    const data = await res.json();
    ordenesDisponibles = data;

    msgLoad.classList.add('d-none');

    if (!data.length) {
        sel.innerHTML = '<option value="">Sin órdenes pendientes</option>';
        msgVacio.classList.remove('d-none');
        return;
    }

    sel.innerHTML = '<option value="">— Seleccionar orden —</option>'
        + data.map(o =>
            `<option value="${o.id}">
                #${o.id} · ${o.cliente_nombre} — ${o.direccion.substring(0,40)}${o.direccion.length>40?'…':''}
            </option>`
        ).join('');
});

// Agregar parada
document.getElementById('btn-agregar-parada').addEventListener('click', () => {
    const sel    = document.getElementById('sel-orden-agregar');
    const ordenId = parseInt(sel.value);
    if (!ordenId) return;

    // Evitar duplicados
    if (paradasSeleccionadas.find(p => p.id === ordenId)) {
        sel.value = '';
        return;
    }

    const orden = ordenesDisponibles.find(o => o.id === ordenId);
    if (!orden) return;

    paradasSeleccionadas.push(orden);
    sel.value = '';
    renderParadas();
});

function renderParadas() {
    const cont  = document.getElementById('contenedor-paradas');
    const badge = document.getElementById('badge-paradas-sel');
    const btnCrear = document.getElementById('btn-crear-ruta');

    badge.textContent = paradasSeleccionadas.length;
    btnCrear.disabled = paradasSeleccionadas.length === 0
        || !document.getElementById('sel-motorizado-ruta').value;

    document.getElementById('msg-vacio').style.display =
        paradasSeleccionadas.length ? 'none' : 'block';

    if (!paradasSeleccionadas.length) {
        cont.innerHTML = '<p class="text-muted small text-center py-3" id="msg-vacio">Agrega al menos una orden</p>';
        return;
    }

    cont.innerHTML = paradasSeleccionadas.map((o, i) => `
        <div class="card border-0 bg-light mb-2" id="parada-card-${o.id}">
            <div class="card-body py-2 px-3 d-flex gap-2 align-items-center">
                <span class="badge bg-primary" style="min-width:24px">${i + 1}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold small">${o.cliente_nombre}</div>
                    <div class="text-muted" style="font-size:11px">
                        ${o.referencia ? '<span class="me-2">#' + o.referencia + '</span>' : ''}
                        <i class="mdi mdi-map-marker"></i> ${o.direccion}
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="moverParada(${i}, -1)" ${i === 0 ? 'disabled' : ''} title="Subir">
                        <i class="mdi mdi-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="moverParada(${i}, 1)"
                            ${i === paradasSeleccionadas.length - 1 ? 'disabled' : ''} title="Bajar">
                        <i class="mdi mdi-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="quitarParada(${o.id})" title="Quitar">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
            </div>
        </div>`
    ).join('');
}

function moverParada(idx, dir) {
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= paradasSeleccionadas.length) return;
    [paradasSeleccionadas[idx], paradasSeleccionadas[newIdx]] =
        [paradasSeleccionadas[newIdx], paradasSeleccionadas[idx]];
    renderParadas();
}

function quitarParada(ordenId) {
    paradasSeleccionadas = paradasSeleccionadas.filter(p => p.id !== ordenId);
    renderParadas();
}

// Submit
document.getElementById('form-ruta').addEventListener('submit', async e => {
    e.preventDefault();

    if (!paradasSeleccionadas.length) {
        alert('Agrega al menos una parada');
        return;
    }

    const fd   = new FormData(e.target);
    const btn  = document.getElementById('btn-crear-ruta');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';

    const body = {
        motorizado_id: fd.get('motorizado_id'),
        fecha:         fd.get('fecha'),
        notas:         fd.get('notas'),
        paradas:       paradasSeleccionadas.map(o => ({ orden_id: o.id })),
    };

    const res = await fetch('/tracking/rutas', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });

    if (res.ok) {
        const data = await res.json();
        window.location.href = `/tracking/rutas/${data.ruta_id}`;
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-content-save me-1"></i>Crear Ruta';
        alert('Error al crear la ruta');
    }
});

// Reset al abrir modal
document.getElementById('modalCrearRuta').addEventListener('show.bs.modal', () => {
    paradasSeleccionadas = [];
    ordenesDisponibles   = [];
    document.getElementById('sel-motorizado-ruta').value = '';
    document.getElementById('sel-orden-agregar').innerHTML = '<option value="">— Seleccionar orden —</option>';
    document.getElementById('badge-paradas-sel').textContent = '0';
    document.getElementById('btn-crear-ruta').disabled = true;
    renderParadas();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/tracking/rutas.blade.php ENDPATH**/ ?>