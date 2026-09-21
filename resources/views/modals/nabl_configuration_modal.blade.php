<div class="modal fade" id="NABLConfigurationModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="LocationModalLabel">NABL Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonNABLConfigurationForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="nabl_location" class="form-label">NABL Location <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="nabl_location" id="nabl_location" class="form-control" required>
                                <div class="invalid-tooltip">
                                    Enter NABL Location.
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="tc_no" class="form-label">TC No. <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="tc_no" id="tc_no" class="form-control" required>
                                <div class="invalid-tooltip" id="tc_no_error">
                                    Please Enter TC No.
                                </div>
                            </div>
                        </div> -->

                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="tc_no" class="form-label">TC No. <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="tc_no" id="tc_no" class="form-control" pattern="[A-Za-z0-9]+" required>
                                <div class="invalid-tooltip" id="tc_no_error">
                                    Enter TC No.
                                </div>
                            </div>
                        </div>
                        <div class="row g-1" style="display: none;">
                            <div class="col-lg-1">
                                <label for="location_no" class="form-label">Location No. <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="location_no" id="location_no" class="form-control isNumberKey">
                                <div class="invalid-tooltip">
                                    Enter Location No.
                                </div>
                            </div>
                        </div>

                        <div class="row g-1" style="display: none;">
                            <div class="col-lg-1">
                                <label for="nabl_type" class="form-label">NABL Type <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <select class="js-example-basic-single skip-tab" name="nabl_type" id="nabl_type">
                                    <!-- <option value="">Select NABL Type</option> -->
                                    <option value="F" selected>F - Full</option>
                                    <option value="P">P - Partial </option>
                                </select>
                                <div class="invalid-tooltip">
                                    Select NABL Type.
                                </div>
                            </div>
                        </div>

                        <!-- <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="ulr_no_format" class="form-label">ULR No. Format</label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="ulr_no_format" id="ulr_no_format" class="form-control skip-tab">
                            </div>
                        </div> -->
                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="ulr_no_format" class="form-label">ULR No. Format</label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="ulr_no_format" id="ulr_no_format" class="form-control skip-tab" readonly>
                            </div>
                        </div>

                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="status" class="form-label">Status <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <select class="js-example-basic-single" name="status" id="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Deactive">Deactive</option>
                                </select>
                            </div>
                            <div class="invalid-tooltip">
                                Select Status.
                            </div>
                        </div>
                    </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("nabl_configuration","add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>

            </form>
        </div>
    </div>
</div>

@push('script-modal')
<script src="{{ URL::asset('views/js/nabl_configuration.js?ver='.getJsVersion()) }}"></script>
@endpush
