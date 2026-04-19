<?php $__env->startSection('badge', 'ALERTA DE VENCIMIENTO'); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div class="email-alert email-alert--warning">
        ⏰ Tu reporte de Caja Chica vence en 1 hora — límite 2:00 PM
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <p class="email-greeting">Hola, <?php echo $notifiable->name ?? 'usuario'; ?>.</p>
    <p class="email-intro">
        Tienes <strong>1 hora</strong> para enviar tu reporte de Caja Chica.
        Recuerda que el límite es el <strong>sábado a las 2:00 PM</strong>.
        Si no lo envías hoy sábado, tu KPI será <strong>0%</strong>.
    </p>

    <div class="sla-box sla-box--warning">
        <div class="sla-box__days">1h</div>
        <div class="sla-box__label">Tiempo restante para enviar</div>
    </div>

    <div class="data-card">
        <div class="data-card__header">Datos del Reporte Pendiente</div>
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
                <div class="data-label">Fecha Límite</div>
                <div class="data-value" style="color:#d97706;font-weight:700;">
                    <?php echo $reporte->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i'); ?> hrs
                </div>
            </div>
        </div>
    </div>

    <div class="highlight-box">
        💡 <strong>Recuerda:</strong> El KPI solo cuenta si envías el reporte hoy sábado. Enviar otro día = 0%.
    </div>

    <div class="cta-wrapper">
        <a href="<?php echo $url; ?>" class="cta-button">Enviar Reporte Ahora</a>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.productividad', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/productividad/caja_chica_alerta.blade.php ENDPATH**/ ?>