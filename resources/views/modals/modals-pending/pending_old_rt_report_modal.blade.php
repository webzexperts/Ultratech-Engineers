<div class="modal fade" id="PendingOldRtReportModal" aria-labelledby="PendingOldRtReportModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingOldRtReportModalLabel">RT Reports Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingOldRtReportTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="radio_column" style="width: 50px;"></th>
                                <th>NABL</th>
                                <th>Report No.</th>
                                <th>Date</th>
                                <th>ULR No.</th>
                                <th>Customer</th>
                                <!-- <th>Project Name</th> -->
                                <th>Type of Job</th>
                                <th>Job Desc.</th>
                                <th>Part No.</th>
                                <th>Drg. No.</th>
                                <th>Heat No.</th>
                                <th>Product Code</th>
                                <th>Material</th>
                                <th>RT No.</th>
                            </tr>
                        </thead>
                        <tbody id="pending_old_rt_report_tbody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitPendingOldRtReportBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>