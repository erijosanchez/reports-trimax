# <?php echo $exception->class(); ?> - <?php echo $exception->title(); ?>


<?php echo $exception->message(); ?>


PHP <?php echo PHP_VERSION; ?>

Laravel <?php echo app()->version(); ?>

<?php echo $exception->request()->httpHost(); ?>


## Stack Trace

<?php $__currentLoopData = $exception->frames(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $frame): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php echo $index; ?> - <?php echo $frame->file(); ?>:<?php echo $frame->line(); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

## Request

<?php echo $exception->request()->method(); ?> <?php echo \Illuminate\Support\Str::start($exception->request()->path(), '/'); ?>


## Headers

<?php $__empty_1 = true; $__currentLoopData = $exception->requestHeaders(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
* **<?php echo $key; ?>**: <?php echo $value; ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
No header data available.
<?php endif; ?>

## Route Context

<?php $__empty_1 = true; $__currentLoopData = $exception->applicationRouteContext(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php echo $name; ?>: <?php echo $value; ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
No routing data available.
<?php endif; ?>

## Route Parameters

<?php if($routeParametersContext = $exception->applicationRouteParametersContext()): ?>
<?php echo $routeParametersContext; ?>

<?php else: ?>
No route parameter data available.
<?php endif; ?>

## Database Queries

<?php $__empty_1 = true; $__currentLoopData = $exception->applicationQueries(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as ['connectionName' => $connectionName, 'sql' => $sql, 'time' => $time]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
* <?php echo $connectionName; ?> - <?php echo $sql; ?> (<?php echo $time; ?> ms)
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
No database queries detected.
<?php endif; ?>
<?php /**PATH /var/www/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/markdown.blade.php ENDPATH**/ ?>