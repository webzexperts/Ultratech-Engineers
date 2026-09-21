<div class="modal fade" id="ItemReturnSlipDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Item Return Slip Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="ItemReturnSlipDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="irsd_id" id="irsd_id" value="0"/>
                    <!-- <input type="hidden" name="irs_iisd_id" id="irs_iisd_id"> -->
                    <input type="hidden" name="irsd_iisd_id" id="irsd_iisd_id"> <!-- add new line -->
                    <input type="hidden" name="irsd_sr_no_or_batch_no" id="irsd_sr_no_or_batch_no"/>

                    <div class="row mb-3 p-1 m-1">
                        <div class="col-lg-6">
                            <div class="row mt-3">
                                <div class="col-12 position-relative">
                                    <label class="form-label col-form-label">Item Name <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="irsd_item_id" id="irsd_item_id" required>
                                            <option value="">Select Item Name</option>
                                            @forelse(getIssueSlipItems() as $item)
                                                <option value="{{ $item->id }}" data-item_type="{{ $item->item_type }}" data-unit_name="{{ $item->unit}}">{{ $item->item_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Item Name
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Item Type </label>
                                    <select class="js-example-basic-single skip-tab" name="item_type_id" id="item_type_id" tabindex="-1">
                                        <option value="">Select Item Type</option>
                                        @forelse(getItemType() as $key=>$val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                            @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Sr. No. </label>
                                    <select class="js-example-basic-single" name="irsd_sr_no_id" id="irsd_sr_no_id">
                                        <option value="">Select Sr. No.</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Batch No. </label>
                                    <select class="js-example-basic-single" name="irsd_batch_no_id" id="irsd_batch_no_id">
                                        <option value="">Select Batch No.</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Issue Qty. </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control skip-tab" name="irsd_issue_qty" id="irsd_issue_qty" readonly>
                                        <input type="text" class="form-control skip-tab" name="issue_unit" id="issue_unit" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Pending Qty. </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="irsd_pending_qty" id="irsd_pending_qty" onblur="formatPoints(this,3)" tabindex="-1" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                             <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Return Qty. <sup class="astric">*</sup></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="irsd_return_qty" id="irsd_return_qty" onblur="formatPoints(this,3)" required>
                                        <input type="hidden" id="irsd_return_qty_unit">
                                        <div class="invalid-tooltip">
                                            Enter Return Qty.
                                        </div>
                                    </div>
                                </div>
                            </div>  
                           
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Non-Returnable Qty. </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="irsd_non_rerurnable_qty" id="irsd_non_rerurnable_qty" onblur="formatPoints(this,3)">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Reason </label>
                                    <div class="d-flex gap-2">
                                        <select class="js-example-basic-single" name="irsd_reason_id" id="irsd_reason_id">
                                            <option value="">Select Reason</option>
                                            @forelse(getReturnSlipReason() as $reason)
                                                <option value="{{ $reason->id }}">{{ $reason->reason_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label col-form-label">Remark </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" name="irsd_remark" id="irsd_remark" class="form-control">
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