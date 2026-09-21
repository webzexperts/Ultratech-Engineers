<div class="modal fade" id="OfferModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="OfferModalLabel">Offer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonOfferForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="old_offer_date" id="old_offer_date">

                    <div class="row">
                        <div class="row g-1">
                            <div class="row mb-1">
                                <div class="col-2 d-flex flex-wrap gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="nabl_type_fix" id="nabl_no" value="Non NABL" required>
                                        <label class="form-check-label" for="nabl_no">Non NABL</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="nabl_type_fix" id="nabl_yes" value="NABL" required>
                                        <label class="form-check-label" for="nabl_yes">NABL</label>
                                    </div>
                                </div>
                                <div class="col-2 d-flex flex-wrap gap-2 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="test_at_fix" id="test_at_lab" value="At Lab" required>
                                        <label class="form-check-label" for="test_at_lab">At Lab</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="test_at_fix" id="test_at_site" value="At Site" required>
                                        <label class="form-check-label" for="test_at_site">At Site</label>
                                    </div>
                                </div>
                                <div class="col-2 d-flex flex-wrap gap-2 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_casting" value="Non-Welding" required>
                                        <label class="form-check-label" for="job_type_casting">Non-Welding</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_welding" value="Welding" required>
                                        <label class="form-check-label" for="job_type_welding">Welding</label>
                                    </div>
                                </div>
                            </div>
                            {{-- ============ Column 1 ============ --}}
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="offer_sequence" class="form-label">Offer No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey" id="offer_sequence" name="offer_sequence" style="max-width:60px" autofocus required>
                                            <input type="text" class="form-control skip-tab" id="offer_no" name="offer_no" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter Offer No.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="offer_date" class="form-label">Offer Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="offer_date" name="offer_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">Enter Offer Date.</div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
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
                            </div>

                            {{-- ============ Column 2 ============ --}}
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">DC No. & Date <sup class="astric">*</sup></label></div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="dc_no" name="dc_no" maxlength="100" required>
                                        <div class="invalid-tooltip">Enter DC No.</div>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control date-picker" id="dc_date" name="dc_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">Enter DC Date.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">PO No. & Date</label></div>
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="po_no" name="po_no" maxlength="100">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control date-picker" id="po_date" name="po_date" autocomplete="off"/>
                                    </div>
                                </div>
                            </div>

                            {{-- ============ Column 3 ============ --}}
                            <div class="col-md-4">
                            </div>
                        </div>

                        <div class="row PIselected"> 
                          <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary" data-bs-target="#OfferDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Offer Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="OfferDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th class="action_col">Actions</th>
                                                        <th>Type of Test</th>
                                                        <th>Nature</th>
                                                        <th>Type of Job</th>
                                                        <th>Job Description</th>
                                                        <th>Part No.</th>
                                                        <th>Drg No.</th>
                                                        <th>Material</th>
                                                        <th>Heat No.</th>
                                                        <th>RT No.</th>
                                                        <th>Product Code</th>
                                                        <th>Thickness(mm)</th>
                                                        <th>Area of Coverage</th>
                                                        <th>Procedure Ref.</th>
                                                        <th>Evaluation as Per</th>
                                                        <th>Acceptance Std.</th>
                                                        <th>Quantity</th>
                                                        <th>Approx. Value</th>
                                                        <th>Approx. Wt.</th>
                                                        <th>Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="20" id="noDetails" class="text-center">No Offer Details Added</td>
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
                                    <div class="col-4"><label class="form-label">Sample Drawn By</label></div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="sample_drawn_by" name="sample_drawn_by" maxlength="100">
                                        <div class="invalid-tooltip">Enter Sample Drawn By.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Is any TPI Witness ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="is_any_tpi_witness" id="is_any_tpi_witness">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Is any TPI Witness.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">TPI Name <sup class="astric" id="tpi_name_astric" style="display:none;">*</sup></label></div>
                                    <div class="col-8">
                                        {{-- enabled only when Is any TPI Witness = Yes (handled in JS) --}}
                                        <input type="text" class="form-control skip-tab" id="tpi_name" name="tpi_name" maxlength="100" readonly style="pointer-events: none;" tabindex="-1">
                                        <div class="invalid-tooltip">Enter TPI Name.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Condition of Sample</label></div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="condition_of_sample" name="condition_of_sample" maxlength="100">
                                        <div class="invalid-tooltip">Enter Condition of Sample.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Is Equipment Available ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="is_equipment_available" id="is_equipment_available">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Is Equipment Available.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Competent Personnel Available</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="competent_personnel_available" id="competent_personnel_available">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Competent Personnel Available.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Is the Test Sub Contracted ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="is_test_sub_contracted" id="is_test_sub_contracted">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Is the Test Sub Contracted.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Test Feasible ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="test_feasible" id="test_feasible">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Test Feasible.</div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">All Test Parameters in Accredited Scope ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="all_test_parameters_are_in_accredited_scope" id="all_test_parameters_are_in_accredited_scope">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select an option.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Required Statement of Conformity ?</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="required_statement_of_conformity" id="required_statement_of_conformity">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                        <div class="invalid-tooltip">Select an option.</div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start"><label class="form-label">Additional Requirement from Customer</label></div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="additional_requirement_from_customer" name="additional_requirement_from_customer" rows="3" maxlength="8000"></textarea>
                                        <div class="invalid-tooltip">Enter Additional Requirement from Customer.</div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start"><label class="form-label">Special Note</label></div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="special_note" name="special_note" rows="3" maxlength="8000"></textarea>
                                        <div class="invalid-tooltip">Enter Special Note.</div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"><label class="form-label">Prepared By</label></div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly>
                                            @forelse(getUsers() as $users)
                                                <option value="{{ $users->id }}" {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                {{-- @if(hasAccess("offer","print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif --}}
                @if(hasAccess("offer","add"))
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
<script src="{{ URL::asset('views/js/offer.js?ver='.getJsVersion()) }}"></script>
@endpush
