<!-- Pending Repair Reports Modal -->
<div class="modal fade" id="PendingRepairReportsModal" tabindex="-1" aria-labelledby="PendingRepairReportsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingRepairReportsModalLabel">Pending Test Report RT Repair Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingRepairReportsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>Report No.</th>
                                <th>Rev. No.</th>
                                <th>Date</th>
                                <th>Type of Job</th>
                                <th>Part No.</th>
                                <th>Drg. No.</th>
                                <th>Heat No.</th>
                                <th>Product Code</th>
                                <th>Material</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="applyPendingRepairBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

