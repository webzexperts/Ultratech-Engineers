<div class="modal fade" id="DPTChemicalDetailsModal" aria-labelledby="DPTChemicalDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="DPTChemicalDetailsModalLabel">DPT Chemical Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="DPTChemicalDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="chem_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="chem_form_index" />
                    <input type="hidden" name="row_index" id="chem_row_index" />  
                    <input type="hidden" name="test_report_dpt_chemical_details_id" id="test_report_dpt_chemical_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- Chemical Select -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_dpt_id" class="form-label">Chemical <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="detail_dpt_id" id="detail_dpt_id" required style="width: 100%;">
                                        <option value="">Select Chemical</option>
                                        @forelse(getDptChemicals() as $dc)
                                            <option value="{{ $dc->dpt_id }}" 
                                                    data-dpt_chemical="{{ $dc->dpt_chemical }}"
                                                    data-designation="{{ $dc->dpt_designation }}"
                                                    data-make="{{ $dc->dpt_make }}" 
                                                    data-batch_no="{{ $dc->dpt_batch_no }}" 
                                                    data-expiry_date="{{ $dc->dpt_expiry_date ? \Carbon\Carbon::parse($dc->dpt_expiry_date)->format('d/m/Y') : '' }}">
                                                {{ $dc->name_for_display }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Chemical.</div>
                                </div>
                            </div>
                            <!-- Designation -->
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="chem_designation" class="form-label">Designation</label></div>
                                <div class="col-8">
                                    <input type="text" name="chemical_designation" id="chem_designation" class="form-control" readonly tabindex="-1">
                                </div>
                            </div> -->
                            <!-- Make -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="chem_make" class="form-label">Make</label></div>
                                <div class="col-8">
                                    <input type="text" name="make" id="chem_make" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Batch No -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="chem_batch_no" class="form-label">Batch No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="batch_no" id="chem_batch_no" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Expiry Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="chem_expiry_date" class="form-label">Expiry Date</label></div>
                                <div class="col-8">
                                    <input type="text" name="expiry_date" id="chem_expiry_date" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitChemicalRowBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
