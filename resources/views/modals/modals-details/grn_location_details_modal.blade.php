<div class="modal fade" id="GrnLocationDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true"
    data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Goods Received Note Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="GrnLocationDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add" />
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />
                    <input type="hidden" name="grn_locd_id" id="grn_locd_id" value="0" />
                    <input type="hidden" name="sr_table_unique_id" id="sr_table_unique_id" />
                    <input type="hidden" name="inter_location_transfer_details_id" id="inter_location_transfer_details_id" />

                    <div class="row mt-2">
                        <div class="col-md-8">
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_qty" class="form-label">DC No.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center gap-2">
                                    <input type="text" class="form-control skip-tab" name="dc_number" id="dc_number"
                                        readonly>
                                    <input type="text" class="form-control" style="width: 100px;" id="dc_date"
                                        name="dc_date" tabindex="-1" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="item_id" class="form-label">Item <sup
                                            class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single skip-tab" name="item_id" id="item_id"
                                        autofocus required>
                                        <option value="">Select Item </option>
                                        @forelse (getItems() as $item)

                                        <option value="{{ $item->id }}" data-item_group="{{ $item->item_group }}"
                                            data-main_group="{{  getItemType()[$item->item_type] ?? '' }}"
                                            data-unit="{{ $item->unit }}">
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
                                    <input type="text" name="item_group" id="item_group" class="form-control skip-tab"
                                        readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="main_group" class="form-label">Main Group</label>
                                </div>

                                <div class="col-8">
                                    <input type="text" name="main_group" id="main_group" class="form-control skip-tab"
                                        readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Sr. No.<sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single skip-tab" name="sr_table_pk_id" id="sr_table_pk_id">
                                        <option value="">Select Sr. No.</option>
                                    </select>
                                    <div class="invalid-tooltip">
                                        Select Sr. No.
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="qty" class="form-label">GRN Qty. <sup
                                            class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey skip-tab" name="qty"
                                        id="qty" onblur="formatPoints(this,3)"
                                        required readonly>
                                    <span class="mt-2 ml-1 text-nowrap" id="qty_unit"></span>
                                    <div class="invalid-tooltip">
                                        Enter GRN Qty.
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
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none"
                    data-bs-dismiss="modal"> Close</a>
            </div>
            </form>

        </div>
    </div>
</div>