<div class="modal fade" id="ObservationSheetDetailEditModal" aria-labelledby="ObservationSheetDetailEditModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ObservationSheetDetailEditModalLabel">Observation Sheet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ObservationSheetDetailEditForm" class="row g-3 needs-validation" novalidate>
                    <input type="hidden" id="detail_index" />

                    <div class="row mt-2">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Inward No & Date + Pending button -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="inward_no" class="form-label">Inward No.</label></div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" id="inward_no" class="form-control form-control-sm" readonly>
                                        <input type="text" id="inward_date" class="form-control form-control-sm" style="width:110px;" readonly>
                                        <button type="button" class="btn btn-sm btn-success text-nowrap" id="btn_pending_technique">Pending</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Report No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="report_no" class="form-label">Report No.</label></div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" id="report_no" class="form-control form-control-sm" readonly>
                                        <input type="text" id="revision_number" class="form-control form-control-sm" style="max-width:65px;" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Nature -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="nature" class="form-label">Nature</label></div>
                                <div class="col-8">
                                    <input type="text" id="nature" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Type of Test -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type_of_test" class="form-label">Type of Test</label></div>
                                <div class="col-8">
                                    <input type="text" id="type_of_test" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- NABL -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="nabl" class="form-label">NABL</label></div>
                                <div class="col-8">
                                    <input type="text" id="nabl" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Test At -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="test_at" class="form-label">Test At</label></div>
                                <div class="col-8">
                                    <input type="text" id="test_at" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type" class="form-label">Type</label></div>
                                <div class="col-8">
                                    <input type="text" id="type" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Challan No. & Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="dc_no" class="form-label">Challan No. </label></div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text" id="dc_no" class="form-control form-control-sm" readonly>
                                        <input type="text" id="dc_date" class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- PO No. & Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="po_no" class="form-label">PO No.</label></div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text" id="po_no" class="form-control form-control-sm" readonly>
                                        <input type="text" id="po_date" class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="type_of_job" class="form-label">Type of Job</label></div>
                                <div class="col-8">
                                    <input type="text" id="type_of_job" class="form-control form-control-sm" readonly>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Type of Job -->
                           
                            
                            <!-- Job Description -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="job_description" class="form-label">Job Description</label></div>
                                <div class="col-8">
                                    <input type="text" id="job_description" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Part No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="die_no" class="form-label">Part No.</label></div>
                                <div class="col-8">
                                    <input type="text" id="die_no" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Drg. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="drg_no" class="form-label">Drg. No.</label></div>
                                <div class="col-8">
                                    <input type="text" id="drg_no" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Material -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="material" class="form-label">Material</label></div>
                                <div class="col-8">
                                    <input type="text" id="material" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Heat No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="heat_no" class="form-label">Heat No.</label></div>
                                <div class="col-8">
                                    <input type="text" id="heat_no" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Product Code -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="product_code" class="form-label">Product Code</label></div>
                                <div class="col-8">
                                    <input type="text" id="product_code" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Thickness (mm) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="thickness" class="form-label">Thickness(mm)</label></div>
                                <div class="col-8">
                                    <input type="text" id="thickness" class="form-control form-control-sm" readonly>
                                </div>
                            </div>



                            <!-- Total Qty. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="total_qty" class="form-label">Total Qty.</label></div>
                                <div class="col-8">
                                    <input type="number" id="total_qty" class="form-control form-control-sm" readonly>
                                </div>
                            </div>

                            <!-- Film Size Unit (inch/cm) -->
                            <div class="row g-2 mb-1" id="film_size_unit_container">
                                <div class="col-4"><label class="form-label">Film Size</label></div>
                                <div class="col-8">
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="obs_film_size_unit_fix" id="obs_film_size_inch" value="inch" checked>
                                            <label class="form-check-label" for="obs_film_size_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="obs_film_size_unit_fix" id="obs_film_size_cm" value="cm">
                                            <label class="form-check-label" for="obs_film_size_cm">cm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remark -->
                            <!-- <div class="row g-2 mb-1">
                                <div class="col-4"><label for="remark" class="form-label">Remark</label></div>
                                <div class="col-8">
                                    <input type="text" id="remark" class="form-control form-control-sm" readonly>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <!-- Bottom Section: Sub-Grid Table -->
                    <div class="col-12 mt-3">
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-success" id="add_sub_detail_btn">Add</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table nowrap align-middle table-bordered details_table remove-reset-filter mb-0" id="ObservationSheetSubDetailTable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center action_col" style="width: 80px;">Actions</th>
                                        <th>Sr. No.</th>
                                        <th>Identification</th>
                                        <th>Location</th>
                                        <th>Source</th>
                                        <th>Film Brand</th>
                                        <th>Film Type</th>
                                        <th>Thickness(mm)</th>
                                        <th>SFD(mm)</th>
                                        <th>IQI Designation</th>
                                        <th>IQI Sensitivity</th>
                                        <th>Film Size</th>
                                        <th>No. of Film</th>
                                    </tr>
                                </thead>
                                <tbody id="sub_details_tbody">
                                    <tr><td colspan="12" class="text-center">No Sub Details Added</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveObsDetailEditBtn">Edit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
