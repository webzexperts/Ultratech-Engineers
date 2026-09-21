<div class="modal fade" id="FilmResultModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="FilmResultModalLabel">Film Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonFilmResultForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-7">
                                <!-- Film Result -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        <label for="film_result_name" class="form-label">Film Result <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-7 position-relative">
                                        <input type="text" name="film_result_name" id="film_result_name" class="form-control" maxlength="100" required>
                                        <div class="invalid-tooltip">
                                            Enter Film Result.
                                        </div>
                                    </div>
                                </div>

                                <!-- Result (radio group) -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-3">
                                        {{-- OLD CODE COMMENTED OUT:
                                        <label class="form-label col-form-label">Result <sup class="astric">*</sup></label>
                                        --}}
                                        <label class="form-label col-form-label">Result</label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_result_type_fix" id="ftf_accepted" value="Accepted" checked required>
                                                <label class="form-check-label" for="ftf_accepted">Accepted</label>
                                            </div>
                                            <!-- <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_result_type_fix" id="ftf_not_accepted" value="Not Accepted" required>
                                                <label class="form-check-label" for="ftf_not_accepted">Not Accepted</label>
                                            </div> -->
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_result_type_fix" id="ftf_repair" value="Repair" required>
                                                <label class="form-check-label" for="ftf_repair">Repair</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_result_type_fix" id="ftf_reshoot" value="Reshoot" required>
                                                <label class="form-check-label" for="ftf_reshoot">Reshoot</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_result_type_fix" id="ftf_retake" value="Retake" required>
                                                <label class="form-check-label" for="ftf_retake">Retake</label>
                                            </div>
                                        </div>
                                        <div class="invalid-tooltip">
                                            Select Result.
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
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/film_result.js?ver='.getJsVersion()) }}"></script>
@endpush
