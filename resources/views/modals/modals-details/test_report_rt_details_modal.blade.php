<div class="modal fade" id="ReportDetailsModal" aria-labelledby="ReportDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ReportDetailsModalLabel">Test Report Details</h5>
                <div class="ms-4 mt-2 d-flex">
                    <label for="copy_from_sr_no" class="form-label me-2 mb-0 text-nowrap ">Copy From Sr. No.</label>
                    <input type="text" id="copy_from_sr_no" class="form-control isNumberKey" style="width: 180px;">
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ReportDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add" />
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />
                    <input type="hidden" name="test_report_rt_details_id" id="test_report_rt_details_id" />
                    <input type="hidden" name="main_rt_detail_id" id="detail_main_rt_detail_id" />
                    <input type="hidden" name="record_type_id" id="detail_record_type_id" value="1" />
                    <input type="hidden" name="include_in_measurement_sheet" id="include_in_measurement_sheet" value="Yes" />

                    <div class="row mt-2">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <!-- Sr No -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_sr_no" class="form-label">Sr. No. <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="sr_no" id="detail_sr_no" class="form-control isNumberKey" required>
                                    <div class="invalid-tooltip">Enter Sr. No.</div>
                                </div>
                            </div>
                            <!-- Identification -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_identification" class="form-label">Identification </div>
                                <div class="col-8">
                                    <input type="text" name="identification" id="detail_identification" class="form-control">
                                    <div class="invalid-tooltip">Enter Identification.</div>
                                </div>
                            </div>
                            <!-- Location -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_location" class="form-label">Location <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="location" id="detail_location" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Location.</div>
                                </div>
                            </div>
                            <!-- Source -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_source_id_fix" class="form-label">Source <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="source_id_fix" id="detail_source_id_fix" required>
                                        <option value="">Select Source</option>
                                        <option value="Ir-192">Ir-192</option>
                                        <option value="Co-60">Co-60</option>
                                        <option value="X-Ray">X-Ray</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Source.</div>
                                </div>
                            </div>
                            <!-- Film Brand -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_film_brand_id" class="form-label">Film Brand <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_film_brand mst_film_brand" name="detail_film_brand_id" id="detail_film_brand_id" required>
                                            <option value="">Select Film Brand</option>
                                            @forelse(getFilmBrands() as $fb)
                                            <option value="{{ $fb->film_brand_id }}">{{ $fb->film_brand }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Film Brand.</div>
                                        @if(hasAccess("film_brand","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedFilmBrand(true)" data-bs-target="#FilmBrandModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- Film Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_film_type_id" class="form-label">Film Type <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_film_type mst_film_type" name="detail_film_type_id" id="detail_film_type_id" required>
                                            <option value="">Select Film Type</option>
                                            @forelse(getFilmTypes() as $ft)
                                            <option value="{{ $ft->film_type_id }}">{{ $ft->film_type }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Film Type.</div>
                                        @if(hasAccess("film_type","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedFilmType(true)" data-bs-target="#FilmTypeModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- Thickness -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_thickness" class="form-label">Thickness(mm) <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="thickness" id="detail_thickness" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Thickness(mm).</div>
                                </div>
                            </div>
                            <!-- SFD -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_sfd" class="form-label">SFD <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="sfd" id="detail_sfd" class="form-control" required>
                                    <div class="invalid-tooltip">Enter SFD.</div>
                                </div>
                            </div>
                            <!-- Optical Density -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_optical_density" class="form-label">Optical Density</label></div>
                                <div class="col-8">
                                    <input type="text" name="optical_density" id="detail_optical_density" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <!-- IQI Designation -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_iqi_designation_id" class="form-label">IQI Designation <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_iqi_designation mst_iqi_designation" name="detail_iqi_designation_id" id="detail_iqi_designation_id" required>
                                            <option value="">Select IQI Designation</option>
                                            @forelse(getIqiDesignations() as $idg)
                                            <option value="{{ $idg->iqi_designation_id }}">{{ $idg->iqi_designation }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select IQI Designation.</div>
                                        @if(hasAccess("iqi_designation","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedIqiDesignation(true)" data-bs-target="#IqiDesignationModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="iqi_designation" class="form-label">IQI Designation <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" name="iqi_designation" id="iqi_designation" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter IQI Designation.</div>
                                </div>
                            </div>

                            <!-- IQI Sensitivity -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_iqi_sensitivity_id" class="form-label">IQI Sensitivity <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_iqi_sensitivity mst_iqi_sensitivity" name="detail_iqi_sensitivity_id" id="detail_iqi_sensitivity_id" required>
                                            <option value="">Select IQI Sensitivity</option>
                                            @forelse(getIqiSensitivities() as $is)
                                            <option value="{{ $is->iqi_sensitivity_id }}">{{ $is->iqi_sensitivity }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select IQI Sensitivity.</div>
                                        @if(hasAccess("iqi_sensitivity","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedIqiSensitivity(true)" data-bs-target="#IqiSensitivityModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="iqi_sensitivity" class="form-label">IQI Sensitivity <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" name="iqi_sensitivity" id="iqi_sensitivity" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter IQI Sensitivity.</div>
                                </div>
                            </div>
                            <!-- Film Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_film_id" class="form-label">Film Size <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_film mst_film" name="detail_film_id" id="detail_film_id" required>
                                            <option value="">Select Film Size</option>
                                            @forelse(getFilms() as $film)
                                            <option value="{{ $film->film_id }}" data-sq_in="{{ $film->sq_in }}" data-sq_cm="{{ $film->sq_cm }}" data-inch="{{ $film->film_size_inch }}" data-cm="{{ $film->film_size_cm }}">
                                                {{ $film->film_size_inch }}
                                            </option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Film Size.</div>
                                        @if(hasAccess("film","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedFilm(true)" data-bs-target="#FilmModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- No. of Film -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_no_of_film_fix" class="form-label">No. of Film <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="no_of_film_fix" id="detail_no_of_film_fix" required>
                                        <!-- <option value="">Select No. of Film</option> -->
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Triple">Triple</option>
                                        <option value="Quadra">Quadra</option>
                                    </select>
                                    <div class="invalid-tooltip">Select No. of Film.</div>
                                </div>
                            </div>
                            <!-- Exposure Time -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_exposure_time" class="form-label">Exposure Time</label></div>
                                <div class="col-8">
                                    <input type="text" name="exposure_time" id="detail_exposure_time" class="form-control">
                                </div>
                            </div>
                            <!-- Finding -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_finding_id" class="form-label">Finding <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_finding mst_finding" name="detail_finding_id" id="detail_finding_id" required>
                                            <option value="">Select Finding</option>
                                            @forelse(getFindings() as $fd)
                                            <option value="{{ $fd->finding_id }}" data-abbreviation="{{ $fd->abbreviation }}" data-required_finding_level="{{ $fd->required_finding_level }}">{{ $fd->finding_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Finding</div>
                                        @if(hasAccess("finding","add"))
                                        <i class="plus-icon bx bx-plus-medical" onclick="addedFinding(true)" data-bs-target="#FindingModal"></i>
                                        @endif
                                    </div>
                                </div>
                            </div> --}}

                            <!-- Finding (Input Field with Suggestion) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="finding" class="form-label ">Finding <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" name="finding" id="finding" autocomplete="off" required>
                                    <!-- <input type="text" class="form-control" name="finding" id="finding" oninput="suggestFinding(event, this)" autocomplete="off" required>
                                    <div id="finding_list" class="suggestion_list"></div> -->
                                    <div class="invalid-tooltip">Enter Finding.</div>
                                </div>
                            </div>
                            {{-- <!-- Finding Level -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_finding_level_id" class="form-label">Finding Level <sup class="astric d-none" id="detail_finding_level_astric">*</sup></label></div>
                                <div class="col-8">
                                    <div class="otherselectwidth">
                                        <select class="js-example-basic-single suggest_finding_level mst_finding_level" name="detail_finding_level_id" id="detail_finding_level_id">
                                            <option value="">Select Finding Level</option>
                                            @forelse(getFindingLevels() as $fl)
                                            <option value="{{ $fl->finding_level_id }}">{{ $fl->finding_level_name }}</option>
                            @empty
                            @endforelse
                            </select>
                            <div class="invalid-tooltip">Select Finding Level</div>
                            @if(hasAccess("finding_level","add"))
                            <i class="plus-icon bx bx-plus-medical" onclick="addedFindingLevel(true)" data-bs-target="#FindingLevelModal"></i>
                            @endif
                        </div>
                    </div>
            </div> --}}
            <!-- Film Result -->
            <div class="row g-2 mb-1">
                <div class="col-4"><label for="detail_film_result_id" class="form-label">Result <sup class="astric">*</sup></label></div>
                <div class="col-8">
                    <div class="otherselectwidth">
                        <select class="js-example-basic-single suggest_film_result mst_film_result" name="detail_film_result_id" id="detail_film_result_id" required>
                            <option value="">Select Result</option>
                            @forelse(getFilmResults() as $fr)
                            <option value="{{ $fr->film_result_id }}">{{ $fr->film_result_name }}</option>
                            @empty
                            @endforelse
                        </select>
                        <div class="invalid-tooltip">Select Result</div>
                        @if(hasAccess("film_result","add"))
                        <i class="plus-icon bx bx-plus-medical" onclick="addedFilmResult(true)" data-bs-target="#FilmResultModal"></i>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Ug -->
            <div class="row g-2 mb-1">
                <div class="col-4"><label for="detail_ug" class="form-label">Ug</label></div>
                <div class="col-8">
                    <input type="text" name="ug" id="detail_ug" class="form-control">
                </div>
            </div>



        </div>
    </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" id="submitDetailRowBtn">Add</button>
    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
</div>
</div>
</div>
</div>
