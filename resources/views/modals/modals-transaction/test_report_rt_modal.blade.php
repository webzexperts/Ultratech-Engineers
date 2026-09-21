<div class="modal fade" id="TestReportRtModal" aria-labelledby="TestReportRtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="TestReportRtModalLabel">Test Report (RT)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Main Form -->
                <form id="commonTestReportRtForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="material_inward_details_id" id="material_inward_details_id">
                    <input type="hidden" name="observation_sheet_details_id" id="observation_sheet_details_id">
                    <input type="hidden" id="old_ir_camera_id">
                    <input type="hidden" id="old_co_camera_id">
                    <input type="hidden" id="old_xray_camera_id">
                    <input type="hidden" id="old_source_strength">
                    <input type="hidden" id="old_source_used">
                    <input type="hidden" id="old_source_size">
                    <input type="hidden" id="old_xray_kv_ma">
                    <input type="hidden" id="old_xray_focal_size">
                    <input type="hidden" id="old_testing_date">
                    <input type="hidden" name="test_report_rt_id" id="test_report_rt_id">
                    <input type="hidden" name="from_type_id_fix" id="from_type_id_fix">
                    <input type="hidden" name="revision_from_report_rt_id" id="revision_from_report_rt_id">
                    <input type="hidden" name="main_test_report_date" id="main_test_report_date">


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
                                        <input type="text" class="form-control skip-tab" id="revision_number" name="revision_number" style="max-width:65px;" tabindex="-1" readonly>
                                        <input type="hidden" id="revision_test_report_rt_id" name="revision_test_report_rt_id">
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
                                        <select class="js-example-basic-single" name="customer_id" id="customer_id" required>
                                            <option value="">Select Customer</option>
                                        </select>

                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label class="form-label"></label></div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap">
                                        <div class="d-flex gap-3 align-items-center">
                                            <div class="form-check me-2">
                                                <input class="form-check-input" type="radio" name="process_type" id="process_type_fresh" value="Fresh" checked>
                                                <label class="form-check-label" for="process_type_fresh">Fresh</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="process_type" id="process_type_repair" value="Repair">
                                                <label class="form-check-label" for="process_type_repair">Repair</label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingInwardForRtModal" id="pending_btn" disabled>Pending Inward</button>
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
                                        <!-- <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingInwardForRtModal" id="pending_btn" disabled>Pending Inward</button> -->
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
                                        <select class="js-example-basic-single" name="type_of_job_id" id="type_of_job_id" required>
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
                                    <select class="js-example-basic-single" name="job_desc_id" id="job_desc_id" required>
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
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="part_id" class="form-label">Part No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="part_id" id="part_id" required>
                                        <option value="">Select Part No.</option>
                                        @forelse(getparts() as $p)
                                            <option value="{{ $p->part_id }}" data-drg_no="{{ $p->drg_no }}">{{ $p->part_no }} - {{ $p->drg_no }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Part No.</div>
                                </div>
                            </div> -->

                            <!-- New Part No. Textbox with suggestion -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="part_no" class="form-label">Part No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="part_no" name="part_no" oninput="suggestPartNo(event, this)" autocomplete="off">
                                    <div id="part_no_list"></div>
                                    <input type="hidden" name="part_no_suggestion" id="part_no_suggestion">
                                    <div class="invalid-tooltip">Enter Part No.</div>
                                </div>
                            </div>

                            <!-- Drg. No. -->
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="drg_no" class="form-label">Drg. No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="drg_no" name="drg_no" tabindex="-1" readonly>
                                </div>
                            </div> -->

                            <!-- New Drg. No. Textbox with suggestion -->
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
                                    <select class="js-example-basic-single" name="material_id" id="material_id" required>
                                        <option value="">Select Material</option>
                                        @forelse(getMaterial() as $m)
                                        <option value="{{ $m->id }}">{{ $m->material }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Material.</div>
                                </div>
                            </div>
                            <!-- Heat No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="heat_no" class="form-label">Heat No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="heat_no" name="heat_no">
                                </div>
                            </div>
                            <!-- RT No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="rt_no" class="form-label">RT No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="rt_no" name="rt_no">
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
                                        <input type="text" class="form-control  skip-tab" id="dc_date" name="dc_date" readonly tabindex="-1">
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
                                        <input type="text" class="form-control  skip-tab" id="po_date" name="po_date" readonly tabindex="-1">
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
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">

                            

                            <!-- Test Carried out at -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_carried_out_at" class="form-label">Test Carried Out At</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="test_carried_out_at" name="test_carried_out_at" oninput="suggestTestCarriedOutAt(event, this)" autocomplete="off">
                                    <div id="test_carried_out_at_list"></div>
                                    <input type="hidden" name="test_carried_out_at_suggestion" id="test_carried_out_at_suggestion">
                                </div>
                            </div>

                            <!-- WorkSheet No. And Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="worksheet_no" class="form-label">Worksheet No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="worksheet_no" name="worksheet_no" autocomplete="off" readonly>
                                </div>
                            </div>

                            <!-- Amendment No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amendment_no" class="form-label">Amendment No.</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <div class="w-50 position-relative">
                                            <input type="text" class="form-control" id="amendment_no" name="amendment_no" readonly tabindex="-1">
                                            <div class="invalid-tooltip">Enter Amendment No.</div>
                                        </div>
                                        <div class="w-50 position-relative">
                                            <input type="text" class="form-control date-picker" id="amendment_date" name="amendment_date" readonly tabindex="-1">
                                            <div class="invalid-tooltip">Enter Date.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Amendment Date -->
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amendment_date" class="form-label">Amendment Date</label>
                                </div>
                                
                            </div> -->
                            <!-- Amendment Reason -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="amendment_reason" class="form-label">Amendment Reason</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="amendment_reason" name="amendment_reason" readonly tabindex="-1">
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
                                    <select class="js-example-basic-single" name="area_of_coverage_id" id="area_of_coverage_id" required>
                                        <option value="">Select Area of Coverage</option>
                                        @forelse(getAreaOfCoverage() as $ac)
                                        <option value="{{ $ac->area_of_coverage_id }}">{{ $ac->area_of_coverage }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Area of Coverage.</div>
                                </div>
                            </div>
                            <!-- RSS Reference -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="rss_reference" class="form-label">RSS Reference</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control" id="rss_reference" name="rss_reference">
                                        <input type="hidden" id="technique_sheet_rt_id" name="technique_sheet_rt_id">
                                        <input type="hidden" id="rss_no" name="rss_no">
                                        <button type="button" class="btn btn-success btn-sm ms-1" id="rss_report_btn">RSS</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Welding Process -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="welding_process" class="form-label">Welding Process</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="welding_process" name="welding_process" oninput="suggestWeldingProcess(event, this)" autocomplete="off">
                                    <div id="welding_process_list"></div>
                                    <input type="hidden" name="welding_process_suggestion" id="welding_process_suggestion">
                                </div>
                            </div>
                            <!-- Joint Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="joint_type" class="form-label">Joint Type</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="joint_type" name="joint_type" oninput="suggestJointType(event, this)" autocomplete="off">
                                    <div id="joint_type_list"></div>
                                    <input type="hidden" name="joint_type_suggestion" id="joint_type_suggestion">
                                </div>
                            </div>
                            <!-- Welder Name -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="welder_name" class="form-label">Welder Name</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="welder_name" name="welder_name" autocomplete="off">
                                    <!-- <div id="welder_name_list"></div>
                                    <input type="hidden" name="welder_name_suggestion" id="welder_name_suggestion"> -->
                                </div>
                            </div>
                            <!-- Welder ID -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="welder_id" class="form-label">Welder ID</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="welder_id" name="welder_id" autocomplete="off">
                                    <!-- <div id="welder_id_list"></div>
                                    <input type="hidden" name="welder_id_suggestion" id="welder_id_suggestion"> -->
                                </div>
                            </div>
                            <!-- Position -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="position" class="form-label">Position</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="position" name="position" autocomplete="off">
                                    <!-- <div id="position_list"></div>
                                    <input type="hidden" name="position_suggestion" id="position_suggestion"> -->
                                </div>
                            </div>
                            <!-- Purpose of Testing -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="purpose_of_testing" class="form-label">Purpose of Testing</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="purpose_of_testing" name="purpose_of_testing" autocomplete="off">
                                    <!-- <div id="purpose_of_testing_list"></div>
                                    <input type="hidden" name="purpose_of_testing_suggestion" id="purpose_of_testing_suggestion"> -->
                                </div>
                            </div>
                            <!-- Camera Ir-192 -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="camera_ir_192_id" class="form-label">Camera Ir-192 <sup class="astric d-none" id="astric_camera_ir_192">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="camera_ir_192_id" id="camera_ir_192_id">
                                        <option value="">Select Camera Ir-192</option>
                                        @forelse(getRTCamerasByIsotope('Ir-192') as $cam)
                                        <option value="{{ $cam->rt_camera_id }}" data-isotope="{{ $cam->rt_isotope }}" data-source-strength="{{ $cam->rtcd_initial_activity_ci }}" data-initial-activity="{{ $cam->rtcd_initial_activity_ci }}" data-loading-date="{{ $cam->rtcd_last_of_loading_date }}" data-source-size="{{ $cam->rtcd_source_size }}">
                                            {{ $cam->name_for_display ?: ($cam->rt_camera_name . ($cam->rt_serial_no ? ' - '.$cam->rt_serial_no : '')) }}
                                        </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Camera Ir-192.</div>
                                </div>
                            </div>
                            <!-- Camera Co-60 -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="camera_co_60_id" class="form-label">Camera Co-60 <sup class="astric d-none" id="astric_camera_co_60">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="camera_co_60_id" id="camera_co_60_id">
                                        <option value="">Select Camera Co-60</option>
                                        @forelse(getRTCamerasByIsotope('Co-60') as $cam)
                                        <option value="{{ $cam->rt_camera_id }}" data-isotope="{{ $cam->rt_isotope }}" data-source-strength="{{ $cam->rtcd_initial_activity_ci }}" data-initial-activity="{{ $cam->rtcd_initial_activity_ci }}" data-loading-date="{{ $cam->rtcd_last_of_loading_date }}" data-source-size="{{ $cam->rtcd_source_size }}">
                                            {{ $cam->name_for_display ?: ($cam->rt_camera_name . ($cam->rt_serial_no ? ' - '.$cam->rt_serial_no : '')) }}
                                        </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Camera Co-60.</div>
                                </div>
                            </div>
                            <!-- Camera X-Ray -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="camera_x_ray_id" class="form-label">Camera X-Ray <sup class="astric d-none" id="astric_camera_x_ray">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="camera_x_ray_id" id="camera_x_ray_id">
                                        <option value="">Select Camera X-Ray</option>
                                        @forelse(getRTCamerasByIsotope('X-Ray') as $cam)
                                        <option value="{{ $cam->rt_camera_id }}" data-xray-kv-ma="{{ $cam->rt_x_ray }}" data-xray-focal-size="{{ $cam->rt_focal_spot }}">
                                            {{ $cam->name_for_display ?: ($cam->rt_camera_name . ($cam->rt_serial_no ? ' - '.$cam->rt_serial_no : '')) }}
                                        </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Camera X-Ray.</div>
                                </div>
                            </div>

                            <!-- Camera Hidden Fields for Decay & Activity -->
                            <input type="hidden" name="hidden_camera_loading_date" id="hidden_camera_loading_date">
                            <input type="hidden" name="hidden_camera_initial_curie" id="hidden_camera_initial_curie">
                            <input type="hidden" name="hidden_camera_isotope" id="hidden_camera_isotope">

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="source_used" class="form-label">Source Used</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="source_used" name="source_used" tabindex="-1" readonly>
                                </div>
                            </div>
                            <!-- Source Strength (Ci) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="source_strength" class="form-label">Source Strength (Ci)</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="source_strength" name="source_strength" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Source Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="source_size" class="form-label">Source Size</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="source_size" name="source_size" readonly tabindex="-1">
                                </div>
                            </div>
                            
                           

                        </div>

                        <!-- Column 3 -->
                        <div class="col-md-4">

                            <!-- X-Ray (Kv) – mA -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="xray_kv_ma" class="form-label">X-Ray (Kv) – mA</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="xray_kv_ma" name="xray_kv_ma" readonly tabindex="-1">
                                </div>
                            </div>
                             <!-- X-Ray Focal Size (mm) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="xray_focal_size" class="form-label">X-Ray Focal Size (mm)</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="xray_focal_size" name="xray_focal_size" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Lead Screen Front / Back -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label class="form-label">Lead Screen (Front)</label>
                                </div>
                                <div class="col-8">
                                    <div class="row g-1">
                                        <!-- <div class="col-auto">
                                             <span class="text-nowrap" style="font-size: 13px;">Front:</span>
                                         </div> -->
                                        <div class="col">
                                            <input type="text" class="form-control" id="lead_screen_thick" name="lead_screen_thick" oninput="suggestLeadScreenThick(event, this)" autocomplete="off">
                                            <div id="lead_screen_thick_list"></div>
                                            <input type="hidden" name="lead_screen_thick_suggestion" id="lead_screen_thick_suggestion">
                                        </div>
                                        <div class="col-auto ms-1">
                                            <span class="text-nowrap" style="font-size: 13px;">Back </span>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" id="lead_screen_thick_back" name="lead_screen_thick_back" oninput="suggestLeadScreenThick(event, this)" autocomplete="off">
                                            <div id="lead_screen_thick_back_list"></div>
                                            <input type="hidden" name="lead_screen_thick_back_suggestion" id="lead_screen_thick_back_suggestion">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- IQI -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="iqi" class="form-label">IQI</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="iqi" name="iqi" oninput="suggestIqi(event, this)" autocomplete="off">
                                    <div id="iqi_list"></div>
                                    <input type="hidden" name="iqi_suggestion" id="iqi_suggestion">
                                </div>
                            </div>
                            <!-- Film Processing -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="film_processing" class="form-label">Film Processing</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_processing" name="film_processing" value="MANUAL" tabindex="-1" readonly>
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
                            <!-- Test Arrangement -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_arrangement" class="form-label">Test Arrangement</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="test_arrangement" name="test_arrangement">
                                </div>
                            </div>
                            <!-- Test Class -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_class" class="form-label">Test Class</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="test_class" name="test_class">
                                </div>
                            </div>
                            <!-- Film Brand -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_brand" class="form-label">Film Brand</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_brand" name="film_brand" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Film Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_type" class="form-label">Film Type</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_type" name="film_type" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Procedure Reference -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="procedure_ref_id" class="form-label">Procedure Reference</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="procedure_ref_id" id="procedure_ref_id">
                                        <option value="">Select Procedure Reference</option>
                                        @forelse(getProcedureReferance() as $pr)
                                        <option value="{{ $pr->procedure_reference_id }}">{{ $pr->procedure_reference }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Evaluation as per -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="evaluation_as_per_id" class="form-label">Evaluation as per</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="evaluation_as_per_id" id="evaluation_as_per_id">
                                        <option value="">Select Evaluation as per</option>
                                        @forelse(getEvaluationAsPer() as $ev)
                                        <option value="{{ $ev->evaluation_as_per_id }}">{{ $ev->evaluation_as_per }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Acceptance Standard -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="acceptance_standard_id" class="form-label">Acceptance Standard</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="acceptance_standard_id" id="acceptance_standard_id">
                                        <option value="">Select Acceptance Standard</option>
                                        @forelse(getAcceptanceStandards() as $as)
                                        <option value="{{ $as->id }}">{{ $as->acceptance_standard }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Customer Procedure Ref. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="customer_procedure_ref" class="form-label">Customer Procedure Ref.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="customer_procedure_ref" name="customer_procedure_ref">
                                </div>
                            </div>
                            <!-- Pending Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="pend_qty" class="form-label">Pending Qty.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="pend_qty" name="pend_qty" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- RT Report Qty. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="rt_report_qty" class="form-label">Report Qty. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="number" class="form-control" id="rt_report_qty" name="rt_report_qty" required>
                                    <div class="invalid-tooltip">Enter Report Qty.</div>
                                </div>
                            </div>
                            <!-- Film Size (inch/cm) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Film Size</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_unit_fix" id="rep_film_size_inch" value="inch" checked>
                                            <label class="form-check-label" for="rep_film_size_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_unit_fix" id="rep_film_size_cm" value="cm">
                                            <label class="form-check-label" for="rep_film_size_cm">cm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- SFD Unit -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">SFD Unit</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sfd_unit_fix" id="rep_sfd_inch" value="inch">
                                            <label class="form-check-label" for="rep_sfd_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sfd_unit_fix" id="rep_sfd_mm" value="mm" checked>
                                            <label class="form-check-label" for="rep_sfd_mm">mm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Print Ug In Report -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Print Ug In Report</label>
                                </div>
                                <div class="col-8">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="print_ug_in_report" id="rep_print_ug_in_report" value="Yes">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid Card -->
                    <div class="row PIselected">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary" id="addDetailRowBtn">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Test Report Details</b></h4>
                                </div>
                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="ReportDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th class="action_col">Actions</th>
                                                        <th scope="col" style="width: 60px;">Sr. No.</th>
                                                        <th scope="col">Identification</th>
                                                        <th scope="col">Location</th>
                                                        <th scope="col">Source</th>
                                                        <th scope="col">Film Brand</th>
                                                        <th scope="col">Film Type</th>
                                                        <th scope="col">Thickness (mm)</th>
                                                        <th scope="col" class="th_sfd_unit">SFD</th>
                                                        <th scope="col">Optical Density</th>
                                                        <th scope="col">IQI Designation</th>
                                                        <th scope="col">IQI Sensitivity</th>
                                                        <th scope="col">Film Size</th>
                                                        <th scope="col">No. of Film</th>
                                                        <th scope="col">Exposure Time</th>
                                                        <th scope="col">Finding</th>
                                                        {{-- <th scope="col">Finding Level</th> --}}
                                                        <th scope="col">Result</th>
                                                        <th scope="col">Ug</th>
                                                        <th scope="col" class="d-none">Inc. In Mea. Sheet</th>
                                                        <th scope="col" class="d-none">Film Qty.</th>
                                                        <th scope="col" class="d-none th_sq_unit">Sq</th>
                                                        <th scope="col" class="d-none th_total_sq_unit">Total Sq</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="18" class="text-center" id="noDetails">
                                                            No Test Report RT Details Added
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Fields Row -->
                    <div class="row mt-1 g-2">
                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <!-- ULR Conf. -->
                            <div class="row g-2 mb-1" style="display:none;">
                                <div class="col-4">
                                    <label for="ulr_id" class="form-label">ULR <span class="astric_ulr"></span></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single skip-tab" name="ulr_id" id="ulr_id">
                                        <option value="">Select ULR</option>
                                        @forelse(getULRConf() as $data)
                                        <option value="{{ $data->id }}">{{ $data->ulr }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select ULR.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1 ulrDesign">
                                <div class="col-4">
                                    <label for="ulr_sequence" class="form-label">ULR <span class="astric_ulr_seq"></span></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control" id="ulr_sequence" name="ulr_sequence" readonly tabindex="-1" style="max-width:80px;">
                                        <div class="invalid-tooltip">Enter ULR </div>
                                        <input type="text" class="form-control skip-tab" id="ulr_no" name="ulr_no" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Tested By -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="tested_by_authority_person_id" class="form-label">Tested By </label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="tested_by_authority_person_id" id="tested_by_authority_person_id">
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
                                <div class="col-4">
                                    <label for="reviewed_by_authority_person_id" class="form-label">Reviewed By</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="reviewed_by_authority_person_id" id="reviewed_by_authority_person_id">
                                        <option value="">Select Reviewed By</option>
                                        @forelse(getAuthorityPersonsReviewedBy() as $ap)
                                        <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Authorized By -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="authorized_by_authority_person_id" class="form-label">Authorized By</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="authorized_by_authority_person_id" id="authorized_by_authority_person_id">
                                        <option value="">Select Authorized By</option>
                                        @forelse(getAuthorityPersonsAuthorizedBy() as $ap)
                                        <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <!-- Abbreviation -->
                            <div class="row g-2 mb-1" style="display: none;">
                                <div class="col-4">
                                    <label for="abbreviation" class="form-label">Abbreviation</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="abbreviation" name="abbreviation" tabindex="-1" readonly>
                                </div>
                            </div>

                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <!-- No. of Films -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="no_of_films" class="form-label">No. of Films</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="no_of_films" name="no_of_films" tabindex="-1" readonly>
                                </div>
                            </div>
                            <!-- Film Size -->
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_size" class="form-label">Film Size</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_size" name="film_size" tabindex="-1" readonly>
                                </div>
                            </div> -->
                            <!-- Total Area -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="total_area" class="form-label">Total Area</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="total_area" name="total_area" tabindex="-1" readonly>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="result" class="form-label">Result <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="result" id="result" required>
                                        <option value="">Select Result</option>
                                        <option value="Ok">Ok</option>
                                        <option value="Not Ok">Not Ok</option>
                                        <option value="Repair (Send Back)">Repair (Send Back)</option>
                                        <option value="Repair">Repair</option>
                                        <option value="Retake">Retake</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Result.</div>
                                </div>
                            </div>
                            <input type="hidden" id="ulr_year" name="ulr_year">
                        </div>

                        <!-- Column 3 -->
                        <div class="col-md-4">
                            <!-- Special Note -->
                            <div class="row g-2 align-items-start mb-1">
                                <div class="col-4">
                                    <label for="sp_note" class="form-label mt-1">Special Note</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control" name="sp_note" id="sp_note" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="row g-2 align-items-start">
                                <div class="col-4">
                                    <label for="note" class="form-label mt-1"> NABL Report Disclaimer</label>
                                </div>
                                <div class="col-8">
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
                @if(hasAccess("test_report_rt", "print"))
                <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("test_report_rt", "add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

@include('modals.modals-details.test_report_rt_details_modal')
@include('modals.modals-pending.pending_inward_for_rt_modal')
@include('modals.modals-pending.copy_report_rt_modal')
@include('modals.modals-pending.copy_rss_rt_modal')
@include('modals.film_brand_modal')
@include('modals.film_type_modal')
@include('modals.iqi_designation_modal')
@include('modals.iqi_sensitivity_modal')
@include('modals.film_modal')
@include('modals.finding_modal')
{{-- @include('modals.finding_level_modal') --}}
@include('modals.film_result_modal')
@push('script-modal')
<script>
    var loginUserId = "{{ auth()->id() }}";
    var checkFileRoute = "{{ route('check-file_exists') }}";

</script>
<script src="{{ URL::asset('views/js/test_report_rt.js?ver='.getJsVersion()) }}"></script>
@endpush


