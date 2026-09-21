 <div class="modal fade" id="NRMCModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="GrnModalLabel">Non Returnable Material Challan</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonNRMCForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row mt-2 g-3">
                        <div class="col-md-4">
                            <label class="form-label">DC No. <sup class="astric">*</sup></label>
                            <div class="d-flex gap-2 position-relative">
                                <input type="text" class="form-control" id="nrmc_sequence" name="nrmc_sequence" style="max-width:80px" autofocus required>
                                <input type="text" class="form-control" id="nrmc_number" name="nrmc_number" tabindex="-1" readonly>
                                <input type="text" class="form-control trans-date-picker" id="nrmc_date" name="nrmc_date" required autocomplete="off">
                                <div class="invalid-tooltip">
                                    Please Enter DC No.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Customer <sup class="astric">*</sup></label>
                            <div class="d-flex gap-2 position-relative">
                                <select class="js-example-basic-single form-select" name="nrmc_customer_id" id="nrmc_customer_id" required>
                                    <option value="">Select Customer</option>
                                </select>
                                <div class="invalid-tooltip">
                                    Please Select Customer
                                </div>
                                <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingInwardForDCModal" id="pending_btn" disabled>Pending</button>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <b>Non Returnable Material Challan Details</b>
                            </h4>
                            <!-- <div class="flex-shrink-0">
                                <button type="button" class="btn btn-primary add_detail" data-bs-target="#MiDetailsModal">Add</button>
                            </div> -->
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" id="DCDetailTable">
                                    <thead>
                                        <tr>
                                            <th>Material DC Type</th>
                                            <th>Challan No.</th>
                                            <th>Challan Date</th>
                                            <th>Type Of Job</th>
                                            <th>Job Desc.</th>
                                            <th>Part No.</th>
                                            <th>Type of Test</th>
                                            <th>Pending Qty.</th>
                                            <th>DC Qty.</th>
                                            <th>Unit</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="12" class="text-center" id="noDetails">
                                                No Returnable Material Challan Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3 position-relative">
                                <label for="nrmc_transporter" class="form-label">Transporter </label>
                                <input type="text" class="form-control" id="nrmc_transporter" name="nrmc_transporter" autocomplete="off" />
                                <div id="nrmc_transporter_list" class="suggestion_list"></div>
                                <input type="hidden" id="nrmc_transporter_suggesion">
                                <div class="invalid-tooltip">
                                        Please Enter Transporter
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nrmc_vehicle_number" class="form-label">Vehicle No.</label>
                                <input type="text" class="form-control" id="nrmc_vehicle_number" name="nrmc_vehicle_number" autocomplete="off" />
                            </div>
                        </div>

                         <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nrmc_lr_no_date" class="form-label">LR No. & Date</label>
                                <input type="text" class="form-control" id="nrmc_lr_no_date" name="nrmc_lr_no_date" autocomplete="off"/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nrmc_special_note" class="form-label">Special Note</label>
                                <textarea class="form-control" name="nrmc_special_note" id="nrmc_special_note"></textarea>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


 @push('script-modal')
<script src="{{ URL::asset('views/js/non_ret_mat_challan.js?ver='.getJsVersion()) }}"></script>
@endpush
