<!-- Reports Selection Modal (Radio Buttons) -->
<div class="modal fade" id="RtReportsListModal" aria-labelledby="RtReportsListModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="RtReportsListModalLabel">Test Report (RT)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="rtReportsTable" style="width:100%">
                        <thead>
                            <tr>
                                <th class="radio_column"></th>
                                <th>Report No.</th>
                                <th>Rev. No.</th>
                                <th>Report Date</th>
                                <th>Customer</th>
                                <th>RT No.</th>
                                <th>Heat No.</th>
                                <th>Material</th>
                                <th>Job Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitReportSelect">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
