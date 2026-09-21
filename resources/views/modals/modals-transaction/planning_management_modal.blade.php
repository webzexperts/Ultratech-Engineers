 <div class="modal fade" id="PlanningManagementModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="PlanningManagementModalLabel">Planning Management</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonPMForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">

                                <div class="card-body">
                                    <div class="row gy-4">

                                        <!-- COLUMN 1 -->
                                        <div class="col-xxl-4 col-md-4">
                                            <div class="mb-3">
                                                    <label class="form-label">Planning No. <sup class="astric">*</sup></label>
                                                    <div class="d-flex gap-2 position-relative">
                                                        <input type="text"                                                   class="form-control"
                                                            id="pm_sequence"
                                                            name="pm_sequence"
                                                            style="max-width:80px"
                                                            autofocus
                                                            required>

                                                        <input type="text"
                                                            class="form-control"
                                                            id="pm_number"
                                                            name="pm_number"
                                                            tabindex="-1"
                                                            readonly>

                                                        <input type="text"
                                                            class="form-control trans-date-picker"
                                                            id="pm_date"
                                                            name="pm_date"
                                                            required
                                                            autocomplete="off">
                                                    </div>
                                            </div>
                                        </div>

                                        <!-- COLUMN 2 -->
                                        <div class="col-xxl-4 col-md-4">
                                            <div class="mb-2">
                                                <label class="form-label">Process At <sup class="astric">*</sup></label>
                                                <div class="d-flex gap-2 position-relative">
                                                    <select class="js-example-basic-single" name="pm_process_at" id="pm_process_at" required>
                                                        <option value="">Select Process At</option>
                                                        <option value="In-House">In-House</option>
                                                        <option value="Outside">Outside</option>                                           
                                                    </select>

                                                    <div class="invalid-tooltip">
                                                        Please Select Process At
                                                    </div>

                                                    <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PMPendingModal" id="pendingBtn">
                                                        Pending
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-4 col-md-4">
                                            <div class="mb-1">
                                                <label for="pm_completion_avrg_period" class="form-label">Completion Avrg. Period</label>
                                                <input type="text" class="form-control" name="pm_completion_avrg_period" id="pm_completion_avrg_period">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
                    

                    <div class="card mt-0">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <b>Planning Management Details</b>
                            </h4>
                            <div class="flex-shrink-0">
                                <!-- <button type="button" class="btn btn-primary add_detail" data-bs-target="#MiDetailsModal">Add</button> -->
                            </div>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" id="PMDetailTable">
                                    <thead>
                                        <tr>
                                            <!-- <th>Actions</th> -->
                                            <th>Type of Test</th>
                                            <th>Plan Qty.</th>
                                            <th>Order Qty.</th>
                                            <th>Plan. Pending Qty.</th>
                                            <th>Unit</th>
                                            <th>Type Of Job</th>
                                            <th>Job Description</th>
                                            <th>Part No.</th>
                                            <th>Customer Code</th>
                                            <th>Customer</th>
                                            <th>PO No.</th>
                                            <th>PO Date</th>
                                            <th>OA No.</th>
                                            <th>OA Date</th>
                                            <th>OA Type</th>
                                            <th>OA Process At</th>
                                            <th>Remark</th>
                                            <th>Sp. Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="18" class="text-center" id="noDetails">
                                                No Planning Management Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
<script src="{{ URL::asset('views/js/planning_management.js?ver='.getJsVersion()) }}"></script>
@endpush
