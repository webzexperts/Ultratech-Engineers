<div class="modal fade" id="PendingForPOMappingProcessModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Material Inspection For PO Mapping Process</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="PendingMaterialInspePOMPForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingPOMappingProcessDataTable">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>Insp. No.</th>
                                <th>Insp. Date.</th>
                                <th>Customer Code</th>
                                <th>Inward No.</th>
                                <th>Inward Date</th>
                                <th>Challan No.</th>
                                <th>Challan Date</th>
                                <th>Type of Job</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>Type of Test</th>
                                <th>Inward Qty.</th>
                                <th>Unit</th>
                                <th>Insp. Qty.</th>
                                <th>Pending Qty.</th>
                                <th>Sp. Note</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>