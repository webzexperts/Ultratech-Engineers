<div class="modal fade" id="TypeOfJobModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="TypeOfJobModalLabel">Type of Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonTypeOfJobForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="type_of_job" class="form-label">Type of Job <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="type_of_job" id="type_of_job" class="form-control" required>
                                <!-- <input type="text" name="type_of_job" id="type_of_job" class="form-control" onkeyup="suggestTypeOfJob(event,this)" required> -->
                                <!-- <div id="type_of_job_list" class="suggestion_list"></div>
                                <input type="hidden" name="type_of_job_suggesion" id="type_of_job_suggesion"> -->
                                <div class="invalid-tooltip">
                                    Enter Type of Job
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("type_of_job","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/type_of_job.js?ver='.getJsVersion()) }}"></script>
@endpush