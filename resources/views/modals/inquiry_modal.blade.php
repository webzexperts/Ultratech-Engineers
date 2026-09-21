<div class="modal fade" id="InquiryModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="InquiryModalLabel">Inquiry</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonInquiryForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">                

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="supplier_name" class="form-label">
                                            Inquiry No. <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey"
                                                id="inq_sequence" name="inq_sequence"
                                                style="max-width:50px" autofocus required>

                                            <input type="text" class="form-control skip-tab"
                                                id="inq_number" name="inq_number"
                                                tabindex="-1" readonly>

                                            <div class="invalid-tooltip">
                                            Enter Inquiry No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inq_date" class="form-label">Inquiry Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="inq_date" name="inq_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">
                                            Enter Inquiry Date.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                       
                                            <select class="js-example-basic-single w-100 suggest_customer_name" name="inq_customer_id" id="inq_customer_id" required>
                                                <option value="">Select Customer</option>
                                                @forelse(getCustomers() as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Customer
                                            </div>

                                            @if(hasAccess("customer","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedCustomer(true)" data-bs-target="#CustomerModal"></i>
                                            @endif
                                        </div>
                                        
                                    </div>
                                </div>
                          
                                
                                <div class="row g-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Kind Attn. </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="inq_kind_attn_id" id="inq_kind_attn_id">
                                            <option value="">Select Kind Attn.</option>
                                        </select>
                                        
                                    </div>
                                </div>

                                 


                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Ref. No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="inq_ref_no_date" name="inq_ref_no_date">  
                                    </div>
                                </div>
                            </div>
                               
                        </div>
                    </div>         


                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header d-flex align-items-center">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary add_detail" data-bs-target="#InquiryDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                        <b>Inquiry Details</b>
                                    </h4>

                                </div>
                            
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="InquiryDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_detail_width">Actions</th>
                                                    <th>Type of Test</th>
                                                    <th>Type of Job</th>
                                                    <th>Job Description</th>
                                                    <th>Part No.</th>
                                                    <th>Process At.</th>
                                                    <th>Qty.</th>
                                                    <th>Unit</th>
                                                    <th>Feasibility Required</th>
                                                    <!-- <th>Estimation Required</th> -->
                                                    <th>Remark</th>
                                                    <th>Attachment</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="centeralign" id="noDetails">
                                                    <td colspan="12">No Inquiry Details Added</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4  justify-content-start">
                                        <label for="inq_sp_note" class="form-label">Special Note </label>
                                    </div>
                                    <div class="col-8">
                                       <textarea class="form-control" name="inq_sp_note" id="inq_sp_note"></textarea>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inq_prepared_by_id" class="form-label">Prepared By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                       <select class="js-example-basic-single skip-tab" name="inq_prepared_by_id" id="inq_prepared_by_id" readonly>
                                             <option value="">Select Prepared By </option>
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
                            </div>                           
                        </div>
                    </div>

                    
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("inquiry","print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("inquiry","add"))
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
    <script src="{{ URL::asset('views/js/inquiry.js?ver='.getJsVersion()) }}"></script>
@endpush