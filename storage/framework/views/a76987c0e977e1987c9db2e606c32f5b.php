<?php $__env->startSection('badge', 'EXTENSIÓN MASIVA'); ?>

<?php $__env->startSection('alert_banner'); ?>
<div class="email-alert email-alert--success">
    <span>📅</span>
    <span><?php echo $esAdmin ? 'Resumen de Extensión Masiva de Acuerdos' : 'Tus Acuerdos Comerciales han sido Extendidos'; ?></span>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
<p class="email-greeting">¡Hola, <?php echo $destinatario; ?>!</p>

<?php if($esAdmin): ?>
<p class="email-intro">
    Se ha realizado una <strong>extensión masiva</strong> de acuerdos comerciales.
    A continuación el resumen completo de los <strong><?php echo count($acuerdos); ?> acuerdo(s)</strong> extendidos.
</p>
<?php else: ?>
<p class="email-intro">
    Los siguientes acuerdos comerciales a tu cargo han sido <strong>extendidos</strong> con una nueva fecha de vencimiento.
    Revisa el detalle a continuación.
</p>
<?php endif; ?>


<div class="data-card" style="margin-bottom:20px;">
    <div class="data-card__header">Detalles de la Extensión</div>
    <div class="data-card__body">
        <div class="data-row">
            <div class="data-label">Nueva Fecha Fin</div>
            <div class="data-value"><strong style="color:#065f46;"><?php echo \Carbon\Carbon::parse($nuevaFecha)->format('d/m/Y'); ?></strong></div>
        </div>
        <div class="data-row">
            <div class="data-label">Motivo</div>
            <div class="data-value"><?php echo $motivo; ?></div>
        </div>
        <div class="data-row">
            <div class="data-label">Extendido por</div>
            <div class="data-value"><?php echo $extendidoPor; ?></div>
        </div>
        <div class="data-row">
            <div class="data-label">Fecha de acción</div>
            <div class="data-value"><?php echo now()->format('d/m/Y H:i'); ?></div>
        </div>
        <?php if($esAdmin): ?>
        <div class="data-row">
            <div class="data-label">Total extendidos</div>
            <div class="data-value"><strong><?php echo count($acuerdos); ?> acuerdo(s)</strong></div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="data-card">
    <div class="data-card__header">
        <?php echo $esAdmin ? 'Listado Completo de Acuerdos Extendidos' : 'Tus Acuerdos Extendidos'; ?>

    </div>
    <div class="data-card__body">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#1e3a8a; color:#fff;">
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">N° ACUERDO</th>
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">RAZÓN SOCIAL</th>
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">SEDE</th>
                    <?php if($esAdmin): ?>
                    <th style="padding:8px 12px; text-align:left; font-weight:700; letter-spacing:0.05em;">CONSULTOR</th>
                    <?php endif; ?>
                    <th style="padding:8px 12px; text-align:center; font-weight:700; letter-spacing:0.05em;">NUEVA FECHA FIN</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $acuerdos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $acuerdo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="background:<?php echo $i % 2 === 0 ? '#ffffff' : '#f8fafc'; ?>; border-bottom:1px solid #e2e8f0;">
                    <td style="padding:8px 12px; font-family:'Courier New',monospace; color:#2563eb; font-weight:700;">
                        <?php echo $acuerdo->numero_acuerdo; ?>

                    </td>
                    <td style="padding:8px 12px; color:#1e293b;"><?php echo $acuerdo->razon_social; ?></td>
                    <td style="padding:8px 12px; color:#475569;"><?php echo $acuerdo->sede; ?></td>
                    <?php if($esAdmin): ?>
                    <td style="padding:8px 12px; color:#475569;"><?php echo $acuerdo->consultor; ?></td>
                    <?php endif; ?>
                    <td style="padding:8px 12px; text-align:center;">
                        <span style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:20px; font-weight:700;">
                            <?php echo \Carbon\Carbon::parse($acuerdo->fecha_fin)->format('d/m/Y'); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>

<div class="cta-wrapper">
    <a href="<?php echo url('/comercial/acuerdos'); ?>" class="cta-button">Ver Acuerdos en el Sistema</a>
</div>

<p style="font-size:13px; color:#64748b; text-align:center; margin-top:8px;">
    Este correo fue generado automáticamente como resultado de una extensión masiva en el CRM.
</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.comercial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/comercial/acuerdos_extendidos_masivo.blade.php ENDPATH**/ ?>