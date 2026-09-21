<div class="modal fade" id="ProcedureReferenceModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ProcedureReferenceModalLabel">Procedure Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonProcedureReferenceForm" class="row g-3 needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1 ">
                            <div class="col-lg-2">
                                <label for="procedure_reference" class="form-label">Procedure Reference <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="procedure_reference" id="procedure_reference" class="form-control"  required>
                                
                                
                                <div class="invalid-tooltip">
                                    Enter Procedure Reference.
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <?php if(hasAccess("procedure_reference","add")): ?>
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                <?php endif; ?>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

<?php $__env->startPush('script-modal'); ?>
    <script src="<?php echo e(URL::asset('views/js/procedure_reference.js?ver='.getJsVersion())); ?>"></script>
<?php $__env->stopPush(); ?>
<?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/modals/procedure_reference_modal.blade.php ENDPATH**/ ?>