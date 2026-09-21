 <div class="modal fade" id="PurchaseOrderModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="PurchaseOrderModalLabel">Purchase Order</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonPurchaseOrderForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id"> 
                    <input type="hidden" name="old_po_date" id="old_po_date">                                

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        {{-- <label class="form-label">Mode</label> --}}
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-4 align-items-center">

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="po_type_id" id="manual_mode" value="Manual" checked>
                                                <label class="form-check-label" for="manual_mode">
                                                    Manual
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="po_type_id" id="indent_mode" value="From Indent">
                                                <label class="form-check-label" for="indent_mode">
                                                    From Indent
                                                </label>
                                            </div>
                                            <button type="button" 
                                                class="btn btn-success btn-sm toggleModalBtn" 
                                                data-bs-target="#PurchaseOrderPendingModal" 
                                                id="pending_btn" disabled>
                                                Pending
                                            </button>

                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="supplier_name" class="form-label">
                                            PO No. <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey"
                                                id="po_sequence" name="po_sequence"
                                                style="max-width:50px" autofocus required>

                                            <input type="text" class="form-control skip-tab"
                                                id="po_number" name="po_number"
                                                tabindex="-1" readonly>
                                            <div class="invalid-tooltip">
                                                Enter PO No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="po_date" class="form-label">PO Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="po_date" name="po_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">
                                            Enter PO Date.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_supplier_name" name="po_supplier_id" id="po_supplier_id" required>
                                                <option value="">Select Supplier</option>
                                                @forelse(getSuppliers() as $supplier)
                                                    <option value="{{ $supplier->id }}" data-state-id="{{ $supplier->state_id }}">{{ $supplier->supplier_name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Supplier.
                                            </div>
                                            @if(hasAccess("supplier","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedSupplier(true)" data-bs-target="#SupplierModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                {{-- <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;"> --}}
                                <div class="row g-2 ml-2">
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Kind Attn. </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="po_kind_attn_id" id="po_kind_attn_id">
                                            <option value="">Select Kind Attn.</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Kind Attn.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Ref. No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="ref_no_date" name="ref_no_date">  
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Payment Terms</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="payment_terms" name="payment_terms">  
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 ml-2">
                                {{-- <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;"> --}}
                                    <div class="col-4">
                                    </div>
                                    <div class="col-8">
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Bill To <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                       
                                        <select class="js-example-basic-single" name="bill_to_location_id" id="bill_to_location_id" required>
                                            <option value="">Select Bill To</option>
                                            @forelse(getGSTBillLocations() as $location)
                                                <option value="{{ $location->location_id }}" data-state_id="{{ $location->location_state_id }}">{{ $location->location_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Bill To.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Ship To <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                       
                                        <select class="js-example-basic-single" name="ship_to_location_id" id="ship_to_location_id" required>
                                            <option value="">Select Ship To</option>
                                            @forelse(getLocations() as $location)
                                                <option value="{{ $location->location_id }}">{{ $location->location_name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Ship To.
                                        </div>
                                    </div>
                                </div>
                            </div>          
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary toggleButton" data-bs-target="#PurchaseOrderDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Purchase Order Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="PurchaseOrderDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_col">Actions</th>
                                                        {{-- <th scope="col">Purchase Indent No.</th>
                                                        <th scope="col">Purchase Indent Date</th> --}}
                                                        <th scope="col">Item</th>
                                                        <th scope="col">Item Group</th>
                                                        <th scope="col">Main Group</th>
                                                        <th scope="col">Stock </th>
                                                        {{-- <th scope="col">Pend. PI Qty.</th> --}}
                                                        <th scope="col">PO Qty.</th>
                                                        <th scope="col">Unit</th>
                                                        <th scope="col">Rate / Unit</th>
                                                        <th scope="col">Amount</th>
                                                        <th scope="col">Del. Date</th>
                                                        <th scope="col">Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noDetails">
                                                        <td colspan="10">No Purchase Order Details Added</td>
                                                    </tr>
                                                </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="8"></td>
                                                            <td id="totalAmount">0.00</td>
                                                            <td colspan="3"></td>
                                                        </tr>
                                                    </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                     
                                        <label for="basic_amount" class="form-label">Basic Amount</label>
                                    </div>
                                    <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="basic_amount" name="basic_amount"  autocomplete="off" readonly/>
                                    </div>
                                   
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>

                                    <div class="col-8">
                                        <div class="d-flex gap-4 align-items-center">

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="gst_type_fix_id" id="sgst_cgst" value="1" >
                                                <label class="form-check-label" for="sgst_cgst">
                                                    SGST + CGST
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="gst_type_fix_id" id="igst" value="2">
                                                <label class="form-check-label" for="igst">
                                                    IGST
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="gst_type_fix_id" id="none" value="3" checked>
                                                <label class="form-check-label" for="none">
                                                    None
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                
                                    <div class="col-4">
                                        <label class="form-label">SGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control sgst-field gst-fields" id="sgst_percentage" name ="sgst_percentage" onblur="formatPoints(this,2)">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab sgst-field disb" id="sgst_amount" name="sgst_amount" onblur="formatPoints(this,2)" readonly>
                                                   
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                
                                    <div class="col-4">
                                        <label class="form-label">CGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control cgst-field gst-fields" id="cgst_percentage" name="cgst_percentage" onblur="formatPoints(this,2)">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab cgst-field disb" id="cgst_amount" name="cgst_amount" onblur="formatPoints(this,2)" readonly>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 <div class="row g-2 mb-1">
                                
                                    <div class="col-4">
                                        <label class="form-label">IGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control igst-field gst-fields" id="igst_percentage" name="igst_percentage" onblur="formatPoints(this,2)">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab igst-field disb" id="igst_amount" name="igst_amount" onblur="formatPoints(this,2)" readonly>
                                                   
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">

                               

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Round Off </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="round_off_val" name="round_off_val" onblur="formatPoints(this,2)"  readonly/>  
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Net Amount </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="net_amount" name="net_amount" onblur="formatPoints(this,2)" readonly/>  
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label mt-1">Terms & Conditions </label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="po_terms_and_conditions" name="po_terms_and_conditions" rows="10"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control " id="po_sp_note" name="po_sp_note"/>  
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
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
                 @if(hasAccess("purchase_order","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("purchase_order","add"))
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
<script src="{{ URL::asset('views/js/purchase_order.js?ver='.getJsVersion()) }}"></script>
@endpush
