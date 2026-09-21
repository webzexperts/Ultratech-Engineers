<div class="modal fade" id="SupplierContactModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ContactModalLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="supplier_detail_form" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />

                    <div class="row mt-2">
                        <div class="row mb-1">
                            <div class="col-lg-3">
                                <label for="contact_person" class="form-label">Contact Person <sup class="astric">*</sup></label>
                            </div>
                            
                            <div class="col-lg-4">
                                <input type="text" name="contact_person" id="contact_person" class="form-control" required>
                                <div class="invalid-tooltip">
                                    Enter Contact Person.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-3">
                                <label for="phone_no" class="form-label">Phone No. </label>
                            </div>

                            <div class="col-lg-4">
                                <input type="text" name="phone_no" id="phone_no" class="form-control  mobile-f">
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-3">
                                <label for="email_id" class="form-label">Email ID </label>
                            </div>

                            <div class="col-lg-4">
                                <input type="email" name="email_id" id="email_id" class="form-control">
                                <div class="invalid-tooltip">
                                    Enter a Valid Email ID.
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