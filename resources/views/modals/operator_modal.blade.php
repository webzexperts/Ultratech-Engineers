<div class="modal fade" id="OperatorModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-fullscreen-right">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="OperatorModalLabel">Operator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonOperatorForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="delete_image" id="delete_image" value="0">
                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label for="category" class="form-label col-form-label">Category <sup class="astric">*</sup></label>
                        </div>

                        <div class="col-lg-6">
                            <select class="js-example-basic-single" name="category" id="category" required>
                                <option value="">Select Category</option>
                                <option value="radiographer">Radiographer</option>
                                <option value="assistant">Assistant</option>
                                <option value="tested_by">Tested By</option>
                                <option value="signature">Signature</option>
                            </select>
                            <div class="invalid-tooltip">
                                Please Select Category
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label for="operator" class="form-label col-form-label">Operator <sup class="astric">*</sup></label>
                        </div>

                        <div class="col-lg-6">
                            <input type="text" name="operator" id="operator" class="form-control" onkeyup="suggestOperator(event,this)" onfocusout="verifyOperator()" required>
                            <div id="operator_list" class="suggestion_list"></div>
                            <input type="hidden" name="operator_suggesion" id="operator_suggesion">
                            <div class="invalid-tooltip">
                                Please Enter Operator
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="designation" class="col-lg-3 col-form-label" id="designation_label">Designation</label>
                        <div class="col-lg-6 position-relative">
                            <input type="text" class="form-control" id="designation" name="designation" onkeyup="suggestDesignation(event,this)" aria-describedby="designationTooltip"/>
                            <div id="designationTooltip" class="invalid-tooltip default-hide">
                                Please Enter Designation
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label for="status" class="form-label col-form-label">Status </label>
                        </div>

                        <div class="col-lg-6">
                            <select class="js-example-basic-single" name="status" id="status">
                                <option value="1">Active</option>
                                <option value="0">Deactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label for="signature" class="form-label col-form-label">Signature</label>
                        </div>

                        <div class="col-lg-6">
                            <div class="upload-card">
                                <img id="sign_preview" class="upload-sign-signature d-none" src="">
                                <h6>Signature</h6>

                                <div class="action-btns">
                                    <button type="button" class="btn btn-danger btn-sm" id="delete_sign_btn">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>

                                    <label class="btn btn-primary btn-sm mb-0" tabindex="0">
                                        <i class="ri-edit-line"></i>
                                        <input type="file" name="upload_sign_file" id="upload_sign_file" hidden accept="image/*">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/operator.js?ver='.getJsVersion()) }}"></script>
@endpush