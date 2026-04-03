<?php $__env->startSection('title', 'Gestionar Usuarios'); ?>

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
                                                <i class="mdi mdi-account-group text-primary me-2"></i>
                                                Gestión de Usuarios
                                            </h3>
                                            <p class="text-muted mt-1">Administra los usuarios del sistema</p>
                                        </div>
                                        <div>
                                            <a class="btn btn-success text-white" href="<?php echo route('admin.users.create'); ?>">
                                                <i class="mdi mdi-account-plus me-1"></i>Nuevo Usuario
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tabla de Usuarios -->
                                <div class="col-lg-8 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-format-list-bulleted text-primary me-2"></i>
                                                    Listado de Usuarios
                                                    <span class="badge badge-primary ms-2"><?php echo $users->total(); ?></span>
                                                </h4>
                                            </div>

                                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                                <table class="table table-hover">
                                                    <thead
                                                        style="position: sticky; top: 0; background: white; z-index: 10;">
                                                        <tr>
                                                            <th>Usuario</th>
                                                            <th>Email</th>
                                                            <th>Rol</th>
                                                            <th>Estado</th>
                                                            <th>Sesiones</th>
                                                            <th>Último Login</th>
                                                            <th style="width: 120px;">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="position-relative me-2">
                                                                            <img class="img-xs rounded-circle"
                                                                                src="https://ui-avatars.com/api/?name=<?php echo urlencode($user->name); ?>&background=6366f1&color=fff"
                                                                                alt="profile">
                                                                            <?php if(isset($onlineIds[$user->id])): ?>
                                                                                <span class="online-indicator pulse"></span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <strong><?php echo $user->name; ?></strong>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted"><?php echo $user->email; ?></small>
                                                                </td>
                                                                <td>
                                                                    <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <span class="badge badge-primary">
                                                                            <?php echo $role->name; ?>

                                                                        </span>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($user->is_active): ?>
                                                                        <span class="badge badge-success">
                                                                            <i class="mdi mdi-check-circle"></i> Activo
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-danger">
                                                                            <i class="mdi mdi-close-circle"></i> Inactivo
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-info">
                                                                        <?php echo $user->sessions_count; ?>

                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">
                                                                        <?php echo $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca'; ?>

                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex gap-1">
                                                                        <a href="<?php echo route('admin.users.edit', $user->id); ?>"
                                                                            class="btn btn-sm btn-warning"
                                                                            data-bs-toggle="tooltip" title="Editar">
                                                                            <i class="mdi mdi-pencil"></i>
                                                                        </a>
                                                                        <?php if($user->id !== auth()->id()): ?>
                                                                            <form method="POST"
                                                                                action="<?php echo route('admin.users.destroy', $user->id); ?>"
                                                                                class="d-inline">
                                                                                <?php echo csrf_field(); ?>
                                                                                <?php echo method_field('DELETE'); ?>
                                                                                <button type="submit"
                                                                                    onclick="return confirm('¿Eliminar usuario <?php echo $user->name; ?>?')"
                                                                                    class="btn btn-sm btn-danger"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Eliminar">
                                                                                    <i class="mdi mdi-delete"></i>
                                                                                </button>
                                                                            </form>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Paginación -->
                                            <?php if($users->hasPages()): ?>
                                                <div class="d-flex justify-content-between align-items-center mt-4">
                                                    <div>
                                                        <small class="text-muted">
                                                            Mostrando <?php echo $users->firstItem(); ?> - <?php echo $users->lastItem(); ?>

                                                            de <?php echo $users->total(); ?> usuarios
                                                        </small>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <?php echo $users->onEachSide(1)->links('pagination::bootstrap-5'); ?>

                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel Lateral - Estado de Usuarios -->
                                <div class="col-lg-4 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="card-title mb-0">
                                                    <i class="mdi mdi-account-clock text-success me-2"></i>
                                                    Estado de Usuarios
                                                </h4>
                                                <span class="badge badge-success" id="usersOnlineCount">
                                                    <?php echo $allUsersStatus->where('is_online', true)->count(); ?> en línea
                                                </span>
                                            </div>
                                            <p class="text-muted mb-3" style="font-size:0.78rem;">
                                                Activos en los últimos 10 minutos
                                            </p>

                                            <div id="usersPresenceList" style="max-height: 500px; overflow-y: auto;">
                                                <?php $__empty_1 = true; $__currentLoopData = $allUsersStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <div class="presence-item d-flex align-items-center border-bottom py-2"
                                                         data-user-id="<?php echo $u['id']; ?>">
                                                        <div class="position-relative me-3 flex-shrink-0">
                                                            <img class="rounded-circle"
                                                                style="width:36px;height:36px;object-fit:cover;"
                                                                src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['name']); ?>&background=<?php echo $u['is_online'] ? '25D366' : 'adb5bd'; ?>&color=fff"
                                                                alt="<?php echo $u['name']; ?>">
                                                            <span class="online-indicator <?php echo $u['is_online'] ? 'dot-online pulse' : 'dot-offline'; ?>"></span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <p class="mb-0 fw-semibold text-truncate" style="font-size:0.875rem;">
                                                                <?php echo $u['name']; ?>

                                                            </p>
                                                            <small class="<?php echo $u['is_online'] ? 'text-success' : 'text-muted'; ?>" style="font-size:0.75rem;">
                                                                <?php echo $u['last_seen']; ?>

                                                            </small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <div class="text-center py-5">
                                                        <i class="mdi mdi-account-off-outline mdi-48px text-muted mb-3 d-block"></i>
                                                        <p class="text-muted">Sin usuarios registrados</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mt-2 text-end">
                                                <small class="text-muted" style="font-size:0.7rem;">
                                                    <i class="mdi mdi-refresh me-1"></i>Se actualiza cada 30s
                                                </small>
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
        .gap-1>*+* {
            margin-left: 0.25rem;
        }

        /* Sticky header for table */
        .table-responsive thead {
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar,
        .card-body>div::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track,
        .card-body>div::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .card-body>div::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover,
        .card-body>div::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Presence / Online indicators */
        .online-indicator {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 11px;
            height: 11px;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .dot-online {
            background-color: #25D366;
        }

        .dot-offline {
            background-color: #adb5bd;
        }

        /* Legacy: when class "pulse" is applied without dot-online */
        .online-indicator.pulse,
        .dot-online.pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0   rgba(37,211,102,.7); }
            70%  { box-shadow: 0 0 0 6px rgba(37,211,102,0);  }
            100% { box-shadow: 0 0 0 0   rgba(37,211,102,0);  }
        }

        .presence-item { transition: background .15s; }
        .presence-item:hover {
            background-color: rgba(0,0,0,.03);
            border-radius: 6px;
        }

        /* Table improvements */
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            // ── Live presence polling ───────────────────────────────────────
            const onlineStatusUrl = '<?php echo route("admin.api.online-status"); ?>';

            function avatarUrl(name, online) {
                const bg = online ? '25D366' : 'adb5bd';
                return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${bg}&color=fff`;
            }

            function refreshPresence() {
                fetch(onlineStatusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        // Update counter
                        const countEl = document.getElementById('usersOnlineCount');
                        if (countEl) countEl.textContent = data.online_count + ' en línea';

                        // Update sidebar list
                        const list = document.getElementById('usersPresenceList');
                        if (!list) return;

                        data.users.forEach(user => {
                            let item = list.querySelector(`[data-user-id="${user.id}"]`);
                            if (!item) return;

                            const img = item.querySelector('img');
                            img.src = avatarUrl(user.name, user.is_online);

                            const dot = item.querySelector('.online-indicator');
                            dot.className = 'online-indicator ' + (user.is_online ? 'dot-online pulse' : 'dot-offline');

                            const label = item.querySelector('small');
                            label.textContent = user.last_seen;
                            label.className = user.is_online ? 'text-success' : 'text-muted';
                            label.style.fontSize = '0.75rem';
                        });

                        // Update table row online dots
                        data.users.forEach(user => {
                            const rows = document.querySelectorAll('table tbody tr');
                            rows.forEach(row => {
                                const nameCell = row.querySelector('td:first-child strong');
                                if (!nameCell) return;
                                // Match by user id stored in presence list
                                const presenceItem = list.querySelector(`[data-user-id="${user.id}"]`);
                                const userName = presenceItem?.querySelector('p')?.textContent?.trim();
                                if (nameCell.textContent.trim() === userName) {
                                    let dot = row.querySelector('.online-indicator');
                                    if (user.is_online && !dot) {
                                        const wrapper = row.querySelector('td:first-child .position-relative');
                                        if (wrapper) {
                                            dot = document.createElement('span');
                                            dot.className = 'online-indicator dot-online pulse';
                                            wrapper.appendChild(dot);
                                        }
                                    } else if (!user.is_online && dot) {
                                        dot.remove();
                                    }
                                }
                            });
                        });

                        // Re-sort presence list: online first
                        const items = [...list.querySelectorAll('.presence-item')];
                        items.sort((a, b) => {
                            const aOn = a.querySelector('.dot-online') ? 1 : 0;
                            const bOn = b.querySelector('.dot-online') ? 1 : 0;
                            return bOn - aOn;
                        });
                        items.forEach(el => list.appendChild(el));
                    })
                    .catch(() => {});
            }

            setInterval(refreshPresence, 30000);
            setTimeout(refreshPresence, 5000);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/users.blade.php ENDPATH**/ ?>