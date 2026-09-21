<div class="modal fade" id="MaterialMptModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="MaterialMptModalLabel">Material - MPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonMaterialMptForm" class="row g-3 needs-validation" novalidate>
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
                                            data-bs-target="#GrnPendingForMaterialMptModal" 
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
                                            <input type="text" class="form-control skip-tab" id="grn_no" name="grn_no" tabindex="-1" readonly>
                                            <input type="text" class="form-control skip-tab" id="grn_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
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
                                            <input type="text" class="form-control skip-tab" id="challan_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
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
                                        <label class="form-label">Material Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="mm_material" name="mm_material" required>
                                        <div class="invalid-tooltip">
                                            Enter Material Name.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Make <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="mm_material_make" name="mm_material_make"  required>
                                        {{-- <div id="mm_material_make_list" class="suggestion_list"></div>
                                        <input type="hidden" id="mm_material_make_suggesion"> --}}
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
                                        <input type="text" class="form-control" id="mm_batch_no" name="mm_batch_no"  required>
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
                                        <input type="text" class="form-control" id="mm_identification_no" name="mm_identification_no">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Expiry Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input  class="form-control date-picker" id="mm_expiry_date" name="mm_expiry_date" required>
                                        <div class="invalid-tooltip">
                                            Enter Expiry Date.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="mm_qty" class="form-label">Qty. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isInteger auto-select" id="mm_qty" name="mm_qty" required>
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
                                        <select class="js-example-basic-single form-select skip-tab" name="mm_status" id="mm_status">
                                            <option value="Active">Active</option>
                                            <option value="Consumed">Consumed</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Deactive">Deactive</option>
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
                        @if(hasAccess("material_mpt","add"))
                            <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                        @endif
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/material_mpt.js?ver='.getJsVersion()) }}"></script>
@endpush