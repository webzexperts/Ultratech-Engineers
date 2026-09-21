
<div class="modal fade" id="ServicePODetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1000px; !important">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Service PO Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ServicePODetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="ser_pod_id" id="ser_pod_id"  value="0"/> 
                     <input type="hidden" name="sr_table_unique_id" id="sr_table_unique_id"/>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_id" class="form-label">Item <sup class="astric">*</sup></label>
                                </div>
                                
                                <div class="col-8">
                                <select class="js-example-basic-single" name="item_id" id="item_id" required>
                                            <option value="">Select Item</option>
                                            @forelse(getItemsForServicePO() as $item)
                                                <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}" data-unit="{{ $item->unit }}"
                                                    data-main_group="{{  getItemType()[$item->item_type] ?? '' }}" >{{ $item->item_name }}
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

                                <div class="col-8">
                                    <input type="text" name="item_group" id="item_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="main_group" class="form-label">Main Group</label>
                                </div>

                                <div class="col-8">
                                    <input type="email" name="main_group" id="main_group" class="form-control skip-tab" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="io_stock_qty" class="form-label">Sr. No. <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-8">
                                    <select class="js-example-basic-single" name="sr_table_pk_id" id="sr_table_pk_id" required>
                                    </select>
                                    <div class="invalid-tooltip">
                                        Select Sr. No.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1 ">
                                <div class="col-4">
                                    <label for="po_qty" class="form-label">PO Qty. <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-8 d-flex">
                                    <input type="text" name="po_qty" id="po_qty" class="form-control isNumberKey auto-select"  onblur="formatPoints(this,3)" required>
                                    <div class="invalid-tooltip">
                                        Enter PO Qty.
                                    </div>
                                    <span class="mt-2 ml-1 text-nowrap" id="po_qty_unit"></span>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="rate_unit" class="form-label">Rate / Unit <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="rate_unit" id="rate_unit" class="form-control isNumberKey"  onblur="formatPoints(this,2)" required>
                                    <div class="invalid-tooltip">
                                        Enter Rate / Unit.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amount" class="form-label">Amount </label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="amount" id="amount" class="form-control skip-tab isNumberKey"  onblur="formatPoints(this,3)" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="del_date" class="form-label">Del. Date </label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="del_date" id="del_date" class="form-control date-picker">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="remark" class="form-label">Remark</label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="remark" id="remark" class="form-control">
                                </div>
                            </div>

                            <div class="row g-2 mb-1" id="for_calibration_row">
                                <div class="col-4">
                                </div>
                                <div class="col-lg-8 d-flex gap-4 align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="for_calibration" name="for_calibration" value="Yes" disabled>
                                        <label class="form-check-label" for="for_calibration">
                                        For Calibration
                                    </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="SupplierDetailTable">
                                    <thead>
                                        <tr>
                                            <th>GRN No.</th>
                                            <th>GRN Date</th>
                                            <th>Supplier</th>
                                            <th>Purpose</th>
                                            <th>GRN Qty.</th>
                                            <th>Rate/Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center" id="noDetails">
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



