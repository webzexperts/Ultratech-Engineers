
<div class="modal fade" id="ItemIssueDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Item Issue Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ItemIssueDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="sr_table_unique_id" id="sr_table_unique_id"/>
                    <input type="hidden" name="issue_detail_id" id="issue_detail_id" /> 
                    <input type="hidden" name="conv_factor" id="conv_factor" /> 
                    <input type="hidden" name="item_type" id="item_type" /> 
                    {{-- <input type="hidden" name="stock_rate_unit" id="stock_rate_unit"/>  --}}
                    {{-- <input type="hidden" name="amount" id="amount"/>  --}}

                    <div class="row mt-2">
                        <div class="col-md-8">                           

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_id" class="form-label">Item <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="item_id" id="item_id"  autofocus required>
                                        <option value="">Select Item Name</option>   
                                        @forelse (getItemsForIssue() as $item)
                                            <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}" 
                                                   data-main_group="{{  getItemType()[$item->item_type] ?? '' }}" data-item_type="{{ $item->item_type }}" data-io_stock_qty="{{ $item->io_stock_qty}}" data-unit="{{ $item->unit }}" data-stock_rate_unit="{{$item->io_stock_rate_unit}}" data-conv_factor="{{ $item->conv_factor }}">
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
                                    <span class="mt-2 ml-1 text-nowrap" id="issue_stock_qty_unit"></span>
                                    
                                </div>
                            </div>                            

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_pend_qty" class="form-label">Issue Qty.<sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey" name="issue_qty" id="issue_qty" onblur="formatPoints(this,3)" required>
                                    <div class="invalid-tooltip">
                                        Enter Issue Qty.
                                    </div>
                                    <span class="mt-2 ml-1 text-nowrap" id="issue_qty_unit"></span>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_pend_qty" class="form-label">Issue Type<sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <select class="js-example-basic-single" name="issue_type" id="issue_type">
                                        <option value="Consumption">Consumption</option>
                                        <option value="Wastage">Wastage</option>
                                    </select>
                                        <div class="invalid-tooltip">
                                            Select Issue Type.
                                        </div>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="grnd_pend_qty" class="form-label">Wastage Reason <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <select class="js-example-basic-single" name="wastage_reason_id" id="wastage_reason_id">
                                    <option value="">Select Reason</option>
                                    @forelse(getWastageReason() as $reason)
                                        <option value="{{ $reason->id }}">{{ $reason->reason_name }}</option>
                                        @empty
                                    @endforelse
                                </select>
                                <div class="invalid-tooltip">
                                    Select Wastage Reason
                                </div>
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



