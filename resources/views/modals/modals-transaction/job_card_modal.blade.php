<div class="modal fade" id="JobCardModal" aria-labelledby="JobCardModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="JobCardModalLabel">Job Card</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonJobCardForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <!-- Top Fields: 3-Column Structured Layout -->
                    <div class="row mb-1 g-2">
                        <!-- Column 1: Jobcard No & Jobcard Date -->
                        <div class="col-md-4">
                            <!-- Jobcard No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="job_card_sequence" class="form-label">Jobcard No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control isNumberKey" id="job_card_sequence" name="job_card_sequence" style="max-width:80px;" autofocus required autocomplete="off">
                                        <input type="text" class="form-control skip-tab" id="job_card_no" name="job_card_no" tabindex="-1" readonly required autocomplete="off">
                                        <div class="invalid-tooltip">Enter Jobcard No.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Jobcard Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="job_card_date" class="form-label">Jobcard Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control trans-date-picker" id="job_card_date" name="job_card_date" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter Jobcard Date.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Shift & Incharge -->
                        <div class="col-md-4">
                            <!-- Shift -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="shift_id" class="form-label">Shift <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" id="shift_id" name="shift_id" required style="width: 100%;">
                                        <option value="">Select Shift</option>
                                        @foreach(getShifts() as $shift)
                                            <option value="{{ $shift->shift_id }}">{{ $shift->shift_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-tooltip">Select Shift.</div>
                                </div>
                            </div>

                            <!-- Incharge -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="incharge_authority_person_id" class="form-label">Incharge <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" id="incharge_authority_person_id" name="incharge_authority_person_id" required style="width: 100%;">
                                        <option value="">Select Incharge</option>
                                        @foreach(getAuthorityPersonsIncharge() as $incharge)
                                            <option value="{{ $incharge->authority_person_id }}">{{ $incharge->operator }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-tooltip">Select Incharge.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 3: Radiographer & Camera -->
                        <div class="col-md-4">
                            <!-- Radiographer -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="radiographer_authority_person_id" class="form-label">Radiographer <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" id="radiographer_authority_person_id" name="radiographer_authority_person_id" required style="width: 100%;">
                                        <option value="">Select Radiographer</option>
                                        @foreach(getAuthorityPersonsIncharge() as $incharge)
                                            <option value="{{ $incharge->authority_person_id }}">{{ $incharge->operator }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-tooltip">Select Radiographer.</div>
                                </div>
                            </div>

                            <!-- Camera -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="rt_camera_id" class="form-label">Camera <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <select class="js-example-basic-single" id="rt_camera_id" name="rt_camera_id" required style="width: 100%;">
                                        <option value="">Select Camera</option>
                                        @foreach(getRTCameras() as $cam)
                                            <option value="{{ $cam->rt_camera_id }}">{{ !empty($cam->name_for_display) ? $cam->name_for_display : ($cam->rt_camera_name . ' (' . $cam->rt_serial_no . ')') }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-tooltip">Select Camera.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid Card Section -->
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1"><b>Job Card Details</b></h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table nowrap align-middle table-bordered" id="JobCardWorksheetTable" data-exclude-search="0,1" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;" class="text-center">
                                                <input type="checkbox" name="checkall_worksheet" class="simple-check" id="checkall_worksheet"/>
                                            </th>
                                            <th style="width: 120px;">Priority</th>
                                            <th>WS No.</th>
                                            <th>WS Date</th>
                                            <th>Customer</th>
                                            <th>Job Description</th>
                                            <th>Die No.</th>
                                            <th>Drg No.</th>
                                            <th>Heat No.</th>
                                            <th>Material</th>
                                            <th>Qty.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="job_card_details_tbody">
                                        <tr><td colspan="11" class="text-center" id="noDetails">No pending worksheets found.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("job_card", "add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>
