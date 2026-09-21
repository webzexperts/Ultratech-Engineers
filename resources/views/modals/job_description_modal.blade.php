<div class="modal fade" id="JobDescriptionModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="JobDescriptionModalLabel">Job Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonJobDescriptionForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="job_description" class="form-label">Job Description <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="job_description" id="job_description" class="form-control" required>
                                <!-- <input type="text" name="job_description" id="job_description" class="form-control" onkeyup="suggestJobDescription(event,this)" required> -->
                                <!-- <div id="job_description_list" class="suggestion_list"></div>
                                <input type="hidden" name="job_description_suggesion" id="job_description_suggesion"> -->
                                <div class="invalid-tooltip">
                                    Enter Job Description
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("job_description","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/job_description.js?ver='.getJsVersion()) }}"></script>
@endpush