<div class="modal fade" id="EnclosureModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EnclosureModalLabel">Enclosure / Source Storage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonEnclosureForm" class="row g-3 needs-validation" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="enclosure_type_value_fix" id="enclosure_radio" value="Enclosure" checked>
                                                <label class="form-check-label" for="enclosure_type_value_fix">
                                                    Enclosure
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="enclosure_type_value_fix" id="source_storage_radio" value="Source Storage">
                                                <label class="form-check-label" for="enclosure_type_value_fix">
                                                    Source Storage
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Enclosure Name -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-4">
                                        <label for="enclosure_name" class="form-label">Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="enclosure_name" id="enclosure_name" class="form-control" maxlength="100" required>
                                        <div class="invalid-tooltip">
                                            Enter Name.
                                        </div>
                                    </div>
                                </div>

                                <!-- Enclosure No. -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-4">
                                        <label for="enclosure_no" class="form-label">No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="enclosure_no" id="enclosure_no" class="form-control" maxlength="100" required>
                                        <div class="invalid-tooltip">
                                            Enter No.
                                        </div>
                                    </div>
                                </div>

                                <!-- Enclosure Layout (file upload, optional) -->
                                <div class="row g-2 mb-2 position-relative" id="layoutRow">
                                    <div class="col-4">
                                        <label class="form-label">Layout <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="enclosure_layout" id="enclosure_layout" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="enclosure_layout_doc" name="enclosure_layout_doc" />
                                                <a href="#" data-remove="enclosure_layout" id="enclosure_layout_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileEnclosureLayout(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="enclosure_layout_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select Layout.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permission To Use (file upload, optional) -->
                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label class="form-label">Permission To Use <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="enclosure_permission_to_use" id="enclosure_permission_to_use" accept=".png,.jpg,.jpeg,.gif,
                                                .pdf">
                                                <input type="hidden" id="enclosure_permission_to_use_doc" name="enclosure_permission_to_use_doc" />
                                                <a href="#" data-remove="enclosure_permission_to_use" id="enclosure_permission_to_use_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFilePermissionToUse(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="enclosure_permission_to_use_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select Permission To Use.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label">Validity <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control date-picker" id="enclosure_validity" name="enclosure_validity" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Validity 
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
                @if(hasAccess("enclosure","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/enclosure.js?ver='.getJsVersion()) }}"></script>
@endpush
