
<div class="modal fade" id="PurchaseOrderDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="PurchaseOrderDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="pod_id" id="pod_id"  value="0"/> 
                    <input type="hidden" name="pod_pid_id" id="pod_pid_id" value="0"/> 

                    <div class="row mt-2">
                        <div class="col-md-6">
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_item_id" class="form-label">Purchase Indent No.</label>
                                </div>
                                
                                <div class="col-8 gap-2 d-flex">
                                    <input type="text" name="pi_no" id="pi_no" class="form-control skip-tab" readonly>
                                    <input type="text" name="pi_date" id="pi_date" class="form-control skip-tab" readonly>
                                </div>
                            </div> --}}

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_item_id" class="form-label">Item <sup class="astric">*</sup></label>
                                </div>
                                
                                <div class="col-lg-8">
                                <select class="js-example-basic-single" name="pod_item_id" id="pod_item_id" required>
                                            <option value="">Select Item</option>
                                            @forelse(getItemsForPI() as $item)
                                                <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}" 
                                                    data-main_group="{{  getItemType()[$item->item_type] ?? '' }}" data-io_stock_qty="{{ $item->io_stock_qty}}" data-unit="{{ $item->unit }}" >
                                                    {{ $item->item_name }}
                                                </option>
                                                @empty
                                            @endforelse
                                    </select>
                                    <div class="invalid-tooltip">
                                        Select Item.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_group" class="form-label">Item Group</label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="text" name="item_group" id="item_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="main_group" class="form-label">Main Group</label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="email" name="main_group" id="main_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="io_stock_qty" class="form-label">Stock</label>
                                </div>

                                <div class="col-lg-8 d-flex">
                                    <input type="text" name="io_stock_qty" id="io_stock_qty" class="form-control isNumberKey skip-tab"  onblur="formatPoints(this,3)" readonly>
                                    <span class="mt-2 ml-1 text-nowrap" id="io_stock_qty_unit"></span>
                                </div>
                            </div>

                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pend_pi_qty" class="form-label">Pend. PI Qty</label>
                                </div>

                                <div class="col-lg-8 d-flex">
                                    <input type="text" name="pend_pi_qty" id="pend_pi_qty" class="form-control isNumberKey skip-tab"  onblur="formatPoints(this,3)" readonly>
                                    <span class="mt-2 ml-1 text-nowrap" id="pend_pi_qty_unit"></span>
                                </div>
                            </div> --}}

                            <div class="row g-2 mb-1 ">
                                <div class="col-4">
                                    <label for="pod_po_qty" class="form-label">PO Qty. <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-lg-8 d-flex">
                                    <input type="text" name="pod_po_qty" id="pod_po_qty" class="form-control isNumberKey auto-select"  onblur="formatPoints(this,3)" required>
                                    <div class="invalid-tooltip">
                                        Enter PO Qty.
                                    </div>
                                    <span class="mt-2 ml-1 text-nowrap" id="pod_po_qty_unit"></span>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_rate_unit" class="form-label">Rate / Unit <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="text" name="pod_rate_unit" id="pod_rate_unit" class="form-control isNumberKey"  onblur="formatPoints(this,2)" required>
                                    <div class="invalid-tooltip">
                                        Enter Rate / Unit.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_amount" class="form-label">Amount </label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="text" name="pod_amount" id="pod_amount" class="form-control skip-tab isNumberKey"  onblur="formatPoints(this,3)" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_del_date" class="form-label">Del. Date <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="text" name="pod_del_date" id="pod_del_date" class="form-control date-picker " required>
                                    <div class="invalid-tooltip">
                                        Select Del. Date.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pod_remark" class="form-label">Remark</label>
                                </div>

                                <div class="col-lg-8">
                                    <input type="text" name="pod_remark" id="pod_remark" class="form-control">
                                </div>
                            </div>
                           
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <!-- <label class="form-check-label" for="tested_by">
                                        Tested By
                                    </label> -->
                                </div>
                                <div class="col-lg-8 d-flex gap-4 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="repeat_item" name="repeat_item">
                                        <label class="form-check-label" for="repeat_item">
                                        Repeat Item
                                    </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="podGRNDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>GRN No.</th>
                                            <th>GRN Date</th>
                                            <th>Supplier</th>
                                            <th>GRN Qty.</th>
                                            <th>Rate/Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center" id="noDetails">
                                                No Data Added
                                            </td>
                                        </tr>
                                        
                                    </tbody>
                                </table>
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



