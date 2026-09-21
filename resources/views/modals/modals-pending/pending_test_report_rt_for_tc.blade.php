
<div class="modal fade" id="TestReportRTPendingModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Test Report RT For Radiographic Shooting Sketch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               
                <form id="addPendingTestReportRTForm" name="addPendingTestReportRTForm" class="stdform" method="post">
                    @csrf
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="pendingDataTable" data-exclude-search="0">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>Report No.</th>
                                <th>Rev. No.</th>
                                <th>Date</th>
                                <th>Type of Job</th>
                                <th>Job Desc.</th>
                                <th>Part No.</th>
                                <th>Material</th>
                                <th>Special Note</th>
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



