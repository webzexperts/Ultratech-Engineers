<div class="modal fade" id="FeasibilityReviewModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="FeasibilityReviewModalLabel">Feasibility Review</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonFeasibilityReviewForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="inqd_id" id="inqd_id">

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-6">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                       
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Feasibility Review No. & Date<sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control skip-tab" id="fr_number" name="fr_number" readonly>
                                        <input type="hidden" id="fr_sequence" name="fr_sequence">
                                        <div class="invalid-tooltip">
                                            Enter Feasibility Review No.
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control trans-date-picker" id="fr_date" name="fr_date" required autocomplete="off">
                                            <div class="invalid-tooltip">
                                                Enter Feasibility Review Date
                                            </div>
                                            <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingForFeasibilityReviewModal" id="pending_btn"> Pending </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Feasibility Review Date <sup class="astric">*</sup></label>
                                    </div>
                                    
                                </div> -->

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Inquiry No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="inq_number" name="inq_number" autocomplete="off" readonly>
                                            <input type="text" class="form-control trans-date-picker skip-tab" id="inq_date" name="inq_date" autocomplete="off" readonly style="width:100px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Customer</label>
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

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Ref. No. & Date</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inq_ref_no_date" name="inq_ref_no_date" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Type of Test</label>
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
                                        <label class="form-label">Type of Job</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inqd_type_of_job_id" name="inqd_type_of_job_id" autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Job Description</label>
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
                                        <label class="form-label">Part No.</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inqd_part_no" name="inqd_part_no" autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Process At</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="inqd_process_at" id="inqd_process_at">
                                            <option value="">Select Process At</option>
                                            <option value="In-House">In-house</option>
                                            <option value="Outside">Outside</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label">Inq. Description</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control skip-tab" id="inqd_description" name="inqd_description" autocomplete="off" readonly></textarea>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Inq. Remark</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="inqd_remark" name="inqd_remark" autocomplete="off" readonly>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label">Inq. Sp. Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control skip-tab" id="inq_sp_note" name="inq_sp_note" autocomplete="off" readonly></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-3">
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

                                <div class="row ml-2 g-2 mb-1 position-relative">
                                    <div class="col-3">
                                        <label class="form-label">Result <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="fr_result_id" id="fr_result_id" required>
                                            <option value="">Select Result</option>
                                            <option value="Feasible">Feasible</option>
                                            <option value="Not Feasible">Not Feasible</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                             Select Result
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1 position-relative">
                                    <div class="col-3">
                                        <label class="form-label">Reason</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="fr_reason_id" id="fr_reason_id">
                                            <option value="">Select Reason</option>
                                            @forelse(getNotFeasiblereason() as $reason)
                                                <option value="{{ $reason->id }}">{{ $reason->reason_name }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                             Select Reason
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-3 justify-content-start">
                                        <label class="form-label">Feasibility Review</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="fr_feasibility_review" name="fr_feasibility_review" autocomplete="off"></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-3 justify-content-start">
                                        <label for="fr_estimation" class="form-label">Estimation</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="fr_estimation" name="fr_estimation" autocomplete="off"></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-3 justify-content-start">
                                        <label for="fr_costing" class="form-label">Costing</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" id="fr_costing" name="fr_costing" autocomplete="off"></textarea>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">File Upload</label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="fr_file_upload" id="fr_file_upload" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="fr_file_upload_doc" name="fr_file_upload_doc" />
                                                <a href="#" data-remove="fr_file_upload" id="fr_file_upload_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFile(event)">Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="fr_file_upload_prev">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-1 mb-1">
                                    <div class="col-3">
                                        <label class="form-label">Inq. File</label>
                                    </div>
                                    <div class="col-8">
                                        <a id="inqd_file_upload_prev" target="_blank" class="hide"></a>
                                    </div>
                                </div>

                                

                                <div class="row ml-2 g-1 mb-1 position-relative">
                                    <div class="col-3">
                                        <label class="form-label">Prepared By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="fr_prepared_by_id" id="fr_prepared_by_id" readonly >
                                            <option value="">Select Prepared By</option>
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

                                <div class="row g-2 mb-1 ml-2 position-relative">
                                    <div class="col-3">
                                        <label class="form-label">Reviewed By <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="fr_reviewed_by_id" id="fr_reviewed_by_id" required>
                                            <option value="">Select Reviewed By</option>
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
                        <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                        <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                        @if(hasAccess("feasibility_review","add"))
                            <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                        @endif
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script-modal')
<script>
    let loginUserId = {{ auth()->id() }};
</script>
<script src="{{ URL::asset('views/js/feasibility_review.js?ver='.getJsVersion()) }}"></script>
@endpush
