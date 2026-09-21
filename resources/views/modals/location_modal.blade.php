<div class="modal fade" id="LocationModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="LocationModalLabel">Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonLocationForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="country_id" id="country_id">
                    <input type="hidden" name="state_id" id="state_id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_name" class="form-label">Location <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="gap-2 position-relative">
                                            <input type="text" name="location_name" id="location_name" class="form-control" required>
                                             {{-- onkeyup="suggestLocationName(event,this)" --}}
                                            {{-- <div id="location_name_list" class="suggestion_list"></div>
                                            <input type="hidden" name="location_name_suggesion" id="location_name_suggesion"> --}}
                                            <div class="invalid-tooltip">
                                                Enter Location.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="location_type" class="form-label">Type <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="location_type" id="location_type" required>
                                                <option value="">Select Type</option>
                                                @forelse(getLocationType() as $location_type)
                                                    <option value="{{ $location_type }}">{{ $location_type }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Type.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_code" class="form-label">Location Code <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class=" gap-2 position-relative">
                                            <input type="text" name="location_code" id="location_code" class="form-control" required>
                                             {{-- onkeyup="suggestLocationCode(event,this)" --}}
                                            {{-- <div id="location_code_list" class="suggestion_list"></div>
                                            <input type="hidden" name="location_code_suggesion" id="location_code_suggesion"> --}}
                                            <div class="invalid-tooltip">
                                                Enter Location Code.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="gst_bill_location" class="form-label">GST Billing Location <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="gst_bill_location" id="gst_bill_location" required>
                                                <option value="">Select GST Billing Location</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select GST Billing Location.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_company_name" class="form-label">Company Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" name="location_company_name" id="location_company_name" class="form-control" required>
                                            <div class="invalid-tooltip">
                                                Enter Company Name.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="location_address" class="form-label mt-1">Address <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" name="location_address" id="location_address" required></textarea>
                                        <div class="invalid-tooltip">
                                            Enter Address.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_city_id" class="form-label">City <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="location_city_id" id="location_city_id" required>
                                                <option value="">Select City</option>
                                                @forelse(getCities() as $city)
                                                    <option value="{{ $city->id }}">{{ $city->city }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select City.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_pin_code" class="form-label">Pin Code <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" name="location_pin_code" id="location_pin_code" class="form-control" required>
                                            <div class="invalid-tooltip">
                                                 Enter Pin Code.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_state_id" class="form-label">State </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2">
                                            <input type="text" name="location_state_id" id="location_state_id" class="form-control skip-tab" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_country_id" class="form-label">Country </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2">
                                            <input type="text" name="location_country_id" id="location_country_id" class="form-control skip-tab" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_phone_no" class="form-label">Phone No. </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" name="location_phone_no" id="location_phone_no" class="form-control" >
                                            <div class="invalid-tooltip">
                                                Enter Valid Phone No.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_email_id" class="form-label">Email ID</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="email" name="location_email_id" id="location_email_id" class="form-control">
                                            <div class="invalid-tooltip">
                                                Enter Valid Email ID.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_gstin" class="form-label">GSTIN <sup class="astric" id="gstinAsterisk" style="display:none">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" name="location_gstin" id="location_gstin" class="form-control" required>
                                            <div class="invalid-tooltip" id="gstinValidationMessage" style="display:none">
                                                Enter GSTIN.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_pan" class="form-label">PAN </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" name="location_pan" id="location_pan" class="form-control" >
                                            <div class="invalid-tooltip">
                                                Enter PAN.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_nabl_applicable" class="form-label">NABL Applicable <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="location_nabl_applicable" id="location_nabl_applicable" required>
                                                <option value="">Select NABL Applicable</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select NABL Applicable.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1" id="div_nabl_conf" >
                                    <div class="col-4">
                                        <label for="nabl_id" class="form-label">NABL Location <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="nabl_id" id="nabl_id">
                                            <option value="">Select NABL Location</option>
                                            @forelse(getNABLConfiguration() as $nabl_conf)
                                                <option value="{{ $nabl_conf->nabl_id }}">{{ $nabl_conf->nabl_location }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select NABL Location.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label col-form-label">NABL Symbol <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="location_nabl_symbol" id="location_nabl_symbol" accept=".png,.jpg,.jpeg,.gif,.pdf" >
                                                <input type="hidden" id="location_nabl_symbol_doc" name="location_nabl_symbol_doc" />
                                                <a href="#" data-remove="location_nabl_symbol" id="location_nabl_symbol_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFilenabl(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="location_nabl_symbol_prev">View</a>

                                                <div class="invalid-tooltip">
                                                     Select NABL Symbol.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label">NABL Test <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="border rounded p-2">
                                            <div class="row g-2">

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="RT" id="nabl_rt">
                                                        <label class="form-check-label" for="nabl_rt">RT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="MPT" id="nabl_mpt">
                                                        <label class="form-check-label" for="nabl_mpt">MPT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="DPT" id="nabl_dpt">
                                                        <label class="form-check-label" for="nabl_dpt">DPT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="UT" id="nabl_ut">
                                                        <label class="form-check-label" for="nabl_ut">UT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="ET" id="nabl_et">
                                                        <label class="form-check-label" for="nabl_et">ET</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="VT" id="nabl_vt">
                                                        <label class="form-check-label" for="nabl_vt">VT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="PAUT" id="nabl_paut">
                                                        <label class="form-check-label" for="nabl_paut">PAUT</label>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="location_nabl_test[]" value="UTT" id="nabl_utt">
                                                        <label class="form-check-label" for="nabl_utt">UTT</label>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- validation -->
                                        <div class="nabl-error   invalid-tooltip">
                                             Select NABL Test.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_ilac_applicable" class="form-label">ILAC Applicable <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="location_ilac_applicable" id="location_ilac_applicable">
                                                <option value="">Select ILAC Applicable</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                            <div class="invalid-tooltip">
                                                 Select ILAC Applicable.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label col-form-label">ILAC Symbol <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="location_ilac_symbol" id="location_ilac_symbol" accept=".png,.jpg,.jpeg,.gif,.pdf" >
                                                <input type="hidden" id="location_ilac_symbol_doc" name="location_ilac_symbol_doc" />
                                                <a href="#" data-remove="location_ilac_symbol" id="location_ilac_symbol_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileilac(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="location_ilac_symbol_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select ILAC Symbol.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="show_all_location_camera" class="form-label">Show All Location  Camera</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="show_all_location_camera" id="show_all_location_camera" value="Yes">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-1 mt-1">          
                            <div class="col-12">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-header-address-label">
                                        <label for="location_header_address" class="form-label">Header Address <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-header-address-input">
                                        <textarea class="ckeditor-classic" name="location_header_address" id="location_header_address" required></textarea>
                                        <div class="invalid-tooltip">
                                            Enter Header Address.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-1 mt-1">
                            <div class="col-md-4">

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="location_status" class="form-label">Status<sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <select class="js-example-basic-single" name="location_status" id="location_status" required>
                                                <option value="Active">Active</option>
                                                <option value="Deactive">Deactive</option>
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Status.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("location","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>

            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/location.js?ver='.getJsVersion()) }}"></script>
@endpush