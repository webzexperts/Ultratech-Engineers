 <div class="modal fade" id="POShortCloseModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-xl modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="POShortCloseModalLabel">PO Short Close</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonPOShortCloseForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="col-md-4">
                        <div class="row g-2">
                            <div class="col-4">
                                <label for="po_sc_date" class="form-label">Short Close Date <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-8 position-relative">
                                <input type="text" class="form-control trans-date-picker" id="po_sc_date" name="po_sc_date" required autocomplete="off" />
                                <div class="invalid-tooltip">
                                    Enter Short Close Date.
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table nowrap align-middle table-bordered" id="POShortCloseDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-po_data" class="simple-check" id="checkall-po_data"/></th>
                                <th>Type</th>
                                <th>PO No.</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Ref. No. & Date</th>
                                <th>Bill To</th>
                                <th>Ship To</th>
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>PO Qty. </th>
                                <th>Unit</th>
                                <th>Pend. PO Qty.</th>
                                <th>Short Close Qty.</th>
                                <th>Reason</th>
                                <th>Del. Date</th>
                                <th>Remark</th>
                                <th>Prepared By</th>
                            </tr>
                        </thead>

                        <tbody>

                        </tbody>
                    </table>
                
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


@push('script-modal')
<script id="q1p6px">
    let reasonOptions = `
        <option value="">Select Reason</option>
         @forelse(getPOSCreason() as $reason)
            <option value="{{ $reason->id }}" >{{ $reason->reason_name }}</option>
            @empty
        @endforelse
    `;
</script>
<script src="{{ URL::asset('views/js/po_short_close.js?ver='.getJsVersion()) }}"></script>
@endpush
