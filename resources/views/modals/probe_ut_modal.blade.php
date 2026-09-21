<div class="modal fade" id="ProbeUtModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <form id="commonProbeUtForm" class="modal-content needs-validation" novalidate>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="ProbeUtModalLabel">Probe - UT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
 
            <div class="modal-body">
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
                                            data-bs-target="#GrnPendingForProbeModal" 
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
                                            <input type="text" class="form-control skip-tab" id="grn_number" tabindex="-1" readonly>
                                            <input type="text" class="form-control skip-tab" id="grn_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="grn_supplier_name" name="grn_supplier_name" readonly>  
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Challan No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                         <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="grn_challan_number" name="grn_challan_number" readonly>  
                                            <input type="text" class="form-control skip-tab" id="grn_challan_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
                                         </div>
                                    </div>
                                </div>

                                
                                
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Item Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab " id="item_group" name="item_group" readonly>  
                                    </div>
                                </div>

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Main Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="main_group" name="main_group" readonly>  
                                    </div>
                                </div>
                               

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Probe <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pu_probe" name="pu_probe" required>
                                        <div class="invalid-tooltip">
                                            Enter Probe
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Probe Sr. No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pu_serial_number" name="pu_serial_number" required>
                                        <div class="invalid-tooltip">
                                            Enter Probe Sr. No.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Size Of Probe <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pu_size_of_probe" name="pu_size_of_probe" required>
                                        <div class="invalid-tooltip">
                                            Enter Size Of Probe
                                        </div>
                                    </div>
                                    <!-- <div class="col-8">
                                        <input type="text" class="form-control" id="pu_size_of_probe" name="pu_size_of_probe" onkeyup="suggestSizeOfProbe(event,this)" required>
                                        <div id="pu_size_of_probe_list" class="suggestion_list"></div>
                                        <input type="hidden" id="pu_size_of_probe_suggesion">
                                        <div class="invalid-tooltip">
                                            Enter Size Of Probe.
                                        </div>
                                    </div> -->
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Refraction Angle <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pu_ref_angle" name="pu_ref_angle" required>
                                        <div class="invalid-tooltip">
                                            Enter Refraction Angle
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Frequency (MHZ) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pu_frequency" name="pu_frequency" required>
                                        <div class="invalid-tooltip">
                                            Enter Frequency (MHZ)
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single form-select skip-tab" name="pu_status" id="pu_status">
                                            <option value="Active">Active</option>
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
                    @if(hasAccess("probe_ut","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
        </form>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/probe_ut.js?ver='.getJsVersion()) }}"></script>
@endpush