<div class="modal fade" id="GSTConfigurationModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GSTConfigurationModalLabel">GST Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonGSTConfigurationForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label for="sac" class="form-label">SAC <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="sac" id="sac" class="form-control" required>
                                        <div id="sac_list" class="suggestion_list"></div>
                                        <input type="hidden" name="sac_suggesion" id="sac_suggesion">
                                        <div class="invalid-tooltip">
                                            Please Enter SAC
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label for="description" class="form-label">Description <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="description" id="description" class="form-control" required>
                                        <div class="invalid-tooltip">
                                            Please Enter Description
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary add_detail" data-bs-target="#GSTCommodityDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>GST Commodity Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="GSTCommodityTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_col">Actions</th>
                                                        <th scope="col">Tax Type</th>
                                                        <th scope="col">Tax(%)</th>
                                                        <th scope="col">Effective Date</th>
                                                        <th scope="col">Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noContact">
                                                        <td colspan="6">No GST Commodity Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label for="gc_remark" class="form-label">Remark </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="gc_remark" id="gc_remark" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("gst_configuration","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/gst_configuration.js?ver='.getJsVersion()) }}"></script>
@endpush