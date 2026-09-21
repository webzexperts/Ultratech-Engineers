<div class="modal fade" id="CopyRssRtModal" aria-labelledby="CopyRssRtModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CopyRssRtModalLabel">Radiographic Shooting Sketch (RSS)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="copyRssRtForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="CopyRssRtTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="radio_column"></th>
                                    <th>RSS No.</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type of Job</th>
                                    <th>Job Desc.</th>
                                    <th>Part No.</th>
                                    <th>Drg. No.</th>
                                    <th>Area of Coverage</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitCopyRssRtBtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
