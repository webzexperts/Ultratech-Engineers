<div class="modal fade" id="ItemReturnSlipModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ItemReturnSlipModalLabel">Item Return Slip</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonItemReturnSlipForm" class="row g-1 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <!-- <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-1">
                                    <label class="form-label">Return Slip No. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="irs_sequence" name="irs_sequence" style="max-width:80px" autofocus required>
                                        <input type="text" class="form-control" id="irs_number" name="irs_number" tabindex="-1" readonly>
                                        <input type="text" class="form-control trans-date-picker" id="irs_date" name="irs_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Please Enter Return Slip No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mt-2">
                                    <label class="form-label">Item Return Slip Type <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="irs_type_id" id="irs_type_id" required>
                                            <option value="From Issue">From Issue</option>
                                            <option value="Manual">Manual</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Please Select Type
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mt-2">
                                    <label class="form-label">Employee <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="irs_employee_id" id="irs_employee_id" required>
                                            <option value="">Select Employee</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Please Select Employee
                                        </div>

                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#ItemReturnSlipPendingModal" id="pending_btn" disabled>Pending</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="row mt-2 g-3">

                        <!-- Return Slip No -->
                        <div class="col-md-4">
                            <label class="form-label">Return Slip No. <sup class="astric">*</sup></label>
                            <div class="d-flex gap-2 position-relative">
                                <input type="text" class="form-control" id="irs_sequence" name="irs_sequence" style="max-width:80px" autofocus required>
                                <input type="text" class="form-control" id="irs_number" name="irs_number" tabindex="-1" readonly>
                                <input type="text" class="form-control trans-date-picker" id="irs_date" name="irs_date" required autocomplete="off">
                                <div class="invalid-tooltip">
                                    Please Enter Return Slip No.
                                </div>
                            </div>
                        </div>

                        <!-- Item Return Slip Type -->
                        <div class="col-md-4">
                            <label class="form-label">Item Return Slip Type <sup class="astric">*</sup></label>
                            <div class="position-relative">
                                <select class="js-example-basic-single form-control" name="irs_type_id" id="irs_type_id" required>
                                    <option value="From Issue">From Issue</option>
                                    <option value="Manual">Manual</option>
                                </select>
                                <div class="invalid-tooltip">
                                    Please Select Type
                                </div>
                            </div>
                        </div>

                        <!-- Employee -->
                        <div class="col-md-4">
                            <label class="form-label">Employee </label>
                            <div class="d-flex gap-2 position-relative">
                                <select class="js-example-basic-single form-control" name="irs_employee_id" id="irs_employee_id">
                                    <option value="">Select Employee</option>
                                </select>
                                <button type="button"
                                        class="btn btn-success btn-sm toggleModalBtn"
                                        data-bs-target="#ItemReturnSlipPendingModal"
                                        id="pending_btn"
                                        disabled>
                                    Pending
                                </button>
                                <div class="invalid-tooltip">
                                    Please Select Employee
                                </div>
                            </div>
                        </div>

                    </div>
                    


                    <div class="card mt-3">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary add_detail" data-bs-target="#ItemReturnSlipDetailsModal">Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Item Return Slip Details</b>
                            </h4>

                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="ItemReturnSlipDetailTable">
                                    <thead>
                                        <tr>
                                            <th>Actions</th>
                                            <th>Item</th>
                                            <th>Item Type</th>
                                            <th>Sr No.</th>
                                            <th>Issue Qty.</th>
                                            <th>Pending Qty.</th>
                                            <th>Return Qty.</th>
                                            <th>Unit</th>
                                            <th>Reason</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10" class="text-center" id="noDetails">
                                                No Item Return Slip Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="row g-1 mt-2">
                                    <div class="mb-3">
                                        <label for="irs_special_note" class="form-label">Special Note</label>
                                        <textarea class="form-control" name="irs_special_note" id="irs_special_note"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/item_return_slip.js?ver='.getJsVersion()) }}"></script>
@endpush