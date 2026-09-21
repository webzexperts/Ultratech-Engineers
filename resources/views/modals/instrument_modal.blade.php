<div class="modal fade" id="InstrumentModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="InstrumentModalLabel">Instrument</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonInstrumentForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="table_pk_id" id="table_pk_id">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button" 
                                            class="btn btn-success btn-sm toggleModalBtn" 
                                            data-bs-target="#GrnPendingForInstrumentModal" 
                                            id="pending_btn" disabled>
                                            Pending
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Type</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="table_unique_id" name="table_unique_id" readonly>  
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">GRN No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="grn_no" tabindex="-1" readonly>
                                            <input type="text" class="form-control skip-tab" id="grn_date" tabindex="-1" readonly autocomplete="off" style="width:100px;"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="supplier" name="supplier" readonly>  
                                    </div>
                                </div>
                                <div class="row g-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Challan No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                         <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="challan_no" name="challan_no" readonly>  
                                            <input type="text" class="form-control skip-tab" id="challan_date" tabindex="-1" readonly autocomplete="off" style="width:100px;"/>
                                         </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Item Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="item_group" name="item_group" readonly>  
                                    </div>
                                </div>
                                
                            </div>

                            <div class="col-md-4">
                                
                                
                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Main Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="main_group" name="main_group" readonly>  
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Instrument No.  <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ins_instrument_no" name="ins_instrument_no" required>
                                        <div class="invalid-tooltip">
                                            Enter Instrument No.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Instrument Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ins_instrument_name" name="ins_instrument_name" required>
                                        <div class="invalid-tooltip">
                                            Enter Instrument Name.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Make <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ins_make" name="ins_make" required>
                                        
                                        <div class="invalid-tooltip">
                                            Enter Make.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Mfg. Sr. No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ins_mfg_sr_no" name="ins_mfg_sr_no"  required>
                                        <div class="invalid-tooltip">
                                            Enter Mfg. Sr. No.
                                        </div>
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_doc_ref_no" class="form-label">AERB Ref. No.</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="ins_doc_ref_no" id="ins_doc_ref_no" class="form-control">
                                        <div class="invalid-tooltip" >
                                            Enter AERB Ref. No.
                                        </div>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="col-md-4">
                          

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_status" class="form-label">Calibration Req. <sup class="astric">*</sup> </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="ins_cali_req" id="ins_cali_req" required>
                                                <option value="">Select Calibration Req.</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip" >
                                            Enter Calibration Req.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_cali_freq" class="form-label">Calibration Freq. <sup class="astric hide d-none">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex">
                                        <input type="text" name="ins_cali_freq" id="ins_cali_freq" class="form-control isNumberKeyNotZero">
                                        <span class="mt-2 ml-1 ms-1">Days</span>
                                        <div class="invalid-tooltip" >
                                            Enter Calibration Freq.
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_last_cali_date" class="form-label">Last Calibration Date <sup class="astric hide d-none">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="ins_last_cali_date" id="ins_last_cali_date" class="form-control date-picker">
                                        <div class="invalid-tooltip" >
                                            Enter Last Calibration Date.
                                        </div>
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_next_cali_due_date" class="form-label">Next Calibration Due Date <sup class="astric hide d-none">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="ins_next_cali_due_date" id="ins_next_cali_due_date" class="form-control date-picker">
                                        <div class="invalid-tooltip" >
                                            Enter Next Calibration Due Date.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Calibration Certificate </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="calibration_certificate" id="calibration_certificate" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="calibration_certificate_doc" name="calibration_certificate_doc"  />
                                                <a href="#" data-remove="calibration_certificate" id="calibration_certificate_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFile(event)" >Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="calibration_certificate_prev">View</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_status" class="form-label">Status </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="ins_status" id="ins_status">
                                            <option value="Active" selected>Active</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Service PO">Service PO</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                        <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                        @if(hasAccess("instrument","add"))
                            <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                        @endif
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/instrument.js?ver='.getJsVersion()) }}"></script>
@endpush