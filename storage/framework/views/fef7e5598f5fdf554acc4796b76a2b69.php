


<?php $__env->startSection('title', 'Requerimientos de Personal'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">

                    
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                        <div class="btn-wrapper">
                            <h3 class="mb-3">
                                <i class="mdi mdi-account-search"></i> Requerimientos de Personal
                            </h3>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            <?php if(auth()->user()->puedeVerTodosLosRequerimientos()): ?>
                                <a href="<?php echo route('rrhh.requerimientos.dashboard'); ?>" class="btn-outline-primary btn">
                                    <i class="mdi mdi-chart-bar"></i> Dashboard
                                </a>
                                <a href="<?php echo route('rrhh.requerimientos.export', request()->query()); ?>"
                                    class="btn-outline-success btn">
                                    <i class="mdi mdi-file-excel"></i> Exportar
                                </a>
                            <?php endif; ?>
                            <?php if(auth()->user()->puedeCrearRequerimientos()): ?>
                                <a href="<?php echo route('rrhh.requerimientos.create'); ?>" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Nuevo Requerimiento
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active">

                            
                            <div class="mb-4 row">
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Total</p>
                                                    <h2 class="mb-0 font-weight-bold text-primary">
                                                        <?php echo $requerimientos->total(); ?>

                                                    </h2>
                                                </div>
                                                <div class="bg-primary icon-stat">
                                                    <i class="mdi mdi-playlist-check"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Pendientes</p>
                                                    <h2 class="mb-0 font-weight-bold text-info">
                                                        <?php echo $requerimientos->getCollection()->where('estado', 'Pendiente')->count(); ?>

                                                    </h2>
                                                </div>
                                                <div class="bg-info icon-stat">
                                                    <i class="mdi-clock-outline mdi"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">En Proceso</p>
                                                    <h2 class="mb-0 font-weight-bold text-warning">
                                                        <?php echo $requerimientos->getCollection()->where('estado', 'En Proceso')->count(); ?>

                                                    </h2>
                                                </div>
                                                <div class="bg-warning icon-stat">
                                                    <i class="mdi mdi-timer-sand"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3 col-sm-6">
                                    <div class="h-100 card card-stat">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <p class="mb-1 text-muted">Contratados</p>
                                                    <h2 class="mb-0 font-weight-bold text-success">
                                                        <?php echo $requerimientos->getCollection()->where('estado', 'Contratado')->count(); ?>

                                                    </h2>
                                                </div>
                                                <div class="bg-success icon-stat">
                                                    <i class="mdi mdi-account-check"></i>
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

                                            <form method="GET" action="<?php echo route('rrhh.requerimientos.index'); ?>">
                                                <div class="mb-3 row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <i class="mdi mdi-magnify"></i> Buscar
                                                        </label>
                                                        <input type="text" name="search" value="<?php echo request('search'); ?>"
                                                            class="form-control" placeholder="Código o puesto...">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <i class="mdi mdi-flag"></i> Estado
                                                        </label>
                                                        <select name="estado" class="form-select">
                                                            <option value="">Todos los estados</option>
                                                            <option value="Pendiente"
                                                                <?php echo request('estado') === 'Pendiente' ? 'selected' : ''; ?>>
                                                                Pendiente</option>
                                                            <option value="En Proceso"
                                                                <?php echo request('estado') === 'En Proceso' ? 'selected' : ''; ?>>
                                                                En Proceso</option>
                                                            <option value="Contratado"
                                                                <?php echo request('estado') === 'Contratado' ? 'selected' : ''; ?>>
                                                                Contratado</option>
                                                            <option value="Cancelado"
                                                                <?php echo request('estado') === 'Cancelado' ? 'selected' : ''; ?>>
                                                                Cancelado</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <i class="mdi mdi-lightning-bolt"></i> Tipo
                                                        </label>
                                                        <select name="tipo" class="form-select">
                                                            <option value="">Todos los tipos</option>
                                                            <option value="Regular"
                                                                <?php echo request('tipo') === 'Regular' ? 'selected' : ''; ?>>
                                                                Regular</option>
                                                            <option value="Urgente"
                                                                <?php echo request('tipo') === 'Urgente' ? 'selected' : ''; ?>>
                                                                Urgente</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            <i class="mdi mdi-office-building"></i> Sede
                                                        </label>
                                                        <select name="sede" class="form-select">
                                                            <option value="">Todas las sedes</option>
                                                            <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo $sede; ?>"
                                                                    <?php echo request('sede') === $sede ? 'selected' : ''; ?>>
                                                                    <?php echo $sede; ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <div class="d-flex gap-2 col-md-12">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="mdi mdi-magnify"></i> Filtrar
                                                        </button>
                                                        <?php if(request()->hasAny(['search', 'estado', 'tipo', 'sede'])): ?>
                                                            <a href="<?php echo route('rrhh.requerimientos.index'); ?>"
                                                                class="btn-outline-secondary btn">
                                                                <i class="mdi-filter-remove mdi"></i> Limpiar
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </form>

                                            
                                            <?php if(session('success')): ?>
                                                <div class="alert alert-success alert-dismissible fade show"
                                                    role="alert">
                                                    <i class="mdi mdi-check-circle"></i> <?php echo session('success'); ?>

                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="alert"></button>
                                                </div>
                                            <?php endif; ?>

                                            
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>CÓDIGO</th>
                                                            <th>PUESTO</th>
                                                            <th>SEDE</th>
                                                            <th>JEFE DIRECTO</th>
                                                            <th>TIPO</th>
                                                            <th>SOLICITUD</th>
                                                            <th>RESPONSABLE RH</th>
                                                            <th>ESTADO</th>
                                                            <th class="text-center">SLA</th>
                                                            <th class="text-center">KPI</th>
                                                            <th class="text-center">TOTAL</th>
                                                            <th class="text-center">ACCIONES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__empty_1 = true; $__currentLoopData = $requerimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr
                                                                class="<?php echo $req->slaVencido() ? 'table-danger' : ($req->estado === 'Pendiente' ? 'table-light' : ''); ?>">
                                                                <td>
                                                                    <strong class="font-monospace text-primary">
                                                                        <?php echo $req->codigo; ?>

                                                                    </strong>
                                                                </td>
                                                                <td><strong><?php echo $req->puesto; ?></strong></td>
                                                                <td><?php echo $req->sede; ?></td>
                                                                <td><small><?php echo $req->jefe_directo; ?></small></td>
                                                                <td>
                                                                    <?php if($req->tipo === 'Urgente'): ?>
                                                                        <span class="bg-danger badge">
                                                                            <i class="mdi mdi-lightning-bolt"></i> Urgente
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="bg-primary badge">Regular</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><small><?php echo $req->fecha_solicitud->format('d/m/Y'); ?></small>
                                                                </td>
                                                                <td>
                                                                    <small>
                                                                        <?php if($req->responsableRh): ?>
                                                                            <?php echo $req->responsableRh->name; ?>

                                                                        <?php elseif($req->responsable_rh_externo): ?>
                                                                            <?php echo $req->responsable_rh_externo; ?>

                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </td>
                                                                <td>
                                                                    <?php
                                                                        $badgeClass = match ($req->estado) {
                                                                            'Pendiente' => 'bg-info',
                                                                            'En Proceso' => 'bg-warning text-dark',
                                                                            'Contratado' => 'bg-success',
                                                                            'Cancelado' => 'bg-danger',
                                                                            default => 'bg-secondary',
                                                                        };
                                                                    ?>
                                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                                        <?php echo $req->estado; ?>

                                                                    </span>
                                                                </td>
                                                                <td class="text-center"><?php echo $req->sla; ?></td>
                                                                <td class="text-center">
                                                                    <?php if(in_array($req->estado, ['Pendiente', 'Cancelado'])): ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php else: ?>
                                                                        <?php
                                                                            $kpiClass = match ($req->semaforo) {
                                                                                'optimo' => 'bg-success',
                                                                                'riesgo' => 'bg-warning text-dark',
                                                                                'critico' => 'bg-danger',
                                                                                default => 'bg-secondary',
                                                                            };
                                                                        ?>
                                                                        <span class="badge <?php echo $kpiClass; ?>">
                                                                            <?php echo $req->kpi; ?>%
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <small><?php echo in_array($req->estado, ['Pendiente', 'Cancelado']) ? '—' : $req->total . 'd'; ?></small>
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="<?php echo route('rrhh.requerimientos.show', $req->id); ?>"
                                                                        class="btn btn-sm btn-info" title="Ver detalle">
                                                                        <i class="mdi mdi-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <tr>
                                                                <td colspan="12" class="py-5 text-center">
                                                                    <i
                                                                        class="d-block mb-2 text-muted mdi mdi-inbox mdi-48px"></i>
                                                                    <p class="text-muted">No hay requerimientos registrados
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            
                                            <div class="d-flex justify-content-end mt-3">
                                                <?php echo $requerimientos->withQueryString()->links(); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            transition: transform .2s;
        }

        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, .12);
        }

        .icon-stat {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .icon-stat.bg-primary {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
        }

        .icon-stat.bg-info {
            background: linear-gradient(135deg, #06B6D4, #0891B2);
        }

        .icon-stat.bg-warning {
            background: linear-gradient(135deg, #F59E0B, #D97706);
        }

        .icon-stat.bg-success {
            background: linear-gradient(135deg, #10B981, #059669);
        }

        .shadow-sm {
            box-shadow: 0 2px 15px rgba(0, 0, 0, .08) !important;
            border: none !important;
            border-radius: 10px !important;
        }

        .table thead th {
            background-color: #1e3a8a !important;
            color: #fff !important;
            font-weight: 600;
            border: none;
            padding: .9rem .75rem;
            font-size: .875rem;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: background-color .15s;
        }

        .table tbody td {
            padding: .8rem .75rem;
            font-size: .875rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .font-monospace {
            font-family: 'Courier New', monospace;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/rrhh/requerimientos/index.blade.php ENDPATH**/ ?>