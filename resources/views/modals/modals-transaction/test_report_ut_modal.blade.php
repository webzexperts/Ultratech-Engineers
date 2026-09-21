<div class="modal fade" id="TestReportUtModal" aria-labelledby="TestReportUtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="TestReportUtModalLabel">Test Report (UT)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Main Form -->
                <form id="commonTestReportUtForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="material_inward_details_id" id="material_inward_details_id">
                    <input type="hidden" name="observation_sheet_details_id" id="observation_sheet_details_id">
                    <input type="hidden" name="from_type_id_fix" id="from_type_id_fix">

                    <!-- Top Fields: 3-Column Layout -->
                    <div class="row mb-1 g-2">
                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <!-- Report No -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_report_sequence" class="form-label">Report No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control isNumberKey" id="test_report_sequence" name="test_report_sequence" style="max-width:80px;" autofocus required>
                                        <input type="text" class="form-control skip-tab" id="test_report_no" name="test_report_no" tabindex="-1" readonly>
                                        <div class="invalid-tooltip">Enter Report No.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Report Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_report_date" class="form-label">Report Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control trans-date-picker" id="test_report_date" name="test_report_date" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter Report Date.</div>
                                </div>
                            </div>
                            <!-- Customer -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="customer_id" class="form-label">Customer <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <select class="js-example-basic-single" name="customer_id" id="customer_id" required style="width: 100%;">
                                            <option value="">Select Customer</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- NABL / Non NABL & Casting / Welding Radios in one line -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">                                     
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap">
                                        <div class="d-flex gap-3 align-items-center" style="pointer-events: none;">
                                            <div class="form-check">
                                                <input class="form-check-input skip-tab" type="radio" name="nabl_type_fix" id="nabl_non_nabl" value="Non NABL" checked tabindex="-1" onclick="return false;">
                                                <label class="form-check-label" for="nabl_non_nabl">Non NABL</label>
                                            </div>
                                            <div class="form-check me-3">
                                                <input class="form-check-input skip-tab" type="radio" name="nabl_type_fix" id="nabl_nabl" value="NABL" tabindex="-1" onclick="return false;">
                                                <label class="form-check-label" for="nabl_nabl">NABL</label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingInwardForUtModal" id="pending_btn" disabled>Pending Inward</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap" style="pointer-events: none;">
                                        <div class="form-check">
                                            <input class="form-check-input skip-tab" type="radio" name="job_type_fix" id="job_casting" value="Non-Welding" checked tabindex="-1" onclick="return false;">
                                            <label class="form-check-label" for="job_casting">Non-Welding</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input skip-tab" type="radio" name="job_type_fix" id="job_welding" value="Welding" tabindex="-1" onclick="return false;">
                                            <label class="form-check-label" for="job_welding">Welding</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer's Client -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="customer_client" class="form-label">Customer's Client</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="customer_client" name="customer_client" oninput="suggestCustomerClient(event, this)" autocomplete="off">
                                    <div id="customer_client_list"></div>
                                    <input type="hidden" name="customer_client_suggestion" id="customer_client_suggestion">
                                </div>
                            </div>
                            <!-- Type of Job -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="type_of_job_id" class="form-label">Type of Job <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <select class="js-example-basic-single" name="type_of_job_id" id="type_of_job_id" required style="width: 100%;">
                                            <option value="">Select Type of Job</option>
                                            @forelse(getTypeOfJob() as $tj)
                                                <option value="{{ $tj->id }}">{{ $tj->type_of_job }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Type of Job.</div>
                                        <button type="button" class="btn btn-success btn-sm ms-1" id="copy_report_btn">Copy</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Job Description -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="job_desc_id" class="form-label">Job Description <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="job_desc_id" id="job_desc_id" required style="width: 100%;">
                                        <option value="">Select Job Description</option>
                                        @forelse(getJobDescription() as $jd)
                                            <option value="{{ $jd->id }}">{{ $jd->job_description }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Job Description.</div>
                                </div>
                            </div>
                            <!-- Part No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="part_no" class="form-label">Part No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="part_no" name="part_no" oninput="suggestPartNo(event, this)" autocomplete="off">
                                    <div id="part_no_list"></div>
                                    <input type="hidden" name="part_no_suggestion" id="part_no_suggestion">
                                </div>
                            </div>
                            <!-- Drg. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="drg_no" class="form-label">Drg. No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="drg_no" name="drg_no" oninput="suggestDrgNo(event, this)" autocomplete="off">
                                    <div id="drg_no_list"></div>
                                    <input type="hidden" name="drg_no_suggestion" id="drg_no_suggestion">
                                </div>
                            </div>
                            <!-- Material -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="material_id" class="form-label">Material <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="material_id" id="material_id" required style="width: 100%;">
                                        <option value="">Select Material</option>
                                        @forelse(getMaterial() as $m)
                                            <option value="{{ $m->id }}">{{ $m->material }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Material.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <!-- Heat No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="heat_no" class="form-label">Heat No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="heat_no" name="heat_no">
                                </div>
                            </div>
                            <!-- Product Code -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="product_code" class="form-label">Product Code</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="product_code" name="product_code" oninput="suggestProductCode(event, this)" autocomplete="off">
                                    <div id="product_code_list"></div>
                                    <input type="hidden" name="product_code_suggestion" id="product_code_suggestion">
                                </div>
                            </div>
                            <!-- DC No. & Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="dc_no" class="form-label">DC No. & Date</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control skip-tab" id="dc_no" name="dc_no" readonly>
                                        <input type="text" class="form-control skip-tab" id="dc_date" name="dc_date" readonly tabindex="-1">
                                    </div>
                                </div>
                            </div>
                            <!-- PO No. & Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="po_no" class="form-label">PO No. & Date</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control skip-tab" id="po_no" name="po_no" readonly>
                                        <input type="text" class="form-control skip-tab" id="po_date" name="po_date" readonly tabindex="-1">
                                    </div>
                                </div>
                            </div>
                            <!-- Date of Receipt -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="date_of_receipt" class="form-label">Date of Receipt</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="date_of_receipt" name="date_of_receipt" readonly>
                                </div>
                            </div>
                            <!-- Date of Testing -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="date_of_testing" class="form-label">Date of Testing <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control date-picker" id="date_of_testing" name="date_of_testing" style="max-width:100px;" required>
                                        <input type="text" class="form-control" id="date_of_testing_value" name="date_of_testing_value">
                                        <div class="invalid-tooltip">Enter Date of Testing.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Test Carried out at -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="test_carried_out_at" class="form-label">Test Carried Out At <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" id="test_carried_out_at" name="test_carried_out_at" oninput="suggestTestCarriedOutAt(event, this)" autocomplete="off" required>
                                    <div id="test_carried_out_at_list"></div>
                                    <input type="hidden" name="test_carried_out_at_suggestion" id="test_carried_out_at_suggestion">
                                    <div class="invalid-tooltip">Enter Test Carried Out At.</div>
                                </div>
                            </div>
                            <!-- Amendment No. & Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amendment_no" class="form-label">Amendment No.</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                    <div class="w-50 position-relative">
                                        <input type="text" class="form-control" id="amendment_no" name="amendment_no">
                                        <div class="invalid-tooltip">Enter Amendment No.</div>
                                    </div>
                                    <div class="w-50 position-relative">
                                        <input type="text" class="form-control date-picker" id="amendment_date" name="amendment_date" >
                                        <div class="invalid-tooltip">Enter Amendment Date.</div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Amendment Reason -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amendment_reason" class="form-label">Amendment Reason</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="amendment_reason" name="amendment_reason">
                                </div>
                            </div>
                            <!-- Stage of Test -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="stage_of_test" class="form-label">Stage of Test</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="stage_of_test" name="stage_of_test">
                                </div>
                            </div>
                            <!-- Area of Coverage -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="area_of_coverage_id" class="form-label">Area of Coverage <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="area_of_coverage_id" id="area_of_coverage_id" required style="width: 100%;">
                                        <option value="">Select Area of Coverage</option>
                                        @forelse(getAreaOfCoverage() as $ac)
                                            <option value="{{ $ac->area_of_coverage_id }}">{{ $ac->area_of_coverage }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Area of Coverage.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="col-md-4">
                            <!-- Surface Condition -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="surface_condition" class="form-label">Surface Condition</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="surface_condition" name="surface_condition" oninput="suggestSurfaceCondition(event, this)" autocomplete="off">
                                    <div id="surface_condition_list"></div>
                                    <input type="hidden" name="surface_condition_suggestion" id="surface_condition_suggestion">
                                </div>
                            </div>
                            <!-- Surface Temp -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="surface_temp" class="form-label">Surface Temp.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="surface_temp" name="surface_temp" oninput="suggestSurfaceTemp(event, this)" autocomplete="off">
                                    <div id="surface_temp_list"></div>
                                    <input type="hidden" name="surface_temp_suggestion" id="surface_temp_suggestion">
                                </div>
                            </div>
                            <!-- Thickness -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="thickness" class="form-label">Thickness</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="thickness" name="thickness" oninput="suggestThickness(event, this)" autocomplete="off">
                                    <div id="thickness_list"></div>
                                    <input type="hidden" name="thickness_suggestion" id="thickness_suggestion">
                                </div>
                            </div>
                            <!-- Couplant -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="couplant" class="form-label">Couplant</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="couplant" name="couplant" oninput="suggestCouplant(event, this)" autocomplete="off">
                                    <div id="couplant_list"></div>
                                    <input type="hidden" name="couplant_suggestion" id="couplant_suggestion">
                                </div>
                            </div>
                            <!-- Test Technique -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="test_technique" class="form-label">Test Technique</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="test_technique" name="test_technique" oninput="suggestTestTechnique(event, this)" autocomplete="off">
                                    <div id="test_technique_list"></div>
                                    <input type="hidden" name="test_technique_suggestion" id="test_technique_suggestion">
                                </div>
                            </div>
                            <!-- Ref Block Used -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="ref_block_used" class="form-label">Ref. Block Used</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="ref_block_used" name="ref_block_used" oninput="suggestRefBlockUsed(event, this)" autocomplete="off">
                                    <div id="ref_block_used_list"></div>
                                    <input type="hidden" name="ref_block_used_suggestion" id="ref_block_used_suggestion">
                                </div>
                            </div>
                            <!-- Scanning Area -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="scanning_area" class="form-label">Scanning Area</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="scanning_area" name="scanning_area" oninput="suggestScanningArea(event, this)" autocomplete="off">
                                    <div id="scanning_area_list"></div>
                                    <input type="hidden" name="scanning_area_suggestion" id="scanning_area_suggestion">
                                </div>
                            </div>
                            <!-- Scan Plan No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="scan_plan_no" class="form-label">Scan Plan No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="scan_plan_no" name="scan_plan_no" oninput="suggestScanPlanNo(event, this)" autocomplete="off">
                                    <div id="scan_plan_no_list"></div>
                                    <input type="hidden" name="scan_plan_no_suggestion" id="scan_plan_no_suggestion">
                                </div>
                            </div>
                            <!-- Procedure Reference -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="procedure_ref_id" class="form-label">Procedure Reference <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="procedure_ref_id" id="procedure_ref_id" required style="width: 100%;">
                                        <option value="">Select Procedure Reference</option>
                                        @forelse(getProcedureReferance() as $pr)
                                            <option value="{{ $pr->procedure_reference_id }}">{{ $pr->procedure_reference }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Procedure Reference.</div>
                                </div>
                            </div>
                            <!-- Acceptance Standard -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="acceptance_standard_id" class="form-label">Acceptance Standard <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="acceptance_standard_id" id="acceptance_standard_id" required style="width: 100%;">
                                        <option value="">Select Acceptance Standard</option>
                                        @forelse(getAcceptanceStandards() as $as)
                                            <option value="{{ $as->id }}">{{ $as->acceptance_standard }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Acceptance Standard.</div>
                                </div>
                            </div>
                            <!-- Defectogram Image Upload -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="defectogram_image" class="form-label">Defectogram Sketch</label>
                                </div>
                                <div class="col-8">
                                    <div class="input-append fileupload position-relative">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="file" name="defectogram_image" id="defectogram_image" class="form-control" accept=".png,.jpg,.jpeg,.gif">
                                            <input type="hidden" name="defectogram_image_doc" id="defectogram_image_doc">
                                            <a href="#" data-remove="defectogram_image" id="defectogram_image_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileTestReportUt(event)">Remove</a>
                                            <a target="_blank" class="btn img-prev hide" id="defectogram_image_prev">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout for grids -->
                    <div class="row mt-1">
                        <!-- Grid 1: UT Equipment Details -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow-none border-0 mb-1">
                                <div class="card-header align-items-center d-flex p-0 pb-1 bg-transparent border-0">
                                    <div class="flex-shrink-0 me-2">
                                        <button type="button" class="btn btn-success btn-sm" id="addEquipmentRowBtn">Add</button>
                                    </div>
                                    <h5 class="card-title mb-0 flex-grow-1"><b>UT Equipment Details</b></h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="EquipmentDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_col">Actions</th>
                                                    <th>Equipment</th>
                                                    <th>Make</th>
                                                    <th>Display</th>
                                                    <th>Sr. No.</th>
                                                    <th>Cali. Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dyn Rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid 2: UT Probe Details -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow-none border-0 mb-1">
                                <div class="card-header align-items-center d-flex p-0 pb-1 bg-transparent border-0">
                                    <div class="flex-shrink-0 me-2">
                                        <button type="button" class="btn btn-success btn-sm" id="addProbeRowBtn">Add</button>
                                    </div>
                                    <h5 class="card-title mb-0 flex-grow-1"><b>UT Probe Details</b></h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="ProbeDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_col">Actions</th>
                                                    <th>Probe</th>
                                                    <th>Probe Sr. No.</th>
                                                    <th>Size of Probe</th>
                                                    <th>Ref. Angle</th>
                                                    <th>Frequency</th>
                                                    <th>Cal. Range</th>
                                                    <th>Ref. Gain</th>
                                                    <th>Scanning dB</th>
                                                    <th>Transfer Corr. Gain</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dyn Rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom part of form -->
                    <div class="row mt-1">
                        <!-- Grid 3: Test Report Details -->
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow-none border-0 mb-1">
                                <div class="card-header align-items-center d-flex p-0 pb-1 bg-transparent border-0">
                                    <div class="flex-shrink-0 me-2">
                                        <button type="button" class="btn btn-success btn-sm" id="addDetailRowBtn">Add</button>
                                    </div>
                                    <h5 class="card-title mb-0 flex-grow-1"><b>Test Report Details</b></h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="ReportDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_col">Actions</th>
                                                    <th>Sr. No.</th>
                                                    <th>UT Test No.</th>
                                                    <th>Heat No.</th>
                                                    <th>Quantity</th>
                                                    <th>Discontinuity Evaluation</th>
                                                    <th>Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dyn Rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom controls (NABL / PDF Upload / Operators / Special Note) -->
                    <div class="row mt-2 g-2">
                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <!-- ULR Conf. -->
                            <div class="row g-2 mb-1" style="display:none;">
                                <div class="col-3">
                                    <label for="ulr_id" class="form-label">ULR <span class="astric_ulr"></span></label>
                                </div>
                                <div class="col-9 position-relative">
                                    <select class="js-example-basic-single skip-tab" name="ulr_id" id="ulr_id" style="width: 100%;">
                                        <option value="">Select ULR</option>
                                        @forelse(getULRConf() as $ulr)
                                            <option value="{{ $ulr->id }}">{{ $ulr->ulr }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select ULR.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1 ulrDesign">
                                <div class="col-3">
                                    <label for="ulr_sequence" class="form-label">ULR <span class="astric_ulr_seq"></span></label>
                                </div>
                                <div class="col-9 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control" id="ulr_sequence" name="ulr_sequence" readonly tabindex="-1" style="max-width:80px;">
                                        <div class="invalid-tooltip">Enter ULR </div>
                                        <input type="text" class="form-control skip-tab" id="ulr_no" name="ulr_no" readonly>
                                        <input type="hidden" name="ulr_year" id="ulr_year">
                                    </div>
                                </div>
                            </div>

                            <!-- Tested By -->
                            <div class="row g-2 mb-1">
                                <div class="col-3">
                                    <label for="tested_by_authority_person_id" class="form-label">Tested By</label>
                                </div>
                                <div class="col-9 position-relative">
                                    <select class="js-example-basic-single" name="tested_by_authority_person_id" id="tested_by_authority_person_id" style="width: 100%;">
                                        <option value="">Select Tested By</option>
                                        @forelse(getAuthorityPersonsTestBy() as $ap)
                                            <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Tested By.</div>
                                </div>
                            </div>

                            <!-- Reviewed By -->
                            <div class="row g-2 mb-1">
                                <div class="col-3">
                                    <label for="reviewed_by_authority_person_id" class="form-label">Reviewed By</label>
                                </div>
                                <div class="col-9">
                                    <select class="js-example-basic-single" name="reviewed_by_authority_person_id" id="reviewed_by_authority_person_id" style="width: 100%;">
                                        <option value="">Select Reviewed By</option>
                                        @forelse(getAuthorityPersonsReviewedBy() as $ap)
                                            <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <!-- Authorized By -->
                            <div class="row g-2 mb-1">
                                <div class="col-3">
                                    <label for="authorized_by_authority_person_id" class="form-label">Authorized By</label>
                                </div>
                                <div class="col-9">
                                    <select class="js-example-basic-single" name="authorized_by_authority_person_id" id="authorized_by_authority_person_id" style="width: 100%;">
                                        <option value="">Select Authorized By</option>
                                        @forelse(getAuthorityPersonsAuthorizedBy() as $ap)
                                            <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Qty & Notes -->
                            <div class="row g-2 mb-1">
                                <div class="col-3">
                                    <label for="pend_qty" class="form-label">Pending Qty.</label>
                                </div>
                                <div class="col-9">
                                    <input type="text" class="form-control skip-tab" id="pend_qty" name="pend_qty" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-3">
                                    <label for="total_qty" class="form-label">Total Qty. </label>
                                </div>
                                <div class="col-9">
                                    <input type="number" class="form-control skip-tab" id="total_qty" name="total_qty" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row g-2 mb-1 align-items-start">
                                <div class="col-3">
                                    <label for="sp_note" class="form-label mt-1">Special Note</label>
                                </div>
                                <div class="col-9">
                                    <textarea class="form-control" name="sp_note" id="sp_note" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="row g-2 align-items-start">
                                <div class="col-3">
                                    <label for="note" class="form-label mt-1"> NABL Report Disclaimer</label>
                                </div>
                                <div class="col-9">
                                    <textarea class="form-control" name="note" id="note" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("test_report_ut", "print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("test_report_ut", "add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

@include('modals.modals-details.test_report_ut_equipment_details_modal')
@include('modals.modals-details.test_report_ut_probe_details_modal')
@include('modals.modals-details.test_report_ut_details_modal')
@include('modals.modals-pending.pending_inward_for_ut_modal')
@include('modals.modals-pending.copy_report_ut_modal')

@push('script-modal')
<script>
    var loginUserId = {{ auth()->id() }};
    var checkFileRoute = "{{ route('check-file_exists') }}";
</script>
<script src="{{ URL::asset('views/js/test_report_ut.js?ver='.getJsVersion()) }}"></script>
@endpush
