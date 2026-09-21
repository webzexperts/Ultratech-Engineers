<div class="modal fade" id="PurchaseIndentDetailsModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ContactModalLabel">Material Indent Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="PurchaseIndentDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />
                    <input type="hidden" name="pid_id" id="pid_id"  value="0"/> 

                    <div class="row mt-2">
                        <div class="col-md-8">
                        <div class="row g-2 mb-1">
                            <div class="col-4">
                                <label for="item_id" class="form-label">Item <sup class="astric">*</sup></label>
                            </div>
                            
                            <div class="col-lg-8">
                               <select class="js-example-basic-single" name="item_id" id="item_id" required>
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

                        <div class="row g-2 mb-1">
                            <div class="col-4">
                                <label for="indent_qty" class="form-label">Indent Qty. <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-8 d-flex">
                                <input type="text" name="indent_qty" id="indent_qty" class="form-control isNumberKey auto-select" required>
                                <span class="mt-2 ml-1 text-nowrap" id="indent_qty_unit" name="unit"></span>
                                <div class="invalid-tooltip">
                                    Enter Indent Qty.
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-1">
                            <div class="col-4">
                                <label for="remark" class="form-label">Remark</label>
                            </div>

                            <div class="col-lg-8">
                                <input type="text" name="remark" id="remark" class="form-control" >
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