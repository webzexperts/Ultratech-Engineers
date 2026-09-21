<div class="modal fade" id="SupplierModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="SupplierModalLabel">Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonSupplierForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="supplier_name" class="form-label">Supplier <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="supplier_name" id="supplier_name" class="form-control"  required>
                                        {{-- onkeyup="suggestSupplier(event,this)" --}}
                                        {{-- <div id="supplier_name_list" class="suggestion_list"></div>
                                        <input type="hidden" name="supplier_name_suggesion" id="supplier_name_suggesion"> --}}
                                        <div class="invalid-tooltip">
                                            Enter Supplier.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="address" class="form-label mt-1">Address </label>
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

                                        <select class="js-example-basic-single suggest_city_name" name="city_id" id="city_id" required>
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
                                        <label for="country" class="form-label">Country </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="country" id="country" class="form-control skip-tab" readonly>
                                    </div>
                                </div>

                                <!-- <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="contact_person" class="form-label">Contact Person </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="contact_person" id="contact_person" class="form-control">
                                    </div>
                                </div> -->

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="phone_no" class="form-label">Phone No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="phone_no" id="phone_no" class="form-control mobile-f">
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="email_id" class="form-label">Email ID</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="email" name="email_id" id="email_id" class="form-control">
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
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="GSTIN" class="form-label">GSTIN </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="GSTIN" id="GSTIN" class="form-control">
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="PAN" class="form-label">PAN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="PAN" id="PAN" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="TAN" class="form-label">TAN</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="TAN" id="TAN" class="form-control">
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="msme_reg_no" class="form-label">MSME Reg. No</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="msme_reg_no" id="msme_reg_no" class="form-control isNumberKey common-phone-validate">
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="payment_terms" class="form-label">Payment Terms </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="payment_terms" id="payment_terms" class="form-control">
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card mt-3">
                                <div class="card-header d-flex align-items-center">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary add_detail" data-bs-target="#SupplierContactModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                        <b>Supplier Details</b>
                                    </h4>

                                </div>
                            
                                <div class="card-body">
                                    <div class="table-responsive details-action">
                                        <table class="table table-bordered align-middle mb-0" id="SupplierDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_detail_width">Actions</th>
                                                    <th>Contact Person</th>
                                                    <th>Phone No.</th>
                                                    <th>Email ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" class="text-center" id="noDetails">
                                                        No Supplier Details Added
                                                    </td>
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
                @if(hasAccess("supplier","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/supplier.js?ver='.getJsVersion()) }}"></script>
@endpush