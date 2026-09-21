<div class="modal fade" id="OfferDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Offer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="OfferDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="offer_details_id" id="offer_details_id" value="0"/>
                    <input type="hidden" name="is_observation_sheet" id="is_observation_sheet"/>
                    <input type="hidden" name="test_report_rt_id" id="test_report_rt_id"/>
                    <input type="hidden" name="revision_number" id="revision_number"/>

                    <div class="row mt-2">
                        {{-- ===== Column 1 ===== --}}
                        <div class="col-md-6">
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type_of_testing_id_fix" class="form-label">Type of Test <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="type_of_testing_id_fix" id="type_of_testing_id_fix" required>
                                        <option value="">Select Type of Test</option>
                                        <option value="RT">RT</option>
                                        <option value="UT">UT</option>
                                        <option value="MPT">MPT</option>
                                        <option value="DPT">DPT</option>
                                        <option value="ET">ET</option>
                                        <option value="VT">VT</option>
                                        <option value="PAUT">PAUT</option>
                                        <option value="UTT">UTT</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Type of Test.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label class="form-label"></label></div>
                                <div class="col-8 d-flex align-items-center gap-3" id="process_type_container" style="pointer-events: none; opacity: 0.7;">
                                    <div class="form-check me-2">
                                        <input class="form-check-input" type="radio" name="process_type" id="process_type_fresh" value="Fresh" checked disabled>
                                        <label class="form-check-label" for="process_type_fresh">Fresh</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="process_type" id="process_type_repair" value="Repair" disabled>
                                        <label class="form-check-label" for="process_type_repair">Repair</label>
                                    </div>
                                    <button type="button" id="pendingBtn" class="btn btn-sm btn-primary ms-auto" disabled>Pending</button>
                                    <button type="button" id="copyMaterialBtn" class="btn btn-sm btn-primary text-nowrap" disabled>Past Inward</button>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type_of_job_id" class="form-label">Type of Job <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_type_of_job" name="type_of_job_id" id="type_of_job_id" required>
                                            <option value="">Select Type of Job</option>
                                            @forelse(getTypeOfJob() as $toj)
                                                <option value="{{ $toj->id }}">{{ $toj->type_of_job }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Type of Job.</div>
                                        @if(hasAccess("type_of_job","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedTypeofJob(true)" data-bs-target="#TypeOfJobModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="inward_job_desc_id" class="form-label">Job Description <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_job_description" name="job_desc_id" id="inward_job_desc_id" required>
                                            <option value="">Select Job Description</option>
                                            @forelse(getMiJobDescription() as $jd)
                                                <option value="{{ $jd->id }}" data-parts="{{ json_encode($jd->parts) }}">{{ $jd->job_description }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Job Description.</div>
                                        @if(hasAccess("job_description","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedJobDescription(true)" data-bs-target="#JobDescriptionModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="part_no" class="form-label">Part No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="part_no" id="part_no" class="form-control" onkeyup="suggestInwardPartNo(event, this)" maxlength="155" autocomplete="off">
                                    <div id="inward_part_no_list"></div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="drg_no" class="form-label">Drg. No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="drg_no" id="drg_no" class="form-control" onkeyup="suggestInwardDrgNo(event, this)" maxlength="155" autocomplete="off">
                                    <div id="inward_drg_no_list"></div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="material_id" class="form-label">Material <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_material_name" name="material_id" id="material_id" required>
                                            <option value="">Select Material</option>
                                            @forelse(getMaterial() as $mat)
                                                <option value="{{ $mat->id }}">{{ $mat->material }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Material.</div>
                                        @if(hasAccess("material","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedMaterial(true)" data-bs-target="#MaterialModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="heat_no" class="form-label">Heat No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="heat_no" id="heat_no" class="form-control" maxlength="100">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="rt_no" class="form-label">RT No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="rt_no" id="rt_no" class="form-control" maxlength="100">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="product_code" class="form-label">Product Code</label></div>
                                <div class="col-8">
                                    <input type="text" name="product_code" id="product_code" class="form-control" onkeyup="suggestInwardProductCode(event, this)" maxlength="100" autocomplete="off">
                                    <div id="inward_product_code_list"></div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== Column 2 ===== --}}
                        <div class="col-md-6">
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="thickness" class="form-label">Thickness (mm)</label></div>
                                <div class="col-8">
                                    <input type="text" name="thickness" id="thickness" class="form-control" maxlength="100">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="area_of_coverage_id" class="form-label">Area of Coverage <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single mst_area_of_coverage" name="area_of_coverage_id" id="area_of_coverage_id" required>
                                            <option value="">Select Area of Coverage</option>
                                            @forelse(getAreaOfCoverage() as $aoc)
                                                <option value="{{ $aoc->area_of_coverage_id }}">{{ $aoc->area_of_coverage }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Area of Coverage.</div>
                                        @if(hasAccess("area_of_coverage","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedAreaOfCoverage(true)" data-bs-target="#AreaOfCoverageModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="procedure_ref_id" class="form-label">Procedure Ref.</label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single mst_procedure_reference" name="procedure_ref_id" id="procedure_ref_id">
                                            <option value="">Select Procedure Ref.</option>
                                            @forelse(getProcedureReferance() as $pr)
                                                <option value="{{ $pr->procedure_reference_id }}">{{ $pr->procedure_reference }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        @if(hasAccess("procedure_reference","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedProcedureReference(true)" data-bs-target="#ProcedureReferenceModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="evaluation_as_per_id" class="form-label">Evaluation as Per</label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single mst_evaluation_as_per" name="evaluation_as_per_id" id="evaluation_as_per_id">
                                            <option value="">Select Evaluation as Per</option>
                                            @forelse(getEvaluationAsPer() as $eap)
                                                <option value="{{ $eap->evaluation_as_per_id }}">{{ $eap->evaluation_as_per }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        @if(hasAccess("evaluation_as_per","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedEvaluationAsPer(true)" data-bs-target="#EvaluationAsPerModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="acceptance_standard_id" class="form-label">Acceptance Std.</label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_acceptance_standard" name="acceptance_standard_id" id="acceptance_standard_id">
                                            <option value="">Select Acceptance Std.</option>
                                            @forelse(getAcceptanceStandards() as $acs)
                                                <option value="{{ $acs->id }}">{{ $acs->acceptance_standard }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        @if(hasAccess("acceptance_standard","add"))
                                            <i class="plus-icon bx bx-plus-medical" onclick="addedAcceptanceStandard(true)" data-bs-target="#AcceptanceStandardModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="quantity" class="form-label">Quantity <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="quantity" id="quantity" class="form-control isInteger" required>
                                    <div class="invalid-tooltip">Enter Quantity.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="approx_value" class="form-label">Approx. Value</label></div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="approx_value" id="approx_value" class="form-control isNumberKey" onblur="formatPoints(this,2)"><span class="mt-2 ml-1 text-nowrap ms-1"> Rs.</span>
                                    <div class="invalid-tooltip">Enter Approx. Value.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="approx_weight" class="form-label">Approx. Wt.</label></div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" name="approx_weight" id="approx_weight" class="form-control isNumberKey" onblur="formatPoints(this,3)">
                                    <div class="invalid-tooltip">Enter Approx. Wt.</div><span class="mt-2 ml-1 text-nowrap ms-1"> Kgs.</span>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="remark" class="form-label">Remark</label></div>
                                <div class="col-8">
                                    <textarea name="remark" id="remark" class="form-control" rows="2" maxlength="8000"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="detailSubmitBtn">Submit</button>
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
