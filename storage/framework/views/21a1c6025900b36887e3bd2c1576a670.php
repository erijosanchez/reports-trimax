<?php $__env->startSection('title', 'Acceso Denegado'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex align-items-center justify-content-center content-wrapper" style="min-height: 80vh;">
    <div class="text-center">
        
        <div class="mb-4">
            <img src="<?php echo asset('assets/img/ltr.png'); ?>" alt="TRIMAX" style="height: 60px;" 
                onerror="this.style.display='none'">
        </div>

        
        <h1 style="font-size: 8rem; font-weight: 900; color: #e3e6f0; line-height: 1;">
            4<span style="color: #3B82F6;">0</span>3
        </h1>

        
        <div class="mb-3">
            <i class="mdi-lock-outline mdi" style="font-size: 4rem; color: #EF4444;"></i>
        </div>

        <h3 class="mb-2 fw-bold">Acceso Denegado</h3>
        <p class="mb-4 text-muted">
            No tienes permisos para acceder a esta sección.<br>
            Si crees que esto es un error, contacta al administrador del sistema.
        </p>

        
        <div class="d-flex justify-content-center gap-2">
            <a href="<?php echo route('home'); ?>" class="btn btn-primary">
                <i class="me-1 mdi mdi-home"></i> Ir al Inicio
            </a>
            <a href="javascript:history.back()" class="btn-outline-secondary btn">
                <i class="mdi-arrow-left me-1 mdi"></i> Volver
            </a>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/errors/403.blade.php ENDPATH**/ ?>