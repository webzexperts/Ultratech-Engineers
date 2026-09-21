<div class="modal fade" id="ObservationSheetSubDetailModal" tabindex="-1" aria-labelledby="ObservationSheetSubDetailModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ObservationSheetSubDetailModalLabel">Observation Sheet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ObservationSheetSubDetailForm" class="needs-validation" novalidate>
                    <input type="hidden" name="sub_detail_index" id="sub_detail_index">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Sr. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_sr_no" class="form-label">Sr. No. <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" name="sr_no" id="sub_sr_no" class="form-control isNumberKey" required>
                                    <div class="invalid-tooltip">Enter Sr. No.</div>
                                </div>
                            </div>
                            <!-- Identification -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_identification" class="form-label">Identification</label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" name="identification" id="sub_identification" class="form-control">
                                    <div class="invalid-tooltip">Enter Identification.</div>
                                </div>
                            </div>
                            <!-- Location -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_location" class="form-label">Location <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" name="location" id="sub_location" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Location.</div>
                                </div>
                            </div>
                            <!-- Source -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_source_id_fix" class="form-label">Source <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="source_id_fix" id="sub_source_id_fix" required style="width:100%;">
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
                                <div class="col-4"><label for="sub_film_brand_id" class="form-label">Film Brand <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single suggest_film_brand mst_film_brand" name="film_brand_id" id="sub_film_brand_id" required style="width:100%;">
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
                                <div class="col-4"><label for="sub_film_type_id" class="form-label">Film Type <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single suggest_film_type mst_film_type" name="film_type_id" id="sub_film_type_id" required style="width:100%;">
                                        <option value="">Select Film Type</option>
                                        @forelse(getFilmTypes() as $ft)
                                        <option value="{{ $ft->film_type_id }}" data-film_brand_id="{{ $ft->film_brand_id }}">{{ $ft->film_type }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Type.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Thickness -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_thickness" class="form-label">Thickness (mm) <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" name="thickness" id="sub_thickness" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Thickness.</div>
                                </div>
                            </div>
                            <!-- SFD -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_sfd" class="form-label">SFD (mm) <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" name="sfd" id="sub_sfd" class="form-control" required>
                                    <div class="invalid-tooltip">Enter SFD.</div>
                                </div>
                            </div>
                            <!-- IQI Designation -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_iqi_designation_id" class="form-label">IQI Designation <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single suggest_iqi_designation mst_iqi_designation" name="iqi_designation_id" id="sub_iqi_designation_id" required style="width:100%;">
                                        <option value="">Select Designation</option>
                                        @forelse(getIqiDesignations() as $iqid)
                                        <option value="{{ $iqid->iqi_designation_id }}">{{ $iqid->iqi_designation }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select IQI Designation.</div>
                                </div>
                            </div> --}}
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="sub_iqi_designation" class="form-label">IQI Designation <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" name="iqi_designation" id="sub_iqi_designation" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter IQI Designation.</div>
                                </div>
                            </div>
                            <!-- IQI Sensitivity -->
                            {{-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_iqi_sensitivity_id" class="form-label">IQI Sensitivity <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single suggest_iqi_sensitivity mst_iqi_sensitivity" name="iqi_sensitivity_id" id="sub_iqi_sensitivity_id" required style="width:100%;">
                                        <option value="">Select Sensitivity</option>
                                        @forelse(getIqiSensitivities() as $iqis)
                                        <option value="{{ $iqis->iqi_sensitivity_id }}">{{ $iqis->iqi_sensitivity }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select IQI Sensitivity.</div>
                                </div>
                            </div> --}}
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="sub_iqi_sensitivity" class="form-label">IQI Sensitivity <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control" name="iqi_sensitivity" id="sub_iqi_sensitivity" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter IQI Sensitivity.</div>
                                </div>
                            </div>
                            <!-- Film Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sub_film_id" class="form-label">Film Size <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single suggest_film mst_film" name="film_id" id="sub_film_id" required style="width:100%;">
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
                                <div class="col-4"><label for="sub_no_of_film_fix" class="form-label">No. of Film <sup class="astric">*</sup></label></div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" name="no_of_film_fix" id="sub_no_of_film_fix" required style="width:100%;">
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Triple">Triple</option>
                                        <option value="Quadra">Quadra</option>
                                    </select>
                                    <div class="invalid-tooltip">Select No. of Film.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveObsSubDetailBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
