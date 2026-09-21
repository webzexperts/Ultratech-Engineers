<div class="modal fade" id="PendingRtForMeasurementModal" tabindex="-1" aria-labelledby="PendingRtForMeasurementModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingRtForMeasurementModalLabel">Pending RT Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pendingRtForMeasurementForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <!-- Date Range Filters -->
                    <div class="row g-1 mb-3">
                        <div class="col-md-3">
                            <div class="row g-1 align-items-center">
                                <div class="col-4">
                                    <label for="pending_start_date" class="form-label">From Date</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control pending-date-picker pending-date-filter" id="pending_start_date" name="pending_start_date" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row g-1 align-items-center">
                                <div class="col-4">
                                    <label for="pending_end_date" class="form-label">To Date</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control pending-date-picker pending-date-filter" id="pending_end_date" name="pending_end_date" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" id="btn_reset_pending_filter">Reset Filter</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pending_rt_reports_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center radio_column" style="width: 40px;">
                                        <input type="checkbox" id="select_all_pending_reports" class="form-check-input">
                                    </th>
                                    <th scope="col">NABL</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">RT Report No.</th>
                                    <th scope="col">Rev. No.</th>
                                    <th scope="col">Report Date</th>
                                    <th scope="col">RT No.</th>
                                    <th scope="col">Heat No.</th>
                                    <th scope="col">Material</th>
                                    <th scope="col">Type of Job</th>
                                    <th scope="col">Job Description</th>
                                    <th scope="col">Part No.</th>
                                    <th scope="col">Drg. No.</th>
                                    <th scope="col">Product Code</th>
                                    <th scope="col">Ir-192 SQIN</th>
                                    <th scope="col">Co-60 SQIN</th>
                                    <th scope="col">X-Ray SQIN</th>
                                    <th scope="col">Ir-192 Repair</th>
                                    <th scope="col">Co-60 Repair</th>
                                    <th scope="col">X-Ray Repair</th>
                                    <th scope="col">Customer DC No.</th>
                                </tr>
                            </thead>
                            <tbody id="pending_rt_reports_tbody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitPendingReportsBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
