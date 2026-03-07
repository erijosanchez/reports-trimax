


<?php $__env->startSection('title', 'Nuevo Requerimiento de Personal'); ?>

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
                        <h3 class="mb-3">
                            <i class="mdi mdi-clipboard-plus"></i> Nuevo Requerimiento de Personal
                        </h3>
                    </div>
                </div>

                
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle"></i>
                        <strong>Errores de validación:</strong>
                        <ul class="mt-1 mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo route('rrhh.requerimientos.store'); ?>" method="POST" id="reqForm">
                    <?php echo csrf_field(); ?>

                    
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-information-outline mdi"></i> Información General
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">GERENCIA</label>
                                            <input type="text" value="GERENCIA COMERCIAL" class="bg-light form-control"
                                                readonly>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                PUESTO <span class="text-danger">*</span>
                                            </label>
                                            <select name="puesto"
                                                class="form-select <?php $__errorArgs = ['puesto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <option value="">Seleccionar puesto...</option>
                                                <?php $__currentLoopData = $puestos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $puesto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $puesto; ?>"
                                                        <?php echo old('puesto') === $puesto ? 'selected' : ''; ?>>
                                                        <?php echo $puesto; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['puesto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo $message; ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                TIPO DE REQUERIMIENTO <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-3 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo"
                                                        id="tipoRegular" value="Regular"
                                                        <?php echo old('tipo', 'Regular') === 'Regular' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="tipoRegular">
                                                        <i class="mdi-clock-outline text-secondary mdi"></i> Regular
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tipo"
                                                        id="tipoUrgente" value="Urgente"
                                                        <?php echo old('tipo') === 'Urgente' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="tipoUrgente">
                                                        <i class="text-danger mdi mdi-lightning-bolt"></i>
                                                        <span class="text-danger fw-semibold">Urgente</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="mt-1 text-danger small"><?php echo $message; ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-briefcase-outline mdi"></i> Datos del Puesto
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                SEDE <span class="text-danger">*</span>
                                            </label>
                                            <select name="sede" class="form-select <?php $__errorArgs = ['sede'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <option value="">Seleccionar sede...</option>
                                                <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $sede; ?>"
                                                        <?php echo old('sede') === $sede ? 'selected' : ''; ?>>
                                                        <?php echo $sede; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['sede'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo $message; ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">
                                                JEFE DIRECTO <span class="text-danger">*</span>
                                            </label>
                                            <select name="jefe_directo"
                                                class="form-select <?php $__errorArgs = ['jefe_directo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                <option value="">Seleccionar jefe directo...</option>
                                                <?php $__currentLoopData = $jefesDirectos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jefe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo $jefe; ?>"
                                                        <?php echo old('jefe_directo') === $jefe ? 'selected' : ''; ?>>
                                                        <?php echo $jefe; ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <?php $__errorArgs = ['jefe_directo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo $message; ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-text-box-outline mdi"></i> Condiciones y Comentarios
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">CONDICIONES DE LA OFERTA</label>
                                            <textarea name="condiciones_oferta" class="form-control" rows="4"
                                                placeholder="Ej: Sueldo base S/ 2,500 + comisiones, horario lunes a sábado..." maxlength="1000"><?php echo old('condiciones_oferta'); ?></textarea>
                                            <small class="text-muted">Máximo 1000 caracteres</small>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">COMENTARIOS</label>
                                            <textarea name="comentarios" class="form-control" rows="4"
                                                placeholder="Información adicional relevante para el proceso..." maxlength="1000"><?php echo old('comentarios'); ?></textarea>
                                            <small class="text-muted">Máximo 1000 caracteres</small>
                                        </div>
                                    </div>

                                    
                                    <div class="d-flex align-items-center gap-2 mb-4 alert alert-info">
                                        <i class="mdi-email-outline mdi mdi-24px"></i>
                                        <span>Al guardar, se enviará una notificación automática a todos los involucrados
                                            del área de RRHH.</span>
                                    </div>

                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo route('rrhh.requerimientos.index'); ?>" class="btn btn-secondary">
                                            <i class="mdi mdi-close"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="mdi-content-save mdi"></i> Guardar Requerimiento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
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
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.getElementById('reqForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Guardando...';
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/rrhh/requerimientos/create.blade.php ENDPATH**/ ?>