@include('modals.modals-pending.pending_inward_for_observation_sheet_modal')

<div class="modal fade" id="ObservationSheetModal" aria-labelledby="ObservationSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ObservationSheetModalLabel">Observation Sheet</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonObservationSheetForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="row">
                        <div class="row g-1">
                            {{-- Row 1: Sr No (Sequence) --}}
                            <div class="col-md-6">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="observation_sheet_sequence" class="form-label">Sr. No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey" id="observation_sheet_sequence" name="observation_sheet_sequence" style="max-width:60px" autofocus required>
                                            <input type="text" class="form-control skip-tab" id="observation_sheet_no" name="observation_sheet_no" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">Enter Sr. No.</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Row 2: Date --}}
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="observation_sheet_date" class="form-label">Observation Sheet Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="observation_sheet_date" name="observation_sheet_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">Enter Observation Sheet Date.</div>
                                    </div>
                                </div>

                                {{-- Row 3: Customer & Pending --}}
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative align-items-center">
                                            <!-- <div class="flex-grow-1" style="min-width: 0;"> -->
                                                <select class="js-example-basic-single suggest_customer_name" name="customer_id" id="customer_id" required style="width: 100%;">
                                                    <option value="">Select Customer</option>
                                                </select>
                                                <div class="invalid-tooltip">Select Customer.</div>
                                            <!-- </div> -->
                                            <button type="button" class="btn btn-success btn-sm text-nowrap toggleModalBtn" id="load_pending_reports" data-bs-target="#PendingInwardForObservationSheetModal" disabled>Pending</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Grid -->
                    <div class="card mb-1 mt-1">
                        <div class="card-header d-flex align-items-center py-2">
                            <!-- <button type="button" class="btn btn-success btn-sm me-2" id="add_detail_btn" data-bs-target="#MaterialInwardDetailsModal">Add</button> -->
                            <h4 class="card-title mb-0 flex-grow-1"><b>Observation Sheet Details</b></h4>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle table-nowrap mb-0" id="ObservationSheetDetailTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center action_col" style="width: 80px;">Actions</th>
                                            <th>Report No.</th>
                                            <th>Nature</th>
                                            <th scope="col">Type of Test</th>
                                            <th scope="col">In. No.</th>
                                            <th scope="col">In. Date</th>
                                            <th scope="col">NABL</th>
                                            <th scope="col">Test At</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">DC No.</th>
                                            <th scope="col">DC Date</th>
                                            <th scope="col">PO No.</th>
                                            <th scope="col">PO Date</th>
                                            <th scope="col">Type of Job</th>
                                            <th scope="col">Job Desc.</th>
                                            <th scope="col">Part No.</th>
                                            <th scope="col">Drg. No.</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Heat No.</th>
                                            <th scope="col">Product Code</th>
                                            <th scope="col">Qty.</th>
                                            <!-- <th scope="col">Remark</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="details_tbody">
                                        <tr id="noDetails"><td colspan="22" class="text-center">No Observation Sheet Details Added</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Prepared By</label>
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

                                <div class="row g-1 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="special_note" name="special_note" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn" form="commonObservationSheetForm">Submit</button>
                <button type="submit" class="btn btn-primary" id="updatebtn" form="commonObservationSheetForm" style="display:none;">Update</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("observation_sheet","print"))
                <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("observation_sheet","add"))
                <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>