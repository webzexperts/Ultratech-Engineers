<script src="<?php echo e(URL::asset('js/jquery.dataTables.min.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('js/dataTables.bootstrap5.min.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('js/dataTables.responsive.min.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('js/dataTables.buttons.min.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('js/buttons.html5.min.js')); ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="<?php echo e(URL::asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/quill/quill.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/form-editor.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/datatables.init.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script src="<?php echo e(URL::asset('build/js/pages/sweetalerts.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/form-validation.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('js/select2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/pages/select2.init.js')); ?>"></script>
<script src="<?php echo e(URL::asset('js/jquery-ui.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script> 
<script src="<?php echo e(URL::asset('js/toastr.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('views/js/custom.js?ver='.getJsVersion())); ?>"></script>
<script src="<?php echo e(URL::asset('views/js/common.js?ver='.getJsVersion())); ?>"></script>
<?php echo $__env->yieldContent('script-manage'); ?>
<?php echo $__env->yieldPushContent('script-modal'); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/layouts/common-scripts.blade.php ENDPATH**/ ?>