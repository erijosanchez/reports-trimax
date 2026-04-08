<?php $__env->startSection('title', 'Editar Usuario'); ?>

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
                                        <div class="d-flex align-items-center">
                                            <a href="<?php echo route('admin.users'); ?>" class="me-3 btn btn-light btn-sm">
                                                <i class="mdi-arrow-left mdi"></i>
                                            </a>
                                            <div>
                                                <h3 class="mb-0 rate-percentage">
                                                    <i class="me-2 text-primary mdi mdi-account-edit"></i>
                                                    Editar Usuario
                                                </h3>
                                                <p class="mt-1 text-muted">Modifica la información del usuario</p>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if($user->is_active): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Formulario principal -->
                                <div class="grid-margin col-lg-8 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-primary mdi mdi-account-circle"></i>
                                                Información del Usuario
                                            </h4>

                                            <form method="POST" action="<?php echo route('admin.users.update', $user->id); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>

                                                <!-- Nombre -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Nombre Completo <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="mdi mdi-account"></i></span>
                                                        <input type="text" name="name"
                                                            class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            value="<?php echo old('name', $user->name); ?>" required>
                                                    </div>
                                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="mt-1 text-danger"><small><?php echo $message; ?></small></div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>

                                                <!-- Cargo -->
                                                <div class="mb-4">
                                                    <label class="form-label">Cargo / Puesto</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="mdi mdi-briefcase-account"></i></span>
                                                        <input type="text" name="cargo"
                                                            class="form-control <?php $__errorArgs = ['cargo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            value="<?php echo old('cargo', $user->cargo); ?>"
                                                            placeholder="Ej: Gerente General, Responsable Comercial...">
                                                    </div>
                                                    <small class="text-muted">Aparece en documentos de requerimiento de
                                                        personal.</small>
                                                    <?php $__errorArgs = ['cargo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="mt-1 text-danger"><small><?php echo $message; ?></small></div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>

                                                <!-- Email -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Correo Electrónico <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                        <input type="email" name="email"
                                                            class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            value="<?php echo old('email', $user->email); ?>" required>
                                                    </div>
                                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="mt-1 text-danger"><small><?php echo $message; ?></small></div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>

                                                <!-- Rol -->
                                                <div class="mb-4">
                                                    <label class="form-label">
                                                        Rol <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="mdi mdi-shield-account"></i></span>
                                                        <select name="role" id="role"
                                                            class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                            required>
                                                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo $role->name; ?>"
                                                                    <?php echo old('role', $user->hasRole($role->name) ? $role->name : '') === $role->name ? 'selected' : ''; ?>>
                                                                    <?php echo ucfirst($role->name); ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="mt-1 text-danger"><small><?php echo $message; ?></small></div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>

                                                <!-- Campo Sede -->
                                                <div class="mb-4" id="sedeField"
                                                    style="<?php echo old('role', $user->hasRole('sede') ? 'sede' : '') === 'sede' ? 'display: block;' : 'display: none;'; ?>">
                                                    <label class="form-label">
                                                        Sede <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="mdi mdi-office-building"></i></span>
                                                        <select name="sede" id="sede"
                                                            class="form-select <?php $__errorArgs = ['sede'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                            <option value="">Seleccionar sede...</option>
                                                            <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $nombre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo $key; ?>"
                                                                    <?php echo old('sede', $user->sede) === $key ? 'selected' : ''; ?>>
                                                                    <?php echo $nombre; ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                    <?php $__errorArgs = ['sede'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <div class="mt-1 text-danger"><small><?php echo $message; ?></small></div>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                    <small class="text-muted">Asigna la sede a la que pertenece este
                                                        usuario</small>
                                                </div>

                                                <!-- Estado -->
                                                <div class="mb-4">
                                                    <div class="form-check form-check-success">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" name="is_active" value="1"
                                                                class="form-check-input"
                                                                <?php echo old('is_active', $user->is_active) ? 'checked' : ''; ?>>
                                                            Usuario activo
                                                            <i class="input-helper"></i>
                                                        </label>
                                                    </div>
                                                    <small class="ms-4 text-muted">Los usuarios inactivos no podrán acceder
                                                        al sistema</small>
                                                </div>

                                                <?php if(auth()->user()->isSuperAdmin()): ?>
                                                    <div class="mb-4">
                                                        <div class="pt-4 border-top">
                                                            <h5 class="mb-1">
                                                                <i class="me-2 text-success mdi mdi-shield-check"></i>
                                                                Permisos Especiales
                                                            </h5>
                                                            <p class="mb-3 text-muted small">Solo el Super Admin puede
                                                                otorgar estos permisos</p>

                                                            
                                                            <div class="mb-4">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi-briefcase-outline mdi"></i> Módulo
                                                                    Comercial
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_consultar_orden"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_consultar_orden', $user->puede_ver_consultar_orden ?? false) ? 'checked' : ''; ?>>
                                                                                Consultar Orden <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Automático para
                                                                                    rol Sede</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_ordenes_x_sede"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_ordenes_x_sede', $user->puede_ver_ordenes_x_sede ?? false) ? 'checked' : ''; ?>>
                                                                                Ordenes por Sede <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Automático para
                                                                                    rol Sede</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_acuerdos_comerciales"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_acuerdos_comerciales', $user->puede_ver_acuerdos_comerciales ?? false) ? 'checked' : ''; ?>>
                                                                                Acuerdos Comerciales <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_ventas_consolidadas"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_ventas_consolidadas', $user->puede_ver_ventas_consolidadas ?? false) ? 'checked' : ''; ?>>
                                                                                Ventas Consolidadas <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_descuentos_especiales"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_descuentos_especiales', $user->puede_ver_descuentos_especiales ?? false) ? 'checked' : ''; ?>>
                                                                                Descuentos Especiales <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_venta_clientes"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_venta_clientes', $user->puede_ver_venta_clientes ?? false) ? 'checked' : ''; ?>>
                                                                                Ventas por Cliente
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            
                                                            <div class="mb-3">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi mdi-houzz"></i> Módulo Producción
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_lead_time"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_lead_time', $user->puede_ver_lead_time ?? false) ? 'checked' : ''; ?>>
                                                                                Lead Time <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_pendiente_entrega_montura"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_pendiente_entrega_montura', $user->puede_ver_pendiente_entrega_montura ?? false) ? 'checked' : ''; ?>>
                                                                                Pendiente de Entrega Montura <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Asignación de Bases -->
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_asignacion_bases"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_asignacion_bases', $user->puede_ver_asignacion_bases ?? false) ? 'checked' : ''; ?>>
                                                                                Asignación de Bases <i
                                                                                    class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            
                                                            <div class="mb-3">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi mdi-account-search"></i> Módulo RRHH
                                                                    — Requerimientos de Personal
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_crear_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_crear_requerimientos', $user->puede_crear_requerimientos ?? false) ? 'checked' : ''; ?>>
                                                                                Crear Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Puede solicitar
                                                                                    nuevas contrataciones</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_gestionar_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_gestionar_requerimientos', $user->puede_gestionar_requerimientos ?? false) ? 'checked' : ''; ?>>
                                                                                Gestionar Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Asignar RH,
                                                                                    cambiar estado, registrar
                                                                                    avances</small></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_todos_requerimientos"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_todos_requerimientos', $user->puede_ver_todos_requerimientos ?? false) ? 'checked' : ''; ?>>
                                                                                Ver Todos los Requerimientos
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Sin esto, solo
                                                                                    ve los suyos</small></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3 mb-0 px-3 py-2 border alert alert-light">
                                                                    <small class="text-muted">
                                                                        <i class="me-1 mdi-information-outline mdi"></i>
                                                                        El rol <strong>RRHH</strong> tiene los 3 permisos
                                                                        automáticamente. Usa estos checkboxes para otros
                                                                        roles que necesiten acceso parcial.
                                                                    </small>
                                                                </div>

                                                                
                                                                <div class="mt-3 px-3 py-2 border rounded"
                                                                    style="border-color:#ffc107 !important; background:#fffdf0;">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label fw-semibold">
                                                                                <input type="checkbox"
                                                                                    name="es_gerente_general"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('es_gerente_general', $user->es_gerente_general ?? false) ? 'checked' : ''; ?>>
                                                                                Es Gerente General
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <small class="d-block mt-1 text-muted">
                                                                        Activa esto para el usuario que firma como Gerente
                                                                        General en los formatos de requerimiento de
                                                                        personal.
                                                                        <strong>Solo debe haber uno activo.</strong>
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            
                                                            <div class="mb-3 pt-3 border-top">
                                                                <p class="mb-2 text-muted text-uppercase fw-bold small"
                                                                    style="letter-spacing: 0.5px;">
                                                                    <i class="me-1 mdi mdi-file-send"></i> Módulo
                                                                    Productividad Sedes
                                                                </p>
                                                                <div class="ms-1 row g-2">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-success">
                                                                            <label class="form-check-label">
                                                                                <input type="checkbox"
                                                                                    name="puede_ver_productividad_sedes"
                                                                                    value="1"
                                                                                    class="form-check-input"
                                                                                    <?php echo old('puede_ver_productividad_sedes', $user->puede_ver_productividad_sedes ?? false) ? 'checked' : ''; ?>>
                                                                                Ver Productividad Sedes
                                                                                <i class="input-helper"></i>
                                                                            </label>
                                                                            <div><small class="text-muted">Cobranza, Caja
                                                                                    Chica y Comentarios — solo vista, no
                                                                                    puede enviar</small></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3 mb-0 px-3 py-2 border alert alert-light">
                                                                    <small class="text-muted">
                                                                        <i class="me-1 mdi-information-outline mdi"></i>
                                                                        Los roles <strong>Sede</strong>,
                                                                        <strong>Admin</strong> y <strong>Super
                                                                            Admin</strong> tienen acceso automáticamente.
                                                                        Usa este permiso para otros roles que necesiten ver
                                                                        los reportes.
                                                                    </small>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Cambiar contraseña -->
                                                <div class="mb-4 pt-4 border-top">
                                                    <h5 class="mb-3">
                                                        <i class="me-2 text-warning mdi mdi-lock-reset"></i>
                                                        Cambiar Contraseña (Opcional)
                                                    </h5>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nueva Contraseña</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-lock"></i></span>
                                                            <input type="password" id="password" name="password"
                                                                class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                                placeholder="Dejar en blanco para mantener la actual">
                                                            <button type="button" class="btn-outline-secondary btn"
                                                                id="togglePassword">
                                                                <i class="mdi-eye-outline mdi"></i>
                                                            </button>
                                                        </div>
                                                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <div class="mt-1 text-danger"><small><?php echo $message; ?></small>
                                                            </div>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                        <small class="text-muted">Mínimo 8 caracteres. Dejar en blanco para
                                                            no cambiar.</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Confirmar Nueva Contraseña</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i
                                                                    class="mdi mdi-lock-check"></i></span>
                                                            <input type="password" id="password_confirmation"
                                                                name="password_confirmation" class="form-control"
                                                                placeholder="Repite la nueva contraseña">
                                                            <button type="button" class="btn-outline-secondary btn"
                                                                id="togglePasswordConfirm">
                                                                <i class="mdi-eye-outline mdi"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Botones -->
                                                <div class="d-flex justify-content-between gap-2 pt-3 border-top">
                                                    <a href="<?php echo route('admin.users'); ?>" class="btn btn-light">
                                                        <i class="me-1 mdi mdi-close"></i>Cancelar
                                                    </a>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="me-1 mdi-content-save mdi"></i>Actualizar Usuario
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel lateral info -->
                                <div class="grid-margin col-lg-4 stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mb-4 card-title">
                                                <i class="me-2 text-info mdi mdi-information"></i>
                                                Información del Usuario
                                            </h4>

                                            <div class="mb-4 pb-3 border-bottom text-center">
                                                <img class="mb-3 rounded-circle" style="width: 100px; height: 100px;"
                                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($user->name); ?>&size=200&background=6366f1&color=fff"
                                                    alt="profile">
                                                <h5 class="mb-1"><?php echo $user->name; ?></h5>
                                                <p class="mb-0 text-muted"><?php echo $user->email; ?></p>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="text-muted">Rol actual</span>
                                                    <span class="badge badge-primary">
                                                        <?php echo $user->roles->first()->name ?? 'Sin rol'; ?>

                                                    </span>
                                                </div>
                                            </div>

                                            <?php if($user->hasRole('sede') && $user->sede): ?>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Sede asignada</span>
                                                        <span class="badge badge-info"><?php echo $user->getSedeName(); ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            
                                            <?php
                                                $permisosComerciales = collect([
                                                    'puede_ver_consultar_orden' => 'Consultar Orden',
                                                    'puede_ver_acuerdos_comerciales' => 'Acuerdos Comerciales',
                                                    'puede_ver_ventas_consolidadas' => 'Ventas Consolidadas',
                                                    'puede_ver_descuentos_especiales' => 'Descuentos Especiales',
                                                    'puede_ver_venta_clientes' => 'Ventas por Cliente',
                                                    'puede_ver_lead_time' => 'Lead Time',
                                                    'puede_ver_pendiente_entrega_montura' =>
                                                        'Pendiente de Entrega Montura',
                                                    'puede_ver_ordenes_x_sede' => 'Ordenes por Sede',
                                                    'puede_ver_asignacion_bases' => 'Asignación de Bases',
                                                ])->filter(fn($label, $campo) => $user->$campo);

                                                $permisosRrhh = collect([
                                                    'puede_crear_requerimientos' => 'Crear Reqs.',
                                                    'puede_gestionar_requerimientos' => 'Gestionar Reqs.',
                                                    'puede_ver_todos_requerimientos' => 'Ver Todos',
                                                ])->filter(fn($label, $campo) => $user->$campo);

                                                $permisosProductividad = collect([
                                                    'puede_ver_productividad_sedes' => 'Productividad Sedes',
                                                ])->filter(fn($label, $campo) => $user->$campo);
                                            ?>

                                            <?php if($permisosComerciales->isNotEmpty()): ?>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <p class="mb-2 text-muted"><small><i
                                                                class="me-1 mdi-briefcase-outline mdi"></i>Permisos
                                                            Comerciales</small></p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php $__currentLoopData = $permisosComerciales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge badge-success">
                                                                <i class="me-1 mdi mdi-check"></i><?php echo $label; ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($permisosRrhh->isNotEmpty()): ?>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <p class="mb-2 text-muted"><small><i
                                                                class="me-1 mdi mdi-account-search"></i>Permisos
                                                            RRHH</small></p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php $__currentLoopData = $permisosRrhh; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge badge-primary">
                                                                <i class="me-1 mdi mdi-check"></i><?php echo $label; ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($permisosProductividad->isNotEmpty()): ?>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <p class="mb-2 text-muted"><small><i
                                                                class="me-1 mdi mdi-file-send"></i>Permisos
                                                            Productividad</small></p>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php $__currentLoopData = $permisosProductividad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge badge-warning">
                                                                <i class="me-1 mdi mdi-check"></i><?php echo $label; ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Estado</span>
                                                    <?php if($user->is_active): ?>
                                                        <span class="badge badge-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Registrado</span>
                                                    <small
                                                        class="text-muted"><?php echo $user->created_at->format('d/m/Y'); ?></small>
                                                </div>
                                            </div>

                                            <div class="mb-3 pb-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Última actualización</span>
                                                    <small
                                                        class="text-muted"><?php echo $user->updated_at->diffForHumans(); ?></small>
                                                </div>
                                            </div>

                                            <?php if(isset($user->dashboards)): ?>
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Dashboards asignados</span>
                                                        <span
                                                            class="badge badge-info"><?php echo $user->dashboards->count(); ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid-margin col-lg-12 stretch-card">
                                    <?php if(auth()->user()->isSuperAdmin() &&
                                            $user->id !== auth()->id() &&
                                            !in_array($user->roles->first()->name ?? '', ['super_admin'])): ?>
                                        <div class="border-danger card">
                                            <div class="card-body">
                                                <h5 class="mb-3 text-danger">
                                                    <i class="me-2 mdi-alert-circle-outline mdi"></i>
                                                    Zona de Peligro
                                                </h5>
                                                <p class="mb-3 text-muted small">
                                                    Eliminar este usuario es permanente y no se puede deshacer.
                                                </p>
                                                <form method="POST"
                                                    action="<?php echo route('admin.users.destroy', $user->id); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="w-100 btn btn-danger"
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?\n\nEsta acción no se puede deshacer.')">
                                                        <i class="me-1 mdi mdi-delete-forever"></i>Eliminar Usuario
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
        }

        .btn-outline-secondary {
            border-color: #e3e6f0;
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #e3e6f0;
            color: #6366f1;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const sedeField = document.getElementById('sedeField');
            const sedeSelect = document.getElementById('sede');

            function toggleSedeField() {
                if (roleSelect.value === 'sede') {
                    sedeField.style.display = 'block';
                    sedeSelect.setAttribute('required', 'required');
                } else {
                    sedeField.style.display = 'none';
                    sedeSelect.removeAttribute('required');
                    sedeSelect.value = '';
                }
            }
            toggleSedeField();
            roleSelect.addEventListener('change', toggleSedeField);

            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' :
                        'mdi mdi-eye-off-outline';
                });
            }

            const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
            const passwordConfirmation = document.getElementById('password_confirmation');
            if (togglePasswordConfirm && passwordConfirmation) {
                togglePasswordConfirm.addEventListener('click', function() {
                    const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' :
                        'password';
                    passwordConfirmation.setAttribute('type', type);
                    this.querySelector('i').className = type === 'password' ? 'mdi mdi-eye-outline' :
                        'mdi mdi-eye-off-outline';
                });
            }

            if (password && passwordConfirmation) {
                passwordConfirmation.addEventListener('input', function() {
                    if (this.value && password.value && password.value !== this.value) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/users-edit.blade.php ENDPATH**/ ?>