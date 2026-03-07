<?php $__env->startSection('title', 'Configurar 2FA'); ?>

<?php $__env->startSection('content'); ?>
    <div style="max-width:600px;margin:2rem auto;">
        <h2>Configurar Autenticación de Dos Factores</h2>

        <p>Escanea este código QR con tu app de autenticación (Google Authenticator, Authy, etc.):</p>

        <div style="text-align:center;margin:2rem 0;">
            <img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=<?php echo urlencode($qrCodeUrl); ?>"
                alt="QR Code">
        </div>

        <p>O ingresa este código manualmente: <strong><?php echo $secret; ?></strong></p>

        <form method="POST" action="<?php echo route('2fa.enable'); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="secret" value="<?php echo $secret; ?>">

            <div style="margin-bottom:1rem;">
                <label>Código de verificación:</label>
                <input type="text" name="code" required style="width:100%;padding:0.5rem;margin-top:0.25rem;">
            </div>

            <button type="submit" style="padding:0.75rem 2rem;background:#28a745;color:white;border:none;cursor:pointer;">
                Habilitar 2FA
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/auth/two-factor-setup.blade.php ENDPATH**/ ?>