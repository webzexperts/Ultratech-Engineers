<div class="modal fade" id="RTCameraDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="RTCameraDetailsModalLabel">Camera - RT Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="RTCameraDetailsForm" class="row g-3 needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="rtcd_id" id="rtcd_id" value="0"/>
                    <input type="hidden" name="old_loading_date" id="old_loading_date">

                        <div class="row mt-2">
                            <div class="col-md-8">
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Date of Loading <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control date-picker" id="rtcd_last_of_loading_date" name="rtcd_last_of_loading_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">
                                            Enter Date of Loading.
                                        </div>
                                    </div>
                                </div>
                            

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Initial Activity (Ci) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isNumberKey" id="rtcd_initial_activity_ci" name="rtcd_initial_activity_ci" required>
                                        <div class="invalid-tooltip">
                                            Enter Initial Activity (Ci)
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Source Size</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rtcd_source_size" name="rtcd_source_size" >
                                        <div class="invalid-tooltip">
                                            Enter Source Size
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Pencil No.</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rtcd_pencil_no" name="rtcd_pencil_no" >
                                        <div class="invalid-tooltip">
                                            Enter Pencil No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label class="form-label">IGA No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rtcd_iga_no" name="rtcd_iga_no">
                                    </div>
                                </div>

                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-4">
                                        <label class="form-label">Decay Chart </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="rtcd_decay_chart" id="rtcd_decay_chart" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="rtcd_decay_chart_doc" name="rtcd_decay_chart_doc" />
                                                <a href="#" data-remove="rtcd_decay_chart" id="rtcd_decay_chart_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileDecayChart(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="rtcd_decay_chart_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select Decay Chart.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submitbtn">Add</button>
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
                    </div>
            </form>
        </div>
    </div>
</div><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/modals/modals-details/rt_camera_details.blade.php ENDPATH**/ ?>