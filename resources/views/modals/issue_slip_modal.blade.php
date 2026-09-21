<div class="modal fade" id="IssueSlipModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="IssueSlipModalLabel">Item Issue Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonIssueSlipForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row gy-4">
                                        <div class="col-xxl-4 col-md-4">
                                            <div class="mb-3 position-relative">
                                                <label class="form-label">Issue Slip No. <sup class="astric">*</sup></label>
                                                <div class="d-flex gap-2 align-items-start">
                                                    <input type="text" class="form-control" id="iis_sequence" name="iis_sequence" style="max-width:80px" onchange="checkSequence()" required>
                                                    <input type="text" class="form-control skip-tab" id="iis_number" name="iis_number" tabindex="-1" readonly>
                                                    <div class="invalid-tooltip">
                                                        Enter Issue Slip No.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2 position-relative">
                                                <label class="form-label">Issue Slip Date <sup class="astric">*</sup></label>
                                                <div class="d-flex gap-2 position-relative">
                                                    <input type="text" class="form-control trans-date-picker" id="iis_date" name="iis_date" required autocomplete="off"/>
                                                    <div class="invalid-tooltip">
                                                        Enter Issue Slip Date
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-4 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Issuer </label>
                                                <select class="js-example-basic-single" name="iis_issuer_id" id="iis_issuer_id">
                                                    <option value="">Select Issuer </option>
                                                    @forelse(getUsers() as $issuer_user)
                                                        <option value="{{ $issuer_user->id }}">{{ $issuer_user->person_name }}</option>
                                                        @empty
                                                    @endforelse
                                                </select>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Receiver </label>
                                                <select class="js-example-basic-single" name="iis_receiver_id" id="iis_receiver_id">
                                                    <option value="">Select Receiver </option>
                                                    @forelse(getUsers() as $receiver_user)
                                                        <option value="{{ $receiver_user->id }}">{{ $receiver_user->person_name }}</option>
                                                        @empty
                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary toggleButton" data-bs-target="#IssueSlipDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Item Issue Slip Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="IssueSlipDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_col">Actions</th>
                                                        <th scope="col">Item</th>
                                                        <th scope="col">Item Type</th>
                                                        <th scope="col">Sr. No.</th>
                                                        <th scope="col">Issue Qty.</th>
                                                        <th scope="col">Stock</th>
                                                        <th scope="col">Issue Type</th>
                                                        <th scope="col">Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noDetails">
                                                        <td colspan="8">No Item Issue Slip Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row gy-4">
                                        <div class="col-xxl-4 col-md-4">
                                            <label class="form-label">Sp Note.</label>
                                            <textarea class="form-control" name="iis_special_note" id="iis_special_note"></textarea>
                                        </div>

                                        <div class="col-md-8">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-md-8">Checked By Issuer (Damage / Shelf life / Expiry before issue)</div>
                                                <div class="col-md-4">
                                                    <select class="js-example-basic-single form-select" name="iis_checked_by_issuer" id="iis_checked_by_issuer">
                                                        <option value="">Select</option>
                                                        <option value="Ok">Ok</option>
                                                        <option value="Not ok">Not ok</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center">
                                                <div class="col-md-8">Checked By Receiver (Damage / Shelf life / Expiry before receiving)</div>
                                                <div class="col-md-4">
                                                    <select class="js-example-basic-single form-select" name="iis_checked_by_receiver" id="iis_checked_by_receiver">
                                                        <option value="">Select</option>
                                                        <option value="Ok">Ok</option>
                                                        <option value="Not ok">Not ok</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/issue_slip.js?ver='.getJsVersion()) }}"></script>
@endpush