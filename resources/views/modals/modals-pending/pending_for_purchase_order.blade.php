
<div class="modal fade" id="PurchaseOrderPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Purchase Indent  For Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendigPoForm" name="addPendigPoForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingPIDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-pi_data" class="simple-check" id="checkall-pi_data"/></th>
                                <th>Indent No.</th>
                                <th>Date</th>
                                <th>To Location</th>
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>Pend. Indent Qty.</th>
                                <th>Unit</th>
                                <th>Remark</th>
                                <th>Indent By</th>
                            </tr>
                        </thead>

                        <tbody>

                        </tbody>
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



