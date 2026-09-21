<div class="modal fade" id="ReasonModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ReasonModalLabel">Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonReasonForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="row g-1 mb-1">
                                <div class="col-3">
                                    <label for="reason_type" class="form-label">Type <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="reason_type" id="reason_type" required>
                                        <option value="">Select Type</option>
                                        @forelse(getReasonType() as $reason)
                                            <option value="{{ $reason }}">{{ $reason }}</option>
                                            @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">
                                        Please Select Type
                                    </div>
                                </div>
                            </div>
                            <div class="row g-1 mb-1">
                                <div class="col-3">
                                    <label for="reason_name" class="form-label ">Reason Name <sup class="astric">*</sup></label>
                                </div>

                                <div class="col-8 position-relative">
                                    <input type="text" name="reason_name" id="reason_name" class="form-control" required>
                                    <div id="reason_name_list" class="suggestion_list"></div>
                                    <input type="hidden" name="reason_name_suggesion" id="reason_name_suggesion">
                                    <div class="invalid-tooltip">
                                        Enter Reason Name
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("reason","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/reason.js?ver='.getJsVersion()) }}"></script>
@endpush