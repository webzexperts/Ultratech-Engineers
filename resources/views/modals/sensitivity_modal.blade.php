<div class="modal fade" id="SensitivityModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="SensitivityModalLabel">Sensitivity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonSensitivityForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="sensitivity" class="form-label">Sensitivity <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-4 position-relative">
                                <input type="text" name="sensitivity" id="sensitivity" class="form-control" onkeyup="suggestSensitivity(event,this)" required>
                                <div id="sensitivity_list" class="suggestion_list"></div>
                                <input type="hidden" name="sensitivity_suggesion" id="sensitivity_suggesion">
                                <div class="invalid-tooltip">
                                    Enter Sensitivity.
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
    <script src="{{ URL::asset('views/js/sensitivity.js?ver='.getJsVersion()) }}"></script>
@endpush