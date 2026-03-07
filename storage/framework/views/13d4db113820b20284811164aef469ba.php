<?php $__env->startSection('title', 'Asignar Sedes'); ?>

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
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="rate-percentage mb-0">
                                                <i class="mdi mdi-office-building text-primary me-2"></i>
                                                Asignar Sedes
                                            </h3>
                                            <p class="text-muted mt-1">Gestiona las sedes asignadas a
                                                <strong><?php echo $consultor->name; ?></strong>
                                            </p>
                                        </div>
                                        <div>
                                            <a href="<?php echo route('marketing.users.show', $consultor->id); ?>"
                                                class="btn btn-outline-secondary">
                                                <i class="mdi mdi-arrow-left me-1"></i> Volver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alertas -->
                            <?php if(session('success')): ?>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="mdi mdi-check-circle me-2"></i>
                                            <?php echo session('success'); ?>

                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(session('error')): ?>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="mdi mdi-alert-circle me-2"></i>
                                            <?php echo session('error'); ?>

                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <!-- Formulario Principal -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-checkbox-multiple-marked text-primary me-2"></i>
                                                Seleccionar Sedes
                                                <span class="badge badge-primary ms-2"><?php echo count($sedesAsignadas); ?>

                                                    asignadas</span>
                                            </h4>

                                            <form method="POST"
                                                action="<?php echo route('marketing.users.update-sedes', $consultor->id); ?>"
                                                id="assignSedesForm">
                                                <?php echo csrf_field(); ?>

                                                <div class="alert alert-info border-0 mb-4">
                                                    <div class="d-flex align-items-start">
                                                        <i class="mdi mdi-information me-2" style="font-size: 20px;"></i>
                                                        <div>
                                                            <strong>Información importante:</strong>
                                                            <p class="mb-0 mt-1" style="color: #525353;">Las encuestas de
                                                                las sedes seleccionadas
                                                                se incluirán en las estadísticas consolidadas de este
                                                                consultor.</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if($sedes->count() > 0): ?>
                                                    <div class="mb-3">
                                                        <div class="d-flex gap-2 mb-3">
                                                            <button type="button" class="btn btn-sm btn-primary text-white"
                                                                onclick="selectAll()">
                                                                <i class="mdi mdi-checkbox-multiple-marked me-1"></i>
                                                                Seleccionar Todas
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                onclick="deselectAll()">
                                                                <i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i>
                                                                Deseleccionar Todas
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="row" id="sedesContainer">
                                                        <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <div class="col-md-6 mb-3">
                                                                <div class="card sede-card <?php echo in_array($sede->id, $sedesAsignadas) ? 'border-primary' : 'border'; ?>"
                                                                    style="cursor: pointer; transition: all 0.3s;"
                                                                    onclick="toggleCheckbox('sede_<?php echo $sede->id; ?>')">
                                                                    <div class="card-body">
                                                                        <div class="d-flex align-items-start">
                                                                            <div class="form-check me-3" style="padding-left: 1rem;">
                                                                                <input
                                                                                    class="form-check-input sede-checkbox"
                                                                                    type="checkbox" name="sedes[]"
                                                                                    value="<?php echo $sede->id; ?>"
                                                                                    id="sede_<?php echo $sede->id; ?>"
                                                                                    <?php echo in_array($sede->id, $sedesAsignadas) ? 'checked' : ''; ?>

                                                                                    onchange="updateCardStyle(this)"
                                                                                    onclick="event.stopPropagation()">
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <div
                                                                                    class="d-flex justify-content-between align-items-start">
                                                                                    <div>
                                                                                        <h6 class="mb-2">
                                                                                            <?php echo $sede->name; ?></h6>
                                                                                        <span class="badge badge-primary">
                                                                                            <i
                                                                                                class="mdi mdi-map-marker"></i>
                                                                                            <?php echo $sede->location; ?>

                                                                                        </span>
                                                                                    </div>
                                                                                    <div class="text-end ms-2">
                                                                                        <div class="mb-1">
                                                                                            <small class="text-muted">
                                                                                                <i
                                                                                                    class="mdi mdi-file-document"></i>
                                                                                                <?php echo $sede->total_surveys; ?>

                                                                                            </small>
                                                                                        </div>
                                                                                        <span class="badge badge-warning">
                                                                                            <i class="mdi mdi-star"></i>
                                                                                            <?php echo number_format($sede->average_rating, 2); ?>

                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>

                                                    <div class="border-top pt-4 mt-4">
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-primary text-white">
                                                                <i class="mdi mdi-content-save me-1"></i>
                                                                Guardar Asignación
                                                            </button>
                                                            <a href="<?php echo route('marketing.users.show', $consultor->id); ?>"
                                                                class="btn btn-light">
                                                                <i class="mdi mdi-close-circle me-1"></i>
                                                                Cancelar
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-5">
                                                        <i
                                                            class="mdi mdi-office-building-outline mdi-48px text-muted mb-3 d-block"></i>
                                                        <h5 class="text-muted mb-3">No hay sedes disponibles</h5>
                                                        <p class="text-muted">No existen sedes activas para asignar a este
                                                            consultor.</p>
                                                        <a href="<?php echo route('marketing.users.create'); ?>"
                                                            class="btn btn-primary text-white">
                                                            <i class="mdi mdi-plus-circle me-1"></i>
                                                            Crear Nueva Sede
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Lateral -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <!-- Info del Consultor -->
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-account-tie text-primary me-2"></i>
                                                Información del Consultor
                                            </h4>

                                            <div class="d-flex align-items-center mb-3">
                                                <img class="img-md rounded-circle me-3"
                                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($consultor->name); ?>&background=6366f1&color=fff&size=128"
                                                    alt="profile">
                                                <div>
                                                    <h6 class="mb-1"><?php echo $consultor->name; ?></h6>
                                                    <small class="text-muted">ID: #<?php echo $consultor->id; ?></small>
                                                </div>
                                            </div>

                                            <div class="border-top pt-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small class="text-muted">Estado</small>
                                                    <?php if($consultor->is_active): ?>
                                                        <span class="badge badge-success">
                                                            <i class="mdi mdi-check-circle"></i> Activo
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="mdi mdi-close-circle"></i> Inactivo
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">Sedes actuales</small>
                                                    <strong class="text-primary"><?php echo count($sedesAsignadas); ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>

                                <div class="col-lg-4 grid-margin stretch-card">
                                    <!-- Resumen de Selección -->
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-3">
                                                <i class="mdi mdi-chart-bar text-success me-2"></i>
                                                Resumen de Selección
                                            </h4>

                                            <div
                                                class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                                <div>
                                                    <small class="text-muted d-block">Sedes Seleccionadas</small>
                                                    <h4 class="mb-0" id="selectedCount"><?php echo count($sedesAsignadas); ?>

                                                    </h4>
                                                </div>
                                                <div class="icon-wrapper bg-primary-subtle rounded">
                                                    <i class="mdi mdi-office-building text-primary"></i>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block">Total Disponibles</small>
                                                    <h4 class="mb-0"><?php echo $sedes->count(); ?></h4>
                                                </div>
                                                <div class="icon-wrapper bg-info-subtle rounded">
                                                    <i class="mdi mdi-office-building-outline text-info"></i>
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
        </div>
    </div>

    <style>
        /* Gap utility for older browsers */
        .gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Sede card hover effect */
        .sede-card {
            transition: all 0.3s ease;
        }

        .sede-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sede-card.border-primary {
            border-width: 2px !important;
            background-color: rgba(99, 102, 241, 0.05);
        }

        /* Checkbox custom styling */
        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .form-check-label {
            cursor: pointer;
        }

        /* Icon wrapper */
        .icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-wrapper i {
            font-size: 24px;
        }

        /* Image sizing */
        .img-md {
            width: 64px;
            height: 64px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Seleccionar todas las sedes
        function selectAll() {
            document.querySelectorAll('.sede-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                updateCardStyle(checkbox);
            });
            updateSelectedCount();
        }

        // Deseleccionar todas las sedes
        function deselectAll() {
            document.querySelectorAll('.sede-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                updateCardStyle(checkbox);
            });
            updateSelectedCount();
        }

        // Toggle checkbox al hacer clic en la card
        function toggleCheckbox(checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            checkbox.checked = !checkbox.checked;
            updateCardStyle(checkbox);
            updateSelectedCount();
        }

        // Actualizar estilo de la card según el estado del checkbox
        function updateCardStyle(checkbox) {
            const card = checkbox.closest('.sede-card');
            if (checkbox.checked) {
                card.classList.add('border-primary');
            } else {
                card.classList.remove('border-primary');
            }
        }

        // Actualizar contador de sedes seleccionadas
        function updateSelectedCount() {
            const count = document.querySelectorAll('.sede-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count;
        }

        // Listener para actualizar contador cuando cambian los checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sede-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/marketing/users/assign-sedes.blade.php ENDPATH**/ ?>