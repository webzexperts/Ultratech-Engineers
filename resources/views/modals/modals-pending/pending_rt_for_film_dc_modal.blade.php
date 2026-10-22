<div class="modal fade" id="PendingRtForFilmDCModal" tabindex="-1" aria-labelledby="PendingRtForFilmDCModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingRtForFilmDCModalLabel">Pending RT Reports for Film DC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pendingRtForFilmDCForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingFilmDcTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center radio_column" style="width: 40px;">
                                        <input type="checkbox" name="checkall-pending_data" id="checkall-pending_data" class="form-check-input">
                                    </th>
                                    <th scope="col">RT Report No.</th>
                                    <th scope="col">Rev. No.</th>
                                    <th scope="col">Report Date</th>
                                    <th scope="col">Customer Client</th>
                                    <th scope="col">RT No.</th>
                                    <th scope="col">Heat No.</th>
                                    <th scope="col">Job Description</th>
                                    <th scope="col">Part No. / Die No.</th>
                                    <th scope="col">Drg. No.</th>
                                    <th scope="col">Product Code</th>
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
                <button type="button" class="btn btn-success" id="submitPendingReportsBtn">Submit</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>