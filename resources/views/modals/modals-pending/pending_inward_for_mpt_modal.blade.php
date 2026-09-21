<div class="modal fade" id="PendingInwardForMptModal" aria-labelledby="PendingInwardForMptModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingInwardForMptModalLabel">Pending Inward for MPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addPendingMptForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingForMptTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="radio_column"></th>
                                    <th>Inward No.</th>
                                    <th>Date</th>
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
                                    <th>Pend. Qty.</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitPendingMptBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
