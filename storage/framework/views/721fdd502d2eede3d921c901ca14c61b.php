

<?php
    $alertClass = match ($tipo) {
        'cambio_estado' => match ($estadoNuevo) {
            'Contratado' => 'email-alert--success',
            'Cancelado' => 'email-alert--warning',
            default => 'email-alert--info',
        },
        'asignacion_rh' => 'email-alert--purple',
        'etapa' => 'email-alert--info',
        default => 'email-alert--info',
    };

    $alertIcon = match ($tipo) {
        'cambio_estado' => match ($estadoNuevo) {
            'Contratado' => '✅',
            'Cancelado' => '🚫',
            default => '🔄',
        },
        'asignacion_rh' => '👤',
        'etapa' => '📌',
        default => '💬',
    };

    $alertText = match ($tipo) {
        'cambio_estado' => "El estado del requerimiento fue actualizado a <strong>{$estadoNuevo}</strong>.",
        'asignacion_rh' => 'Se asignó un responsable RH al requerimiento.',
        'etapa' => 'Se registró un nuevo avance en el proceso de selección.',
        default => 'Hay una actualización en el requerimiento.',
    };

    $badgeLabel = match ($tipo) {
        'cambio_estado' => 'CAMBIO DE ESTADO',
        'asignacion_rh' => 'RESPONSABLE ASIGNADO',
        'etapa' => 'AVANCE DEL PROCESO',
        default => 'ACTUALIZACIÓN',
    };
?>

<?php $__env->startSection('badge', $badgeLabel); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div class="email-alert <?php echo $alertClass; ?>">
        <?php echo $alertIcon; ?> <?php echo $alertText; ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <p class="email-greeting">Hola, <?php echo $notifiable->name; ?></p>

    
    <?php if($tipo === 'cambio_estado'): ?>
        <p class="email-intro">
            El requerimiento <strong><?php echo $requerimiento->codigo; ?></strong> ha cambiado de estado.
        </p>
        
        <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin:20px 0;">
            <?php
                $bc1 = match ($estadoAnterior) {
                    'Pendiente' => 'badge-pendiente',
                    'En Proceso' => 'badge-en-proceso',
                    'Contratado' => 'badge-contratado',
                    'Cancelado' => 'badge-cancelado',
                    default => 'badge-regular',
                };
                $bc2 = match ($estadoNuevo) {
                    'Pendiente' => 'badge-pendiente',
                    'En Proceso' => 'badge-en-proceso',
                    'Contratado' => 'badge-contratado',
                    'Cancelado' => 'badge-cancelado',
                    default => 'badge-regular',
                };
            ?>
            <span class="badge-estado <?php echo $bc1; ?>"
                style="font-size:13px;padding:6px 16px;"><?php echo $estadoAnterior; ?></span>
            <span style="font-size:18px;color:#94a3b8;">→</span>
            <span class="badge-estado <?php echo $bc2; ?>" style="font-size:13px;padding:6px 16px;"><?php echo $estadoNuevo; ?></span>
        </div>
    <?php elseif($tipo === 'asignacion_rh'): ?>
        <p class="email-intro">
            El requerimiento <strong><?php echo $requerimiento->codigo; ?></strong> tiene un responsable RH asignado y
            su proceso ha <strong>iniciado oficialmente</strong>.
        </p>
        <div class="highlight-box">
            👤 <strong>Responsable asignado:</strong> <?php echo $extra; ?>

        </div>
    <?php elseif($tipo === 'etapa'): ?>
        <p class="email-intro">
            El responsable RH registró un nuevo avance en el proceso de selección del requerimiento
            <strong><?php echo $requerimiento->codigo; ?></strong>.
        </p>
        <div class="highlight-box">
            📌 <?php echo $extra; ?>

        </div>
    <?php endif; ?>

    
    <div class="data-card">
        <div class="data-card__header">📄 Datos del Requerimiento</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Código</div>
                <div class="data-value data-value--mono"><?php echo $requerimiento->codigo; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado Actual</div>
                <div class="data-value">
                    <span class="badge-estado <?php echo $bc2 ?? 'badge-en-proceso'; ?>">
                        <?php echo $requerimiento->estado; ?>

                    </span>
                </div>
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
                <div class="data-label">Solicitante</div>
                <div class="data-value"><?php echo $requerimiento->solicitante->name; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Responsable RH</div>
                <div class="data-value">
                    <?php if($requerimiento->responsableRh): ?>
                        <?php echo $requerimiento->responsableRh->name; ?>

                    <?php elseif($requerimiento->responsable_rh_externo): ?>
                        <?php echo $requerimiento->responsable_rh_externo; ?>

                    <?php else: ?>
                        <span style="color:#94a3b8;">Sin asignar</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Solicitud</div>
                <div class="data-value"><?php echo $requerimiento->fecha_solicitud->format('d/m/Y'); ?></div>
            </div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="<?php echo $url; ?>" class="cta-button">Ver Detalle en el CRM</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.trimax', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/rrhh/requerimiento_estado.blade.php ENDPATH**/ ?>