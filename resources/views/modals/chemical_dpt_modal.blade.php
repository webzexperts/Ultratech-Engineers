<div class="modal fade" id="ChemicalDptModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ChemicalDptModalLabel">Chemical - DPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonChemicalDptForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="table_pk_id" id="table_pk_id">
                    <input type="hidden" name="item_id" id="item_id">
                    <input type="hidden" name="pending_qty" id="pending_qty">
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button" 
                                            class="btn btn-success btn-sm toggleModalBtn" 
                                            data-bs-target="#GrnPendingForChemicalDPTModal" 
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
                                            <input type="text" class="form-control skip-tab" id="grn_no" tabindex="-1" name="grn_no" readonly>
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
                                
                            </div>

                            <div class="col-md-4">
                                
                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Item Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="item_group" name="item_group" readonly>  
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

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Chemical <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="dpt_chemical" name="dpt_chemical" required>
                                        <div class="invalid-tooltip">
                                            Enter Chemical.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label class="form-label">Designation <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="dpt_designation" name="dpt_designation">
                                        <div class="invalid-tooltip">
                                            Enter Designation.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Make <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="dpt_make" name="dpt_make" required>
                                        <div class="invalid-tooltip">
                                            Enter Make.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Batch No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="dpt_batch_no" name="dpt_batch_no"  required>
                                        <div class="invalid-tooltip">
                                            Enter Batch No.
                                        </div>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="col-md-4">
                                


                                 <div class="row g-2 ml-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label class="form-label">Identification No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="dpt_identification_no" name="dpt_identification_no">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="dpt_expiry_date" class="form-label">Expiry Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control date-picker" id="dpt_expiry_date" name="dpt_expiry_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Expiry Date.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="dpt_qty" class="form-label">Qty. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isInteger auto-select" id="dpt_qty" name="dpt_qty" required>
                                        <div class="invalid-tooltip">
                                            Enter Qty.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single form-select skip-tab" name="dpt_status" id="dpt_status">
                                            <option value="Active">Active</option>
                                            <option value="Consumed">Consumed</option>
                                            <option value="Deactive">Deactive</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Service PO">Service PO</option>
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
                        @if(hasAccess("chemical_dpt","add"))
                            <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                        @endif
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/chemical_dpt.js?ver='.getJsVersion()) }}"></script>
@endpush