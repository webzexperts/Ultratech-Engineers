<div class="modal fade" id="MPTMaterialDetailsModal" aria-labelledby="MPTMaterialDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="MPTMaterialDetailsModalLabel">MPT Material Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="MPTMaterialDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="mat_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="mat_form_index" />
                    <input type="hidden" name="row_index" id="mat_row_index" />  
                    <input type="hidden" name="test_report_mpt_material_details_id" id="test_report_mpt_material_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- Material Select -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_mm_id" class="form-label">Material <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="detail_mm_id" id="detail_mm_id" required style="width: 100%;">
                                        <option value="">Select Material</option>
                                        @forelse(getMaterialMpt() as $mat)
                                            <option value="{{ $mat->mm_id }}" 
                                                    data-material_name="{{ $mat->mm_material }}"
                                                    data-batch_no="{{ $mat->mm_batch_no }}" 
                                                    data-make="{{ $mat->mm_material_make }}" 
                                                    data-expiry_date="{{ $mat->mm_expiry_date ? \Carbon\Carbon::parse($mat->mm_expiry_date)->format('d/m/Y') : '' }}">
                                                {{ $mat->name_for_display ?? $mat->mm_material }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Material.</div>
                                </div>
                            </div>
                            <!-- Batch No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="mat_batch_no" class="form-label">Batch No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="batch_no" id="mat_batch_no" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Make -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="mat_make" class="form-label">Make</label></div>
                                <div class="col-8">
                                    <input type="text" name="make" id="mat_make" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Expiry Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="mat_expiry_date" class="form-label">Expiry Date</label></div>
                                <div class="col-8">
                                    <input type="text" name="expiry_date" id="mat_expiry_date" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitMaterialRowBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
