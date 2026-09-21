
<div class="modal fade" id="OADetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Order Acceptance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="OADetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="oad_id" id="oad_id"  value="0"/> 
                    <input type="hidden" name="oad_quotd_id" id="oad_quotd_id"  value="0"/> 

                    <div class="row mb-3 p-1 m-1">

                        <div class="col-lg-6">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_quot_number" class="form-label">Quot. No. </label>
                                        <input type="text" class="form-control" id="oad_quot_number" name=
                                        "oad_quot_number" tabindex="-1" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_quot_date" class="form-label">Quot. Date </label>
                                        <input type="text" class="form-control" id="oad_quot_date" name=
                                        "oad_quot_date" tabindex="-1" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_test_method_id" class="form-label col-form-label">Type of Test <sup class="astric">*</sup></label></label>
                                    <select class="js-example-basic-single" name="oad_test_method_id" id="oad_test_method_id"  autofocus required>
                                        <option value="">Select Type of Test</option>   
                                        @forelse (getAllTestMethod() as $testmethod)
                                            <option value="{{ $testmethod }}">{{ $testmethod }}</option>
                                        @empty
                                        @endforelse 
                                    </select>
                                    <div class="invalid-tooltip" >
                                            Please Select Type of Test
                                    </div> 
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_type_of_job_id" class="form-label col-form-label">Type of Job <sup class="astric">*</sup></label></label>
                                    <select class="js-example-basic-single" name="oad_type_of_job_id" id="oad_type_of_job_id" required>
                                        <option value="">Select Type of Job</option>
                                        @forelse (getTypeOfJob() as $typeofjob)
                                        <option value="{{ $typeofjob->id }}">{{ $typeofjob->type_of_job }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip" >
                                            Please Select Type of Job
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_job_desc_id" class="form-label col-form-label">Job Description</label>
                                    <select class="js-example-basic-single" name="oad_job_desc_id" id="oad_job_desc_id">
                                        <option value="">Select Job Description</option>
                                        @foreach (getMiJobDescription() as $job)
                                            {{-- <option value="{{ $job->id }}" data-parts='@json($job->parts)'>{{ $job->job_description }}</option> --}}
                                            <option
                                                value="{{ $job->id }}"
                                                data-parts="{{ json_encode($job->parts) }}">
                                                {{ $job->job_description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_part_id" class="form-label col-form-label">Part No.</label>
                                    <select class="js-example-basic-single" name="oad_part_id" id="oad_part_id">
                                        <option value="">Select Part No.</option>
                                        <!-- @forelse (getparts() as $part)
                                        <option value="{{ $part->part_id }}">{{ $part->part }}</option>
                                        @empty
                                        @endforelse -->
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_description" class="form-label col-form-label">Description</label>
                                    <textarea name="oad_description" id="oad_description" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_process_at_id" class="form-label col-form-label">Process At</label>
                                    <select class="js-example-basic-single" name="oad_process_at_id" id="oad_process_at_id">
                                        <option value="">Select Process At</option>
                                        <option value="In-House">In-house</option>
                                        <option value="Outside">Outside</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_qty" class="form-label col-form-label">Quantity <sup class="astric">*</sup></label></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="oad_qty" id="oad_qty" style="width: 100px;" onblur="formatPoints(this,3)" required >
                                        <select class="js-example-basic-single" name="oad_unit_id" id="oad_unit_id"  style="width: 225px;">
                                            <option value="">Select Unit</option>
                                             @forelse (getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip" id="oad_qty_error">
                                            Please Enter Quantity.
                                        </div>
                                    </div>
                                </div>
                            </div>  

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_rate_unit" class="form-label col-form-label">Rate/Unit</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="oad_rate_unit" id="oad_rate_unit" style="width: 100px;" onblur="formatPoints(this,2)" >
                                        <select class="js-example-basic-single skip-tab" name="oad_rate_unit_id" id="oad_rate_unit_id"  style="width: 225px;" onblur="formatPoints(this,2)">
                                            <option value="">Select Unit</option>
                                            @forelse (getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip" id="oad_rate_unit_error">
                                            Please Enter Rate/Unit.
                                        </div>
                                    </div>
                                </div>
                            </div>  

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_minimum_charge" class="form-label col-form-label">Minumum Charge</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="oad_minimum_charge" id="oad_minimum_charge" style="width: 100px;" onblur="formatPoints(this,2)" >
                                        <select class="js-example-basic-single" name="oad_minimum_charge_unit_id" id="oad_minimum_charge_unit_id"  style="width: 225px;">
                                            <option value="">Select Unit</option>
                                            <option value="Per Day">Per Day</option>
                                            <option value="Per Visit">Per Visit</option>
                                        </select>
                                        <div class="invalid-tooltip" id="oad_minimum_charge_error">
                                            Please Enter Minumum Charge
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_conveyance_charge" class="form-label col-form-label">Coveyance Charge</label>
                                    <div  class="d-flex align-items-center gap-2">
                                        <input type="text" name="oad_conveyance_charge" id="oad_conveyance_charge" class="form-control isNumberKey" onblur="formatPoints(this,2)" >
                                    </div>
                                </div>
                            </div>  
                            

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="oad_remark" class="form-label col-form-label">Remark</label>
                                    <div  class="d-flex align-items-center gap-2">
                                        <input type="text" name="oad_remark" id="oad_remark" class="form-control">
                                    </div>
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



