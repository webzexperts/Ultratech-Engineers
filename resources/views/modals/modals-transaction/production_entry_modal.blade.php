<div class="modal fade" id="ProductionEntryModal" aria-labelledby="ProductionEntryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="ProductionEntryModalLabel">Production Entry</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="commonProductionEntryForm" class="needs-validation h-100 d-flex flex-column" novalidate>
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">

                    {{-- ============ Master Fields Section ============ --}}
                    <div class="row g-3 mb-3">
                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <!-- Sr No -->
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-4">
                                    <label for="production_entry_sequence" class="form-label">Sr. No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-2">
                                        <input type="text"
                                               class="form-control isNumberKey"
                                               id="production_entry_sequence"
                                               name="production_entry_sequence"
                                               style="max-width:70px;"
                                               autofocus
                                               required>
                                        <input type="text"
                                               class="form-control skip-tab"
                                               id="production_entry_no"
                                               name="production_entry_no"
                                               tabindex="-1"
                                               readonly>
                                        <div class="invalid-tooltip">Enter Sr. No.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <!-- Date -->
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-4">
                                    <label for="production_entry_date" class="form-label">Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="text"
                                           class="form-control trans-date-picker"
                                           id="production_entry_date"
                                           name="production_entry_date"
                                           autocomplete="off"
                                           required>
                                    <div class="invalid-tooltip">Enter Date.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="col-md-4">
                            <!-- Enclosure Name -->
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-4">
                                    <label for="enclosure_id" class="form-label">Enclosure Name <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single"
                                            name="enclosure_id"
                                            id="enclosure_id"
                                            required>
                                        <option value="">Select Enclosure Name</option>
                                        @forelse(getCurrentLocationEnclosures() as $enc)
                                            <option value="{{ $enc->enclosure_id }}">
                                                {{ $enc->enclosure_name }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Enclosure Name.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ============ Detail Grid Section ============ --}}
                    <div class="card mt-3">
                        <div class="card-header align-items-center d-flex pt-0 pb-1">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary toggleButton" id="open_details_modal_btn">Add</button>
                            </div>
                            <h5 class="card-title mb-0 flex-grow-1 ml-2"><b>Production Entry Details</b></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle table-nowrap mb-0" id="ProductionEntryDetailTable" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th class="action_col">Actions</th>
                                            <th>Shift</th>
                                            <th>Source</th>
                                            <th>Item Group</th>
                                            <th>Item Name</th>
                                            <th class="text-end">Stock Qty.</th>
                                            <th>Unit</th>
                                            <th class="text-end">Production Qty.</th>
                                            <th class="text-end">Retake Qty.</th>
                                            <th class="text-end">Wastage Qty.</th>
                                            <th class="text-end">Total Cons. Qty.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="11" id="noDetails" class="text-center">No Production Entry Details Added</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ============ Master Totals & Remark Section ============ --}}
                    <div class="row g-3 mt-3">
                        <!-- Total Consumption -->
                        <div class="col-md-4">
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-4">
                                    <label for="total_sq_in" class="form-label">Total Consumption</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text"
                                           class="form-control skip-tab w-100"
                                           id="total_sq_in"
                                           name="total_sq_in"
                                           tabindex="-1"
                                           readonly>
                                    <span class="text-nowrap ms-2" id="master_total_unit">SQIN</span>
                                </div>
                            </div>
                        </div>

                        <!-- Remark -->
                        <div class="col-md-4">
                            <div class="row g-2 mb-2 align-items-start">
                                <div class="col-4">
                                    <label for="remark" class="form-label">Remark</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control"
                                              id="remark"
                                              name="remark"
                                              rows="3"
                                              maxlength="8000"
                                              ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>

                    @if(hasAccess("production_entry","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>

        </div>
    </div>
</div>

@push('script-modal')
<script src="{{ URL::asset('views/js/production_entry.js?ver='.getJsVersion()) }}"></script>
@endpush