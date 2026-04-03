<!-- <<<<<<<<<<<<<<<<<<<<<<< resources/views/includes/sidebar.blade.php >>>>>>>>>>>>>>>>>>>> -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link <?php echo request()->routeIs('home') ? 'active' : ''; ?>" href="<?php echo route('home'); ?>">
                <i class="mdi mdi-home menu-icon"></i>
                <span class="menu-title">Home</span>
            </a>
        </li>

        
        <?php if(!auth()->user()->isMarketing()): ?>
            <li class="nav-item nav-category">General</li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('dashboards.index') ? 'active' : ''; ?>"
                    href="<?php echo route('dashboards.index'); ?>">
                    <i class="mdi mdi-chart-bar menu-icon"></i>
                    <span class="menu-title">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('files.index') ? 'active' : ''; ?>"
                    href="<?php echo route('files.index'); ?>">
                    <i class="mdi mdi-archive menu-icon"></i>
                    <span class="menu-title">Archivos</span>
                </a>
            </li>
        <?php endif; ?>

        <?php
            $user = auth()->user();
            $tieneAccesoComercial =
                $user->puedeVerConsultarOrden() ||
                $user->puedeVerAcuerdosComerciales() ||
                $user->puedeVerDescuentosEspeciales() ||
                $user->puedeVerVentaClientes() ||
                $user->puedeVerVentasConsolidadas();
        ?>

        <?php if($tieneAccesoComercial): ?>
            <li class="nav-item nav-category">COMERCIAL</li>

            <?php if($user->puedeVerConsultarOrden()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.orden') ? 'active' : ''; ?>"
                        href="<?php echo route('comercial.orden'); ?>">
                        <i class="mdi mdi-file-document-box menu-icon"></i>
                        <span class="menu-title">Consultar Orden</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerOrdenesXSede()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.ordenesPorSede') ? 'active' : ''; ?>"
                        href="<?php echo route('comercial.ordenesPorSede'); ?>">
                        <i class="mdi mdi-store menu-icon"></i>
                        <span class="menu-title">Ordenes por Sede</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerAcuerdosComerciales()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.acuerdos') ? 'active' : ''; ?>"
                        href="<?php echo route('comercial.acuerdos'); ?>">
                        <i class="mdi mdi-book-open-page-variant menu-icon"></i>
                        <span class="menu-title">Acuerdos Comerciales</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerDescuentosEspeciales()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.descuentos.index') ? 'active' : ''; ?>"
                        href="<?php echo route('comercial.descuentos.index'); ?>">
                        <i class="mdi mdi-sale menu-icon"></i>
                        <span class="menu-title">Descuentos Especiales</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerVentasConsolidadas()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.ventas.sedes') ? 'active' : ''; ?>"
                        href="<?php echo route('comercial.ventas.sedes'); ?>">
                        <i class="mdi-cart-outline mdi menu-icon"></i>
                        <span class="menu-title">Ventas</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerVentaClientes()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('comercial.venta-cliente.*') ? '' : 'collapsed'; ?>"
                        data-bs-toggle="collapse" href="#venta-clientes-menu"
                        aria-expanded="<?php echo request()->routeIs('comercial.venta-cliente.*') ? 'true' : 'false'; ?>"
                        aria-controls="venta-clientes-menu">

                        <i class="mdi-account-group mdi menu-icon"></i>
                        <span class="menu-title">Venta Clientes</span>
                        <i class="menu-arrow"></i>
                    </a>

                    <div class="collapse <?php echo request()->routeIs('comercial.venta-cliente.*') ? 'show' : ''; ?>"
                        id="venta-clientes-menu">
                        <ul class="flex-column nav sub-menu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('comercial.venta-cliente.mes') ? 'active' : ''; ?>"
                                    href="<?php echo route('comercial.venta-cliente.mes'); ?>">
                                    Evolutivo — Mes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('comercial.venta-cliente.anio') ? 'active' : ''; ?>"
                                    href="<?php echo route('comercial.venta-cliente.anio'); ?>">
                                    Evolutivo — Año
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

        <?php endif; ?>

        
        <?php
            $user = auth()->user();
            $tieneAccesoProduccion =
                $user->puedeVerPendienteEntregaMontura() ||
                $user->puedeVerLeadTime() ||
                $user->puedeVerAsignacionBases();
        ?>

        <?php if($tieneAccesoProduccion): ?>
            <li class="nav-item nav-category">PRODUCCIÓN</li>

            <?php if($user->puedeVerPendienteEntregaMontura()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('produccion.pendiente-montura.index') ? 'active' : ''; ?>"
                        href="<?php echo route('produccion.pendiente-montura.index'); ?>">
                        <i class="mdi mdi-glasses menu-icon"></i>
                        <span class="menu-title">Entrega Montura</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerLeadTime()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('produccion.lead-time.*') ? '' : 'collapsed'; ?>"
                        data-bs-toggle="collapse" href="#lead-time-menu"
                        aria-expanded="<?php echo request()->routeIs('produccion.lead-time.*') ? 'true' : 'false'; ?>"
                        aria-controls="lead-time-menu">

                        <i class="mdi-clock-outline mdi menu-icon"></i>
                        <span class="menu-title">Lead Time</span>
                        <i class="menu-arrow"></i>
                    </a>

                    <div class="collapse <?php echo request()->routeIs('produccion.lead-time.*') ? 'show' : ''; ?>"
                        id="lead-time-menu">

                        <ul class="flex-column nav sub-menu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('produccion.lead-time.index') ? 'active' : ''; ?>"
                                    href="<?php echo route('produccion.lead-time.index'); ?>">
                                    Lead Time Dashboard
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('produccion.lead-time.semanal') ? 'active' : ''; ?>"
                                    href="<?php echo route('produccion.lead-time.semanal'); ?>">
                                    Lead Time x Tiempo
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('produccion.lead-time.objetivo-mas') ? 'active' : ''; ?>"
                                    href="<?php echo route('produccion.lead-time.objetivo-mas'); ?>">
                                    Lead Time Objetivo +
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if($user->puedeVerAsignacionBases()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo request()->routeIs('produccion.asignacion-bases.*') ? '' : 'collapsed'; ?>"
                        data-bs-toggle="collapse" href="#asignacion-bases-menu"
                        aria-expanded="<?php echo request()->routeIs('produccion.asignacion-bases.*') ? 'true' : 'false'; ?>"
                        aria-controls="asignacion-bases-menu">
                        <i class="mdi-layers-outline mdi menu-icon"></i>
                        <span class="menu-title">Asignación de Bases</span>
                        <i class="menu-arrow"></i>
                    </a>

                    <div class="collapse <?php echo request()->routeIs('produccion.asignacion-bases.*') ? 'show' : ''; ?>"
                        id="asignacion-bases-menu">
                        <ul class="flex-column nav sub-menu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('produccion.asignacion-bases.index') ? 'active' : ''; ?>"
                                    href="<?php echo route('produccion.asignacion-bases.index'); ?>">
                                    Evolutivo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('produccion.asignacion-bases.demanda') ? 'active' : ''; ?>"
                                    href="<?php echo route('produccion.asignacion-bases.demanda'); ?>">
                                    Demanda
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        
        <li class="nav-item nav-category">PRODUCTIVIDAD DE SEDES</li>
        <li class="nav-item">
            <a class="nav-link <?php echo request()->routeIs('marketing.index') ? 'active' : ''; ?>"
                href="<?php echo route('marketing.index'); ?>">
                <i class="mdi-grid menu-icon mdi"></i>
                <span class="menu-title">Ordenes x Usuario</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo request()->routeIs('marketing.users.index') ? 'active' : ''; ?>"
                href="<?php echo route('marketing.users.index'); ?>">
                <i class="mdi mdi-account-multiple menu-icon"></i>
                <span class="menu-title">Envios</span>
            </a>
        </li>

        
        <?php if(auth()->user()->isMarketing() || auth()->user()->isSuperAdmin()): ?>
            <li class="nav-item nav-category">MARKETING</li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('marketing.index') ? 'active' : ''; ?>"
                    href="<?php echo route('marketing.index'); ?>">
                    <i class="mdi-grid menu-icon mdi"></i>
                    <span class="menu-title">Dashboard Marketing</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('marketing.users.index') ? 'active' : ''; ?>"
                    href="<?php echo route('marketing.users.index'); ?>">
                    <i class="mdi mdi-account-multiple menu-icon"></i>
                    <span class="menu-title">Usuarios Marketing</span>
                </a>
            </li>
        <?php endif; ?>

        
        <?php if($user->isRrhh() || $user->isSuperAdmin() || $user->puedeCrearRequerimientos()): ?>
            <li class="nav-item nav-category">RECURSOS HUMANOS</li>

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#rrhh-menu"
                    aria-expanded="<?php echo request()->routeIs('rrhh.requerimientos.*') ? 'true' : 'false'; ?>"
                    aria-controls="rrhh-menu">
                    <i class="mdi mdi-account-search menu-icon"></i>
                    <span class="menu-title">Recursos Humanos</span>
                    <i class="menu-arrow"></i>
                </a>

                <div class="collapse <?php echo request()->routeIs('rrhh.requerimientos.*') ? 'show' : ''; ?>" id="rrhh-menu">
                    <ul class="flex-column nav sub-menu">

                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('rrhh.requerimientos.index') ? 'active' : ''; ?>"
                                href="<?php echo route('rrhh.requerimientos.index'); ?>">
                                Requerimientos
                            </a>
                        </li>

                        <?php if($user->isRrhh() || $user->isSuperAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo request()->routeIs('rrhh.requerimientos.dashboard') ? 'active' : ''; ?>"
                                    href="<?php echo route('rrhh.requerimientos.dashboard'); ?>">
                                    Dashboard RR.HH.
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </div>
            </li>
        <?php endif; ?>

        
        <?php if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()): ?>
            <li class="nav-item nav-category">Administrador</li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('admin.dashboard*') ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#collapse-admin-dashboard"
                    aria-expanded="<?php echo request()->routeIs('admin.dashboard*') ? 'true' : 'false'; ?>"
                    aria-controls="collapse-admin-dashboard">
                    <i class="mdi-grid menu-icon mdi"></i>
                    <span class="menu-title">Admin Dashboard</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse <?php echo request()->routeIs('admin.dashboard') ? 'show' : ''; ?>"
                    id="collapse-admin-dashboard">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.dashboard') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.dashboard'); ?>">Dashboard</a>
                        </li>

                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#charts"
                    aria-expanded="<?php echo request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? 'true' : 'false'; ?>"
                    aria-controls="charts">
                    <i class="menu-icon mdi mdi-account-switch"></i>
                    <span class="menu-title">Admin Usuarios</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse <?php echo request()->routeIs('admin.users*') || request()->routeIs('admin.locations.*') ? 'show' : ''; ?>"
                    id="charts">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.users') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.users'); ?>">Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.locations.map') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.locations.map'); ?>">Ubicaciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.locations.index') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.locations.index'); ?>">Historial de Ubicaciones</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#icons"
                    aria-expanded="<?php echo request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? 'true' : 'false'; ?>"
                    aria-controls="icons">
                    <i class="mdi-lock-outline menu-icon mdi"></i>
                    <span class="menu-title">Seguridad</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse <?php echo request()->routeIs('admin.security*') || request()->routeIs('admin.activity-logs*') ? 'show' : ''; ?>"
                    id="icons">
                    <ul class="flex-column nav sub-menu">
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.security') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.security'); ?>">Seguridad y Análisis</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->routeIs('admin.activity-logs') ? 'active' : ''; ?>"
                                href="<?php echo route('admin.activity-logs'); ?>">Logs de Actividad</a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <li class="nav-item nav-category">help</li>
        <li class="nav-item">
            <a class="nav-link" href="http://bootstrapdash.com/demo/star-admin2-free/docs/documentation.html">
                <i class="menu-icon mdi mdi-file-document"></i>
                <span class="menu-title">Documentation</span>
            </a>
        </li>
    </ul>
</nav>
<?php /**PATH /var/www/resources/views/includes/sidebar.blade.php ENDPATH**/ ?>