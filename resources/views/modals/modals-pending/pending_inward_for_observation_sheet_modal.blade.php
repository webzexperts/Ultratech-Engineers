<div class="modal fade" id="PendingInwardForObservationSheetModal" aria-labelledby="PendingInwardForObservationSheetModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingInwardForObservationSheetModalLabel">Pending Inward for Observation Sheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addPendingObservationSheetForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingForObservationSheetTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="select_all_pending_obs">
                                    </th>
                                    <th>RT No.</th>
                                    <th>Nature</th>
                                    <th>Type of Test</th>
                                    <th>In. No.</th>
                                    <th>In. Date</th>
                                    <th>NABL</th>
                                    <th>Test At</th>
                                    <th>Type</th>
                                    <th>DC No.</th>
                                    <th>DC Date</th>
                                    <th>PO No.</th>
                                    <th>PO Date</th>
                                    <th>Type of Job</th>
                                    <th>Job Desc.</th>
                                    <th>Part No.</th>
                                    <th>Drg. No.</th>
                                    <th>Material</th>
                                    <th>Heat No.</th>
                                    <th>Product Code</th>
                                    <th>Qty.</th>
                                </tr>
                            </thead>
                            <tbody id="pending_obs_tbody">

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitPendingObsBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>