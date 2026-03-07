


<?php $__env->startSection('title', 'Detalle - ' . $requerimiento->codigo); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">

                
                <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                    <div>
                        <a href="<?php echo route('rrhh.requerimientos.index'); ?>"
                            class="d-block mb-1 text-muted text-decoration-none">
                            <i class="mdi-arrow-left mdi"></i> Volver al listado
                        </a>
                        <h3 class="mb-0">
                            <i class="mdi mdi-account-search"></i>
                            Requerimiento
                            <span class="font-monospace text-primary"><?php echo $requerimiento->codigo; ?></span>
                        </h3>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                        <?php
                            $badgeClass = match ($requerimiento->estado) {
                                'Pendiente' => 'bg-info',
                                'En Proceso' => 'bg-warning text-dark',
                                'Contratado' => 'bg-success',
                                'Cancelado' => 'bg-secondary',
                                default => 'bg-secondary',
                            };
                        ?>
                        <span class="badge <?php echo $badgeClass; ?> fs-6 px-3 py-2">
                            <?php echo $requerimiento->estado; ?>

                        </span>
                        <?php if($requerimiento->tipo === 'Urgente'): ?>
                            <span class="bg-danger px-3 py-2 badge fs-6">
                                <i class="mdi mdi-lightning-bolt"></i> Urgente
                            </span>
                        <?php else: ?>
                            <span class="bg-primary px-3 py-2 badge fs-6">Regular</span>
                        <?php endif; ?>
                    </div>
                </div>

                
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle"></i> <?php echo session('success'); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($requerimiento->estado === 'Pendiente'): ?>
                    <div class="d-flex align-items-center gap-2 alert alert-info">
                        <i class="mdi mdi-information mdi-24px"></i>
                        <span>Este requerimiento está <strong>pendiente de asignación</strong>. El proceso iniciará
                            automáticamente al asignar un responsable RH.</span>
                    </div>
                <?php endif; ?>

                <div class="row">

                    
                    <div class="<?php echo auth()->user()->puedeGestionarRequerimientos() ? 'col-lg-8' : 'col-lg-12'; ?>">

                        
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-4 text-primary">
                                    <i class="mdi-briefcase-outline mdi"></i> Datos del Requerimiento
                                </h6>
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">GERENCIA</p>
                                        <p class="mb-0"><?php echo $requerimiento->gerencia; ?></p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">PUESTO</p>
                                        <p class="mb-0 fw-bold"><?php echo $requerimiento->puesto; ?></p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SEDE</p>
                                        <p class="mb-0"><?php echo $requerimiento->sede; ?></p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">JEFE DIRECTO</p>
                                        <p class="mb-0"><?php echo $requerimiento->jefe_directo; ?></p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SOLICITANTE</p>
                                        <p class="mb-0"><?php echo $requerimiento->solicitante->name; ?></p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">RESPONSABLE RH</p>
                                        <p class="mb-0">
                                            <?php if($requerimiento->responsableRh): ?>
                                                <?php echo $requerimiento->responsableRh->name; ?>

                                            <?php elseif($requerimiento->responsable_rh_externo): ?>
                                                <?php echo $requerimiento->responsable_rh_externo; ?>

                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">FECHA SOLICITUD</p>
                                        <p class="mb-0"><?php echo $requerimiento->fecha_solicitud->format('d/m/Y H:i'); ?></p>
                                    </div>
                                    <?php if($requerimiento->fecha_cierre): ?>
                                        <div class="mb-3 col-md-4">
                                            <p class="mb-1 text-muted small fw-semibold">FECHA CIERRE</p>
                                            <p class="mb-0"><?php echo $requerimiento->fecha_cierre->format('d/m/Y H:i'); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->condiciones_oferta): ?>
                                        <div class="mb-3 col-md-12">
                                            <p class="mb-1 text-muted small fw-semibold">CONDICIONES DE LA OFERTA</p>
                                            <p class="mb-0"><?php echo $requerimiento->condiciones_oferta; ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->comentarios): ?>
                                        <div class="mb-3 col-md-12">
                                            <p class="mb-1 text-muted small fw-semibold">COMENTARIOS DEL SOLICITANTE</p>
                                            <p class="mb-0"><?php echo $requerimiento->comentarios; ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <?php if($requerimiento->estado !== 'Pendiente'): ?>
                            <div class="mb-4 row">
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">SLA</p>
                                            <h2 class="mb-0 text-dark"><?php echo $requerimiento->sla; ?></h2>
                                            <small class="text-muted">días máx.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">KPI</p>
                                            <?php
                                                $kpiColor = match ($requerimiento->semaforo) {
                                                    'optimo' => 'text-success',
                                                    'riesgo' => 'text-warning',
                                                    'critico' => 'text-danger',
                                                    default => 'text-muted',
                                                };
                                            ?>
                                            <h2 class="mb-0 <?php echo $kpiColor; ?>"><?php echo $requerimiento->kpi; ?></h2>
                                            <small class="text-muted">días transcurridos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">TIEMPO TOTAL</p>
                                            <h2 class="mb-0 text-dark"><?php echo $requerimiento->tiempo_total; ?></h2>
                                            <small class="text-muted">SLA + KPI</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 text-center card card-stat">
                                        <div class="card-body">
                                            <p class="mb-1 text-muted small fw-semibold">SEMÁFORO</p>
                                            <?php
                                                $semaforoIcon = match ($requerimiento->semaforo) {
                                                    'optimo' => 'mdi-circle text-success',
                                                    'riesgo' => 'mdi-circle text-warning',
                                                    'critico' => 'mdi-circle text-danger',
                                                    default => 'mdi-circle text-muted',
                                                };
                                                $semaforoLabel = match ($requerimiento->semaforo) {
                                                    'optimo' => 'Óptimo',
                                                    'riesgo' => 'En Riesgo',
                                                    'critico' => 'Crítico',
                                                    default => '—',
                                                };
                                            ?>
                                            <i class="mdi <?php echo $semaforoIcon; ?> mdi-36px d-block"></i>
                                            <small class="text-muted"><?php echo $semaforoLabel; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <div class="shadow-sm card">
                            <div class="card-body">
                                <h6 class="mb-4 text-primary">
                                    <i class="mdi mdi-history"></i> Historial del Proceso
                                </h6>

                                <?php $__empty_1 = true; $__currentLoopData = $requerimiento->historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $borderColor = match ($evento->color_timeline) {
                                            'blue' => '#2563eb',
                                            'green' => '#059669',
                                            'purple' => '#7c3aed',
                                            'red' => '#dc2626',
                                            'cyan' => '#0891b2',
                                            'indigo' => '#4338ca',
                                            'teal' => '#0d9488',
                                            'amber' => '#d97706',
                                            'gray' => '#6b7280',
                                            default => '#6b7280',
                                        };
                                    ?>
                                    <div class="mb-3 timeline-card" style="border-left: 4px solid <?php echo $borderColor; ?>;">
                                        <div class="d-flex align-items-start justify-content-between mb-1">
                                            <p class="mb-0 fw-bold"><?php echo $evento->titulo; ?></p>
                                            <small class="ms-3 text-muted text-nowrap">
                                                <?php echo $evento->created_at->format('d/m/Y H:i'); ?>

                                            </small>
                                        </div>
                                        <?php if($evento->descripcion): ?>
                                            <p class="mb-1 text-muted small"><?php echo $evento->descripcion; ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-1">
                                            <small class="text-muted">
                                                <i class="mdi-account-outline mdi"></i> <?php echo $evento->usuario->name; ?>

                                            </small>
                                            <?php if($evento->estado_anterior && $evento->estado_nuevo): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php
                                                        $bc1 = match ($evento->estado_anterior) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-secondary',
                                                            default => 'bg-secondary',
                                                        };
                                                        $bc2 = match ($evento->estado_nuevo) {
                                                            'Pendiente' => 'bg-info',
                                                            'En Proceso' => 'bg-warning text-dark',
                                                            'Contratado' => 'bg-success',
                                                            'Cancelado' => 'bg-secondary',
                                                            default => 'bg-secondary',
                                                        };
                                                    ?>
                                                    <span
                                                        class="badge <?php echo $bc1; ?>"><?php echo $evento->estado_anterior; ?></span>
                                                    <i class="mdi-arrow-right text-muted mdi"></i>
                                                    <span
                                                        class="badge <?php echo $bc2; ?>"><?php echo $evento->estado_nuevo; ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="py-4 text-center">
                                        <i class="d-block mb-2 mdi-timeline-text-outline text-muted mdi mdi-48px"></i>
                                        <p class="text-muted">No hay eventos registrados aún.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    
                    <?php if(auth()->user()->puedeGestionarRequerimientos()): ?>
                        <div class="col-lg-4">

                            
                            <div class="shadow-sm mb-3 card">
                                <div
                                    class="card-header <?php echo $requerimiento->estado === 'Pendiente' ? 'bg-primary text-white' : 'bg-light'; ?>">
                                    <h6 class="mb-0">
                                        <i class="mdi mdi-account-check"></i> Responsable RH
                                        <?php if($requerimiento->estado === 'Pendiente'): ?>
                                            <span class="bg-warning ms-2 text-dark badge">Requerido para iniciar</span>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo route('rrhh.requerimientos.responsable', $requerimiento->id); ?>"
                                        method="POST">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">USUARIO INTERNO</label>
                                            <select name="responsable_rh_id" class="form-select-sm form-select">
                                                <option value="">Sin asignar</option>
                                                <?php $__currentLoopData = $usuariosRrhh; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $u->id; ?>"
                                                        <?php echo $requerimiento->responsable_rh_id == $u->id ? 'selected' : ''; ?>>
                                                        <?php echo $u->name; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                        <div class="mb-3 text-muted text-center small">— o empresa externa —</div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">EMPRESA / EXTERNO</label>
                                            <input type="text" name="responsable_rh_externo"
                                                class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->responsable_rh_externo; ?>"
                                                placeholder="Ej: Chamba Talent">
                                        </div>
                                        <button type="submit"
                                            class="btn w-100 <?php echo $requerimiento->estado === 'Pendiente' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <i
                                                class="mdi mdi-<?php echo $requerimiento->estado === 'Pendiente' ? 'play-circle' : 'account-tie'; ?>"></i>
                                            <?php echo $requerimiento->estado === 'Pendiente' ? 'Asignar e Iniciar Proceso' : 'Reasignar Responsable'; ?>

                                        </button>
                                    </form>
                                </div>
                            </div>

                            
                            <?php if($requerimiento->estado === 'En Proceso'): ?>
                                <div class="shadow-sm mb-3 card">
                                    <div class="bg-light card-header">
                                        <h6 class="mb-0">
                                            <i class="mdi mdi-swap-horizontal"></i> Actualizar Estado
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="<?php echo route('rrhh.requerimientos.estado', $requerimiento->id); ?>"
                                            method="POST">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">NUEVO ESTADO</label>
                                                <select name="estado" class="form-select-sm form-select">
                                                    <option value="En Proceso" selected>En Proceso</option>
                                                    <option value="Contratado">Contratado</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="w-100 btn btn-primary">
                                                <i class="mdi-content-save mdi"></i> Guardar Estado
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                
                                <div class="shadow-sm mb-3 card">
                                    <div class="bg-light card-header">
                                        <h6 class="mb-0">
                                            <i class="mdi-clipboard-check-outline mdi"></i> Registrar Avance
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="<?php echo route('rrhh.requerimientos.etapa', $requerimiento->id); ?>"
                                            method="POST">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">ETAPA DEL PROCESO</label>
                                                <select name="tipo_etapa" class="form-select-sm form-select"
                                                    id="tipoEtapaSelect" required>
                                                    <option value="">Seleccionar etapa...</option>
                                                    <?php $__currentLoopData = $etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">DESCRIPCIÓN / DETALLE</label>
                                                <textarea name="descripcion" class="form-control form-control-sm" rows="4"
                                                    placeholder="Describe el avance de esta etapa..." maxlength="2000" required></textarea>
                                            </div>
                                            <button type="submit" class="w-100 btn btn-success">
                                                <i class="mdi mdi-send"></i> Registrar Avance
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .font-monospace {
            font-family: 'Courier New', monospace;
        }

        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        /* Timeline tarjeta con borde izquierdo */
        .timeline-card {
            background: #f8fafc;
            border-radius: 0 8px 8px 0;
            padding: .85rem 1rem;
            transition: background .15s;
        }

        .timeline-card:hover {
            background: #f0f4ff;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const placeholders = {
            publicacion_oferta: 'Ej: Publicado en LinkedIn, Bumeran y Computrabajo...',
            revision_cvs: 'Ej: Se recibieron 24 CVs, se preseleccionaron 8 candidatos...',
            entrevista_virtual: 'Ej: Se realizaron entrevistas virtuales con 5 candidatos...',
            entrevista_presencial: 'Ej: Se citaron 3 candidatos a entrevista presencial...',
            evaluacion: 'Ej: Se aplicó evaluación psicológica y prueba técnica...',
            oferta_candidato: 'Ej: Se envió oferta formal a Juan Pérez. Sueldo: S/ 2,800...',
            nota: 'Escribe tu nota o comentario libre aquí...',
        };
        document.getElementById('tipoEtapaSelect')?.addEventListener('change', function() {
            const textarea = this.closest('form').querySelector('textarea[name="descripcion"]');
            if (textarea && placeholders[this.value]) textarea.placeholder = placeholders[this.value];
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/rrhh/requerimientos/show.blade.php ENDPATH**/ ?>