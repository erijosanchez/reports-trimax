<?php $__env->startSection('badge', $esEdicion ? 'ACTUALIZACIÓN DE REPORTE' : 'NUEVO REPORTE'); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div class="email-alert <?php echo $esEdicion ? 'email-alert--warning' : 'email-alert--success'; ?>">
        <?php echo $esEdicion ? '✏️' : '💬'; ?>

        <?php echo $esEdicion ? 'Se actualizó un reporte de Comentarios' : 'Nuevo reporte de Comentarios recibido'; ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <p class="email-greeting">Estimado(a),</p>
    <p class="email-intro">
        <?php if($esEdicion): ?>
            Se ha <strong>editado</strong> el reporte de Comentarios de la sede <strong><?php echo $reporte->sede; ?></strong>.
            <?php if($reporte->editado_tarde): ?>
                <br><span style="color:#dc2626;font-weight:600;">⚠️ Edición realizada después del límite (jueves 2:00 PM). KPI afectado.</span>
            <?php endif; ?>
        <?php else: ?>
            Se recibió el <strong>reporte semanal de Comentarios</strong> de la sede <strong><?php echo $reporte->sede; ?></strong>.
        <?php endif; ?>
    </p>

    <div class="data-card">
        <div class="data-card__header">Detalle del Reporte</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Sede</div>
                <div class="data-value"><strong><?php echo $reporte->sede; ?></strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Semana</div>
                <div class="data-value data-value--mono">S<?php echo $reporte->semana_numero; ?>/<?php echo $reporte->anio; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Período</div>
                <div class="data-value"><?php echo $reporte->semana_inicio?->format('d/m/Y'); ?> — <?php echo $reporte->semana_fin?->format('d/m/Y'); ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha Límite</div>
                <div class="data-value"><?php echo $reporte->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i'); ?> hrs</div>
            </div>
            <div class="data-row">
                <div class="data-label"><?php echo $esEdicion ? 'Fecha Edición' : 'Fecha Envío'; ?></div>
                <div class="data-value">
                    <?php echo ($esEdicion ? $reporte->fecha_ultimo_envio : $reporte->fecha_envio_original)?->setTimezone('America/Lima')->format('d/m/Y H:i'); ?> hrs
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">KPI</div>
                <div class="data-value">
                    <?php $kpi = (float)$reporte->kpi_porcentaje; ?>
                    <span style="font-weight:700;font-size:16px;color:<?php echo $kpi>=100?'#059669':($kpi>=75?'#d97706':($kpi>=50?'#0891b2':'#dc2626')); ?>">
                        <?php echo $reporte->kpiLabel(); ?>

                    </span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Archivos</div>
                <div class="data-value"><?php echo count($reporte->archivos ?? []); ?> archivo(s)</div>
            </div>
            <?php if($reporte->notas): ?>
            <div class="data-row">
                <div class="data-label">Notas</div>
                <div class="data-value"><?php echo $reporte->notas; ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="highlight-box">
        💡 <strong>KPI Comentarios:</strong> Jueves = 100% · Viernes = 75% · Sábado = 50% · 3+ días = 0%
    </div>

    <div class="cta-wrapper">
        <a href="<?php echo $url; ?>" class="cta-button">Ver en el Sistema</a>
    </div>

    <p style="font-size:12px;color:#94a3b8;text-align:center;">
        Enviado por: <?php echo $reporte->user?->name ?? 'Sistema'; ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.productividad', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/productividad/comentarios_submitida.blade.php ENDPATH**/ ?>