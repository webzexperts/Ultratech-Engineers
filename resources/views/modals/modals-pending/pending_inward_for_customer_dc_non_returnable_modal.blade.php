<div class="modal fade" id="PendingInwardForCustomerDcModal" aria-labelledby="PendingInwardForCustomerDcModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingInwardForCustomerDcModalLabel">Pending Inward for Material Outward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addPendingCustomerDcForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingCustomerDcTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" name="checkall-pending_data" class="form-check-input" id="checkall-pending_data"/></th>
                                    <th>In. No.</th>
                                    <th>In. Date</th>
                                    <th>DC No.</th>
                                    <th>DC Date</th>
                                    <th>PO No.</th>
                                    <th>PO Date</th>
                                    <th>Type of Test</th>
                                    <th>Type of Job</th>
                                    <th>Job Description</th>
                                    <th>Part No.</th>
                                    <th>Drg. No.</th>
                                    <th>Material</th>
                                    <th>Heat No.</th>
                                    <th>RT No.</th>
                                    <th>Product Code</th>
                                    <th>In. Qty.</th>
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
                <button type="button" class="btn btn-primary" id="import_pending_btn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
