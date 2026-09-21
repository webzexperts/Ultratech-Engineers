<div class="modal fade" id="PartModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PartModalLabel">Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonPartForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-2">
                                <label for="job_desc_id" class="form-label">Job Description <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4">
                                <select class="js-example-basic-single suggest_job_description" name="job_desc_id" id="job_desc_id" required>
                                    <option value="">Select Job Description</option>

                                    @forelse (getJobDescription() as $job_desc)
                                    <option value="{{ $job_desc->id }}">{{ $job_desc->job_description }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                <div class="invalid-tooltip">
                                     Select Job Description
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header d-flex align-items-center">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        <b>Part Details</b>
                                    </h4>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-primary btn-sm me-2" id="addContainerBtn">+</button>
                                    </div>
                                </div>
                            
                                <div class="card-body">
                                    <div class="table-responsive details-action">
                                        <table class="table table-bordered align-middle table-nowrap mb-0" id="PartDetailTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_detail_width">Actions</th>
                                                    <th>Part No. <sup class="astric">*</sup></th>
                                                    <th>Drg. No. <sup class="astric">*</sup></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="3" class="text-center" id="noDetails">
                                                        No Part Details Added
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
                @if(hasAccess("part","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/part.js?ver='.getJsVersion()) }}"></script>
@endpush