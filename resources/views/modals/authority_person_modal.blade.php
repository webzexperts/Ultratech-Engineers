<div class="modal fade" id="AuthorityPersonModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="AuthorityPersonModalLabel">Authority Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonAuthorityPersonForm" class="row g-3 needs-validation" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="operator_type" id="operator_type">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <!-- Operator -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="operator" class="form-label">Operator <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="operator" id="operator" class="form-control" maxlength="100" required>
                                        <div class="invalid-tooltip">
                                            Enter Operator.
                                        </div>
                                    </div>
                                </div>

                                <!-- Designation -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="designation" class="form-label">Designation</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="designation" id="designation" class="form-control" maxlength="100">
                                        <div class="invalid-tooltip">
                                            Enter Designation.
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature (file upload) -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="signature" class="form-label">Signature</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="file" name="signature" id="signature" class="form-control" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="signature_doc" name="signature_doc" />
                                                <a href="#" data-remove="signature" id="signature_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileAuthorityPerson(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="signature_prev">View</a>
                                                <div class="invalid-tooltip">
                                                    Upload Signature.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="status" class="form-label">Status</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="status" id="status">
                                            <option value="Active">Active</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Status.
                                        </div>
                                    </div>
                                </div>

                                <!-- Operator Type (checkboxes -> hidden operator_type) -->
                                <div class="row g-1  mb-2">
                                    <div class="col-lg-3">
                                        <!-- <label class="form-check-label" for="tested_by">
                                            Tested By
                                        </label> -->
                                    </div>
                                    <div class="col-lg-4 d-flex gap-4 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input operator_type_chk" type="checkbox" name="tested_by" id="tested_by" value="Yes">
                                            <label class="form-check-label" for="tested_by">
                                            Tested By
                                        </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1  mb-2">
                                    <div class="col-lg-3">
                                        <!-- <label class="form-check-label" for="reviewed_by">
                                            Reviewed By
                                        </label> -->
                                    </div>
                                    <div class="col-lg-4 d-flex gap-4 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input operator_type_chk" type="checkbox" name="reviewed_by" id="reviewed_by" value="Yes">
                                            <label class="form-check-label" for="reviewed_by">
                                            Reviewed By
                                        </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1  mb-2">
                                    <div class="col-lg-3">
                                        <!-- <label class="form-check-label" for="authorized_by">
                                            Authorized By
                                        </label> -->
                                    </div>
                                    <div class="col-lg-4 d-flex gap-4 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input operator_type_chk" type="checkbox" name="authorized_by" id="authorized_by" value="Yes">
                                            <label class="form-check-label" for="authorized_by">
                                            Authorized By
                                        </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1  mb-2">
                                    <div class="col-lg-3">
                                        <!-- <label class="form-check-label" for="checked_by">
                                            Checked By
                                        </label> -->
                                    </div>
                                    <div class="col-lg-4 d-flex gap-4 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input operator_type_chk" type="checkbox" name="checked_by" id="checked_by" value="Yes">
                                             <label class="form-check-label" for="checked_by">
                                            Checked By
                                        </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1  mb-2">
                                    <div class="col-lg-3">
                                        <!-- <label class="form-check-label" for="approved_by">
                                            Approved By
                                        </label> -->
                                    </div>
                                    <div class="col-lg-4 d-flex gap-4 align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input operator_type_chk" type="checkbox" name="approved_by" id="approved_by" value="Yes">
                                            <label class="form-check-label" for="approved_by">
                                            Approved By
                                        </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="status" class="form-label">RSO / Radiographer</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="authority_person_type_value_fix" id="authority_person_type_value_fix">
                                            <option value="">Select RSO / Radiographer</option>
                                            <option value="RSO">RSO</option>
                                            <option value="Radiographer">Radiographer</option>
                                        </select>
                                        
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 position-relative" id="location_row">
                                    <div class="col-3">
                                        <label for="status" class="form-label">Location <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="current_location_id" id="current_location_id">
                                            <option value="">Select Location</option>
                                            @forelse(getLocations() as $location)
                                                <option value="{{ $location->location_id }}">{{ $location->location_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Location.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1" id="validity_row">
                                    <div class="col-3">
                                        <label class="from-label">Validity <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control date-picker" id="validity" name="validity" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Validity 
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 position-relative" id="pms_row">
                                    <div class="col-3">
                                        <label for="pms_no" class="form-label">PMS No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="pms_no" id="pms_no" class="form-control" maxlength="100">
                                        <div class="invalid-tooltip">
                                            Enter PMS No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative" id="certificate_row">
                                    <div class="col-3">
                                        <label class="form-label">Certificate <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="certificate" id="certificate" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="certificate_doc" name="certificate_doc" />
                                                <a href="#" data-remove="certificate" id="certificate_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileCertificate(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="certificate_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select Certificate.
                                                </div>
                                            </div>
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
            @if(hasAccess("authority_person","add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
            @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/authority_person.js?ver='.getJsVersion()) }}"></script>
@endpush
