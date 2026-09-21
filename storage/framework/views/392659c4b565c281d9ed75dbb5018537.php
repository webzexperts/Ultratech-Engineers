<div class="modal fade" id="CityModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CityModalLabel">City</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonCityForm" class="row g-3 needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" id="id"/>
                    <input type="hidden" name="page_name" id="page_name" value="city"/>

                    <div class="row mt-2">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <div class="row g-2 mb-1">
                                    <div class="col-3">
                                        <label for="city" class="form-label">City <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8 position-relative">
                                        <input type="text" name="city" id="city" class="form-control"  required>
                                        <!-- 
                                         -->
                                        <div class="invalid-tooltip">
                                            Enter City.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-3">
                                        <label for="state_id" class="form-label">State <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">

                                    <div class="col-8 position-relative otherselectwidth">
                                        <select class="js-example-basic-single suggest_state_name" name="state_id" id="state_id" required>
                                            <option value="">Select State</option>
                                            <?php $__empty_1 = true; $__currentLoopData = getStates(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <option value="<?php echo e($state->id); ?>"><?php echo e($state->state); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <?php endif; ?>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select State.
                                        </div>

                                        <?php if(hasAccess("state","add")): ?>
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedState(true)" data-bs-target="#StateModal"></i>
                                        <?php endif; ?>
                                    </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-3">
                                        <label for="state_code" class="form-label">GST Code </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="state_code" id="state_code" class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-3">
                                        <label for="country_name" class="form-label">Country </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="country_name" id="country_name" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    <?php if(hasAccess("city","add")): ?>
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    <?php endif; ?>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('script-modal'); ?>
    <script src="<?php echo e(URL::asset('views/js/city.js?ver='.getJsVersion())); ?>"></script>
<?php $__env->stopPush(); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/modals/city_modal.blade.php ENDPATH**/ ?>