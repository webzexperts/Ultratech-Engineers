<div class="modal fade" id="PendingForGRNModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingForGRNModalLabel">Pending For GRN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="PendingForLPTChemicalForm" name="PendingForLPTChemicalForm" class="stdform" method="post">
                    @csrf
                   <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingForLPTChemicalTable">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>GRN No.</th>
                                <th>GRN Date</th>
                                <th>Supplier</th>
                                <th>Challan No.</th>
                                <th>Challan Date</th>
                                <th>LPT Chemical</th>
                                <th>GRN Quantity</th>
                                <th>Pending Quantity</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
                </div>
            </form>
        </div>
    </div>
</div>