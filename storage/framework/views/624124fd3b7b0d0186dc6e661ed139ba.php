


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
                        <a href="<?php echo route('rrhh.requerimientos.pdf', $requerimiento->id); ?>"
                            target="_blank" class="btn btn-sm btn-outline-danger ms-1">
                            <i class="mdi mdi-file-pdf-box"></i> Descargar PDF
                        </a>
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

                    <?php
                        $tieneColumnaLateral = auth()->user()->puedeGestionarRequerimientos()
                            || auth()->user()->esGerenteGeneral()
                            || $requerimiento->solicitante_id === auth()->id();
                    ?>
                    
                    <div class="<?php echo $tieneColumnaLateral ? 'col-lg-8' : 'col-lg-12'; ?>">

                        
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

                        
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary">
                                    <i class="mdi mdi-clipboard-text-outline"></i> Detalle del Formulario
                                </h6>
                                <div class="row">
                                    <?php if($requerimiento->supervisa_a): ?>
                                    <div class="mb-2 col-md-4">
                                        <p class="mb-1 text-muted small fw-semibold">SUPERVISA A</p>
                                        <p class="mb-0"><?php echo $requerimiento->supervisa_a; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mb-2 col-md-2">
                                        <p class="mb-1 text-muted small fw-semibold">VACANTES</p>
                                        <p class="mb-0"><?php echo $requerimiento->num_vacantes ?? 1; ?></p>
                                    </div>
                                    <?php if($requerimiento->tipo_vacante): ?>
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">TIPO VACANTE</p>
                                        <p class="mb-0"><?php echo ['vacante'=>'Vacante','reemplazo'=>'Reemplazo','posicion_nueva'=>'Posición nueva'][$requerimiento->tipo_vacante]; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->permanencia): ?>
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">PERMANENCIA</p>
                                        <p class="mb-0"><?php echo ['temporal'=>'Temporal','permanente'=>'Permanente'][$requerimiento->permanencia]; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->jornada): ?>
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">JORNADA</p>
                                        <p class="mb-0"><?php echo ['tiempo_parcial'=>'Tiempo Parcial','tiempo_completo'=>'Tiempo Completo'][$requerimiento->jornada]; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="mb-2 col-md-3">
                                        <p class="mb-1 text-muted small fw-semibold">DISPONIB. VIAJE</p>
                                        <p class="mb-0"><?php echo $requerimiento->disponibilidad_viaje ? 'Sí' : 'No'; ?></p>
                                    </div>
                                    <?php if($requerimiento->motivo): ?>
                                    <div class="mb-2 col-md-12">
                                        <p class="mb-1 text-muted small fw-semibold">MOTIVO</p>
                                        <p class="mb-0"><?php echo $requerimiento->motivo; ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <?php
                            $puedeEditarSec45 = $requerimiento->solicitante_id === auth()->id()
                                || auth()->user()->puedeGestionarRequerimientos();
                        ?>
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-1 text-primary">
                                    <i class="mdi mdi-account-multiple-outline"></i> Sección 4 y 5 — Candidatos y Herramientas
                                </h6>
                                <p class="text-muted small mb-3">Puede ser completado por el solicitante o por RRHH.</p>

                                <?php if($puedeEditarSec45): ?>
                                <form action="<?php echo route('rrhh.requerimientos.candidatos', $requerimiento->id); ?>" method="POST">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                                    <p class="fw-semibold small mb-2">4. Candidatos a considerar (máx. 3)</p>
                                    <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $letra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $cand = $requerimiento->candidatos[$i] ?? null;
                                        ?>
                                        <div class="row mb-2">
                                            <div class="col-md-7">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><?php echo strtoupper($letra); ?>.</span>
                                                    <input type="text" name="candidatos[<?php echo $i; ?>][nombre]"
                                                        class="form-control"
                                                        value="<?php echo $cand['nombre'] ?? ''; ?>"
                                                        placeholder="Nombre completo">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="candidatos[<?php echo $i; ?>][telefono]"
                                                    class="form-control form-control-sm"
                                                    value="<?php echo $cand['telefono'] ?? ''; ?>"
                                                    placeholder="Teléfono">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <hr>
                                    <p class="fw-semibold small mb-2">5. Herramientas que el puesto requiere (máx. 3)</p>
                                    <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $letra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-2">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?php echo strtoupper($letra); ?>.</span>
                                                <input type="text" name="herramientas[<?php echo $i; ?>]"
                                                    class="form-control"
                                                    value="<?php echo $requerimiento->herramientas[$i] ?? ''; ?>"
                                                    placeholder="Ej: Excel avanzado, SAP...">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <button type="submit" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="mdi mdi-content-save"></i> Guardar candidatos y herramientas
                                    </button>
                                </form>
                                <?php else: ?>
                                    
                                    <?php if($requerimiento->candidatos): ?>
                                        <p class="fw-semibold small mb-1">4. Candidatos</p>
                                        <?php $__currentLoopData = $requerimiento->candidatos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="d-flex gap-3 mb-1 small">
                                                <span class="text-muted"><?php echo ['a','b','c'][$i] ?? ($i+1); ?>.</span>
                                                <span><?php echo $c['nombre']; ?></span>
                                                <?php if(!empty($c['telefono'])): ?><span class="text-muted">— <?php echo $c['telefono']; ?></span><?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <p class="text-muted small">4. Sin candidatos registrados.</p>
                                    <?php endif; ?>
                                    <?php if($requerimiento->herramientas): ?>
                                        <p class="fw-semibold small mb-1 mt-2">5. Herramientas</p>
                                        <?php $__currentLoopData = $requerimiento->herramientas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="small mb-1">
                                                <span class="text-muted"><?php echo ['a','b','c'][$i] ?? ($i+1); ?>.</span> <?php echo $h; ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <p class="text-muted small mt-2">5. Sin herramientas registradas.</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <?php if(auth()->user()->puedeGestionarRequerimientos()): ?>
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3" style="color:#5c5c5c;">
                                    <i class="mdi mdi-lock-outline"></i> Sección RRHH — Información del contrato
                                </h6>
                                <?php if($requerimiento->fecha_estimada_contratacion || $requerimiento->tipo_contrato): ?>
                                <div class="row mb-3">
                                    <?php if($requerimiento->fecha_estimada_contratacion): ?>
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">FECHA EST. CONTRATACIÓN</p>
                                        <p class="mb-0"><?php echo $requerimiento->fecha_estimada_contratacion->format('d/m/Y'); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->tipo_contrato): ?>
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">TIPO DE CONTRATO</p>
                                        <p class="mb-0"><?php echo $requerimiento->tipo_contrato; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->duracion_contrato): ?>
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">DURACIÓN</p>
                                        <p class="mb-0"><?php echo $requerimiento->duracion_contrato; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->remuneracion_prevista): ?>
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">REMUNERACIÓN S/.</p>
                                        <p class="mb-0"><?php echo number_format($requerimiento->remuneracion_prevista, 2); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->horario_trabajo): ?>
                                    <div class="col-md-4 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">HORARIO</p>
                                        <p class="mb-0"><?php echo $requerimiento->horario_trabajo; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($requerimiento->beneficios): ?>
                                    <div class="col-md-12 mb-2">
                                        <p class="mb-1 text-muted small fw-semibold">BENEFICIOS</p>
                                        <p class="mb-0"><?php echo $requerimiento->beneficios; ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <hr>
                                <?php endif; ?>
                                
                                <form action="<?php echo route('rrhh.requerimientos.info-rrhh', $requerimiento->id); ?>" method="POST">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <div class="row">
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">FECHA EST. CONTRATACIÓN</label>
                                            <input type="date" name="fecha_estimada_contratacion" class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->fecha_estimada_contratacion?->format('Y-m-d'); ?>">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">TIPO DE CONTRATO</label>
                                            <input type="text" name="tipo_contrato" class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->tipo_contrato; ?>"
                                                placeholder="Ej: Indeterminado, Plazo fijo...">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">DURACIÓN</label>
                                            <input type="text" name="duracion_contrato" class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->duracion_contrato; ?>"
                                                placeholder="Ej: 3 meses, 1 año...">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">REMUNERACIÓN S/.</label>
                                            <input type="number" step="0.01" name="remuneracion_prevista" class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->remuneracion_prevista; ?>"
                                                placeholder="0.00">
                                        </div>
                                        <div class="mb-2 col-md-4">
                                            <label class="form-label small fw-semibold">HORARIO DE TRABAJO</label>
                                            <input type="text" name="horario_trabajo" class="form-control form-control-sm"
                                                value="<?php echo $requerimiento->horario_trabajo; ?>"
                                                placeholder="Ej: Lun-Sáb 9am-6pm">
                                        </div>
                                        <div class="mb-2 col-md-12">
                                            <label class="form-label small fw-semibold">BENEFICIOS</label>
                                            <textarea name="beneficios" class="form-control form-control-sm" rows="2"
                                                placeholder="Ej: Seguro médico, gratificaciones..."><?php echo $requerimiento->beneficios; ?></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary mt-2">
                                        <i class="mdi mdi-content-save"></i> Guardar información RRHH
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        
                        <div class="shadow-sm mb-4 card">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary">
                                    <i class="mdi mdi-draw"></i> Aprobaciones y Firmas
                                </h6>
                                <div class="row text-center">
                                    <?php
                                        $user = auth()->user();
                                        $puedeFirmarSolicitante = $requerimiento->solicitante_id === $user->id && empty($requerimiento->firma_solicitante_data);
                                        $puedeFirmarRrhh        = ($user->isRrhh() || $user->puedeGestionarRequerimientos()) && !$user->esGerenteGeneral() && empty($requerimiento->firma_rrhh_data);
                                        $puedeFirmarGerente     = $user->esGerenteGeneral() && empty($requerimiento->firma_gerente_data);
                                    ?>

                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            <?php if($requerimiento->firma_solicitante_data): ?>
                                                <img src="<?php echo $requerimiento->firma_solicitante_data; ?>" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_solicitante_nombre; ?></div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_solicitante_at?->format('d/m/Y H:i'); ?></div>
                                            <?php elseif($puedeFirmarSolicitante): ?>
                                                <?php if($user->tieneFirmaRegistrada()): ?>
                                                    <form method="POST" action="<?php echo route('rrhh.requerimientos.firmar', $requerimiento->id); ?>"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Responsable de Área Solicitante?')">
                                                        <?php echo csrf_field(); ?>
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="<?php echo route('firma.index'); ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold small mt-2">Responsable de Área Solicitante</div>
                                    </div>

                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            <?php if($requerimiento->firma_rrhh_data): ?>
                                                <img src="<?php echo $requerimiento->firma_rrhh_data; ?>" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_rrhh_nombre; ?></div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_rrhh_at?->format('d/m/Y H:i'); ?></div>
                                            <?php elseif($puedeFirmarRrhh): ?>
                                                <?php if($user->tieneFirmaRegistrada()): ?>
                                                    <form method="POST" action="<?php echo route('rrhh.requerimientos.firmar', $requerimiento->id); ?>"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Responsable de RRHH?')">
                                                        <?php echo csrf_field(); ?>
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="<?php echo route('firma.index'); ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold small mt-2">Responsable de Recursos Humanos</div>
                                    </div>

                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3" style="min-height:110px;">
                                            <?php if($requerimiento->firma_gerente_data): ?>
                                                <img src="<?php echo $requerimiento->firma_gerente_data; ?>" style="max-height:60px; max-width:100%;" alt="firma">
                                                <div class="small text-success mt-1"><i class="mdi mdi-check-circle"></i> Firmado</div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_gerente_nombre; ?></div>
                                                <div class="small text-muted"><?php echo $requerimiento->firma_gerente_at?->format('d/m/Y H:i'); ?></div>
                                            <?php elseif($puedeFirmarGerente): ?>
                                                <?php if($user->tieneFirmaRegistrada()): ?>
                                                    <form method="POST" action="<?php echo route('rrhh.requerimientos.firmar', $requerimiento->id); ?>"
                                                        onsubmit="return confirm('¿Confirmas tu firma como Gerente General?')">
                                                        <?php echo csrf_field(); ?>
                                                        <p class="text-muted small mb-2">Pendiente de tu firma</p>
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="mdi mdi-draw"></i> Firmar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <p class="text-muted small mb-2">Pendiente de firma</p>
                                                    <a href="<?php echo route('firma.index'); ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="mdi mdi-alert"></i> Registra tu firma primero
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="text-muted" style="padding-top:30px;">
                                                    <i class="mdi mdi-dots-horizontal mdi-24px"></i>
                                                    <div class="small">Pendiente</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fw-bold small mt-2">Gerente General</div>
                                        <?php if($gerenteGeneral): ?>
                                            <div class="text-muted small"><?php echo $gerenteGeneral->name; ?></div>
                                        <?php else: ?>
                                            <div class="text-danger small"><i class="mdi mdi-alert-circle-outline"></i> Sin Gerente General configurado</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="text-muted small mt-1">
                                    <i class="mdi mdi-information-outline"></i>
                                    Para poder firmar, cada usuario debe tener su <a href="<?php echo route('firma.index'); ?>">firma digital registrada</a>.
                                </div>
                            </div>
                        </div>

                        
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

                    
                    <?php if(auth()->user()->puedeGestionarRequerimientos() || auth()->user()->esGerenteGeneral() || $requerimiento->solicitante_id === auth()->id()): ?>
                        <div class="col-lg-4">

                            
                            <?php if(!auth()->user()->tieneFirmaRegistrada()): ?>
                            <div class="shadow-sm mb-3 card" style="border-left:4px solid #ffc107 !important;">
                                <div class="card-body py-2">
                                    <p class="mb-1 small fw-semibold text-warning"><i class="mdi mdi-alert"></i> Sin firma registrada</p>
                                    <a href="<?php echo route('firma.index'); ?>" class="btn btn-sm btn-warning w-100">
                                        <i class="mdi mdi-draw"></i> Registrar mi firma
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            
                            <?php if(auth()->user()->puedeGestionarRequerimientos()): ?>
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