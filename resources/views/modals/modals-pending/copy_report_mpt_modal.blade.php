<div class="modal fade" id="CopyReportMptModal" aria-labelledby="CopyReportMptModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CopyReportMptModalLabel">Test Report (MPT)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="copyReportMptForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="CopyReportMptTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="radio_column"></th>
                                    <th>Report No.</th>
                                    <th>Report Date</th>
                                    <th>Customer</th>
                                    <th>NABL</th>
                                    <th>Type</th>
                                    <th>Type of Job</th>
                                    <th>Job Desc.</th>
                                    <th>Part No.</th>
                                    <th>Drg. No.</th>
                                    <th>Material</th>
                                    <th>Heat No.</th>
                                    <th>Product Code</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitCopyReportMptBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
