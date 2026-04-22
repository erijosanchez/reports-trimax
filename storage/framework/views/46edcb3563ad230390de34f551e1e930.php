


<?php $__env->startSection('badge', 'NUEVO REQUERIMIENTO'); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div class="email-alert email-alert--info">
        📋 Se registró un nuevo requerimiento de personal en espera de asignación.
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <p class="email-greeting">Hola, <?php echo $notifiable->name; ?></p>
    <p class="email-intro">
        Se ha creado un nuevo requerimiento de personal en el sistema CRM de Trimax.
        El estado inicial es <strong>Pendiente</strong> hasta que se asigne un responsable RH.
    </p>

    <div class="data-card">
        <div class="data-card__header">📄 Datos del Requerimiento</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono"><?php echo $requerimiento->codigo; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado</div>
                <div class="data-value">
                    <span class="badge-estado badge-pendiente">⏳ Pendiente</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Gerencia</div>
                <div class="data-value"><?php echo $requerimiento->gerencia; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Puesto</div>
                <div class="data-value"><strong><?php echo $requerimiento->puesto; ?></strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Sede</div>
                <div class="data-value"><?php echo $requerimiento->sede; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Jefe Directo</div>
                <div class="data-value"><?php echo $requerimiento->jefe_directo; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Tipo</div>
                <div class="data-value">
                    <?php if($requerimiento->tipo === 'Urgente'): ?>
                        <span class="badge-estado badge-urgente">⚡ Urgente</span>
                    <?php else: ?>
                        <span class="badge-estado badge-regular">Regular</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Solicitante</div>
                <div class="data-value"><?php echo $requerimiento->solicitante->name; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Solicitud</div>
                <div class="data-value"><?php echo $requerimiento->fecha_solicitud->format('d/m/Y H:i'); ?></div>
            </div>
            <?php if($requerimiento->condiciones_oferta): ?>
                <div class="data-row">
                    <div class="data-label">Condiciones</div>
                    <div class="data-value"><?php echo $requerimiento->condiciones_oferta; ?></div>
                </div>
            <?php endif; ?>
            <?php if($requerimiento->comentarios): ?>
                <div class="data-row">
                    <div class="data-label">Comentarios</div>
                    <div class="data-value"><?php echo $requerimiento->comentarios; ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="<?php echo $url; ?>" class="cta-button">Ver Requerimiento en el CRM</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.trimax', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/rrhh/requerimiento_creado.blade.php ENDPATH**/ ?>