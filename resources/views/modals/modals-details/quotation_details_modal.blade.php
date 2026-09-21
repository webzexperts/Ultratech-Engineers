
<div class="modal fade" id="QuotationDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Quotation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="QuotationDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="quotd_id" id="quotd_id"  value="0"/> 
                    <input type="hidden" name="quotd_inqd_id" id="quotd_inqd_id"  value="0"/> 

                    <div class="row mt-2">
                        <div class="col-md-8">
                            <!-- Inq No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_inq_number" class="form-label">Inq No. </label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="quotd_inq_number" name="quotd_inq_number" tabindex="-1" readonly>
                                </div>
                            </div>

                            <!-- Inq Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_inq_date" class="form-label">Inq Date </label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="quotd_inq_date" name="quotd_inq_date" tabindex="-1" readonly>
                                </div>
                            </div>

                            <!-- Type of Test -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_test_method_id" class="form-label pt-0">Type of Test</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="quotd_test_method_id" id="quotd_test_method_id" autofocus>
                                        <option value="">Select Type of Test</option>   
                                        @forelse (getAllTestMethod() as $testmethod)
                                            <option value="{{ $testmethod }}">{{ $testmethod }}</option>
                                        @empty
                                        @endforelse     
                                    </select>
                                </div>
                            </div>

                            <!-- Type of Job -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_type_of_job_id" class="form-label pt-0">Type of Job</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="quotd_type_of_job_id" id="quotd_type_of_job_id">
                                        <option value="">Select Type of Job</option>
                                        @forelse (getTypeOfJob() as $typeofjob)
                                        <option value="{{ $typeofjob->id }}">{{ $typeofjob->type_of_job }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <!-- Job Description -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_job_desc_id" class="form-label pt-0">Job Description</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="quotd_job_desc_id" id="quotd_job_desc_id">
                                        <option value="">Select Job Description</option>
                                        @foreach(getMiJobDescription() as $job)
                                            {{-- <option value="{{ $job->id }}" data-parts='@json($job->parts)'>{{ $job->job_description }}</option> --}}
                                            <option value="{{ $job->id }}" data-parts="{{ json_encode($job->parts) }}">{{ $job->job_description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Part No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_part_no" class="form-label pt-0">Part No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="quotd_part_no" id="quotd_part_no" class="form-control" onkeyup="suggestQuotationPartNo(event, this)" maxlength="155" autocomplete="off">
                                    <div id="quotd_part_no_list"></div>
                                    <input type="hidden" name="quotd_part_no_suggestion" id="quotd_part_no_suggestion">
                                </div>
                            </div>

                            <!-- Process At -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_process_at_id" class="form-label pt-0">Process At</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="quotd_process_at_id" id="quotd_process_at_id">
                                        <option value="">Select Process At</option>
                                        <option value="In-House">In-house</option>
                                        <option value="Outside">Outside</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="row g-2 mb-1">
                                <div class="col-4 justify-content-start">
                                    <label for="quotd_description" class="form-label pt-0">Description</label>
                                </div>
                                <div class="col-8">
                                    <textarea name="quotd_description" id="quotd_description" class="form-control"></textarea>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="row g-2 mb-1 position-relative">
                                <div class="col-4">
                                    <label for="quotd_qty" class="form-label pt-0">Quantity</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- <input type="text" class="form-control isNumberKey" name="quotd_qty" id="quotd_qty" style="width: 80px;" onblur="formatPoints(this,3)" tabindex="-1" readonly> --}}
                                        <input type="text" class="form-control isNumberKey" name="quotd_qty" id="quotd_qty" style="width: 80px;" onblur="formatPoints(this,2)" tabindex="-1" readonly>
                                        <select class="js-example-basic-single" name="quotd_unit_id" id="quotd_unit_id" style="width: 225px;">
                                            <option value="">Select Unit</option>
                                             @forelse (getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip" id="quotd_qty_error">
                                            Please Enter Quantity.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rate/Unit -->
                            <div class="row g-2 mb-1 position-relative">
                                <div class="col-4">
                                    <label for="quotd_rate_unit" class="form-label pt-0">Rate/Unit</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="quotd_rate_unit" id="quotd_rate_unit" style="width: 80px;" onblur="formatPoints(this,2)">
                                        <select class="js-example-basic-single" name="quotd_rate_unit_id" id="quotd_rate_unit_id" style="width: 225px;" onblur="formatPoints(this,2)">
                                            <option value="">Select Unit</option>
                                            @forelse (getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip" id="quotd_rate_unit_error">
                                            Please Enter Rate/Unit.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Minimum Charge -->
                            <div class="row g-2 mb-1 position-relative">
                                <div class="col-4">
                                    <label for="quotd_minimum_charge" class="form-label pt-0">Minimum Charge</label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="quotd_minimum_charge" id="quotd_minimum_charge" style="width: 80px;" onblur="formatPoints(this,2)">
                                        <select class="js-example-basic-single" name="quotd_minimum_charge_unit_id" id="quotd_minimum_charge_unit_id" style="width: 225px;">
                                            <option value="">Select Unit</option>
                                            <option value="Per Day">Per Day</option>
                                            <option value="Per Visit">Per Visit</option>
                                        </select>
                                        <div class="invalid-tooltip" id="quotd_minimum_charge_error">
                                            Please Enter Minimum Charge
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Conveyance Charge -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_conveyance_charge" class="form-label pt-0">Conveyance Charge</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="quotd_conveyance_charge" id="quotd_conveyance_charge" class="form-control isNumberKey" onblur="formatPoints(this,2)">
                                </div>
                            </div>

                            <!-- Remark -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="quotd_remark" class="form-label pt-0">Remark</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="quotd_remark" id="quotd_remark" class="form-control">
                                </div>
                            </div>
                        </div>

                    </div> 
                    
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
            </div>
          </form>

        </div>
    </div>
</div>



