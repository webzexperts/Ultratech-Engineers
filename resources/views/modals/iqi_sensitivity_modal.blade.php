<div class="modal fade" id="IqiSensitivityModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="IqiSensitivityModalLabel">IQI Sensitivity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonIqiSensitivityForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1 ">
                            <div class="col-lg-2">
                                <label for="iqi_sensitivity" class="form-label">IQI Sensitivity <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-4 position-relative">
                                <input type="text" name="iqi_sensitivity" id="iqi_sensitivity" class="form-control"  required>
                                {{-- onkeyup="suggestIqiSensitivity(event,this)" --}}
                                {{-- <div id="iqi_sensitivity_list" class="suggestion_list"></div>
                                <input type="hidden" name="iqi_sensitivity_suggesion" id="iqi_sensitivity_suggesion"> --}}
                                <div class="invalid-tooltip">
                                    Enter IQI Sensitivity.
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("iqi_sensitivity","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/iqi_sensitivity.js?ver='.getJsVersion()) }}"></script>
@endpush
