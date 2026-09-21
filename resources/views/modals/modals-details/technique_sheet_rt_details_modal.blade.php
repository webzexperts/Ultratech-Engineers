<div class="modal fade" id="TSDetailsModal" aria-labelledby="TSDetailsModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="TSDetailsModalLabel">Radiographic Shooting Sketch Details</h5>
                <div class="ms-4 d-flex mt-2">
                    <label for="copy_from_sr_no" class="form-label me-2 mb-0 text-nowrap">Copy From Sr. No.</label>
                    <input type="text" id="copy_from_sr_no" class="form-control isNumberKey" style="width: 180px;">
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="TSDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="technique_sheet_rt_details_id" id="technique_sheet_rt_details_id" /> 

                    <div class="row mt-2">
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
                                <div class="col-4"><label for="detail_identification" class="form-label">Identification</label></div>
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
                                    <select class="js-example-basic-single" name="film_brand_id" id="detail_film_brand_id" required>
                                        <option value="">Select Film Brand</option>
                                        @forelse(getFilmBrands() as $fb)
                                            <option value="{{ $fb->film_brand_id }}">{{ $fb->film_brand }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Brand.</div>
                                </div>
                            </div>
                            <!-- Film Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_film_type_id" class="form-label">Film Type <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="film_type_id" id="detail_film_type_id" required>
                                        <option value="">Select Film Type</option>
                                        @forelse(getFilmTypes() as $ft)
                                            <option value="{{ $ft->film_type_id }}">{{ $ft->film_type }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Type.</div>
                                </div>
                            </div>
                            <!-- Thickness -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_thickness" class="form-label">Thickness (mm) <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="thickness" id="detail_thickness" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Thickness (mm).</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- SFD -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_sfd" class="form-label">SFD <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="sfd" id="detail_sfd" class="form-control" required>
                                    <div class="invalid-tooltip">Enter SFD.</div>
                                </div>
                            </div>
                            <!-- IQI Designation -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_iqi_designation_id" class="form-label">IQI Designation <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="iqi_designation_id" id="detail_iqi_designation_id" required>
                                        <option value="">Select Designation</option>
                                        @forelse(getIqiDesignations() as $idg)
                                            <option value="{{ $idg->iqi_designation_id }}">{{ $idg->iqi_designation }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select IQI Designation.</div>
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
                                    <select class="js-example-basic-single" name="iqi_sensitivity_id" id="detail_iqi_sensitivity_id" required>
                                        <option value="">Select Sensitivity</option>
                                        @forelse(getIqiSensitivities() as $is)
                                            <option value="{{ $is->iqi_sensitivity_id }}">{{ $is->iqi_sensitivity }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select IQI Sensitivity.</div>
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
                                    <select class="js-example-basic-single" name="film_id" id="detail_film_id" required>
                                        <option value="">Select Film Size</option>
                                        @forelse(getFilms() as $film)
                                            <option value="{{ $film->film_id }}" data-sq_in="{{ $film->sq_in }}" data-sq_cm="{{ $film->sq_cm }}" data-inch="{{ $film->film_size_inch }}" data-cm="{{ $film->film_size_cm }}">
                                                {{ $film->film_size_inch }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Size.</div>
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
                            <!-- Test Technique -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="detail_test_technique" class="form-label">Test Technique</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_test_technique" name="test_technique" onkeyup="suggestTestTechnique(event, this)" autocomplete="off">
                                    <div id="detail_test_technique_list"></div>
                                    <input type="hidden" name="test_technique_suggestion" id="detail_test_technique_suggestion">
                                </div>
                            </div>
                            <!-- Film Position -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="detail_film_position" class="form-label">Film Position</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_film_position" name="film_position" autocomplete="off">
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
