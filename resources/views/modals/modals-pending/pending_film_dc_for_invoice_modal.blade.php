<div class="modal fade" id="PendingFilmDcForInvoiceModal" aria-labelledby="PendingFilmDcForInvoiceModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PendingFilmDcForInvoiceModalLabel">Pending For Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="PendingFilmDcForInvoiceForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="PendingFilmDcTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" name="checkall-pending_data" class="form-check-input" id="checkall-pending_data"/>
                                    </th>
                                    <th>Film DC No.</th>
                                    <th>Date</th>
                                    <th>Dispatch Type</th>
                                    <th>Party Name</th>
                                    <th>Total Qty.</th>
                                    <th>Sp Note.</th>
                                </tr>
                            </thead>
                            <tbody id="pending_film_dc_tbody">
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="import_pending_dc_btn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
