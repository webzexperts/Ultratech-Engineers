
<div class="modal fade" id="GrnSupplierDCPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Supplier DC For GRN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendingGrnSupplierDCForm" name="addPendingGrnSupplierDCForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingSupplierDCDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-dc_data" class="simple-check" id="checkall-dc_data"/></th>
                                <th>DC No.</th>
                                <th>Date</th>
                                <th>Ref. No. & Date</th>
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>Pend. DC Qty.</th>
                                <th>Unit</th>
                                <th>Sr. No.</th>
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



