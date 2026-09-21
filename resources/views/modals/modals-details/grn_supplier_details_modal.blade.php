
<div class="modal fade" id="GrnDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Goods Received Note Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="GrnDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="grnd_id" id="grnd_id"  value="0"/> 
                    <input type="hidden" name="table_unique_id" id="table_unique_id"/> 
                    <input type="hidden" name="table_pk_id" id="table_pk_id"  value="0"/> 
                    <input type="hidden" name="sr_table_unique_id" id="sr_table_unique_id"/> 
                    <input type="hidden" name="sr_table_pk_id" id="sr_table_pk_id"  value="0"/> 

                    <div class="row mt-2">
                        <div class="col-md-8">
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_qty" class="form-label">PO No.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center gap-2">
                                    <input type="text" class="form-control  skip-tab" name="po_no" id="po_no"  readonly>
                                    <input type="text" class="form-control" style="width: 100px;" id="po_date" name=
                                    "po_date" tabindex="-1" readonly>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_qty" class="form-label">DC No.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center gap-2">
                                    <input type="text" class="form-control  skip-tab" name="sup_dc_number" id="sup_dc_number"   readonly>
                                    <input type="text" class="form-control" style="width: 100px;" id="sup_dc_date" name="sup_dc_date" tabindex="-1" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_item_id" class="form-label">Item <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="grnd_item_id" id="grnd_item_id"  autofocus required>
                                        <option value="">Select Item </option>   
                                        @forelse (getItems() as $item)                                            

                                            <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}" 
                                                   data-main_group="{{  getItemType()[$item->item_type] ?? '' }}"  data-unit="{{ $item->unit }}" >
                                                   {{ $item->item_name }}
                                            </option>
                                        @empty
                                        @endforelse     
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
                                     <input type="text" name="sr_no" id="sr_no" class="form-control skip-tab" readonly>
                                    <div class="invalid-tooltip">
                                        Select Sr. No.
                                    </div>
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
                                    <label for="grnd_qty" class="form-label">GRN Qty. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey auto-select" name="grnd_qty" id="grnd_qty" onblur="formatPoints(this,3)" onchange="CalculateAmount()" required>     
                                    <span class="mt-2 ml-1 text-nowrap" id="grnd_qty_unit"></span>                               
                                    <div class="invalid-tooltip">
                                            Enter GRN Qty.
                                    </div>
                                </div>
                            </div> 

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_rate_unit" class="form-label">Rate/Unit <sup class="astric">*</sup></label>
                                </div>
                                <div  class="col-8 d-flex align-items-center">
                                    <input type="text" name="grnd_rate_unit" id="grnd_rate_unit" class="form-control isNumberKey" onblur="formatPoints(this,2)" onchange="CalculateAmount()" required >
                                    <div class="invalid-tooltip">
                                            Enter Rate/Unit
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_amount" class="form-label">Amount</label>
                                </div>
                                <div  class="col-8 d-flex align-items-center">
                                    <input type="text" name="grnd_amount" id="grnd_amount" class="form-control isNumberKey skip-tab" onblur="formatPoints(this,2)" readonly tabindex="-1">
                                </div>
                            </div>  

                            <div class="row g-2 mb-1">
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

                            <div class="row g-2 mb-1">
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
                            <div class="row g-2 mb-1">
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

                            <div class="row g-2 mb-1">
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
        
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_remark" class="form-label">Remark</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="grnd_remark" id="grnd_remark" class="form-control">
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



