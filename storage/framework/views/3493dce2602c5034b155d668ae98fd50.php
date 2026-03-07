

<?php $__env->startSection('badge', 'ASIGNACIÓN RH'); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div class="email-alert email-alert--purple">
        👤 Has sido asignado como responsable de un proceso de selección.
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <p class="email-greeting">Hola, <?php echo $notifiable->name; ?></p>
    <p class="email-intro">
        Se te ha asignado como <strong>Responsable RH</strong> del siguiente requerimiento de personal.
        El proceso de selección está ahora <strong>En Proceso</strong> a tu cargo.
    </p>

    <div class="data-card">
        <div class="data-card__header">📄 Requerimiento Asignado</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono"><?php echo $requerimiento->codigo; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado</div>
                <div class="data-value">
                    <span class="badge-estado badge-en-proceso">En Proceso</span>
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
                <div class="data-value"><?php echo $requerimiento->fecha_solicitud->format('d/m/Y'); ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">SLA Máximo</div>
                <div class="data-value"><strong>45 días</strong> desde la fecha de solicitud</div>
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

    <div class="highlight-box">
        💡 Recuerda registrar los avances del proceso en el sistema CRM usando las etapas:
        Publicación de oferta → Revisión de CVs → Entrevistas → Evaluación → Oferta al candidato.
    </div>

    <div class="cta-wrapper">
        <a href="<?php echo $url; ?>" class="cta-button">Gestionar Requerimiento</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.trimax', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/rrhh/requerimiento_rh_asignado.blade.php ENDPATH**/ ?>