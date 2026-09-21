 <div class="modal fade" id="SupplierDCModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="SupplierDCModalLabel">Supplier DC</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonSupplierDCForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">

                                <!-- <div class="row g-2 mb-1">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-1 align-items-center">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="sup_dc_type_id" value="Non Returnable - Manual" checked>
                                                <label class="form-check-label" for="sup_dc_type_id">
                                                    Non Returnable - Manual
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="sup_dc_type_id" value="Returnable - Manual">
                                                <label class="form-check-label" for="sup_dc_type_id">
                                                    Returnable - Manual
                                                </label>
                                            </div>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="sup_dc_type_id" value="Returnable - Service PO">
                                                <label class="form-check-label" for="sup_dc_type_id">
                                                    Returnable - Service PO
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>

                                    <div class="col-8">
                                        <div class="d-flex gap-2 align-items-center">

                                            <div class="form-check text-nowrap">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="non_returnable" value="Non Returnable - Manual" checked >
                                                <label class="form-check-label" for="non_returnable">
                                                    Non Returnable - Manual
                                                </label>
                                            </div>

                                            <div class="form-check text-nowrap">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="non_returnable_cut_films" value="SQIN from Prod. Area">
                                                <label class="form-check-label" for="non_returnable_cut_films">
                                                    SQIN from Prod. Area
                                                </label>
                                            </div>

                                            <div class="form-check text-nowrap">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="returnable_manual" value="Returnable - Manual">
                                                <label class="form-check-label" for="returnable_manual" >
                                                    Returnable - Manual
                                                </label>
                                            </div>

                                            <div class="form-check text-nowrap">
                                                <input class="form-check-input" type="radio" name="sup_dc_type_id" id="returnable_service" value="Returnable - Service PO">
                                                <label class="form-check-label" for="returnable_service">
                                                    Returnable - Service PO
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>  
                        </div> 
                        <div class="row g-1">
                            <div class="col-md-4">

                                <!-- COLUMN 1 -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">DC No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="sup_dc_sequence" name="sup_dc_sequence" style="max-width:80px" autofocus required>
                                            <input type="text" class="form-control skip-tab" id="sup_dc_number" name="sup_dc_number" tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter DC No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="from-label"> DC Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control trans-date-picker" id="sup_dc_date" name="sup_dc_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter DC Date
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single"
                                                name="supplier_id"
                                                id="supplier_id"
                                                required>
                                            <option value="">Select Supplier</option>
                                                
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Supplier
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn pending_detail" id="pending_btn" disabled data-bs-target="#SupplierDCPendingModal">
                                            Pending
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Ref. No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ref_no_date" name="ref_no_date" >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary add_detail" data-bs-target="#SupplierDCDetailsModal">Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Supplier DC Details</b>
                            </h4>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="SupplierDCDetailTable">
                                    <thead>
                                        <tr>
                                            <th>Actions</th>
                                            <th>Service PO No.</th>
                                            <th>Service PO Date</th>
                                            <th>Item</th>
                                            <th>Item Group</th>
                                            <th>Main Group</th>
                                            <th>Sr. No.</th>
                                            <th>Stock</th>
                                            <th>Pend. Service PO Qty.</th>
                                            <th>Return Qty. </th>
                                            <th>Unit</th>
                                            <th>AERB No.</th>
                                            <th>Application No.</th>
                                            <th>Movement Approval</th>
                                            <th>Validity</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="18" class="text-center" id="noDetails">
                                                No Supplier DC Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="mode_of_transport" class="form-label">Mode of Transport </label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" class="form-control" id="mode_of_transport" name="mode_of_transport" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="row  g-2 mb-1">
                                    <div class="col-4">
                                        <label for="transporter" class="form-label">Transporter </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="transporter" name="transporter" autocomplete="off"   />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="vehicle_no" class="form-label">Vehicle No. </label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1"> 
                                    <div class="col-4 justify-content-start">
                                        <label for="sp_note" class="form-label justi mt-1">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" name="sp_note" id="sp_note"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                @if(hasAccess("supplier_dc","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("supplier_dc","add"))
                 <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


 @push('script-modal')
 @php
    $allItems = getItemsForSupDC(); 
@endphp
  <script>
    let loginUserId = {{ auth()->id() }};
    let allItems = @json($allItems);
    let itemTypeMap = @json(getItemType());
    let checkFileRoute = "{{ route('check-file_exists') }}";
    let storageUrl = "{{ asset('storage') }}";
</script>
<script src="{{ URL::asset('views/js/supplier_dc.js?ver='.getJsVersion()) }}"></script>
@endpush
