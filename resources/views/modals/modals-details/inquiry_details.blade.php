<div class="modal fade" id="InquiryDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Inquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="InquiryDetailsForm" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="inqd_id" id="inqd_id" value="0"/>

                    <div class="row mt-2">  
                        <div class="col-md-8">  
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_test_method" class="form-label">
                                            Type of Test <sup class="astric">*</sup>
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <select class="js-example-basic-single"
                                                name="inqd_test_method"
                                                id="inqd_test_method"
                                                required>
                                            <option value="">Select Type of Test</option>

                                            @forelse(getAllTestMethod() as $testmethod)
                                                <option value="{{ $testmethod }}">
                                                    {{ $testmethod }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </select>

                                        <div class="invalid-tooltip">
                                            Select Type of Test
                                        </div>
                                    </div>
                                </div>

        
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_type_of_job_id" class="form-label">
                                            Type of Job <sup class="astric">*</sup>
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_type_of_job"
                                                    name="inqd_type_of_job_id"
                                                    id="inqd_type_of_job_id"
                                                    required>
                                                <option value="">Select Type of Job</option>

                                                @forelse(getTypeOfJob() as $typeofjob)
                                                    <option value="{{ $typeofjob->id }}">
                                                        {{ $typeofjob->type_of_job }}
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>

                                            <div class="invalid-tooltip">
                                              Select Type of Job
                                            </div>

                                            @if(hasAccess("type_of_job","add"))
                                                <i class="plus-icon bx bx-plus-medical"
                                                onclick="addedTypeofJob(true)"
                                                data-bs-target="#TypeOfJobModal"></i>
                                            @endif
                                        </div>                                       
                                    </div>
                                </div>

        
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_job_description_id" class="form-label">
                                            Job Description
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single suggest_job_description"
                                                    name="inqd_job_description_id"
                                                    id="inqd_job_description_id">
                                                <option value="">Select Job Description</option>

                                                @foreach(getMiJobDescription() as $job)
                                                    {{-- <option value="{{ $job->id }}" data-parts='@json($job->parts)'>{{ $job->job_description }}</option> --}}
                                                    <option value="{{ $job->id }}"
                                                            data-parts="{{ json_encode($job->parts) }}">
                                                        {{ $job->job_description }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if(hasAccess("job_description","add"))
                                                <i class="plus-icon bx bx-plus-medical"
                                                onclick="addedJobDescription(true)"
                                                data-bs-target="#JobDescriptionModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="inqd_part_no" class="form-label">
                                            Part No.
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text" name="inqd_part_no"
                                                id="inqd_part_no"
                                                class="form-control"
                                                onkeyup="suggestInquiryPartNo(event, this)"
                                                maxlength="155"
                                                autocomplete="off">
                                        <div id="inqd_part_no_list"></div>
                                        <input type="hidden" name="inqd_part_no_suggestion" id="inqd_part_no_suggestion">
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_process_at" class="form-label">
                                            Process At
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <select class="js-example-basic-single"
                                                name="inqd_process_at"
                                                id="inqd_process_at">
                                            <option value="">Select Process At</option>
                                            <option value="In-House">In-House</option>
                                            <option value="Outside">Outside</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label for="inqd_description" class="form-label">
                                            Description
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <textarea name="inqd_description"
                                                id="inqd_description"
                                                class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_quantity" class="form-label">
                                            Quantity <sup class="astric">*</sup>
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text"
                                            class="form-control isNumberKey"
                                                name="inqd_quantity"
                                                id="inqd_quantity"
                                                {{-- onblur="formatPoints(this,3)" --}}
                                                onblur="formatPoints(this,2)"
                                                required   style="width:130px;">
                                                 <div class="invalid-tooltip">
                                                    Enter Quantity
                                                </div>
                                            <div style="width: 150px;" class="otherselectwidth"> 
                                                <select class="form-select js-example-basic-single mst_unit"
                                                        name="inqd_unit_id"
                                                        id="inqd_unit_id">
                                                    <option value="">Select Unit</option>

                                                    @forelse(getUnit() as $unit)
                                                        <option value="{{ $unit->id }}">
                                                            {{ $unit->unit }}
                                                        </option>
                                                    @empty
                                                    @endforelse
                                                </select>

                                                @if(hasAccess("unit","add"))
                                                    <i class="plus-icon bx bx-plus-medical"
                                                    onclick="addedUnit(true)"
                                                    data-bs-target="#UnitModal"></i>
                                                @endif

                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>

            
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_feasibility_required" class="form-label">
                                            Feasibility Required <sup class="astric">*</sup>
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <select class="js-example-basic-single"
                                                name="inqd_feasibility_required"
                                                id="inqd_feasibility_required"
                                                required>
                                            <option value="">Select Feasibility Required</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>

                                        <div class="invalid-tooltip">
                                            Select Feasibility Required
                                        </div>
                                    </div>
                                </div>

        
                                <!-- <div class="row g-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label for="inqd_estimation_required" class="form-label">
                                            Estimation Required
                                            <sup class="astric" id="est_star" style="display:none;">*</sup>
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <select class="js-example-basic-single"
                                                name="inqd_estimation_required"
                                                id="inqd_estimation_required">
                                            <option value="">Select Estimation Required</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>

                                        <div class="invalid-tooltip">
                                            Select Estimation Required
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Remark -->
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_remark" class="form-label">
                                            Remark
                                        </label>
                                    </div>

                                    <div class="col-8">
                                        <input type="text"
                                            name="inqd_remark"
                                            id="inqd_remark"
                                            class="form-control">
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="inqd_file_upload" class="form-label">
                                            File Upload
                                        </label>
                                    </div>

                                    <div class="col-8 d-flex">
                                        <input class="form-control"
                                            type="file"
                                            name="inqd_file_upload"
                                            id="inqd_file_upload"
                                            onchange="InquiryfileUpload(event)"
                                            accept=".png,.jpg,.jpeg,.gif,.pdf">

                                        <input type="hidden"
                                            id="inqd_file_upload_doc"
                                            name="inqd_file_upload_doc" />

                                        <a href="#"
                                        data-remove="inqd_file_upload"
                                        id="inqd_file_upload_remove"
                                        class="btn fileupload-exists hide ms-2"
                                        data-dismiss="fileupload"
                                        onclick="removeFile(event)">
                                            Remove
                                        </a>

                                        <a target="_blank"
                                        class="btn img-prev hide ms-2"
                                        id="inqd_file_upload_prev">
                                            View
                                        </a>
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

<script>
    window.jobPartsMap = {};
    @foreach(getMiJobDescription() as $job)
        window.jobPartsMap["{{ $job->id }}"] = @json($job->parts);
    @endforeach
</script>