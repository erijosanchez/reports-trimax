<?php $__env->startSection('title', 'Crear Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center mb-4">
                                        <a href="<?php echo route('dashboards.index'); ?>" class="btn btn-light btn-sm me-3">
                                            <i class="mdi mdi-arrow-left"></i>
                                        </a>
                                        <div>
                                            <h3 class="rate-percentage mb-0">Crear Nuevo Dashboard</h3>
                                            <p class="text-muted mt-1">Configura un nuevo dashboard de Power BI</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario -->
                            <div class="row">
                                <div class="col-lg-12 col-xl-12 mx-auto grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-chart-box-plus-outline text-primary me-2"></i>
                                                Información del Dashboard
                                            </h4>

                                            <form method="POST" action="<?php echo route('dashboards.store'); ?>">
                                                <?php echo csrf_field(); ?>

                                                <!-- Nombre -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Nombre del Dashboard <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-chart-line"></i>
                                                        </span>
                                                        <input type="text" name="name"
                                                            class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            value="<?php echo old('name'); ?>" required
                                                            placeholder="Ej: Dashboard de Ventas Mensual">
                                                    </div>
                                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="text-danger mt-1">
                                                            <small><?php echo $message; ?></small>
                                                        </div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <small class="text-muted">
                                                        Ingresa un nombre descriptivo para identificar el dashboard
                                                    </small>
                                                </div>

                                                <!-- Descripción -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Descripción
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-text"></i>
                                                        </span>
                                                        <textarea name="description" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3"
                                                            placeholder="Breve descripción del dashboard y su propósito..."><?php echo old('description'); ?></textarea>
                                                    </div>
                                                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="text-danger mt-1">
                                                            <small><?php echo $message; ?></small>
                                                        </div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <small class="text-muted">
                                                        Opcional: Describe el contenido y objetivo de este dashboard
                                                    </small>
                                                </div>

                                                <!-- Link de Power BI -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Link de Power BI <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="mdi mdi-link-variant"></i>
                                                        </span>
                                                        <input type="url" name="powerbi_link"
                                                            class="form-control <?php $__errorArgs = ['powerbi_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            value="<?php echo old('powerbi_link'); ?>" required
                                                            placeholder="https://app.powerbi.com/view?r=...">
                                                    </div>
                                                    <?php $__errorArgs = ['powerbi_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="text-danger mt-1">
                                                            <small><?php echo $message; ?></small>
                                                        </div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                                                    <!-- Instrucciones -->
                                                    <div class="alert alert-info mt-3 d-flex align-items-start"
                                                        role="alert">
                                                        <i class="mdi mdi-information-outline me-2 mt-1"></i>
                                                        <div>
                                                            <strong>¿Cómo obtener el link de Power BI?</strong>
                                                            <ol class="mb-0 mt-2" style="padding-left: 1.2rem;">
                                                                <li>Abre tu informe en Power BI Service</li>
                                                                <li>Haz clic en <strong>Archivo → Insertar informe</strong>
                                                                </li>
                                                                <li>En la pestaña <strong>Publicar en web</strong>, copia el
                                                                    enlace</li>
                                                                <li>Pega el enlace completo aquí</li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Estado -->
                                                <div class="mb-4">
                                                    <div class="form-check form-check-success">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" name="is_active" value="1"
                                                                class="form-check-input"
                                                                <?php echo old('is_active', true) ? 'checked' : ''; ?>>
                                                            Dashboard activo
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                                    <small class="text-muted ms-4">
                                                        Los dashboards activos estarán visibles para los usuarios asignados
                                                    </small>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                                    <a href="<?php echo route('dashboards.index'); ?>" class="btn btn-light">
                                                        <i class="mdi mdi-close me-1"></i>Cancelar
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>Crear Dashboard
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card de ayuda adicional -->
                            <div class="row">
                                <div class="col-lg-12 col-xl-12 mx-auto">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="mb-3">
                                                <i class="mdi mdi-lightbulb-on-outline text-warning me-2"></i>
                                                Consejos para mejores resultados
                                            </h5>
                                            <ul class="text-muted mb-0">
                                                <li class="mb-2">
                                                    <strong>Nombre claro:</strong> Usa nombres descriptivos que indiquen el
                                                    área o período (ej: "Ventas Q1 2025")
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Descripción útil:</strong> Explica qué métricas o datos se
                                                    visualizan en el dashboard
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Link correcto:</strong> Asegúrate de usar el link de "Publicar
                                                    en web" de Power BI
                                                </li>
                                                <li class="mb-0">
                                                    <strong>Permisos:</strong> Después de crear, podrás asignar usuarios
                                                    específicos desde la edición
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Fix para gap en navegadores que no lo soporten */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Mejoras para input groups */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
        }

        /* Estilo para textarea */
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/dashboards/create.blade.php ENDPATH**/ ?>