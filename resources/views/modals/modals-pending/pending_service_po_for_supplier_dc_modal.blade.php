
<div class="modal fade" id="SupplierDCPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Supplier DC For GRN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendinSupplierDCForm" name="addPendingSupplierDCForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingSupplierDCDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-sup_dc_data" class="simple-check" id="checkall-sup_dc_data"/></th>
                                <th>PO No.</th>
                                <th>PO Date</th>
                                <th>Purpose</th>
                                <th>Ref. No. & Date</th>
                                <th>Bill To</th>
                                <th>For Location</th>
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>Sr. No.</th>
                                <th>Pend. PO Qty.</th>
                                <th>Unit</th>
                                <th>Remark</th>
                                <th>Prepared By</th>
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



