<div class="modal fade" id="FilmModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="FilmModalLabel">Film</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonFilmForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="film_size_inch" class="form-label">Film Size (inch) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="film_size_inch" id="film_size_inch" class="form-control skip-tab" maxlength="100" readonly required>
                                        <div class="invalid-tooltip">
                                            Enter Film Size (inch).
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="length_inch" class="form-label">Length (inch) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="length_inch" id="length_inch" class="form-control isNumberKey" onblur="formatPoints(this,2)" required>
                                        <div class="invalid-tooltip">
                                            Enter Length (inch).
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="width_inch" class="form-label">Width (inch) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="width_inch" id="width_inch" class="form-control isNumberKey" onblur="formatPoints(this,2)" required>
                                        <div class="invalid-tooltip">
                                            Enter Width (inch).
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="sq_in" class="form-label">SqIn </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="sq_in" id="sq_in" class="form-control skip-tab" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="film_size_cm" class="form-label">Film Size (cm) <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="film_size_cm" id="film_size_cm" class="form-control skip-tab" maxlength="100" readonly required>
                                        <div class="invalid-tooltip">
                                            Enter Film Size (cm).
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="length_cm" class="form-label">Length (cm) </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="length_cm" id="length_cm" class="form-control skip-tab" disabled>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="width_cm" class="form-label">Width (cm) </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="width_cm" id="width_cm" class="form-control skip-tab" disabled>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="sq_cm" class="form-label">SqCm </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="sq_cm" id="sq_cm" class="form-control skip-tab" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="status" class="form-label">Status <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="status" id="status" required>
                                            <option value="Active">Active</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Status.
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
                @if(hasAccess("film","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/film.js?ver='.getJsVersion()) }}"></script>
@endpush
