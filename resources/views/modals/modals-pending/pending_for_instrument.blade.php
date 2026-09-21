
<div class="modal fade" id="GrnPendingForInstrumentModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending For Instrument </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendigGrnForm" name="addPendigGrnForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingGrnDataTable">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>Type</th>
                                <th>GRN No.</th>
                                <th>GRN Date </th>
                                <th>Supplier</th>
                                <th>Challan No.</th>
                                <th>Challan Date</th>
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>Qty.</th>
                                <th>Pend. Qty.</th>
                                <th>Unit</th>
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



