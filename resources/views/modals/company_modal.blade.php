 <div class="modal fade" id="CompanyModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CompanyModalLabel">Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonCompanyForm"  class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="HsnCode_name" class="form-label">Company Name</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="company_name" id="company_name" class="form-control"   placeholder="Enter Company Name" required>
                            {{-- <div id="hsn_code_list" class="suggestion_list" ></div>
                            <input type="hidden" name="hsn_suggestion" id="hsn_suggestion">  --}}
                             <div class="invalid-tooltip">
                                Please Enter Company Name.
                            </div>

                        </div>
                    </div>          
                    
                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="address" class="form-label">Address</label>
                        </div>
                        <div class="col-lg-6">
                           <textarea class="form-control" name="address" id="address" rows="3"></textarea> 
                        </div>
                    </div>      
                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="address" class="form-label">Country</label>
                        </div>
                        <div class="col-lg-6">
                            <select class="js-example-basic-single" name="address" id="address">
                                <option value="">Select Country</option>
                                    @forelse (getCountries() as $country)                                   
                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                    @empty
                                    @endforelse   
                                
                            </select>
                        </div>                          
                    </div>     
                    
                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="state" class="form-label">State</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="state" id="state" class="form-control"   placeholder="Enter State Name">                           
                        </div>

                    </div>      

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="city" class="form-label">City</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="city" id="city" class="form-control"   placeholder="Enter City Name">                          
                        </div>

                    </div>      

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="mobile_no" class="form-label">Mobile No.</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="mobile_no" id="mobile_no" class="form-control"   placeholder="Enter Mobile No.">                          
                        </div>

                    </div>  
                    
                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="email" class="form-label">Email ID</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="email" id="email" class="form-control"   placeholder="Enter Email ID">                         
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="web_address" class="form-label">Web Address</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="web_address" id="web_address" class="form-control"   placeholder="Enter Web Address">                            
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="pan" class="form-label">PAN</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="pan" id="pan" class="form-control"   placeholder="Enter PAN">                          
                        </div>

                    </div>                  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="gstin" class="form-label">GSTIN</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="gstin" id="gstin" class="form-control"   placeholder="Enter GSTIN">                            
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="gstin" class="form-label">CIN</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="gstin" id="gstin" class="form-control"   placeholder="Enter CIN">
                           
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="bin" class="form-label">BIN</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="bin" id="bin" class="form-control"   placeholder="Enter BIN">                          
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="exporters_ref" class="form-label">Exporter's Ref. (IEC)</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="exporters_ref" id="exporters_ref" class="form-control"   placeholder="Enter Exporter's Ref.">                        
                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="company_logo" class="form-label">Company Logo</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="file" name="company_logo" id="company_logo" class="form-control">
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-danger" id="removeFileBtn">Remove</button>
                                {{-- <button type="button" class="btn btn-sm btn-secondary" id="viewFileBtn">View</button> --}}
                                <a target="_blank" class="btn btn-sm btn-secondary img-prev hidden addButton" id="agreement_document_prev">View</a>
                            </div>                      

                        </div>

                    </div>  

                    <div class="row mb-3">
                        <div class="col-lg-2">
                            <label for="stamp" class="form-label">Stamp</label>
                        </div>
                        <div class="col-lg-6">
                            <input type="file" name="stamp" id="stamp" class="form-control">   
                             <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-danger" id="removeFileBtn">Remove</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="viewFileBtn">View</button>
                            </div>                               
                        </div>

                    </div>  
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/hsn_code.js?ver='.getJsVersion()) }}"></script>
@endpush
