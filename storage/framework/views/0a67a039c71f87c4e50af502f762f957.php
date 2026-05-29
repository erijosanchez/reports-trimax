<?php $__env->startSection('title', 'Vouchers'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Vouchers ──────────────────────────────────────── */
.voucher-table thead th {
    font-size: .71rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    vertical-align: middle;
    background: var(--bs-tertiary-bg, #f8f9fa);
}
.voucher-table tbody td { vertical-align: middle; }

.badge-pendiente { background: rgba(255,193,7,.15);  color: #856404; border: 1px solid rgba(255,193,7,.4);  font-weight:600; }
.badge-aplicado  { background: rgba(25,135,84,.12);  color: #198754; border: 1px solid rgba(25,135,84,.3);  font-weight:600; }

.codigo-badge {
    background: linear-gradient(135deg,rgba(255,152,0,.15),rgba(255,87,34,.10));
    border: 1px solid rgba(255,152,0,.35);
    color: #e65100;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: .82rem;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: .04em;
}

.sede-badge {
    background: rgba(115,103,240,.10);
    color: #7367f0;
    border: 1px solid rgba(115,103,240,.25);
    font-size: .75rem;
    font-weight: 600;
    padding: 2px 9px;
    border-radius: 20px;
}

.kpi-ok   { color: #198754; font-weight:700; }
.kpi-warn { color: #856404; font-weight:700; }
.kpi-bad  { color: #dc3545; font-weight:700; }

/* Factura rows */
.factura-row td { padding: 6px 10px; }
.factura-row input { font-size:.85rem; }

.total-row td {
    font-weight: 700;
    font-size: .95rem;
    border-top: 2px solid #dee2e6 !important;
    background: var(--bs-tertiary-bg,#f8f9fa);
}

/* Pending card (Silvia) */
.pending-card {
    border-left: 4px solid #fd7e14;
    transition: box-shadow .15s;
}
.pending-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }

/* Drop-zone */
.drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 22px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #f8fafc;
}
.drop-zone.dragover {
    border-color: #2563eb;
    background: rgba(37,99,235,.05);
}
.drop-zone p { margin: 0; }
.preview-thumb {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}
.preview-thumb img {
    width: 100%;
    height: 70px;
    object-fit: cover;
    display: block;
}
.preview-thumb .preview-icon {
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}
.preview-remove {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    background: rgba(220,53,69,.85);
    color: #fff;
    border: none;
    border-radius: 50%;
    font-size: 12px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.preview-name {
    font-size: 10px;
    text-align: center;
    padding: 2px 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Toast */
#vchr-toast {
    position: fixed;
    top: 18px;
    right: 18px;
    z-index: 9999;
    min-width: 280px;
}
.vchr-toast-item {
    padding: 11px 16px;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 500;
    margin-bottom: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    display: flex;
    align-items: center;
    gap: 10px;
    animation: vchrFade .25s ease;
}
.vchr-toast-item.success { background:#198754; color:#fff; }
.vchr-toast-item.error   { background:#dc3545; color:#fff; }
@keyframes vchrFade { from{opacity:0;transform:translateX(30px)} to{opacity:1;transform:none} }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">

    
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center gap-3 page-title">
                    <h3 class="mb-0">
                        <i class="me-2 text-primary mdi mdi-receipt"></i>Vouchers
                    </h3>
                    <?php if($sedUsuario): ?>
                        <span class="sede-badge"><?php echo $sedUsuario; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="vchr-toast"></div>

    
    <?php if($esSilvia): ?>
    <div class="mb-4 row">
        <div class="col-12">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-clock-alert-outline text-warning mdi"></i>
                        Vouchers Pendientes de Aplicar
                    </h6>
                    <span class="bg-warning text-dark badge">
                        <?php echo $vouchers->where('status','pendiente')->count(); ?> pendiente(s)
                    </span>
                </div>
                <div class="p-0 card-body">
                    <?php $pendientes = $vouchers->where('status','pendiente'); ?>
                    <?php if($pendientes->isEmpty()): ?>
                    <div class="py-5 text-muted text-center">
                        <i class="mdi mdi-check-all" style="font-size:2rem;opacity:.3"></i>
                        <p class="mt-2 mb-0">No hay vouchers pendientes. ¡Todo al día!</p>
                    </div>
                    <?php else: ?>
                    <div class="d-flex flex-column gap-3 p-3">
                        <?php $__currentLoopData = $pendientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $kpi = $v->solicitado_at ? $v->solicitado_at->diffInDays(now()) : null;
                        ?>
                        <div class="mb-0 card pending-card">
                            <div class="py-3 card-body">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="codigo-badge"><?php echo $v->codigo; ?></span>
                                            <span class="sede-badge"><?php echo $v->sede; ?></span>
                                            <span class="badge badge-pendiente">Pendiente</span>
                                            <?php if($kpi !== null): ?>
                                                <span class="badge <?php echo $kpi <= 3 ? 'bg-success' : ($kpi <= 7 ? 'bg-warning text-dark' : 'bg-danger'); ?>">
                                                    <?php echo $kpi; ?>d
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            Solicitado por <strong><?php echo $v->creator?->name ?? '—'; ?></strong>
                                            el <?php echo $v->solicitado_at?->format('d/m/Y') ?? '—'; ?>

                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-dark fw-bold">S/ <?php echo number_format($v->total, 2); ?></span>
                                        <button class="btn-outline-secondary btn btn-sm btn-ver-detalle"
                                                title="Ver facturas"
                                                data-id="<?php echo $v->id; ?>"
                                                data-codigo="<?php echo $v->codigo; ?>">
                                            <i class="mdi-eye-outline mdi"></i>
                                        </button>
                                        <button class="text-dark btn btn-sm btn-warning fw-bold btn-aplicar"
                                                data-id="<?php echo $v->id; ?>"
                                                data-codigo="<?php echo $v->codigo; ?>">
                                            <i class="me-1 mdi mdi-check-bold"></i>APLICAR
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="facturas-inline mt-2" id="facturas-inline-<?php echo $v->id; ?>" style="display:none">
                                    <table class="table table-bordered table-sm mt-2 mb-0" style="font-size:.82rem">
                                        <thead class="table-light">
                                            <tr><th>Factura</th><th class="text-end">Monto</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $v->facturas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo $f->factura; ?></td>
                                                <td class="text-end">S/ <?php echo number_format($f->monto,2); ?></td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light fw-bold">
                                                <td>Total</td>
                                                <td class="text-end">S/ <?php echo number_format($v->total,2); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php else: ?>
    <div class="mb-4 row">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-plus-circle-outline text-primary mdi"></i>
                        Nuevo Voucher
                    </h6>
                </div>
                <div class="card-body">
                    <div id="msg-crear"></div>
                    <form id="formCrear" enctype="multipart/form-data" novalidate>
                        <?php echo csrf_field(); ?>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="c-codigo">
                                N° de Voucher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="c-codigo"
                                   name="codigo" placeholder="Ej: ER1452D225"
                                   style="font-family:'Courier New',monospace;font-weight:700;letter-spacing:.05em"
                                   required>
                            <small class="text-muted">Ingresa el número real del voucher físico.</small>
                        </div>

                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="mb-0 form-label fw-semibold">Facturas</label>
                                <button type="button" class="btn-outline-primary btn btn-sm" id="btn-add-factura">
                                    <i class="me-1 mdi mdi-plus"></i>Agregar fila
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-1" id="tabla-facturas">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:55%">Factura</th>
                                            <th style="width:35%">Monto (S/)</th>
                                            <th style="width:10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-facturas">
                                        
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td class="text-end">TOTAL DOCUMENTO:</td>
                                            <td colspan="2">
                                                S/ <span id="total-display">0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <small class="text-muted">Agrega al menos una factura antes de enviar.</small>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Documentos adjuntos
                                <span class="text-muted fw-normal">(opcional · JPG, PNG, PDF, Excel · máx. 20 MB c/u)</span>
                            </label>
                            <div class="drop-zone" id="drop-zone-crear"
                                 onclick="document.getElementById('archivos-crear').click()">
                                <i class="mdi mdi-cloud-upload-outline" style="font-size:2.2rem;color:#94a3b8"></i>
                                <p class="mt-1 mb-1 text-muted" style="font-size:.88rem">
                                    Arrastra archivos aquí o haz clic para seleccionar
                                </p>
                                <p class="text-muted small mb-2">JPG, PNG, GIF, WEBP, PDF, Excel/CSV</p>
                                <div class="d-flex justify-content-center gap-2" onclick="event.stopPropagation()">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="document.getElementById('archivos-crear').click()">
                                        <i class="mdi mdi-paperclip me-1"></i>Seleccionar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="abrirCamara('archivos-crear','preview-crear')">
                                        <i class="mdi mdi-camera me-1"></i>Tomar foto
                                    </button>
                                </div>
                                <input type="file" id="archivos-crear" name="archivos[]"
                                       multiple class="d-none"
                                       accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xlsx,.xls,.csv">
                            </div>
                            <div id="preview-crear" class="mt-2 row g-2"></div>
                        </div>

                        <button type="submit" class="w-100 btn btn-primary" id="btn-crear-submit">
                            <i class="me-2 mdi mdi-send"></i>Crear y enviar a Silvia para aplicar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-3 mt-lg-0 col-12 col-lg-5">
            <div class="h-100 card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi-information-outline text-info mdi"></i>
                        ¿Cómo funciona?
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0" style="font-size:.88rem;line-height:1.8">
                        <li>Ingresa el <strong>número real del voucher</strong> físico.</li>
                        <li>Agrega las facturas con sus montos.</li>
                        <li>Arrastra o adjunta los documentos/fotos del comprobante.</li>
                        <li>Presiona <strong>"Crear y enviar"</strong> — Silvia recibe notificación por correo.</li>
                        <li>Cuando sea aplicado, recibirás un correo de confirmación.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 card-title">
                        <i class="me-2 mdi mdi-history"></i>
                        Historial de Vouchers
                        <?php if($esSilvia): ?>
                            <span class="bg-secondary ms-2 badge" style="font-size:.72rem">Todas las sedes</span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table voucher-table table-hover mb-0" id="tablaHistorial">
                            <thead>
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Voucher</th>
                                    <th>Estado</th>
                                    <th>Sede</th>
                                    <?php if($esSilvia): ?>
                                    <th>Solicitante</th>
                                    <?php endif; ?>
                                    <th class="text-end">Monto</th>
                                    <th>Solicitado</th>
                                    <th>Aplicado</th>
                                    <th class="text-center">KPI</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-historial">
                                <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $fin = $v->aplicado_at ?? now()->toDateString();
                                    $kpi = $v->solicitado_at ? $v->solicitado_at->diffInDays($fin) : null;
                                    $kpiClass = $kpi === null ? '' : ($kpi <= 3 ? 'kpi-ok' : ($kpi <= 7 ? 'kpi-warn' : 'kpi-bad'));
                                ?>
                                <tr id="hrow-<?php echo $v->id; ?>" data-id="<?php echo $v->id; ?>" data-status="<?php echo $v->status; ?>">
                                    <td class="ps-3 text-muted" style="font-size:.8rem"><?php echo $v->id; ?></td>
                                    <td><span class="codigo-badge"><?php echo $v->codigo; ?></span></td>
                                    <td>
                                        <?php if($v->status === 'pendiente'): ?>
                                            <span class="badge badge-pendiente">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge badge-aplicado">Aplicado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="sede-badge"><?php echo $v->sede; ?></span></td>
                                    <?php if($esSilvia): ?>
                                    <td style="font-size:.83rem"><?php echo $v->creator?->name ?? '—'; ?></td>
                                    <?php endif; ?>
                                    <td class="text-end fw-semibold">S/ <?php echo number_format($v->total,2); ?></td>
                                    <td style="font-size:.82rem;white-space:nowrap">
                                        <?php echo $v->solicitado_at?->format('d/m/Y') ?? '—'; ?>

                                    </td>
                                    <td style="font-size:.82rem;white-space:nowrap">
                                        <?php if($v->aplicado_at): ?>
                                            <span class="text-success fw-semibold">
                                                <?php echo $v->aplicado_at->format('d/m/Y'); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($kpi !== null): ?>
                                            <span class="<?php echo $kpiClass; ?>"><?php echo $kpi; ?>d</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="min-width:120px">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button class="btn-outline-secondary btn btn-sm btn-ver-detalle"
                                                    title="Ver detalle"
                                                    data-id="<?php echo $v->id; ?>"
                                                    data-codigo="<?php echo $v->codigo; ?>">
                                                <i class="mdi-eye-outline mdi"></i>
                                            </button>
                                            <?php if(!$esSilvia && $v->status === 'pendiente' && $v->created_by === auth()->id()): ?>
                                            <button class="btn-outline-info btn btn-sm btn-reenviar"
                                                    title="Reenviar notificación a Silvia"
                                                    data-id="<?php echo $v->id; ?>">
                                                <i class="mdi-email-send-outline mdi"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if($v->status === 'pendiente'): ?>
                                            <button class="btn-outline-danger btn btn-sm btn-eliminar"
                                                    title="Eliminar"
                                                    data-id="<?php echo $v->id; ?>">
                                                <i class="mdi-trash-can-outline mdi"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr id="hrow-empty">
                                    <td colspan="<?php echo $esSilvia ? 10 : 9; ?>" class="py-5 text-muted text-center">
                                        <i class="mdi-receipt-outline mdi" style="font-size:2rem;opacity:.3"></i>
                                        <p class="mt-2 mb-0">No hay vouchers registrados.</p>
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
</div>


<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="me-2 text-primary mdi mdi-receipt"></i>
                    Detalle Voucher — <span id="det-codigo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="det-body">
                <div class="py-3 text-center">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function () {
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    const BASE   = '<?php echo url("vouchers"); ?>';
    const ES_SILVIA = <?php echo $esSilvia ? 'true' : 'false'; ?>;

    /* ── helpers ──────────────────────────────────────────── */
    function toast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `vchr-toast-item ${type}`;
        el.innerHTML = `<i class="mdi ${type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'}"></i><span>${msg}</span>`;
        document.getElementById('vchr-toast').appendChild(el);
        setTimeout(() => el.remove(), 3800);
    }

    async function apiFetch(url, opts = {}) {
        const isFormData = opts.body instanceof FormData;
        const headers = { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
        if (!isFormData) headers['Content-Type'] = 'application/json';

        const res  = await fetch(url, { headers, ...opts });
        const data = await res.json();
        if (!res.ok) {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' | ')
                : (data.message ?? `Error ${res.status}`);
            throw new Error(msg);
        }
        return data;
    }

    function setBtn(btn, loading, origHtml) {
        if (loading) {
            btn._orig = btn.innerHTML;
            btn.innerHTML = '<span class="me-1 spinner-border spinner-border-sm"></span>';
            btn.disabled  = true;
        } else {
            btn.innerHTML = origHtml ?? btn._orig;
            btn.disabled  = false;
        }
    }

    function statusBadge(status) {
        return status === 'pendiente'
            ? '<span class="badge badge-pendiente">Pendiente</span>'
            : '<span class="badge badge-aplicado">Aplicado</span>';
    }

    /* ══ DROP-ZONE: múltiples archivos ═════════════════════ */
    const _acumulados = {};

    function setupDropZone(zoneId, inputId, previewId) {
        const zone    = document.getElementById(zoneId);
        const input   = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!zone || !input) return;

        _acumulados[inputId] = new DataTransfer();

        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            agregarArchivos(e.dataTransfer.files, inputId, preview);
        });
        input.addEventListener('change', () => {
            agregarArchivos(input.files, inputId, preview);
            input.value = '';
        });
    }

    function agregarArchivos(newFiles, inputId, preview) {
        const dt = _acumulados[inputId];
        for (const f of newFiles) dt.items.add(f);
        renderPreview(dt.files, preview, inputId);
    }

    function renderPreview(files, preview, inputId) {
        preview.innerHTML = '';
        for (let i = 0; i < files.length; i++) {
            const f   = files[i];
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3 col-lg-2';
            const isImg = f.type.startsWith('image/');
            let iconHtml;
            if (isImg) {
                iconHtml = `<img src="${URL.createObjectURL(f)}" alt="${f.name}">`;
            } else if (f.type.includes('pdf')) {
                iconHtml = `<div class="preview-icon"><i class="mdi mdi-file-pdf-box text-danger"></i></div>`;
            } else {
                iconHtml = `<div class="preview-icon"><i class="mdi mdi-file-excel text-success"></i></div>`;
            }
            col.innerHTML = `
                <div class="preview-thumb">
                    ${iconHtml}
                    <button type="button" class="preview-remove"
                            onclick="quitarArchivo(${i},'${inputId}','${preview.id}')">×</button>
                    <div class="preview-name" title="${f.name}">${f.name}</div>
                </div>`;
            preview.appendChild(col);
        }
        // Sync al input real
        const input = document.getElementById(inputId);
        if (input) {
            const dt2 = _acumulados[inputId];
            Object.defineProperty(input, 'files', { value: dt2.files, writable: true });
        }
    }

    function quitarArchivo(idx, inputId, previewId) {
        const dt  = _acumulados[inputId];
        const dt2 = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== idx) dt2.items.add(dt.files[i]);
        }
        _acumulados[inputId] = dt2;
        renderPreview(dt2.files, document.getElementById(previewId), inputId);
    }

    function abrirCamara(inputId, previewId) {
        const input = document.createElement('input');
        input.type  = 'file';
        input.accept= 'image/*';
        input.capture = 'environment';
        input.onchange = () => {
            agregarArchivos(input.files, inputId, document.getElementById(previewId));
        };
        input.click();
    }

    /* ══ PANEL SEDE: Crear voucher ══════════════════════════ */
    if (!ES_SILVIA) {
        setupDropZone('drop-zone-crear', 'archivos-crear', 'preview-crear');

        let facturaIdx = 0;

        function addFacturaRow() {
            const i     = facturaIdx++;
            const tbody = document.getElementById('tbody-facturas');
            const tr    = document.createElement('tr');
            tr.className = 'factura-row';
            tr.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm"
                           name="facturas[${i}][factura]"
                           placeholder="Ej: F0090" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm monto-input"
                           name="facturas[${i}][monto]"
                           placeholder="0.00" step="0.01" min="0.01" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">
                        <i class="mdi mdi-minus"></i>
                    </button>
                </td>`;
            tbody.appendChild(tr);
            tr.querySelector('input').focus();
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.monto-input').forEach(inp => {
                total += parseFloat(inp.value) || 0;
            });
            document.getElementById('total-display').textContent = total.toFixed(2);
        }

        addFacturaRow();

        document.getElementById('btn-add-factura').addEventListener('click', addFacturaRow);

        document.getElementById('tbody-facturas').addEventListener('input', e => {
            if (e.target.classList.contains('monto-input')) recalcTotal();
        });

        document.getElementById('tbody-facturas').addEventListener('click', e => {
            const btn = e.target.closest('.btn-remove-row');
            if (!btn) return;
            if (document.querySelectorAll('#tbody-facturas .factura-row').length <= 1) {
                toast('Debes tener al menos una factura.', 'error');
                return;
            }
            btn.closest('tr').remove();
            recalcTotal();
        });

        document.getElementById('formCrear').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-crear-submit');
            const msg = document.getElementById('msg-crear');
            msg.innerHTML = '';

            if (!document.querySelectorAll('#tbody-facturas .factura-row').length) {
                msg.innerHTML = '<div class="alert alert-danger py-2">Agrega al menos una factura.</div>';
                return;
            }

            setBtn(btn, true);
            try {
                // Sync archivos acumulados al input antes de leer FormData
                const archInput = document.getElementById('archivos-crear');
                const dt = _acumulados['archivos-crear'];
                if (dt) {
                    const dt2 = new DataTransfer();
                    for (const f of dt.files) dt2.items.add(f);
                    Object.defineProperty(archInput, 'files', { value: dt2.files, writable: true });
                }

                const fd = new FormData(this);

                // Agregar archivos manualmente si FormData no los tomó
                if (dt && dt.files.length) {
                    for (const f of dt.files) fd.append('archivos[]', f);
                }

                const data = await apiFetch(BASE, { method: 'POST', body: fd });

                toast(data.message);
                this.reset();
                document.getElementById('c-codigo').value = '';
                document.getElementById('tbody-facturas').innerHTML = '';
                document.getElementById('preview-crear').innerHTML = '';
                _acumulados['archivos-crear'] = new DataTransfer();
                facturaIdx = 0;
                addFacturaRow();
                recalcTotal();

                const v = data.voucher;
                removeEmpty('hrow-empty');
                const tbody = document.getElementById('tbody-historial');
                const tr    = document.createElement('tr');
                tr.id           = `hrow-${v.id}`;
                tr.dataset.id   = v.id;
                tr.dataset.status = v.status;
                tr.innerHTML    = historialRow(v, ES_SILVIA);
                tbody.insertAdjacentElement('afterbegin', tr);

            } catch (err) {
                msg.innerHTML = `<div class="alert alert-danger py-2">${err.message}</div>`;
            } finally {
                setBtn(btn, false);
            }
        });
    }

    /* ══ SILVIA: Aplicar voucher ════════════════════════════ */
    if (ES_SILVIA) {
        document.addEventListener('click', async e => {
            const btn = e.target.closest('.btn-aplicar');
            if (!btn) return;

            const id     = btn.dataset.id;
            const codigo = btn.dataset.codigo;
            if (!confirm(`¿Aplicar el voucher ${codigo}?`)) return;

            setBtn(btn, true);
            try {
                const data = await apiFetch(`${BASE}/${id}/aplicar`, { method: 'PATCH', body: JSON.stringify({}) });
                toast(data.message);

                // Quitar la pending-card
                btn.closest('.card.pending-card')?.remove();

                // Actualizar fila en historial
                const hrow = document.getElementById(`hrow-${id}`);
                if (hrow) {
                    hrow.dataset.status = 'aplicado';
                    const v = data.voucher;
                    hrow.innerHTML = historialRow(v, ES_SILVIA);
                }

                // Actualizar contador badge
                const badgeCount = document.querySelector('.card-header .badge.bg-warning');
                if (badgeCount) {
                    const n = parseInt(badgeCount.textContent) - 1;
                    badgeCount.textContent = `${n} pendiente(s)`;
                }
            } catch (err) {
                toast(err.message, 'error');
                setBtn(btn, false);
            }
        });
    }

    /* ══ Ver detalle ════════════════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-ver-detalle');
        if (!btn) return;

        const id     = btn.dataset.id;
        const codigo = btn.dataset.codigo;
        document.getElementById('det-codigo').textContent = codigo;
        document.getElementById('det-body').innerHTML =
            '<div class="py-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        new bootstrap.Modal(document.getElementById('modalDetalle')).show();

        try {
            const data  = await apiFetch(`${BASE}/${id}/facturas`);
            const rows  = data.facturas.map(f =>
                `<tr><td>${f.factura}</td><td class="text-end">S/ ${f.monto}</td></tr>`
            ).join('');
            const total = data.facturas.reduce((s, f) => s + parseFloat(f.monto), 0);

            let archivosHtml = '';
            if (data.archivos && data.archivos.length) {
                const items = data.archivos.map(a => {
                    const isImg = a.mime && a.mime.startsWith('image/');
                    const icon  = a.mime && a.mime.includes('pdf')
                        ? '<i class="mdi mdi-file-pdf-box text-danger fs-4"></i>'
                        : '<i class="mdi mdi-file-excel text-success fs-4"></i>';
                    return `
                        <div class="col-6 col-md-4">
                            <a href="${a.url}" target="_blank" class="text-decoration-none">
                                <div class="border rounded p-2 text-center bg-light">
                                    ${isImg
                                        ? `<img src="${a.url}" class="img-fluid rounded mb-1" style="max-height:80px;object-fit:cover">`
                                        : `<div class="py-2">${icon}</div>`}
                                    <div class="text-truncate text-muted" style="font-size:10px" title="${a.name}">${a.name}</div>
                                    <div class="text-muted" style="font-size:10px">${a.size}</div>
                                </div>
                            </a>
                        </div>`;
                }).join('');
                archivosHtml = `
                    <div class="mt-3">
                        <p class="fw-semibold mb-2" style="font-size:.85rem">
                            <i class="mdi mdi-paperclip me-1 text-muted"></i>Adjuntos (${data.archivos.length})
                        </p>
                        <div class="row g-2">${items}</div>
                    </div>`;
            }

            document.getElementById('det-body').innerHTML = `
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Factura</th><th class="text-end">Monto</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td>Total</td>
                            <td class="text-end">S/ ${total.toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
                ${archivosHtml}`;
        } catch (err) {
            document.getElementById('det-body').innerHTML =
                `<div class="alert alert-danger">${err.message}</div>`;
        }
    });

    /* ══ Mostrar/ocultar facturas inline (Silvia) ═══════════ */
    document.addEventListener('click', e => {
        // handled by btn-ver-detalle for modal; this is the inline toggle in pending cards
        const btn = e.target.closest('.btn-ver-detalle');
        if (!btn) return;
        const inline = document.getElementById(`facturas-inline-${btn.dataset.id}`);
        if (inline) {
            inline.style.display = inline.style.display === 'none' ? 'block' : 'none';
        }
    });

    /* ══ Reenviar notificación ══════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-reenviar');
        if (!btn) return;
        setBtn(btn, true);
        try {
            const data = await apiFetch(`${BASE}/${btn.dataset.id}/enviar`, {
                method: 'PATCH', body: JSON.stringify({})
            });
            toast(data.message);
        } catch (err) {
            toast(err.message, 'error');
        } finally {
            setBtn(btn, false);
        }
    });

    /* ══ Eliminar ═══════════════════════════════════════════ */
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-eliminar');
        if (!btn) return;
        if (!confirm('¿Eliminar este voucher? Esta acción no se puede deshacer.')) return;

        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            await apiFetch(`${BASE}/${id}`, { method: 'DELETE', body: JSON.stringify({}) });
            toast('Voucher eliminado.');
            const row = document.getElementById(`hrow-${id}`);
            if (row) {
                row.style.opacity = '0';
                row.style.transition = 'opacity .3s';
                setTimeout(() => row.remove(), 300);
            }
            // Quitar pending-card si existe
            const pendCard = document.querySelector(`.btn-aplicar[data-id="${id}"]`)?.closest('.card.pending-card');
            if (pendCard) pendCard.remove();
        } catch (err) {
            toast(err.message, 'error');
            btn.disabled = false;
        }
    });

    /* ── helpers de render ────────────────────────────────── */
    function removeEmpty(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function kpiHtml(v) {
        if (!v.solicitado_at) return '—';
        const cls = v.kpi <= 3 ? 'kpi-ok' : (v.kpi <= 7 ? 'kpi-warn' : 'kpi-bad');
        return `<span class="${cls}">${v.kpi}d</span>`;
    }

    function historialRow(v, esSilvia) {
        const aplicadoHtml = v.aplicado_at
            ? `<span class="text-success fw-semibold">${v.aplicado_at}</span>`
            : `<span class="text-muted">—</span>`;

        const silviaCols = esSilvia
            ? `<td style="font-size:.83rem">${v.creator_name ?? '—'}</td>`
            : '';

        const reenviarBtn = !esSilvia && v.status === 'pendiente'
            ? `<button class="btn-outline-info btn btn-sm btn-reenviar" title="Reenviar" data-id="${v.id}">
                   <i class="mdi-email-send-outline mdi"></i></button>` : '';

        const eliminarBtn = v.status === 'pendiente'
            ? `<button class="btn-outline-danger btn btn-sm btn-eliminar" title="Eliminar" data-id="${v.id}">
                   <i class="mdi-trash-can-outline mdi"></i></button>` : '';

        return `
            <td class="ps-3 text-muted" style="font-size:.8rem">${v.id}</td>
            <td><span class="codigo-badge">${v.codigo}</span></td>
            <td>${statusBadge(v.status)}</td>
            <td><span class="sede-badge">${v.sede}</span></td>
            ${silviaCols}
            <td class="text-end fw-semibold">S/ ${v.total}</td>
            <td style="font-size:.82rem;white-space:nowrap">${v.solicitado_at ?? '—'}</td>
            <td style="font-size:.82rem;white-space:nowrap">${aplicadoHtml}</td>
            <td class="text-center">${kpiHtml(v)}</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button class="btn-outline-secondary btn btn-sm btn-ver-detalle" title="Ver detalle"
                            data-id="${v.id}" data-codigo="${v.codigo}">
                        <i class="mdi-eye-outline mdi"></i>
                    </button>
                    ${reenviarBtn}
                    ${eliminarBtn}
                </div>
            </td>`;
    }

})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/vouchers/index.blade.php ENDPATH**/ ?>