<div class="modal fade" id="UTReportDetailsModal" aria-labelledby="UTReportDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="UTReportDetailsModalLabel">UT Test Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="UTReportDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="det_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="det_form_index" />
                    <input type="hidden" name="row_index" id="det_row_index" />  
                    <input type="hidden" name="test_report_ut_details_id" id="test_report_ut_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- UT Test No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="det_ut_test_no" class="form-label">UT Test No. <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="ut_test_no" id="det_ut_test_no" class="form-control" required>
                                    <div class="invalid-tooltip">Enter UT Test No.</div>
                                </div>
                            </div>
                            <!-- Heat No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="det_heat_no" class="form-label">Heat No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="heat_no" id="det_heat_no" class="form-control">
                                </div>
                            </div>
                            <!-- Quantity -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="det_quantity" class="form-label">Quantity <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="number" name="quantity" id="det_quantity" class="form-control isInteger" required>
                                    <div class="invalid-tooltip">Enter Quantity.</div>
                                </div>
                            </div>
                            <!-- Discontinuity Evaluation -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="det_discontinuity_evaluation" class="form-label">Discontinuity Evaluation <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="discontinuity_evaluation" id="det_discontinuity_evaluation" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Discontinuity Evaluation.</div>
                                </div>
                            </div>
                            <!-- Result Dropdown -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_result_id" class="form-label">Result <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single suggest_film_result mst_film_result" name="detail_result_id" id="detail_result_id" required style="width: 100%;">
                                        <option value="">Select Result</option>
                                        @forelse(getFilmResults() as $fr)
                                            <option value="{{ $fr->film_result_id }}">{{ $fr->film_result_name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Result.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitDetailsRowBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
