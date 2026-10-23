<div class="modal fade" id="MaterialInwardModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="MaterialInwardModalLabel">Material Inward</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonMaterialInwardForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="old_inward_date" id="old_inward_date">
                    <input type="hidden" name="inward_type_value_fix" id="inward_type_value_fix" value="Manual">
                    <input type="hidden" name="form_type_value_fix" id="form_type_value_fix" value="Manual">
                    <input type="hidden" name="offer_id" id="offer_id" value="">

                    <div class="row">
                        <div class="row g-1">
                            <div class="row mb-1 align-items-center">
                                <div class="col-auto d-flex flex-nowrap align-items-center gap-3 mt-1">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="nabl_type_fix" id="nabl_no" value="Non NABL" required>
                                        <label class="form-check-label" for="nabl_no">Non NABL</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="nabl_type_fix" id="nabl_yes" value="NABL" required>
                                        <label class="form-check-label" for="nabl_yes">NABL</label>
                                    </div>
                                </div>
                                <div class="col-auto d-flex flex-nowrap align-items-center gap-3 mt-1 ms-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_casting" value="Casting" required>
                                        <label class="form-check-label" for="job_type_casting">Casting</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_fabrication" value="Fabrication" required>
                                        <label class="form-check-label" for="job_type_fabrication">Fabrication</label>
                                    </div>
                                </div>
                                <div class="col-auto d-flex flex-nowrap align-items-center gap-3 mt-1 ms-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="test_at_fix" id="test_at_inhouse" value="Inhouse" required>
                                        <label class="form-check-label" for="test_at_inhouse">Inhouse</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="radio" name="test_at_fix" id="test_at_customer_location" value="Customer Location" required>
                                        <label class="form-check-label text-nowrap mb-0" for="test_at_customer_location">Customer Location</label>
                                    </div>
                                </div>
                            </div>

                            {{-- ============ Column 1 ============ --}}
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="material_inward_sequence" class="form-label">Inward No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey" id="material_inward_sequence" name="material_inward_sequence" style="max-width:60px" required>
                                            <input type="text" class="form-control skip-tab" id="material_inward_no" name="material_inward_no" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter Inward No.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="material_inward_date" class="form-label">Inward Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="material_inward_date" name="material_inward_date" required autocomplete="off" />
                                        <div class="invalid-tooltip">Enter Inward Date.</div>
                                    </div>
                                </div>

                                
                            </div>

                            {{-- ============ Column 2 ============ --}}
                            <div class="col-md-4">
                                <div class="row g-2 mb-1 ml-2">
                                    <div class="col-4">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_customer_name" name="customer_id" id="customer_id" required>
                                                <option value="">Select Customer</option>
                                                @forelse(getCustomers() as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">Select Customer.</div>
                                            @if(hasAccess("customer","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedCustomer(true)" data-bs-target="#CustomerModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label" id="challan_label">Challan No. <sup class="astric" style="display:none;">*</sup></label></div>
                                    <div class="col-4 position-relative">
                                        <input type="text" class="form-control" id="challan_no" name="challan_no" maxlength="100">
                                        <div class="invalid-tooltip">Enter Challan No.</div>
                                    </div>
                                    <div class="col-4 position-relative">
                                        <input type="text" class="form-control date-picker" id="challan_date" name="challan_date" autocomplete="off" />
                                        <div class="invalid-tooltip">Enter Challan Date.</div>
                                    </div>
                                </div>

                                
                            </div>

                            {{-- ============ Column 3 ============ --}}
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label" id="offer_label">Offer No. <sup class="astric" style="display:none;">*</sup></label></div>
                                    <div class="col-4 position-relative">
                                        <input type="text" class="form-control" id="offer_no" name="offer_no" maxlength="100">
                                        <div class="invalid-tooltip">Enter Offer No.</div>
                                    </div>
                                    <div class="col-4 position-relative">
                                        <input type="text" class="form-control date-picker" id="offer_date" name="offer_date" autocomplete="off" />
                                        <div class="invalid-tooltip">Enter Offer Date.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Material Type <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="material_type_id" id="material_type_id" required>
                                            <option value="">Select Material Type</option>
                                            @forelse(getMaterialType() as $mt)
                                                <option value="{{ $mt->material_type_id }}">{{ $mt->material_type }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Material Type.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row PIselected"> 
                          <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary" data-bs-target="#MaterialInwardDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Material Inward Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="MaterialInwardDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th class="action_col">Actions</th>
                                                        <th>Sr. No.</th>
                                                        <th>Process Type</th>
                                                        <th>Process</th>
                                                        <th>Lab ID</th>
                                                        <th>Customer ID No.</th>
                                                        <th>Job Description</th>
                                                        <th>Die No.</th>
                                                        <th>Heat No.</th>
                                                        <th>Drg No.</th>
                                                        <th>Material</th>
                                                        <th>Thickness(mm)</th>
                                                        <th>Quantity</th>
                                                        <th>Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="14" id="noDetails" class="text-center">No Material Inward Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>

                        <div class="row g-1 mt-1">
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Total Qty.</label></div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab isNumberKey" id="total_qty" name="total_qty" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Test Method <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="test_method_id" id="test_method_id" required>
                                            <option value="">Select Test Method</option>
                                            @forelse(getTestMethod() as $tm)
                                                <option value="{{ $tm->test_method_id }}">{{ $tm->test_method }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Test Method.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label for="procedure_ref_id" class="form-label">Procedure Std. Ref. <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single mst_procedure_reference" name="procedure_ref_id" id="procedure_ref_id" required>
                                                <option value="">Select Procedure Std. Ref.</option>
                                                @forelse(getProcedureReferance() as $pr)
                                                    <option value="{{ $pr->procedure_reference_id }}">{{ $pr->procedure_reference }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">Select Procedure Std. Ref.</div>
                                            @if(hasAccess("procedure_reference","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedProcedureReference(true)" data-bs-target="#ProcedureReferenceModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label for="evaluation_as_per_id" class="form-label">Evaluation Followed <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single mst_evaluation_as_per" name="evaluation_as_per_id" id="evaluation_as_per_id" required>
                                                <option value="">Select Evaluation Followed</option>
                                                @forelse(getEvaluationAsPer() as $eap)
                                                    <option value="{{ $eap->evaluation_as_per_id }}">{{ $eap->evaluation_as_per }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">Select Evaluation Followed.</div>
                                            @if(hasAccess("evaluation_as_per","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedEvaluationAsPer(true)" data-bs-target="#EvaluationAsPerModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label for="acceptance_standard_id" class="form-label">Acceptance Std. <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_acceptance_standard" name="acceptance_standard_id" id="acceptance_standard_id" required>
                                                <option value="">Select Acceptance Std.</option>
                                                @forelse(getAcceptanceStandards() as $acs)
                                                    <option value="{{ $acs->id }}">{{ $acs->acceptance_standard }}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">Select Acceptance Std.</div>
                                            @if(hasAccess("acceptance_standard","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedAcceptanceStandard(true)" data-bs-target="#AcceptanceStandardModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label for="radiation_energy_fix" class="form-label">Radiation Energy <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="radiation_energy_fix" id="radiation_energy_fix" required>
                                            <option value="">Select Energy</option>
                                            <option value="Gamma Rays">Gamma Rays</option>
                                            <option value="X-Rays">X-Rays</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Radiation Energy.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Coverage Area </label></div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="coverage_area" name="coverage_area">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label for="procedure_submitted_fix" class="form-label">Procedure Submitted</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="procedure_submitted_fix" id="procedure_submitted_fix">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Procedure Submitted.</div>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Del. Date <sup class="astric">*</sup></label></div>
                                    <div class="col-8">
                                        <input type="text" class="form-control date-picker" id="del_date" name="del_date" maxlength="100" required autocomplete="off">
                                        <div class="invalid-tooltip">Enter Del. Date.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start d-flex flex-column align-items-start">
                                        <label class="form-label">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="special_note" name="special_note" rows="3" maxlength="8000"></textarea>
                                        <div class="invalid-tooltip">Enter Special Note.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("material_inward","print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif  
                @if(hasAccess("material_inward","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
<script>
    let loginUserId = {{ auth()->id() }};
    let checkFileRoute = "{{ route('check-file_exists') }}";
    window.locationNablDefault = "{{ getCurrentLocation()->location_nabl_applicable == 'Yes' ? 'NABL' : 'Non NABL' }}";
    window.locationNablTests = "{{ getCurrentLocation()->location_nabl_test ?? '' }}";
</script>
<script src="{{ URL::asset('views/js/material_inward.js?ver='.getJsVersion()) }}"></script>
@endpush
