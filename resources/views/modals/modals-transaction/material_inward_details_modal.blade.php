<div class="modal fade" id="MaterialInwardDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="MaterialInwardDetailsModalLabel">Material Inward Details</h5>
                <div class="ms-4 mt-2 d-flex">
                    <label for="copy_from_sr_no" class="form-label me-2 mb-0 text-nowrap">Copy From Sr. No.</label>
                    <input type="text" id="copy_from_sr_no" class="form-control isNumberKey" style="width: 180px;">
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="MaterialInwardDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="material_inward_details_id" id="material_inward_details_id" value="0"/>
                    <input type="hidden" name="offer_details_id" id="offer_details_id" value="0"/>
                    <input type="hidden" name="is_observation_sheet" id="is_observation_sheet"/>
                    <input type="hidden" name="test_report_rt_id" id="test_report_rt_id"/>
                    <input type="hidden" name="revision_number" id="revision_number"/>

                    <div class="row mt-2">
                        {{-- ===== Column 1 ===== --}}
                        <div class="col-md-6">

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label class="form-label"></label></div>
                                <div class="col-8 d-flex align-items-center gap-3" id="process_type_container">
                                    <div class="form-check me-2">
                                        <input class="form-check-input" type="radio" name="process_type_fix" id="process_type_fresh" value="Fresh" checked>
                                        <label class="form-check-label" for="process_type_fresh">Fresh</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="process_type_fix" id="process_type_repair" value="Repair">
                                        <label class="form-check-label" for="process_type_repair">Repair</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="sr_no" class="form-label">Sr. No. </label></div>
                                <div class="col-8">
                                    <input type="text" name="sr_no" id="sr_no" class="form-control isInteger isNumberKey" maxlength="11" autocomplete="off">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type_of_testing_id_fix" class="form-label">Process <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="type_of_testing_id_fix" id="type_of_testing_id_fix" required>
                                        <option value="">Select Process</option>
                                        <option value="RT" selected>RT</option>
                                        <option value="UT">UT</option>
                                        <option value="MPT">MPT</option>
                                        <option value="DPT">DPT</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Process.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="lab_id" class="form-label">Lab ID <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="lab_id" id="lab_id" class="form-control" maxlength="100" required autocomplete="off">
                                    <div class="invalid-tooltip">Enter Lab ID</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="customer_id_no" class="form-label">Customer ID No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="customer_id_no" id="customer_id_no" class="form-control" maxlength="100" autocomplete="off">
                                </div>
                            </div>
                            
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="job_desc" class="form-label">Job Description <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="job_desc" id="job_desc" class="form-control" maxlength="500" required autocomplete="off">
                                    <div class="invalid-tooltip">Enter Job Description.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="die_no" class="form-label">Die No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="die_no" id="die_no" class="form-control" onkeyup="suggestInwardDieNo(event, this)" maxlength="100" autocomplete="off">
                                    <div id="inward_die_no_list"></div>
                                </div>
                            </div>
                         
                        </div>

                        {{-- ===== Column 2 ===== --}}
                        <div class="col-md-6">

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="heat_no" class="form-label">Heat No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="heat_no" id="heat_no" class="form-control" maxlength="100" autocomplete="off">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="drg_no" class="form-label">Drg. No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="drg_no" id="drg_no" class="form-control" onkeyup="suggestInwardDrgNo(event, this)" maxlength="100" autocomplete="off">
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
                                <div class="col-4"><label for="thickness" class="form-label">Thickness (mm)</label></div>
                                <div class="col-8">
                                    <input type="text" name="thickness" id="thickness" class="form-control" maxlength="100">
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="quantity" class="form-label">Quantity <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="quantity" id="quantity" class="form-control isInteger skip-tab" value="1" readonly tabindex="-1" required>
                                    <div class="invalid-tooltip">Enter Quantity.</div>
                                </div>
                            </div>

                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start"><label for="remark" class="form-label">Remark</label></div>
                                <div class="col-8">
                                    <textarea name="remark" id="remark" class="form-control" rows="2" maxlength="500"></textarea>
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
