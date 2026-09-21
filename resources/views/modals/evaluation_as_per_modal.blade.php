<div class="modal fade" id="EvaluationAsPerModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EvaluationAsPerModalLabel">Evaluation as per</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonEvaluationAsPerForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1 ">
                            <div class="col-lg-2">
                                <label for="evaluation_as_per" class="form-label">Evaluation as per <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-4 =position-relative">
                                <input type="text" name="evaluation_as_per" id="evaluation_as_per" class="form-control"  required>
                                {{-- onkeyup="suggestEvaluationAsPer(event,this)" --}}
                                {{-- <div id="evaluation_as_per_list" class="suggestion_list"></div>
                                <input type="hidden" name="evaluation_as_per_suggesion" id="evaluation_as_per_suggesion"> --}}
                                <div class="invalid-tooltip">
                                    Enter Evaluation as per.
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("evaluation_as_per", "add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/evaluation_as_per.js?ver='.getJsVersion()) }}"></script>
@endpush
