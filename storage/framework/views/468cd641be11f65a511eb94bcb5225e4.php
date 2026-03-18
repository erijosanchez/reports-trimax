

<?php $__env->startSection('badge', 'ALERTA MARKETING'); ?>

<?php $__env->startSection('alert_banner'); ?>
    <div
        class="email-alert <?php echo $survey->experience_rating === 1 || $survey->service_quality_rating === 1 ? 'email-alert--danger' : 'email-alert--warning'; ?>">
        <?php echo $survey->experience_rating === 1 || $survey->service_quality_rating === 1 ? '🔴' : '🟡'; ?>

        Encuesta con calificación negativa recibida
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>

    <p class="email-greeting">¡Hola <?php echo $notifiable->name; ?>!</p>
    <p class="email-intro">
        Se registró una nueva encuesta con calificación baja en el sistema de marketing.
        Te notificamos para que puedas tomar acción a tiempo.
    </p>

    
    <div class="data-card">
        <div class="data-card__header">👤 Evaluado</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Nombre</div>
                <div class="data-value"><strong><?php echo $evaluado->name; ?></strong></div>
            </div>
            <div class="data-row">
                <div class="data-label">Tipo</div>
                <div class="data-value">
                    <?php if($evaluado->role === 'consultor'): ?>
                        Consultor
                    <?php elseif($evaluado->role === 'trimax'): ?>
                        TRIMAX General
                    <?php else: ?>
                        Sede — <?php echo $evaluado->location; ?>

                    <?php endif; ?>
                </div>
            </div>
            <?php if($evaluado->location && $evaluado->role === 'sede'): ?>
                <div class="data-row">
                    <div class="data-label">Ubicación</div>
                    <div class="data-value"><?php echo $evaluado->location; ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="data-card">
        <div class="data-card__header">⭐ Calificaciones recibidas</div>
        <div class="data-card__body">
            <?php
                $labels = [1 => 'Muy Insatisfecho 😞', 2 => 'Insatisfecho 😐', 3 => 'Feliz 🙂', 4 => 'Muy Feliz 😊'];
                $combined = number_format(($survey->experience_rating + $survey->service_quality_rating) / 2, 2);
            ?>
            <div class="data-row">
                <div class="data-label">Experiencia</div>
                <div class="data-value">
                    <span
                        class="badge-estado <?php echo $survey->experience_rating <= 1 ? 'badge-urgente' : ($survey->experience_rating === 2 ? 'badge-en-proceso' : 'badge-contratado'); ?>">
                        <?php echo $labels[$survey->experience_rating] ?? 'N/A'; ?>

                    </span>
                    <span
                        style="color:#64748b; font-size:12px; margin-left:6px;">(<?php echo $survey->experience_rating; ?>/4)</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Atención</div>
                <div class="data-value">
                    <span
                        class="badge-estado <?php echo $survey->service_quality_rating <= 1 ? 'badge-urgente' : ($survey->service_quality_rating === 2 ? 'badge-en-proceso' : 'badge-contratado'); ?>">
                        <?php echo $labels[$survey->service_quality_rating] ?? 'N/A'; ?>

                    </span>
                    <span
                        style="color:#64748b; font-size:12px; margin-left:6px;">(<?php echo $survey->service_quality_rating; ?>/4)</span>
                </div>
            </div>
            <div class="data-row">
                <div class="data-label">Promedio</div>
                <div class="data-value data-value--mono"><?php echo $combined; ?> / 4.00</div>
            </div>
        </div>
    </div>

    
    <div class="data-card">
        <div class="data-card__header">🧑 Datos del cliente</div>
        <div class="data-card__body">
            <div class="data-row">
                <div class="data-label">Nombre</div>
                <div class="data-value"><?php echo $survey->client_name ?: 'Anónimo'; ?></div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha</div>
                <div class="data-value"><?php echo $survey->created_at->format('d/m/Y H:i'); ?></div>
            </div>
        </div>
    </div>

    
    <?php if($survey->comments): ?>
        <div class="highlight-box">
            💬 <strong>Comentario del cliente:</strong><br>
            "<?php echo $survey->comments; ?>"
        </div>
    <?php endif; ?>

    
    <div class="cta-wrapper">
        <a href="<?php echo url('/marketing'); ?>" class="cta-button">
            Ver Dashboard Marketing
        </a>
    </div>

    <hr class="divider">

    <p style="font-size:12px; color:#94a3b8; text-align:center;">
        Este correo se generó automáticamente porque se recibió una encuesta con calificación ≤ 2.<br>
        Solo los usuarios con rol <strong>Marketing</strong> y <strong>Super Admin</strong> reciben esta alerta.
    </p>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/emails/marketing/survey-alert.blade.php ENDPATH**/ ?>