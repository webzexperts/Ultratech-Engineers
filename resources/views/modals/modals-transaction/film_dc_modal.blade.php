<div class="modal fade" id="FilmDCModal" aria-labelledby="FilmDCModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="FilmDCModalLabel">Film DC</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="commonFilmDCForm" class="needs-validation d-flex flex-column h-100" novalidate>
                @csrf
                <input type="hidden" name="id" id="id">

                <div class="modal-body">
                    <!-- Top Fields: 2-Column Layout -->
                    <div class="row mb-1 g-2">
                        <!-- Left Column (DC No., DC Date, Job Type, DC From Type, Film Size) -->
                        <div class="col-md-6">
                            <!-- DC No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_dc_sequence" class="form-label">DC No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control isNumberKey" id="film_dc_sequence" name="film_dc_sequence" style="max-width:80px;" autofocus required>
                                        <input type="text" class="form-control skip-tab" id="film_dc_no" name="film_dc_no" tabindex="-1" readonly>
                                        <div class="invalid-tooltip">Enter DC No.</div>
                                    </div>
                                </div>
                            </div>
                            <!-- DC Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="film_dc_date" class="form-label">DC Date <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control trans-date-picker" id="film_dc_date" name="film_dc_date" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter DC Date.</div>
                                </div>
                            </div>
                            <!-- Job Type (Below DC Date) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Job Type <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_non_welding" value="Non-Welding" checked>
                                            <label class="form-check-label" for="job_type_non_welding">Non-Welding</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_welding" value="Welding">
                                            <label class="form-check-label" for="job_type_welding">Welding</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- DC From Type (Below Job Type) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">DC From Type <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="from_type_id" id="from_type_rt" value="Test Report RT" checked>
                                            <label class="form-check-label" for="from_type_rt">RT Report</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="from_type_id" id="from_type_manual" value="Manual">
                                            <label class="form-check-label" for="from_type_manual">Manual</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Film Size Unit (Below DC From Type) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label class="form-label">Film Size <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex gap-3 align-items-center mt-1 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_unit_fix" id="film_size_unit_inch" value="inch" checked>
                                            <label class="form-check-label" for="film_size_unit_inch">inch</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="film_size_unit_fix" id="film_size_unit_cm" value="cm">
                                            <label class="form-check-label" for="film_size_unit_cm">cm</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column (Customer, Customer Client) -->
                        <div class="col-md-6">
                            <!-- Customer (Beside DC No.) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="customer_id" class="form-label">Customer <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 d-flex gap-2 position-relative align-items-center">
                                    <div class="flex-grow-1 position-relative" style="min-width: 0;">
                                        <select class="js-example-basic-single" name="customer_id" id="customer_id" required style="width: 100%;">
                                            <option value="">Select Customer</option>
                                        </select>
                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm toggleModalBtn flex-shrink-0" data-bs-target="#PendingRtForFilmDCModal" id="pending_btn" disabled style="white-space: nowrap;">Pending</button>
                                </div>
                            </div>
                            <!-- Customer Client (Beside DC Date) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="customer_client" class="form-label">Customer Client</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control" id="customer_client" name="customer_client" placeholder="Customer Client" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DETAILS GRID SECTION -->
                    <div class="card mt-2">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary" id="addDetailRowBtn" disabled>Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Film DC Details</b>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0 text-nowrap" id="DCDetailTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="action_col">Actions</th>
                                            <th scope="col">RT Report No.</th>
                                            <th scope="col">Report Date</th>
                                            <th scope="col">Part No. / Die No.</th>
                                            <th scope="col">Film Brand</th>
                                            <th scope="col" class="th_film_size_unit">Film Size (Inch)</th>
                                            <th scope="col">DC Qty.</th>
                                            <th scope="col">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="details_tbody">
                                         <tr id="noDetails">
                                            <td colspan="8" class="text-center" id="noDetailsCell">
                                                No Film DC Details Added
                                            </td>
                                         </tr>
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                    </div>

                    <!-- Lower Fields: Only Total Quantity & Sp. Note -->
                    <div class="row mb-1 g-2 mt-2">
                        <!-- Column 1: Total Quantity & Sp. Note -->
                        <div class="col-md-4">
                            <!-- Total Quantity -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="total_qty" class="form-label">Total Quantity</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="total_qty" name="total_qty" readonly tabindex="-1">
                                </div>
                            </div>

                                    <!-- Sp. Note -->
                            <div class="row g-2 align-items-start mb-1">
                                <div class="col-4">
                                    <label for="sp_note" class="form-label mt-1">Sp. Note</label>
                                </div>
                                <div class="col-8">
                                    <textarea class="form-control" name="sp_note" id="sp_note" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Hidden fields for backend calculations -->
                            <input type="hidden" id="total_square_inch" name="total_square_inch" value="0.000">
                            <input type="hidden" id="film_size_for_print" name="film_size_for_print">
                        </div>
                    </div>
                </div>

                <!-- FOOTER BUTTONS -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("film_dc","print"))
                        <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                    @endif
                    @if(hasAccess("film_dc","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('modals.modals-pending.pending_rt_for_film_dc_modal')
@include('modals.modals-details.film_dc_detail_modal')

@push('script-modal')
<script>
    let loginUserId = {{ auth()->id() }};
    let checkFileRoute = "{{ route('check-file_exists') }}";
</script>
<script src="{{ URL::asset('views/js/film_dc.js?ver='.getJsVersion()) }}"></script>
@endpush
