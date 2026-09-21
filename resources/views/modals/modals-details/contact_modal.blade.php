<div class="modal fade" id="ContactModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ContactModalLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="contact_form" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />

                    <div class="row mt-2">
                        <div class="col-md-8">
                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label for="contact_person" class="form-label">Contact Person <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-8">
                                <input type="text" name="contact_person" id="contact_person" class="form-control" required>
                                <div class="invalid-tooltip">
                                    Enter Contact Person.
                                </div>
                            </div>
                        </div>

                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label for="contact_designation" class="form-label">Designation </label>
                            </div>

                            <div class="col-8">
                                <input type="text" name="contact_designation" id="contact_designation" class="form-control" onkeyup="suggestDesignation(event,this)">
                                <div id="contact_designation_list" class="suggestion_list"></div>
                            </div>
                        </div>

                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label for="contact_phone_no" class="form-label">Phone No. </label>
                            </div>

                            <div class="col-8">
                                <input type="text" name="contact_phone_no" id="contact_phone_no" class="form-control isNumberKey common-phone-validate">
                            </div>
                        </div>

                        <!-- <div class="row mb-3">
                            <div class="col-8">
                                <label for="contact_mobile_no" class="form-label col-form-label">Mobile No. </label>
                            </div>

                            <div class="col-lg-6">
                                <input type="text" name="contact_mobile_no" id="contact_mobile_no" class="form-control isNumberKey common-mobile-validate">
                            </div>
                        </div> -->

                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label for="contact_email" class="form-label">Email ID </label>
                            </div>

                            <div class="col-8">
                                <input type="email" name="contact_email" id="contact_email" class="form-control">
                                <div class="invalid-tooltip">
                                    Enter a Valid Email ID.
                                </div>
                            </div>
                        </div>

                        <div class="row g-1 mb-1">
                            <div class="col-4">
                                <label for="send_sms_for" class="form-label">Send Email For </label>
                            </div>

                            <div class="col-8">
                                <input type="checkbox" name="send_email_for" id="send_email_for_report" value="report"> Report&nbsp;
                                <input type="checkbox" name="send_email_for" id="send_email_for_invoice" value="invoice"> Invoice
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