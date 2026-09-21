<div class="modal fade" id="CompanyYearModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CompanyYearLabel">Company Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonCompanyYearForm"  class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="type" class="form-label">Type <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <select class="js-example-basic-single" name="type" id="type" required>
                                    <option value="forward">Forward</option>
                                    <option value="reverse">Reverse</option>
                                </select>
                                <div class="invalid-tooltip">
                                    Select Type
                                </div>
                            </div>
                        </div>

                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="startdate" class="form-label">Start Date </label>
                            </div>
                            <div class="col-lg-4">
                                <input type="text" name="startdate" id="startdate" class="form-control skip-tab" readonly>
                                <input type="hidden" name="year" id="year"/>
                                <input type="hidden" name="yearcode" id="yearcode"/>
                            </div>
                        </div>

                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="enddate" class="form-label">End Date </label>
                            </div>
                            <div class="col-lg-4">
                                <input type="text" name="enddate" id="enddate" class="form-control skip-tab" readonly>
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
    <script src="{{ URL::asset('views/js/company_year.js?ver='.getJsVersion()) }}"></script>
@endpush