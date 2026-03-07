<?php $__env->startSection('title', 'Verificar 2FA'); ?>

<?php $__env->startSection('content'); ?>
    <div style="max-width:400px;margin:4rem auto;">
        <h2>Verificación 2FA</h2>

        <p>Ingresa el código de tu aplicación de autenticación:</p>

        <form method="POST" action="<?php echo route('2fa.verify'); ?>">
            <?php echo csrf_field(); ?>

            <div style="margin-bottom:1rem;">
                <label>Código:</label>
                <input type="text" name="code" required autofocus style="width:100%;padding:0.5rem;margin-top:0.25rem;">
            </div>

            <button type="submit"
                style="width:100%;padding:0.75rem;background:#007bff;color:white;border:none;cursor:pointer;">
                Verificar
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/auth/two-factor-verify.blade.php ENDPATH**/ ?>