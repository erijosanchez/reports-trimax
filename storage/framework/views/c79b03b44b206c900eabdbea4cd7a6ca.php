<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $__env->yieldContent('title'); ?> - Reportes Trimax</title>
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <!-- plugins:css -->
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/feather/feather.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/mdi/css/materialdesignicons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/ti-icons/css/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/typicons/typicons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/simple-line-icons/css/simple-line-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/vendors/css/vendor.bundle.base.css'); ?>">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="<?php echo asset('assets/js/select.dataTables.min.css'); ?>">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="<?php echo asset('assets/css/vertical-layout-light/style.css'); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
    <!-- endinject -->
    <link rel="shortcut icon" href="<?php echo asset('assets/img/fv.png'); ?>" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?php echo $__env->make('includes._navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial -->
            <!-- partial:partials/_sidebar.html -->
            <?php echo $__env->make('includes.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <!-- partial -->
            <div class="main-panel">
                <?php echo $__env->yieldContent('content'); ?>
                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->
                <?php echo $__env->make('includes._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    
    <!-- Asistente IA Trimax -->
    <?php if(auth()->guard()->check()): ?>
        <?php echo $__env->make('components.ai-assistant', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <!-- plugins:js -->
    <script src="<?php echo asset('assets/vendors/js/vendor.bundle.base.js'); ?>"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="<?php echo asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/vendors/progressbar.js/progressbar.min.js'); ?>"></script>

    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="<?php echo asset('assets/js/off-canvas.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/hoverable-collapse.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/template.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/settings.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/todolist.js'); ?>"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="<?php echo asset('assets/js/dashboard.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/Chart.roundedBarCharts.js'); ?>"></script>
    <!-- End custom js for this page-->
    <?php echo $__env->yieldContent('scripts'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>