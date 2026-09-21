
<div class="modal fade" id="OAPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending Quotation For Order Acceptance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendigOAForm" name="addPendigOAForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingQuotationDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-quot_data" class="simple-check" id="checkall-quot_data"/></th>
                                <th>Quot. No.</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Customer Code</th>
                                <th>Ref. No.</th>
                                <th>Type of Test</th>
                                <th>Process At</th>
                                <th>Type of Job</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>Qty.</th>
                                <th>Unit</th>
                                <th>Remark</th>
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



