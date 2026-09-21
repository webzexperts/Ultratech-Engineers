 <div class="modal fade" id="EstimationCostingModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="EstimationCostingModalLabel">Estimation & Costing</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonEstimationCostingForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">      
                    <input type="hidden" name="inqd_id" id="inqd_id">                             
                    <input type="hidden" name="ec_fr_id" id="ec_fr_id">                             

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button"
                                                class="btn btn-success btn-sm toggleModalBtn"
                                                data-bs-target="#PendingEstimationModal" id="pending_btn">
                                            Pending
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ec_number" class="form-label">Estimation & Costing No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="ec_number" name="ec_number" readonly>
                                        <input type="hidden" id="ec_sequence" name="ec_sequence">
                                        <div class="invalid-tooltip">
                                             Enter Estimation & Costing No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ec_date" class="form-label">Estimation & Costing Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="ec_date" name="ec_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                             Enter Estimation & Costing Date
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Inquiry No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="inq_number" name="inq_number" autocomplete="off" readonly>
                                            <input type="text" class="form-control trans-date-picker skip-tab" id="inq_date" name="inq_date" required autocomplete="off" readonly style="width:100px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_test_method" class="form-label">Type of Test</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inqd_test_method" id="inqd_test_method">
                                            <option value="">Select Type of Test</option>                  
                                            @forelse (getAllTestMethod() as $testmethod)
                                                <option value="{{ $testmethod }}">{{ $testmethod }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_type_of_job_id" class="form-label">Type of Job</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inqd_type_of_job_id" name="inqd_type_of_job_id" autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_job_description_id" class="form-label">Job Description</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inqd_job_description_id" id="inqd_job_description_id">
                                            <option value="">Select Job Description</option>
                                            @forelse(getJobDescription() as $jobdesc)
                                                <option value="{{ $jobdesc->id }}">{{ $jobdesc->job_description }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_part_id" class="form-label">Part No.</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inqd_part_id" id="inqd_part_id">
                                            <option value="">Select Part No.</option> 
                                             @forelse(getparts() as $part)
                                                <option value="{{ $part->part_id }}">{{ !empty($part->drg_no) ? $part->part_no . ' - ' . $part->drg_no : $part->part_no }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_process_at" class="form-label">Process At</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inqd_process_at" id="inqd_process_at">
                                            <option value="">Select Process At</option>
                                            <option value="In-House">In-house</option>
                                            <option value="Outside">Outside</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;">
                                    <div class="col-4"></div>
                                    <div class="col-8"></div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="inqd_description" class="form-label">Inq. Description</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control skip-tab" id="inqd_description" name="inqd_description" autocomplete="off" readonly></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Quantity & Unit</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="inqd_quantity" name="inqd_quantity" autocomplete="off" readonly>
                                            <select class="js-example-basic-single skip-tab" name="inqd_unit_id" id="inqd_unit_id" style="width:100px;">
                                                <option value="">Select Unit</option>
                                                @forelse(getUnit() as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="ec_estimation" class="form-label">Estimation</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="ec_estimation" name="ec_estimation" autocomplete="off"></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="ec_costing" class="form-label">Costing</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="ec_costing" name="ec_costing" autocomplete="off"></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">File Upload</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="ec_file_upload" id="ec_file_upload" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="ec_file_upload_doc" name="ec_file_upload_doc" />
                                                <a data-remove="ec_file_upload" id="ec_file_upload_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFile(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="ec_file_upload_prev">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label for="inq_customer_id" class="form-label">Customer</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inq_customer_id" id="inq_customer_id">
                                            <option value="">Select Customer</option>
                                            @forelse(getCustomers() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2" style="margin-bottom: 1.9rem !important;">
                                    <div class="col-4"></div>
                                    <div class="col-8"></div>
                                </div>                               

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label for="inq_ref_no_date" class="form-label">Ref. No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inq_ref_no_date" name="inq_ref_no_date" readonly>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_remark" class="form-label">Inq. Remark</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inqd_remark" name="inqd_remark" autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Inq. File</label>
                                    </div>
                                    <div class="col-8">
                                        <a id="inqd_file_upload_prev" target="_blank" class="hide"></a>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Feas. Review File</label>
                                    </div>
                                    <div class="col-8">
                                        <a id="fr_file_upload_prev" target="_blank" class="hide"></a>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="inq_sp_note" class="form-label">Inq. Sp. Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control skip-tab" id="inq_sp_note" name="inq_sp_note" autocomplete="off" readonly></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="ec_prepared_by_id" class="form-label">Prepared By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="ec_prepared_by_id" id="ec_prepared_by_id" required readonly>
                                            <option value="">Select Prepared By </option>
                                            @forelse(getUsers() as $user)
                                                <option value="{{ $user->id }}"  {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Prepared By
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="ec_reviewed_by_id" class="form-label">Reviewed By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="ec_reviewed_by_id" id="ec_reviewed_by_id" required>
                                            <option value="">Select Reviewed By </option>
                                            @forelse (getUsers() as $user)
                                            <option value="{{ $user->id }}">{{ $user->person_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Reviewed By
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
                @if(hasAccess("estimation_costing","add"))
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
</script>

<script src="{{ URL::asset('views/js/estimation_costing.js?ver='.getJsVersion()) }}"></script>
@endpush
