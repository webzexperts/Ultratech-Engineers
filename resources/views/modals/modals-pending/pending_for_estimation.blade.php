
<div class="modal fade" id="PendingEstimationModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Pending For Estimation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendingestimationForm" name="addPendingestimationForm" class="stdform" method="post" enctype="multipart/form-data">
                    @csrf
                     <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingEstimationDataTable">
                        <thead>
                            <tr>
                                 <th class="radio_column"></th>
                                <th>Inq. No.</th>
                                <th>Date</th>
                                <th>Customer</th>
                                {{-- <th>Customer Code</th> --}}
                                <th>Ref. No.</th>
                                <th>Type of Test</th>
                                <th>Type of Job</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>Process At</th>
                                <th>Qty.</th>
                                <th>Unit</th>
                                <th>Inq. Remark</th>
                                <th>Inq. Sp. Note</th>
                            </tr>
                        </thead>

                        <tbody>
                            
                        </tbody>
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



