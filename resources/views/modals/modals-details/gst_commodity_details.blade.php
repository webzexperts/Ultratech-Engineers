<div class="modal fade" id="GSTCommodityDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GSTCommodityDetailsModalLabel">GST Commodity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="gst_details_form" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="gcd_details_id" id="gcd_details_id"/>
                    <input type="hidden" name="gc_id" id="gc_id"/>
                    <input type="hidden" name="gst_name" id="gst_name"/>

                    <div class="row mt-2">  
                        <div class="col-md-8">  
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="tax_type" class="form-label">Tax Type <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="tax_type" id="tax_type" required>
                                        <option value="">Select Tax Type </option>
                                        <option value="1">SGST</option>
                                        <option value="2">CGST</option>
                                        <option value="3">IGST</option>
                                    </select>
                                    <div class="invalid-tooltip">
                                        Please Select Tax Type
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="tax" class="form-label">Tax(%) <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="tax" id="tax" class="form-control isNumberKey" required onblur="formatPoints(this,3)">
                                    <div class="invalid-tooltip">
                                        Please Enter Tax
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="effective_date" class="form-label">Effective Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="effective_date" id="effective_date" class="form-control date-picker">
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="remark" class="form-label">Remark </label>
                                </div>
                                <div class="col-8">
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