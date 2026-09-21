<div class="modal fade" id="CustomerModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CustomerModalLabel">Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonCustomerForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id"/>
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4">
                                <!-- <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="customer_code" class="form-label">Customer Code <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" name="customer_code" id="customer_code" class="form-control skip-tab" readonly>
                                    </div>
                                </div> -->

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="customer" class="form-label">Customer <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8">
                                    
                                        <input type="text" name="customer" id="customer" class="form-control" onkeyup="suggestCustomer(event,this)" required>
                                        <div id="customer_list" class="suggestion_list"></div>
                                        <input type="hidden" name="customer_suggesion" id="customer_suggesion">
                                        <div class="invalid-tooltip">
                                             Enter Customer.
                                        </div>
                                    </div>
                                </div>

                                 <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="address" class="form-label">Address </label>
                                    </div>

                                    <div class="col-8">
                                        <textarea name="address" id="address" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="city_id" class="form-label">City <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_city_name" name="city_id" id="city_id" required >
                                                <option value="">Select City</option>
                                                @forelse(getCities() as $city)
                                                    <option value="{{ $city->id }}">{{ $city->city }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                 Select City.
                                            </div>

                                            @if(hasAccess("city","add"))
                                                <i class="plus-icon bx bx-plus-medical" onclick="addedCity(true)" data-bs-target="#CityModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="pincode" class="form-label">Pin Code </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="pincode" id="pincode" class="form-control" rows="3">
                                    </div>
                                </div>

                                 <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="state" class="form-label">State </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="state" id="state" class="form-control skip-tab" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="state_code" class="form-label">State Code </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="state_code" id="state_code" class="form-control skip-tab" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="country" class="form-label">Country </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="country" id="country" class="form-control skip-tab" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="mobile_no" class="form-label">Phone No. </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="mobile_no" id="mobile_no" class="form-control isNumberKey common-phone-validate">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="email" class="form-label">Email ID </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="email" name="email" id="email" class="form-control">
                                        <div class="invalid-tooltip">
                                             Enter a Valid Email ID.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="web_address" class="form-label">Web Address </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="web_address" id="web_address" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="gstin" class="form-label">GSTIN </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="gstin" id="gstin" class="form-control">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="pan" class="form-label">PAN </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="pan" id="pan" class="form-control">
                                    </div>
                                </div>

                                <!-- <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="payment_terms" class="form-label">Payment Terms </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="payment_terms" id="payment_terms" class="form-control" onkeyup="suggestPaymentTerms(event,this)">
                                        <div id="payment_terms_list" class="suggestion_list" ></div>
                                        <input type="hidden" name="paymentTerms" id="paymentTerms">
                                    </div>
                                </div> -->

                                 <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="tan" class="form-label">TAN </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="tan" id="tan" class="form-control">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="msme_reg_no" class="form-label">MSME Reg. No. </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="msme_reg_no" id="msme_reg_no" class="form-control">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="credit_days" class="form-label">Credit Days </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="credit_days" id="credit_days" class="form-control isInteger">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <div class="flex-shrink-0 mr-5">
                                    <button type="button" class="btn btn-primary" data-bs-target="#ContactModal">Add</button>
                                </div>
                                <h4 class="card-title mb-0 flex-grow-1 ml-2">Contact Details</h4>
                            </div>

                            <div class="card-body">
                                <div class="live-preview">
                                    <div class="table-responsive details-action">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="ContactTable">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="action_col">Actions</th>
                                                    <th scope="col">Contact Person</th>
                                                    <th scope="col">Designation</th>
                                                    <th scope="col">Phone</th>
                                                    <!-- <th scope="col">Mobile</th> -->
                                                    <th scope="col">Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                    <tr class="centeralign" id="noContact">
                                                    <td colspan="5">No Contact Details Added</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                 @if(hasAccess("customer","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/customer.js?ver='.getJsVersion()) }}"></script>
@endpush