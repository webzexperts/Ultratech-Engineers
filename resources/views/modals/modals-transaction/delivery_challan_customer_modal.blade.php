 <div class="modal fade" id="DcCustomerModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="DcCustomerModalLabel">Delivery Challan (Customer)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonDCCustomerForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">DC No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="dc_sequence" name="dc_sequence" style="max-width:80px" autofocus required>
                                             <div class="invalid-tooltip">
                                                Enter DC No.
                                            </div>
                                            <input type="text" class="form-control" id="dc_number" name="dc_number" tabindex="-1" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label"> DC Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control trans-date-picker" id="dc_date" name="dc_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter DC Date
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="customer_id" class="form-label">
                                            Customer <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single suggest_city_name" name="customer_id" id="customer_id" required>
                                            <option value="">Select Customer</option>
                                            @forelse(getCustomers() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Customer.
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary toggleButton" data-bs-target="#DCDetailsModal">Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Delivery Challan Details</b>
                            </h4>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="DCDetailTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="action_detail_width">Action </th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Item Group</th>
                                            <th scope="col">Main Group</th>
                                            <th scope="col">Sr. No.</th>
                                            <th scope="col">Stock</th>
                                            <th scope="col">DC Qty.</th>
                                            <th scope="col">Unit</th>
                                            <th scope="col">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="11" class="text-center" id="noDetails">
                                                No Delivery Challan Details Added
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
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="transporter" class="form-label">Transporter</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="transporter" name="transporter" autocomplete="off" />
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
                                        <label for="special_note" class="form-label justi mt-1">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" name="special_note" id="special_note"></textarea>
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
                 @if(hasAccess("delivery_challan_customer","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("delivery_challan_customer","add"))
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
<script src="{{ URL::asset('views/js/delivery_challan_customer.js?ver='.getJsVersion()) }}"></script>
@endpush
