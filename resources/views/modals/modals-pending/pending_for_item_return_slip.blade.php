<div class="modal fade" id="ItemReturnSlipPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Item Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addPendigIISForm" name="addPendigIISForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingIssueDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-issue_slip_data" class="simple-check" id="checkall-issue_slip_data"/></th>
                                <th>Sr No.</th>
                                <th>Issue Slip No.</th>
                                <th>Issue Slip Date</th>
                                <th>Receiver</th>
                                <th>Item</th>
                                <th>Item Type</th>
                                <th>Issue Qty.</th>
                                <th>Pending Qty.</th>
                                <th>Unit</th>
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