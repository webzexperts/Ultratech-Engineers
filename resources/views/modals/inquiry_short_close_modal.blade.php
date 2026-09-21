 <div class="modal fade" id="InquiryShortCloseModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-xl modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="InquiryShortCloseModalLabel">Inquiry Short Close</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonInquiryShortCloseForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">               

                    <div class="col-md-4">
                        <div class="row g-2">
                            <div class="col-4">
                               <label for="inq_sc_date" class="form-label">Inquiry Short Close Date <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-8 position-relative">
                                <input type="text" class="form-control trans-date-picker" id="inq_sc_date" name="inq_sc_date" required autocomplete="off" />
                                <div class="invalid-tooltip">
                                    Enter Inquiry Short Close Date.
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table nowrap align-middle table-bordered" id="InquiryShortCloseDataTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="checkall-inq_data" class="simple-check" id="checkall-inq_data"/></th>
                                <th>Inq. No.</th>
                                <th>Date</th>
                                {{-- <th>Customer Code</th> --}}
                                <th>Customer</th>
                                <th>Ref. No.</th>
                                <th>Type of Test</th>
                                <th>Process At</th>
                                <th>Type of Job</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>Qty.</th>
                                <th>Remark</th>
                                <th>Sp. Note</th>
                            </tr>
                        </thead>

                        <tbody>

                        </tbody>
                    </table>                    


                    <div class="col-md-4">
                        <div class="row g-2 mb-1">
                            <div class="col-4">
                              <label for="inq_sc_regret_reason_id" class="form-label">
                                    Regret Reason <sup class="astric">*</sup>
                                </label>
                            </div>
                            <div class="col-8 position-relative">
                                <select class="js-example-basic-single form-select"
                                        name="inq_sc_regret_reason_id"
                                        id="inq_sc_regret_reason_id"
                                        required>
                                    <option value="">Select Reason</option>
                                        @forelse(getInqSCreason() as $reason)
                                        <option value="{{ $reason->id }}">{{ $reason->reason_name    }}</option>
                                        @empty
                                    @endforelse
                                </select>
                                <div class="invalid-tooltip">
                                    Select Regret Reason
                                </div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-4 justify-content-start">
                               <label for="inq_sc_special_note" class="form-label">Special Note</label>
                            </div>
                            <div class="col-8 position-relative">
                                 <textarea class="form-control"
                                        name="inq_sc_special_note"
                                        id="inq_sc_special_note"></textarea>
                            </div>
                        </div>

                    </div>

                    
                                     
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
<script src="{{ URL::asset('views/js/inquiry_short_close.js?ver='.getJsVersion()) }}"></script>
@endpush
