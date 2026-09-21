<div class="modal fade" id="CustomerDCNonReturnableModal" aria-labelledby="CustomerDCNonReturnableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="CustomerDCNonReturnableModalLabel">Material Outward</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonCustomerDCForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <!-- TOP SECTION: HEADER FIELDS matching Supplier DC Layout (col-4 label, col-8 input) -->
                    <div class="row">
                        <div class="row g-1">
                            <!-- COLUMN 1: DC No. & DC Date -->
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">DC No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="customer_dc_non_returnable_sequence" name="customer_dc_non_returnable_sequence" style="max-width:50px" autofocus required>
                                            <input type="text" class="form-control skip-tab" id="customer_dc_non_returnable_no" name="customer_dc_non_returnable_no" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter DC No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                               
                            </div>

                            <!-- COLUMN 2: Customer + Pending Button -->
                            <div class="col-md-4">
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">DC Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <input type="text" class="form-control trans-date-picker" id="customer_dc_non_returnable_date" name="customer_dc_non_returnable_date" required autocomplete="off" value="{{ date('d/m/Y') }}">
                                        <div class="invalid-tooltip">
                                            Enter DC Date.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single" name="customer_id" id="customer_id" required>
                                            <option value="">Select Customer</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Customer.
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn pending_detail" id="pending_btn" disabled data-bs-target="#PendingInwardForCustomerDcModal">
                                            Pending
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DETAILS GRID SECTION -->
                    <div class="card mt-2">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Material Outward Details</b>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive details-action" style="max-height: 300px; overflow-y: auto !important;">
                                <table class="table table-bordered align-middle mb-0 text-nowrap" id="DCDetailTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="action_col" style="width: 60px;">Actions</th>
                                            <th scope="col">In. No.</th>
                                            <th scope="col">In. Date</th>
                                            <th scope="col">DC No.</th>
                                            <th scope="col">DC Date</th>
                                            <th scope="col">PO No.</th>
                                            <th scope="col">PO Date</th>
                                            <th scope="col">Type of Test</th>
                                            <th scope="col">Type of Job</th>
                                            <th scope="col">Job Description</th>
                                            <th scope="col">Part No.</th>
                                            <th scope="col">Drg. No.</th>
                                            <th scope="col">Material</th>
                                            <th scope="col">Heat No.</th>
                                            <th scope="col">RT No.</th>
                                            <th scope="col">Product Code</th>
                                            <th scope="col">In. Qty.</th>
                                            <th scope="col">Pend. Qty.</th>
                                            <th scope="col" style="min-width: 110px;">DC Qty. <sup class="astric">*</sup></th>
                                            <th scope="col" style="min-width: 180px;">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                         <tr id="noDetails">
                                            <td colspan="20" class="text-center" id="noDetailsCell">
                                                No Material Outward Details Added
                                            </td>
                                         </tr>
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>

                     <!-- LOWER FOOTER FIELDS matching Supplier DC 3-column side-by-side layout -->
                     <div class="row mt-2">
                         <div class="row g-1">
                             <!-- COLUMN 1: Total Qty & Vehicle No -->
                             <div class="col-md-4">
                                 <div class="row g-2 mb-1">
                                     <div class="col-4">
                                         <label class="form-label">Total Qty.</label>
                                     </div>
                                     <div class="col-8">
                                         <input type="text" class="form-control skip-tab" id="total_qty" name="total_qty" readonly value="">
                                     </div>
                                 </div>
                                 <div class="row g-2 mb-1">
                                     <div class="col-4 justify-content-start">
                                         <label class="form-label">Vehicle No.</label>
                                     </div>
                                      <div class="col-8 position-relative">
                                          <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" onkeyup="suggestVehicleNo(event, this)" autocomplete="off">
                                          <div id="vehicle_no_list" class="suggestion_list"></div>
                                      </div>
                                 </div>
                             </div>

                             <!-- COLUMN 2: Mode of Transport & Sp. Note -->
                             <div class="col-md-4">
                                 <div class="row g-2 ml-2 mb-1">
                                     <div class="col-4 justify-content-start">
                                         <label class="form-label">Mode of Transport</label>
                                     </div>
                                     <div class="col-8 position-relative">
                                         <input type="text" class="form-control" id="mode_of_transport" name="mode_of_transport" onkeyup="suggestModeOfTransport(event, this)" autocomplete="off">
                                         <div id="mode_of_transport_list" class="suggestion_list"></div>
                                     </div>
                                 </div>
                                 <div class="row g-2 ml-2 mb-1">
                                     <div class="col-4 justify-content-start">
                                         <label class="form-label">Sp. Note</label>
                                     </div>
                                     <div class="col-8 position-relative">
                                         <!-- <textarea class="form-control" id="sp_note" name="sp_note" rows="3" onkeyup="suggestSpNote(event, this)" autocomplete="off"></textarea> -->
                                         <textarea class="form-control" id="sp_note" name="sp_note" rows="3"  autocomplete="off"></textarea>
                                         <div id="sp_note_list" class="suggestion_list"></div>
                                     </div>
                                 </div>
                             </div>

                             <!-- COLUMN 3: Transporter & Prepared By -->
                             <div class="col-md-4">
                                 <div class="row g-2 ml-2 mb-1">
                                     <div class="col-4 justify-content-start">
                                         <label class="form-label">Transporter</label>
                                     </div>
                                     <div class="col-8 position-relative">
                                         <input type="text" class="form-control" id="transporter" name="transporter" onkeyup="suggestTransporter(event, this)" autocomplete="off">
                                         <div id="transporter_list" class="suggestion_list"></div>
                                     </div>
                                 </div>
                                  <div class="row g-2 ml-2 mb-1">
                                      <div class="col-4">
                                          <label class="form-label">Prepared By</label>
                                      </div>
                                      <div class="col-8">
                                          <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly>
                                              <option value="">Select Prepared By</option>
                                              @forelse(getUsers() as $users)
                                                  <option value="{{ $users->id }}" {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->person_name }}</option>
                                              @empty
                                              @endforelse
                                          </select>
                                          <div class="invalid-tooltip">
                                              Select Prepared By.
                                          </div>
                                      </div>
                                  </div>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- FOOTER BUTTONS MATCHING SYSTEM TRANSACTION MODALS -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("customer_dc_non_returnable","print"))
                        <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                    @endif
                    @if(hasAccess("customer_dc_non_returnable","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>
@push('script-modal')
<script>
    let loginUserId = {{ auth()->id() }};
    let checkFileRoute = "{{ route('check-file_exists') }}";
</script>
<script src="{{ URL::asset('views/js/customer_dc_non_returnable.js?ver='.getJsVersion()) }}"></script>
@endpush

