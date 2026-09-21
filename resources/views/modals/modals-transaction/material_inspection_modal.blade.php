 <div class="modal fade" id="MaterialInspectionModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="MaterialInspectionModalLabel">Material Inspection</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonMaterialInspectionForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="mins_mid_id" id="mins_mid_id" value="">     

                    <div class="row mt-2">
                        <div class="row g-3">
                            <div class="col-md-4 ">
                               <div class="mb-3 mt-2">
                                    <label class="form-label">Inspection No. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 align-items-start position-relative">
                                        <input type="text" class="form-control isNumberKey" id="mins_sequence" name="mins_sequence" style="max-width:80px" onchange="checkSequence()" required>
                                        <div class="invalid-tooltip">
                                            Enter Inspection No. 
                                        </div>
                                        <input type="text" class="form-control skip-tab" id="mins_number" name="mins_number" tabindex="-1" readonly>
                                    </div>
                                </div>
                                <div class="col-12 g-2 position-relative">
                                    <label class="form-label">Inspection Date <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control trans-date-picker" id="mins_date" name="mins_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Inspection Date
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingForMaterialInspectionModal" id="pending_btn" disabled>Pending</button>
                                    </div>
                                </div>

                                <div class="col-12 g-2 mt-2">
                                    <label for="mi_customer_id" class="form-label">Customer</label>
                                    <select class="js-example-basic-single skip-tab" name="mi_customer_id" id="mi_customer_id" >
                                        <option value="">Select Customer</option>                  
                                        @forelse(getCustomers() as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                            @empty
                                        @endforelse                                
                                    </select>
                                </div>

                               <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <label for="mi_number" class="form-label mb-2">
                                            Inward No.
                                        </label>
                                        <input type="text" class="form-control skip-tab" id="mi_number" name="mi_number" autocomplete="off" readonly/>
                                    </div>
                                    <div class="col-6">
                                        <label for="mi_date" class="form-label mb-2">
                                            Inward Date
                                        </label>
                                        <input type="text" class="form-control trans-date-picker skip-tab" id="mi_date" name="mi_date" required autocomplete="off" readonly/>
                                    </div>
                                </div>

                                <div class="row g-2 mt-2">
                                    <div class="col-6">
                                        <label for="mi_challan_number" class="form-label mb-2">
                                            Challan No.
                                        </label>
                                        <input type="text" class="form-control skip-tab" id="mi_challan_number" name="mi_challan_number" autocomplete="off" readonly/>
                                    </div>
                                    <div class="col-6">
                                        <label for="mi_challan_date" class="form-label mb-2">
                                            Challan Date
                                        </label>
                                        <input type="text" class="form-control trans-date-picker skip-tab" id="mi_challan_date" name="mi_challan_date" required autocomplete="off" readonly/>
                                    </div>
                                </div>

                               <div class="col-12 g-2 mt-2">
                                    <label for="mid_test_method_id" class="form-label">Type of Test </label>
                                    <select class="js-example-basic-single skip-tab" name="mid_test_method_id" id="mid_test_method_id" >
                                        <option value="">Select Type of Test</option>                  
                                        @forelse (getAllTestMethod() as $testmethod)
                                            <option value="{{ $testmethod }}">{{ $testmethod }}</option>
                                            @empty
                                        @endforelse                                 
                                    </select>
                                </div>
  
                            </div>
                            <div class="col-md-4">
                                <div class="col-12 g-2 mt-2">
                                    <label for="mid_type_of_job_id" class="form-label">Type of Job</label>
                                    <input type="text" class="form-control skip-tab" id="mid_type_of_job_id" name="mid_type_of_job_id" autocomplete="off"  readonly/>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mid_job_desc" class="form-label">Job Description</label>
                                    <textarea type="text" class="form-control skip-tab" id="mid_job_desc" name="mid_job_desc" autocomplete="off" readonly></textarea>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mid_part_id" class="form-label">Part No.</label>
                                    <select class="js-example-basic-single skip-tab" name="mid_part_id" id="mid_part_id" >
                                        <option value="">Select Part No.</option> 
                                         @forelse(getparts() as $part)
                                            <option value="{{ $part->part_id }}">{{ !empty($part->drg_no) ? $part->part_no . ' - ' . $part->drg_no : $part->part_no }}</option>
                                            @empty
                                        @endforelse                                             
                                    </select>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mins_description" class="form-label">Description</label>
                                    <textarea type="text" class="form-control" id="mins_description" name="mins_description" autocomplete="off" ></textarea>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mid_qty" class="form-label">Challan Qty.</label>
                                    <input type="text" class="form-control skip-tab" id="mid_qty" name="mid_qty" autocomplete="off" readonly/>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mid_pending_qty" class="form-label">Pending Qty.</label>
                                    <input type="text" class="form-control skip-tab" id="mid_pending_qty" name="mid_pending_qty" autocomplete="off" readonly/>
                                </div>
                                

                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 mt-2">
                                    <div class="col-6 position-relative">
                                        <label for="mins_insp_qty" class="form-label mb-2">
                                            Insp. Qty. <sup class="astric">*</sup>
                                        </label>
                                        <input type="text" class="form-control isNumberKey" id="mins_insp_qty" name="mins_insp_qty" onblur="formatPoints(this,3)" autocomplete="off" required/>
                                         <div class="invalid-tooltip">
                                            Enter Insp. Qty.
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="mid_qty_unit_id" class="form-label mb-2">
                                            Unit
                                        </label>
                                         <select class="js-example-basic-single skip-tab" name="mid_qty_unit_id" id="mid_qty_unit_id">
                                            <option value="">Select Unit</option>
                                             @forelse (getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 g-2 mt-2 position-relative">
                                    <label for="mins_result" class="form-label">Result <sup class="astric">*</sup></label>
                                    <select class="js-example-basic-single" name="mins_result" id="mins_result" required>
                                        <option value="">Select Result</option>
                                        <option value="Accepted">Accepted</option>
                                        <option value="Short">Short</option>
                                        <option value="Rejected">Rejected</option>                               
                                    </select>
                                     <div class="invalid-tooltip">
                                        Select Result
                                    </div>
                                </div>
                                <div class="col-12 g-2 mt-2 position-relative">
                                    <label for="mins_rej_reason" class="form-label">Rej. Reason</label>
                                    <select class="js-example-basic-single skip-tab" name="mins_rej_reason" id="mins_rej_reason" >
                                        <option value="">Select Rej. Reason</option>  
                                        <option value="Damage">Damage</option>  
                                        <option value="Extra-returned without testing">Extra-returned without testing</option>                 
                                    </select>
                                        <div class="invalid-tooltip">
                                            Please Select Rej. Reason
                                        </div>
                                </div>

                                 <div class="col-12 g-2 mt-2 position-relative">
                                    <label for="mins_inspected_by_id" class="form-label">Inspected By <sup class="astric">*</sup></label>
                                    <select class="js-example-basic-single" name="mins_inspected_by_id" id="mins_inspected_by_id" required>
                                        <option value="">Select Inspected By </option>
                                        @forelse (getUsers() as $user)
                                        <option value="{{ $user->id }}">{{ $user->person_name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                     <div class="invalid-tooltip">
                                        Please Select Inspected By
                                    </div>
                                </div>
                                <div class="col-12 g-2 mt-2">
                                    <label for="mins_special_note" class="form-label">Special Note</label>
                                    <textarea type="text" class="form-control" id="mins_special_note" name="mins_special_note" autocomplete="off" ></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


@push('script-modal')
<script src="{{ URL::asset('views/js/material_inspection.js?ver='.getJsVersion()) }}"></script>
@endpush
