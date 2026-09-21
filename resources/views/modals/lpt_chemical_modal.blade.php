<div class="modal fade" id="LPTChemicalModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="LPTChemicalModalLabel">Chemical - LPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonLPTChemicalForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="lpt_grnd_id" id="lpt_grnd_id">
                    <div class="row">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Inward Type <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="lpt_inward_type" id="lpt_inward_type" required>
                                            <option value="Manual">Manual</option>
                                            <option value="From GRN">From GRN</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Inward Type.
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingForGRNModal" id="pending_btn" disabled>Pending</button>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">LPT Chemical <sup class="astric" id="lptchemicalAsterisk" style="display:none">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="lpt_chemical_id" id="lpt_chemical_id" required>
                                            <option value="">Select LPT Chemical</option>
                                            @forelse(getLPTChemicalItems() as $lpt_chemical_type_item)
                                                <option value="{{ $lpt_chemical_type_item->id }}" data-item_name="{{ $lpt_chemical_type_item->item_name }}" data-item_type="{{ $lpt_chemical_type_item->item_type }}">{{ $lpt_chemical_type_item->item_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select LPT Chemical.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Chemical <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_chemical_name" name="lpt_chemical_name" required>
                                        <div class="invalid-tooltip">
                                            Enter Chemical.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Designation <sup class="astric">*</sup></label>
                                    <div class="gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_designation" name="lpt_designation" required onkeyup="suggestDesignation(event,this)">
                                        <div id="lpt_designation_list" class="suggestion_list"></div>
                                        <input type="hidden" name="lpt_designation_suggesion" id="lpt_designation_suggesion">
                                        <div class="invalid-tooltip">
                                            Enter Designation.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Make <sup class="astric">*</sup></label>
                                    <div class="gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_make" name="lpt_make" required onkeyup="suggestMake(event,this)">
                                        <div id="lpt_make_list" class="suggestion_list"></div>
                                        <input type="hidden" name="lpt_make_suggesion" id="lpt_make_suggesion">
                                        <div class="invalid-tooltip">
                                            Enter Make.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Batch No. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_batch_no" name="lpt_batch_no" required>
                                        <div class="invalid-tooltip">
                                            Enter Batch No.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Identification No. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_identification_no" name="lpt_identification_no" required>
                                        <div class="invalid-tooltip">
                                            Enter Identification No.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Mfg Date. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control date-picker" id="lpt_mfg_date" name="lpt_mfg_date" required>
                                        <div class="invalid-tooltip">
                                            Enter Mfg Date.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Status </label>
                                    <div class="d-flex gap-2">
                                        <select class="js-example-basic-single form-select" name="lpt_status" id="lpt_status">
                                            <option value="Active">Active</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Document Ref. No. </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="lpt_document_ref_no" name="lpt_document_ref_no">
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Validity Date </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control date-picker" id="lpt_validity_date" name="lpt_validity_date" autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Remark </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <textarea class="form-control" name="lpt_remark" id="lpt_remark"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">GRN No. </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="grn_number" name="grn_number" tabindex="-1" readonly>
                                        <input type="text" class="form-control trans-date-picker" id="grn_date" name="grn_date" tabindex="-1" readonly autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Supplier </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" tabindex="-1" readonly>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Challan No. </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="challan_number" name="challan_number" tabindex="-1" readonly>
                                        <input type="text" class="form-control trans-date-picker" id="challan_date" name="challan_date" tabindex="-1" readonly autocomplete="off"/>
                                    </div>
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
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/lpt_chemical.js?ver='.getJsVersion()) }}"></script>
@endpush