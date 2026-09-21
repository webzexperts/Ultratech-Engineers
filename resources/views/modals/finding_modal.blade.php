<div class="modal fade" id="FindingModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="FindingModalLabel">Finding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonFindingForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="finding_name" class="form-label">Finding <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="finding_name" id="finding_name" class="form-control" maxlength="100" required>
                                <div class="invalid-tooltip">
                                    Enter Finding.
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Abbreviation -->
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="abbreviation" class="form-label">Abbreviation</label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="abbreviation" id="abbreviation" class="form-control" maxlength="100">
                                <div class="invalid-tooltip">
                                    Enter Abbreviation.
                                </div>
                            </div>
                        </div> --}}

                        {{-- <!-- Req. Finding Level -->
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <!-- <label for="required_finding_level" class="form-label">Req. Finding Level</label> -->
                            </div>
                            <div class="col-lg-4 position-relative">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_finding_level" id="required_finding_level" value="Yes" checked><label for="required_finding_level" class="form-label">Req. Finding Level</label>
                                </div>
                            </div>
                        </div> --}}
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("finding","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/finding.js?ver='.getJsVersion()) }}"></script>
@endpush
