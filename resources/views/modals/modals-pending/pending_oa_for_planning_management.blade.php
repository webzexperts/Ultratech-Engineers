
<div class="modal fade" id="PMPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Order Acceptance For Planning Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendigPMForm" name="addPendigPMForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingOADataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-oa_data" class="simple-check" id="checkall-oa_data"/></th>
                                <th>Type of Test</th>
                                <th>Pending Qty.</th>
                                <th>OA Qty.</th>
                                <th>Unit</th>
                                <th>Type of Job</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>Customer</th>
                                <th>Customer Code</th>
                                <th>PO No.</th>
                                <th>PO Date</th>
                                <th>OA No.</th>
                                <th>OA Date</th>
                                <th>OA Type</th>
                                <th>Process At</th>
                                <th>Remark</th>
                                <th>Sp. Note</th>
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



