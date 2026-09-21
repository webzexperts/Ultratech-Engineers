<div class="modal fade" id="AcceptanceStandardModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="AcceptanceStandardModalLabel">Acceptance Standard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonAcceptanceStandardForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="acceptance_standard" class="form-label">Acceptance Standard <sup class="astric">*</sup></label>
                            </div>

                            <div class="col-lg-4 position-relative">
                                <input type="text" name="acceptance_standard" id="acceptance_standard" class="form-control" required>
                                <!-- <input type="text" name="acceptance_standard" id="acceptance_standard" class="form-control" onkeyup="suggestAcceptanceStandard(event,this)" required> -->
                                <!-- <div id="acceptance_standard_list" class="suggestion_list"></div>
                                <input type="hidden" name="acceptance_standard_suggesion" id="acceptance_standard_suggesion"> -->
                                <div class="invalid-tooltip">
                                    Enter Acceptance Standard
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="row mb-3">
                        <div class="col-lg-4">
                            <label for="status" class="form-label col-form-label">Status </label>
                        </div>

                        <div class="col-lg-6">
                            <select class="js-example-basic-single" name="status" id="status">
                                <option value="Active">Active</option>
                                <option value="Deactive">Deactive</option>
                            </select>
                        </div>
                    </div> -->
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("acceptance_standard","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/acceptance_standard.js?ver='.getJsVersion()) }}"></script>
@endpush