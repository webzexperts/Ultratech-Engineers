<div class="modal fade" id="POMappingProcessModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="POMappingProcessModalLabel">PO Mapping Process</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonPOMappingProcessForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="mins_id" id="mins_id">   
                    <input type="hidden" name="pm_id" id="pm_id">   
                    <input type="hidden" name="pmd_id" id="pmd_id">   
                    <div class="row">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">PO Mapping No. <sup class="astric">*</sup></label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="po_mp_number" name="po_mp_number" style="max-width:130px" autofocus required tabindex="-1" readonly>
                                        <input type="hidden" class="form-control" id="po_mp_sequence" name="po_mp_sequence">
                                        <input type="text" class="form-control trans-date-picker" id="po_mp_date" name="po_mp_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Please Enter PO Mapping No.
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" data-bs-target="#PendingForPOMappingProcessModal" id="pending_btn">Pending</button>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2 position-relative">
                                    <label class="form-label">Customer </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <select class="js-example-basic-single skip-tab" name="po_mp_customer_id" id="po_mp_customer_id">
                                            <option value="">Select Customer</option>
                                            @forelse(getCustomers() as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Insp. No. </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="mins_number" tabindex="-1" readonly>
                                        <input type="text" class="form-control trans-date-picker" id="mins_date" tabindex="-1" readonly autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Challan. No. </label>
                                    <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control" id="challan_no" tabindex="-1" readonly>
                                        <input type="text" class="form-control trans-date-picker" id="challan_date" tabindex="-1" readonly autocomplete="off"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Type of Job </label>
                                    <select class="js-example-basic-single skip-tab" name="po_mp_type_of_job_id" id="po_mp_type_of_job_id">
                                        <option value="">Select Job Description</option>
                                        @forelse(getTypeOfJob() as $type_of_job)
                                            <option value="{{ $type_of_job->id }}">{{ $type_of_job->type_of_job }}</option>
                                            @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Job Description </label>
                                    <select class="js-example-basic-single suggest_job_description skip-tab" name="po_mp_job_description_id" id="po_mp_job_description_id">
                                        <option value="">Select Job Description</option>
                                        @foreach(getMiJobDescription() as $job)
                                            {{-- <option value="{{ $job->id }}" data-parts='@json($job->parts)'>{{ $job->job_description }}</option> --}}
                                            <option value="{{ $job->id }}" data-parts="{{ json_encode($job->parts) }}">{{ $job->job_description }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Part No. </label>
                                    <select class="js-example-basic-single suggest_part skip-tab" name="po_mp_part_id" id="po_mp_part_id">
                                        <option value="">Select Part No.</option>
                                        @forelse(getparts() as $part)
                                            <option value="{{ $part->part_id }}">{{ !empty($part->drg_no) ? $part->part_no . ' - ' . $part->drg_no : $part->part_no }}</option>
                                            @empty
                                        @endforelse 
                                    </select>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Description </label>
                                    <textarea name="po_mp_description" id="po_mp_description" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Type of Test </label>
                                    <select class="js-example-basic-single skip-tab" name="po_mp_test_method_id" id="po_mp_test_method_id">
                                        <option value="">Select Type of Test</option>
                                        @forelse(getAllTestMethod() as $testmethod)
                                            <option value="{{ $testmethod }}">{{ $testmethod }}</option>
                                            @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Accepted Qty. </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="po_mp_accepted_qty" id="po_mp_accepted_qty" style="width: 225px;" onblur="formatPoints(this,3)" tabindex="-1" readonly>
                                        <select class="js-example-basic-single skip-tab" name="po_mp_accepted_unit_id" id="po_mp_accepted_unit_id"  style="width: 80px;">
                                            <option value="">Select Unit</option>
                                            @forelse(getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 g-2 m-2">
                                    <label class="form-label">Pending Qty. </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control isNumberKey" name="po_mp_pending_qty" id="po_mp_pending_qty" style="width: 225px;" onblur="formatPoints(this,3)" tabindex="-1" readonly>
                                        <select class="js-example-basic-single skip-tab" name="po_mp_pending_unit_id" id="po_mp_pending_unit_id"  style="width: 80px;">
                                            <option value="">Select Unit</option>
                                            @forelse(getUnit() as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <h4 class="card-title mb-0 flex-grow-1"><b>PO Mapping Process Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="POMappingProcessDetailTable">
                                                <thead>
                                                    <tr>
                                                        <!-- <th>Action</th> -->
                                                        <th>Order Type</th>
                                                        <th>OA No.</th>
                                                        <th>OA Date</th>
                                                        <th>PO No.</th>
                                                        <th>PO Date</th>
                                                        <th>Job Description</th>
                                                        <th>Part No.</th>
                                                        <th>Unit</th>
                                                        <th>OA Qty.</th>
                                                        <th>Planning Qty.</th>
                                                        <th>Pending Qty.</th>
                                                        <th>Inward Mapping Qty.</th>
                                                        <th>Inward Mapping Unit</th>
                                                        <th>OA Mapping Qty.</th>
                                                        <th>Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noDetails">
                                                        <td colspan="16">No PO Mapping Process Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="col-12 g-2 m-2 position-relative">
                            <label class="form-label">Mapped By <sup class="astric">*</sup></label>
                            <div class="d-flex gap-2 position-relative">
                                <select class="js-example-basic-single" name="po_mp_mapped_by_user_id" id="po_mp_mapped_by_user_id" required>
                                    <option value="">Select Mapped By</option>
                                    @forelse(getUsers() as $issuer_user)
                                        <option value="{{ $issuer_user->id }}">{{ $issuer_user->person_name }}</option>
                                        @empty
                                    @endforelse
                                </select>
                                <div class="invalid-tooltip">
                                    Please Select Mapped By
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="col-12 g-2 m-2 position-relative">
                            <label class="form-label">Total Mapping No. </label>
                            <div class="d-flex gap-2 position-relative">
                                <input type="text" class="form-control" id="po_mp_total_mapping_no" name="po_mp_total_mapping_no" tabindex="-1" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="col-12 g-2 m-2 position-relative">
                            <label class="form-label">Special Note </label>
                            <textarea name="po_mp_sp_note" id="po_mp_sp_note" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/po_mapping_process.js?ver='.getJsVersion()) }}"></script>
@endpush