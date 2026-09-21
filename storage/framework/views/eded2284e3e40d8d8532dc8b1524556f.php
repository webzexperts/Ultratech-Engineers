<?php $__env->startSection('title'); ?>
<?php echo app('translator')->get('translation.dashboards'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="blank-container mb-3">
    <div class="welcome-text">
        <h1 class="name_as_is">Welcome, <?php echo e(Auth::user()->user_name); ?></h1>
    </div>
</div>

<?php if(isset($permittedLocations)): ?>
    <!-- Dashboard Cards Grid -->
    <div class="row g-4">
        <!-- Card 1: Pending Inter Location Transfers -->
        <!-- <?php if(hasAccess('inter_location_transfer', 'manage')): ?> -->
            <?php echo $__env->make('dashboards.pending_transfer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- <?php endif; ?> -->

        <!-- Card 2: Radiography Sources & Cameras -->
        <!-- <?php if(hasAccess('rt_camera', 'manage')): ?> -->
            <?php echo $__env->make('dashboards.camera', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- <?php endif; ?> -->

        <!-- Card 3: Location Production Output -->
        <!-- <?php if(hasAccess('production_entry', 'manage')): ?> -->
            <?php echo $__env->make('dashboards.production', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- <?php endif; ?> -->

        <!-- Card 4: Industrial X-Ray Film Stock -->
        <!-- <?php if(hasAccess(['item_opening_prod_area', 'item_opening', 'production_entry'], 'manage')): ?> -->
            <?php echo $__env->make('dashboards.film_stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- <?php endif; ?> -->
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/dashboard.blade.php ENDPATH**/ ?>