 <div class="modal fade" id="OrderAcceptanceModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="Order AcceptanceModalLabel">Order Acceptance</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonOAForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">

                                <div class="card-body">
                                    <div class="row gy-4">

                                        <!-- COLUMN 1 -->
                                        <div class="col-xxl-4 col-md-4">

                                            <div class="mb-3">
                                                    <label class="form-label">Order Acceptance No. <sup class="astric">*</sup></label>
                                                    <div class="d-flex gap-2 position-relative">
                                                        <input type="text"                                                   class="form-control"
                                                            id="oa_sequence"
                                                            name="oa_sequence"
                                                            style="max-width:80px"
                                                            autofocus
                                                            required>

                                                        <input type="text"
                                                            class="form-control"
                                                            id="oa_number"
                                                            name="oa_number"
                                                            tabindex="-1"
                                                            readonly>

                                                        <input type="text"
                                                            class="form-control trans-date-picker"
                                                            id="oa_date"
                                                            name="oa_date"
                                                            required
                                                            autocomplete="off">
                                                    </div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Type <sup class="astric">*</sup></label>
                                                <div class="d-flex gap-2 position-relative">

                                                    <select class="js-example-basic-single"
                                                            name="oa_type_id"
                                                            id="oa_type_id"
                                                            required>
                                                        <option value="From Quotation">From Quotation </option>
                                                        <option value="Manual">Manual </option>
                                                    </select>
                                                    <div class="invalid-tooltip">
                                                        Please Select Type
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Customer -->
                                            <div class="mb-2">
                                                    <label class="form-label">Customer <sup class="astric">*</sup></label>
                                                    <div class="d-flex gap-2 position-relative">
                                                        <select class="js-example-basic-single"
                                                                name="oa_customer_id"
                                                                id="oa_customer_id"
                                                                required>
                                                            <option value="">Select Customer</option>
                                                        </select>
                                                        <div class="invalid-tooltip">
                                                            Please Select Customer
                                                        </div>

                                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#OAPendingModal" disabled>
                                                            Pending
                                                        </button>
                                                    </div>
                                            </div>

                                        </div>

                                        <!-- COLUMN 2 -->
                                        <div class="col-xxl-4 col-md-4">


                                            <!-- Kind Attention -->
                                            <div class="mb-2">
                                                    <label class="form-label">Kind Attention </label>
                                                        <div class="d-flex gap-2 position-relative">
                                                            <select class="js-example-basic-single"
                                                                    name="oa_kind_attn_id"
                                                                    id="oa_kind_attn_id">
                                                                <option value="">Select Kind Attention</option>
                                                            </select>
                                                        </div>
                                            </div>

                                            

                                            <div class="row g-1 mt-2">
                                                <div class="col-6">
                                                    <label class="form-label">PO No. <sup class="astric">*</sup></label>

                                                        <input type="text"
                                                            class="form-control"
                                                            id="oa_po_number"
                                                            name="oa_po_number"
                                                            required>
                                                        <div class="invalid-tooltip">
                                                            Please Enter PO No.
                                                        </div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">PO Date <sup class="astric">*</sup></label>
                                                    <input type="text"
                                                        class="form-control date-picker"
                                                        id="oa_po_date"
                                                        name="oa_po_date"
                                                        required
                                                        autocomplete="off">
                                                    <div class="invalid-tooltip">
                                                        Please Enter PO Date
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
                    
                    <!-- Tab Panel -->
                    
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary add_detail" data-bs-target="#OADetailsModal" disabled>Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                        <b>Order Acceptance Details</b>
                                    </h4>
                                </div>
                                <div class="card-body">

                                        <!-- STEP NAV -->
                                        <div class="step-arrow-nav mb-4">
                                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">

                                                <li class="nav-item">
                                                    <button class="nav-link active"
                                                        id="steparrow-gen-info-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#steparrow-gen-info"
                                                        type="button" role="tab">
                                                        Details
                                                    </button>
                                                </li>

                                                <li class="nav-item">
                                                    <button class="nav-link"
                                                        id="steparrow-description-info-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#steparrow-description-info"
                                                        type="button" role="tab">
                                                        Terms & Condition
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- TAB CONTENT -->
                                        <div class="tab-content">

                                            <!-- ================= DETAILS TAB ================= -->
                                            <div class="tab-pane fade show active"
                                                id="steparrow-gen-info"
                                                role="tabpanel">

                                                <div class="card">
                                                    
                                                    <div class="card-body">
                                                        <div class="table-responsive details-action">
                                                            <table class="table table-bordered align-middle mb-0" id="OADetailTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Actions</th>
                                                                        <th>Quot. No.</th>
                                                                        <th>Date</th>
                                                                        <th>Type of Test</th>
                                                                        <th>Type Of Job</th>
                                                                        <th>Job Description</th>
                                                                        <th>Part No.</th>
                                                                        <th>Process At.</th>
                                                                        <th>Qty.</th>
                                                                        <th>Unit</th>
                                                                        <th>Rate/Unit</th>
                                                                        <th>Rate Per</th>
                                                                        <th>Minimum Charge</th>
                                                                        <th>Minimum Charge Per</th>
                                                                        <th>Conveyance Charge</th>
                                                                        <th>Remark</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="16" class="text-center" id="noDetails">
                                                                            No Order Acceptance Details Added
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <!-- =============== END DETAILS TAB =============== -->


                                            <!-- ================= TERMS TAB ================= -->
                                            <div class="tab-pane fade"
                                                id="steparrow-description-info"
                                                role="tabpanel">

                                                <div class="row">
                                                    <div class="col-xxl-6 col-md-8">

                                                        <div class="mb-3">
                                                            <label class="form-label">
                                                                Copy From 
                                                            </label>
                                                            <select class="form-control js-example-basic-single" name="oa_copy_from_id" id="oa_copy_from_id">
                                                                <option value="">Select</option>
                                                            </select>
                                                        </div>

                                                        <label class="form-label">Terms & Conditions</label>

                                                      

                                                        <div class="mb-3">
                                                                <textarea class="form-control" name="oa_terms_and_conditions" id="oa_terms_and_conditions"></textarea>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <!-- =============== END TERMS TAB =============== -->

                                        </div>
                                        <!-- END TAB CONTENT -->

                                    <!-- </form> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Panel End -->
                    
                        <div class="row gy-4">
                            <!-- File Upload -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label col-form-label">File Upload</label>
                                    <div class="input-append fileupload">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-control" type="file" name="oa_file_upload" id="oa_file_upload"
                                                accept=".png,.jpg,.jpeg,.gif,.pdf">
                                            <input type="hidden" id="oa_file_upload_doc" name="oa_file_upload_doc" />
                                            <a href="#" data-remove="oa_file_upload" id="oa_file_upload_remove"
                                                class="btn fileupload-exists hide" onclick="removeFile(event)">Remove</a>
                                            <a target="_blank" class="btn img-prev hide" id="oa_file_upload_prev">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Note -->
                            <div class="col-xxl-4 col-md-4">
                                <div class="mb-3">
                                    <label for="oa_special_note" class="form-label">Special Note</label>
                                    <textarea class="form-control" name="oa_special_note" id="oa_special_note"
                                        placeholder="Enter Special Note"></textarea>
                                </div>
                            </div>
                        </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


@push('script-modal')
<script src="{{ URL::asset('views/js/order_acceptance.js?ver='.getJsVersion()) }}"></script>
@endpush
