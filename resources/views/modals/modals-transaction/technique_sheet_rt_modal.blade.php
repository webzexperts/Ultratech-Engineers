<div class="modal fade" id="TechniqueSheetRtModal" aria-labelledby="TechniqueSheetRtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="TechniqueSheetRtModalLabel">Radiographic Shooting Sketch</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonTechniqueSheetRtForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="test_report_rt_id" id="test_report_rt_id">

                    <!-- Top Fields: 3-column structured layout matching PDF checklist exactly -->
                    <div class="row mb-1 g-2">
                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <!-- Entry Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label"></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="entry_type_fix" id="entry_type_manual" value="Manual" checked required>
                                            <label class="form-check-label" for="entry_type_manual">Manual</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="entry_type_fix" id="entry_type_rt_report" value="From RT Report" required>
                                            <label class="form-check-label" for="entry_type_rt_report">From RT Report</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Sr. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="technique_sheet_rt_sequence" class="form-label">Sr. No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control isNumberKey" id="technique_sheet_rt_sequence" name="technique_sheet_rt_sequence" style="max-width:80px;" autofocus required>
                                        <input type="text" class="form-control skip-tab" id="technique_sheet_rt_no" name="technique_sheet_rt_no" tabindex="-1" readonly>
                                        <div class="invalid-tooltip">Enter Sr. No.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="technique_sheet_rt_date" class="form-label">Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control trans-date-picker" id="technique_sheet_rt_date" name="technique_sheet_rt_date" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter Date.</div>
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
                                            @forelse(getCustomers() as $cust)
                                            <option value="{{ $cust->id }}">{{ $cust->customer }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        {{-- Old Pending button - commented out as per requirement --}}
                                        {{-- <button type="button" class="btn btn-success btn-sm toggleModalBtn pending_detail" id="pending_btn" disabled data-bs-target="#TestReportRTPendingModal">
                                            Pending
                                        </button> --}}
                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
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
                                        <button type="button" class="btn btn-success btn-sm" id="copyAllBtn" disabled>Copy</button>
                                        <div class="invalid-tooltip">Select Type of Job.</div>
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
                                    <label for="part_id" class="form-label">Part No.</label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="part_id" id="part_id">
                                        <option value="">Select Part No.</option>
                                        @forelse(getparts() as $part)
                                            <option value="{{ $part->part_id }}" data-drg_no="{{ $part->drg_no }}" data-job_desc_id="{{ $part->job_desc_id }}">
                                                {{ $part->part_no }} - {{ $part->drg_no }}
                                            </option>
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
                                    <input type="text" class="form-control" id="part_no" name="part_no" onkeyup="suggestPartNo(event, this)" autocomplete="off">
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
                                    <label for="drg_no" class="form-label">Drg. No.</label></label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="drg_no" name="drg_no" onkeyup="suggestDrgNo(event, this)" autocomplete="off">
                                    <div id="drg_no_list"></div>
                                    <input type="hidden" name="drg_no_suggestion" id="drg_no_suggestion">
                                    <div class="invalid-tooltip">Enter Drg. No.</div>
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
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <!-- Source Used -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="source_used" class="form-label">Source Used</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="source_used" name="source_used" tabindex="-1" readonly>
                                </div>
                            </div>
                            <!-- Source Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="source_size" class="form-label">Source Size</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="source_size" name="source_size" readonly>
                                </div>
                            </div>
                            <!-- X-Ray Focal Size (mm) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="xray_focal_size" class="form-label">X-Ray Focal Size (mm)</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="xray_focal_size" name="xray_focal_size" readonly>
                                </div>
                            </div>
                            <!-- Lead Screen Front / Back -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label class="form-label">Lead Screen (Front)</label>
                                </div>
                                <div class="col-8">
                                    <div class="row g-1">
                                        <div class="col">
                                            <input type="text" class="form-control" id="lead_screen_thick" name="lead_screen_thick" autocomplete="off">
                                        </div>
                                        <div class="col-auto ms-1">
                                            <span class="text-nowrap" style="font-size: 13px;">Back </span>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" id="lead_screen_thick_back" name="lead_screen_thick_back" autocomplete="off">
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
                                    <input type="text" class="form-control" id="iqi" name="iqi" onkeyup="suggestIqi(event, this)">
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
                                    <input type="text" class="form-control" id="film_processing" name="film_processing" onkeyup="suggestFilmProcessing(event, this)">
                                    <div id="film_processing_list"></div>
                                    <input type="hidden" name="film_processing_suggestion" id="film_processing_suggestion">
                                </div>
                            </div>
                            <!-- Test Technique -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="test_technique" class="form-label">Test Technique</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="test_technique" name="test_technique" tabindex="-1" readonly>
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
                        </div>

                        <!-- Column 3 -->
                        <div class="col-md-4">
                            <!-- Film Brand -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_brand" class="form-label">Film Brand</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_brand" name="film_brand" tabindex="-1" readonly>
                                </div>
                            </div>
                            <!-- Film Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_type" class="form-label">Film Type</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_type" name="film_type" tabindex="-1" readonly>
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
                            <!-- Shooting Sketch Image -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="shooting_sketch_image_file" class="form-label">Shooting Sketch Image</label>
                                </div>
                                <div class="col-8">
                                    <div class="input-append fileupload position-relative">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="file" name="shooting_sketch_image_file" id="shooting_sketch_image_file" class="form-control" accept=".png,.jpg,.jpeg,.gif">
                                            <input type="hidden" id="shooting_sketch_image_doc" name="shooting_sketch_image_doc">
                                            <a href="#" data-remove="shooting_sketch_image_file" id="shooting_sketch_image_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileTechniqueSheetRt(event)">Remove</a>
                                            <a target="_blank" class="btn img-prev hide" id="shooting_sketch_image_prev">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Film Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Film Size</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_fix" id="film_size_fix_inch" value="inch" checked>
                                            <label class="form-check-label" for="film_size_fix_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_fix" id="film_size_fix_cm" value="cm">
                                            <label class="form-check-label" for="film_size_fix_cm">cm</label>
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
                                            <input class="form-check-input" type="radio" name="sfd_unit_fix" id="sfd_unit_fix_inch" value="inch">
                                            <label class="form-check-label" for="sfd_unit_fix_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="sfd_unit_fix" id="sfd_unit_fix_mm" value="mm" checked>
                                            <label class="form-check-label" for="sfd_unit_fix_mm">mm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid Card -->
                    <div class="card mt-2">
                        <div class="card-header d-flex align-items-center py-2">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary toggleButton" id="addDetailRowBtn" data-bs-target="#TSDetailsModal">Add</button>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1 ml-2"><b>Radiographic Shooting Sketch Details</b></h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" id="TSDetailTable">
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
                                            <th scope="col">IQI Designation</th>
                                            <th scope="col">IQI Sensitivity</th>
                                            <th scope="col">Film Size</th>
                                            <th scope="col">No. of Film</th>
                                            <th scope="col">Test Technique</th>
                                            <th scope="col">Film Position</th>
                                            <th scope="col" class="d-none">Film Qty</th>
                                            <th scope="col" class="d-none th_sq_unit">SqIn / SqCm</th>
                                            <th scope="col" class="d-none th_total_sq_unit">Total SqIn / SqCm</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="15" class="text-center" id="noDetails">
                                                No Technique Sheet Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Fields: Summaries, Prepared By, Signatures, Notes -->
                    <div class="row mt-2 g-2">
                        <div class="col-md-4">
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="no_of_films" class="form-label">No. of Films</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="no_of_films" name="no_of_films" tabindex="-1" readonly>
                                </div>
                            </div>
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_size" class="form-label">Film Size</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="film_size" name="film_size" tabindex="-1" readonly>
                                </div>
                            </div> -->
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
                                    <label for="prepared_by_user_id" class="form-label">Prepared By</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly>
                                        <option value="">Select Prepared By</option>
                                        @forelse(getUsers() as $u)
                                        <option value="{{ $u->id }}" {{ Auth::id() == $u->id ? 'selected' : '' }}>{{ $u->person_name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="checked_by_authority_person_id" class="form-label">Checked By</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="checked_by_authority_person_id" id="checked_by_authority_person_id">
                                        <option value="">Select Checked By</option>
                                        @forelse(getCheckedByAuthorityPersons() as $ap)
                                        <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse

                                    </select>
                                    <div class="invalid-tooltip">Select Checked By</div>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="authorized_by_authority_person_id" class="form-label">Authorized By</label></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="authorized_by_authority_person_id" id="authorized_by_authority_person_id">
                                        <option value="">Select Authorized By</option>
                                        @forelse(getAuthorizedByAuthorityPersons() as $ap)
                                        <option value="{{ $ap->authority_person_id }}">{{ $ap->operator }}</option>
                                        @empty
                                        @endforelse

                                    </select>
                                    <div class="invalid-tooltip">Select Authorized By</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row g-2 align-items-start">
                                <div class="col-4">
                                    <label for="sp_note" class="form-label mt-1">Special Note</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control" name="sp_note" id="sp_note" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("technique_sheet_rt", "print"))
                <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("technique_sheet_rt", "add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
            </form>
        </div>
    </div>
</div>

@include('modals.modals-details.technique_sheet_rt_details_modal')
@include('modals.modals-transaction.technique_sheet_rt_copy_modal')

@push('script-modal')
<script>
    let loginUserId = "{{ auth()->id() }}";
    let fileUploadRoute = "{{ route('upload-docs') }}";
    let checkFileRoute = "{{ route('check-file_exists') }}";

</script>
<script src="{{ URL::asset('views/js/technique_sheet_rt.js?ver='.getJsVersion()) }}"></script>
@endpush
