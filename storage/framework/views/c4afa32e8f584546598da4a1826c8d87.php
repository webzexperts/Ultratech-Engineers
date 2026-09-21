<div class="modal fade" id="StateModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="StateModalLabel">State</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonStateForm" class="row g-3 needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="id">

                    <div class="row mt-2">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="state" class="form-label">State <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8 position-relative">
                                        <input type="text" name="state" id="state" class="form-control"  required>
                                        
                                        
                                        <div class="invalid-tooltip">
                                            Enter State.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="country_id" class="form-label">Country <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8">
                                    <div class="col-8 position-relative otherselectwidth">
                                        <select class="js-example-basic-single suggest_country_name" name="country_id" id="country_id" required>
                                            <option value="">Select Country</option>
                                            <?php $__empty_1 = true; $__currentLoopData = getCountries(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <option value="<?php echo e($country->id); ?>"><?php echo e($country->country_name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Country.
                                        </div>

                                        <?php if(hasAccess("country","add")): ?>
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedCountry(true)" data-bs-target="#CountryModal"></i>
                                        <?php endif; ?>
                                    </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="state_code" class="form-label">GST Code </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="number" name="state_code" id="state_code" class="form-control skip-tab" readonly>
                                        <div class="invalid-tooltip">
                                            Enter GST Code.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    <?php if(hasAccess("state","add")): ?>
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    <?php endif; ?>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('script-modal'); ?>
    <script src="<?php echo e(URL::asset('views/js/state.js?ver='.getJsVersion())); ?>"></script>
<?php $__env->stopPush(); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/modals/state_modal.blade.php ENDPATH**/ ?>