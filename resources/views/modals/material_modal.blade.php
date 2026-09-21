<div class="modal fade" id="MaterialModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="MaterialModalLabel">Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonMaterialForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row mt-2">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <div class="row g-1">
                                    <div class="col-3">
                                        <label for="material" class="form-label">Material <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="material" id="material" class="form-control" required>
                                        <!-- <input type="text" name="material" id="material" class="form-control" onkeyup="suggestMaterial(event,this)" required>
                                        <div id="material_list" class="suggestion_list"></div>
                                        <input type="hidden" name="material_suggesion" id="material_suggesion"> -->
                                        <div class="invalid-tooltip">
                                            Enter Material
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
                @if(hasAccess("material","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/material.js?ver='.getJsVersion()) }}"></script>
@endpush