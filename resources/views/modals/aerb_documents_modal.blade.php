<div class="modal fade" id="AerbDocumentModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="AerbDocumentModalLabel">AERB Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonAerbDocumentForm" class="row g-3 needs-validation" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <!-- AERB Document Name -->
                                <div class="row g-2 mb-2 position-relative">
                                    <div class="col-4">
                                        <label for="aerb_documents_name" class="form-label">AERB Document Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="aerb_documents_name" id="aerb_documents_name" class="form-control" maxlength="100" required>
                                        <div class="invalid-tooltip">
                                            Enter AERB Document Name.
                                        </div>
                                    </div>
                                </div>

                                <!-- AERB Document Upload (file upload, optional) -->
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label class="form-label">AERB Document Upload</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="aerb_documents_upload" id="aerb_documents_upload" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="aerb_documents_upload_doc" name="aerb_documents_upload_doc" />
                                                <a href="#" data-remove="aerb_documents_upload" id="aerb_documents_upload_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileAerbDocument(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="aerb_documents_upload_prev">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Remark -->
                                <div class="row g-2 mb-2">
                                    <div class="col-4 justify-content-start">
                                        <label for="remark" class="form-label">Remark</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea name="remark" id="remark" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("aerb_documents","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/aerb_documents.js?ver='.getJsVersion()) }}"></script>
@endpush
