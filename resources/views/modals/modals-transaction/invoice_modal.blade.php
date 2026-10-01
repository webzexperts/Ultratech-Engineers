<div class="modal fade" id="InvoiceModal" aria-labelledby="InvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="InvoiceModalLabel">Invoice</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonInvoiceForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <!-- ========================================================================= -->
                    <!-- TOP SECTION: 3-Column Layout Matching Desktop Photo                       -->
                    <!-- ========================================================================= -->
                    <div class="row">
                        <div class="row g-1">
                            
                            <!-- ---------------- COLUMN 1 (LEFT) ---------------- -->
                            <div class="col-md-4">
                                <!-- 1. Ref. No. & Ref Date -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="ref_title" name="ref_title" value="Ref. No.">
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2">
                                            <input type="text" class="form-control" id="ref_no" name="ref_no">
                                            <input type="text" class="form-control trans-date-picker" id="ref_date" name="ref_date" autocomplete="off" style="max-width: 120px;">
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Source: (•) From Film DC  ( ) Manual -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="source_type" id="source_film_dc" value="From Film DC" checked>
                                                <label class="form-check-label" for="source_film_dc">From Film DC</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="source_type" id="source_manual" value="Manual">
                                                <label class="form-check-label" for="source_manual">Manual</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Customer -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="customer_id" class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <select class="js-example-basic-single suggest_customer_name" name="customer_id" id="customer_id" required style="width: 100%;">
                                            <option value="">Select Customer</option>
                                            @foreach(getCustomers() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-tooltip">Select Customer.</div>
                                    </div>
                                </div>

                                <!-- 4. Tax Type + Pending Button -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="tax_type" class="form-label">Tax Type <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <select class="form-select js-example-basic-single" name="tax_type" id="tax_type" required style="width: 100%;">
                                                <option value="SGST + CGST" selected>SGST + CGST</option>
                                                <option value="IGST">IGST</option>
                                                <option value="Exempted">Exempted</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn flex-shrink-0" data-bs-target="#PendingFilmDcForInvoiceModal" id="pending_btn" disabled style="white-space: nowrap;">Pending</button>
                                    </div>
                                </div>

                                <!-- 5. Bill No. (Sequence + Formatted Number + Bill Date) -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="invoice_sequence" class="form-label">Bill No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <div class="d-flex gap-1">
                                            <input type="text" class="form-control isNumberKey" id="invoice_sequence" name="invoice_sequence" style="max-width:55px;" autofocus required>
                                            <input type="text" class="form-control skip-tab" id="invoice_no" name="invoice_no" tabindex="-1" readonly>
                                            <input type="text" class="form-control trans-date-picker" id="invoice_date" name="invoice_date" autocomplete="off" required value="{{ date('d/m/Y') }}" style="max-width: 105px;">
                                            <div class="invalid-tooltip">Enter Bill No.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. SAC -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="sac_id" class="form-label">SAC</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="sac_id" id="sac_id" style="width: 100%;">
                                            <option value="">Select SAC</option>
                                            @foreach(getSAC() as $sac)
                                                <option value="{{ $sac->gc_id }}">{{ $sac->gc_sac }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ---------------- COLUMN 2 (MIDDLE) ---------------- -->
                            <div class="col-md-4">
                                <!-- 1. Job Type : (•) Casting  ( ) Fabrication -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Job Type <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_casting" value="Casting" checked>
                                                <label class="form-check-label" for="job_type_casting">Casting</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="job_type_fix" id="job_type_fabrication" value="Fabrication">
                                                <label class="form-check-label" for="job_type_fabrication">Fabrication</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Film Size in : (•) inch  ( ) cm -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Film Size in <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_size_unit_fix" id="film_size_inch" value="inch" checked>
                                                <label class="form-check-label" for="film_size_inch">inch</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="film_size_unit_fix" id="film_size_cm" value="cm">
                                                <label class="form-check-label" for="film_size_cm">cm</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Customer's Client (Multiline Textarea with justify-content-start) -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="customer_client" class="form-label mt-1">Customer's Client</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="customer_client" name="customer_client" rows="4"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ---------------- COLUMN 3 (RIGHT: Place of Supply) ---------------- -->
                            <div class="col-md-4">
                                <!-- Section Heading: Place of Supply -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-12 text-center">
                                        <span class="fw-bold text-primary" style="text-decoration: underline;">Place of Supply</span>
                                    </div>
                                </div>

                                <!-- 1. POS Customer -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="pos_customer" class="form-label">Customer</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pos_customer" name="pos_customer">
                                    </div>
                                </div>

                                <!-- 2. POS Address (with justify-content-start) -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="pos_address" class="form-label mt-1">Address</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="pos_address" name="pos_address" rows="2"></textarea>
                                    </div>
                                </div>

                                <!-- 3. POS State -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="pos_state_id" class="form-label">State</label>
                                    </div>
                                    <div class="col-6">
                                        <select class="js-example-basic-single" name="pos_state_id" id="pos_state_id" style="width: 100%;">
                                            <option value="">Select State</option>
                                            @foreach(getStates() as $state)
                                                <option value="{{ $state->id }}" data-state_code="{{ $state->state_code }}">{{ $state->state }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <input type="text" class="form-control skip-tab" id="state_code" name="state_code" readonly>
                                    </div>
                                </div>

                                <!-- 4. POS GSTIN No. -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="pos_gstin_no" class="form-label">GSTIN No.</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pos_gstin_no" name="pos_gstin_no">
                                    </div>
                                </div>

                                <!-- 5. POS PAN No. -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="pos_pan_no" class="form-label">PAN No.</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="pos_pan_no" name="pos_pan_no">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- DETAILS GRID SECTION (Without Footer)                                     -->
                    <!-- ========================================================================= -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary toggleButton" data-bs-target="#InvoiceDetailModal" id="addDetailRowBtn">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Invoice Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="InvoiceDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_detail_width">Actions</th>
                                                        <th scope="col">DC No.</th>
                                                        <th scope="col">DC Date</th>
                                                        <th scope="col">Report No.</th>
                                                        <th scope="col">Film Brand</th>
                                                        <th scope="col" class="th_film_size">Film Size</th>
                                                        <th scope="col" class="th_film_qty">Film Qty.</th>
                                                        <th scope="col" class="th_qty">Qty.</th>
                                                        <th scope="col">Unit</th>
                                                        <th scope="col" class="th_rate">Rate</th>
                                                        <th scope="col" class="th_amount">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="invoice_details_tbody">
                                                    <tr class="centeralign" id="noInvoiceDetails">
                                                        <td colspan="11">No Invoice Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- LOWER SECTION: 2-Column Layout (Matching Photo Exactly)                   -->
                    <!-- ========================================================================= -->
                    <div class="row mt-2">
                        <div class="row g-1">
                            
                            <!-- ---------------- COLUMN 1 (LEFT: Basic Amount, SGST, CGST, IGST) ---------------- -->
                            <div class="col-md-4">
                                <!-- 1. Basic Amount -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="basic_amount" class="form-label">Basic Amount</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="basic_amount" name="basic_amount" autocomplete="off" readonly value="0.00"/>
                                    </div>
                                </div>

                                <!-- 2. SGST -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">SGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control sgst-field gst-fields" id="sgst_percentage" name="sgst_percentage" onblur="formatPoints(this,2)" value="9.00">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab sgst-field disb" id="sgst_amount" name="sgst_amount" onblur="formatPoints(this,2)" readonly value="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. CGST -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">CGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control cgst-field gst-fields" id="cgst_percentage" name="cgst_percentage" onblur="formatPoints(this,2)" value="9.00">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab cgst-field disb" id="cgst_amount" name="cgst_amount" onblur="formatPoints(this,2)" readonly value="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. IGST -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">IGST</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="row g-2">
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control igst-field gst-fields" id="igst_percentage" name="igst_percentage" onblur="formatPoints(this,2)" value="0.00" disabled>
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <div class="input-group input-group-sm w-100">
                                                    <input type="text" class="form-control skip-tab igst-field disb" id="igst_amount" name="igst_amount" onblur="formatPoints(this,2)" readonly value="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ---------------- COLUMN 2 (RIGHT: Discount, Charges, Round Off, Net Amount, Due) ---------------- -->
                            <div class="col-md-4">
                                <!-- 1. Discount -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="discount_amount">Discount</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isNumberKey calc-trigger" id="discount_amount" name="discount_amount" onblur="formatPoints(this,2)" value="0.00"/>
                                    </div>
                                </div>

                                <!-- 2. Other Charges -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="other_charges">Other Charges</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isNumberKey calc-trigger" id="other_charges" name="other_charges" onblur="formatPoints(this,2)" value="0.00"/>
                                    </div>
                                </div>

                                <!-- 3. Round Off -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="round_off_val">Round Off</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="round_off_val" name="round_off_val" onblur="formatPoints(this,2)" readonly value="0.00"/>
                                    </div>
                                </div>

                                <!-- 4. Net Amount -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="net_amount">Net Amount</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="net_amount" name="net_amount" onblur="formatPoints(this,2)" readonly value="0.00"/>
                                    </div>
                                </div>

                                <!-- 5. Due Days -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="due_days">Due Days</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control isNumberKey calc-trigger" id="due_days" name="due_days" value=""/>
                                    </div>
                                </div>

                                <!-- 6. Due Date -->
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label" for="due_date">Due Date</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control date-picker" id="due_date" name="due_date" autocomplete="off"/>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================================================= -->
            <!-- FOOTER BUTTONS                                                            -->
            <!-- ========================================================================= -->
            <div class="modal-footer">
                <button type="submit" form="commonInvoiceForm" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="submit" form="commonInvoiceForm" class="btn btn-primary" id="updatebtn" style="display:none;">Update</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("invoice","print"))
                    <a type="button" class="btn btn-secondary" target="_blank" id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("invoice","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

@include('modals.modals-pending.pending_film_dc_for_invoice_modal')
@include('modals.modals-details.invoice_detail_modal')

@push('script-modal')
<script>
    let loginUserId = {{ auth()->id() }};
    let checkFileRoute = "{{ route('check-file_exists') }}";
</script>
<script src="{{ URL::asset('views/js/invoice.js?ver='.getJsVersion()) }}"></script>
@endpush
