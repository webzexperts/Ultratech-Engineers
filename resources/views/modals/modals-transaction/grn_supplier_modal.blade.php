 <div class="modal fade" id="GrnModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="GrnModalLabel">Goods Received Note (Supplier)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonGrnForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="grn_type_id" id="grn_type_id" value="Against PO" checked>
                                                <label class="form-check-label" for="grn_type_id">
                                                    Against PO
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="grn_type_id" id="grn_type_id" value="Manual">
                                                <label class="form-check-label" for="grn_type_id">
                                                    Manual
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="grn_type_id" id="grn_type_id" value="Against DC">
                                                <label class="form-check-label" for="grn_type_id">
                                                    Against DC
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMN 1 -->
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">GRN No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="grn_sequence" name="grn_sequence" style="max-width:80px" autofocus required>
                                            <input type="text" class="form-control" id="grn_number" name="grn_number" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter GRN No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label"> GRN Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control trans-date-picker" id="grn_date" name="grn_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter GRN Date
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                    </div>
                                </div>
                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single"
                                                name="grn_supplier_id"
                                                id="grn_supplier_id"
                                                required>
                                            <option value="">Select Supplier</option>
                                                
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Supplier
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn pending_detail" id="pending_btn" disabled >
                                            Pending
                                        </button> 
                                    </div>
                                </div>
                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Challan / Invoice No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="grn_challan_number" name="grn_challan_number" required>
                                        <div class="invalid-tooltip">
                                            Enter Challan / Invoice No. 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                    </div>
                                </div>
                                <div class="row g-1 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Challan / Invoice Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <input type="text" class="form-control date-picker" id="grn_challan_date" name="grn_challan_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Challan / Invoice Date
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary add_detail" data-bs-target="#GrnDetailsModal">Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>GRN Details</b>
                            </h4>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="GrnDetailTable">
                                    <thead>
                                        <tr>
                                            <th>Actions</th>
                                            <th>PO No.</th>
                                            <th>PO Date</th>
                                            <th>DC No.</th>
                                            <th>DC Date</th>
                                            <th>Item</th>
                                            <th>Item Group</th>
                                            <th>Main Group</th>
                                            <th>Sr. No.</th>
                                            <th>Pending PO / DC Qty.</th>
                                            <th>GRN Qty.</th>
                                            <th>Unit</th>
                                            <th>Rate/Unit</th>
                                            <th>Amount</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="15" class="text-center" id="noDetails">
                                                No GRN Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="13"></td>
                                            <td id="totalAmount"></td>
                                            <td colspan="1"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="grn_total_amount" class="form-label">Total Amount </label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative">
                                        <input type="text" class="form-control isNumberKey skip-tab" id="grn_total_amount" name="grn_total_amount" onblur="formatPoints(this,2)" autocomplete="off" readonly tabindex="-1" />
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="mode_of_transport" class="form-label">Mode of Transport </label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" class="form-control" id="mode_of_transport" name="mode_of_transport" autocomplete="off" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="grn_transporter" class="form-label">Transporter </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="grn_transporter" name="grn_transporter" autocomplete="off"   />
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="grn_vehicle_number" class="form-label">Vehicle No. </label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" class="form-control" id="grn_vehicle_number" name="grn_vehicle_number" autocomplete="off" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row ml-2 g-2 mb-1"> 
                                    <div class="col-4 justify-content-start">
                                        <label for="grn_special_note" class="form-label justi mt-1">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" name="grn_special_note" id="grn_special_note"></textarea>
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Prepared By </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly >
                                            <option value="">Select Prepared By</option>
                                            @forelse(getUsers() as $users)
                                                <!-- <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->user_name }}</option> -->
                                                <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->person_name }}</option>
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
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                 @if(hasAccess("grn_supplier","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("grn_supplier","add"))
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
<script src="{{ URL::asset('views/js/grn_supplier.js?ver='.getJsVersion()) }}"></script>
@endpush
