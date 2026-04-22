

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
                                            <select name="puesto" id="selectPuesto"
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
                                            <input type="text" name="puesto_otro" id="inputPuestoOtro"
                                                class="form-control mt-2 <?php $__errorArgs = ['puesto_otro'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo old('puesto_otro'); ?>"
                                                placeholder="Especificar puesto..."
                                                style="display:none">
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
                                            <?php $__errorArgs = ['puesto_otro'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback d-block"><?php echo $message; ?></div>
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
                                                <div class="form-check" style="margin-left: 40px">
                                                    <input class="form-check-input" type="radio" name="tipo"
                                                        id="tipoRegular" value="Regular"
                                                        <?php echo old('tipo', 'Regular') === 'Regular' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="tipoRegular">
                                                        <i class="mdi-clock-outline text-secondary mdi"></i> Regular
                                                    </label>
                                                </div>
                                                <div class="form-check" style="margin-left: 40px">
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
                                                JEFE DIRECTO (Reporta a) <span class="text-danger">*</span>
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
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">SUPERVISA A</label>
                                            <input type="text" name="supervisa_a"
                                                class="form-control <?php $__errorArgs = ['supervisa_a'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo old('supervisa_a'); ?>"
                                                placeholder="Ej: Consultores de sede">
                                            <?php $__errorArgs = ['supervisa_a'];
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
                                        <div class="mb-3 col-md-3">
                                            <label class="form-label fw-semibold">N° DE VACANTES</label>
                                            <input type="number" name="num_vacantes"
                                                class="form-control <?php $__errorArgs = ['num_vacantes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo old('num_vacantes', 1); ?>" min="1" max="99">
                                        </div>
                                        <div class="d-flex align-items-end mb-3 col-md-4">
                                            <div class="mb-2 form-check" style="margin-left: 35px">
                                                <input class="form-check-input" style="margin-right: -30px" type="checkbox" name="info_confidencial"
                                                    id="infoConf" value="1" <?php echo old('info_confidencial') ? 'checked' : ''; ?>>
                                                <label class="form-check-label fw-semibold" for="infoConf">
                                                    Maneja información confidencial
                                                </label>
                                            </div>
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
                                        <i class="mdi-information-outline mdi"></i> Información adicional del puesto
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">TIPO DE VACANTE</label>
                                            <div class="d-flex flex-column gap-1 mt-1">
                                                <?php $__currentLoopData = ['vacante' => 'Vacante', 'reemplazo' => 'Reemplazo', 'posicion_nueva' => 'Posición nueva']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="form-check" style="margin-left: 35px">
                                                        <input class="form-check-input" type="radio" name="tipo_vacante"
                                                            id="tv_<?php echo $val; ?>" value="<?php echo $val; ?>"
                                                            <?php echo old('tipo_vacante') === $val ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="tv_<?php echo $val; ?>"><?php echo $label; ?></label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">PERMANENCIA</label>
                                            <div class="d-flex flex-column gap-1 mt-1">
                                                <div class="form-check" style="margin-left: 35px">
                                                    <input class="form-check-input" type="radio" name="permanencia"
                                                        id="perm_temp" value="temporal"
                                                        <?php echo old('permanencia') === 'temporal' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="perm_temp">Temporal</label>
                                                </div>
                                                <div class="form-check" style="margin-left: 35px">
                                                    <input class="form-check-input" type="radio" name="permanencia"
                                                        id="perm_perm" value="permanente"
                                                        <?php echo old('permanencia') === 'permanente' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="perm_perm">Permanente</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">JORNADA</label>
                                            <div class="d-flex flex-column gap-1 mt-1">
                                                <div class="form-check" style="margin-left: 35px">
                                                    <input class="form-check-input" type="radio" name="jornada"
                                                        id="jorn_parcial" value="tiempo_parcial"
                                                        <?php echo old('jornada') === 'tiempo_parcial' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="jorn_parcial">Tiempo Parcial</label>
                                                </div>
                                                <div class="form-check" style="margin-left: 35px">
                                                    <input class="form-check-input" type="radio" name="jornada"
                                                        id="jorn_completo" value="tiempo_completo"
                                                        <?php echo old('jornada') === 'tiempo_completo' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="jorn_completo">Tiempo Completo</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3 pt-2 col-md-4">
                                            <div class="form-check" style="margin-left: 35px">
                                                <input class="form-check-input" type="checkbox" name="disponibilidad_viaje"
                                                    id="dispViaje" value="1" <?php echo old('disponibilidad_viaje') ? 'checked' : ''; ?>>
                                                <label class="form-check-label fw-semibold" for="dispViaje">
                                                    Disponibilidad para viajar
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-8">
                                            <label class="form-label fw-semibold">MOTIVO DE LA VACANTE</label>
                                            <input type="text" name="motivo"
                                                class="form-control <?php $__errorArgs = ['motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                value="<?php echo old('motivo'); ?>"
                                                placeholder="Ej: Renuncia voluntaria, expansión de operaciones...">
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
                                        <i class="mdi-account-multiple-outline mdi"></i> Candidatos a considerar <small class="text-muted fw-normal">(opcional, máx. 3)</small>
                                    </h6>
                                    <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $letra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-2 row">
                                            <div class="col-md-7">
                                                <div class="input-group">
                                                    <span class="input-group-text"><?php echo strtoupper($letra); ?>.</span>
                                                    <input type="text" name="candidatos[<?php echo $i; ?>][nombre]"
                                                        class="form-control"
                                                        value="<?php echo old("candidatos.{$i}.nombre"); ?>"
                                                        placeholder="Nombre completo">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="candidatos[<?php echo $i; ?>][telefono]"
                                                    class="form-control"
                                                    value="<?php echo old("candidatos.{$i}.telefono"); ?>"
                                                    placeholder="Teléfono de contacto">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-tools mdi"></i> Herramientas que el puesto requiere <small class="text-muted fw-normal">(opcional, máx. 3)</small>
                                    </h6>
                                    <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $letra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-2">
                                            <div class="input-group">
                                                <span class="input-group-text"><?php echo strtoupper($letra); ?>.</span>
                                                <input type="text" name="herramientas[<?php echo $i; ?>]"
                                                    class="form-control"
                                                    value="<?php echo old("herramientas.{$i}"); ?>"
                                                    placeholder="Ej: Excel avanzado, SAP, AutoCAD...">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="grid-margin col-lg-12 stretch-card">
                            <div class="shadow-sm card">
                                <div class="card-body">
                                    <h6 class="mb-4 text-primary">
                                        <i class="mdi-text-box-outline mdi"></i> Notas internas
                                    </h6>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">CONDICIONES DE LA OFERTA</label>
                                            <textarea name="condiciones_oferta" class="form-control" rows="3"
                                                placeholder="Ej: Sueldo base S/ 2,500 + comisiones..." maxlength="1000"><?php echo old('condiciones_oferta'); ?></textarea>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">COMENTARIOS ADICIONALES</label>
                                            <textarea name="comentarios" class="form-control" rows="3"
                                                placeholder="Información relevante para el proceso..." maxlength="1000"><?php echo old('comentarios'); ?></textarea>
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
        const selectPuesto = document.getElementById('selectPuesto');
        const inputOtro    = document.getElementById('inputPuestoOtro');

        function togglePuestoOtro() {
            const esOtros = selectPuesto.value === 'Otros';
            inputOtro.style.display = esOtros ? 'block' : 'none';
            inputOtro.required = esOtros;
            if (!esOtros) inputOtro.value = '';
        }

        selectPuesto.addEventListener('change', togglePuestoOtro);

        // Restaurar estado si hay old() después de error de validación
        togglePuestoOtro();

        document.getElementById('reqForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Guardando...';
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/rrhh/requerimientos/create.blade.php ENDPATH**/ ?>