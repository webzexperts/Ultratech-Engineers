 <div class="modal fade" id="QuotationModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="QuotationModalLabel">Quotation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonQuotationForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                     <div class="row">
                        <!-- <div class="row g-1"> -->

                            <!-- COLUMN 1 -->
                            <div class="col-lg-6">

                                <div class="row g-1 mb-1">
                                    <div class="col-lg-3">
                                        <label class="form-label">Quotation No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control"
                                                id="quot_sequence"
                                                name="quot_sequence"
                                                style="max-width:80px"
                                                autofocus
                                                required>

                                            <input type="text"
                                                class="form-control"
                                                id="quot_number"
                                                name="quot_number"
                                                tabindex="-1"
                                                readonly>
                                            
                                            <div class="invalid-tooltip">
                                            Enter Quotation No.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-lg-3">
                                        <label class="form-label">Quotation Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text"
                                            class="form-control trans-date-picker"
                                            id="quot_date"
                                            name="quot_date"
                                            required
                                            autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Quotation Date
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer -->
                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-lg-3">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="d-flex gap-2">
                                            <select class="js-example-basic-single"
                                                    name="quot_customer_id"
                                                    id="quot_customer_id"
                                                    required>
                                                <option value="">Select Customer</option>
                                                <!-- @foreach(getCustomers() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                @endforeach -->
                                            </select>
                                            <div class="invalid-tooltip">
                                                 Select Customer
                                            </div>

                                            <button type="button" class="btn btn-success btn-sm toggleModalBtn" disabled data-bs-target="#QuotationPendingModal" id="pending_btn">
                                                Pending
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-6">
                                <!-- Kind Attention -->
                                <div class="row g-1 mb-1">
                                    <div class="col-lg-3">
                                        <label class="form-label">Kind Attention </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single"
                                                    name="quot_kind_attn_id"
                                                    id="quot_kind_attn_id">
                                                <option value="">Select Kind Attn.</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- SAC -->
                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-lg-3">
                                        <label class="form-label">SAC </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="js-example-basic-single"
                                                name="quot_sac_id"
                                                id="quot_sac_id">
                                            <option value="">Select SAC</option>
                                            @foreach(getSAC() as $sac)
                                                <option value="{{ $sac->gc_id }}">{{ $sac->gc_sac }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-tooltip">
                                             Select SAC
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Ref No & Date -->
                                <div class="row g-1 mb-1">
                                    <div class="col-lg-3">
                                        <label class="form-label">Ref. No. & Date </label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" id="quot_ref_no_date" name="quot_ref_no_date"
                                        autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        <!-- </div> -->
                    </div>    
                    
                    <!-- Tab Panel -->
                    
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">

                                    <!-- <form action="#" class="form-steps" autocomplete="off"> -->

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

                                                <li class="nav-item">
                                                    <button class="nav-link"
                                                        id="pills-experience-tab"
                                                        data-bs-toggle="pill"
                                                        data-bs-target="#pills-experience"
                                                        type="button" role="tab">
                                                        Revision Document
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
                                                    <div class="card-header d-flex align-items-center">
                                                        <h4 class="card-title mb-0 flex-grow-1">
                                                            <b>Quotation Details</b>
                                                        </h4>
                                                        <!-- <div class="flex-shrink-0">
                                                            <button type="button" class="btn btn-primary" data-bs-target="#QuotationDetailsModal">Add</button>
                                                        </div> -->
                                                    </div>
                                                    

                                                    <div class="card-body">
                                                        <div class="table-responsive details-action">
                                                            <table class="table table-bordered align-middle mb-0" id="QuotationDetailTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Actions</th>
                                                                        <th>Inq. No.</th>
                                                                        <th>Inq. Date</th>
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
                                                                        <td colspan="15" class="text-center" id="noDetails">
                                                                            No Quotation Details Added
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

                                                        <!-- Copy From -->
                                                        <div class="row g-2 mb-1">
                                                            <div class="col-4">
                                                                <label class="form-label">Copy From</label>
                                                            </div>
                                                            <div class="col-8">
                                                                <select class="form-control js-example-basic-single" name="quot_copy_from_id" id="quot_copy_from_id">
                                                                    <option value="">Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Terms & Conditions -->
                                                        <div class="row g-2 mb-1">
                                                            <div class="col-4 justify-content-start">
                                                                <label class="form-label">Terms & Conditions</label>
                                                            </div>
                                                            <div class="col-8">
                                                                <textarea class="form-control" name="quot_terms_and_conditions" id="quot_terms_and_conditions" rows="5"></textarea>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <!-- =============== END TERMS TAB =============== -->


                                            <!-- ================= REVISION TAB ================= -->
                                            <div class="tab-pane fade"
                                                id="pills-experience"
                                                role="tabpanel">

                                                 <div class="row">
                                                     <div class="col-xxl-6 col-md-8">
                                                         <div class="row g-2 mb-1">
                                                             <div class="col-4">
                                                                 <label class="form-label">Revision Document</label>
                                                             </div>
                                                             <div class="col-8">
                                                                 <div class="input-append fileupload">
                                                                      <div class="d-flex align-items-center gap-2">
                                                                            <input class="form-control" type="file" name="revision_doc" id="revision_doc" accept=".png,.jpg,.jpeg,.gif,.pdf">

                                                                            <input type="hidden"
                                                                            id="revision_doc_doc"
                                                                            name="revision_doc_doc" />

                                                                            <a href="#"
                                                                            data-remove="revision_doc"
                                                                            class="btn fileupload-exists hide"
                                                                            data-dismiss="fileupload"
                                                                            onclick="removeFile(event)">
                                                                                Remove
                                                                            </a>

                                                                            <a target="_blank"
                                                                            class="btn img-prev hide"
                                                                            id="revision_doc_prev">
                                                                                View
                                                                            </a>
                                                                      </div>    
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>


                                            </div>
                                            <!-- =============== END REVISION TAB =============== -->

                                        </div>
                                        <!-- END TAB CONTENT -->

                                    <!-- </form> -->

                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Panel End -->
                    
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-lg-6">
                                <!-- Offer Validity -->
                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-lg-3">
                                        <label for="quot_offer_validity" class="form-label">Offer Validity <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="d-flex gap-2">
                                            <input type="text" class="form-control only-int" id="quot_offer_validity" name="quot_offer_validity" required autocomplete="off" />
                                            <span class="form-label offer_text mt-2">Days</span>
                                            <div class="invalid-tooltip">
                                                 Enter Offer Validity
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Prepared By -->
                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-lg-3">
                                        <label for="quot_prepared_by_id" class="form-label">Prepared By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="js-example-basic-single skip-tab" name="quot_prepared_by_id" id="quot_prepared_by_id" required>
                                            <option value="">Select Prepared By</option>
                                            @forelse(getUsers() as $user)
                                                <option value="{{ $user->id }}"  {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                             Select Prepared By
                                        </div>
                                    </div>
                                </div>

                                <!-- Authorised By -->
                                <div class="row g-1 mb-1 position-relative">
                                    <div class="col-lg-3">
                                        <label for="quot_authorised_by_id" class="form-label">Authorised By</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="js-example-basic-single form-select" name="quot_authorised_by_id" id="quot_authorised_by_id">
                                            <option value="">Select Authorised By</option>
                                            @forelse (getUsers() as $user)
                                                <option value="{{ $user->id }}">{{ $user->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                             Select Authorised By
                                        </div>
                                    </div>
                                </div>

                                <!-- Special Note -->
                                <div class="row g-1 mb-1">
                                    <div class="col-lg-3 justify-content-start">
                                        <label for="quot_special_note" class="form-label">Special Note</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <textarea class="form-control" name="quot_special_note" id="quot_special_note"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("quotation","print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("quotation","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


@push('script-modal')
 <script>
        let loginUserId = {{ auth()->id() }};
        let checkFileRoute = "{{ route('check-file_exists') }}";
    </script>
<script src="{{ URL::asset('views/js/quotation.js?ver='.getJsVersion()) }}"></script>
@endpush
