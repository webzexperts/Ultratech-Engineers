<script src="<?php echo e(URL::asset('js/jquery-3.6.0.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/simplebar/simplebar.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/node-waves/waves.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/feather-icons/feather.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/plugins.js')); ?>"></script>
<?php echo $__env->yieldContent('script'); ?>

<script>
    // Enforce static backdrop globally for all Bootstrap modals (allow Esc key closing)
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.Default.backdrop = 'static';
        bootstrap.Modal.Default.keyboard = true;
    }

    jQuery(document).on('show.bs.modal', '.modal', function () {
        jQuery(this).attr('data-bs-backdrop', 'static');
        jQuery(this).attr('data-bs-keyboard', 'true');

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalInstance = bootstrap.Modal.getInstance(this);
            if (modalInstance && modalInstance._config) {
                modalInstance._config.backdrop = 'static';
                modalInstance._config.keyboard = true;
            }
        }
    });
     // Disable bounce animation on backdrop click
    jQuery(document).on('hidePrevented.bs.modal', '.modal', function (e) {
        e.preventDefault();
    });
</script><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/layouts/vendor-scripts.blade.php ENDPATH**/ ?>