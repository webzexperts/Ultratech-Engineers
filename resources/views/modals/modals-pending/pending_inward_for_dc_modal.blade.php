<div class="modal fade" id="PendingInwardForDCModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ContactModalLabel">Pending Non Returnable Material Challan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addPendigDCForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                   <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingForMaterialDCTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-inwd_data" class="simple-check" id="checkall-inwd_data"/></th>
                                <th>Material From</th>
                                <th>Inward No.</th>
                                <th>Inward Date</th>
                                <th>Challan No.</th>
                                <th>Challan Date</th>
                                <th>Type of Job</th>
                                <th>Job Desc.</th>
                                <th>Method</th>
                                <th>Material DC Type</th>
                                <th>Pending Qty.</th>
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