
<div class="modal fade" id="SupplierDCDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Supplier DC Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="SupplierDCDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="sup_dcd_id" id="sup_dcd_id"  value="0"/> 
                    <input type="hidden" name="ser_pod_id" id="ser_pod_id"/> 
                    <input type="hidden" name="sr_table_unique_id" id="sr_table_unique_id"/>  

                    <div class="row mt-2">
                        <div class="col-md-8">
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ser_po_number" class="form-label">Service PO No.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center gap-2">
                                    <input type="text" class="form-control skip-tab" name="ser_po_number" id="ser_po_number"  readonly>
                                    <input type="text" class="form-control skip-tab" style="width: 100px;" id="ser_po_date" name=
                                    "ser_po_date" tabindex="-1" readonly>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_id" class="form-label">Item <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="item_id" id="item_id"  autofocus required>
                                        <option value="">Select Item</option>
                                            {{-- @forelse(getItemsForPI() as $item)
                                                <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}" 
                                                    data-main_group="{{  getItemType()[$item->item_type] ?? '' }}" data-io_stock_qty="{{ $item->io_stock_qty}}" data-unit="{{ $item->unit }}" >
                                                    {{ $item->item_name }}
                                                </option>
                                                @empty
                                            @endforelse      --}}
                                    </select>
                                    <div class="invalid-tooltip">
                                        Select Item
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_group" class="form-label">Item Group</label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="item_group" id="item_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="main_group" class="form-label">Main Group</label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="main_group" id="main_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Sr. No.<sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="sr_table_pk_id" id="sr_table_pk_id">
                                        <option value="">Select Sr. No.</option>
                                    </select>
                                    <div class="invalid-tooltip">
                                        Select Sr. No.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_pend_qty" class="form-label">Stock</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey" name="io_stock_qty" id="io_stock_qty" onblur="formatPoints(this,3)"  tabindex="-1" readonly >
                                    <span class="mt-2 ml-1 text-nowrap" id="io_stock_qty_unit"></span>
                                    
                                </div>
                            </div>

                             <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_pend_qty" class="form-label">Pend. PO / DC Qty.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey" name="pending_qty" id="pending_qty" onblur="formatPoints(this,3)"  tabindex="-1" readonly >
                                    <span class="mt-2 ml-1 text-nowrap" id="pending_qty_unit"></span>
                                </div>
                            </div>
                           
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="return_qty" class="form-label">Return Qty. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey auto-select" name="return_qty" id="return_qty" onblur="formatPoints(this,3)"  required>     
                                    <span class="mt-2 ml-1 text-nowrap" id="return_qty_unit"></span>                               
                                    <div class="invalid-tooltip">
                                            Enter Return Qty.
                                    </div>
                                </div>
                            </div> 

                            <div class="row g-2 mb-1" id="common_div">
                                <div class="col-4">
                                    <label for="aerb_no" class="form-label">AERB No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="aerb_no" id="aerb_no" class="form-control skip-tab" required>
                                    <div class="invalid-tooltip">
                                            Enter AERB No.
                                    </div>
                                </div>
                            </div> 

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="application_no" class="form-label ">Application No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="application_no" id="application_no" class="form-control skip-tab" required>
                                    <div class="invalid-tooltip">
                                            Enter Application No.
                                    </div>
                                </div>
                            </div> 

                            <div class="row g-1 mb-1 position-relative">
                                <div class="col-4">
                                    <label class="form-label">Movement Approval <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="input-append fileupload position-relative">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-control skip-tab" type="file" name="movement_approval" id="movement_approval" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                            <input type="hidden" id="movement_approval_doc" name="movement_approval_doc" />
                                            <a href="#" data-remove="movement_approval" id="movement_approval_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileMovement(event)">Remove</a>
                                            <a target="_blank" class="btn img-prev hide" id="movement_approval_prev">View</a>
                                            <div class="invalid-tooltip">
                                                    Select Movement Approval.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-1 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Validity </label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab date-picker" id="validity" name="validity"  autocomplete="off"/>
                                   
                                </div>
                            </div>
        
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="remark" class="form-label">Remark</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="remark" id="remark" class="form-control">
                                </div>
                            </div> 
                            
                            <div class="row g-2 mb-1" id="for_calibration_row">
                                <div class="col-4">
                                </div>
                                <div class="col-lg-8 d-flex gap-4 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="for_calibration" name="for_calibration" value="Yes">
                                        <label class="form-check-label" for="for_calibration">
                                        For Calibration
                                    </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                    
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
            </div>
          </form>

        </div>
    </div>
</div>



