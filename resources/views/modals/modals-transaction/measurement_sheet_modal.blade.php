<div class="modal fade" id="MeasurementSheetModal" aria-labelledby="MeasurementSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="MeasurementSheetModalLabel">Measurement Sheet</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonMeasurementSheetForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Sheet No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2">
                                            <div class="d-flex gap-2 flex-grow-1 position-relative">
                                                <input type="text" class="form-control isNumberKey" id="measurement_sheet_sequence" name="measurement_sheet_sequence" style="max-width:80px" autofocus required>
                                                <input type="text" class="form-control skip-tab" id="measurement_sheet_no" name="measurement_sheet_no" tabindex="-1" readonly>
                                                <div class="invalid-tooltip">Enter Sheet No.</div>
                                            </div>
                                            <div class="position-relative">
                                                <input type="text" class="form-control trans-date-picker" id="measurement_sheet_date" name="measurement_sheet_date" required autocomplete="off" style="max-width:100px">
                                                <div class="invalid-tooltip">Enter Sheet Date.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative d-flex gap-2">
                                        <select class="js-example-basic-single" name="customer_id" id="customer_id" required>
                                            <option value="">Select Customer</option>
                                        </select>
                                        <button type="button" class="btn btn-success text-nowrap" id="load_pending_reports" disabled>RT Report</button>
                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Grid with Standard Header Label -->
                    <div class="card mb-2">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1"><b>Measurement Sheet Details</b></h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <!-- <div class="table-responsive" style="max-height: 400px; overflow-y: auto;"> -->
                                <table class="table table-bordered align-middle table-nowrap mb-0" id="details_table">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center action_col" style="width: 80px;">Actions</th>
                                            <th scope="col">NABL</th>
                                            <th scope="col">Type</th>
                                            <th scope="col" style="min-width: 200px;">RT Report No.</th>
                                            <th scope="col">Rev. No.</th>
                                            <th scope="col">Report Date</th>
                                            <th scope="col">RT No.</th>
                                            <th scope="col">Heat No.</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Type of Job</th>
                                            <th scope="col">Job Description</th>
                                            <th scope="col">Part No.</th>
                                            <th scope="col">Drg. No.</th>
                                            <th scope="col">Product Code</th>
                                            <th scope="col">Ir-192 SQIN</th>
                                            <th scope="col">Co-60 SQIN</th>
                                            <th scope="col">X-Ray SQIN</th>
                                            <th scope="col">Ir-192 Repair</th>
                                            <th scope="col">Co-60 Repair</th>
                                            <th scope="col">X-Ray Repair</th>
                                            <th scope="col">Customer DC No.</th>
                                            <th scope="col">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="details_tbody">
                                        <!-- Loaded dynamically via javascript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total Ir-192 SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_ir_192_sqin" name="total_ir_192_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total Co-60 SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_co_60_sqin" name="total_co_60_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total X-Ray SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_x_ray_sqin" name="total_x_ray_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="from-label">Prepared By</label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly tabindex="-1">
                                            <option value="">Select Prepared By</option>
                                            @forelse(getUsers() as $user)
                                            <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">Select Prepared By.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total Ir-192 Repair SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_ir_192_repair_sqin" name="total_ir_192_repair_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total Co-60 Repair SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_co_60_repair_sqin" name="total_co_60_repair_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Total X-Ray Repair SQIN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="total_x_ray_repair_sqin" name="total_x_ray_repair_sqin" value="0.00" readonly tabindex="-1">
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label class="from-label">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="sp_note" name="sp_note" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn" form="commonMeasurementSheetForm">Submit</button>
                <button type="submit" class="btn btn-primary" id="updatebtn" form="commonMeasurementSheetForm" style="display:none;">Update</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("measurement_sheet","print"))
                <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("measurement_sheet","add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
